<?php

namespace KrokedilKlarnaPaymentsDeps\Krokedil\Support\Api\Controllers;

use KrokedilKlarnaPaymentsDeps\Krokedil\Support\OrderSupport;
/**
 * Class for the order support controller.
 *
 * @package Krokedil/Support/Api/Controllers
 */
class OrderSupportController extends BaseController
{
    /**
     * The path for the controller.
     *
     * @var string
     */
    protected $path = 'support';
    /**
     * Register the routes for the objects of the controller.
     *
     * @return void
     */
    public function register_routes()
    {
        register_rest_route($this->namespace, $this->get_request_path('order/(?P<id>[0-9]+)'), array('methods' => \WP_REST_Server::READABLE, 'callback' => array($this, 'get_response_object'), 'permission_callback' => array($this, 'verify_request'), 'args' => array('id' => array('description' => __('The ID of the order.', 'krokedil-support'), 'type' => 'integer', 'required' => \true), 'log_context' => array('description' => __('The context or slug of the log files to search for.', 'krokedil-support'), 'type' => 'string', 'required' => \false), 'log_metadata' => array('description' => __('The metadata key to use for searching in the log.', 'krokedil-support'), 'type' => 'string', 'required' => \false))));
    }
    /**
     * Get the response object.
     *
     * @param \WP_REST_Request $request The request object.
     *
     * @return \WP_REST_Response
     */
    public function get_response_object($request)
    {
        // Get the order ID from the request.
        $order_id = $request->get_param('id');
        // Get the query args for the log_context and log_metadata.
        $log_context = $request->get_param('log_context');
        $log_metadata = $request->get_param('log_metadata');
        // Using the order export class, generate the response json.
        $order_export = new OrderSupport($log_context, $log_metadata);
        // Get the order object.
        $order = wc_get_order($order_id);
        if (empty($order)) {
            return new \WP_REST_Response(array('error' => __('Order not found.', 'krokedil-support')), 404);
        }
        $data = $order_export->export_orders($order);
        // If we only have one entry in the order export, return that. Otherwise, return the whole array.
        $response = \count($data) === 1 ? \reset($data) : $data;
        return new \WP_REST_Response($response, 200, array('Content-Type' => 'application/json'));
    }
}
