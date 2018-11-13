<?php
defined( 'ABSPATH'  ) || exit;

class Advanced_Ads_Admin_Ad_Type {
	/**
	 * Instance of this class.
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * post type slug
	 *
	 * @since   1.0.0
	 * @var     string
	 */
	protected $post_type = '';

	private function __construct() {
		// registering custom columns needs to work with and without DOING_AJAX
		add_filter( 'manage_advanced_ads_posts_columns', array($this, 'ad_list_columns_head') ); // extra column
		add_filter( 'manage_advanced_ads_posts_custom_column', array($this, 'ad_list_columns_content'), 10, 2 ); // extra column
		add_filter( 'manage_advanced_ads_posts_custom_column', array($this, 'ad_list_columns_timing'), 10, 2 ); // extra column
		add_filter( 'manage_advanced_ads_posts_custom_column', array($this, 'ad_list_columns_shortcode'), 10, 2 ); // extra column
		add_action( 'restrict_manage_posts', array( $this, 'ad_list_add_filters') );
		add_filter( 'default_hidden_columns', array( $this, 'hide_ad_list_columns' ), 10, 2 ); // hide the ad shortcode column by default

		// ad updated messages
		add_filter( 'bulk_post_updated_messages', array($this, 'ad_bulk_update_messages'), 10, 2 );

		// handling (ad) lists
		add_filter( 'request', array($this, 'ad_list_request') ); // order ads by title, not ID	
		add_action( 'all_admin_notices', array($this, 'no_ads_yet_notice') );

		// save ads post type
		add_action( 'save_post', array($this, 'save_ad') );
		// delete ads post type
		add_action( 'delete_post', array($this, 'delete_ad') );

		// on post/ad edit screen
		add_action( 'edit_form_top', array($this, 'edit_form_above_title') );
		add_action( 'edit_form_after_title', array($this, 'edit_form_below_title') );
		add_action( 'dbx_post_sidebar', array($this, 'edit_form_end') );
		add_action( 'post_submitbox_misc_actions', array($this, 'add_submit_box_meta') );
		add_action( 'admin_enqueue_scripts', array($this, 'use_code_editor') );

		// ad updated messages
		add_filter( 'post_updated_messages', array($this, 'ad_update_messages') );

		$this->post_type = constant( 'Advanced_Ads::POST_TYPE_SLUG' );

		add_filter( 'gettext', array( $this, 'replace_cheating_message' ), 20, 2 );
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
	 * add heading for extra column of ads list
	 * remove the date column
	 *
	 * @since 1.3.3
	 * @param arr $columns
	 */
	public function ad_list_columns_head( $columns ){
		$new_columns = array();
		if( is_array( $columns ) ){
			foreach( $columns as $key => $value ) {
				$new_columns[ $key ] = $value;
				if ( $key == 'title' ){
					$new_columns[ 'ad_details' ] = __( 'Ad Details', 'advanced-ads' );
					$new_columns[ 'ad_timing' ] = __( 'Ad Planning', 'advanced-ads' );
					$new_columns[ 'ad_shortcode' ] = __( 'Ad Shortcode', 'advanced-ads' );
				}
			}
		} else {
			$new_columns[ 'ad_details' ] = __( 'Ad Details', 'advanced-ads' );
			$new_columns[ 'ad_timing' ] = __( 'Ad Planning', 'advanced-ads' );
			$new_columns[ 'ad_shortcode' ] = __( 'Ad Shortcode', 'advanced-ads' );
		}

		// white-listed columns
		$whitelist = apply_filters( 'advanced-ads-ad-list-allowed-columns', array(
		    'cb', // checkbox
		    'title',
		    'ad_details',
		    'ad_timing',
		    'ad_shortcode',
		    'taxonomy-advanced_ads_groups',
		) );

		// remove non-white-listed columns
		foreach( $new_columns as $_key => $_value ){
			if( ! in_array( $_key, $whitelist ) ){
				unset( $new_columns[ $_key ] );
			}
		}

		return $new_columns;
	}

	/**
	 * display ad details in ads list
	 *
	 * @since 1.3.3
	 * @param string $column_name name of the column
	 * @param int $ad_id id of the ad
	 */
	public function  ad_list_columns_content($column_name, $ad_id) {
		if ( $column_name == 'ad_details' ) {
			$ad = new Advanced_Ads_Ad( $ad_id );

			// load ad type title
			$types = Advanced_Ads::get_instance()->ad_types;
			$type = ( ! empty($types[$ad->type]->title)) ? $types[$ad->type]->title : 0;

			// load ad size
			$size = 0;
			if ( ! empty($ad->width) || ! empty($ad->height) ) {
				$size = sprintf( '%d x %d', $ad->width, $ad->height );
			}

			$size = apply_filters( 'advanced-ads-list-ad-size', $size, $ad );

			include ADVADS_BASE_PATH . 'admin/views/ad-list-details-column.php';
		}
	}

	/**
	 * display ad details in ads list
	 *
	 * @since 1.6.11
	 * @param string $column_name name of the column
	 * @param int $ad_id id of the ad
	 */
	public function  ad_list_columns_timing($column_name, $ad_id) {
		if ( $column_name == 'ad_timing' ) {
			$ad = new Advanced_Ads_Ad( $ad_id );

			$expiry = false;
			$post_future = false;
			$post_start = get_post_time('U', true, $ad->id );
			$html_classes = 'advads-filter-timing';
			$expiry_date_format = get_option( 'date_format' ). ', ' . get_option( 'time_format' );

			if( isset( $ad->expiry_date ) && $ad->expiry_date ){
				$html_classes .= ' advads-filter-any-exp-date';

				$expiry = $ad->expiry_date;
				if( $ad->expiry_date < time() ){
					$html_classes .= ' advads-filter-expired';
				}
			}
			if( $post_start > time() ){
				$post_future = $post_start;
				$html_classes .= ' advads-filter-future';
			}

		    ob_start();
		    do_action_ref_array( 'advanced-ads-ad-list-timing-column-after', array( $ad, &$html_classes ) );
		    $content_after = ob_get_clean();

			include ADVADS_BASE_PATH . 'admin/views/ad-list-timing-column.php';
		}
	}

	/**
	 * display ad shortcode in ads list
	 *
	 * @since 1.8.2
	 * @param string $column_name name of the column
	 * @param int $ad_id id of the ad
	 */
	public function  ad_list_columns_shortcode($column_name, $ad_id) {
		if ( $column_name == 'ad_shortcode' ) {
			$ad = new Advanced_Ads_Ad( $ad_id );
		
			include ADVADS_BASE_PATH . 'admin/views/ad-list-shortcode-column.php';
		}
	}
	
	/**
	 * display ad shortcode in ads list
	 *
	 * @since 1.10.5
	 * @param array	    $hidden An array of columns hidden by default.
	 * @param WP_Screen $screen WP_Screen object of the current screen.
	 */
	public function hide_ad_list_columns( $hidden, $screen ) {
	    
		if( isset( $screen->id ) && 'edit-' . Advanced_Ads::POST_TYPE_SLUG === $screen->id ){
		    
			$hidden[] = 'ad_shortcode';
			
		}
		
		return $hidden;
	}

	/**
	 * adds filter dropdowns before the 'Filter' button on the ad list table
	 */
	public function ad_list_add_filters() {
		$screen = get_current_screen();
		if ( ! isset( $screen->id ) || $screen->id !== 'edit-advanced_ads' ) {
			return;
		}
		include ADVADS_BASE_PATH . 'admin/views/ad-list-filters.php';
	}

	/**
	 * edit ad bulk update messages
	 *
	 * @since 1.4.7
	 * @param arr $messages existing bulk update messages
	 * @param arr $counts numbers of updated ads
	 * @return arr $messages
	 *
	 * @see wp-admin/edit.php
	 */
	public function ad_bulk_update_messages(array $messages, array $counts){
		$post = get_post();

		$messages[Advanced_Ads::POST_TYPE_SLUG] = array(
			'updated'   => _n( '%s ad updated.', '%s ads updated.', $counts['updated'], 'advanced-ads' ),
			'locked'    => _n( '%s ad not updated, somebody is editing it.', '%s ads not updated, somebody is editing them.', $counts['locked'], 'advanced-ads' ),
			'deleted'   => _n( '%s ad permanently deleted.', '%s ads permanently deleted.', $counts['deleted'], 'advanced-ads' ),
			'trashed'   => _n( '%s ad moved to the Trash.', '%s ads moved to the Trash.', $counts['trashed'], 'advanced-ads' ),
			'untrashed' => _n( '%s ad restored from the Trash.', '%s ads restored from the Trash.', $counts['untrashed'], 'advanced-ads' ),
		);

		return $messages;
	}

	/**
	 * order ads by title on ads list
	 *
	 * @since 1.3.18
	 * @param arr $vars array with request vars
	 */
	public function ad_list_request($vars){

		// order ads by title on ads list
		if ( is_admin() && empty( $vars['orderby'] ) && isset( $vars['post_type'] ) && $this->post_type == $vars['post_type'] ) {
			$vars = array_merge( $vars, array(
				'orderby' => 'title',
				'order' => 'ASC'
			) );
		}

		return $vars;
	}

	/**
	 * show instructions to create first ad above the ad list
	 *
	 * @return type
	 */
	public function no_ads_yet_notice(){
		$screen = get_current_screen();
		if ( ! isset( $screen->id ) || $screen->id !== 'edit-advanced_ads' ) {
			return;
		}
		
		// get number of ads
		$existing_ads = Advanced_Ads::get_number_of_ads();

		// only display if there are no more than 2 ads
		if( 3 > $existing_ads ){
		    echo '<div class="advads-ad-metabox postbox" style="clear: both; margin: 10px 20px 0 2px;">';
		    include ADVADS_BASE_PATH . 'admin/views/ad-list-no-ads.php';
		    echo '</div>';
		}
	}


	/**
	 * prepare the ad post type to be saved
	 *
	 * @since 1.0.0
	 * @param int $post_id id of the post
	 * @todo handling this more dynamic based on ad type
	 */
	public function save_ad($post_id) {

		if ( ! current_user_can( Advanced_Ads_Plugin::user_cap( 'advanced_ads_edit_ads') )
			// only use for ads, no other post type
			|| ! isset($_POST['post_type']) 
			|| $this->post_type != $_POST['post_type'] 
			|| ! isset($_POST['advanced_ad']['type']) 
			|| wp_is_post_revision( $post_id ) ) {
			return;
		}

		// get ad object
		$ad = new Advanced_Ads_Ad( $post_id );
		if ( ! $ad instanceof Advanced_Ads_Ad ) {
			return;
		}

		// filter to allow change of submitted ad settings
		$_POST['advanced_ad'] = apply_filters( 'advanced-ads-ad-settings-pre-save', $_POST['advanced_ad'] );

		$ad->type = $_POST['advanced_ad']['type'];

		/**
		 * deprecated since introduction of "visitors" in 1.5.4
		 */
		if ( isset($_POST['advanced_ad']['visitor']) ) {
			$ad->set_option( 'visitor', $_POST['advanced_ad']['visitor'] );
		} else {
			$ad->set_option( 'visitor', array() );
		}
		// visitor conditions
		if ( isset($_POST['advanced_ad']['visitors']) ) {
			$ad->set_option( 'visitors', $_POST['advanced_ad']['visitors'] );
		} else {
			$ad->set_option( 'visitors', array() );
		}
		$ad->url = 0;
		if ( isset($_POST['advanced_ad']['url']) ) {
			$ad->url = esc_url( $_POST['advanced_ad']['url'] );
		}
		// save size
		$ad->width = 0;
		if ( isset($_POST['advanced_ad']['width']) ) {
			$ad->width = absint( $_POST['advanced_ad']['width'] );
		}
		$ad->height = 0;
		if ( isset($_POST['advanced_ad']['height']) ) {
			$ad->height = absint( $_POST['advanced_ad']['height'] );
		}

		if ( ! empty($_POST['advanced_ad']['description']) ) {
			$ad->description = esc_textarea( $_POST['advanced_ad']['description'] ); }
		else { $ad->description = ''; }

		if ( ! empty($_POST['advanced_ad']['content']) ) {
			$ad->content = $_POST['advanced_ad']['content']; }
		else { $ad->content = ''; }

		$output = isset( $_POST['advanced_ad']['output'] ) ? $_POST['advanced_ad']['output'] : array();

		// Find Advanced Ads shortcodes.
		if ( ! empty( $output['allow_shortcodes'] ) ) {
			$shortcode_pattern = get_shortcode_regex( array( 'the_ad', 'the_ad_group', 'the_ad_placement' ) );
			$output['has_shortcode'] = preg_match( '/' . $shortcode_pattern . '/s', $ad->content );
		}

		// Set output.
		$ad->set_option( 'output', $output );

		if ( ! empty($_POST['advanced_ad']['conditions']) ){
			$ad->conditions = $_POST['advanced_ad']['conditions'];
		} else {
			$ad->conditions = array();
		}
		// prepare expiry date
		if ( isset($_POST['advanced_ad']['expiry_date']['enabled']) ) {
			$year   = absint( $_POST['advanced_ad']['expiry_date']['year'] );
			$month  = absint( $_POST['advanced_ad']['expiry_date']['month'] );
			$day    = absint( $_POST['advanced_ad']['expiry_date']['day'] );
			$hour   = absint( $_POST['advanced_ad']['expiry_date']['hour'] );
			$minute = absint( $_POST['advanced_ad']['expiry_date']['minute'] );

			$expiration_date = sprintf( "%04d-%02d-%02d %02d:%02d:%02d", $year, $month, $day, $hour, $minute, '00' );
			$valid_date = wp_checkdate( $month, $day, $year, $expiration_date );

			if ( !$valid_date ) {
				$ad->expiry_date = 0;
			} else {
				$_gmDate = date_create( $expiration_date, Advanced_Ads_Admin::get_wp_timezone() );
                $_gmDate->setTimezone( new DateTimeZone( 'UTC' ) );
				$gmDate = $_gmDate->format( 'Y-m-d-H-i' );
				list( $year, $month, $day, $hour, $minute ) = explode( '-', $gmDate );
				$ad->expiry_date = gmmktime($hour, $minute, 0, $month, $day, $year);
			}
		} else {
			$ad->expiry_date = 0;
		}

		$image_id = ( isset( $_POST['advanced_ad']['output']['image_id'] ) ) ? absint( $_POST['advanced_ad']['output']['image_id'] ) : 0;
		if ( $image_id ) {
			$all_posts_id = get_post_meta( $image_id, '_advanced-ads_parent_id' );

			if ( ! in_array ( $post_id, $all_posts_id ) ) {
				add_post_meta( $image_id, '_advanced-ads_parent_id', $post_id, false  );
			}
		}

		$ad->save();
	}

	/**
	 * prepare the ad post type to be removed
	 *
	 * @param int $post_id id of the post
	 */
	public function delete_ad( $post_id ) {
		global $wpdb;

		if ( ! current_user_can( 'delete_posts' ) ) {
			return;
		}

		if ( $post_id > 0 ) {
			$post_type = get_post_type( $post_id );
			if ( $post_type == $this->post_type ) {
				$wpdb->query(
					$wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %d", '_advanced-ads_parent_id', $post_id )
				);
			}
		}
	}

	/**
	 * add information above the ad title
	 *
	 * @since 1.5.6
	 * @param obj $post
	 */
	public function edit_form_above_title($post){
		if ( ! isset($post->post_type) || $post->post_type != $this->post_type ) {
			return;
		}
		
		// highlight Dummy ad if this is the first ad
		if( ! Advanced_Ads::get_number_of_ads() ){
			?><style>.advanced-ads-type-list-dummy { font-weight: bold; }</style><?php
		}

		
		$ad = new Advanced_Ads_Ad( $post->ID );

		$placement_types = Advanced_Ads_Placements::get_placement_types();
		$placements = Advanced_Ads::get_ad_placements_array(); // -TODO use model

		// display general and wizard information
		include ADVADS_BASE_PATH . 'admin/views/ad-info-top.php';
		// display ad injection information
		include ADVADS_BASE_PATH . 'admin/views/placement-injection-top.php';
	}

	/**
	 * add information about the ad below the ad title
	 *
	 * @since 1.1.0
	 * @param obj $post
	 */
	public function edit_form_below_title($post){
		if ( ! isset($post->post_type) || $post->post_type != $this->post_type ) {
			return;
		}
		$ad = new Advanced_Ads_Ad( $post->ID );

		include ADVADS_BASE_PATH . 'admin/views/ad-info.php';
	}

	/**
	 * add information below the ad edit form
	 *
	 * @since 1.7.3
	 * @param obj $post
	 */
	public function edit_form_end($post){
		if ( ! isset($post->post_type) || $post->post_type != $this->post_type ) {
			return;
		}

		include ADVADS_BASE_PATH . 'admin/views/ad-info-bottom.php';
	}

	/**
	 * add meta values below submit box
	 *
	 * @since 1.3.15
	 */
	public function add_submit_box_meta(){
		global $post, $wp_locale;

		if ( $post->post_type !== Advanced_Ads::POST_TYPE_SLUG ) { return; }

		$ad = new Advanced_Ads_Ad( $post->ID );

		// get time set for ad or current timestamp (both GMT)
		$utc_ts = $ad->expiry_date ? $ad->expiry_date : time();
		$utc_time = date_create( '@' . $utc_ts );
        $tz_option = get_option( 'timezone_string' );
        $exp_time = clone $utc_time;

        if ( $tz_option ) {
            $exp_time->setTimezone( Advanced_Ads_Admin::get_wp_timezone() );
        } else {
            $tz_name = Advanced_Ads_Admin::timezone_get_name( Advanced_Ads_Admin::get_wp_timezone() );
            $tz_offset = substr( $tz_name, 3 );
            $off_time = date_create( $utc_time->format( 'Y-m-d\TH:i:s' ) . $tz_offset );
            $offset_in_sec = date_offset_get( $off_time );
            $exp_time = date_create( '@' . ( $utc_ts + $offset_in_sec ) );
        }

		list( $curr_year, $curr_month, $curr_day, $curr_hour, $curr_minute ) = explode( '-', $exp_time->format( 'Y-m-d-H-i' ) );
		$enabled = 1 - empty($ad->expiry_date);

		include ADVADS_BASE_PATH . 'admin/views/ad-submitbox-meta.php';
	}
	
	/**
	 * use CodeMirror for plain text input field
	 * 
	 * needs WordPress 4.9 and higher
	 * 
	 * @since 1.8.15
	 */
	public function use_code_editor(){
		global $wp_version;
		if ( 'advanced_ads' !== get_current_screen()->id 
			|| defined( 'ADVANCED_ADS_DISABLE_CODE_HIGHLIGHTING' )
			|| -1 === version_compare( $wp_version, '4.9' ) ) {
		    return;
		}
		
		// Enqueue code editor and settings for manipulating HTML.
		$settings = wp_enqueue_code_editor( array( 'type' => 'application/x-httpd-php' ) );

		// Bail if user disabled CodeMirror.
		if ( false === $settings ) {
			return;
		}

		wp_add_inline_script(
		    'code-editor',
		    sprintf(
			'jQuery( function() { if( jQuery( "#advads-ad-content-plain" ).length ){ wp.codeEditor.initialize( "advads-ad-content-plain", %s ); } } );',
			wp_json_encode( $settings )
		    )
		);
	}

	/**
	 * edit ad update messages
	 *
	 * @since 1.4.7
	 * @param arr $messages existing post update messages
	 * @return arr $messages
	 *
	 * @see wp-admin/edit-form-advanced.php
	 */
	public function ad_update_messages($messages = array()){
		$post = get_post();

		// added to hide error message caused by third party code that uses post_updated_messages filter wrong
		if( ! is_array( $messages )){
		    return $messages;
		}

		$messages[Advanced_Ads::POST_TYPE_SLUG] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => __( 'Ad updated.', 'advanced-ads' ),
			4  => __( 'Ad updated.', 'advanced-ads' ),
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Ad restored to revision from %s', 'advanced-ads' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => __( 'Ad saved.', 'advanced-ads' ), // published
			7  => __( 'Ad saved.', 'advanced-ads' ), // saved
			8  => __( 'Ad submitted.', 'advanced-ads' ),
			9  => sprintf(
				__( 'Ad scheduled for: <strong>%1$s</strong>.', 'advanced-ads' ),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i', 'advanced-ads' ), strtotime( $post->post_date ) )
			),
			10 => __( 'Ad draft updated.', 'advanced-ads' )
		);
		return $messages;
	}

