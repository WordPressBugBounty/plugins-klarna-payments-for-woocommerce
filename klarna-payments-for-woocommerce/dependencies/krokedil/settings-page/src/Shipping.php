<?php

namespace KrokedilKlarnaPaymentsDeps\Krokedil\SettingsPage;

\defined('ABSPATH') || exit;
/**
 * Class for extending a Shipping settings page.
 */
class Shipping extends WcSettingsPage
{
    /**
     * Shipping method instance.
     *
     * @var \WC_Shipping_Method
     */
    protected $shipping;
    /**
     * Class Constructor.
     *
     * @param \WC_Shipping_Method  $shipping The shipping method object.
     * @param array<string, mixed> $args Arguments for the page.
     *
     * @return void
     */
    public function __construct(\WC_Shipping_Method $shipping, array $args = array())
    {
        $this->shipping = $shipping;
        $this->id = $shipping->id;
        parent::__construct($this->id, $args, $shipping->get_form_fields());
        $this->page_title = $args['page_title'] ?? $shipping->get_method_title();
        $this->page_description = $args['page_description'] ?? $shipping->get_method_description();
        if (empty($args['back_link_label'])) {
            $this->back_link_label = __('Return to shipping', 'woocommerce');
        }
        if (empty($args['back_link_url'])) {
            $this->back_link_url = admin_url('admin.php?page=wc-settings&tab=shipping');
        }
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
        echo $this->shipping->generate_settings_html($this->shipping->get_form_fields(), \false);
        //phpcs:ignore
        ?>
		</table>
		<?php 
    }
}
