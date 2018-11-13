<?php

class Advanced_Ads_AdSense_Admin {

	private $data;
	private $nonce;
	private static $instance = null;
	protected $notice = null;
        private $settings_page_hook = 'advanced-ads-adsense-settings-page';
	
	const	ADSENSE_NEW_ACCOUNT_LINK = 'https://www.google.com/adsense/start/?utm_source=AdvancedAdsPlugIn&utm_medium=partnerships&utm_campaign=AdvancedAdsPartner';

	private function __construct() {
		$this->data = Advanced_Ads_AdSense_Data::get_instance();
		add_action( 'advanced-ads-settings-init', array($this, 'settings_init') );
		// add_action( 'advanced-ads-additional-settings-form', array($this, 'settings_init') );
                add_filter('advanced-ads-setting-tabs', array($this, 'setting_tabs'));

		add_action( 'admin_enqueue_scripts', array($this, 'enqueue_scripts') );
		add_action( 'admin_print_scripts', array($this, 'print_scripts') );
		add_filter( 'advanced-ads-list-ad-size', array($this, 'ad_details_column'), 10, 2 );
		add_filter( 'advanced-ads-ad-settings-pre-save', array($this, 'sanitize_ad_settings') );
		add_filter( 'advanced-ads-ad-notices', array($this, 'ad_notices'), 10, 3 );
	}

	public function ad_details_column($size, $the_ad) {
		if ( 'adsense' == $the_ad->type ) {
			$content = json_decode( $the_ad->content );
			if ( $content && 'responsive' == $content->unitType ) { $size = __( 'Responsive', 'advanced-ads' ); }
		}
		return $size;
	}

	public function print_scripts() {
		global $pagenow, $post_type;
		if (
				('post-new.php' == $pagenow && Advanced_Ads::POST_TYPE_SLUG == $post_type) ||
				('post.php' == $pagenow && Advanced_Ads::POST_TYPE_SLUG == $post_type && isset($_GET['action']) && 'edit' == $_GET['action'])
		) {
			$db = Advanced_Ads_AdSense_Data::get_instance();
			$pub_id = $db->get_adsense_id();
			?>
			<script type="text/javascript">
				var gadsenseData = {
					pubId : '<?php echo $pub_id; ?>',
					msg : {
						unknownAd : '<?php esc_attr_e( "The ad details couldn't be retrieved from the ad code", 'advanced-ads' ); ?>',
						pubIdMismatch : '<?php esc_attr_e( 'Warning : The AdSense account from this code does not match the one set with the Advanced Ads Plugin. This ad might cause troubles when used in the front end.', 'advanced-ads' ); ?>',
					},
					pagenow: '<?php echo $pagenow ?>',
				};
			</script>
			<?php
		}
	}

