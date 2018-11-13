<?php

/**
 * checks for various things
 *
 * @since 1.6.9
 */
class Advanced_Ads_Checks {
    
	/**
	 * Minimum required PHP version of Advanced Ads
	 */
	const MINIMUM_PHP_VERSION = 5.4;
    
    
	/**
	 * show the list of potential issues
	 */
	public static function show_issues(){
		include_once ADVADS_BASE_PATH . '/admin/views/checks.php';
	}

	/**
	 * php version minimum 5.4
	 *
	 * @return bool true if 5.4 and higher
	 */
	 public static function php_version_minimum(){

		if (version_compare(phpversion(), self::MINIMUM_PHP_VERSION, '>=')) {
			return true;
		}

		return false;
	 }

	/**
	 * caching used
	 *
	 * @return bool true if active
	 */
	 public static function cache(){
		if ( ( defined( 'WP_CACHE' ) && WP_CACHE ) // general cache constant
			|| defined('W3TC') // W3 Total Cache
			|| function_exists( 'wp_super_cache_text_domain' ) // WP SUper Cache
			|| defined( 'WP_ROCKET_SLUG' ) //WP Rocket
			|| defined( 'WPFC_WP_CONTENT_DIR' ) //WP Fastest Cache
			|| class_exists( 'HyperCache', false ) // Hyper Cache
			|| defined( 'CE_CACHE_DIR' ) // Cache Enabler
		){
			return true;
		}

		return false;
	 }

	 /**
	  * WordPress update available
	  *
	  * @return bool true if WordPress update available
	  */
	 public static function wp_update_available(){

		$update_data = wp_get_update_data();
		$count = absint( $update_data['counts']['wordpress'] );

		if( $count ){
			return true;
		}

		return false;
	 }

	 /**
	  * any plugin updates available
	  *
	  * @return bool true if plugin updates are available
	  */
	 public static function plugin_updates_available(){
		
		// iterate throught the plugins and check if any of them is ours (i.e., starts with the string "advanced-ads")
		$update_plugins = get_site_transient( 'update_plugins' );
		if ( ! empty( $update_plugins->response ) ) {
			foreach( $update_plugins->response as $_key => $_responsive ){
				if( 0 === strpos( $_key, 'advanced-ads') ){
					return true; 
				}
			}
		}

		return false;
	 }

	 /**
	  * check if license keys are missing or invalid or expired
	  *
	  * @since 1.6.6
	  * @update 1.6.9 moved from Advanced_Ads_Plugin
	  * @update 1.8.21 also check for expired licenses
	  * @return true if there are missing licenses
	  */
	public static function licenses_invalid(){

	    $add_ons = apply_filters( 'advanced-ads-add-ons', array() );
	    
	    if( $add_ons === array() ) {
		    return false;
	    }

	    foreach( $add_ons as $_add_on_key => $_add_on ){
		    $status = Advanced_Ads_Admin_Licenses::get_instance()->get_license_status( $_add_on['options_slug'] );
		    
		    // check expiry date
		    $expiry_date = Advanced_Ads_Admin_Licenses::get_instance()->get_license_expires( $_add_on['options_slug'] );

		    if( $expiry_date && 'lifetime' !== $expiry_date && strtotime( $expiry_date ) < time() ){
			    return true;
		    }
		    
		    // don’t check if license is valid
		    if( $status === 'valid' ) {
			    continue;
		    }

		    // retrieve our license key from the DB
		    $licenses = Advanced_Ads_Admin_Licenses::get_instance()->get_licenses();

		    $license_key = isset($licenses[$_add_on_key]) ? $licenses[$_add_on_key] : false;

		    if( ! $license_key || $status !== 'valid' ){
			    return true;
		    }
	    }

	    return false;
	}
	
	/**
	 * Autoptimize plugin installed
	 *   can change ad tags, especially inline css and scripts
	 *
	 * @link https://wordpress.org/plugins/autoptimize/
	 * @return bool true if Autoptimize is installed
	 */
	public static function active_autoptimize(){

		if( defined( 'AUTOPTIMIZE_CACHE_DIR' ) ){
			return true;
		}

		return false;
	}

