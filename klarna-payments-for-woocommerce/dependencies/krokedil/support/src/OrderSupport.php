<?php

namespace KrokedilKlarnaPaymentsDeps\Krokedil\Support;

use KrokedilKlarnaPaymentsDeps\Krokedil\Support\AJAX;
/**
 * Class to generate support information for WooCommerce orders and order information so it can be sent to Krokedil.
 *
 * @package Krokedil/Support
 */
class OrderSupport
{
    /**
     * Placeholder for a masked value that was present.
     */
    const REDACTED = '[REDACTED]';
    /**
     * Placeholder for a masked value that was empty.
     */
    const MISSING = '[MISSING]';
    /**
     * The address fields kept readable, since none of them identify a person on their own.
     */
    const ADDRESS_KEPT = array('city', 'state', 'postcode', 'country');
    /**
     * The top level order fields that identify the customer or grant access to the order.
     */
    const ORDER_FIELDS_MASKED = array('customer_ip_address', 'customer_user_agent', 'order_key', 'customer_note');
    /**
     * Key fragments whose values are masked in the order meta and the log lines, matched case insensitively as substrings.
     */
    const KEYS_MASKED = array('email', 'phone', 'first_name', 'last_name', 'given_name', 'family_name', 'company', 'organization_name', 'address_1', 'address_2', 'street_address', 'address_index', 'ip_address', 'user_agent', 'date_of_birth', 'personal_number', 'national_identification', 'pno', 'order_key', 'token', 'secret', 'password', 'authorization', 'redirect_url', 'distribution_url', 'qr_code_url', 'html_snippet', 'vat_id', 'organization_registration_id', 'tracking_number', 'tracking_uri', 'passwd', 'api_key', 'apikey', 'credential', 'private_key', 'signature', 'cookie', 'csrf', 'cvv', 'cvc', 'card_number', 'iban');
    /**
     * Key fragments masked in the order meta only, where a session is stored as one opaque blob.
     */
    const META_KEYS_MASKED = array('session');
    /**
     * The slug for the log files to search in.
     *
     * @var string|null
     */
    private $log_context = null;
    /**
     * The metadata key from the order to search for the value in the log files.
     *
     * @var string|null
     */
    private $log_metadata = null;
    /**
     * Reference to the AJAX class.
     *
     * @var AJAX
     */
    private $ajax;
    /**
     * Class constructor.
     *
     * @param string $log_context The log file slug.
     * @param string $log_metadata The metadata key for the log file to search for.
     *
     * @return void
     */
    public function __construct($log_context = null, $log_metadata = null)
    {
        $this->log_context = $log_context;
        $this->log_metadata = $log_metadata;
        $this->ajax = new AJAX($this);
    }
    /**
     * Add a button for exporting an order from the admin order page.
     *
     * @param \WC_Order $order The order object.
     * @param bool      $print Whether to print the button or return it as a string.
     *
     * @return string|void
     */
    public function add_export_order_button($order, $print = \true)
    {
        // Enqueue the scripts for the admin order page.
        Assets::enqueue_admin_order_scripts($order, $this->get_ajax_action());
        if (!$print) {
            \ob_start();
        }
        ?>
		<button id="krokedil-support-export-order" class="button button-secondary" type="button" style="margin-top: 10px; margin-bottom: 10px;">
			<?php 
        esc_html_e('Export order for support', 'krokedil-support');
        ?>
		</button>
		<?php 
        if (!$print) {
            return \ob_get_clean();
        }
    }
    /**
     * Export the order.
     *
     * @param \WC_Order[]|\WC_Order $orders The order IDs to export.
     *
     * @return array
     */
    public function export_orders($orders)
    {
        // If we are passed a single order, convert it to a single element array to make it easier to handle the different cases.
        if (!\is_array($orders)) {
            $orders = array($orders);
        }
        // Filter out any orders that are not instances of WC_Order.
        $orders = \array_filter($orders, function ($order) {
            return $order instanceof \WC_Order;
        });
        // Get the log lines and order data for the orders.
        if (empty($orders)) {
            return array();
        }
        $log_data = LogParser::get_log_data($orders, $this->log_context, $this->log_metadata);
        $order_data = $this->get_order_data($orders);
        /*
         *  Combine the log and order data into a single array like this:
         * [
         *     '[order_id]' => [
         *         'data' => [order data],
         *         'log'  => [log data],
         *     ],
         * ]
         */
        $result = array('orders' => array(), 'system_report' => SystemReport::get_report());
        foreach ($orders as $i => $order) {
            $order_id = $order->get_id();
            $result['orders'][$order_id] = array('wc_order' => $order_data[$order_id] ?? array(), 'integration_order' => $this->mask_integration_order_data($this->get_integration_order_data($order)), 'logs' => $this->mask_log_lines($log_data[$order_id] ?? array()));
        }
        return $result;
    }
    /**
     * Get order data.
     *
     * @param \WC_Order[] $orders The order object.
     * @return array
     */
    public function get_order_data($orders)
    {
        $order_data = array();
        foreach ($orders as $order) {
            $data = $order->get_data();
            // Empty the line_items, shipping_lines, fee_lines, coupon_lines and tax_lines arrays.
            $data['line_items'] = array();
            $data['shipping_lines'] = array();
            $data['fee_lines'] = array();
            $data['coupon_lines'] = array();
            // Get the formatted line, shipping, fee, coupon and tax items too.
            foreach ($order->get_items() as $item_id => $item) {
                $data['line_items'][$item_id] = $item->get_data();
            }
            foreach ($order->get_items('shipping') as $item_id => $item) {
                $data['shipping_lines'][$item_id] = $item->get_data();
            }
            foreach ($order->get_items('fee') as $item_id => $item) {
                $data['fee_lines'][$item_id] = $item->get_data();
            }
            foreach ($order->get_items('coupon') as $item_id => $item) {
                $data['coupon_lines'][$item_id] = $item->get_data();
            }
            foreach ($order->get_items('tax') as $item_id => $item) {
                $data['tax_lines'][$item_id] = $item->get_data();
            }
            // Get all order comments for the order as well.
            $data['order_comments'] = wc_get_order_notes(array('order_id' => $order->get_id()));
            $order_data[$order->get_id()] = $this->is_masking_enabled() ? $this->mask_order_data($data) : $data;
        }
        return $order_data;
    }
    /**
     * Whether export masking is enabled via the shared krokedil/wp-api log masking filter.
     *
     * @return bool
     */
    protected function is_masking_enabled()
    {
        try {
            /** This filter is documented in krokedil/wp-api src/Masking.php */
            return \false !== apply_filters('krokedil_wp_api_mask_log_data', \true, (string) $this->log_context);
        } catch (\Throwable $e) {
            return \true;
        }
    }
    /**
     * Mask the customer's personal data and anything granting access to the order.
     *
     * @param array $data The order data.
     *
     * @return array
     */
    protected function mask_order_data($data)
    {
        // Collect the customer's own values before they are masked, to find them in the order notes.
        $personal_values = $this->get_personal_values($data);
        foreach (array('billing', 'shipping') as $address_type) {
            foreach ($data[$address_type] ?? array() as $field => $value) {
                if (!\in_array($field, self::ADDRESS_KEPT, \true)) {
                    $data[$address_type][$field] = self::placeholder($value);
                }
            }
        }
        foreach (self::ORDER_FIELDS_MASKED as $field) {
            if (\array_key_exists($field, $data)) {
                $data[$field] = self::placeholder($data[$field]);
            }
        }
        $data['meta_data'] = $this->mask_meta_data($data['meta_data'] ?? array());
        foreach (array('line_items', 'shipping_lines', 'fee_lines', 'coupon_lines', 'tax_lines') as $item_type) {
            foreach ($data[$item_type] ?? array() as $item_id => $item) {
                $data[$item_type][$item_id]['meta_data'] = $this->mask_meta_data($item['meta_data'] ?? array());
            }
        }
        $data['order_comments'] = $this->mask_order_notes($data['order_comments'] ?? array(), $personal_values);
        return $data;
    }
    /**
     * Get the customer's personal values from the order data, longest first; values shorter than three characters are omitted because they would match too much.
     *
     * @param array $data The unmasked order data.
     *
     * @return string[]
     */
    private function get_personal_values($data)
    {
        $values = array();
        foreach (array('billing', 'shipping') as $address_type) {
            foreach ($data[$address_type] ?? array() as $field => $value) {
                if (!\in_array($field, self::ADDRESS_KEPT, \true)) {
                    $values[] = $value;
                }
            }
        }
        $values[] = $data['customer_ip_address'] ?? '';
        $values = \array_filter(\array_unique(\array_map('trim', \array_filter($values, 'is_string'))), function ($value) {
            return \strlen($value) >= 3;
        });
        \usort($values, function ($a, $b) {
            return \strlen($b) - \strlen($a);
        });
        return $values;
    }
    /**
     * Mask the customer's own personal values wherever they appear in the order notes. The notes
     * are kept, since what happened to the order is what support needs most.
     *
     * @param object[] $notes           The order notes.
     * @param string[] $personal_values The customer's personal values.
     *
     * @return object[]
     */
    private function mask_order_notes($notes, $personal_values)
    {
        if (empty($personal_values)) {
            return $notes;
        }
        $patterns = \array_map(function ($value) {
            // Match whole values only, so a name is not masked inside another word.
            return '/(?<![\\p{L}\\p{N}])' . \preg_quote($value, '/') . '(?![\\p{L}\\p{N}])/iu';
        }, $personal_values);
        $masked = array();
        foreach ($notes as $note) {
            $note = clone $note;
            if (isset($note->content) && \is_string($note->content)) {
                $content = \preg_replace($patterns, self::REDACTED, $note->content);
                $note->content = null === $content ? self::REDACTED : $content;
            }
            $masked[] = $note;
        }
        return $masked;
    }
    /**
     * Mask the integration's order data by key name, since it often repeats the customer's details.
     *
     * @param array $data The integration order data.
     *
     * @return array
     */
    protected function mask_integration_order_data($data)
    {
        if (!\is_array($data) || !$this->is_masking_enabled()) {
            return $data;
        }
        return $this->mask_by_key($data, self::KEYS_MASKED);
    }
    /**
     * Mask the values of the meta data whose keys look sensitive, keeping every key readable.
     *
     * @param \WC_Meta_Data[]|array $meta_data The meta data.
     *
     * @return array
     */
    protected function mask_meta_data($meta_data)
    {
        $masked_keys = \array_merge(self::KEYS_MASKED, self::META_KEYS_MASKED);
        $masked = array();
        foreach ($meta_data as $meta) {
            $meta = $meta instanceof \WC_Meta_Data ? $meta->get_data() : (array) $meta;
            if (self::is_masked_key($meta['key'] ?? '', $masked_keys)) {
                $meta['value'] = self::placeholder($meta['value'] ?? null);
            } else {
                // A meta value can be an array or a JSON string with sensitive keys of its own.
                $meta['value'] = $this->mask_value($meta['value'] ?? null, $masked_keys);
            }
            $masked[] = $meta;
        }
        return $masked;
    }
    /**
     * Mask the log lines for an order. The JSON payload of a line is masked by key name, and
     * any WooCommerce order key is masked wherever it appears, such as in a confirmation URL.
     *
     * @param string[] $log_lines The raw log lines.
     *
     * @return string[]
     */
    protected function mask_log_lines($log_lines)
    {
        if (!$this->is_masking_enabled()) {
            return \array_map('stripslashes', $log_lines);
        }
        $masked_keys = self::KEYS_MASKED;
        $masked = array();
        foreach ($log_lines as $line) {
            $json_start = \strpos($line, '{');
            $payload = \false !== $json_start ? \json_decode(\substr($line, $json_start), \true) : null;
            if (\is_array($payload)) {
                $line = \substr($line, 0, $json_start) . wp_json_encode($this->mask_by_key($payload, $masked_keys), \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE);
            } else {
                $line = \stripslashes($line);
            }
            $masked[] = \preg_replace('/wc_order_[A-Za-z0-9]+/', self::REDACTED, $line);
        }
        return $masked;
    }
    /**
     * Mask the values in an array whose keys look sensitive, at any depth.
     *
     * @param array    $data        The data to mask.
     * @param string[] $masked_keys The key fragments to mask.
     *
     * @return array
     */
    private function mask_by_key($data, $masked_keys)
    {
        foreach ($data as $key => $value) {
            if (\is_string($key) && self::is_masked_key($key, $masked_keys)) {
                $data[$key] = self::placeholder($value);
            } else {
                $data[$key] = $this->mask_value($value, $masked_keys);
            }
        }
        return $data;
    }
    /**
     * Mask the sensitive keys inside a value, when it is an array or a JSON encoded string.
     *
     * @param mixed    $value       The value to mask.
     * @param string[] $masked_keys The key fragments to mask.
     *
     * @return mixed
     */
    private function mask_value($value, $masked_keys)
    {
        if (\is_array($value)) {
            return $this->mask_by_key($value, $masked_keys);
        }
        // A request body or a meta value is often stored as a JSON encoded string.
        if (\is_string($value) && \in_array(\substr(\ltrim($value), 0, 1), array('{', '['), \true)) {
            $decoded = \json_decode($value, \true);
            if (\is_array($decoded)) {
                return wp_json_encode($this->mask_by_key($decoded, $masked_keys), \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE);
            }
        }
        return $value;
    }
    /**
     * Whether a key contains one of the masked key fragments, ignoring case and reading '-' as '_'.
     *
     * @param string   $key         The key to check.
     * @param string[] $masked_keys The key fragments to mask.
     *
     * @return bool
     */
    private static function is_masked_key($key, $masked_keys)
    {
        $key = \str_replace('-', '_', \strtolower((string) $key));
        foreach ((array) $masked_keys as $masked_key) {
            if ('' !== $masked_key && \false !== \strpos($key, \strtolower($masked_key))) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * The placeholder for a masked value, telling a value that was there from an empty one.
     *
     * @param mixed $value The value being masked.
     *
     * @return string
     */
    private static function placeholder($value)
    {
        return null === $value || '' === $value || array() === $value ? self::MISSING : self::REDACTED;
    }
    /**
     * Abstract method to get the integrations order data.
     *
     * @param \WC_Order $order The order object.
     *
     * @return array
     */
    public function get_integration_order_data($order)
    {
        return array();
    }
    /**
     * Get the AJAX action name for exporting an order, unique to the log context.
     *
     * @return string
     */
    public function get_ajax_action()
    {
        $action = 'krokedil_support_export_order';
        if (!empty($this->log_context)) {
            $action .= '_' . sanitize_key($this->log_context);
        }
        return $action;
    }
    /**
     * Get the AJAX class.
     *
     * @return AJAX
     */
    public function ajax()
    {
        return $this->ajax;
    }
}
