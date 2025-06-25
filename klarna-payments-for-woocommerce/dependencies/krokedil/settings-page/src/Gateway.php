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
     * Whether to display page navigation as a sidebar for the settings sections of the current page.
     *
     * @var bool $settings_navigation
     */
    protected $settings_navigation;
    /**
     * Whether to style the output for the settings page.
     *
     * @var bool $styled_output
     */
    protected $styled_output;
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
        $this->icon = $args['icon'] ?? 'img.png';
        $this->sidebar = $args['sidebar'] ?? array();
        $this->settings_navigation = $args['settings_navigation'] ?? \false;
        $this->styled_output = $args['styled_output'] ?? \false;
        if ($this->styled_output) {
            add_filter('woocommerce_generate_krokedil_section_start_html', array(__CLASS__, 'krokedil_section_start'), 10, 3);
            add_filter('woocommerce_generate_krokedil_section_end_html', array(__CLASS__, 'krokedil_section_end'), 10, 3);
        }
    }
    /**
     * Output the layout.
     *
     * @return void
     */
    public function output()
    {
        wp_enqueue_style('krokedil-settings-page');
        wp_enqueue_script('krokedil-settings-page');
        ?>
		<?php 
        $this->output_header();
        ?>
		<?php 
        SettingsPage::get_instance()->navigation($this->gateway->id)->output();
        ?>
		<div class="krokedil_settings__gateway_page<?php 
        echo esc_attr($this->styled_output ? ' styled' : '');
        ?>">
			<div class="krokedil_settings__wrapper">
				<?php 
        $this->output_subsection($this->styled_output && $this->settings_navigation ? \true : \false);
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
    /**
     * Get the HTML as a string for a Klarna Payments section start.
     *
     * @param string $html The HTML to append the section start to.
     * @param string $key The key for the section.
     * @param array  $section The arguments for the section.
     *
     * @return string
     */
    public static function krokedil_section_start($html, $key, $section)
    {
        \ob_start();
        $always_open_sections = array('general', 'checkout_configuration', 'order_management');
        // This needs to be moved to the specific plugin.
        ?>
		</table>
		<div id="krokedil_section_<?php 
        echo esc_attr($key);
        ?>" class="krokedil_settings__section">
			<div class="krokedil_settings__section_header">
				<span class="krokedil_settings__section_toggle dashicons<?php 
        echo esc_attr(\in_array($key, $always_open_sections, \true) ? ' dashicons-arrow-up-alt2' : ' dashicons-arrow-down-alt2');
        ?>"></span>
				<h3 class="krokedil_settings__section_title">
					<?php 
        echo esc_html($section['title']);
        ?>
				</h3>
				<div class="krokedil_settings__section_description">
					<p><?php 
        echo esc_html($section['description'] ?? '');
        ?></p>
				</div>
			</div>

			<div class="krokedil_settings__section_content<?php 
        echo esc_attr(\in_array($key, $always_open_sections, \true) ? ' active' : '');
        ?>">
				<table class="form-table">
		<?php 
        return \ob_get_clean();
    }
    /**
     * Get the HTML as a string for a Klarna Payments section end.
     *
     * @param string $html The HTML to append the section end to.
     * @param string $key The key for the section end.
     * @param array  $section The arguments for the section.
     *
     * @return string
     */
    public static function krokedil_section_end($html, $key, $section)
    {
        \ob_start();
        ?>
		</table>
			</div>
				</div>
		<?php 
        return \ob_get_clean();
    }
}
