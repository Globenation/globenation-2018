<?php
/**
 * Advanced Ads main admin class
 *
 * @package   Advanced_Ads_Admin
 * @author    Thomas Maier <thomas.maier@webgilde.com>
 * @license   GPL-2.0+
 * @link      https://wpadvancedads.com
 * @copyright since 2013 Thomas Maier, webgilde GmbH
 * 
 * Plugin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 */

class Advanced_Ads_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Instance of admin notice class.
	 *
	 * @since    1.5.2
	 * @var      object
	 */
	protected $notices = null;

	/**
	 * Slug of the settings page
	 *
	 * @since    1.0.0
	 * @var      string
	 */
	public $plugin_screen_hook_suffix = null;

	/**
	 * General plugin slug
	 *
	 * @since   1.0.0
	 * @var     string
	 */
	protected $plugin_slug = '';

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			new Advanced_Ads_Ad_Ajax_Callbacks();
			add_action( 'plugins_loaded', array( $this, 'wp_plugins_loaded_ajax' ) );
		} else {
			add_action( 'plugins_loaded', array( $this, 'wp_plugins_loaded' ) );
			add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), 100 );
			Advanced_Ads_Ad_List_Filters::get_instance();
		}
		// add shortcode creator to TinyMCE.
		Advanced_Ads_Shortcode_Creator::get_instance();
		Advanced_Ads_Admin_Licenses::get_instance();
	}

	/**
	 * License handling legacy code after moving license handling code to Advanced_Ads_Admin_Licenses
	 *
	 * @param   string $addon      slug of the add-on.
	 * @param   string $plugin_name    name of the add-on.
	 * @param   string $options_slug   slug of the options the plugin is saving in the options table.
	 * @since version 1.7.16 (early January 2017)
	 */
	public function deactivate_license( $addon = '', $plugin_name = '', $options_slug = '' ) {
		return Advanced_Ads_Admin_Licenses::get_instance()->deactivate_license( $addon, $plugin_name, $options_slug ); }

	/**
	 * Get license status.
	 *
	 * @param   string $slug   slug of the add-on.
	 * @return string   license status
	 */
	public function get_license_status( $slug = '' ) {
		return Advanced_Ads_Admin_Licenses::get_instance()->get_license_status( $slug ); }

	/**
	 * Actions and filter available after all plugins are initialized.
	 */
	public function wp_plugins_loaded() {
		/*
		 * Call $plugin_slug from public plugin class.
		 *
		 */
		$plugin            = Advanced_Ads::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		add_action( 'current_screen', array( $this, 'current_screen' ) );

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ), 9 );

		// update placements.
		add_action( 'admin_init', array( 'Advanced_Ads_Placements', 'update_placements' ) );

		// check for update logic.
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );

		// add links to plugin page.
		add_filter( 'plugin_action_links_' . ADVADS_BASE, array( $this, 'add_plugin_links' ) );

		// display information when user is going to disable the plugin.
		add_filter( 'admin_footer', array( $this, 'add_deactivation_logic' ) );
		// add_filter( 'after_plugin_row_' . ADVADS_BASE, array( $this, 'display_deactivation_message' ) );
		// disable adding rel="noopener noreferrer" to link added through TinyMCE for rich content ads.
		add_filter( 'tiny_mce_before_init', array( $this, 'tinymce_allow_unsafe_link_target' ) );

		add_action( 'plugins_api_result', array( $this, 'recommend_suitable_add_ons' ), 11, 3 );

		Advanced_Ads_Admin_Meta_Boxes::get_instance();
		Advanced_Ads_Admin_Menu::get_instance();
		Advanced_Ads_Admin_Ad_Type::get_instance();
		Advanced_Ads_Admin_Settings::get_instance();
	}

	/**
	 * Actions and filters that should also be available for ajax
	 */
	public function wp_plugins_loaded_ajax() {
		// needed here in order to work with Quick Edit option on ad list page.
		Advanced_Ads_Admin_Ad_Type::get_instance();

		add_action( 'wp_ajax_advads_send_feedback', array( $this, 'send_feedback' ) );
		add_action( 'wp_ajax_advads_load_rss_widget_content', array( 'Advanced_Ads_Admin_Meta_Boxes', 'dashboard_widget_function_output' ) );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * General stuff after page is loaded and screen variable is available
	 */
	public function current_screen() {
		$screen = get_current_screen();

		if ( ! isset( $screen->id ) ) {
			return;
		}

		switch ( $screen->id ) {
			case 'edit-advanced_ads': // ad overview page.
			case 'advanced_ads': // ad edit page.
				// remove notice about missing first ad.
				break;
		}
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     1.0.0
	 */
	public function enqueue_admin_styles() {
		wp_enqueue_style( $this->plugin_slug . '-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), ADVADS_VERSION );
		if ( self::screen_belongs_to_advanced_ads() ) {
			// jQuery ui smoothness style 1.11.4.
			wp_enqueue_style( $this->plugin_slug . '-jquery-ui-styles', plugins_url( 'assets/jquery-ui/jquery-ui.min.css', __FILE__ ), array(), '1.11.4' );
		}
	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     1.0.0
	 */
	public function enqueue_admin_scripts() {

		// global js script.
		wp_enqueue_script( $this->plugin_slug . '-admin-global-script', plugins_url( 'assets/js/admin-global.js', __FILE__ ), array( 'jquery' ), ADVADS_VERSION, false );
		wp_enqueue_script( $this->plugin_slug . '-admin-find-adblocker', plugins_url( 'assets/js/advertisement.js', __FILE__ ), array(), ADVADS_VERSION, false );

		// register ajax nonce.
		$params = array(
			'ajax_nonce' => wp_create_nonce( 'advanced-ads-admin-ajax-nonce' ),
		);
		wp_localize_script( $this->plugin_slug . '-admin-global-script', 'advadsglobal', $params );

		if ( self::screen_belongs_to_advanced_ads() ) {
			wp_register_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery', 'jquery-ui-autocomplete', 'jquery-ui-button' ), ADVADS_VERSION, false );
			wp_register_script( $this->plugin_slug . '-wizard-script', plugins_url( 'assets/js/wizard.js', __FILE__ ), array( 'jquery' ), ADVADS_VERSION, false );

			// jquery ui.
			wp_enqueue_script( 'jquery-ui-accordion' );
			wp_enqueue_script( 'jquery-ui-button' );
			wp_enqueue_script( 'jquery-ui-tooltip' );

			// just register this script for later inclusion on ad group list page.
			wp_register_script( 'inline-edit-group-ads', plugins_url( 'assets/js/inline-edit-group-ads.js', __FILE__ ), array( 'jquery' ), ADVADS_VERSION, false );

			$auto_ads_strings = Advanced_Ads_AdSense_Admin::get_auto_ads_messages();

			// register admin.js translations.
			$translation_array = array(
				'condition_or'           => __( 'or', 'advanced-ads' ),
				'condition_and'          => __( 'and', 'advanced-ads' ),
				'after_paragraph_promt'  => __( 'After which paragraph?', 'advanced-ads' ),
				'page_level_ads_enabled' => $auto_ads_strings['enabled'],
			);

			wp_localize_script( $this->plugin_slug . '-admin-script', 'advadstxt', $translation_array );

			wp_enqueue_script( $this->plugin_slug . '-admin-script' );
			wp_enqueue_script( $this->plugin_slug . '-wizard-script' );
		}

		// call media manager for image upload only on ad edit pages.
		$screen = get_current_screen();
		if ( isset( $screen->id ) && Advanced_Ads::POST_TYPE_SLUG === $screen->id ) {
			// the 'wp_enqueue_media' function can be executed only once and should be called with the 'post' parameter
			// in this case, the '_wpMediaViewsL10n' js object inside html will contain id of the post, that is necessary to view oEmbed priview inside tinyMCE editor.
			// since other plugins can call the 'wp_enqueue_media' function without the 'post' parameter, Advanced Ads should call it earlier.
			global $post;
			wp_enqueue_media( array( 'post' => $post ) );
		}

	}

	/**
	 * Check if the current screen belongs to Advanced Ads
	 *
	 * @since 1.6.6
	 * @return bool true if screen belongs to Advanced Ads
	 */
	public static function screen_belongs_to_advanced_ads() {

		if ( ! function_exists( 'get_current_screen' ) ) {
			return false;
		}

		$screen = get_current_screen();
		if ( ! isset( $screen->id ) ) {
			return false;
		}

		$advads_pages = apply_filters(
			'advanced-ads-dashboard-screens', array(
				'advanced-ads_page_advanced-ads-groups', // ad groups.
				'edit-advanced_ads', // ads overview.
				'advanced_ads', // ad edit page.
				'advanced-ads_page_advanced-ads-placements', // placements.
				'advanced-ads_page_advanced-ads-settings', // settings.
				'toplevel_page_advanced-ads', // overview.
				'admin_page_advanced-ads-debug', // debug.
			// 'advanced-ads_page_advanced-ads-support', // support.
				'admin_page_advanced-ads-import-export', // import & export.
			)
		);

		if ( in_array( $screen->id, $advads_pages, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get action from the params
	 *
	 * @since 1.0.0
	 */
	public function current_action() {
		$request = wp_unslash( $_REQUEST );
		if ( isset( $request['action'] ) && -1 !== $request['action'] ) {
			return $request['action'];
		}

		return false;
	}

	/**
	 *  Get DateTimeZone object for the WP installation
	 */
	public static function get_wp_timezone() {
		$_time_zone = get_option( 'timezone_string' );
		$time_zone  = new DateTimeZone( 'UTC' );
		if ( $_time_zone ) {
			$time_zone = new DateTimeZone( $_time_zone );
		} else {
			$gmt_offset = floatval( get_option( 'gmt_offset' ) );
			$sign       = ( 0 > $gmt_offset ) ? '-' : '+';
			$int        = floor( abs( $gmt_offset ) );
			$frac       = abs( $gmt_offset ) - $int;

			$gmt = '';
			if ( $gmt_offset ) {
				$gmt      .= $sign . zeroise( $int, 2 ) . ':' . zeroise( 60 * $frac, 2 );
				$time_zone = date_create( '2017-10-01T12:00:00' . $gmt )->getTimezone();
			}
		}
		return $time_zone;
	}

	/**
	 *  Get literal expression of timezone
	 *
	 *  @param DateTimeZone $date_time_zone the DateTimeZone object to get literal value from.
	 */
	public static function timezone_get_name( $date_time_zone ) {
		if ( $date_time_zone instanceof DateTimeZone ) {
			$time_zone = timezone_name_get( $date_time_zone );
			if ( 'UTC' === $time_zone ) {
				return 'UTC+0';
			}
			if ( false === strpos( $time_zone, '/' ) ) {
				$time_zone = 'UTC' . $time_zone;
			} else {
				// translators: time zone name.
				$time_zone = sprintf( __( 'time of %s', 'advanced-ads' ), $time_zone );
			}
			return $time_zone;
		}
		return 'UTC+0';
	}

	/**
	 * Initiate the admin notices class
	 *
	 * @since 1.5.3
	 */
	public function admin_notices() {
		// display ad block warning to everyone who can edit ads.
		if ( current_user_can( Advanced_Ads_Plugin::user_cap( 'advanced_ads_edit_ads' ) ) ) {
			if ( $this->screen_belongs_to_advanced_ads() ) {
				include ADVADS_BASE_PATH . 'admin/views/notices/adblock.php';
				include ADVADS_BASE_PATH . 'admin/views/notices/jqueryui_error.php';
			}
		}

		if ( current_user_can( Advanced_Ads_Plugin::user_cap( 'advanced_ads_edit_ads' ) ) ) {
			$this->notices = Advanced_Ads_Admin_Notices::get_instance()->notices;
			Advanced_Ads_Admin_Notices::get_instance()->display_notices();
		}
	}

	/**
	 * Add links to the plugins list
	 *
	 * @since 1.6.14
	 * @param arr $links array of links for the plugins, adapted when the current plugin is found.
	 * @return array $links
	 */
	public function add_plugin_links( $links ) {

		if ( ! is_array( $links ) ) {
			return $links;
		}

		// add link to support page.
		$support_link = '<a href="' . esc_url( admin_url( 'admin.php?page=advanced-ads-settings#top#support' ) ) . '">' . __( 'Support', 'advanced-ads' ) . '</a>';
		array_unshift( $links, $support_link );

		// add link to add-ons.
		$extend_link = '<a href="' . ADVADS_URL . 'add-ons/#utm_source=advanced-ads&utm_medium=link&utm_campaign=plugin-page" target="_blank">' . __( 'Add-Ons', 'advanced-ads' ) . '</a>';
		array_unshift( $links, $extend_link );

		return $links;
	}

	/**
	 * Display deactivation logic on plugins page
	 *
	 * @since 1.7.14
	 */
	public function add_deactivation_logic() {

		$screen = get_current_screen();
		if ( ! isset( $screen->id ) || ! in_array( $screen->id, array( 'plugins', 'plugins-network' ), true ) ) {
			return;
		}

		$current_user = wp_get_current_user();
		if ( ! ( $current_user instanceof WP_User ) ) {
			$from  = '';
			$email = '';
		} else {
			$from  = $current_user->user_nicename . ' <' . trim( $current_user->user_email ) . '>';
			$email = $current_user->user_email;
		}

		include ADVADS_BASE_PATH . 'admin/views/feedback-disable.php';
	}

	/**
	 * Send feedback via email
	 *
	 * @since 1.7.14
	 */
	public function send_feedback() {
		/**
		 * We first need to get the form data from the string and can verify the nonce afterwards
		 * This throws an issue with the WP Coding Standards though
		 */
		if ( isset( $_POST['formdata'] ) ) {
			parse_str( wp_unslash( $_POST['formdata'] ), $form );
		}

		if ( ! wp_verify_nonce( $form['advanced_ads_disable_form_nonce'], 'advanced_ads_disable_form' ) ) {
			die();
		}

		$text = '';
		if ( isset( $form['advanced_ads_disable_text'] ) ) {
			$text = implode( "\n\r", $form['advanced_ads_disable_text'] );
		}

		// get first version to see if this is a new problem or might be an older on.
		$options   = Advanced_Ads_Plugin::get_instance()->internal_options();
		$installed = isset( $options['installed'] ) ? date( 'd.m.Y', $options['installed'] ) : '–';

		$text .= "\n\n" . home_url() . " ($installed)";

		$headers = array();

		$from = isset( $form['advanced_ads_disable_from'] ) ? $form['advanced_ads_disable_from'] : '';
		// the user clicked on the "don’t disable" button or if an address is given in the form then use that one.
		if ( isset( $form['advanced_ads_disable_reason'] )
				&& 'get help' === $form['advanced_ads_disable_reason']
				&& ! empty( $form['advanced_ads_disable_reply_email'] ) ) {
			$email		= isset( $form['advanced_ads_disable_reply_email'] ) ? trim( $form['advanced_ads_disable_reply_email'] ) : $current_user->email;
			$current_user	= wp_get_current_user();
			$name		= ( $current_user instanceof WP_User ) ? $current_user->user_nicename : '';
			$from		= $name . ' <' . $email . '>';
			$is_german	=  ( preg_match( '/\.de$/', $from ) || "de_" === substr( get_locale(), 0, 3 ) || "de_" === substr( get_user_locale(), 0, 3 ) );
			if ( isset( $form['advanced_ads_disable_text'][0] )
				&& trim( $form['advanced_ads_disable_text'][0] ) !== '' ) { // is a text given then ask for help.
				// send German text
				if( $is_german ){
				    $text .= "\n\n Hilfe ist auf dem Weg.";
				} else {
				    $text .= "\n\n Help is on its way.";
				}
			} else { // if no text is given, just reply.
				if( $is_german ){
				    $text .= "\n\n Vielen Dank für das Feedback.";
				} else {
				    $text .= "\n\n Thank you for your feedback.";
				}
			}
		}
		if ( $from ) {
			$headers[] = "From: $from";
			$headers[] = "Reply-To: $from";
		}

		$subject = isset( $form['advanced_ads_disable_reason'] ) ? $form['advanced_ads_disable_reason'] : '(no reason given)';
		// append plugin name to get a better subject.
		$subject .= ' (Advanced Ads)';

		$success = wp_mail( 'improve@wpadvancedads.com', $subject, $text, $headers );

		die();

	}

	/**
	 * Configure TinyMCE to allow unsafe link target.
	 *
	 * @param boolean $mce_init the tinyMce initialization array.
	 * @return boolean
	 */
	public function tinymce_allow_unsafe_link_target( $mce_init ) {

		// check if we are on the ad edit screen.
		if ( ! function_exists( 'get_current_screen' ) ) {
			return $mce_init;
		}

		$screen = get_current_screen();
		if ( isset( $screen->id ) && 'advanced_ads' === $screen->id ) {
			$mce_init['allow_unsafe_link_target'] = true;
		}

		return $mce_init;
	}

	/**
	 * Sort visitor and display condition arrays alphabetically by their label.
	 *
	 * @param array $a array to be compared.
	 * @param array $b array to be compared.
	 * @since 1.8.12
	 */
	public static function sort_condition_array_by_label( $a, $b ) {
		if ( ! isset( $a['label'] ) || ! isset( $b['label'] ) ) {
			return;
		}
		return strcmp( strtolower( $a['label'] ), strtolower( $b['label'] ) );
	}

	/**
	 * Recommend additional add-ons
	 *
	 * @param object  $result original result object.
	 * @param unknown $action action.
	 * @param object  $args   additional arguments.
	 */
	public function recommend_suitable_add_ons( $result, $action, $args ) {
		if ( empty( $args->browse ) ) {
			return $result;
		}

		if ( 'featured' !== $args->browse && 'recommended' !== $args->browse && 'popular' !== $args->browse ) {
			return $result;
		}

		if ( ! isset( $result->info['page'] ) || 1 < $result->info['page'] ) {
			return $result;
		}

		// Recommend AdSense In-Feed add-on.
		if ( ! is_plugin_active( 'advanced-ads-adsense-in-feed/advanced-ads-in-feed.php' )
			&& ! is_plugin_active_for_network( 'advanced-ads-adsense-in-feed/advanced-ads-in-feed.php' ) ) {

			// Grab all slugs from the api results.
			$result_slugs = wp_list_pluck( $result->plugins, 'slug' );

			if ( in_array( 'advanced-ads-adsense-in-feed', $result_slugs, true ) ) {
				return $result;
			}

			$query_args  = array(
				'slug'   => 'advanced-ads-adsense-in-feed',
				'fields' => array(
					'icons'             => true,
					'active_installs'   => true,
					'short_description' => true,
					'group'             => true,
				),
			);
			$plugin_data = plugins_api( 'plugin_information', $query_args );

			if ( ! is_wp_error( $plugin_data ) ) {
				if ( 'featured' === $args->browse ) {
					array_push( $result->plugins, $plugin_data );
				} else {
					array_unshift( $result->plugins, $plugin_data );
				}
			}
		}

		// Recommend Genesis Ads add-on.
		if ( defined( 'PARENT_THEME_NAME' ) && 'Genesis' === PARENT_THEME_NAME
			&& ! is_plugin_active( 'advanced-ads-genesis/genesis-ads.php' )
			&& ! is_plugin_active_for_network( 'advanced-ads-genesis/genesis-ads.php' ) ) {

			// Grab all slugs from the api results.
			$result_slugs = wp_list_pluck( $result->plugins, 'slug' );

			if ( in_array( 'advanced-ads-genesis', $result_slugs, true ) ) {
				return $result;
			}

			$query_args  = array(
				'slug'   => 'advanced-ads-genesis',
				'fields' => array(
					'icons'             => true,
					'active_installs'   => true,
					'short_description' => true,
					'group'             => true,
				),
			);
			$plugin_data = plugins_api( 'plugin_information', $query_args );

			if ( ! is_wp_error( $plugin_data ) ) {
				if ( 'featured' === $args->browse ) {
					array_push( $result->plugins, $plugin_data );
				} else {
					array_unshift( $result->plugins, $plugin_data );
				}
			}
		}

		// Recommend WP Bakery (former Visual Composer) add-on.
		if ( defined( 'WPB_VC_VERSION' )
			&& ! is_plugin_active( 'ads-for-visual-composer/advanced-ads-vc.php' )
			&& ! is_plugin_active_for_network( 'ads-for-visual-composer/advanced-ads-vc.php' ) ) {

			// Grab all slugs from the api results.
			$result_slugs = wp_list_pluck( $result->plugins, 'slug' );

			if ( in_array( 'ads-for-visual-composer', $result_slugs, true ) ) {
				return $result;
			}

			$query_args  = array(
				'slug'   => 'ads-for-visual-composer',
				'fields' => array(
					'icons'             => true,
					'active_installs'   => true,
					'short_description' => true,
					'group'             => true,
				),
			);
			$plugin_data = plugins_api( 'plugin_information', $query_args );

			if ( ! is_wp_error( $plugin_data ) ) {
				if ( 'featured' === $args->browse ) {
					array_push( $result->plugins, $plugin_data );
				} else {
					array_unshift( $result->plugins, $plugin_data );
				}
			}
		}

		return $result;
	}

	/**
	 * Overrides WordPress text in Footer
	 *
	 * @param String $default_text The default footer text.
	 *
	 * @return string
	 */
	public function admin_footer_text( $default_text ) {
		if ( $this->screen_belongs_to_advanced_ads() ) {

			/* translators: %s is the URL to add a new review, https://wordpress.org/support/plugin/advanced-ads/reviews/?filter=5#new-post */
			return sprintf( __( 'Thank the developer with a &#9733;&#9733;&#9733;&#9733;&#9733; review on <a href="%s" target="_blank">wordpress.org</a>', 'advanced-ads' ), 'https://wordpress.org/support/plugin/advanced-ads/reviews/?filter=5#new-post' );

		}
		return $default_text;
	}

}
