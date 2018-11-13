<?php
defined( 'ABSPATH'  ) || exit;

class Advanced_Ads_Admin_Settings {
	/**
	 * Instance of this class.
	 *
	 * @var      object
	 */
	protected static $instance = null;

	private function __construct() {
		// settings handling
		add_action( 'admin_init', array( $this, 'settings_init' ) );
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
	 * initialize settings
	 *
	 * @since 1.0.1
	 */
	public function settings_init(){

		// get settings page hook
		$hook = Advanced_Ads_Admin::get_instance()->plugin_screen_hook_suffix;

		// register settings
		register_setting( ADVADS_SLUG, ADVADS_SLUG, array($this, 'sanitize_settings') );

		// general settings section
		add_settings_section(
			'advanced_ads_setting_section',
			'',//__( 'General', 'advanced-ads' ),
			array($this, 'render_settings_section_callback'),
			$hook
		);
		
		// Pro pitch section
		if( ! defined( 'AAP_VERSION' ) ){
		    add_settings_section(
			    'advanced_ads_settings_pro_pitch_section',
			    '',
			    array($this, 'render_settings_pro_pitch_section_callback'),
			    'advanced-ads-settings-pro-pitch-page'
		    );

		    add_filter( 'advanced-ads-setting-tabs', array( $this, 'pro_pitch_tab') );
		}		
		
		// Tracking pitch section
		if( ! defined( 'AAT_VERSION' ) ){
		    add_settings_section(
			    'advanced_ads_settings_tracking_pitch_section',
			    '',
			    array($this, 'render_settings_tracking_pitch_section_callback'),
			    'advanced-ads-settings-tracking-pitch-page'
		    );

		    add_filter( 'advanced-ads-setting-tabs', array( $this, 'tracking_pitch_tab') );
		}		

		// licenses section only for main blog
		if( is_main_site( get_current_blog_id() ) ){
		    // register license settings
		    register_setting( ADVADS_SLUG . '-licenses', ADVADS_SLUG . '-licenses' );

		    add_settings_section(
			    'advanced_ads_settings_license_section',
			    '', //__( 'Licenses', 'advanced-ads' ),
			    array($this, 'render_settings_licenses_section_callback'),
			    'advanced-ads-settings-license-page'
		    );

		    add_filter( 'advanced-ads-setting-tabs', array( $this, 'license_tab') );
		    
		    add_settings_section(
			    'advanced_ads_settings_license_pitch_section',
			    '', //__( 'Licenses', 'advanced-ads' ),
			    array($this, 'render_settings_licenses_pitch_section_callback'),
			    'advanced-ads-settings-license-page'
		    );
		}

		// add setting fields to disable ads
		add_settings_field(
			'disable-ads',
			__( 'Disable ads', 'advanced-ads' ),
			array($this, 'render_settings_disable_ads'),
			$hook,
			'advanced_ads_setting_section'
		);
		// add setting fields for user role
		add_settings_field(
			'hide-for-user-role',
			__( 'Hide ads for logged in users', 'advanced-ads' ),
			array($this, 'render_settings_hide_for_users'),
			$hook,
			'advanced_ads_setting_section'
		);
		// add setting fields for advanced js
		add_settings_field(
			'activate-advanced-js',
			__( 'Use advanced JavaScript', 'advanced-ads' ),
			array($this, 'render_settings_advanced_js'),
			$hook,
			'advanced_ads_setting_section'
		);
		// add setting fields for content injection protection
		add_settings_field(
			'content-injection-everywhere',
			__( 'Unlimited ad injection', 'advanced-ads' ),
			array($this, 'render_settings_content_injection_everywhere'),
			$hook,
			'advanced_ads_setting_section'
		);
		// add setting fields for content injection priority
		add_settings_field(
			'content-injection-priority',
			__( 'Priority of content injection filter', 'advanced-ads' ),
			array($this, 'render_settings_content_injection_priority'),
			$hook,
			'advanced_ads_setting_section'
		);
		// add setting fields to remove injection level limitation
		add_settings_field(
			'content-injection-level-limitation',
			__( 'Disable level limitation', 'advanced-ads' ),
			array($this, 'render_settings_content_injection_level_limitation'),
			$hook,
			'advanced_ads_setting_section'
		);
		// add setting fields for hiding ads from bots
		add_settings_field(
			'block-bots',
			__( 'Hide ads from bots', 'advanced-ads' ),
			array($this, 'render_settings_block_bots'),
			$hook,
			'advanced_ads_setting_section'
		);
		// opt out from internal notices
		add_settings_field(
			'disable-notices',
			__( 'Disable notices', 'advanced-ads' ),
			array($this, 'render_settings_disabled_notices'),
			$hook,
			'advanced_ads_setting_section'
		);
		// opt out from internal notices
		add_settings_field(
			'front-prefix',
			__( 'ID prefix', 'advanced-ads' ),
			array($this, 'render_settings_front_prefix'),
			$hook,
			'advanced_ads_setting_section'
		);
		// allow editors to manage ads
		add_settings_field(
			'editors-manage-ads',
			__( 'Allow editors to manage ads', 'advanced-ads' ),
			array($this, 'render_settings_editors_manage_ads'),
			$hook,
			'advanced_ads_setting_section'
		);

		add_settings_field(
			'add-custom-label',
			__( 'Ad label', 'advanced-ads' ),
			array( $this, 'render_settings_add_custom_label' ),
			$hook,
			'advanced_ads_setting_section'
		);
		
		// add setting fields
		add_settings_field(
			'link-target',
			__('Open links in a new window', 'advanced-ads'),
			array($this, 'render_settings_link_target_callback'),
			$hook,
			'advanced_ads_setting_section'
		);		

		// only for main blog
		if ( is_main_site( get_current_blog_id() ) ) {
			add_settings_field(
				'uninstall-delete-data',
				__( 'Delete data on uninstall', 'advanced-ads' ),
				array( $this, 'render_settings_uninstall_delete_data' ),
				$hook,
				'advanced_ads_setting_section'
			);
		}

		// allow to disable shortcode button in TinyMCE
		add_settings_field(
			'disable-shortcode-button',
			__( 'Disable shortcode button', 'advanced-ads' ),
			array( $this, 'render_settings_disable_shortcode_button' ),
			$hook,
			'advanced_ads_setting_section'
		);

		// hook for additional settings from add-ons
		do_action( 'advanced-ads-settings-init', $hook );
	}

	/**
	 * add license tab
	 *
	 * arr $tabs setting tabs
	 */
	public function license_tab( array $tabs ){

		$tabs['licenses'] = array(
			'page' => 'advanced-ads-settings-license-page',
			'group' => ADVADS_SLUG . '-licenses',
			'tabid' => 'licenses',
			'title' => __( 'Licenses', 'advanced-ads' )
		);

		return $tabs;
	}
	
	/**
	 * add pro pitch tab
	 *
	 * arr $tabs setting tabs
	 */
	public function pro_pitch_tab( array $tabs ){

		$tabs['pro_pitch'] = array(
			'page' => 'advanced-ads-settings-pro-pitch-page',
			//'group' => ADVADS_SLUG . '-pro-pitch',
			'tabid' => 'pro-pitch',
			'title' => __( 'Pro', 'advanced-ads' )
		);

		return $tabs;
	}
	
	/**
	 * add tracking pitch tab
	 *
	 * arr $tabs setting tabs
	 */
	public function tracking_pitch_tab( array $tabs ){

		$tabs['tracking_pitch'] = array(
			'page' => 'advanced-ads-settings-tracking-pitch-page',
			'tabid' => 'tracking-pitch',
			'title' => __( 'Tracking', 'advanced-ads' )
		);

		return $tabs;
	}

	/**
	 * render settings section
	 *
	 * @since 1.1.1
	 */
	public function render_settings_section_callback(){
		// for whatever purpose there might come
	}

	/**
	 * render licenses settings section
	 *
	 * @since 1.5.1
	 */
	public function render_settings_licenses_section_callback(){
		echo '<p>' . sprintf( __( 'Enter license keys for our powerful <a href="%s" target="_blank">add-ons</a>.', 'advanced-ads' ), ADVADS_URL . 'add-ons/#utm_source=advanced-ads&utm_medium=link&utm_campaign=settings-licenses' );
		echo ' ' . sprintf( __( 'See also <a href="%s" target="_blank">Issues and questions about licenses</a>.', 'advanced-ads' ), ADVADS_URL . 'manual-category/purchase-licenses/#utm_source=advanced-ads&utm_medium=link&utm_campaign=settings-licenses') . '</p>';
		// nonce field
		echo '<input type="hidden" id="advads-licenses-ajax-referrer" value="' . wp_create_nonce( "advads_ajax_license_nonce" ) . '"/>';
	}
	
	/**
	 * render licenses pithces settings section
	 *
	 * @since 1.8.12
	 */
	public function render_settings_licenses_pitch_section_callback(){
		
		echo '<h3>' . __( 'Are you missing something?', 'advanced-ads' ) . '</h3>';
	    
		Advanced_Ads_Overview_Widgets_Callbacks::render_addons( $hide_activated = true );
	}
	
	/**
	 * render pro pitch settings section
	 *
	 * @since 1.8.12
	 */
	public function render_settings_pro_pitch_section_callback(){
		echo '<br/>';
		include ADVADS_BASE_PATH . 'admin/views/pitch-pro-tab.php';
	}
	
	/**
	 * render tracking pitch settings section
	 *
	 * @since 1.8.12
	 */
	public function render_settings_tracking_pitch_section_callback(){
		echo '<br/>';
		include ADVADS_BASE_PATH . 'admin/views/pitch-tracking.php';
	}

	/**
	 * options to disable ads
	 *
	 * @since 1.3.11
	 */
	public function render_settings_disable_ads(){
		$options = Advanced_Ads::get_instance()->options();

		// set the variables
		$disable_all = isset($options['disabled-ads']['all']) ? 1 : 0;
		$disable_404 = isset($options['disabled-ads']['404']) ? 1 : 0;
		$disable_archives = isset($options['disabled-ads']['archives']) ? 1 : 0;
		$disable_secondary = isset($options['disabled-ads']['secondary']) ? 1 : 0;
		$disable_feed = ( ! isset( $options['disabled-ads']['feed'] ) || $options['disabled-ads']['feed'] ) ? 1 : 0;

		// load the template
		include ADVADS_BASE_PATH . 'admin/views/settings-disable-ads.php';
	}

	/**
	 * render setting to hide ads from logged in users
	 *
	 * @since 1.1.1
	 */
	public function render_settings_hide_for_users(){
		$options = Advanced_Ads::get_instance()->options();
		$current_capability_role = isset($options['hide-for-user-role']) ? $options['hide-for-user-role'] : 0;

		$capability_roles = array(
		'' => __( '(display to all)', 'advanced-ads' ),
		'read' => __( 'Subscriber', 'advanced-ads' ),
		'delete_posts' => __( 'Contributor', 'advanced-ads' ),
		'edit_posts' => __( 'Author', 'advanced-ads' ),
		'edit_pages' => __( 'Editor', 'advanced-ads' ),
		'activate_plugins' => __( 'Admin', 'advanced-ads' ),
		);
		echo '<select name="'.ADVADS_SLUG.'[hide-for-user-role]">';
		foreach ( $capability_roles as $_capability => $_role ) {
			echo '<option value="'.$_capability.'" '.selected( $_capability, $current_capability_role, false ).'>'.$_role.'</option>';
		}
		echo '</select>';

		echo '<p class="description">'. __( 'Choose the lowest role a user must have in order to not see any ads.', 'advanced-ads' ) .'</p>';
	}

	/**
	 * render setting to display advanced js file
	 *
	 * @since 1.2.3
	 */
	public function render_settings_advanced_js(){
		$options = Advanced_Ads::get_instance()->options();
		$checked = ( ! empty($options['advanced-js'])) ? 1 : 0;

		// display notice if js file was overridden
		if( ! $checked && apply_filters( 'advanced-ads-activate-advanced-js', $checked ) ){
			echo '<p>' . __( '<strong>notice: </strong>the file is currently enabled by an add-on that needs it.', 'advanced-ads' ) . '</p>';
		}
		echo '<input id="advanced-ads-advanced-js" type="checkbox" value="1" name="'.ADVADS_SLUG.'[advanced-js]" '.checked( $checked, 1, false ).'>';
		echo '<p class="description">'. sprintf( __( 'Enable advanced JavaScript functions (<a href="%s" target="_blank">here</a>). Some features and add-ons might override this setting if they need features from this file.', 'advanced-ads' ), ADVADS_URL . 'javascript-functions/#utm_source=advanced-ads&utm_medium=link&utm_campaign=settings' ) .'</p>';
	}

	/**
	 * render setting for content injection protection
	 *
	 * @since 1.4.1
	 */
	public function render_settings_content_injection_everywhere(){
		$options = Advanced_Ads::get_instance()->options();
				
                if ( ! isset( $options['content-injection-everywhere'] ) ){
                    $everywhere = 0;
                } elseif ( $options['content-injection-everywhere'] === 'true') {
                    $everywhere = -1;
                } else {
                    $everywhere = absint( $options['content-injection-everywhere'] );
                }

		echo '<input id="advanced-ads-injection-everywhere" type="number" value="' . $everywhere . '" min="-1" name="'.ADVADS_SLUG.'[content-injection-everywhere]">';
		echo '<p class="description">'. __( 'Some plugins and themes trigger ad injections where it shouldn’t happen. Therefore, Advanced Ads ignores injected placements on non-singular pages and outside the loop. However, this can cause problems with some themes. Set this option to -1 in order to enable unlimited ad injection at your own risk, set it to 0 to keep it disabled or choose a positive number to enable the injection only in the first x posts on your archive pages.', 'advanced-ads' ) .'</p>';

	}

	/**
	 * render setting for content injection priority
	 *
	 * @since 1.4.1
	 */
	public function render_settings_content_injection_priority(){
		$options = Advanced_Ads::get_instance()->options();
		$priority = ( isset($options['content-injection-priority'])) ? intval( $options['content-injection-priority'] ) : 100;

		echo '<input id="advanced-ads-content-injection-priority" type="number" value="'.$priority.'" name="'.ADVADS_SLUG.'[content-injection-priority]" size="3"/>';
		echo '<p class="description">';
		if ( $priority < 11 ) {
			echo '<span class="advads-error-message">' . __( 'Please check your post content. A priority of 10 and below might cause issues (wpautop function might run twice).', 'advanced-ads' ) . '</span><br />';
		}
		_e( 'Play with this value in order to change the priority of the injected ads compared to other auto injected elements in the post content.', 'advanced-ads' );
		echo '</p>';
	}

	/**
	 * render setting to disable content injection level limitation
	 *
	 * @since 1.7.22
	 */
	public function render_settings_content_injection_level_limitation(){
		$options = Advanced_Ads::get_instance()->options();
		$checked = ( ! empty($options['content-injection-level-disabled'])) ? 1 : 0;

		echo '<input id="advanced-ads-content-injection-level-disabled" type="checkbox" value="1" name="'.ADVADS_SLUG.'[content-injection-level-disabled]" '.checked( $checked, 1, false ).'>';
		echo '<p class="description">'. __( 'Advanced Ads ignores paragraphs and other elements in containers when injecting ads into the post content. Check this option to ignore this limitation and ads might show up again.', 'advanced-ads' ) . '</p>';
	}
	
	/**
	 * render setting for blocking bots
	 *
	 * @since 1.4.9
	 */
	public function render_settings_block_bots(){
		$options = Advanced_Ads::get_instance()->options();
		$checked = ( ! empty($options['block-bots'])) ? 1 : 0;

		echo '<input id="advanced-ads-block-bots" type="checkbox" value="1" name="'.ADVADS_SLUG.'[block-bots]" '.checked( $checked, 1, false ).'>';
		if( Advanced_Ads::get_instance()->is_bot() ){
			echo '<span class="advads-error-message">' . __( 'You look like a bot', 'advanced-ads' ) . '</a>. </span>';
		}
		echo '<span class="description"><a href="'. ADVADS_URL . 'hide-ads-from-bots/#utm_source=advanced-ads&utm_medium=link&utm_campaign=settings" target="blank">'. __( 'Read this first', 'advanced-ads' ) . '</a></span>';
		echo '<p class="description">'. __( 'Hide ads from crawlers, bots and empty user agents.', 'advanced-ads' ) .'</p>';
	}

	/**
	 * render setting to disable notices
	 *
	 * @since 1.5.3
	 */
	public function render_settings_disabled_notices(){
		$options = Advanced_Ads::get_instance()->options();
		$checked = ( ! empty($options['disable-notices'])) ? 1 : 0;

		echo '<input id="advanced-ads-disabled-notices" type="checkbox" value="1" name="'.ADVADS_SLUG.'[disable-notices]" '.checked( $checked, 1, false ).'>';
		echo '<p class="description">'. __( 'Disable internal notices like tips, tutorials, email newsletters and update notices. Disabling notices is recommended if you run multiple blogs with Advanced Ads already.', 'advanced-ads' ) . '</p>';
	}

	/**
	* render setting for frontend prefix
	*
	* @since 1.6.8
	*/
	public function render_settings_front_prefix(){
		$options = Advanced_Ads::get_instance()->options();

		$prefix = Advanced_Ads_Plugin::get_instance()->get_frontend_prefix();
		$old_prefix = ( isset($options['id-prefix'])) ? esc_attr( $options['id-prefix'] ) : '';

		echo '<input id="advanced-ads-front-prefix" type="text" value="' .$prefix .'" name="'.ADVADS_SLUG.'[front-prefix]" />';
		// deprecated
		echo '<input type="hidden" value="' .$old_prefix .'" name="'.ADVADS_SLUG.'[id-prefix]" />';
		echo '<p class="description">'. __( 'Prefix of class or id attributes in the frontend. Change it if you don’t want <strong>ad blockers</strong> to mark these blocks as ads.<br/>You might need to <strong>rewrite css rules afterwards</strong>.', 'advanced-ads' ) .'</p>';
	}

	/**
	 * render setting to allow editors to manage ads
	 *
	 * @since 1.6.14
	 */
	public function render_settings_editors_manage_ads(){
		$options = Advanced_Ads::get_instance()->options();

		// is false by default if no options where previously set
		if( isset($options['editors-manage-ads']) && $options['editors-manage-ads'] ){
		    $allow = true;
		} else {
		    $allow = false;
		}

		echo '<input id="advanced-ads-editors-manage-ads" type="checkbox" ' . checked( $allow, true, false ) . ' name="'.ADVADS_SLUG.'[editors-manage-ads]" />';
		echo '<p class="description">'. __( 'Allow editors to also manage and publish ads.', 'advanced-ads' ) . 
			' ' . sprintf(__( 'You can assign different ad-related roles on a user basis with <a href="%s" target="_blank">Advanced Ads Pro</a>.', 'advanced-ads' ), ADVADS_URL . 'add-ons/advanced-ads-pro/#utm_source=advanced-ads&utm_medium=link&utm_campaign=settings') . '</p>';
	}

	/**
	 * render setting to add an "Advertisement" label before ads
	 *
	 */
	public function render_settings_add_custom_label(){
		$options = Advanced_Ads::get_instance()->options();

		$enabled = isset( $options['custom-label']['enabled'] );
		$label = ! empty ( $options['custom-label']['text'] ) ? esc_html( $options['custom-label']['text'] ) : _x( 'Advertisements', 'label before ads', 'advanced-ads' );
		?>

		<fieldset>
			<input type="checkbox" <?php checked( $enabled, true ); ?> value="1" name="<?php echo ADVADS_SLUG . '[custom-label][enabled]'; ?>" />
			<input id="advads-custom-label" type="text" value="<?php echo $label; ?>" name="<?php echo ADVADS_SLUG . '[custom-label][text]'; ?>" />
		</fieldset>
	    <p class="description"><?php _e( 'Displayed above ads.', 'advanced-ads' ); ?>&nbsp;<a target="_blank" href="<?php echo ADVADS_URL . 'manual/advertisement-label/#utm_source=advanced-ads&utm_medium=link&utm_campaign=settings-advertisement-label'?>"><?php _e( 'Manual', 'advanced-ads' ); ?></a></p>

        <?php
	}

	/**
	 * render link-nofollow setting
	 *
	 * @since 1.8.4 – moved here from Tracking add-on
	 */
	public function render_settings_link_target_callback(){
	    
		// get option if saved for tracking
		$options = Advanced_Ads::get_instance()->options();
		if( !isset( $options['target-blank'] ) && class_exists( 'Advanced_Ads_Tracking_Plugin' ) ){
			$tracking_options = Advanced_Ads_Tracking_Plugin::get_instance()->options();
			if( isset( $tracking_options['target'] ) ){
				$options['target-blank'] = $tracking_options['target'];
			}
		}
	    
		$target = isset($options['target-blank']) ? $options['target-blank'] : 0;
		include ADVADS_BASE_PATH . 'admin/views/setting-target.php';
	}	
	
	/**
	* render setting 'Delete data on uninstall"
	*
	*/
	public function render_settings_uninstall_delete_data(){
		$options = Advanced_Ads::get_instance()->options();
		$enabled = ! empty( $options['uninstall-delete-data'] ); ?>

		<input type="checkbox" value="1" name="<?php echo ADVADS_SLUG; ?>[uninstall-delete-data]" <?php checked( $enabled, 1 ); ?>>
		<p class="description"><?php _e( 'Clean up all data related to Advanced Ads when removing the plugin.', 'advanced-ads' ); ?></p>
		<?php
	}

	/**
	 * Render setting to disable shortcode button.
	 */
	public function render_settings_disable_shortcode_button(){
		$options = Advanced_Ads::get_instance()->options();

		$checked = ! empty( $options['disable-shortcode-button'] );

		echo '<input id="advanced-ads-disable-shortcode-button" type="checkbox" ' . checked( $checked, true, false ) . ' name="' . ADVADS_SLUG . '[disable-shortcode-button]" />';
		echo '<p class="description">' . __( 'Disable shortcode button in visual editor.', 'advanced-ads' ) . '</p>';
	}

	/**
	 * sanitize plugin settings
	 *
	 * @since 1.5.1
	 * @param array $options all the options
	 */
	public function sanitize_settings($options){

		// sanitize whatever option one wants to sanitize

		if ( isset( $options['front-prefix'] ) ) {
			$options['front-prefix'] = sanitize_html_class( $options['front-prefix'], Advanced_Ads_Plugin::DEFAULT_FRONTEND_PREFIX );
		}

		$options = apply_filters( 'advanced-ads-sanitize-settings', $options );

		// check if editors can edit ads now and set the rights
		// else, remove that right
		$editor_role = get_role( 'editor' );
		if( null == $editor_role ){
		    return $options;
		}
		if( isset($options['editors-manage-ads']) && $options['editors-manage-ads'] ){
			$editor_role->add_cap( 'advanced_ads_see_interface' );
			$editor_role->add_cap( 'advanced_ads_edit_ads' );
			$editor_role->add_cap( 'advanced_ads_manage_placements' );
			$editor_role->add_cap( 'advanced_ads_place_ads' );
		} else {
			$editor_role->remove_cap( 'advanced_ads_see_interface' );
			$editor_role->remove_cap( 'advanced_ads_edit_ads' );
			$editor_role->remove_cap( 'advanced_ads_manage_placements' );
			$editor_role->remove_cap( 'advanced_ads_place_ads' );
		}

		// we need 3 states: ! isset, 1, 0
		$options['disabled-ads']['feed'] = isset( $options['disabled-ads']['feed'] ) ? 1 : 0;
                
                if ( isset( $options['content-injection-everywhere'] ) ){
                    if ( $options['content-injection-everywhere'] == 0 ){
                        unset( $options['content-injection-everywhere'] );
                    } elseif ( $options['content-injection-everywhere'] <= -1 ){
                        $options['content-injection-everywhere'] = "true";
                    } else {
                        $options['content-injection-everywhere'] = absint($options['content-injection-everywhere']);
                    }
                }

		return $options;
	}

}
