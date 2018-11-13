<?php

/**
 * Wordpress integration and definitions:
 *
 * - posttypes
 * - taxonomy
 * - textdomain
 *
 * @since 1.5.0
 */
class Advanced_Ads_Plugin {
	/**
	 *
	 * @var Advanced_Ads_Plugin
	 */
	protected static $instance;

	/**
	 *
	 * @var Advanced_Ads_Model
	 */
	protected $model;

	/**
	 * plugin options
	 *
	 * @since   1.0.1
	 * @var     array (if loaded)
	 */
	protected $options;

	/**
	 * interal plugin options – set by the plugin
	 *
	 * @since   1.4.5
	 * @var     array (if loaded)
	 */
	protected $internal_options;

	/**
	 * default prefix of selectors (id, class) in the frontend
	 * can be changed by options
	 *
	 * @var Advanced_Ads_Plugin
	 */
	const DEFAULT_FRONTEND_PREFIX = 'advads-';

	/**
	 *
	 * @var frontend prefix for classes and IDs
	 */
	private $frontend_prefix;


	private function __construct() {
		register_activation_hook( ADVADS_BASE, array( $this, 'activate' ) );
		register_deactivation_hook( ADVADS_BASE, array( $this, 'deactivate' ) );
		register_uninstall_hook( ADVADS_BASE, array( 'Advanced_Ads_Plugin', 'uninstall' ) );

		add_action( 'plugins_loaded', array( $this, 'wp_plugins_loaded' ), 10 );
	}

	/**
	 *
	 * @return Advanced_Ads_Plugin
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 *
	 * @param Advanced_Ads_Model $model
	 */
	public function set_model(Advanced_Ads_Model $model) {
		$this->model = $model;
	}

	public function wp_plugins_loaded() {
		// Load plugin text domain
		$this->load_plugin_textdomain();
		
		$internal_options = $this->internal_options();
		
		/**
		 * run upgrades, if this is a new version or version does not exist
		 */
		if ( ! defined( 'DOING_AJAX' ) && ( ! isset( $internal_options['version'] ) || version_compare( $internal_options['version'], ADVADS_VERSION, '<' ) ) ) {
			new Advanced_Ads_Upgrades();
		}

		// activate plugin when new blog is added on multisites // -TODO this is admin-only
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_head', array( $this, 'print_head_scripts' ), 7 );

		// add short codes
		add_shortcode( 'the_ad', array( $this, 'shortcode_display_ad' ) );
		add_shortcode( 'the_ad_group', array( $this, 'shortcode_display_ad_group' ) );
		add_shortcode( 'the_ad_placement', array( $this, 'shortcode_display_ad_placement' ) );

		// remove default ad group menu item // -TODO only for admin
		add_action( 'admin_menu', array( $this, 'remove_taxonomy_menu_item' ) );
		// load widgets
		add_action( 'widgets_init', array( $this, 'widget_init' ) );
		
		// load display conditions
		Advanced_Ads_Display_Conditions::get_instance();
		new Advanced_Ads_Frontend_Checks;
		new Advanced_Ads_Compatibility;
	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		// wp_enqueue_style( $this->get_plugin_slug() . '-plugin-styles', plugins_url('assets/css/public.css', __FILE__), array(), ADVADS_VERSION);
	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
	    return ADVADS_SLUG;
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		// wp_enqueue_script( $this->get_plugin_slug() . '-plugin-script', plugins_url('assets/js/public.js', __FILE__), array('jquery'), ADVADS_VERSION);
		$options = $this->options();
		$activated_js = apply_filters( 'advanced-ads-activate-advanced-js', isset( $options['advanced-js'] ) );
		if ( $activated_js ){
			wp_enqueue_script( $this->get_plugin_slug() . '-advanced-js', ADVADS_BASE_URL . 'public/assets/js/advanced.js', array( 'jquery' ), ADVADS_VERSION );
		}
	}

	/**
	 * Print public-facing JavaScript in the HTML head.
	 *
	 * @since    untagged
	 */
	public function print_head_scripts() {
		/**
		 * Usage example in add-ons:
		 * ( window.advanced_ads_ready || jQuery( document ).ready ).call( null, function() {
		 *    // Called when DOM is ready.
		 * } );
		 */
		
		echo apply_filters( 'advanced-ads-attribution', sprintf( '<!-- managing ads with Advanced Ads – %s -->', ADVADS_URL ) ); 

		ob_start();
		?><script>
		<?php if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			readfile( ADVADS_BASE_PATH . 'public/assets/js/ready.js' );
		} else { ?>
			advanced_ads_ready=function(){var fns=[],listener,doc=typeof document==="object"&&document,hack=doc&&doc.documentElement.doScroll,domContentLoaded="DOMContentLoaded",loaded=doc&&(hack?/^loaded|^c/:/^loaded|^i|^c/).test(doc.readyState);if(!loaded&&doc){listener=function(){doc.removeEventListener(domContentLoaded,listener);window.removeEventListener("load",listener);loaded=1;while(listener=fns.shift())listener()};doc.addEventListener(domContentLoaded,listener);window.addEventListener("load",listener)}return function(fn){loaded?setTimeout(fn,0):fns.push(fn)}}();
			<?php
		}

