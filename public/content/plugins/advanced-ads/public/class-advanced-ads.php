<?php
/**
 * Advanced Ads.
 *
 * @package   Advanced_Ads_Admin
 * @author    Thomas Maier <thomas.maier@webgilde.com>
 * @license   GPL-2.0+
 * @link      http://webgilde.com
 * @copyright 2013-2018 Thomas Maier, webgilde GmbH
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * @package Advanced_Ads
 * @author  Thomas Maier <thomas.maier@webgilde.com>
 */
class Advanced_Ads {

	/**
	 * Post type slug
	 *
	 * @since   1.0.0
	 * @var     string
	 */
	const POST_TYPE_SLUG = 'advanced_ads';

	/**
	 * Ad group slug
	 *
	 * @since   1.0.0
	 * @var     string
	 */
	const AD_GROUP_TAXONOMY = 'advanced_ads_groups';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 * @var      object
	 */
	private static $instance = null;

	/**
	 * Array with ads currently delivered in the frontend
	 *
	 * @var array Ads already loaded in the frontend
	 */
	public $current_ads = array();

	/**
	 * Ad types
	 *
	 * @var array Ad types
	 */
	public $ad_types = array();

	/**
	 * Plugin options
	 *
	 * @since   1.0.1
	 * @var     array (if loaded)
	 */
	protected $options = false;

	/**
	 * Interal plugin options – set by the plugin
	 *
	 * @since   1.4.5
	 * @var     array (if loaded)
	 */
	protected $internal_options = false;

	/**
	 * List of bots and crawlers to exclude from ad impressions
	 *
	 * @since 1.4.9
	 * @var array list of bots
	 */
	protected $bots = array( 'bot', 'spider', 'crawler', 'scraper', 'parser', '008', 'Accoona-AI-Agent', 'ADmantX', 'alexa', 'appie', 'Apple-PubSub', 'Arachmo', 'Ask Jeeves', 'avira\.com', 'B-l-i-t-z-B-O-T', 'boitho\.com-dc', 'BUbiNG', 'Cerberian Drtrs', 'Charlotte', 'cosmos', 'Covario IDS', 'curl', 'DataparkSearch', 'DDG-Android', 'expo9', 'facebookexternalhit', 'Feedfetcher-Google', 'FindLinks', 'Firefly', 'froogle', 'Genieo', 'heritrix', 'Holmes', 'htdig', 'https://developers\.google\.com', 'ia_archiver', 'ichiro', 'igdeSpyder', 'InfoSeek', 'inktomi', 'Kraken', 'L\.webis', 'Larbin', 'Linguee', 'LinkWalker', 'looksmart', 'lwp-trivial', 'mabontland', 'Mnogosearch', 'mogimogi', 'Morning Paper', 'MVAClient', 'NationalDirectory', 'NetResearchServer', 'NewsGator', 'NG-Search', 'Nusearch', 'NutchCVS', 'Nymesis', 'oegp', 'Orbiter', 'Peew', 'Pompos', 'PostPost', 'proximic', 'PycURL', 'Qseero', 'rabaz', 'Radian6', 'Reeder', 'savetheworldheritage', 'SBIder', 'Scooter', 'ScoutJet', 'Scrubby', 'SearchSight', 'semanticdiscovery', 'Sensis', 'ShopWiki', 'silk', 'Snappy', 'Spade', 'Sqworm', 'StackRambler', 'TechnoratiSnoop', 'TECNOSEEK', 'Teoma', 'Thumbnail\.CZ', 'TinEye', 'truwoGPS', 'updated', 'Vagabondo', 'voltron', 'Vortex', 'voyager', 'VYU2', 'WebBug', 'webcollage', 'WebIndex', 'Websquash\.com', 'WeSEE:Ads', 'wf84', 'Wget', 'WomlpeFactory', 'WordPress', 'yacy', 'Yahoo! Slurp', 'Yahoo! Slurp China', 'YahooSeeker', 'YahooSeeker-Testing', 'YandexBot', 'YandexMedia', 'YandexBlogs', 'YandexNews', 'YandexCalendar', 'YandexImages', 'Yeti', 'yoogliFetchAgent', 'Zao', 'ZyBorg', 'okhttp', 'ips-agent', 'ltx71', 'Optimizer', 'Daum', 'Qwantify' );

