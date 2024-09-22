<?php
/**
 * Main request class
 *
 * @package WC_Klarna_Payments/Classes/Requests
 */

defined( 'ABSPATH' ) || exit;

use KrokedilKlarnaPaymentsDeps\Krokedil\WpApi\Request;

/**
 * Base class for all request classes.
 */
abstract class KP_Requests extends Request {
	/**
	 * The Klarna merchant Id, or MID. Used for calculating the request auth.
	 *
	 * @var string
	 */
	protected $merchant_id;

	/**
	 * The Klarna shared api secret. Used for calculating the request auth.
	 *
	 * @var string
	 */
	protected $shared_secret;

	/**
	 * Iframe options generated by helper class used to modify what the Klarna Payments iframe looks like.
	 *
	 * @var KP_IFrame
	 */
	protected $iframe_options;

	/**
	 * Filter to use for the request args.
	 *
	 * @var string
	 */
	protected $request_filter = 'wc_klarna_payments_request_args';

	/**
	 * Class constructor.
	 *
	 * @param mixed $arguments The request arguments.
	 */
	public function __construct( $arguments ) {
		$settings = get_option( 'woocommerce_klarna_payments_settings' );
		$config   = array(
			'slug'                   => 'klarna_payments',
			'plugin_version'         => WC_KLARNA_PAYMENTS_VERSION,
			'plugin_short_name'      => 'KP',
			'plugin_user_agent_name' => 'KP',
			'logging_enabled'        => 'no' !== $settings['logging'] ?? 'no',
			'extended_debugging'     => 'extra' === ( isset( $settings['logging'] ) ? $settings['logging'] : 'no' ),
			'base_url'               => $this->get_base_url( $arguments['country'], $settings ),
		);

		parent::__construct( $config, $settings, $arguments );
		$this->set_credentials();
		$this->iframe_options = new KP_IFrame( $settings );

		add_filter( 'wc_kp_image_url_cart_item', array( $this, 'maybe_allow_product_urls' ), 1, 1 );
		add_filter( 'wc_kp_url_cart_item', array( $this, 'maybe_allow_product_urls' ), 1, 1 );
		add_filter( 'wc_kp_image_url_order_item', array( $this, 'maybe_allow_product_urls' ), 1, 1 );
		add_filter( 'wc_kp_url_order_item', array( $this, 'maybe_allow_product_urls' ), 1, 1 );
	}

	/**
	 * Sets the environment.
	 *
	 * @param string $country The country code.
	 * @param array  $settings The settings array.
	 */
	protected function get_base_url( $country, $settings ) {
		$country_data = KP_Form_Fields::$kp_form_auto_countries[ strtolower( $country ?? '' ) ] ?? null;
		$testmode     = wc_string_to_bool( $settings['testmode'] ?? 'no' ); // Get the testmode setting.

		$region     = strtolower( apply_filters( 'klarna_base_region', $country_data['endpoint'] ?? '' ) ); // Get the region from the country parameters, blank for EU.
		$playground = $testmode ? '.playground' : ''; // If testmode is enabled, add playground to the subdomain.
		$subdomain  = "api{$region}{$playground}"; // Combine the string to one subdomain.

		return "https://{$subdomain}.klarna.com/"; // Return the full base url for the api.
	}

	/**
	 * Sets Klarna credentials.
	 */
	public function set_credentials() {
		$country     = strtolower( $this->arguments['country'] ) ?? strtolower( kp_get_klarna_country() ); // Get the country from the arguments, or the fetch from helper method.
		$combined_eu = isset( $this->settings['combine_eu_credentials'] ) ? ( 'yes' === $this->settings['combine_eu_credentials'] ) : false; // Check if we should combine the EU credentials.
		$testmode    = wc_string_to_bool( $this->settings['testmode'] ?? 'no' ); // Get the testmode setting.

		// If the country is a EU country, check if we should get the credentials from the EU settings.
		if ( $combined_eu && key_exists( $country, KP_Form_Fields::available_countries( 'eu' ) ) ) {
			$country = 'eu';
		}

		$prefix = $testmode ? 'test_' : ''; // If testmode is enabled, add test_ to the setting strings.

		$merchant_id   = "{$prefix}merchant_id_{$country}";
		$shared_secret = "{$prefix}shared_secret_{$country}";

		$this->merchant_id   = isset( $this->settings[ $merchant_id ] ) ? $this->settings[ $merchant_id ] : '';
		$this->shared_secret = isset( $this->settings[ $shared_secret ] ) ? $this->settings[ $shared_secret ] : '';
	}

	/**
	 * Calculates the auth header for the request.
	 *
	 * @return string
	 */
	public function calculate_auth() {
		return 'Basic ' . base64_encode( $this->merchant_id . ':' . htmlspecialchars_decode( $this->shared_secret, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401 ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions -- Base64 used to calculate auth headers.
	}

	/**
	 * Maybe filters out product urls before we send them to Klarna based on settings.
	 *
	 * @param string $url The URL to the product or product image.
	 * @return string|null
	 */
	public function maybe_allow_product_urls( $url ) {
		if ( 'yes' === $this->settings['send_product_urls'] ?? false ) {
			$url = null;
		}
		return $url;
	}

	/**
	 * Gets the error message from the Klarna payments response.
	 *
	 * @param array $response
	 * @return WP_Error
	 */
	public function get_error_message( $response ) {
		$error_message = '';
		// Get the error messages.
		if ( null !== json_decode( $response['body'], true ) ) {
			foreach ( json_decode( $response['body'], true )['error_messages'] as $error ) {
				$error_message = "$error_message $error";
			}
		}
		$code          = wp_remote_retrieve_response_code( $response );
		$error_message = empty( $error_message ) ? $response['response']['message'] : $error_message;
		return new WP_Error( $code, $error_message );
	}
}