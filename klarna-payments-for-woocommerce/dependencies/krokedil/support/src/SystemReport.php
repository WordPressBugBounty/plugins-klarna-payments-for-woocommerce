<?php

namespace KrokedilKlarnaPaymentsDeps\Krokedil\Support;

use Automattic\WooCommerce\Utilities\LoggingUtil;
if (!\defined('ABSPATH')) {
    exit;
}
/**
 * Class SystemReport
 *
 * @package Krokedil\Support
 */
class SystemReport
{
    /**
     * Plugin ID.
     *
     * @var string
     */
    private $id;
    /**
     * The plugin name (or title).
     *
     * @var string
     */
    private $name;
    /**
     * The included settings for the system report.
     *
     * @var array
     */
    private $included_settings = array();
    /**
     * SystemReport constructor.
     *
     * @param string $id The plugin ID.
     * @param string $name The plugin name (or title).
     * @param array  $settings The options for determining what to include in the system report.
     */
    public function __construct($id, $name, $settings)
    {
        $this->id = $id;
        $this->name = $name;
        $this->included_settings = $settings;
        add_action('woocommerce_system_status_report', array($this, 'add_status_page_box'));
        add_action('woocommerce_cleanup_logs', array($this, 'remove_old_entries'));
    }
    /**
     * Get the current settings for the gateway.
     *
     * @return array|false An associative title:value array of the current settings. False if plugin settings is cannot be retrieved.
     */
    private function get_current_settings()
    {
        $payment_gateways = WC()->payment_gateways()->payment_gateways();
        $gateway = $payment_gateways[$this->id] ?? null;
        $form_fields = $gateway ? $gateway->get_form_fields() : array();
        if (empty($form_fields)) {
            return \false;
        }
        $output = array();
        $settings = get_option('woocommerce_' . $this->id . '_settings', array());
        foreach ($settings as $setting_key => $value) {
            $form_field = $form_fields[$setting_key] ?? array();
            // Check if the form field is valid for the system report output.
            if (!$this->is_form_field_valid($form_field, $setting_key)) {
                continue;
            }
            $form_field = $this->process_modifiers($form_field);
            if (empty($value)) {
                $value = $form_field['default'] ?? $value;
            }
            $output[$setting_key] = array('title' => \rtrim($form_field['title'], ':'), 'value' => $value, 'type' => $form_field['type']);
        }
        return $output;
    }
    /**
     * Check if a form field is valid for the system report output.
     *
     * @param array  $form_field The form field to check.
     * @param string $setting_key The setting key to check against.
     *
     * @return bool True if the form field is valid, false otherwise.
     */
    private function is_form_field_valid($form_field, $setting_key)
    {
        // If the form field is empty, return false.
        if (empty($form_field)) {
            return \false;
        }
        return $this->is_form_field_included($form_field, $setting_key);
    }
    /**
     * Check if the form field is valid in the included settings.
     *
     * @param array  $form_field The form field to check.
     * @param string $setting_key The setting key to check against.
     *
     * @return bool True if the form field is valid, false otherwise.
     */
    private function is_form_field_included($form_field, $setting_key)
    {
        $form_field['id'] = $setting_key;
        $setting = $this->is_match($form_field);
        if ($setting) {
            return !$this->maybe_exclude($form_field, $setting);
        }
        // Default to false if no match is found.
        return \false;
    }
    /**
     * Check if the setting should be skipped or not based on the existence of the 'exclude' property, and its values.
     *
     * @param array $form_field The form field to check.
     * @param array $setting The options setting.
     *
     * @return bool True if the setting should be skipped, false otherwise.
     */
    private function maybe_exclude($form_field, $setting)
    {
        if (!isset($setting['exclude']) || empty($setting['exclude'])) {
            return \false;
        }
        foreach ($setting['exclude'] as $key => $value) {
            // These are special keywords: empty and isset.
            // If the form field is empty, the field will be excluded.
            if ('empty' === $key && (empty($form_field[$value]) || !isset($form_field[$value]))) {
                return \true;
            }
            // If the form field is non-empty, the field will be excluded.
            if ('isset' === $key && isset($form_field[$value])) {
                return \true;
            }
            // The remaining keys are treated as properties in the form field, and whose value to match against.
            if (isset($form_field[$key]) && $form_field[$key] === $value) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * Process the form field for any modifiers, and modifies it accordingly.
     *
     * @param array $form_field The form field to process.
     *
     * @return array The processed form field.
     */
    private function process_modifiers($form_field)
    {
        $setting = $this->is_match($form_field);
        if (!empty($setting)) {
            if (isset($setting['is_section'])) {
                $form_field['type'] = 'section';
            }
        }
        return $form_field;
    }
    /**
     * Check if the form field matches any of the included settings.
     *
     * @param array $form_field The form field to check.
     *
     * @return array|false The setting that matched, false otherwise.
     */
    private function is_match($form_field)
    {
        foreach ($this->included_settings as $setting) {
            if (isset($setting['type']) && $setting['type'] === $form_field['type']) {
                return $setting;
            }
            if (isset($setting['id']) && $setting['id'] === $form_field['id']) {
                return $setting;
            }
            if (isset($setting['class']) && $setting['class'] === $form_field['class']) {
                return $setting;
            }
        }
        return \false;
    }
    /**
     * Displays the log entries on the System Report page.
     *
     * @return void
     */
    public function add_status_page_box()
    {
        $settings = $this->get_current_settings();
        $id = $this->id;
        $name = $this->name;
        include_once __DIR__ . '/Views/Admin/status-report.php';
    }
    /**
     * Add a log entry to the system report.
     *
     * @param array|object|\WP_Error $response The API request that you want to report about.
     * @param mixed                  $extra    Any extra information you want to include in the report.
     *
     * @return array|object|\WP_Error
     */
    public function request($response, $extra = null)
    {
        if (!is_wp_error($response)) {
            return $response;
        }
        $logs = \json_decode(get_option('krokedil_support_' . $this->id, '[]'), \true);
        $logs[] = array('timestamp' => current_time('mysql'), 'response' => array('code' => $response->get_error_code(), 'message' => $response->get_error_message(), 'extra' => $extra));
        update_option('krokedil_support_' . $this->id, wp_json_encode($logs));
        return $response;
    }
    /**
     * Remove old report entries.
     *
     * @hook woocommerce_cleanup_logs
     */
    public function remove_old_entries()
    {
        $retention_period = LoggingUtil::get_retention_period();
        $reports = \json_decode(get_option('krokedil_support_' . $this->id, '[]'), \true);
        foreach ($reports as $key => $report) {
            if (\strtotime($report['timestamp']) < \strtotime("-{$retention_period} days")) {
                unset($reports[$key]);
            }
        }
        update_option('krokedil_support_' . $this->id, wp_json_encode($reports));
    }
}
