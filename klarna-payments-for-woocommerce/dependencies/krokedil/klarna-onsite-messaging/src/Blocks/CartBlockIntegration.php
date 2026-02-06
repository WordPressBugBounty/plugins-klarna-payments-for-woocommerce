<?php

namespace KrokedilKlarnaPaymentsDeps\Krokedil\KlarnaOnsiteMessaging\Blocks;

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;
use KrokedilKlarnaPaymentsDeps\Krokedil\KlarnaOnsiteMessaging\Utility;
use KrokedilKlarnaPaymentsDeps\Krokedil\KlarnaOnsiteMessaging\Settings;
/**
 * Integration for Klarna Onsite Messaging Cart Block.
 */
class CartBlockIntegration implements IntegrationInterface
{
    /**
     * Get the name of the integration.
     *
     * @return string
     */
    public function get_name()
    {
        return 'osm-cart-block-integration';
    }
    /**
     * Initialize the integration.
     *
     * @return void
     */
    public function initialize()
    {
        $script_path = plugin_dir_url(__DIR__) . 'assets/js/osm-cart-block-integration.js';
        wp_register_script('osm-cart-block-integration-script', $script_path, array('wp-blocks', 'wp-element', 'wp-editor'), \KOSM_VERSION, \true);
    }
    /**
     * Get the script handles to be enqueued for the integration.
     *
     * @return array
     */
    public function get_script_handles()
    {
        return array('osm-cart-block-integration-script');
    }
    /**
     * Get the editor script handles to be enqueued for the integration.
     *
     * @return array
     */
    public function get_editor_script_handles()
    {
        return array();
    }
    /**
     * Get the script data to be localized for the integration.
     *
     * @return array
     */
    public function get_script_data()
    {
        $key = $settings['placement_data_key_cart'] ?? 'credit-promotion-badge';
        $theme = $settings['onsite_messaging_theme_cart'] ?? '';
        $wc_cart_total = WC()->cart ? WC()->cart->get_total('edit') : 0;
        $purchase_amount = (int) \round(\floatval(\str_replace(array(',', '.'), '', $wc_cart_total)));
        $locale = Utility::get_locale_from_currency();
        return array('key' => $key, 'theme' => $theme, 'purchase_amount' => $purchase_amount, 'locale' => $locale);
    }
}