	public function enqueue_scripts() {
		global $gadsense_globals, $pagenow, $post_type;
		$screen = get_current_screen();
		$plugin = Advanced_Ads_Admin::get_instance();
		if (
				('post-new.php' == $pagenow && Advanced_Ads::POST_TYPE_SLUG == $post_type) ||
				('post.php' == $pagenow && Advanced_Ads::POST_TYPE_SLUG == $post_type && isset($_GET['action']) && 'edit' == $_GET['action'])
		) {
			$default_script = array(
				'path' => GADSENSE_BASE_URL . 'admin/assets/js/new-ad.js',
				'dep' => array('jquery'),
				'version' => null,
			);

			$scripts = array(
				'gadsense-new-ad' => $default_script,
			);

			// Allow modifications of script files to enqueue
			$scripts = apply_filters( 'advanced-ads-gadsense-ad-param-script', $scripts );

			foreach ( $scripts as $handle => $value ) {
				if ( empty($handle) ) {
					continue;
				}
				if ( ! empty($handle) && empty($value) ) {
					// Allow inclusion of WordPress's built-in script like jQuery
					wp_enqueue_script( $handle );
				} else {
					if ( ! isset($value['version']) ) { $value['version'] = null; }
					wp_enqueue_script( $handle, $value['path'], $value['dep'], $value['version'] );
				}
			}

			$styles = array();

			// Allow modifications of default style files to enqueue
			$styles = apply_filters( 'advanced-ads-gadsense-ad-param-style', $styles );

			foreach ( $styles as $handle => $value ) {
				if ( ! isset($value['path']) ||
						! isset($value['dep']) ||
						empty($handle)
				) {
					continue;
				}
				if ( ! isset($value['version']) ) {
					$value['version'] = null; }
				wp_enqueue_style( $handle, $value['path'], $value['dep'], $value['version'] );
			}
		}
	}

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function settings_init() {

                // get settings page hook
		$hook = $this->settings_page_hook;
		
                register_setting( ADVADS_SLUG . '-adsense', ADVADS_SLUG . '-adsense', array($this, 'sanitize_settings') );
		
		// add new section
		add_settings_section(
                        'advanced_ads_adsense_setting_section',
                        '', //__( 'AdSense', 'advanced-ads' ),
                        array($this, 'render_settings_section_callback'),
                        $hook
		);

		// add setting field to disable ads
		add_settings_field(
			'adsense-id',
			__( 'AdSense account', 'advanced-ads' ),
			array($this, 'render_settings_adsense_id'),
			$hook,
			'advanced_ads_adsense_setting_section'
		);		

		// activate AdSense verification code and Auto ads (previously Page-Level ads)
		add_settings_field(
			'adsense-page-level',
			__( 'Verification code & Auto ads', 'advanced-ads' ),
			array($this, 'render_settings_adsense_page_level'),
			$hook,
			'advanced_ads_adsense_setting_section'
		);
		
		// add setting field for adsense limit
		add_settings_field(
			'adsense-limit',
			__( 'Limit to 3 ads', 'advanced-ads' ),
			array($this, 'render_settings_adsense_limit'),
			$hook,
			'advanced_ads_adsense_setting_section'
		);

		// disable AdSense violation warnings
		add_settings_field(
			'adsense-warnings-disable',
			__( 'Disable violation warnings', 'advanced-ads' ),
			array($this, 'render_settings_adsense_warnings_disable'),
			$hook,
			'advanced_ads_adsense_setting_section'
		);

		add_settings_field(
			'adsense-background',
			__( 'Transparent background', 'advanced-ads' ),
			array( $this, 'render_settings_adsense_background' ),
			$hook,
			'advanced_ads_adsense_setting_section'
		);
		
		$adsense_id = $this->data->get_adsense_id();
		
		// if ( !empty( $adsense_id ) ) {
		
			// add_settings_field(
				// 'adsense-mapi',
				// __( 'Google AdSense Management API', 'advanced-ads' ),
				// array( $this, 'render_settings_management_api' ),
				// $hook,
				// 'advanced_ads_adsense_setting_section'
			// );

		// }
		// hook for additional settings from add-ons
		do_action( 'advanced-ads-adsense-settings-init', $hook );
	}

        /**
	 * render adsense settings section
	 *
	 * @since 1.5.1
	 */
	public function render_settings_section_callback(){
		// for whatever purpose there might come
	}

	/**
	 * render adsense management api setting 
	 */
	public function render_settings_management_api() {
		require_once GADSENSE_BASE_PATH . 'admin/views/mapi-settings.php';
	}
	
	/**
	 * render adsense id setting
	 *
	 * @since 1.5.1
	 */
	public function render_settings_adsense_id(){
		require_once GADSENSE_BASE_PATH . 'admin/views/adsense-account.php';
	}

	/**
	 * render adsense limit setting
	 *
	 * @since 1.5.1
	 */
	public function render_settings_adsense_limit(){
                $limit_per_page = $this->data->get_limit_per_page();

                ?><label><input type="checkbox" name="<?php echo GADSENSE_OPT_NAME; ?>[limit-per-page]" value="1" <?php checked( $limit_per_page ); ?> />
		<?php printf( __( 'Limit to %d AdSense ads', 'advanced-ads' ), 3 ); ?></label>
                <p class="description">
		<?php
			printf(
				__( 'There is no explicit limit for AdSense ads anymore, but you can still use this setting to prevent too many AdSense ads to show accidentally on your site.', 'advanced-ads' ),
				esc_url( 'https://www.google.com/adsense/terms' )
			); ?></p>
		<?php if( defined( 'AAP_VERSION' ) ) : /* give warning when cache-busting in Pro is active */ ?>
		<p class="advads-error-message"><?php _e( 'Due to technical restrictions, the limit does not work on placements with cache-busting enabled.', 'advanced-ads' ); ?></p>
		<?php endif;
	}

