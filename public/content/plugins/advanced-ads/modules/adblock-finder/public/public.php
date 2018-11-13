<?php

class Advanced_Ads_Adblock_Finder {

	public function __construct() {
		add_action( 'wp_footer', array( $this, 'print_adblock_check_js' ), 9 );
	}

	public function print_adblock_check_js() {
		$options = Advanced_Ads::get_instance()->options();
		if ( !empty( $options['ga-UID'] ) ) {
		?><script>
		var advanced_ads_ga_UID = <?php echo isset( $options['ga-UID'] ) ? "'" . esc_js( $options['ga-UID'] ). "'" : 'false' ?>;
		var advanced_ads_ga_anonymIP = <?php 
		
		if ( defined( 'ADVANCED_ADS_DISABLE_ANALYTICS_ANONYMIZE_IP' ) ) {
			echo "false;\n";
		} else {
			echo "true;\n";
		}
		
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG && current_user_can( 'manage_options' ) ) {
			readfile( dirname( __FILE__ ) . '/script.js' );
		} else {
			readfile( dirname( __FILE__ ) . '/script.min.js' );
		}

		?></script><?php
		}
	}
}
