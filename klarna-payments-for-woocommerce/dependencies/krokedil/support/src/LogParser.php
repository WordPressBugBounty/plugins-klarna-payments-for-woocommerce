<?php

namespace KrokedilKlarnaPaymentsDeps\Krokedil\Support;

use Automattic\WooCommerce\Utilities\LoggingUtil;
/**
 * A class to get log entries for orders.
 *
 * @package Krokedil/Support
 */
class LogParser
{
    /**
     * Get any log rows for the order that might exist.
     *
     * @param \WC_Order[] $orders The order object.
     * @param string|null $log_context The log file slug.
     * @param string|null $log_metadata The metadata key for the log file to search for.
     *
     * @return array
     */
    public static function get_log_data($orders, $log_context, $log_metadata)
    {
        if (empty($log_metadata)) {
            return array();
        }
        // For each order, get the metadata value to check for.
        $search_values = self::get_search_values($orders, $log_metadata);
        if (empty($search_values)) {
            return array();
        }
        // See if there are any log files that exist for the order, using the same slug as set in the constructor.
        $log_file_paths = self::get_log_file_paths($log_context);
        $log_lines = array();
        // Loop each log file and search for any rows that contain the metadata value from the order.
        foreach ($log_file_paths as $log_file_path) {
            self::process_log_file($log_file_path, $search_values, $log_lines);
        }
        return $log_lines;
    }
    /**
     * Get the values to search for in the log files.
     *
     * @param \WC_Order[] $orders The order object.
     * @param string      $log_metadata The metadata key for the log file to search for.
     *
     * @return array
     */
    private static function get_search_values($orders, $log_metadata)
    {
        $search_values = array();
        foreach ($orders as $order) {
            $search_value = $order->get_meta($log_metadata);
            if (empty($search_value)) {
                continue;
            }
            // Add it to the search values along with the order id as the key.
            $search_values[$order->get_id()] = $search_value;
        }
        return $search_values;
    }
    /**
     * Get the log file paths to search through.
     *
     * @param string $log_file_slug The log file slug.
     *
     * @return array
     */
    private static function get_log_file_paths($log_file_slug = 'krokedil-support')
    {
        $log_file_paths = \WC_Log_Handler_File::get_log_files();
        // Filter any log files that does not contain the log file slug in its id.
        $log_file_paths = \array_filter($log_file_paths, function ($log_file) use($log_file_slug) {
            return \false !== \strpos($log_file, $log_file_slug ?? 'krokedil-support');
        });
        return $log_file_paths;
    }
    /**
     * Process a log and return the relevant log entries from it.
     *
     * @param string $log_file_path The path to the log file.
     * @param array  $search_values The values to search for in the log file.
     * @param array  $log_lines The log lines to return. This is passed by reference.
     *
     * @return void
     */
    private static function process_log_file($log_file_path, $search_values, &$log_lines)
    {
        $file_path = LoggingUtil::get_log_directory() . $log_file_path;
        // Open the log file as a stream.
        $handle = \fopen($file_path, 'r');
        // phpcs:ignore
        if (!$handle) {
            return;
        }
        // Find any line from the log and search for the metadata key from the order.
        while (($line = \fgets($handle)) !== \false) {
            // phpcs:ignore
            // Search for each of the metadata values from the orders, and save them with the order id as the key to the log line.
            foreach ($search_values as $order_id => $search_value) {
                if (\false !== \strpos($line, $search_value)) {
                    // Strip any newlines from the line and add it to the log lines array. Slashes are kept so the JSON in the line still decodes.
                    $log_lines[$order_id][] = \str_replace(array("\n", "\r"), '', $line);
                    continue;
                }
            }
        }
        \fclose($handle);
    }
}