	/**
	 * render page-level ads setting
	 *
	 * @since 1.6.9
	 */
	public function render_settings_adsense_page_level(){
                $options = $this->data->get_options();
                $page_level = $options['page-level-enabled'];

                ?><label><input type="checkbox" name="<?php echo GADSENSE_OPT_NAME; ?>[page-level-enabled]" value="1" <?php checked( $page_level ); ?> />
		<?php esc_attr_e( 'Insert the AdSense header code used for verification and the Auto Ads feature.', 'advanced-ads' ); 
		if( !empty( $options['adsense-id'] ) ) :
		    ?>&nbsp;<a href="https://www.google.com/adsense/new/u/0/<?php echo $options['adsense-id']; ?>/myads/auto-ads" target="_blank"><?php /**
		    * translators: this is the text for a link to a sub-page in an AdSense account
		    */
		   esc_attr_e( 'Adjust Auto ads options', 'advanced-ads' ); ?></a>
		<?php endif; ?>
                </label><p class="description"><?php printf(__( 'Please read <a href="%s" target="_blank">this article</a> if <strong>ads appear in random places</strong>.', 'advanced-ads' ), ADVADS_URL . 'adsense-in-random-positions-auto-ads/#utm_source=advanced-ads&utm_medium=link&utm_campaign=backend-autoads-ads' ); ?></p>
                <p class="description"><a href="<?php echo ADVADS_URL . 'adsense-auto-ads-wordpress/#Display_Auto_Ads_only_on_specific_pages'; ?>" target="_blank"><?php esc_attr_e( 'Display Auto ads only on specific pages', 'advanced-ads' ); ?></a></p>
                <p class="description"><a href="<?php echo ADVADS_URL . 'adsense-auto-ads-wordpress/#AMP_Auto_Ads'; ?>" target="_blank"><?php esc_attr_e( 'Auto ads on AMP pages', 'advanced-ads' ); ?></a></p><?php
	}

	/**
	 * render AdSense violation warnings setting
	 *
	 * @since 1.6.9
	 */
	public function render_settings_adsense_warnings_disable(){
                $options = $this->data->get_options();
                $disable_violation_warnings = isset( $options['violation-warnings-disable'] ) ? 1 : 0;

                ?><label><input type="checkbox" name="<?php echo GADSENSE_OPT_NAME; ?>[violation-warnings-disable]" value="1" <?php checked( 1, $disable_violation_warnings ); ?> />
		<?php _e( 'Disable warnings about potential violations of the AdSense terms.', 'advanced-ads' ); ?></label>
		<p class="description"><?php printf(__( 'Our <a href="%s" target="_blank">Ad Health</a> feature monitors if AdSense is implemented correctly on your site. It also considers ads not managed with Advanced Ads. Enable this option to remove these checks', 'advanced-ads' ), ADVADS_URL . 'manual/ad-health/#utm_source=advanced-ads&utm_medium=link&utm_campaign=backend-autoads-ads' ); ?></p><?php
	}

	/**
	 * Render transparent background setting.
	 */
	public function render_settings_adsense_background() {
		$options = $this->data->get_options();
		$background = $options['background'];

		?><label><input type="checkbox" name="<?php echo GADSENSE_OPT_NAME; ?>[background]" value="1" <?php checked( $background ); ?> />
		<?php _e( 'Enable this option in case your theme adds an unfortunate background color to AdSense ads.', 'advanced-ads' ); ?></label><?php
	}

        /**
         * sanitize adsense settings
         *
         * @since 1.5.1
         * @param array $options all the options
         */
        public function sanitize_settings($options){

            // sanitize whatever option one wants to sanitize
            if(isset($options['adsense-id']) && $options['adsense-id'] != ''){
		// remove "ca-" prefix if it was added by the user
		if( 0 === strpos( $options['adsense-id'], 'ca-' ) ){
		    $options['adsense-id'] = str_replace( 'ca-', '', $options['adsense-id'] );
		}
		
		if( 0 !== strpos( $options['adsense-id'], 'pub-' ) ){
                    // add settings error
                    add_settings_error(
                            'adsense-limit',
                            'settings_updated',
                            __( 'The Publisher ID has an incorrect format. (must start with "pub-")', 'advanced-ads' ));
                }
		// trim publisher id
		$options['adsense-id'] = trim($options['adsense-id']);
            }

            return $options;
        }

        /**
         * add adsense setting tab
         *
         * @since 1.5.1
         * @param arr $tabs existing setting tabs
         * @return arr $tabs setting tabs with AdSense tab attached
         */
        public function setting_tabs(array $tabs){

            $tabs['adsense'] = array(
                'page' => $this->settings_page_hook,
                'group' => ADVADS_SLUG . '-adsense',
                'tabid' => 'adsense',
                'title' => __( 'AdSense', 'advanced-ads' )
            );

            return $tabs;
        }

