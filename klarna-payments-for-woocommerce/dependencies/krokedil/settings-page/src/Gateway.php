<?php

namespace KrokedilKlarnaPaymentsDeps\Krokedil\SettingsPage;

\defined('ABSPATH') || exit;
/**
 * Class for extending a Gateways settings page.
 */
class Gateway extends WcSettingsPage
{
    /**
     * The gateway object.
     *
     * @var \WC_Payment_Gateway $gateway
     */
    protected $gateway;
    /**
     * Class Constructor.
     *
     * @param \WC_Payment_Gateway  $gateway The gateway object.
     * @param array<string, mixed> $args Arguments for the page.
     *
     * @return void
     */
    public function __construct(\WC_Payment_Gateway $gateway, array $args = array())
    {
        $this->gateway = $gateway;
        $this->id = $gateway->id;
        parent::__construct($this->id, $args, $gateway->get_form_fields());
        $this->page_title = $args['page_title'] ?? $gateway->get_method_title();
        $this->page_description = $args['page_description'] ?? $gateway->get_method_description();
    }
    /**
     * Output the page HTML.
     *
     * @return void
     */
    public function output_page_content() : void
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