		// Output privacy options.
		$privacy_options = Advanced_Ads_Privacy::get_instance()->options();
		if ( ! empty( $privacy_options['enabled'] ) ) {
			printf( '(advads_options = window.advads_options || {} )["privacy"] = %s;', json_encode( $privacy_options ) );
		}

		?></script><?php
		echo Advanced_Ads_Utils::get_inline_asset( ob_get_clean() );


	}

	public function widget_init() {
		register_widget( 'Advanced_Ads_Widget' );
	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site($blog_id) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		$this->single_activate();
		restore_current_blog();
	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	protected function single_activate() {
		// $this->post_types_rewrite_flush();
		// -TODO inform modules
		$this->create_capabilities();
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	protected function single_deactivate() {
		// -TODO inform modules
		$this->remove_capabilities();
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {
		// $locale = apply_filters('advanced-ads-plugin-locale', get_locale(), $domain);
		load_plugin_textdomain( 'advanced-ads', false, ADVADS_BASE_DIR . '/languages' );
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public function activate($network_wide) {
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {
				// Get all blog ids
				global $wpdb;
				$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );
				$original_blog_id = $wpdb->blogid;

				foreach ( $blog_ids as $blog_id ) {
					switch_to_blog( $blog_id );
					$this->single_activate();
				}

				switch_to_blog( $original_blog_id );
			} else {
				$this->single_activate();
			}
		} else {
			$this->single_activate();
		}
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 * @param    boolean    $network_wide
	 *
	 * True if WPMU superadmin uses
	 * "Network Deactivate" action, false if
	 * WPMU is disabled or plugin is
	 * deactivated on an individual blog.
	 */
	public function deactivate($network_wide) {
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {
				// Get all blog ids
				global $wpdb;
				$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );
				$original_blog_id = $wpdb->blogid;

				foreach ( $blog_ids as $blog_id ) {
					switch_to_blog( $blog_id );
					$this->single_deactivate();
				}

				switch_to_blog( $original_blog_id );
			} else {
				$this->single_deactivate();
			}
		} else {
			$this->single_deactivate();
		}
	}

	/**
	 * flush rewrites on plugin activation so permalinks for them work from the beginning on
	 *
	 * @since 1.0.0
	 * @link http://codex.wordpress.org/Function_Reference/register_post_type#Flushing_Rewrite_on_Activation
	 */
	/*public function post_types_rewrite_flush(){
		// load custom post type
		Advanced_Ads::get_instance()->create_post_types();
		// flush rewrite rules
		flush_rewrite_rules();
	}*/

	/**
	 * remove WP tag edit page for the ad group taxonomy
	 *  needed, because we can’t remove it with `show_ui` without also removing the meta box
	 *
	 * @since 1.0.0
	 */
	public function remove_taxonomy_menu_item() {
		remove_submenu_page( 'edit.php?post_type=advanced_ads', 'edit-tags.php?taxonomy=advanced_ads_groups&amp;post_type=advanced_ads' );
	}

	/**
	 * shortcode to include ad in frontend
	 *
	 * @since 1.0.0
	 * @param arr $atts
	 */
	public function shortcode_display_ad($atts){
		$atts = is_array( $atts ) ? $atts : array();
		$id = isset($atts['id']) ? (int) $atts['id'] : 0;
		$atts = $this->prepare_shortcode_atts( $atts );

		// use the public available function here
		return get_ad( $id, $atts );
	}

	/**
	 * shortcode to include ad from an ad group in frontend
	 *
	 * @since 1.0.0
	 * @param arr $atts
	 */
	public function shortcode_display_ad_group($atts){
		$atts = is_array( $atts ) ? $atts : array();
		$id = isset($atts['id']) ? (int) $atts['id'] : 0;
		$atts = $this->prepare_shortcode_atts( $atts );

		// use the public available function here
		return get_ad_group( $id, $atts );
	}

	/**
	 * shortcode to display content of an ad placement in frontend
	 *
	 * @since 1.1.0
	 * @param arr $atts
	 */
	public function shortcode_display_ad_placement($atts){
		$atts = is_array( $atts ) ? $atts : array();
		$id = isset($atts['id']) ? (string) $atts['id'] : '';
		$atts = $this->prepare_shortcode_atts( $atts );

		// use the public available function here
		return get_ad_placement( $id, $atts );
	}

	/**
	 * Prepare shortcode attributes.
	 *
	 * @param array $atts array with strings
	 * @return array
	 */
	private function prepare_shortcode_atts( $atts ) {
		$result = array();

		/**
		 * Prepare attributes by converting strings to multi-dimensional array
		 * Example: [ 'output__margin__top' => 1 ]  =>  ['output']['margin']['top'] = 1
		 */
		if ( ! defined( 'ADVANCED_ADS_DISABLE_CHANGE' ) || ! ADVANCED_ADS_DISABLE_CHANGE ) {
			foreach ( $atts as $attr => $data  ) {
				$levels = explode( '__', $attr );
				$last = array_pop( $levels );

				$cur_lvl = &$result;

				foreach ( $levels as $lvl ) {
					if ( ! isset( $cur_lvl[ $lvl ] ) ) {
						$cur_lvl[ $lvl ] = array();
					}

					$cur_lvl = &$cur_lvl[ $lvl ];
				}

				$cur_lvl[ $last ] = $data;
			}

			$result = array_diff_key( $result, array( 'id' => false, 'blog_id' => false, 'ad_args' => false ) );
		}

		// Ad type: 'content' and a shortcode inside.
		if ( isset( $atts['ad_args'] ) ) {
			$result = array_merge( $result, json_decode( urldecode( $atts['ad_args'] ) ,true) );

		}

		return $result;
	}

	/**
	 * return plugin options
	 * these are the options updated by the user
	 *
	 * @since 1.0.1
	 * @return array $options
	 * @todo parse default options
	 */
	public function options() {
		if ( ! isset( $this->options ) ) {
			$this->options = get_option( ADVADS_SLUG, array() );
		}

		return $this->options;
	}

	/**
	 * update plugin options (not for settings page, but if automatic options are needed)
	 *
	 * @since 1.5.1
	 * @param array $options new options
	 */
	public function update_options( array $options ) {
		// do not allow to clear options
		if ( $options === array() ) {
			return;
		}

		$this->options = $options;
		update_option( ADVADS_SLUG, $options );
	}

	/**
	 * return internal plugin options
	 * these are options set by the plugin
	 *
	 * @since 1.0.1
	 * @return array $options
	 * @todo parse default options
	 */
	public function internal_options() {
		if ( ! isset( $this->internal_options ) ) {
		    $defaults = array(
			'version' => ADVADS_VERSION,
			'installed' => time(), // when was this installed
		    );
		    $this->internal_options = get_option( ADVADS_SLUG . '-internal', array() );

		    // save defaults
		    if($this->internal_options === array()){
			$this->internal_options = $defaults;
			$this->update_internal_options($this->internal_options);

			Advanced_Ads_Plugin::get_instance()->create_capabilities();
		    }

		    // for versions installed prior to 1.5.3 set installed date for now
		    if( ! isset( $this->internal_options['installed'] )){
			$this->internal_options['installed'] = time();
			$this->update_internal_options($this->internal_options);
		    }
		}

		return $this->internal_options;
	}

	/**
	 * update internal plugin options
	 *
	 * @since 1.5.1
	 * @param array $options new internal options
	 */
	public function update_internal_options( array $options ) {
		// do not allow to clear options
		if ( $options === array() ) {
			return;
		}

		$this->internal_options = $options;
		update_option( ADVADS_SLUG . '-internal', $options );
	}

	/**
	 * get prefix used for frontend elements
	 *
	 * @since 1.6.8.2
	 */
	public function get_frontend_prefix(){
		if ( ! $this->frontend_prefix ) {
			$options = $this->options();

			if ( ! isset( $options['front-prefix'] ) ) {
				if ( isset( $options['id-prefix'] ) ) {
					// deprecated: keeps widgets working that previously received an id based on the front-prefix
					$this->frontend_prefix = esc_attr( $options['id-prefix'] );
				} else {
					$host  = parse_url( get_home_url(), PHP_URL_HOST );
					$this->frontend_prefix = preg_match( '/[A-Za-z][A-Za-z0-9_]{4}/', $host, $result ) ? $result[0] . '-' : Advanced_Ads_Plugin::DEFAULT_FRONTEND_PREFIX;
				}
			} else {
				$this->frontend_prefix = esc_attr( $options['front-prefix'] );
			}
        }
		return $this->frontend_prefix;
	}

	/**
	 * get priority used for injection inside content
	 *
	 * @since 1.6.10.2
	 */
	public function get_content_injection_priority(){
		$options = $this->options();

		return isset( $options['content-injection-priority'] ) ? intval( $options['content-injection-priority'] ) : 100;
	}
	
	/**
	 * returns the capability needed to perform an action
	 * 
	 * @since 1.6.14
	 * @param str $capability a capability to check, can be internal to Advanced Ads
	 * @return str $capability a valid WordPress capability
	 */
	public static function user_cap( $capability = 'manage_options' ){
		
		global $advanced_ads_capabilities;
		
		// admins can do everything
		// is also a fallback if no option or more specific capability is given
		if( current_user_can( 'manage_options' ) ){
			return 'manage_options';
		}
		
		return apply_filters( 'advanced-ads-capability', $capability );
		
		// check, if capability is mapped to an existing WP capability
		/*if( isset( $advanced_ads_capabilities[ $capability ] ) ){
			return apply_filters( 'advanced-ads-capability', $advanced_ads_capabilities[ $capability ], $capability );
		} else {
			// if not, use 'manage_posts' capability
			return apply_filters( 'advanced-ads-capability', 'manage_options', $capability );
		}*/
		
	}

	/**
	 * Create roles and capabilities
	 *
	 */
	public function create_capabilities() {
		if ( $role = get_role( 'administrator' ) ) {
			$role->add_cap( 'advanced_ads_manage_options' );
			$role->add_cap( 'advanced_ads_see_interface' );
			$role->add_cap( 'advanced_ads_edit_ads' );
			$role->add_cap( 'advanced_ads_manage_placements' );
			$role->add_cap( 'advanced_ads_place_ads' );
		}
	}

	/**
	 * Remove roles and capabilities
	 *
	 */
	public function remove_capabilities() {
		if ( $role = get_role( 'administrator' ) ) {
			$role->remove_cap( 'advanced_ads_manage_options' );
			$role->remove_cap( 'advanced_ads_see_interface' );
			$role->remove_cap( 'advanced_ads_edit_ads' );
			$role->remove_cap( 'advanced_ads_manage_placements' );
			$role->remove_cap( 'advanced_ads_place_ads' );
		}
	}

	/**
	 * Fired when the plugin is uninstalled.
	 */
	public static function uninstall() {
		$advads_options = Advanced_Ads::get_instance()->options();

		if ( ! empty( $advads_options['uninstall-delete-data'] ) ) {
			global $wpdb;
			$main_blog_id = $wpdb->blogid;

			Advanced_Ads::get_instance()->create_post_types();

			if ( ! is_multisite() ) {
				Advanced_Ads_Plugin::get_instance()->uninstall_single();
			} else {
				$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );

				foreach ( $blog_ids as $blog_id ) {
					switch_to_blog( $blog_id );
					Advanced_Ads_Plugin::get_instance()->uninstall_single();
				}
				switch_to_blog( $main_blog_id );
			}

			// Delete assets (main blog).
			Advanced_Ads_Ad_Blocker_Admin::get_instance()->clear_assets();
			delete_option( ADVADS_AB_SLUG );
		}

	}

	/**
	 * Fired for each blog when the plugin is uninstalled.
	 *
	 */
	protected function uninstall_single() {
		global $wpdb;

		// Ads.
		$post_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s", Advanced_Ads::POST_TYPE_SLUG ) );

		if ( $post_ids ) {
			$wpdb->delete(
				$wpdb->posts,
				array( 'post_type' => Advanced_Ads::POST_TYPE_SLUG ),
				array( '%s' )
			);

			$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE post_id IN( " . implode( ',', $post_ids ) . " )" );
		}

		// Groups.
		$term_ids = $wpdb->get_col( $wpdb->prepare( "SELECT t.term_id FROM {$wpdb->terms} AS t INNER JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy = %s", Advanced_Ads::AD_GROUP_TAXONOMY ) );

		foreach ( $term_ids as $term_id ) {
			wp_delete_term( $term_id, Advanced_Ads::AD_GROUP_TAXONOMY );
		}

		delete_option( 'advads-ad-groups' );
		delete_option( Advanced_Ads::AD_GROUP_TAXONOMY . '_children' );
		delete_option( 'advads-ad-weights' );

		// Placements.
		delete_option( 'advads-ads-placements' );

		// User metadata.
		delete_metadata( 'user', null, 'advanced-ads-hide-wizard', '', true );
		delete_metadata( 'user', null, 'advanced-ads-subscribed', '', true );

		// Post metadata.
		delete_metadata( 'post', null, '_advads_ad_settings', '', true );

		// Transients.
		delete_transient( ADVADS_SLUG . '_add-on-updates-checked' );

		delete_option( GADSENSE_OPT_NAME );
		delete_option( ADVADS_SLUG );
		delete_option( ADVADS_SLUG . '-internal' );
		delete_option( ADVADS_SLUG . '-notices' );

		// Widget.
		$base_widget_id = Advanced_Ads_Widget::get_base_id();
		delete_option( 'widget_' . $base_widget_id );

		do_action( 'advanced-ads-uninstall' );

		wp_cache_flush();
	}

}
