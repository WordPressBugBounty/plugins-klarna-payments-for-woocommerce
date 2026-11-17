<?php

namespace KrokedilKlarnaPaymentsDeps\Krokedil\Support\Api\Controllers;

use KrokedilKlarnaPaymentsDeps\Krokedil\Support\Interfaces\APIControllerInterface;
/**
 * Abstract base controller for the API.
 *
 * @package Krokedil/Support/Api/Controllers
 */
abstract class BaseController implements APIControllerInterface
{
    /**
     * Namespace for the controller.
     *
     * @var string
     */
    protected $namespace = 'wc-krokedil';
    /**
     * The version for the controller.
     *
     * @var string
     */
    protected $version = 'v1';
    /**
     * The path for the controller
     *
     * @var string
     */
    protected $path;
    /**
     * Get the base path for the controller.
     *
     * @return string
     */
    protected function get_base_path()
    {
        // Combine the version and path to create the base path, ensuring that the path doesn't start or end with a slash.
        return \trim("{$this->version}/{$this->path}", '/');
    }
    /**
     * Get the request path for a specific endpoint.
     *
     * @param string $endpoint The endpoint to get the path for.
     *
     * @return string
     */
    protected function get_request_path($endpoint)
    {
        $base_path = $this->get_base_path();
        return \trim("{$base_path}/{$endpoint}", '/');
    }
    /**
     * Verify the request is valid.
     *
     * @param \WP_REST_Request $request Request object.
     *
     * @return bool
     */
    public function verify_request($request)
    {
        return \true;
    }
}