	/**
	 * check for additional conflicting plugins
	 *
	 * @return arr $plugins names of conflicting plugins
	 */
	public static function conflicting_plugins(){

		$conflicting_plugins = array();

		if( defined( 'Publicize_Base' ) ){ // JetPack Publicize module
			$conflicting_plugins[] = 'Jetpack – Publicize';
		}
		if( defined( 'PF__PLUGIN_DIR' ) ){ // Facebook Instant Articles & Google AMP Pages by PageFrog
			$conflicting_plugins[] = 'Facebook Instant Articles & Google AMP Pages by PageFrog';
		}
		if( defined( 'GT_VERSION' ) ){ // GT ShortCodes
			$conflicting_plugins[] = 'GT ShortCodes';
		}
		if( class_exists( 'ITSEC_Core', false ) && defined ( 'AAP_VERSION' ) ){ // iThemes Security, but only if Pro is enabled
			$conflicting_plugins[] = 'iThemes Security';
		}

		return $conflicting_plugins;
	}
	
	/**
	 * check if any of the global hide ads options is set
	 * ignore feed setting, because it is standard
	 * 
	 * @since 1.7.10
	 * @return bool
	 */
	public static function ads_disabled(){
		$options = Advanced_Ads::get_instance()->options();
		if( isset( $options['disabled-ads'] ) && is_array( $options['disabled-ads'] ) ){
			foreach( $options['disabled-ads'] as $_key => $_value ){
				// don’t warn if "feed" and "404" option are enabled, because they are normally not critical
				if( !empty( $_value ) && !in_array($_key, array( 'feed', '404') ) ){
					return true;
				}
			}
		}
		return false;
	}
	
	/**
	 * check for required php extensions
	 * 
	 * @since 1.8.21
	 * @return bool
	 */
	public static function php_extensions(){
		
		$missing_extensions = array();
		
		if( !extension_loaded('dom') ){
		    $missing_extensions[] = 'dom';
		}
		
		if( !extension_loaded('xml') ){
		    $missing_extensions[] = 'xml';
		}
		
		return $missing_extensions;
	}
	
	/**
	 * Get the list of Advanced Ads constant defined by the user.
	 *
	 * @return array
	 */
	public static function get_defined_constants() {
		$constants = apply_filters( 'advanced-ads-constants', array(
			'ADVADS_ADS_DISABLED',
			'ADVADS_ALLOW_ADSENSE_ON_404',
			'ADVADS_DISABLE_RESPONSIVE_IMAGES',
			'ADVANCED_ADS_AD_DEBUG_FOR_ADMIN_ONLY',
			'ADVANCED_ADS_DISABLE_ANALYTICS_ANONYMIZE_IP',
			'ADVANCED_ADS_DISABLE_CHANGE',
			'ADVANCED_ADS_DISABLE_CODE_HIGHLIGHTING',
			'ADVANCED_ADS_DISABLE_FRONTEND_AD_WEIGHT_UPDATE',
			'ADVANCED_ADS_DISABLE_SHORTCODE_BUTTON',
			'ADVANCED_ADS_DISALLOW_PHP',
			'ADVANCED_ADS_ENABLE_REVISIONS',
			'ADVANCED_ADS_PRO_CUSTOM_POSITION_MOVE_INTO_HIDDEN',
			'ADVANCED_ADS_PRO_PAGE_IMPR_EXDAYS',
			'ADVANCED_ADS_PRO_REFERRER_EXDAYS',
			'ADVANCED_ADS_RESPONSIVE_DISABLE_BROWSER_WIDTH',
			'ADVANCED_ADS_SUPPRESS_PLUGIN_ERROR_NOTICES',
			'ADVANCED_ADS_TRACKING_DEBUG',
			'ADVANCED_ADS_TRACKING_NO_HOURLY_LIMIT',
		) );

		$result = array();
		foreach ( $constants as $constant ) {
			if ( defined( $constant ) ) {
				$result[] = $constant;
			}
		}
		return $result;
	}
	
	/**
	 * check for potential jQuery errors
	 * only script, so no return, but direct output
	 * 
	 */
	public static function jquery_ui_conflict(){
	    ?>
	    <div id="advads-jqueryui-conflict-message" style="display:none;" class="message error"><p><?php printf( __( 'Possible conflict between jQueryUI library, used by Advanced Ads and other libraries (probably <a href="%s">Twitter Bootstrap</a>). This might lead to misfortunate formats in forms, but should not damage features.', 'advanced-ads' ), 'http://getbootstrap.com/javascript/#js-noconflict' ); ?></p></div>
	    <script>// string from jquery-ui source code
		jQuery(document).ready(function(){
		    var needle = 'var g="string"==typeof f,h=c.call(arguments,1)';
		    if ( jQuery.fn.button.toString().indexOf( needle ) === -1 || jQuery.fn.tooltip.toString().indexOf( needle ) === -1 ) {
			    jQuery( '#advads-jqueryui-conflict-message' ).show();
		    }
		});
	    </script><?php
	}
}
