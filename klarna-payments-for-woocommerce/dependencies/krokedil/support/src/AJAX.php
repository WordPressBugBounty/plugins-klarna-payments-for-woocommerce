<?php

namespace KrokedilKlarnaPaymentsDeps\Krokedil\Support;

/**
 * Support AJAX class file.
 *
 * @package Krokedil/Support
 */
if (!\defined('ABSPATH')) {
    exit;
}
/**
 * Support AJAX class
 */
class AJAX
{
    /**
     * The OrderSupport instance that created this class.
     *
     * @var OrderSupport
     */
    private $order_support;
    /**
     * AJAX constructor.
     *
     * @param OrderSupport $order_support The OrderSupport instance to export orders with.
     */
    public function __construct($order_support)
    {
        $this->order_support = $order_support;
        $this->add_ajax_events();
    }
    /**
     * Hook in methods - uses WordPress ajax handlers (admin-ajax).
     */
    public function add_ajax_events()
    {
        $action = $this->order_support->get_ajax_action();
        add_action('wc_ajax_' . $action, array($this, 'krokedil_support_export_order'));
        add_action('wp_ajax_woocommerce_' . $action, array($this, 'krokedil_support_export_order'));
    }
    /**
     * Export an order for support.
     *
     * @return void
     */
    public function krokedil_support_export_order()
    {
        check_ajax_referer($this->order_support->get_ajax_action(), 'nonce');
        $order_id = \filter_input(\INPUT_POST, 'order_id', \FILTER_SANITIZE_NUMBER_INT);
        if (!current_user_can('edit_shop_orders') || !$order_id) {
            wp_send_json_error(__('Invalid order ID.', 'krokedil-support'));
        }
        $order = wc_get_order($order_id);
        if (!$order) {
            wp_send_json_error(__('Order not found.', 'krokedil-support'));
        }
        $exported_order = $this->order_support->export_orders($order);
        wp_send_json_success($exported_order);
    }
}
