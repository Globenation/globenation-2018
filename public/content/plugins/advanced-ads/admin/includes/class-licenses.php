<?php
defined( 'ABSPATH'  ) || exit;

/**
 * handle add-on licenses
 */
class Advanced_Ads_Admin_Licenses {
	/**
	 * Instance of this class.
	 *
	 * @var      object
	 */
	protected static $instance = null;

	private function __construct() {
		if ( ! defined( 'DOING_AJAX' ) ) {
			add_action( 'load-plugins.php', array( $this, 'check_plugin_licenses' ) );
		}
		add_action( 'plugins_loaded', array( $this, 'wp_plugins_loaded' ) );
		
		// todo: check if this is loaded late enough and all add-ons are registered already
		add_filter( 'upgrader_pre_download', array( $this, 'addon_upgrade_filter' ), 10, 3 );		
	}
	
	/**
	 * actions and filter available after all plugins are initialized
	 */
	public function wp_plugins_loaded() {
	
		// check for add-on updates
		add_action( 'admin_init', array($this, 'add_on_updater'), 1 );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
	
	/**
	 * initiate plugin checks
	 * 
	 * @since 1.7.12
	 */
	public function check_plugin_licenses(){
	    
		if( is_multisite() ){
			return;
		}

		// gather all add-on plugin files 
		$add_ons = apply_filters( 'advanced-ads-add-ons', array() );
		foreach( $add_ons as $_add_on ){
		    
			// check license status
			if( $this->get_license_status( $_add_on['options_slug'] ) !== 'valid' ) {
				// register warning
				$plugin_file = plugin_basename( $_add_on['path'] );
				add_action( 'after_plugin_row_' . $plugin_file, array( $this, 'add_plugin_list_license_notice'), 10, 3 );
			}
		}
	}
	
	/**
	 * add a row below add-ons with an invalid license on the plugin list
	 * 
	 * @since 1.7.12
	 * @param string $plugin_file Path to the plugin file, relative to the plugins directory.
	 * @param array  $plugin_data An array of plugin data.
	 * @param string $status      Status of the plugin. Defaults are 'All', 'Active',
	 *                            'Inactive', 'Recently Activated', 'Upgrade', 'Must-Use',
	 *                            'Drop-ins', 'Search'.
	 * @todo make this work on multisite as well
	 */
	public function add_plugin_list_license_notice( $plugin_file, $plugin_data, $status ){
	    
		echo '<tr class="advads-plugin-update-tr plugin-update-tr active"><td class="plugin-update colspanchange" colspan="3"><div class="update-message notice inline notice-warning notice-alt"><p>' 
		    . sprintf( __( 'There might be a new version of %1$s. Please <strong>provide a valid license key</strong> in order to receive updates and support <a href="%2$s">on this page</a>.', 'advanced-ads' ), $plugin_data['Title'], admin_url( 'admin.php?page=advanced-ads-settings#top#licenses' ) )
		    . '</p></div></td></tr>';

	}	
	

	/**
	 * save license key
	 *
	 * @since 1.2.0
	 * @param string $addon string with addon identifier
	 */
	public function activate_license( $addon = '', $plugin_name = '', $options_slug = '', $license_key = '' ) {

		if ( '' === $addon || '' === $plugin_name || '' === $options_slug ) {
			return __( 'Error while trying to register the license. Please contact support.', 'advanced-ads' );
		}
		
		$license_key = esc_attr( trim( $license_key ) );
		if ( '' == $license_key ) {
			return __( 'Please enter a valid license key', 'advanced-ads' );
		}
		
		if ( has_filter( 'advanced_ads_license_'. $options_slug ) ) {
			return apply_filters( 'advanced_ads_license_' . $options_slug, false, __METHOD__, $plugin_name, $options_slug, $license_key );
		}
		
		/**
		 * we need to remove the mltlngg_get_url_translated filter added by Multilanguage by BestWebSoft, https://wordpress.org/plugins/multilanguage/
		 * it causes the URL to look much different than it originally is
		 * we are adding it again later
		 * 
		 */
		remove_filter( 'home_url', 'mltlngg_get_url_translated' );
		
		$api_params = array(
			'edd_action'=> 'activate_license',
			'license' 	=> $license_key,
			'item_name' => urlencode( $plugin_name ),
			'url'       => home_url()
		);
		
		/**
		 * re-add the filter removed from above
		 */
		if ( function_exists( 'mltlngg_get_url_translated' ) ) {
			add_filter( 'home_url', 'mltlngg_get_url_translated' );
		}
		
		// Call the custom API.
		$response = wp_remote_post( ADVADS_URL, array(
			'timeout'   => 15,
			'sslverify' => false,
			'body'      => $api_params
		) );
		
		// show license debug output if constant is set
		if( defined( 'ADVANCED_ADS_SHOW_LICENSE_RESPONSE' ) ){
			return '<pre>' . print_r( $response, true ) . '</pre>';
		}
		
		if ( is_wp_error( $response ) ) {
			$body = wp_remote_retrieve_body( $response );
			if ( $body ){
			    return $body;
			} else {
			    // return print_r($response, true);
			    $curl = curl_version();
			    return __( 'License couldn’t be activated. Please try again later.', 'advanced-ads' ) . " (cURL {$curl['version']})";
			}
		}		

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );
		// save license status
		if( !empty( $license_data->license ) ){
		    update_option($options_slug . '-license-status', $license_data->license, false);
		}
		if( !empty( $license_data->expires ) ){
			update_option($options_slug . '-license-expires', $license_data->expires, false); 
		}		

		// display activation problem
		if( !empty( $license_data->error )) {
		    // user friendly texts for errors
		    $errors = array(
			'license_not_activable' => __( 'This is the bundle license key.', 'advanced-ads' ),
			'item_name_mismatch' => __( 'This is not the correct key for this add-on.', 'advanced-ads' ),
			'no_activations_left' => __( 'There are no activations left.', 'advanced-ads' )
		    );
		    $error = isset( $errors[ $license_data->error ] ) ? $errors[ $license_data->error ] : $license_data->error;
		    if( 'expired' === $license_data->error ){
			return 'ex';
		    } else {
			if( isset($errors[ $license_data->error ] ) ) {
			    return $error;
			} else {
			    return sprintf( __( 'License is invalid. Reason: %s', 'advanced-ads' ), $error);
			}
		    }
		} else {
		    // reset license_expires admin notification
		    Advanced_Ads_Admin_Notices::get_instance()->remove_from_queue( 'license_expires' ); // this one is no longer added, but we keep the check here in case it is still in the queue for some users
		    Advanced_Ads_Admin_Notices::get_instance()->remove_from_queue( 'license_expired' ); // this one is no longer added, but we keep the check here in case it is still in the queue for some users
		    Advanced_Ads_Admin_Notices::get_instance()->remove_from_queue( 'license_invalid' );
		    // save license key
		    $licenses = $this->get_licenses();		    
		    $licenses[ $addon ] = $license_key;
		    $this->save_licenses( $licenses );
		}

		return 1;
	}
	
