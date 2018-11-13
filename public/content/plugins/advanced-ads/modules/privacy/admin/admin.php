<?php

class Advanced_Ads_Privacy_Admin
{
	/**
	 * Singleton instance of the plugin
	 *
	 * @var     Advanced_Ads_Privacy_Admin
	 */
	protected static $instance;

	/**
	 * module options
	 *
	 * @var     array (if loaded)
	 */
	protected $options;

	/**
	 * Initialize the module
	 *
	 */
	private function __construct() {
	    
		// add module settings to Advanced Ads settings page
		add_action( 'advanced-ads-settings-init', array( $this, 'settings_init' ), 20, 1 );
		add_filter('advanced-ads-setting-tabs', array($this, 'setting_tabs'), 20 );
		
		// additional ad options
		add_action('advanced-ads-ad-params-after', array($this, 'render_ad_options'), 20, 2);
		add_filter( 'advanced-ads-save-options', array( $this, 'save_ad_options' ), 10, 2 );
	}

	/**
	 * Return an instance of Advanced_Ads_Privacy_Admin
	 *
	 * @return  Advanced_Ads_Privacy_Admin
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if (null === self::$instance)
		{
			self::$instance = new self;
		}

		return self::$instance;
	}
	
	/**
	 * add tracking settings tab
	 *
	 * @since 1.8.30
	 * @param arr $tabs existing setting tabs
	 * @return arr $tabs setting tabs with AdSense tab attached
	 */
	public function setting_tabs(array $tabs) {
	    
	    $tabs['privacy'] = array(
		// TODO abstract string
		'page' => ADVADS_PRIVACY_SLUG . '-settings',
		'group' => ADVADS_PRIVACY_SLUG,
		'tabid' => 'privacy',
		'title' => __( 'Privacy', 'advanced-ads' )
	    );

	    return $tabs;
	}
	
	/**
	 * add settings to settings page
	 *
	 * @param string $hook settings page hook
	 */
	public function settings_init($hook) {

		register_setting( ADVADS_PRIVACY_SLUG, Advanced_Ads_Privacy::OPTION_KEY );

		// add new section
		add_settings_section(
			ADVADS_PRIVACY_SLUG . '_settings_section', '', array($this, 'render_settings_section'), ADVADS_PRIVACY_SLUG . '-settings'
		);
		
		add_settings_field(
			'enable-privacy-module', __('Enable Privacy module', 'advanced-ads'), array($this, 'render_settings_enable_module'), ADVADS_PRIVACY_SLUG . '-settings', ADVADS_PRIVACY_SLUG . '_settings_section'
		);
		add_settings_field(
			'consent-method', __('Consent method', 'advanced-ads'), array($this, 'render_settings_consent_method'), ADVADS_PRIVACY_SLUG . '-settings', ADVADS_PRIVACY_SLUG . '_settings_section'
		);
	}
	
	
	/**
	 * render settings section
	 */
	public function render_settings_section() {

	}
	
	/**
	 * Render enable module setting
	 */
	public function render_settings_enable_module() {
		$options = Advanced_Ads_Privacy::get_instance()->options();
		$module_enabled = isset( $options['enabled']) ? $options['enabled'] : false;
		require ADVADS_BASE_PATH . 'modules/privacy/admin/views/setting-enable.php';
	}
	
	/**
	 * Render setting to choose the cookie method to hide ads
	 */
	public function render_settings_consent_method() {
		$options = Advanced_Ads_Privacy::get_instance()->options();
		$methods =  Advanced_Ads_Privacy::get_instance()->get_consent_methods();
		$current_method = isset( $options['consent-method']) ? $options['consent-method'] : '0';
		$custom_cookie_name = isset( $options['custom-cookie-name'] ) ? $options['custom-cookie-name'] : '';
		$custom_cookie_value = isset( $options['custom-cookie-value'] ) ? $options['custom-cookie-value'] : '';
		$show_non_personalized_adsense = isset( $options['show-non-personalized-adsense']) ? 1 : false;
		require ADVADS_BASE_PATH . 'modules/privacy/admin/views/setting-consent-method.php';
	}
	
	/**
	 * add options to ad edit page
	 *
	 * @param obj $ad ad object
	 * @param arr $types ad types
	 */
	public function render_ad_options( $ad, $types ) {

		if (!isset($ad->id) || empty($ad->id)) {
			return;
		}

		$ad = new Advanced_Ads_Ad($ad->id);
		$ad_options = $ad->options();
		$ad_privacy_options = isset( $ad_options['privacy'] ) ? $ad_options['privacy'] : array();
		$ignore_consent = isset( $ad_privacy_options['ignore-consent']) ? true : false;

		$privacy_options = Advanced_Ads_Privacy::get_instance()->options();
		$module_enabled = ! empty( $privacy_options['enabled'] );

		// If the module is not enabled and the option wasn't checked before.
		if ( ! $module_enabled && ! $ignore_consent ) {
			return;
		}

		include ADVADS_BASE_PATH . 'modules/privacy/admin/views/setting-ad-ignore-consent.php';

	}

	/**
	 * Save ad options.
	 *
	 * @param arr $options
	 * @param obj $ad Advanced_Ads_Ad
	 * @return arr $options
	 */
	public function save_ad_options( $options = array(), Advanced_Ads_Ad $ad ) {
		if ( isset( $_POST['advanced_ad']['privacy'] ) ) {
			$options['privacy'] = $_POST['advanced_ad']['privacy'];
		} else {
			unset( $options['privacy'] );
		}

		return $options;
	}

}
