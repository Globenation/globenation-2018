<?php

class Advanced_Ads_AdSense_Public {

	private $data; // options

	private static $instance = null;

	private function __construct() {
		$this->data = Advanced_Ads_AdSense_Data::get_instance();
		add_action( 'wp_head', array( $this, 'inject_header' ), 20 );
	}

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Print data in the head tag on the front end.
	 */
	public function inject_header(){
		$options = $this->data->get_options();

		// Inject CSS to make AdSense background transparent.
		if ( ! empty( $options['background'] ) ) {
			echo '<style>ins.adsbygoogle { background-color: transparent; padding: 0; }</style>';
		}

		$privacy_options = Advanced_Ads_Privacy::get_instance()->options();
		$privacy_enabled = ! empty( $privacy_options['enabled'] ) && 'not_needed' !== Advanced_Ads_Privacy::get_instance()->get_state();
		$npa_enabled = ! empty( $privacy_options['show-non-personalized-adsense'] );

		// Show non-personalized Adsense ads if consent was not given.
		// If non-personalized ads are enabled.
		if ( $privacy_enabled && $npa_enabled ) {
			echo '<script>';
			// If the page is not from a cache.
			if ( Advanced_Ads_Privacy::get_instance()->get_state() === 'unknown' ) {
				echo '(adsbygoogle=window.adsbygoogle||[]).requestNonPersonalizedAds=1;';
			}

			// If the page is from a cache.
			// Wait until 'advads.privacy' is available. Execute before cache-busting.
			echo '( window.advanced_ads_ready || jQuery( document ).ready ).call( null, function() {
					var state = ( advads.privacy ) ? advads.privacy.get_state() : "";
					var use_npa = ( state === "unknown" ) ? 1 : 0;
					(adsbygoogle=window.adsbygoogle||[]).requestNonPersonalizedAds=use_npa;
			} )</script>';
		}

		/**
		 * inject page-level header code
		 *
		 * @since 1.6.9
		 */
		$pub_id = trim( $this->data->get_adsense_id() );

		if( ! defined( 'ADVADS_ADS_DISABLED' ) && $pub_id && isset( $options['page-level-enabled'] ) && $options['page-level-enabled'] ){
			$pub_id = $this->data->get_adsense_id();
			$client_id = 'ca-' . $pub_id;
			include GADSENSE_BASE_PATH . 'public/templates/page-level.php';
		}
	}
}