	/**
	 * Loaded instance of Advanced_Ads_Model
	 *
	 * @var Advanced_Ads_Model
	 */
	protected $model;

	/**
	 * Loaded instance of Advanced_Ads_Plugin
	 *
	 * @var Advanced_Ads_Plugin
	 */
	protected $plugin;

	/**
	 * Loaded instance of Advanced_Ads_Select
	 *
	 * @var Advanced_Ads_Select
	 */
	protected $ad_selector;

	/**
	 * Is the query the main query?, when WP_Query is used
	 *
	 * @var bool
	 */
	private $is_main_query;

	/**
	 * Save number of ads
	 *
	 * @var int
	 */
	private $number_of_ads = false;

	/**
	 * Initialize frontend features
	 */
	private function __construct() {
		$this->plugin = Advanced_Ads_Plugin::get_instance();
		$this->plugin->set_model( $this->get_model() );
		$this->ad_selector = Advanced_Ads_Select::get_instance();

		// initialize plugin specific functions.
		add_action( 'init', array( $this, 'wp_init' ) );

		// only when not doing ajax.
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			Advanced_Ads_Ajax::get_instance();
		}
		add_action( 'plugins_loaded', array( $this, 'wp_plugins_loaded' ) );

		// allow add-ons to interact.
		add_action( 'init', array( $this, 'advanced_ads_loaded' ), 9 );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 * @return    Advanced_Ads    A single instance of this class.
	 */
	public static function get_instance() {

		// if the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Return the Advanced_Ads_Model responsible for loading ads, groups and placements into the frontend
	 *
	 * @return Advanced_Ads_Model
	 */
	public function get_model() {

		global $wpdb;

		if ( ! isset( $this->model ) ) {
			$this->model = new Advanced_Ads_Model( $wpdb );
		}

		return $this->model;
	}

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	public function wp_plugins_loaded() {
		// register hook for global constants.
		add_action( 'wp', array( $this, 'set_disabled_constant' ) );

		// setup default ad types.
		add_filter( 'advanced-ads-ad-types', array( $this, 'setup_default_ad_types' ), 5 );

		// register hooks and filters for auto ad injection.
		$this->init_injection();

		// manipulate sidebar widget.
		add_filter( 'dynamic_sidebar_params', array( $this, 'manipulate_widget_output' ) );

		// add meta robots noindex, nofollow to images, which are part of 'Image ad' ad type.
		add_action( 'wp_head', array( $this, 'noindex_attachment_images' ) );
		
		// use custom CSS or other custom header code.
		add_action( 'wp_head', array( $this, 'custom_header_code' ) );

		// check if ads are disabled in secondary queries.
		add_action( 'the_post', array( $this, 'set_query_type' ), 10, 2 );

	}

	/**
	 *  Allow add-ons to hook
	 */
	public function advanced_ads_loaded() {
		do_action( 'advanced-ads-plugin-loaded' );
	}

	/**
	 * Init / load plugin specific functions and settings
	 *
	 * @since 1.0.0
	 */
	public function wp_init() {
		// load ad post types.
		$this->create_post_types();
		// set ad types array.
		$this->set_ad_types();
	}

	/**
	 * Define ad types with their options
	 * name => publically readable name
	 * description => publically readable description
	 * editor => kind of editor: text (normal text field), content (WP content field), none (no content field)
	 *  will display text field, if left empty
	 *
	 * @since 1.0.0
	 */
	public function set_ad_types() {

		/**
		 * Load default ad type files
		 * custom ad types can also be loaded in your own plugin or functions.php
		 */
		$types = array();

		/**
		 * Developers can add new ad types using this filter
		 * see classes/ad-type-content.php for an example for an ad type and usage of this filter
		*/
		$this->ad_types = apply_filters( 'advanced-ads-ad-types', $types );
	}

	/**
	 * Load filters to inject ads into various sections of our site
	 */
	public function init_injection() {
		// -TODO abstract
		add_action( 'wp_head', array( $this, 'inject_header' ), 20 );
		add_action( 'wp_footer', array( $this, 'inject_footer' ), 20 );
		add_filter( 'the_content', array( $this, 'inject_content' ), $this->plugin->get_content_injection_priority() );
	}

	/**
	 * Set global constant that prevents ads from being displayed on the current page view
	 *
	 * @since 1.3.10
	 */
	public function set_disabled_constant() {

		global $post, $wp_the_query;

		// don't set the constant if already defined.
		if ( defined( 'ADVADS_ADS_DISABLED' ) ) {
			return; }

		$options = $this->plugin->options();

		// check if ads are disabled completely.
		if ( ! empty( $options['disabled-ads']['all'] ) ) {
			define( 'ADVADS_ADS_DISABLED', true );
			return;
		}

		// check if ads are disabled from 404 pages.
		if ( $wp_the_query->is_404() && ! empty( $options['disabled-ads']['404'] ) ) {
			define( 'ADVADS_ADS_DISABLED', true );
			return;
		}

		// check if ads are disabled from non singular pages (often = archives).
		if ( ! $wp_the_query->is_singular() && ! empty( $options['disabled-ads']['archives'] ) ) {
			define( 'ADVADS_ADS_DISABLED', true );
			return;
		}

		// check if ads are disabled in Feed.
		if ( $wp_the_query->is_feed() && ( ! isset( $options['disabled-ads']['feed'] ) || $options['disabled-ads']['feed'] ) ) {
			define( 'ADVADS_ADS_DISABLED', true );
			return;
		}

		// check if ads are disabled on the current page.
		if ( $wp_the_query->is_singular() && isset( $post->ID ) ) {
			$post_ad_options = get_post_meta( $post->ID, '_advads_ad_settings', true );

			if ( ! empty( $post_ad_options['disable_ads'] ) ) {
				define( 'ADVADS_ADS_DISABLED', true );
			}
		};

		/**
		 * Check if ads are disabled on WooCommerce shop page (and currently on shop page).
		 * since WooCommerce changes the post ID of the static page selected to be the product overview page, we need to get the original page id from the WC options.
		 */
		if ( function_exists( 'is_shop' ) && is_shop() ) {
			$shop_id         = get_option( 'woocommerce_shop_page_id' );
			$shop_ad_options = get_post_meta( absint( $shop_id ), '_advads_ad_settings', true );
			if ( ! empty( $shop_ad_options['disable_ads'] ) ) {
				define( 'ADVADS_ADS_DISABLED', true );
			}
		}
	}

	/**
	 * Return the plugin slug.
	 *
	 * @since  1.0.0
	 * @return Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin->get_plugin_slug();
	}

	/**
	 * Add plain and content ad types to the default ads of the plugin using a filter
	 *
	 * @param arr $types array with ad types.
	 * @since 1.0.0
	 */
	public function setup_default_ad_types( $types ) {
		$types['plain']   = new Advanced_Ads_Ad_Type_Plain(); /* plain text and php code */
		$types['dummy']   = new Advanced_Ads_Ad_Type_Dummy(); /* dummy ad */
		$types['content'] = new Advanced_Ads_Ad_Type_Content(); /* rich content editor */
		$types['image']   = new Advanced_Ads_Ad_Type_Image(); /* image ads */
		$types['group']   = new Advanced_Ads_Ad_Type_Group(); /* group ad */
		return $types;
	}

	/**
	 * Log error messages when debug is enabled
	 *
	 * @param string $message error message.
	 * @since 1.0.0
	 * @link http://www.smashingmagazine.com/2011/03/08/ten-things-every-wordpress-plugin-developer-should-know/
	 */
	public static function log( $message ) {
		if ( true === WP_DEBUG ) {
			if ( is_array( $message ) || is_object( $message ) ) {
				error_log( __( 'Advanced Ads Error following:', 'advanced-ads' ) );
				error_log( print_r( $message, true ) );
			} else {
				// translators: %s is an error message generated by the plugin.
				$message = sprintf( __( 'Advanced Ads Error: %s', 'advanced-ads' ), $message );
				error_log( $message );
			}
		}
	}

	/**
	 * Compat method
	 *
	 * @return array with plugin options
	 */
	public function options() {
		return $this->plugin->options();
	}

	/**
	 * Compat method
	 *
	 * @return array with internal plugin options
	 */
	public function internal_options() {
		return $this->plugin->internal_options();
	}

	/**
	 * Injected ad into header
	 *
	 * @since 1.1.0
	 */
	public function inject_header() {
		$placements = get_option( 'advads-ads-placements', array() );
		if ( is_array( $placements ) ) {
			foreach ( $placements as $_placement_id => $_placement ) {
				if ( isset( $_placement['type'] ) && 'header' === $_placement['type'] ) {
					$_options = isset( $_placement['options'] ) ? $_placement['options'] : array();
					echo Advanced_Ads_Select::get_instance()->get_ad_by_method( $_placement_id, Advanced_Ads_Select::PLACEMENT, $_options );
				}
			}
		}
	}

	/**
	 * Injected ads into footer
	 *
	 * @since 1.1.0
	 */
	public function inject_footer() {
		$placements = get_option( 'advads-ads-placements', array() );
		if ( is_array( $placements ) ) {
			foreach ( $placements as $_placement_id => $_placement ) {
				if ( isset( $_placement['type'] ) && 'footer' === $_placement['type'] ) {
					$_options = isset( $_placement['options'] ) ? $_placement['options'] : array();
					echo Advanced_Ads_Select::get_instance()->get_ad_by_method( $_placement_id, Advanced_Ads_Select::PLACEMENT, $_options );
				}
			}
		}
	}

	/**
	 * Injected ad into content (before and after)
	 * Displays ALL ads
	 *
	 * @since 1.1.0
	 * @param str $content post content.
	 */
	public function inject_content( $content = '' ) {
		$options = $this->plugin->options();

		// do not inject in content when on a BuddyPress profile upload page (avatar & cover image).
		if ( ( function_exists( 'bp_is_user_change_avatar' ) && bp_is_user_change_avatar() ) || ( function_exists( 'bp_is_user_change_cover_image' ) && bp_is_user_change_cover_image() ) ) {
			return $content;
		}

		// check if ads are disabled in secondary queries and this function was called by ajax (in secondary query).
		if ( ! empty( $options['disabled-ads']['secondary'] ) && ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return $content;
		}

		// run only within the loop on single pages of public post types.
		$public_post_types = get_post_types(
			array(
				'public'             => true,
				'publicly_queryable' => true,
			), 'names', 'or'
		);

		// make sure that no ad is injected into another ad.
		if ( get_post_type() === self::POST_TYPE_SLUG ) {
			return $content;
		}

		// check if admin allows injection in all places.
		if ( ! isset( $options['content-injection-everywhere'] ) || 0 === $options['content-injection-everywhere'] ) {
					// check if this is a singular page within the loop or an AMP page.
					$is_amp = advads_is_amp();
			if ( ( ! is_singular( $public_post_types ) && ! is_feed() ) || ( ! $is_amp && ! in_the_loop() ) ) {
				return $content; }
		} else {
					global $wp_query;
			if ( is_main_query() && 'true' !== $options['content-injection-everywhere'] && isset( $wp_query->current_post ) && $wp_query->current_post >= ( $options['content-injection-everywhere'] ) ) {
				return $content;
			}
		}

		$placements = get_option( 'advads-ads-placements', array() );

		if ( ! apply_filters( 'advanced-ads-can-inject-into-content', true, $content, $placements ) ) {
			return $content;
		}

		if ( is_array( $placements ) ) {
			foreach ( $placements as $_placement_id => $_placement ) {
				if ( empty( $_placement['item'] ) || ! isset( $_placement['type'] ) ) {
					continue; }
				$_options = isset( $_placement['options'] ) ? $_placement['options'] : array();

				// check if injection is ok for a specific placement ID.
				if ( ! apply_filters( 'advanced-ads-can-inject-into-content-' . $_placement_id, true, $content, $_placement_id ) ) {
					continue;
				}

				switch ( $_placement['type'] ) {
					case 'post_top':
						$content = Advanced_Ads_Select::get_instance()->get_ad_by_method( $_placement_id, Advanced_Ads_Select::PLACEMENT, $_options ) . $content;
						break;
					case 'post_bottom':
						$content .= Advanced_Ads_Select::get_instance()->get_ad_by_method( $_placement_id, Advanced_Ads_Select::PLACEMENT, $_options );
						break;
					case 'post_content':
						$content = Advanced_Ads_Placements::inject_in_content( $_placement_id, $_options, $content );
						break;
				}
			}
		}

		return $content;
	}

	/**
	 * Load all ads based on WP_Query conditions
	 *
	 * @deprecated 1.4.8 use model class
	 * @since 1.1.0
	 * @param arr $args WP_Query arguments that are more specific that default.
	 * @return arr $ads array with post objects
	 */
	public static function get_ads( $args = array() ) {
		return self::get_instance()->get_model()->get_ads( $args );
	}

	/**
	 * Load all ad groups
	 *
	 * @deprecated 1.4.8 use model class
	 * @since 1.1.0
	 * @param arr $args array with options.
	 * @return arr $groups array with ad groups
	 * @link http://codex.wordpress.org/Function_Reference/get_terms
	 */
	public static function get_ad_groups( $args = array() ) {
		return self::get_instance()->get_model()->get_ad_groups( $args );
	}

	/**
	 * Get the array with ad placements
	 *
	 * @since 1.1.0
	 * @deprecated 1.4.8 use model
	 * @return arr $ad_placements
	 */
	public static function get_ad_placements_array() {
		return self::get_instance()->get_model()->get_ad_placements_array();
	}

	/**
	 * Get ad conditions.
	 *
	 * @deprecated 1.4.8 use model
	 * @return array
	 */
	public static function get_ad_conditions() {
		return self::get_instance()->get_model()->get_ad_conditions();
	}

	/**
	 * General check if ads can be displayed for the whole page impression
	 *
	 * @since 1.4.9
	 * @return bool true, if ads can be displayed.
	 * @todo move this to set_disabled_constant().
	 */
	public function can_display_ads() {

		// check global constant if ads are enabled or disabled.
		if ( defined( 'ADVADS_ADS_DISABLED' ) ) {
			return false;
		}

		$options = $this->options();

		// check if ads are disabled in secondary queries.
		// and this is not main query and this is not ajax (because main query does not exist in ajax but ad needs to be shown).
		if ( ! empty( $options['disabled-ads']['secondary'] ) && ! $this->is_main_query() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return false;
		}

		$see_ads_capability = isset( $options['hide-for-user-role'] ) && '' !== $options['hide-for-user-role'] ? $options['hide-for-user-role'] : false;

		// check if user is logged in and if so if users with his rights can see ads.
		if ( $see_ads_capability && is_user_logged_in() && current_user_can( $see_ads_capability ) ) {
			return false;
		}

		// check bots if option is enabled.
		if ( ( isset( $options['block-bots'] ) && $options['block-bots']
			&& ! $this->is_cache_bot() && $this->is_bot() ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if the current user agent is given or a bot
	 *
	 * @since 1.4.9
	 * @return bool true if the current user agent is empty or a bot.
	 */
	public function is_bot() {
		// show ads on AMP version also for bots in order to allow Google (and maybe others) to cache the page.
		if ( advads_is_amp() ) {
			return false;
		}

		$bots = apply_filters( 'advanced-ads-bots', $this->bots );

		$bots  = implode( '|', $bots );
		$bots  = preg_replace( '@[^-_;/|\][ :.!a-z0-9]@i', '', $bots );
		$regex = "@$bots@i";

		if ( isset( $_SERVER['HTTP_USER_AGENT'] ) && '' !== $_SERVER['HTTP_USER_AGENT'] ) {
			$agent = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) );

			return preg_match( $regex, $agent ) === 1;
		}

		return true;
	}

	/**
	 * Check if the current user is a bot prepopulating the cache
	 * Ads should be loaded for the bot, because they should show up on the cached site
	 *
	 * @return bool
	 */
	public function is_cache_bot() {
		if ( isset( $_SERVER['HTTP_USER_AGENT'] ) && '' !== $_SERVER['HTTP_USER_AGENT'] ) {
			// WP Rocket.
			if ( false !== strpos( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ), 'wprocketbot' ) ) {
				return true;
			}

			// WP Super Cache.
			$wp_useragent = apply_filters( 'http_headers_useragent', 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' ) );
			if ( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) === $wp_useragent ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Registers ad post type and group taxonomies
	 *
	 * @since 1.0.0
	 */
	public function create_post_types() {
		if ( 1 !== did_action( 'init' ) && 1 !== did_action( 'uninstall_' . ADVADS_BASE ) ) {
			return;
		}

		// register ad group taxonomy.
		if ( ! taxonomy_exists( self::AD_GROUP_TAXONOMY ) ) {
			$post_type_params = $this->get_group_taxonomy_params();
			register_taxonomy( self::AD_GROUP_TAXONOMY, array( self::POST_TYPE_SLUG ), $post_type_params );
		}

		// register ad post type.
		if ( ! post_type_exists( self::POST_TYPE_SLUG ) ) {
			$post_type_params = $this->get_post_type_params();
			register_post_type( self::POST_TYPE_SLUG, $post_type_params );
		}
	}

	/**
	 * Defines the parameters for the ad post type taxonomy
	 *
	 * @since 1.0.0
	 * @return array
	 */
	protected function get_group_taxonomy_params() {
		$labels = array(
			'name'              => _x( 'Ad Groups & Rotations', 'ad group general name', 'advanced-ads' ),
			'singular_name'     => _x( 'Ad Group', 'ad group singular name', 'advanced-ads' ),
			'search_items'      => __( 'Search Ad Groups', 'advanced-ads' ),
			'all_items'         => __( 'All Ad Groups', 'advanced-ads' ),
			'parent_item'       => __( 'Parent Ad Groups', 'advanced-ads' ),
			'parent_item_colon' => __( 'Parent Ad Groups:', 'advanced-ads' ),
			'edit_item'         => __( 'Edit Ad Group', 'advanced-ads' ),
			'update_item'       => __( 'Update Ad Group', 'advanced-ads' ),
			'add_new_item'      => __( 'Add New Ad Group', 'advanced-ads' ),
			'new_item_name'     => __( 'New Ad Groups Name', 'advanced-ads' ),
			'menu_name'         => __( 'Groups', 'advanced-ads' ),
			'not_found'         => __( 'No Ad Group found', 'advanced-ads' ),
		);

		$args = array(
			'public'            => false,
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_in_nav_menus' => false,
			'show_tagcloud'     => false,
			'show_admin_column' => true,
			'query_var'         => false,
			'rewrite'           => false,
		);

		return $args;
	}

	/**
	 * Defines the parameters for the custom post type
	 *
	 * @since 1.0.0
	 * @return array
	 */
	protected function get_post_type_params() {
		$labels = array(
			'name'               => __( 'Ads', 'advanced-ads' ),
			'singular_name'      => __( 'Ad', 'advanced-ads' ),
			'add_new'            => __( 'New Ad', 'advanced-ads' ),
			'add_new_item'       => __( 'Add New Ad', 'advanced-ads' ),
			'edit'               => __( 'Edit', 'advanced-ads' ),
			'edit_item'          => __( 'Edit Ad', 'advanced-ads' ),
			'new_item'           => __( 'New Ad', 'advanced-ads' ),
			'view'               => __( 'View', 'advanced-ads' ),
			'view_item'          => __( 'View the Ad', 'advanced-ads' ),
			'search_items'       => __( 'Search Ads', 'advanced-ads' ),
			'not_found'          => __( 'No Ads found', 'advanced-ads' ),
			'not_found_in_trash' => __( 'No Ads found in Trash', 'advanced-ads' ),
			'parent'             => __( 'Parent Ad', 'advanced-ads' ),
		);

		$supports = array( 'title' );
		if ( defined( 'ADVANCED_ADS_ENABLE_REVISIONS' ) ) {
			$supports[] = 'revisions';
		};

		$post_type_params = array(
			'labels'       => $labels,
			'public'       => false,
			'show_ui'      => true,
			'show_in_menu' => false,
			'hierarchical' => false,
			'capabilities' => array(
				// Meta capabilities.
				'edit_post'              => 'advanced_ads_edit_ads',
				'read_post'              => 'advanced_ads_edit_ads',
				'delete_post'            => 'advanced_ads_edit_ads',
				'edit_page'              => 'advanced_ads_edit_ads',
				'read_page'              => 'advanced_ads_edit_ads',
				'delete_page'            => 'advanced_ads_edit_ads',
				// Primitive capabilities used outside of map_meta_cap().
				'edit_posts'             => 'advanced_ads_edit_ads',
				'edit_others_posts'      => 'advanced_ads_edit_ads',
				'publish_posts'          => 'advanced_ads_edit_ads',
				'read_private_posts'     => 'advanced_ads_edit_ads',
				// Primitive capabilities used within map_meta_cap().
				'read'                   => 'advanced_ads_edit_ads',
				'delete_posts'           => 'advanced_ads_edit_ads',
				'delete_private_posts'   => 'advanced_ads_edit_ads',
				'delete_published_posts' => 'advanced_ads_edit_ads',
				'delete_others_posts'    => 'advanced_ads_edit_ads',
				'edit_private_posts'     => 'advanced_ads_edit_ads',
				'edit_published_posts'   => 'advanced_ads_edit_ads',
				'create_posts'           => 'advanced_ads_edit_ads',
			),
			'has_archive'  => false,
			'query_var'    => true,
			'supports'     => $supports,
			'taxonomies'   => array( self::AD_GROUP_TAXONOMY ),
		);

		return apply_filters( 'advanced-ads-post-type-params', $post_type_params );
	}

	/**
	 * Manipulate output of ad widget
	 *
	 * @since 1.6.8.2
	 * @param arr $params widget and sidebar params.
	 */
	public function manipulate_widget_output( $params = array() ) {

		if ( 'Advanced Ads' === $params[0]['widget_name'] ) {

			$options = $this->plugin->options();
			// hide id by default (when options are empty) or when option is enabled.
			if ( array() === $options || ( isset( $options['remove-widget-id'] ) && $options['remove-widget-id'] ) ) {
				$pattern                    = '#\s(id)=("|\')[^"^\']+("|\')#';
				$params[0]['before_widget'] = preg_replace( $pattern, '', $params[0]['before_widget'] );
			}
		}

		return $params;
	}

	/**
	 * Add meta robots noindex, nofollow to images, which are part of 'Image ad' ad type
	 */
	public function noindex_attachment_images() {
		global $post;
		// if the image was not attached to any post.
		if ( is_attachment() && is_object( $post ) && isset( $post->post_parent ) && 0 === $post->post_parent ) {
			// if at least one ad contains the image.
			if ( get_post_meta( get_the_ID(), '_advanced-ads_parent_id', true ) > 0 ) {
				echo '<meta name="robots" content="noindex, nofollow" />';
			}
		}
	}
	
	/**
	 * Show custom CSS in the header
	 * 
	 * @since 1.10.8
	 */
	public function custom_header_code(){
		if( current_user_can( Advanced_Ads_Plugin::user_cap( 'advanced_ads_edit_ads') ) ){
			?><style>
			    div.advads-edit-bar{position:relative;top:0;left:0;height:0;display:none;z-index:10000;}
			    a.advads-edit-button{position:absolute;top:0;left:0;text-decoration:none;border-bottom:none;color:#0074a2;margin-top:-5px;}
			    a.advads-edit-button span{top:10px;line-height:25px;margin-left:-5px;width:26px;height:26px;border-radius:13px;border:solid 1px #0074a2;background:#fff}
			    div:hover > .advads-edit-bar{display:block;}</style><?php
		}
	}

	/**
	 * Supports the "$this->is_main_query=true" while main query is being executed
	 *
	 * @param WP_Post  $post The Post object (passed by reference).
	 * @param WP_Query $query The current Query object (passed by reference).
	 */
	public function set_query_type( $post, $query = null ) {
		if ( $query instanceof WP_Query ) {
			$this->is_main_query = $query->is_main_query();
		}
	}

	/**
	 * Check if main query is being executed
	 *
	 * @return bool true while main query is being executed or not in the loop, false otherwise
	 */
	public function is_main_query() {
		if ( ! in_the_loop() ) {
			// the secondary query check only designed for within post content.
			return true;
		}

		return true === $this->is_main_query;
	}

	/**
	 * Get an "Advertisement" label to use before single ad or before first ad in a group
	 *
	 * @param string $placement_state default/enabled/disabled.
	 * @return string label, empty string if label should not be displayed.
	 */
	public function get_label( $placement_state = 'default' ) {
		if ( 'disabled' === $placement_state ) {
			return '';
		}

		$advads_options = self::get_instance()->options();

		if ( 'enabled' !== $placement_state && empty( $advads_options['custom-label']['enabled'] ) ) {
			return '';
		}

		$label = ! empty( $advads_options['custom-label']['text'] ) ? esc_html( $advads_options['custom-label']['text'] ) : _x( 'Advertisements', 'label above ads', 'advanced-ads' );

		$template = sprintf( '<div class="%s">%s</div>', Advanced_Ads_Plugin::get_instance()->get_frontend_prefix() . 'adlabel', $label );
		return apply_filters( 'advanced-ads-custom-label', $template, $label );
	}

	/**
	 * Retrieve the number of ads in any status except trash
	 */
	public static function get_number_of_ads() {
		// query number of ads only, if not retrieved, yet.
		if ( self::get_instance()->number_of_ads === false ) {
			$args                               = array( 'post_status' => 'any' );
			$recent_ads                         = self::get_instance()->get_model()->get_ads( $args );
			self::get_instance()->number_of_ads = count( $recent_ads );
		}

		return self::get_instance()->number_of_ads;
	}

	/**
	 * Switch the current blog.
	 *
	 * @param int $blog_id ID of the blog in the WP network.
	 */
	public function switch_to_blog( $blog_id ) {
		if ( is_multisite() ) {
			switch_to_blog( $blog_id );
			self::get_instance()->get_model()->reset_placement_array();
		}
	}

	/**
	 * Restore the current blog.
	 */
	public function restore_current_blog() {
		if ( is_multisite() ) {
			restore_current_blog();
			self::get_instance()->get_model()->reset_placement_array();
		}
	}
}
