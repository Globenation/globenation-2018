<?php

/**
 * container class for admin notices
 *
 * @package WordPress
 * @subpackage Advanced Ads Plugin
 * @since 1.4.5
 */
class Advanced_Ads_Admin_Notices {

	/**
	 * maximum number of notices to show at once
	 */
	 const MAX_NOTICES = 2;

	/**
	 * instance of this class.
	 *
	 * @since    1.5.3
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * options
	 *
	 * @since	1.5.3
	 * @var	array
	 */
	protected $options;

	/**
	 * notices to be displayed
	 *
	 * @since	1.5.3
	 * @var	array
	 */
	public $notices = array();

	/**
	 * plugin class
	 */
	private $plugin;

	public function __construct() {
		$this->plugin = Advanced_Ads_Plugin::get_instance();
		// load notices
		$this->load_notices();
		// display notices
		//$this->display_notices();

		add_action( 'advanced-ads-ad-params-before', array( $this, 'adsense_tutorial' ), 10, 2 );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.5.3
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
	 * load admin notices
	 *
	 * @since 1.4.5
	 * @updated 1.5.3 moved from admin class here
	 */
	public function load_notices() {

		$options = $this->options();
		$plugin_options = $this->plugin->options();

		// load notices from queue
		$this->notices = isset($options['queue']) ? $options['queue'] : array();
		$notices_before = $this->notices;

		// check license notices
		$this->register_license_notices();
		// notice for Adblocker module
		$this->check_assets_expired();

		// don’t check non-critical notices if they are disabled
		if ( ! isset($plugin_options['disable-notices']) ) {
			// handle version notices
			$this->register_version_notices();
			// check other notices
			$this->check_notices();
		}

		// register notices in db so they get displayed until closed for good
		if ( $this->notices !== $notices_before ) {
			$this->add_to_queue( $this->notices );
		}
	}

	/**
	 * register update notices
	 *
	 */
	public function register_version_notices() {
		if ( defined( 'DOING_AJAX' ) ) {
			return;
		}

		$internal_options = $this->plugin->internal_options();
		$plugin_options = $this->plugin->options();

		// set an artifical older version for updates on installed plugins before the notice logic was invented
		if ( ! isset($internal_options['version']) && $plugin_options !== array() ) {
			$old_version = '1.4.4';
		} elseif ( isset($internal_options['version']) ) {
			$old_version = $internal_options['version'];
		} else {
			// empty version for new installations
			$old_version = 0;
		}
		
		if ( isset($internal_options['version']) && ($internal_options['version'] !== ADVADS_VERSION) && $old_version ) {
			if ( version_compare( $old_version, '1.7' ) == -1 ) {
				$this->notices[] = '1.7';
			}
		}
	}
	
	/**
	 * update version number to latest one
	 */
	public function update_version_number(){
	    
	    $internal_options = $this->plugin->internal_options();
	    $new_options = $internal_options; // in case we udpate options here
	    
	    $new_options['version'] = ADVADS_VERSION;
	    
	    // update version numbers
	    if ( $internal_options !== $new_options ) {
		    $this->plugin->update_internal_options( $new_options );
	    }
	}

	/**
	 * check various notices conditions
	 */
	public function check_notices() {
		$internal_options = $this->plugin->internal_options();
		$now = time();
		$activation = (isset($internal_options['installed'])) ? $internal_options['installed'] : $now; // activation time

		$options = $this->options();
		$closed = isset($options['closed']) ? $options['closed'] : array();
		$queue = isset($options['queue']) ? $options['queue'] : array();
		$paused = isset($options['paused']) ? $options['paused'] : array();
		
		// register intro message
		if( ! Advanced_Ads_Admin::get_instance()->screen_belongs_to_advanced_ads() 
			&& $options === array() && ! in_array( 'nl_intro', $queue ) && ! isset( $closed['nl_intro'] ) ){
			$this->notices[] = 'nl_intro';
		} elseif ( Advanced_Ads_Admin::get_instance()->screen_belongs_to_advanced_ads() ){
			$key = array_search( 'nl_intro', $this->notices );
			if ( $key !== false ) {
				unset( $this->notices[$key]);
			}
		}
		// offer free add-ons if not yet subscribed
		if ( $this->user_can_subscribe(  ) && ! in_array( 'nl_free_addons', $queue ) && ! isset( $closed['nl_free_addons'] )) {
			// get number of ads
			if( Advanced_Ads::get_number_of_ads() ){
				$this->notices[] = 'nl_free_addons';
			}
		}
		// ask for a review after 5 days and when 3 ads were created and when not paused
		if ( ! in_array( 'review', $queue ) 
                        && ! isset( $closed['review'] )
                        && ( ! isset( $paused['review'] ) || $paused['review'] <= time() )
                        && 432000 < ( time() - $activation)
			&& 3 <= Advanced_Ads::get_number_of_ads()
                            ) {
                            $this->notices[] = 'review';
		}
	}

	/**
	 * register license key notices
	 */
	public function register_license_notices(){

		if( ! Advanced_Ads_Admin::screen_belongs_to_advanced_ads() ){
		    return;
		}

		$options = $this->options();
		$queue = isset($options['queue']) ? $options['queue'] : array();
		// check license keys
		
		if ( Advanced_Ads_Checks::licenses_invalid() ){
		    if( ! in_array( 'license_invalid', $queue )) {
			$this->notices[] = 'license_invalid';
		    }
		} else {
		    $this->remove_from_queue( 'license_invalid' );
		}
	}

	/**
	 * Notice for Adblocker module
	 */
	public function check_assets_expired() {
		$plugin_options = $this->plugin->options();
		$options = $this->options();

		if ( empty ( $plugin_options['use-adblocker'] ) ) {
			// check if assets expired, but user disabled Adblocker module
			$key = array_search( 'assets_expired', $this->notices );
			if ( $key !== false ) {
				$this->remove_from_queue( 'assets_expired' );
				unset( $this->notices[ $key] );
			}

			return;
		}

		$adblocker_options = Advanced_Ads_Ad_Blocker::get_instance()->options();

		if ( ! in_array( 'assets_expired', $this->notices ) && ( empty ( $adblocker_options['module_can_work'] ) )
		) {
			$this->notices[] = 'assets_expired';
		}
	}

	/**
	 * add update notices to the queue of all notices that still needs to be closed
	 *
	 * @since 1.5.3
	 * @param str|arr $notices one or more notices to be added to the queue
	 */
	public function add_to_queue($notices = 0) {
		if ( ! $notices ) {
			return;
		}

		// get queue from options
		$options = $this->options();
		$queue = isset($options['queue']) ? $options['queue'] : array();

		if ( is_array( $notices ) ) {
			$queue = array_merge( $queue, $notices );
		} else {
			$queue[] = $notices;
		}

		// remove possible duplicated
		$queue = array_unique( $queue );

		// update db
		$options['queue'] = $queue;
		$this->update_options( $options );
	}

	/**
	 * remove update notice from queue
	 *  move notice into "closed"
	 *
	 * @since 1.5.3
	 * @param str $notice notice to be removed from the queue
	 */
	public function remove_from_queue($notice) {
		if ( ! isset($notice) ) {
			return;
		}

		// get queue from options
		$options_before = $options = $this->options();
		if ( ! isset($options['queue']) ) {
			return;
		}
		$queue = (array) $options['queue'];
		$closed = isset($options['closed']) ? $options['closed'] : array();
		$paused = isset($options['paused']) ? $options['paused'] : array();

		$key = array_search( $notice, $queue );
		if ( $key !== false ) {
			unset($queue[$key]);
			// close message with timestamp
		}
		// don’t close again twice
		if( ! isset( $closed[$notice] )){
		    $closed[$notice] = time();
		}
		// remove from pause
		if( isset( $paused[$notice] ) ){
		    unset( $paused[$notice] );
		}
		
		// update db
		$options['queue'] = $queue;
		$options['closed'] = $closed;
		$options['paused'] = $paused;
		
		
		// only update if changed
		if( $options_before !== $options ){
		    $this->update_options( $options );
		    // update already registered notices
		    $this->load_notices();
		}
	}
	
	/**
	 *  hide any notice for a given time
	 *  move notice into "paused" with notice as key and timestamp as value
	 *
	 * @since 1.8-17
	 * @param str $notice notice to be paused
	 */
	public function hide_notice($notice) {
		if ( ! isset($notice) ) {
			return;
		}

		// get queue from options
		$options_before = $options = $this->options();
		if ( ! isset($options['queue']) ) {
			return;
		}
		$queue = (array) $options['queue'];
		$paused = isset($options['paused']) ? $options['paused'] : array();

		$key = array_search( $notice, $queue );
		if ( $key !== false ) {
			unset($queue[$key]);
		}
		// close message with timestamp in 7 days
		// don’t close again twice
		if( ! isset( $paused[$notice] )){
		    $paused[$notice] = time() + WEEK_IN_SECONDS;
		}
		
		// update db
		$options['queue'] = $queue;
		$options['paused'] = $paused;
		
		// only update if changed
		if( $options_before !== $options ){
		    $this->update_options( $options );
		    // update already registered notices
		    $this->load_notices();
		}
	}

	/**
	 *
	 * display notices
	 *
	 */
	public function display_notices() {

		if ( defined( 'DOING_AJAX' ) ) {
			return; }

		if ( $this->notices === array() ) {
			return; }

		// load notices
		include ADVADS_BASE_PATH . '/admin/includes/notices.php';

		// iterate through notices
		$count = 0;
		foreach ( $this->notices as $_notice ) {

			if ( isset($advanced_ads_admin_notices[$_notice]) ) {
				$notice = $advanced_ads_admin_notices[$_notice];
				$text = $advanced_ads_admin_notices[$_notice]['text'];
				$type = isset($advanced_ads_admin_notices[$_notice]['type']) ? $advanced_ads_admin_notices[$_notice]['type'] : '';
			} else {
				continue;
			}
			
			// don’t display non-global notices on other than plugin related pages
			if( ( ! isset( $advanced_ads_admin_notices[$_notice]['global'] ) || ! $advanced_ads_admin_notices[$_notice]['global'] ) 
				&& ! Advanced_Ads_Admin::screen_belongs_to_advanced_ads() ) {
				continue;
			}
                        
                        // don't display license nag if ADVANCED_ADS_SUPPRESS_PLUGIN_ERROR_NOTICES is defined
                        if( defined( 'ADVANCED_ADS_SUPPRESS_PLUGIN_ERROR_NOTICES' ) && $advanced_ads_admin_notices[$_notice]['type'] == 'plugin_error' ) {
                            continue;
                        }

			switch ( $type ) {
				case 'info' :
					include ADVADS_BASE_PATH . '/admin/views/notices/info.php';
				break;
				case 'subscribe' :
					include ADVADS_BASE_PATH . '/admin/views/notices/subscribe.php';
				break;
				case 'plugin_error' :
					include ADVADS_BASE_PATH . '/admin/views/notices/plugin_error.php';
				break;
				default :
					include ADVADS_BASE_PATH . '/admin/views/notices/error.php';
			}

			if( ++$count == self::MAX_NOTICES ) {
			    break;
			}
		}
	}

	/**
	 * return notices options
	 *
	 * @since 1.5.3
	 * @return array $options
	 */
	public function options() {
		if ( ! isset($this->options) ) {
			$this->options = get_option( ADVADS_SLUG . '-notices', array() );
		}

		return $this->options;
	}

	/**
	 * update notices options
	 *
	 * @since 1.5.3
	 * @param array $options new options
	 */
	public function update_options(array $options) {
		// do not allow to clear options
		if ( $options === array() ) {
			return;
		}

		$this->options = $options;
		update_option( ADVADS_SLUG . '-notices', $options );
	}

	/**
	 * subscribe to newsletter and autoresponder
	 *
	 * @since 1.5.3
	 * @param string $notice slug of the subscription notice to send the correct reply
	 */
	public function subscribe($notice) {
		if ( ! isset( $notice ) ) {
			return;
		}

		global $current_user;
		$user = wp_get_current_user();

		if ( $user->user_email == '' ) {
			return sprintf( __( 'You don’t seem to have an email address. Please use <a href="%s" target="_blank">this form</a> to sign up.', 'advanced-ads' ), 'http://eepurl.com/bk4z4P' );
		}

		$data = array(
			'email' => $user->user_email,
			'notice' => $notice
		);

		$result = wp_remote_post('https://wpadvancedads.com/remote/subscribe.php?source=plugin', array(
			'method' => 'POST',
			'timeout' => 20,
			'redirection' => 5,
			'httpversion' => '1.1',
			'blocking' => true,
			'body' => $data)
		);

		if ( is_wp_error( $result ) ) {
			return __( 'How embarrassing. The email server seems to be down. Please try again later.', 'advanced-ads' );
		} else {
			// mark as subscribed and move notice from quere
			$this->mark_as_subscribed();
			$this->remove_from_queue( $notice );
			return sprintf(__( 'Please check your email (%s) for the confirmation message. If you didn’t receive one or want to use another email address then please use <a href="%s" target="_blank">this form</a> to sign up.', 'advanced-ads' ), $user->user_email, 'http://eepurl.com/bk4z4P' );
		}
	}

	/**
	 * check if blog is subscribed to the newsletter
	 */
	public function is_subscribed() {

		/**
		 * respect previous settings
		 */
		$options = $this->options();
		if ( isset($options['is_subscribed'] ) ) {
		    return true;
		}

		$user_id = get_current_user_id();
		if( ! $user_id ) {
		    return true;
		}

		$subscribed = get_user_meta($user_id, 'advanced-ads-subscribed', true);
		return $subscribed;
	}
	
	/**
	 * check if a usesr can be subscribed to our newsletter
	 * check if is already subscribed or email is invalid
	 * 
	 * @return bool true if user can subscribe
	 */
	public function user_can_subscribe( ){
	    
		/**
		 * respect previous settings
		 */
		$options = $this->options();
		if ( isset($options['is_subscribed'] ) ) {
		    return true;
		}

		$current_user = wp_get_current_user();
		
		if( empty( $current_user->ID ) || empty( $current_user->user_email ) ) {
		    return false;
		}

		$subscribed = get_user_meta( $current_user->ID, 'advanced-ads-subscribed', true);
		
		// secureserver.net email address belong to GoDaddy (?) and have very, very low open rates. Seems like only temporary setup
		return ( ! $subscribed 
			&& is_email( $current_user->user_email ) 
			&& false === strpos( $current_user->user_email, 'secureserver.net' ) ) 
			? true : false;
		
	}

	/**
	 * update information that the current user is subscribed
	 */
	private function mark_as_subscribed() {

		$user_id = get_current_user_id();

		if( ! $this->is_subscribed() ) {
		    update_user_meta( $user_id, 'advanced-ads-subscribed', true);
		}
	}

	/**
	 * add AdSense tutorial notice
	 *
	 * @param obj $ad ad object
	 * @param arr $types ad types
	 */
	public function adsense_tutorial( $ad, $types = array() ){

	    $options = $this->options();
	    $_notice = 'nl_adsense';

	    if ( $ad->type !== 'adsense' || isset($options['closed'][ $_notice ] ) ) {
		return;
	    }

	    include ADVADS_BASE_PATH . '/admin/includes/notices.php';

	    if ( ! isset( $advanced_ads_admin_notices[ $_notice ] ) ) {
		return;
	    }

	    $notice = $advanced_ads_admin_notices[ $_notice ];
	    $text = $notice['text'];
	    include ADVADS_BASE_PATH . '/admin/views/notices/inline.php';
	}
	
	/**
	 * create the content of a welcome panel like WordPress core does
	 */
	public function get_welcome_panel(){
	    
	    ob_start();
	    include ADVADS_BASE_PATH . '/admin/views/notices/welcome-panel.php';
	    return ob_get_clean();
	    
	}
}
