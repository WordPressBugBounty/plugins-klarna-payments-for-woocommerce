<?php

namespace KrokedilKlarnaPaymentsDeps\Krokedil\SettingsPage\Traits;

trait Layout
{
    use Sidebar;
    use Subsection;
    /**
     * The gateway object.
     *
     * @var \WC_Payment_Gateway|null $gateway
     */
    protected $gateway;
    /**
     * The icon for the page.
     *
     * @var string $icon
     */
    protected $icon;
    /**
     * Plugin name.
     *
     * @var string|null $plugin_name
     */
    protected $plugin_name = null;
    /**
     * Set the icon url.
     *
     * @param string $icon The icon url.
     *
     * @return void
     */
    public function set_icon($icon)
    {
        $this->icon = $icon;
    }
    /**
     * Set the plugin name.
     *
     * @param string $plugin_name The plugin name.
     */
    public function set_plugin_name($plugin_name)
    {
        $this->plugin_name = $plugin_name;
    }
    /**
     * Print the header for the page.
     *
     * @return void
     */
    public function output_header()
    {
        if (empty($this->gateway)) {
            return;
        }
        ?>
		<div class="krokedil_settings__header">
			<?php 
        if (!empty($this->icon)) {
            ?>
				<img height="64px" class="kp_settings__header_logo" src="<?php 
            echo esc_attr($this->icon);
            ?>" alt="<?php 
            echo esc_html($this->gateway->get_method_title());
            ?>" />
			<?php 
        }
        ?>
			<div class="krokedil_settings__header_text">
				<h2 class="krokedil_settings__header_title">
					<?php 
        echo esc_html($this->gateway->get_method_title());
        ?>
					<?php 
        wc_back_link(__('Return to payments', 'woocommerce'), admin_url('admin.php?page=wc-settings&tab=checkout'));
        //phpcs:ignore
        ?>
				</h2>
				<p class="krokedil_settings__header_description"><?php 
        echo esc_html($this->gateway->get_method_description());
        ?></p>
			</div>
		</div>
		<?php 
    }
    /**
     * Output the layout.
     *
     * @return void
     */
    public function output()
    {
        wp_enqueue_style('krokedil-settings-page');
        ?>
		<?php 
        if (\method_exists($this, 'output_description')) {
            ?>
			<?php 
            $this->output_description();
            ?>
		<?php 
        }
        ?>
		<div class="krokedil_settings__wrapper">
			<?php 
        $this->output_subsection();
        ?>
			<?php 
        $this->output_sidebar();
        ?>
		</div>
		<?php 
    }
}
