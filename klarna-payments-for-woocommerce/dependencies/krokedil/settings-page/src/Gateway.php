<?php

namespace KrokedilKlarnaPaymentsDeps\Krokedil\SettingsPage;

use KrokedilKlarnaPaymentsDeps\Krokedil\SettingsPage\Traits\Layout;
\defined('ABSPATH') || exit;
/**
 * Class for extending a Gateways settings page.
 */
class Gateway
{
    use Layout;
    /**
     * Arguments for the page.
     *
     * @var array $args
     */
    protected $args;
    /**
     * Icon for the gateway.
     *
     * @var string $icon
     */
    protected $icon;
    /**
     * Class Constructor.
     *
     * @param \WC_Payment_Gateway $gateway The gateway object.
     * @param array               $args Arguments for the page.
     *
     * @return void
     */
    public function __construct($gateway, $args = array())
    {
        $this->gateway = $gateway;
        $this->args = $args;
        $this->icon = $args['icon'] ?? '';
        $this->sidebar = $args['sidebar'] ?? array();
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
        $this->output_header();
        ?>
		<?php 
        SettingsPage::get_instance()->navigation($this->gateway->id)->output();
        ?>
		<div class="krokedil_settings__gateway_page">
			<div class="krokedil_settings__wrapper">
				<?php 
        $this->output_subsection();
        ?>
				<?php 
        $this->output_sidebar();
        ?>
			</div>
		</div>
		<?php 
    }
    /**
     * Output the page HTML.
     *
     * @return void
     */
    public function output_page_content()
    {
        ?>
		<table class="form-table">
			<?php 
        echo $this->gateway->generate_settings_html($this->gateway->get_form_fields(), \false);
        //phpcs:ignore
        ?>
		</table>
		<?php 
    }
}
