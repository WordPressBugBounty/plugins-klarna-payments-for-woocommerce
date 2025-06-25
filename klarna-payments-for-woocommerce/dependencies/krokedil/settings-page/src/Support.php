<?php

namespace KrokedilKlarnaPaymentsDeps\Krokedil\SettingsPage;

\defined('ABSPATH') || exit;
use KrokedilKlarnaPaymentsDeps\Krokedil\SettingsPage\Traits\Layout;
/**
 * Support class to handle the support section of the settings page.
 */
class Support
{
    use Layout;
    /**
     * The Support for the page.
     *
     * @var array $support
     */
    protected $support = array();
    /**
     * Class constructor.
     *
     * @param array                    $support Support for the page.
     * @param array                    $sidebar Sidebar content.
     * @param \WC_Payment_Gateway|null $gateway The gateway object.
     *
     * @return void
     */
    public function __construct($support, $sidebar, $gateway = null)
    {
        $this->title = __('Support', 'krokedil-settings');
        $this->gateway = $gateway;
        $this->support = $support;
        $this->sidebar = $sidebar;
    }
    /**
     * Return the Helpscout beacon script.
     *
     * @param string $use_helpscout Whether to include the Helpscout beacon.
     * @return string|null
     */
    public static function hs_beacon_script($use_helpscout = 'no')
    {
        if ('yes' !== $use_helpscout) {
            return;
        }
        return '!function(e,t,n){function a(){var e=t.getElementsByTagName("script")[0],n=t.createElement("script");n.type="text/javascript",n.async=!0,n.src="https://beacon-v2.helpscout.net",e.parentNode.insertBefore(n,e)}if(e.Beacon=n=function(t,n,a){e.Beacon.readyQueue.push({method:t,options:n,data:a})},n.readyQueue=[],"complete"===t.readyState)return a();e.attachEvent?e.attachEvent("onload",a):e.addEventListener("load",a,!1)}(window,document,window.Beacon||function(){});';
    }
    /**
     * Enqueue the scripts for the support page.
     *
     * @return void
     */
    public function enqueue_scripts()
    {
        // Load CSS.
        wp_enqueue_style('krokedil-support-page');
        // If the WC Version is 9.0 or above, get the container for the RestAPiUtil, else us the WC()->api class property.
        // The deprecation notice says 9.1.0, but the WC()->api property is not set in 9.0.0.
        // @see https://github.com/woocommerce/woocommerce/blob/5690850e47284b3a9afd4e61dec01121159ca9e8/plugins/woocommerce/src/Internal/Utilities/LegacyRestApiStub.php#L183-L187.
        $legacy_wc = \version_compare(WC_VERSION, '9.0', '<=');
        $system_report = '';
        if ($legacy_wc) {
            $system_report = WC()->api->get_endpoint_data('/wc/v3/system_status');
        } elseif (\class_exists(\Automattic\WooCommerce\Utilities\RestApiUtil::class)) {
            $system_report = wc_get_container()->get(\Automattic\WooCommerce\Utilities\RestApiUtil::class)->get_endpoint_data('/wc/v3/system_status');
        }
        $beacon_id = '9c22f83e-3611-42aa-a148-1ca06de53566';
        // Localize the support scrip.
        wp_localize_script('krokedil-support-page', 'krokedil_support_params', array('systemReport' => $system_report, 'beaconId' => $beacon_id));
        // Load JS.
        wp_add_inline_script('krokedil-support-page', self::hs_beacon_script($this->support['use_helpscout'] ?? 'yes'), 'before');
        wp_enqueue_script('krokedil-support-page');
    }
    /**
     * Output the support HTML.
     *
     * @return void
     */
    public function output_page_content()
    {
        global $hide_save_button;
        $hide_save_button = \true;
        $this->enqueue_scripts();
        $content = $this->support['content'];
        $use_helpscout = $this->support['use_helpscout'] ?? 'yes';
        ?>
		<div class='krokedil_support'>
			<div class="krokedil_support__info">
				<?php 
        foreach ($content as $item) {
            ?>
					<?php 
            echo wp_kses_post(self::print_content($item));
            ?>
					<?php 
        }
        if ('yes' === $use_helpscout) {
            ?>
					<p><?php 
            esc_html_e('If you still need help, please open a support ticket with Krokedil.', 'krokedil-settings');
            ?></p>
					<button type="button" class="button button-primary support-button"><?php 
            esc_html_e('Open support ticket with Krokedil', 'krokedil-settings');
            ?></button>
					<?php 
        }
        ?>
			</div>
		</div>
		<?php 
    }
}
