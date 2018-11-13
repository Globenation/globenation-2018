<?php
class Advanced_Ads_Privacy
{
	/**
	 * Singleton instance of the plugin
	 *
	 * @var     Advanced_Ads_Privacy
	 */
	protected static $instance;

	/**
	 * module options
	 *
	 * @var     array (if loaded)
	 */
	protected $options;
	
	/**
	 * option key
	 * 
	 * @const	    array
	 */
	const OPTION_KEY = 'advanced-ads-privacy';

	/**
	 * Initialize the module
	 */
	private function __construct() {
		$options = $this->options();

		add_filter( 'advanced-ads-can-display', array( $this, 'can_display_by_consent' ), 10, 3 );

		if ( ! empty( $options['enabled'] ) ) {
			add_filter( 'advanced-ads-activate-advanced-js', '__return_true' );
		}
	}

	/**
	 * Return an instance of Advanced_Ads_Privacy
	 *
	 * @return  Advanced_Ads_Privacy
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance )
		{
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Return module options
	 *
	 * @return  array $options
	 */
	public function options() {
		if ( ! isset( $this->options ) ) {
			$this->options = get_option( self::OPTION_KEY, array() );
		}
		return $this->options;
	}

	/**
	 * Check if ad can be displayed based on user's consent.
	 *
	 * @return bool
	 */
	public function can_display_by_consent( $can_display, Advanced_Ads_Ad $ad, $check_options ) {
		if ( ! $can_display ) {
			return $can_display;
		}

		if ( $check_options['passive_cache_busting'] ) {
			return true;
		}

		$ad_options = $ad->options();
		// If consent is not needed for the ad.
		if ( ! empty( $ad_options['privacy']['ignore-consent'] ) ) {
			return true;
		}

		$privacy_options = $this->options();
		if ( $ad->type === 'adsense' ) {
			if ( ! empty( $privacy_options['show-non-personalized-adsense'] ) ) {
				// Either personalized or non-personalized ad will be shown.
				return true;
			}
		}

		$state = $this->get_state();
		return ( $state === 'accepted' || $state === 'not_needed' );
	}

	/**
	 * Check if consent is not needed or was given by the user.
	 *
	 * @return str
	 *     'not_needed' - consent is not needed.
	 *     'accepted' - consent was given.
	 *     'unknown' - consent was not given yet.
	 */
	public function get_state() {
		if ( empty ( $this->options['enabled'] ) ) {
			return 'not_needed';
		}

		$consent_method = isset( $this->options['consent-method'] ) ? $this->options['consent-method'] : 0;
		switch ( $consent_method ) {
			case '0':
				return 'not_needed';
				break;
			case 'custom':
				if ( ! isset( $this->options['custom-cookie-name'] ) || ! isset( $this->options['custom-cookie-value'] ) ) {
					return 'not_needed';
				}
				$name = $this->options['custom-cookie-name'];
				$value = $this->options['custom-cookie-value'];
				if ( ! isset( $_COOKIE[ $name ] ) ) {
					return 'unknown';
				}

				if ( ( $value === '' && $_COOKIE[ $name ] === '' )
					|| ($value !== '' &&  strpos( $_COOKIE[ $name ], $value ) !== false ) ) {
					return 'accepted';
				}
				return 'unknown';
				break;
			default:
				return isset( $_COOKIE[ $consent_method ] ) ? 'accepted' : 'unknown';
				break;
		}
	}

	/**
	 * Get consent methods.
	 *
	 * @return arr
	 */
	public function get_consent_methods() {
		$methods = array(
			'0' => __( 'Show all ads even without consent.', 'advanced-ads' ),
			'custom' => __( 'Cookie', 'advanced-ads' ),
		);
		/*
		// https://wordpress.org/plugins/cookie-notice/
		if ( class_exists( 'Cookie_Notice' ) ) {
			$methods['cookie_notice_accepted'] = 'Cookie Notice by dFactory';
		}
		// https://wordpress.org/plugins/uk-cookie-consent/
		if ( defined( 'CTCC_PLUGIN_URL' ) ) {
			$methods['catAccCookies'] = 'Cookie Consent';
		}
		// https://wordpress.org/plugins/cookie-law-info/
		if ( defined( 'CLI_PLUGIN_DEVELOPMENT_MODE' ) ) {
			$methods['viewed_cookie_policy'] = 'GDPR Cookie Consent';
		}
		*/
		return $methods;
	}
}