	/**
	 * sanitize ad settings
	 *  save publisher id from new ad unit if not given in main options
	 *
	 * @since 1.6.2
	 * @param arr $ad_settings_post
	 * @return arr $ad_settings_post
	 */
	public function sanitize_ad_settings( array $ad_settings_post ){

	    // check ad type
	    if( ! isset( $ad_settings_post['type'] ) ||  'adsense' !== $ad_settings_post['type'] ){
		return $ad_settings_post;
	    }

	    // save AdSense publisher ID if given and remove it from options
	    if ( ! empty($ad_settings_post['output']['adsense-pub-id']) ) {
		    // get options
		    $adsense_options = get_option( 'advanced-ads-adsense', array() );
		    $adsense_options['adsense-id'] = $ad_settings_post['output']['adsense-pub-id'];

		    // save adsense options including publisher id
		    update_option( 'advanced-ads-adsense', $adsense_options );

	    }
	    unset( $ad_settings_post['output']['adsense-pub-id'] );

	    return $ad_settings_post;
	}
	
	/**
	 * show AdSense ad specific notices in parameters box
	 * 
	 * @since 1.7.22
	 */
	public function ad_notices( $notices, $box, $post ){
	    
	    $ad = new Advanced_Ads_Ad( $post->ID );
	    
	    // $content = json_decode( stripslashes( $ad->content ) );
	    
	    switch ($box['id']){
		case 'ad-parameters-box' :
			// add warning if this is a responsive ad unit without custom sizes and position is set to left or right
			// hidden by default and made visible with JS
			$notices[] = array(
				'text' => sprintf(__( 'Responsive AdSense ads don’t work reliably with <em>Position</em> set to left or right. Either switch the <em>Type</em> to "normal" or follow <a href="%s" target="_blank">this tutorial</a> if you want the ad to be wrapped in text.', 'advanced-ads' ), ADVADS_URL . 'adsense-responsive-custom-sizes/#utm_source=advanced-ads&utm_medium=link&utm_campaign=adsense-custom-sizes-tutorial' ),
				'class' => 'advads-ad-notice-responsive-position error hidden',
			);
			// show hint about AdSense In-feed add-on
			if( ! class_exists( 'Advanced_Ads_In_Feed', false ) ){
				$notices[] = array(
					'text' => sprintf(__( '<a href="%s" target="_blank">Install the free AdSense In-feed add-on</a> in order to place ads between posts.', 'advanced-ads' ), wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . 'advanced-ads-adsense-in-feed'), 'install-plugin_' . 'advanced-ads-adsense-in-feed') ),
					'class' => 'advads-ad-notice-in-feed-add-on hidden',
				);
			}
			// show message about Responsive add-on
			if ( ! defined( 'AAR_SLUG' ) ) {
			    $notices[] = array(
				'text' => sprintf( __( 'Use the <a href="%s" target="_blank">Responsive add-on</a> in order to define the exact size for each browser width or choose between horizontal, vertical, or rectangle formats.', 'advanced-ads' ), ADVADS_URL . 'add-ons/responsive-ads/#utm_source=advanced-ads&utm_medium=link&utm_campaign=edit-adsense' ),
				'class' => 'advads-ad-notice-responsive-add-on',
			    );
			}
			
			// show hint about Content ad, Link unit or Matched content being defined in AdSense account
			// disabled since it might no longer be needed with the new ad types
			/* if( 'adsense' === $ad->type ){
			    $notices[] = array(
				    'text' => sprintf( __( 'The type of your AdSense ad unit (content unit, link unit or matched content) needs to be defined in <a href="%s" target="_blank">your AdSense account</a>.', 'advanced-ads' ), 'https://www.google.com/adsense' ),
				    'class' => 'advads-ad-notice-adsense-ad-unit-type',
			    );
			}*/
		    break;
	    }
	    
	    
	    return $notices;
	}

	/**
	 * Get Auto Ads messages.
	 */
	public static function get_auto_ads_messages() {
		return array(
			'enabled' => sprintf(__( 'The AdSense verification and Auto ads code is already activated in the <a href="%s">AdSense settings</a>.', 'advanced-ads' ), 
				admin_url( 'admin.php?page=advanced-ads-settings#top#adsense' ) )
		    . ' ' . __( 'No need to add the code manually here, unless you want to include it into certain pages only.', 'advanced-ads' ),
			'disabled' => sprintf( '%s <button id="adsense_enable_pla" type="button" class="button">%s</button>',
				sprintf ( __( 'The AdSense verification and Auto ads code should be set up in the <a href="%s">AdSense settings</a>. Click on the following button to enable it now.', 'advanced-ads' ), admin_url( 'admin.php?page=advanced-ads-settings#top#adsense' ) ),
				esc_attr__( 'Activate', 'advanced-ads' ) )
		);
	}

}