	/**
	 * check if a specific license key was already activated for the current page
	 * 
	 * @since 1.6.17
	 * @return bool true if already activated
	 * @deprecated since version 1.7.2 because it only checks if a key is valid, not if the url registered with that key
	 */
	public function check_license( $license_key = '', $plugin_name = '', $options_slug = '' ){
	    
		if ( has_filter( 'advanced_ads_license_'. $options_slug ) ) {
			return apply_filters( 'advanced_ads_license_' . $options_slug, false, __METHOD__, $plugin_name, $options_slug, $license_key );
		}
	    
		$api_params = array(
			'edd_action' => 'check_license',
			'license' => $license_key,
			'item_name' => urlencode( $plugin_name )
		);
		$response = wp_remote_get( add_query_arg( $api_params, ADVADS_URL ), array( 'timeout' => 15, 'sslverify' => false ) );
		if ( is_wp_error( $response ) ) {
			return false;
		}
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );
		
		// if this license is still valid
		if( $license_data->license == 'valid' ) {
			update_option($options_slug . '-license-expires', $license_data->expires, false);
			update_option($options_slug . '-license-status', $license_data->license, false);
			
			return true;
		}
		return false;
	}	
	
	/**
	 * deactivate license key
	 *
	 * @since 1.6.11
	 * @param string $addon string with addon identifier
	 */
	public function deactivate_license( $addon = '', $plugin_name = '', $options_slug = '' ) {

		if ( '' === $addon || '' === $plugin_name || '' === $options_slug ) {
			return __( 'Error while trying to disable the license. Please contact support.', 'advanced-ads' );
		}		

		$licenses = $this->get_licenses();
		$license_key = isset($licenses[$addon]) ? $licenses[$addon] : '';

		if ( has_filter( 'advanced_ads_license_'. $options_slug ) ) {
			return apply_filters( 'advanced_ads_license_' . $options_slug, false, __METHOD__, $plugin_name, $options_slug, $license_key );
		}

		$api_params = array(
			'edd_action' => 'deactivate_license',
			'license'    => $license_key,
			'item_name'  => urlencode( $plugin_name )
		);
		// Send the remote request
		$response = wp_remote_post( ADVADS_URL, array( 
		    'body' => $api_params, 
		    'timeout' => 15,
		    'sslverify' => false,
		) );
		
		// show license debug output if constant is set
		if( defined( 'ADVANCED_ADS_SHOW_LICENSE_RESPONSE' ) ){
			return '<pre>' . print_r( $response, true ) . '</pre>';
		}
		
		if ( is_wp_error( $response ) ) {
			$body = wp_remote_retrieve_body( $response );
			if ( $body ){
			    return $body;
			} else {
			    return __( 'License couldn’t be deactivated. Please try again later.', 'advanced-ads' );
			}
		}

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );
		
		// check if call was blocked by our site (e.g., Firewall)
		if( isset( $response['response']['code'] ) &&  403 === $response['response']['code'] ){
			return sprintf(__( 'Your call was blocked by a Firewall. <a href="%s" target="_blank">Learn more</a>', 'advanced-ads' ), ADVADS_URL . "manual/i-cant-activate-my-license/" );
		}
		
		// save license status

		// remove data
		if( 'deactivated' === $license_data->license ) {
		    delete_option( $options_slug . '-license-status' );
		    delete_option( $options_slug . '-license-expires' );
		} elseif( 'failed' === $license_data->license ) {
		    update_option($options_slug . '-license-expires', $license_data->expires, false);
		    update_option($options_slug . '-license-status', $license_data->license, false);
		    return 'ex';
		} else {
		    return __( 'License couldn’t be deactivated. Please try again later.', 'advanced-ads' );
		}

		return 1;
	}
	
	/**
	 * get license keys for all add-ons
	 * 
	 * @since 1.6.15
	 * @return arr $licenses licenses
	 */
	public function get_licenses(){
	    
	    $licenses = array();
	    
	    if( is_multisite() ){
		    // if multisite, get option from main blog
		    global $current_site;
		    $licenses = get_blog_option( $current_site->blog_id, ADVADS_SLUG . '-licenses', array() );
		    
	    } else {
		    $licenses = get_option( ADVADS_SLUG . '-licenses', array() );
	    }
	    
	    return $licenses;
	}
	
	/**
	 * save license keys for all add-ons
	 * 
	 * @since 1.7.2
	 * @return arr $licenses licenses
	 */
	public function save_licenses( $licenses = array() ){
	    
	    if( is_multisite() ){
		    // if multisite, get option from main blog
		    global $current_site;
		    update_blog_option( $current_site->blog_id, ADVADS_SLUG . '-licenses', $licenses );
	    } else {
		    update_option( ADVADS_SLUG . '-licenses', $licenses );
	    }
	}
	
	/**
	 * get license status of an add-on
	 * 
	 * @since 1.6.15
	 * @param  str $slug slug of the add-on
	 * @return str $status license status, e.g. "valid" or "invalid"
	 */
	public function get_license_status( $slug = '' ){
	    
	    $status = false;
	    
	    if( is_multisite() ){
		    // if multisite, get option from main blog
		    global $current_site;
		    $status = get_blog_option( $current_site->blog_id, $slug . '-license-status', false);
	    } else {
		    $status = get_option( $slug . '-license-status', false);
	    }
	    
	    return $status;
	}
	
	/**
	 * get license expired value of an add-on
	 * 
	 * @since 1.6.15
	 * @param  str $slug slug of the add-on
	 * @return str $date expiry date of an add-on
	 */
	public function get_license_expires( $slug = '' ){
	    
	    $date = false;
	    
	    if( is_multisite() ){
		    // if multisite, get option from main blog
		    global $current_site;
		    $date = get_blog_option( $current_site->blog_id, $slug . '-license-expires', false);
	    } else {
		    $date = get_option( $slug . '-license-expires', false);
	    }
	    
	    return $date;
	}
	
	
	/*
         * add-on updater
	 *
	 * @since 1.5.7
         */
        public function add_on_updater(){
	    
		// ignore, if not main blog
		if( ( is_multisite() && ! is_main_site() ) ){
		    return;
		}

		/**
		 * list of registered add ons
		 * contains:
		 *	    name
		 *	    version
		 *	    path
		 *	    options_slug
		 *	    short option slug (=key)
		 */
		$add_ons = apply_filters( 'advanced-ads-add-ons', array() );

		if( $add_ons === array() ) {
		    return;
		}
		
		// load license keys
		$licenses = get_option(ADVADS_SLUG . '-licenses', array());

		foreach( $add_ons as $_add_on_key => $_add_on ){

			// check if a license expired over time
			$expiry_date = $this->get_license_expires( $_add_on['options_slug'] );
			$now = time();
			if( $expiry_date && 'lifetime' !== $expiry_date && strtotime( $expiry_date ) < $now ){
				// remove license status
				delete_option( $_add_on['options_slug'] . '-license-status' );
				continue;
			}

			// check status
			if( $this->get_license_status( $_add_on['options_slug'] ) !== 'valid' ) {
				continue;
			}

			// retrieve our license key
			$license_key = isset($licenses[$_add_on_key]) ? $licenses[$_add_on_key] : '';

			// setup the updater
			if( $license_key ){
			    
				// register filter to set EDD transient to 86,400 seconds (day) instead of 3,600 (hours)
				$slug	    = basename( $_add_on['path'], '.php' );
				$transient_key = md5( serialize( $slug . $license_key ) );
				
				// add_filter( 'expiration_of_transient_' . $transient_key, array( $this, 'set_expiration_of_update_transient' ) );
				add_filter( 'pre_update_option_' . $transient_key, array( $this, 'set_expiration_of_update_option' ) );
				
				new ADVADS_SL_Plugin_Updater( ADVADS_URL, $_add_on['path'], array(
					'version' 	=> $_add_on['version'],
					'license' 	=> $license_key,
					'item_name' => $_add_on['name'],
					'author' 	=> 'Thomas Maier'
				    )
				);
			}
		}
        }
	
	/**
	 * set the expiration of the updater transient key to 1 day instead of 1 hour to prevent too many update checks
	 * 
	 * @deprecated since version 1.7.14 – not using transient anymore, but option
	 */
	public function set_expiration_of_update_transient( $expiration ){

		return 86400;
	}
	
	/**
	 * set the expiration of the updater transient key to 1 day instead of 1 hour to prevent too many update checks
	 * 
	 * @since   1.7.14
	 */
	public function set_expiration_of_update_option( $value ){

		$value['timeout'] = time() + 86400;
	    
		return $value;
	}
	
	/**
	 * add custom messages to plugin updater
	 * 
	 * @param type $reply
	 * @param type $package
	 * @param type $updater
	 * @return type
	 */
	public function addon_upgrade_filter( $reply, $package, $updater ) {
	    
		if( isset( $updater->skin->plugin ) ){
		    $plugin_file = $updater->skin->plugin;
		} elseif ( isset( $updater->skin->plugin_info['Name'] ) ){
		    $add_on = $this->get_installed_add_on_by_name( $updater->skin->plugin_info['Name'] );
		    $plugin_file = plugin_basename( $add_on['path'] );
		}
		
		if( isset( $plugin_file ) && $plugin_file ){
		    // hides the download url, but makes debugging harder
		    // $updater->strings['downloading_package'] = __( 'Downloading updated version...', 'advanced-ads' );
		    //$updater->skin->feedback( 'downloading_package' );
		    
		    // if AJAX; show direct update link as first possible solution
		    if( defined( 'DOING_AJAX' ) ){
			    $update_link = wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' ) . $plugin_file, 'upgrade-plugin_' . $plugin_file );
			    $updater->strings['download_failed'] = sprintf(__( 'Download failed. <a href="%s">Click here to try another method</a>.', 'advanced-ads' ), $update_link );		
		    } else {
			    $updater->strings['download_failed'] = sprintf(__( 'Download failed. <a href="%s" target="_blank">Click here to learn why</a>.', 'advanced-ads' ), ADVADS_URL . 'manual/download-failed-updating-add-ons/#utm_source=advanced-ads&utm_medium=link&utm_campaign=download-failed' );		
		    }
		    
		}
		
		/*$res = $updater->fs_connect( array( WP_CONTENT_DIR ) );
		if ( ! $res ) {
			return new WP_Error( 'no_credentials', __( "Error! Can't connect to filesystem", 'advanced-ads' ) );
		}*/

		return $reply;
	}
	
	/**
	 * search if a name is in the add-on array and return the add-on data of it
	 * 
	 * @param   str $name name of an add-on
	 * @return  arr	array with the add-on data
	 */
	private function get_installed_add_on_by_name( $name = '' ){
	    
		$add_ons = apply_filters( 'advanced-ads-add-ons', array() );
	    
		if( is_array( $add_ons ) ) {
		    foreach ( $add_ons as $key => $_add_on ) {
			if ($_add_on['name'] === $name ) {
			    return $_add_on;
			}
		    }
		}
		return null;
	}
	
	/**
	 * check if any license is valid
	 * can be used to display information for any Pro user only, like link to direct support
	 */
	public static function any_license_valid(){
		$add_ons = apply_filters( 'advanced-ads-add-ons', array() );
	    
		if( $add_ons === array() ) {
			return false;
		}

		foreach( $add_ons as $_add_on ){
			$status = Advanced_Ads_Admin_Licenses::get_instance()->get_license_status( $_add_on['options_slug'] );

			// check expiry date
			$expiry_date = Advanced_Ads_Admin_Licenses::get_instance()->get_license_expires( $_add_on['options_slug'] );

			if( ( $expiry_date && strtotime( $expiry_date ) > time() ) 
				|| 'valid' === $status
				|| 'lifetime' === $expiry_date ){
			    
				return true;
			}
		}

		return false;
	}


}