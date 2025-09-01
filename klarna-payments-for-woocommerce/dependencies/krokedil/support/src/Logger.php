<?php

namespace KrokedilKlarnaPaymentsDeps\Krokedil\Support;

use WC_Log_Levels;
if (!\defined('ABSPATH')) {
    exit;
}
/**
 * Class Logger.
 */
class Logger
{
    /**
     * WC logger.
     *
     * @var \WC_Logger
     */
    private $logger;
    /**
     * Plugin ID.
     *
     * @var string
     */
    private $id;
    /**
     * Whether logging is enabled.
     *
     * @var bool
     */
    private $enabled = \false;
    /**
     * Logger constructor.
     *
     * @param string $id Plugin ID.
     */
    public function __construct($id, $enabled)
    {
        $this->logger = new \WC_Logger();
        $this->id = $id;
        $this->enabled = $enabled;
    }
    /**
     * Add settings fields.
     *
     * Extends the plugin's settings fields with troubleshooting settings.
     *
     * @param array $form_fields Form fields.
     *
     * @return array
     */
    public static function add_settings_fields($form_fields)
    {
        $settings = array('troubleshooting' => array('title' => __('Troubleshooting', 'krokedil-support'), 'type' => 'title'), 'logging' => array('title' => __('Logging', 'krokedil-support'), 'label' => 'Enable', 'type' => 'checkbox', 'description' => __('Logging is required for troubleshooting any issues related to the plugin. It is recommended that you always have it enabled.', 'krokedil-support'), 'default' => 'yes'), 'extended_logging' => array('title' => __('Detailed logging', 'krokedil-support'), 'label' => __('Enable', 'krokedil-support'), 'type' => 'checkbox', 'description' => __('Enable detailed logging to capture extra data. Use this only when needed for debugging hard-to-replicate issues, as it generates significantly more log entries.', 'krokedil-support'), 'default' => 'no'));
        return \array_merge($form_fields, $settings);
    }
    /**
     * Creates a log entry.
     *
     * @param string $message Log message.
     * @param string $level One of the following:
     *    - `emergency`: System is unusable.
     *    - `alert`: Action must be taken immediately.
     *    - `critical`: Critical conditions.
     *    - `error`: Error conditions.
     *    - `warning`: Warning conditions.
     *    - `notice`: Normal but significant condition.
     *    - `info`: Informational messages.
     *    - `debug`: Debug-level messages.
     * @param array  $args Additional context to log.
     */
    public function log($message, $level = WC_Log_Levels::INFO, ...$args)
    {
        if (!$this->enabled) {
            return;
        }
        $context = array('source' => $this->id);
        $context = !empty($args) ? \array_merge($context, \array_merge(...$args)) : $context;
        $this->logger->log($level, $message, $context);
    }
    /**
     * Log a debug message.
     *
     * @param string $message Debug message.
     * @param array  $args Additional context to log.
     */
    public function debug($message, ...$args)
    {
        $this->log($message, WC_Log_Levels::DEBUG, $args);
    }
    /**
     * Log an info message.
     *
     * @param string $message Info message.
     * @param array  $args Additional context to log.
     */
    public function info($message, ...$args)
    {
        $this->log($message, WC_Log_Levels::INFO, $args);
    }
    /**
     * Log a notice message.
     *
     * @param string $message Notice message.
     * @param array  $args Additional context to log.
     */
    public function notice($message, ...$args)
    {
        $this->log($message, WC_Log_Levels::NOTICE, $args);
    }
    /**
     * Log a warning message.
     *
     * @param string $message Warning message.
     * @param array  $args Additional context to log.
     */
    public function warning($message, ...$args)
    {
        $this->log($message, WC_Log_Levels::WARNING, $args);
    }
    /**
     * Log an error message.
     *
     * @param string $message Error message.
     * @param array  $args Additional context to log.
     */
    public function error($message, ...$args)
    {
        $this->log($message, WC_Log_Levels::ERROR, $args);
    }
    /**
     * Log a critical message.
     *
     * @param string $message Critical message.
     * @param array  $args Additional context to log.
     */
    public function critical($message, ...$args)
    {
        $this->log($message, WC_Log_Levels::CRITICAL, $args);
    }
    /**
     * Log an alert message.
     *
     * @param string $message Alert message.
     * @param array  $args Additional context to log.
     */
    public function alert($message, ...$args)
    {
        $this->log($message, WC_Log_Levels::ALERT, $args);
    }
    /**
     * Log an emergency message.
     *
     * @param string $message Emergency message.
     * @param array  $args Additional context to log.
     */
    public function emergency($message, ...$args)
    {
        $this->log($message, WC_Log_Levels::EMERGENCY, $args);
    }
}
