<?php

namespace KrokedilKlarnaPaymentsDeps\Krokedil\WooCommerce\Interfaces;

\defined('ABSPATH') || exit;
interface MetaboxInterface
{
    /**
     * Add metabox to order edit screen.
     *
     * @param string $post_type The post type for the current screen.
     *
     * @return void
     */
    public function add_metabox($post_type);
    /**
     * Render the metabox.
     *
     * @param \WP_Post|\WC_Order $post The post object.
     *
     * @return void
     */
    public function render_metabox($post);
    /**
     * Get the ID for the current screen.
     *
     * @return int
     */
    public function get_id();
}
