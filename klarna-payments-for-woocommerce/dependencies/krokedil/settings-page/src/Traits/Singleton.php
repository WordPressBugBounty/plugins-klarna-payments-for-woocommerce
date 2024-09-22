<?php

namespace KrokedilKlarnaPaymentsDeps\Krokedil\SettingsPage\Traits;

trait Singleton
{
    /**
     * Instance of the class.
     *
     * @var self
     */
    protected static $instance;
    /**
     * Get the instance of the class.
     *
     * @return self
     */
    public static function get_instance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }
    /**
     * Prevent creating a new instance of the class.
     */
    protected function __construct()
    {
    }
    /**
     * Prevent cloning the instance of the class.
     */
    protected function __clone()
    {
    }
    /**
     * Prevent unserializing the instance of the class.
     */
    public function __wakeup()
    {
    }
}
