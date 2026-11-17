<?php

namespace KrokedilKlarnaPaymentsDeps\Krokedil\Support\Interfaces;

/**
 * Interface for the API controllers.
 *
 * @package Krokedil/Support/Interfaces
 */
interface APIControllerInterface
{
    /**
     * Register the routes for the objects of the controller.
     *
     * @return void
     */
    public function register_routes();
}
