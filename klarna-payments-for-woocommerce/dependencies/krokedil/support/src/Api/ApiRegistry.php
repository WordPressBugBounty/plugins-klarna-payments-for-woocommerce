<?php

namespace KrokedilKlarnaPaymentsDeps\Krokedil\Support\Api;

use KrokedilKlarnaPaymentsDeps\Krokedil\Support\Interfaces\APIControllerInterface;
/**
 * Class for the API Registry for the support package.
 *
 * @package Krokedil/Support/Api
 */
class ApiRegistry
{
    /**
     * The list of controllers.
     *
     * @var APIControllerInterface[]
     */
    protected $controllers = array();
    /**
     * Class constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->init();
        add_action('rest_api_init', array($this, 'register_controllers'));
    }
    /**
     * Initialize the registry.
     *
     * @return void
     */
    public function init()
    {
        $this->add_controller(new Controllers\OrderSupportController());
    }
    /**
     * Add a controller to the registry.
     *
     * @param APIControllerInterface $controller The controller to add.
     *
     * @return void
     */
    public function add_controller(APIControllerInterface $controller)
    {
        $this->controllers[] = $controller;
    }
    /**
     * Register the controllers.
     *
     * @return void
     */
    public function register_controllers()
    {
        foreach ($this->controllers as $controller) {
            $controller->register_routes();
        }
    }
}
