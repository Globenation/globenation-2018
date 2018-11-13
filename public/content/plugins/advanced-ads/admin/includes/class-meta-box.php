<?php
defined( 'ABSPATH'  ) || exit;

class Advanced_Ads_Admin_Meta_Boxes {
	/**
	 * Instance of this class.
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * meta box ids
	 *
	 * @since   1.7.4.2
	 * @var	    array
	 */
	protected $meta_box_ids = array();


	private function __construct() {
		add_action( 'add_meta_boxes_' . Advanced_Ads::POST_TYPE_SLUG, array( $this, 'add_meta_boxes' ) );
		// add meta box for post types edit pages
		add_action( 'add_meta_boxes', array( $this, 'add_post_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_post_meta_box' ) );
		// register dashboard widget
		add_action( 'wp_dashboard_setup', array($this, 'add_dashboard_widget') );
		// fixes compatibility issue with WP QUADS PRO
		add_action( 'quads_meta_box_post_types', array($this, 'fix_wpquadspro_issue'), 11 );
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
	 * Add meta boxes
	 *
	 * @since    1.0.0
	 */
	public function add_meta_boxes() {
		$post_type = Advanced_Ads::POST_TYPE_SLUG;

		add_meta_box(
			'ad-main-box', __( 'Ad Type', 'advanced-ads' ), array($this, 'markup_meta_boxes'), $post_type, 'normal', 'high'
		);
		// use dynamic filter from to add close class to ad type meta box after saved first time
		add_filter( 'postbox_classes_advanced_ads_ad-main-box', array( $this, 'close_ad_type_metabox' ) );

		add_meta_box(
			'ad-parameters-box', __( 'Ad Parameters', 'advanced-ads' ), array($this, 'markup_meta_boxes'), $post_type, 'normal', 'high'
		);
		add_meta_box(
			'ad-output-box', __( 'Layout / Output', 'advanced-ads' ), array($this, 'markup_meta_boxes'), $post_type, 'normal', 'high'
		);
		add_meta_box(
			'ad-display-box', __( 'Display Conditions', 'advanced-ads' ), array($this, 'markup_meta_boxes'), $post_type, 'normal', 'high'
		);
		add_meta_box(
			'ad-visitor-box', __( 'Visitor Conditions', 'advanced-ads' ), array($this, 'markup_meta_boxes'), $post_type, 'normal', 'high'
		);
		if( ! defined( 'AAP_VERSION' ) ){
			add_meta_box(
				'advads-pro-pitch', __( 'Increase your ad revenue', 'advanced-ads' ), array($this, 'markup_meta_boxes'), $post_type, 'side', 'low'
			);
		}
		if( ! defined( 'AAT_VERSION' ) ){
			add_meta_box(
				'advads-tracking-pitch', __( 'Ad Stats', 'advanced-ads' ), array($this, 'markup_meta_boxes'), $post_type, 'normal', 'low'
			);
		}

		// register meta box ids
		$this->meta_box_ids = array(
		    'ad-main-box',
		    'ad-parameters-box',
		    'ad-output-box',
		    'ad-display-box',
		    'ad-visitor-box',
		    'advads-pro-pitch',
		    'advads-tracking-pitch',
		    'revisionsdiv', // revisions – only when activated
		    'advanced_ads_groupsdiv' // automatically added by ad groups taxonomy
		);

		// force AA meta boxes to never be completely hidden by screen options
		add_filter( 'hidden_meta_boxes', array( $this, 'unhide_meta_boxes' ), 10, 2 );

		$whitelist = apply_filters( 'advanced-ads-ad-edit-allowed-metaboxes', array_merge(
			$this->meta_box_ids,
			array(
				'submitdiv',
				'slugdiv',
				'tracking-ads-box',
				'ad-layer-ads-box', // deprecated
			)
		) );

		global $wp_meta_boxes;
		// remove non-white-listed meta boxes
		foreach ( array( 'normal', 'advanced', 'side' ) as $context ) {
			if ( isset( $wp_meta_boxes[ $post_type ][ $context ] ) ) {
				foreach ( array( 'high', 'sorted', 'core', 'default', 'low' ) as $priority ) {
					if ( isset( $wp_meta_boxes[ $post_type ][ $context ][ $priority ]) ) {
						foreach ( (array) $wp_meta_boxes[ $post_type ][ $context ][ $priority ] as $id => $box ) {
							if ( ! in_array( $id, $whitelist ) )  {
								unset( $wp_meta_boxes[ $post_type ][ $context ][ $priority ][ $id ] );
							}
						}
					}
				}
			}
		}
	}

	/**
	 * load templates for all meta boxes
	 *
	 * @since 1.0.0
	 * @param obj $post
	 * @param array $box
	 * @todo move ad initialization to main function and just global it
	 */
	public function markup_meta_boxes($post, $box) {
		$ad = new Advanced_Ads_Ad( $post->ID );
		
		switch ( $box['id'] ) {
			case 'ad-main-box':
				$view = 'ad-main-metabox.php';
				$hndlelinks = '<a href="' . ADVADS_URL . 'manual/ad-types#utm_source=advanced-ads&utm_medium=link&utm_campaign=edit-ad-type" target="_blank">' . __('Manual', 'advanced-ads') . '</a>';
				break;
			case 'ad-parameters-box':
				$view = 'ad-parameters-metabox.php';
				break;
			case 'ad-output-box':
				$view = 'ad-output-metabox.php';
				break;
			case 'ad-display-box':
				$view = 'ad-display-metabox.php';
				$hndlelinks = '<a href="#" class="advads-video-link">' . __('Video', 'advanced-ads') . '</a>';
				$hndlelinks .= '<a href="' . ADVADS_URL . 'manual/display-conditions#utm_source=advanced-ads&utm_medium=link&utm_campaign=edit-display" target="_blank">' . __('Manual', 'advanced-ads') . '</a>';
				$videomarkup = '<iframe width="420" height="315" src="https://www.youtube-nocookie.com/embed/wVB6UpeyWNA?rel=0&amp;showinfo=0" frameborder="0" allowfullscreen></iframe>';
				break;
			case 'ad-visitor-box':
				$view = 'ad-visitor-metabox.php';
				$hndlelinks = '<a href="' . ADVADS_URL . 'manual/visitor-conditions#utm_source=advanced-ads&utm_medium=link&utm_campaign=edit-visitor" target="_blank">' . __('Manual', 'advanced-ads') . '</a>';
				break;
			case 'advads-pro-pitch':
				$view = 'pitch-bundle.php';
				// $hndlelinks = '<a href="' . ADVADS_URL . 'manual/visitor-conditions#utm_source=advanced-ads&utm_medium=link&utm_campaign=edit-visitor" target="_blank">' . __('Manual', 'advanced-ads') . '</a>';
				break;
			case 'advads-tracking-pitch':
				$view = 'pitch-tracking.php';
				// $hndlelinks = '<a href="' . ADVADS_URL . 'manual/visitor-conditions#utm_source=advanced-ads&utm_medium=link&utm_campaign=edit-visitor" target="_blank">' . __('Manual', 'advanced-ads') . '</a>';
				break;
		}

		if ( ! isset( $view ) ) {
			return;
		}
		// markup moved to handle headline of the metabox
		if( isset( $hndlelinks ) ){
		    ?><span class="advads-hndlelinks hidden"><?php echo $hndlelinks; ?></span>
		    <?php
		}
		// show video markup
		if( isset( $videomarkup ) ){
		    echo '<div class="advads-video-link-container" data-videolink=\'' . $videomarkup . '\'></div>';
		}
		/**
		 *  list general notices
		 * 
		 *  elements in $warnings contain [text] and [class] attributes
		 */
		$warnings = array();
		// show warning if ad contains https in parameters box
		if ( 'ad-parameters-box' === $box['id'] &&  $message = Advanced_Ads_Ad_Debug::is_https_and_http( $ad ) ) {
			$warnings[] = array(
				'text' => $message,
				'class' =>'advads-ad-notice-https-missing error'
			);
		}

		if ( 'ad-parameters-box' === $box['id'] ) {
			$auto_ads_strings = Advanced_Ads_AdSense_Admin::get_auto_ads_messages();

			if ( Advanced_Ads_AdSense_Data::get_instance()->is_page_level_enabled() ) {
				$warnings[] = array(
					'text' => $auto_ads_strings['enabled'],
					'class' => 'advads-auto-ad-in-ad-content hidden error'
				);
			} else {
				$warnings[] = array(
					'text' => $auto_ads_strings['disabled'],
					'class' => 'advads-auto-ad-in-ad-content hidden error'
				);
			}
		}
		
		$warnings = apply_filters( 'advanced-ads-ad-notices', $warnings, $box, $post );
		echo '<ul id="' .$box['id'].'-notices" class="advads-metabox-notices">';
		foreach( $warnings as $_warning ){
			if( isset( $_warning['text'] ) ) :
			    $warning_class = isset( $_warning['class'] ) ? $_warning['class'] : '';
			    echo '<li class="'. $warning_class . '">';
			    echo $_warning['text'];
			    echo '</li>';
			endif;
		}
		echo '</ul>';
		include ADVADS_BASE_PATH . 'admin/views/' . $view;
	}

	/**
	 * force all AA related meta boxes to stay visible
	 *
	 * @since 1.7.4.2
	 * @param array     $hidden       An array of hidden meta boxes
	 * @param WP_Screen $screen       WP_Screen object of the current screen
	 */
	public function unhide_meta_boxes( $hidden, $screen ){
		// only check on Advanced Ads edit screen
		if ( ! isset( $screen->id ) || $screen->id !== 'advanced_ads' || !is_array( $this->meta_box_ids ) ) {
			return $hidden;
		}

		// return only hidden elements which are not among the Advanced Ads meta box ids
		return array_diff( $hidden, $this->meta_box_ids );
	}

	/**
	 * add a meta box to post type edit screens with ad settings
	 *
	 * @since 1.3.10
	 * @param string $post_type current post type
	 */
	public function add_post_meta_box($post_type = ''){
		// don’t display for non admins
		if( ! current_user_can( Advanced_Ads_Plugin::user_cap( 'advanced_ads_edit_ads') ) ) {
			return;
		}

		// get public post types
		$public_post_types = get_post_types( array('public' => true, 'publicly_queryable' => true), 'names', 'or' );

		//limit meta box to public post types
		if ( in_array( $post_type, $public_post_types ) ) {
			add_meta_box(
				'advads-ad-settings',
				__( 'Ad Settings', 'advanced-ads' ),
				array( $this, 'render_post_meta_box' ),
				$post_type,
				'side',
				'low'
			);
		}
	}

	/**
	 * render meta box for ad settings on a per post basis
	 *
	 * @since 1.3.10
	 * @param WP_Post $post The post object.
	 */
	public function render_post_meta_box( $post ) {

		// nonce field to check when we save the values
		wp_nonce_field( 'advads_post_meta_box', 'advads_post_meta_box_nonce' );

		// retrieve an existing value from the database.
		$values = get_post_meta( $post->ID, '_advads_ad_settings', true );

		// load the view
		include ADVADS_BASE_PATH . 'admin/views/post-ad-settings-metabox.php';

		do_action( 'advanced_ads_render_post_meta_box', $post, $values );
	}

	/**
	 * save the ad meta when the post is saved.
	 *
	 * @since 1.3.10
	 * @param int $post_id The ID of the post being saved.
	*/
	public function save_post_meta_box( $post_id ) {

		if( ! current_user_can( Advanced_Ads_Plugin::user_cap( 'advanced_ads_edit_ads') ) ) {
		    return;
		}

		// check nonce
		if ( ! isset( $_POST['advads_post_meta_box_nonce'] ) ) {
			return $post_id; }

		$nonce = $_POST['advads_post_meta_box_nonce'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'advads_post_meta_box' ) ) {
			return $post_id; }

		// don’t save on autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id; }

		// check the user's permissions.
		if ( 'page' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return $post_id; }
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id; }
		}

		// Sanitize the user input.
		$_data['disable_ads'] = isset($_POST['advanced_ads']['disable_ads']) ? absint( $_POST['advanced_ads']['disable_ads'] ) : 0;

		$_data = apply_filters( 'advanced_ads_save_post_meta_box', $_data );

		// Update the meta field.
		update_post_meta( $post_id, '_advads_ad_settings', $_data );
	}

	/**
	 * add "close" class to collapse the ad-type metabox after ad was saved first
	 *
	 * @since 1.7.2
	 * @param arr $classes
	 * @return arr $classes
	 */
	public function close_ad_type_metabox( $classes = array() ) {
	    global $post;
	    if ( isset( $post->ID ) && 'publish' === $post->post_status ) {
			if ( ! in_array( 'closed', $classes ) ) {
			    $classes[] = 'closed';
			}
	    } else {
			$classes = array();
	    }
	    return $classes;
	}

	/**
	 * add dashboard widget with ad stats and additional information
	 *
	 * @since 1.3.12
	 */
	public function add_dashboard_widget(){
		// display dashboard widget only to authors and higher roles
		if( ! current_user_can( Advanced_Ads_Plugin::user_cap( 'advanced_ads_see_interface') ) ) {
		        return;
		}
		add_meta_box( 'advads_dashboard_widget', __( 'Ads Dashboard', 'advanced-ads' ), array($this, 'dashboard_widget_function'), 'dashboard', 'side', 'high' );
	}

	/**
	 * display widget functions
	 */
	public static function dashboard_widget_function($post, $callback_args){
		// get number of ads
		$ads_count = Advanced_Ads::get_number_of_ads();
		if( current_user_can( Advanced_Ads_Plugin::user_cap( 'advanced_ads_edit_ads') ) ) {
			echo '<p>';
			printf(__( '%d ads – <a href="%s">manage</a> - <a href="%s">new</a>', 'advanced-ads' ),
				$ads_count,
				'edit.php?post_type='. Advanced_Ads::POST_TYPE_SLUG,
			'post-new.php?post_type='. Advanced_Ads::POST_TYPE_SLUG);
			echo '</p>';
		}

		// get and display plugin version
		$advads_plugin_data = get_plugin_data( ADVADS_BASE_PATH . 'advanced-ads.php' );
		if ( isset($advads_plugin_data['Version']) ){
			$version = $advads_plugin_data['Version'];
			echo '<p><a href="'.ADVADS_URL.'#utm_source=advanced-ads&utm_medium=link&utm_campaign=dashboard" target="_blank" title="'.
				__( 'plugin manual and homepage', 'advanced-ads' ).'">Advanced Ads</a> '. $version .'</p>';
		}

		$notice_options = Advanced_Ads_Admin_Notices::get_instance()->options();
		$_notice = 'nl_first_steps';
		if ( ! isset($notice_options['closed'][ $_notice ] ) ) {
			?><div class="advads-admin-notice">
			    <p><button type="button" class="button-primary advads-notices-button-subscribe" data-notice="<?php echo $_notice ?>"><?php _e('Get the tutorial via email', 'advanced-ads'); ?></button></p>
			</div><?php
		}

		$_notice = 'nl_adsense';
		if ( ! isset($notice_options['closed'][ $_notice ] ) ) {
			?><div class="advads-admin-notice">
			    <p><button type="button" class="button-primary advads-notices-button-subscribe" data-notice="<?php echo $_notice ?>"><?php _e('Get AdSense tips via email', 'advanced-ads'); ?></button></p>
			</div><?php
		}

		// rss feed
		self::dashboard_cached_rss_widget();

		// add markup for utm variables
		// todo: move to js file
		?><script>jQuery('#advads_dashboard_widget .rss-widget a').each(function(){ this.href = this.href + '#utm_source=advanced-ads&utm_medium=rss-link&utm_campaign=dashboard'; })</script><?php
	}

	/**
	 * checks to see if there are feed urls in transient cache; if not, load them
	 * built using a lot of https://developer.wordpress.org/reference/functions/wp_dashboard_cached_rss_widget/
	 *
	 * @since 1.3.12
	 * @param string $widget_id
	 * @param callback $callback
	 * @param array $check_urls RSS feeds
	 * @return bool False on failure. True on success.
	 */
	static function dashboard_cached_rss_widget() {
	    
		$cache_key = 'dash_' . md5( 'advads_dashboard_widget' );

		if ( false !== ( $output = get_transient( $cache_key ) ) ) {
		    echo $output;
		    return true;
		}
		/**
		 * only display dummy output which then loads the content via AJAX
		 */
		?><div id="advads-dashboard-widget-placeholder">
		    <img src="<?php echo admin_url( 'images/spinner.gif' ); ?>" width="20" height="20"/>
		    <script>advads_load_dashboard_rss_widget_content();</script>
		</div><?php

		return true;
	}

	/**
	 * create the rss output of the widget
	 *
	 * @param string $widget_id Widget ID.
	 */
	static function dashboard_widget_function_output( ) {
	    
		check_ajax_referer('advanced-ads-admin-ajax-nonce', 'nonce');
	    
		$cache_key = 'dash_' . md5( 'advads_dashboard_widget' );
	    
		$feeds = array(
			array(
				'link'         => 'http://webgilde.com/en/ad-optimization/',
				'url'          => 'http://webgilde.com/en/ad-optimization/feed/',
				'title'        => __( 'From the ad optimization universe', 'advanced-ads' ),
				'items'        => 2,
				'show_summary' => 0,
				'show_author'  => 0,
				'show_date'    => 0,
			),
			array(
				'link'         => ADVADS_URL,
				'url'          => ADVADS_URL . 'feed/',
				'title'        => __( 'Advanced Ads Tutorials', 'advanced-ads' ),
				'items'        => 2,
				'show_summary' => 0,
				'show_author'  => 0,
				'show_date'    => 0,
			),
		);
	    
		// create output and also cache it
		
		ob_start();
		foreach ( $feeds as $_feed ){
			echo '<div class="rss-widget">';
			echo '<h4>'.$_feed['title'].'</h4>';
			wp_widget_rss_output( $_feed['url'], $_feed );
			echo '</div>';
		}
		set_transient( $cache_key, ob_get_flush(), 48 * HOUR_IN_SECONDS ); // Default lifetime in cache of 48 hours
		die();
	}
	
	/**
	 * fixes a WP QUADS PRO compatibility issue
	 * they inject their ad optimization meta box into our ad page, even though it is not a public post type
	 * using they filter, we remove AA from the list of post types they inject this box into
	 */
	function fix_wpquadspro_issue( $allowed_post_types ){
		unset( $allowed_post_types['advanced_ads'] );
		return $allowed_post_types;
	}

}
