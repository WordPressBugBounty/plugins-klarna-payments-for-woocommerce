<?php

namespace KrokedilKlarnaPaymentsDeps\Krokedil\WooCommerce;

\defined('ABSPATH') || exit;
use KrokedilKlarnaPaymentsDeps\Krokedil\WooCommerce\Interfaces\MetaboxInterface;
use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;
/**
 * Class to handle metabox functionality for WooCommerce order pages.
 *
 * @package Krokedil\WooCommerce
 */
abstract class OrderMetabox implements MetaboxInterface
{
    /**
     * Metabox id.
     *
     * @var string
     */
    protected $id;
    /**
     * Metabox title.
     *
     * @var string
     */
    protected $title;
    /**
     * Payment method ID.
     *
     * @var string
     */
    protected $payment_method_id;
    /**
     * Script handles to enqueue.
     *
     * @var array
     */
    protected $scripts = array();
    /**
     * Style handles to enqueue.
     *
     * @var array
     */
    protected $styles = array();
    /**
     * Constructor
     *
     * @param string $id Metabox id.
     * @param string $title Metabox title.
     * @param string $payment_method_id Payment method ID.
     *
     * @return void
     */
    public function __construct($id, $title, $payment_method_id)
    {
        $this->id = $id;
        $this->title = $title;
        $this->payment_method_id = $payment_method_id;
        add_action('add_meta_boxes', array($this, 'add_metabox'));
        add_action('admin_init', array($this, 'register_assets'));
    }
    /**
     * Render the metabox.
     *
     * @param \WP_Post|\WC_Order $post The post object or a WC Order for later versions of WooCommerce.
     *
     * @return void
     */
    public function render_metabox($post)
    {
        ?>
		<div class="krokedil_wc__metabox">
			<?php 
        $this->metabox_content($post);
        ?>
		</div>
		<?php 
    }
    /**
     * Output the metabox content.
     *
     * @param \WP_Post|\WC_Order $post The post object or a WC Order for later versions of WooCommerce.
     *
     * @return void
     */
    public abstract function metabox_content($post);
    /**
     * Add metabox to order edit screen.
     *
     * @param string $post_type The post type for the current screen.
     *
     * @return void
     */
    public function add_metabox($post_type)
    {
        if (!$this->is_edit_order_screen($post_type)) {
            return;
        }
        // Ensure we are on a order page.
        $order_id = $this->get_id();
        $order = $order_id ? wc_get_order($order_id) : \false;
        if (!$order_id || !$order) {
            return;
        }
        // Ensure the order has the correct payment method id.
        $payment_method = $order->get_payment_method();
        if (!empty($this->payment_method_id) && $this->payment_method_id !== $payment_method) {
            return;
        }
        $this->enqueue_assets();
        add_meta_box($this->id, $this->title, array($this, 'render_metabox'), $post_type, 'side', 'core');
    }
    /**
     * Enqueue the metabox assets.
     *
     * @return void
     */
    protected function enqueue_assets()
    {
        // Enqueue the default.
        wp_enqueue_style('krokedil_wc_metabox');
        wp_enqueue_script('krokedil_wc_metabox');
        // Enqueue any custom scripts.
        foreach ($this->scripts as $script) {
            $this->maybe_localize_script($script);
            wp_enqueue_script($script);
        }
        // Enqueue any custom styles.
        foreach ($this->styles as $style) {
            wp_enqueue_style($style);
        }
    }
    /**
     * Maybe localize the script with data.
     *
     * @param string $handle The script handle.
     *
     * @return void
     */
    protected function maybe_localize_script($handle)
    {
        // No default implementation.
    }
    /**
     * Register the metabox assets.
     *
     * @return void
     */
    public function register_assets()
    {
        wp_register_style('krokedil_wc_metabox', self::get_asset_url('css/metabox.css'), array(), '1.0.0');
        wp_register_script('krokedil_wc_metabox', self::get_asset_url('js/metabox.js'), array('jquery'), '1.0.0', \true);
    }
    /**
     * Is HPOS enabled.
     *
     * @return bool
     */
    public function is_hpos_enabled()
    {
        if (\class_exists(CustomOrdersTableController::class)) {
            return wc_get_container()->get(CustomOrdersTableController::class)->custom_orders_table_usage_is_enabled();
        }
        return \false;
    }
    /**
     * Check if the current screen is the edit order screen.
     *
     * @param string $post_type The post type to check.
     *
     * @return bool
     */
    public function is_edit_order_screen($post_type)
    {
        $valid_screens = array('shop_order', 'woocommerce_page_wc-orders');
        return \in_array($post_type, $valid_screens, \true);
    }
    /**
     * Get the order ID from the current screen.
     *
     * @return int|null
     */
    public function get_id()
    {
        $hpos_enabled = $this->is_hpos_enabled();
        $order_id = $hpos_enabled ? \filter_input(\INPUT_GET, 'id', \FILTER_SANITIZE_NUMBER_INT) : get_the_ID();
        if (empty($order_id)) {
            return \false;
        }
        return $order_id;
    }
    /**
     * Print a error message into the metabox.
     *
     * @param string $message Error message to output.
     *
     * @return void
     */
    protected static function output_error($message)
    {
        ?>
		<p class="krokedil_wc__metabox krokedil_metabox__error">
			<?php 
        echo esc_html($message);
        ?>
		</p>
		<?php 
    }
    /**
     * Output labeled text info for the metabox.
     *
     * @param string $label Label for the text.
     * @param string $text Text to output.
     * @param string $tip Tip to show.
     *
     * @return void
     */
    protected static function output_info($label, $text, $tip = null)
    {
        ?>
		<h4>
			<?php 
        echo esc_html($label);
        ?>
			<?php 
        if ($tip) {
            ?>
				<?php 
            echo wp_kses_post(wc_help_tip($tip));
            ?>
			<?php 
        }
        ?>
		</h4>
		<span><?php 
        echo wp_kses_post($text);
        ?></span>
		<?php 
    }
    /**
     * Output an action button.
     *
     * @param string $text The text to display on the button.
     * @param string $url The URL to link to.
     * @param bool   $new_tab Whether to open the link in a new tab.
     * @param string $classes The class to add to the button.
     *
     * @return void
     */
    protected static function output_action_button($text, $url, $new_tab = \false, $classes = '')
    {
        $target = $new_tab ? 'target="_blank"' : '';
        $classes = \trim("button {$classes}");
        ?>
		<a href="<?php 
        echo esc_url($url);
        ?>" class="krokedil_wc__metabox_button krokedil_wc__metabox_action <?php 
        echo esc_attr($classes);
        ?>" <?php 
        echo esc_url($target);
        ?>>
			<?php 
        echo esc_html($text);
        ?>
		</a>
		<?php 
    }
    /**
     * Output a button.
     *
     * @param string $text The text to display on the button.
     * @param string $classes The class to add to the button.
     * @param array  $data The data attributes to add to the button.
     *
     * @return void
     */
    protected static function output_button($text, $classes = '', $data = array())
    {
        $classes = \trim("button {$classes}");
        $data_attributes = '';
        foreach ($data as $key => $value) {
            $data_attributes .= " data-{$key}={$value}";
        }
        ?>
		<button
			type="button"
			class="krokedil_wc__metabox_button <?php 
        echo esc_attr($classes);
        ?>"
			<?php 
        echo esc_attr($data_attributes);
        ?>
		>
			<?php 
        echo esc_html($text);
        ?>
		</button>
		<?php 
    }
    /**
     * Output a toggle switch.
     *
     * @param string $label The label for the input.
     * @param bool   $checked Whether the switch should be checked.
     * @param string $tip The tip to show.
     * @param string $classes The classes to add to the input.
     * @param array  $data The data attributes to add to the input.
     */
    protected static function output_toggle_switch($label, $checked = \false, $tip = null, $classes = '', $data = array())
    {
        $data_attributes = '';
        foreach ($data as $key => $value) {
            $data_attributes .= " data-{$key}={$value}";
        }
        $toggle_suffix = $checked ? 'enabled' : 'disabled';
        $toggle_class = "woocommerce-input-toggle--{$toggle_suffix}";
        ?>
		<div class="krokedil_wc__metabox_toggle_wrapper">
			<h4>
				<?php 
        echo esc_html($label);
        ?>
				<?php 
        if ($tip) {
            ?>
					<?php 
            echo wp_kses_post(wc_help_tip($tip));
            ?>
				<?php 
        }
        ?>
			</h4>
			<span
				class="woocommerce-input-toggle <?php 
        echo esc_attr($toggle_class);
        ?> <?php 
        echo esc_attr($classes);
        ?>"
				<?php 
        echo esc_attr($data_attributes);
        ?>
			></span>
		</div>
		<?php 
    }
    /**
     * Output a collapsable advanced section.
     *
     * @param string          $id The id for the section.
     * @param string          $label The label for the section.
     * @param string|callable $content The content to display in the section.
     * @param bool            $is_open Whether the section should be open by default.
     *
     * @return void
     */
    protected static function output_collapsable_section($id, $label, $content, $is_open = \false)
    {
        ?>
		<div class="krokedil_wc__metabox_section">
			<h4 class="krokedil_wc__metabox_label krokedil_wc__metabox_section_toggle">
				<?php 
        echo esc_html($label);
        ?>
				<span class="dashicons dashicons-arrow-down <?php 
        echo esc_attr($is_open ? 'krokedil_wc__metabox_open' : '');
        ?>"></span>
			</h4>
			<div id="<?php 
        echo esc_attr($id);
        ?>" class="krokedil_wc__metabox_section_content" style="<?php 
        echo esc_attr($is_open ? '' : 'display:none;');
        ?>">
				<?php 
        if (\is_callable($content)) {
            ?>
					<?php 
            \call_user_func($content);
            ?>
				<?php 
        } else {
            ?>
					<?php 
            echo wp_kses_post($content);
            ?>
				<?php 
        }
        ?>
			</div>
		</div>
		<?php 
    }
    /**
     * Get the url for assets in the package relative to the plugin root.
     *
     * @param string $path The path to the asset.
     *
     * @return string
     */
    protected static function get_asset_url($path)
    {
        return plugin_dir_url(__FILE__) . '../assets/' . \rtrim($path, '/');
    }
}