	/**
	 * whether to show the wizard welcome message or not
	 *
	 * @since 1.7.4
	 * @return bool true, if wizard welcome message should be displayed
	 */
	public function show_wizard_welcome(){
		$user_id = get_current_user_id();
		if( ! $user_id ) {
		    return true;
		}

		$hide_wizard =  get_user_meta( $user_id, 'advanced-ads-hide-wizard', true );
		global $post;

		return ( ! $hide_wizard && 'edit' !== $post->filter ) ? true : false;
	}

	/**
	 * whether to start the wizard by default or not
	 *
	 * @since 1.7.4
	 * return bool true, if wizard should start automatically
	 */
	public function start_wizard_automatically(){
		$user_id = get_current_user_id();
		if( ! $user_id ) {
		    return true;
		}

		$hide_wizard =  get_user_meta( $user_id, 'advanced-ads-hide-wizard', true );
		global $post;

		// true if the wizard was never started or closed
		return ( ( ! $hide_wizard && 'edit' !== $post->filter ) || 'false'=== $hide_wizard ) ? true : false;
	}

	/**
	 * Check if an ad is not valid for 'Post Content' placement
	 *
	 * @param obj $ad Advanced_Ads_Ad object
	 * @return string with error if not valid, empty string if valid
	 */
	public static function check_ad_dom_is_not_valid( Advanced_Ads_Ad $ad ) {
		$adContent = ( isset( $ad->content ) ) ? $ad->content : '';
		if ( ! extension_loaded( 'dom' ) || ! $adContent ) {
			return false;
		}

		$wpCharset = get_bloginfo('charset');
		$adDom = new DOMDocument('1.0', $wpCharset);

		$libxml_previous_state = libxml_use_internal_errors( true );
		// clear existing errors
		libxml_clear_errors();
		// source for this regex: http://stackoverflow.com/questions/17852537/preg-replace-only-specific-part-of-string
		$adContent = preg_replace('#(document.write.+)</(.*)#', '$1<\/$2', $adContent); // escapes all closing html tags
		$adDom->loadHtml('<!DOCTYPE html><html><meta http-equiv="Content-Type" content="text/html; charset=' . $wpCharset . '" /><body>' . $adContent);

		$errors = '';
		foreach( libxml_get_errors() as $_error ) {
			// continue, if there is '&' symbol, but not HTML entity
			if ( false === stripos( $_error->message, 'htmlParseEntityRef:' ) ) {
				$errors .= print_r( $_error, true );
			}
		}

		libxml_use_internal_errors( $libxml_previous_state );
		return $errors;
	}

	/**
	 * Replace 'You need a higher level of permission.' message if user role does not have required permissions.
	 *
	 * @param string $translation   Translated text.
	 * @param string $text          Text to translate.
	 * @return string $translation  Translated text.
	 */
	public function replace_cheating_message( $translated_text, $untranslated_text ) {
		global $typenow;

		if ( isset( $typenow ) && $untranslated_text === 'You need a higher level of permission.' && $typenow === $this->post_type ) {
			$translated_text = __( 'You don’t have access to ads. Please deactivate and re-enable Advanced Ads again to fix this.', 'advanced-ads' );
		}

		return $translated_text;
	}

}
