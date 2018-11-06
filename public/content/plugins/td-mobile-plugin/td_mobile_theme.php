<?php
/**
 * Created by PhpStorm.
 * User: tagdiv
 * Date: 19.10.2015
 * Time: 15:04
 *
 * @package td-mobile-plugin
 */

/**
 * Class td_mobile_theme
 * Helper class used to interface the used detection library.
 */
class td_mobile_theme {

	const TDM_MOBILE_ON_DESKTOP = 'tdm_mobile_on_desktop';

	/**
	 * Directory name of the mobile theme.
	 * @var $mobile_dir string
	 */
	private static $mobile_dir = '/mobile';

	/**
	 * The detector object.
	 * @var object $mobile_detect object
	 */
	private static $mobile_detect;

	/**
	 * The absolute path to the mobile theme.
	 * @var string $mobile_dir_path
	 */
	static $mobile_dir_path;

	/**
	 * The remained uri path to the mobile theme.
	 * @var string $mobile_uri_path
	 */
	static $mobile_uri_path;

	/**
	 * The full url path to the main theme (It's internally used. It's public just for wp hook callback)
	 * It's computed on the 'setup_theme' wp hook, before mobile theme switching.
	 * @var string $main_uri_path
	 */
	static $main_uri_path;

	static $main_dir_path;


	/**
	 * It detects the mobile.
	 * For some situations, especially for debugging, to force the mobile version of the theme for desktop agents.
	 * For this, it's enough to set the mobile version from theme panel and than call the function with $force_mobile_on_destop = true.
	 * Without parameter, this function detects just the mobile version.
	 *
	 * Example of $force_mobile_on_desktop = true, @see td-mobile-plugin.php
	 * The callback of the the_content wp hook:
	 *      add_filter('the_content', 'td_mobile_the_content', 999);
	 *
	 * It gets the mobile content of a page, on desktop agent.
	 *
	 * @param bool $force_mobile_on_desktop - Force the mobile version of the theme.
	 *
	 * @return bool
	 */
	public static function is_mobile($force_mobile_on_desktop = false) {
		if (!isset(self::$mobile_detect)) {
			self::$mobile_detect = new Mobile_Detect();
		}

		$current_theme = wp_get_theme();
		if ($current_theme !== null && is_a($current_theme, 'WP_Theme') === true) {
			$the_stylesheet = $current_theme->get_stylesheet();

			if (empty($the_stylesheet)) {
				$removed = remove_action('option_stylesheet', array('td_mobile_theme', 'mobile'));
				$the_stylesheet = get_option('stylesheet');

				if ($removed) {
					add_action('option_stylesheet', array('td_mobile_theme', 'mobile'));
				}
			}
			$theme_options = get_option('td_' . $the_stylesheet);

			$use_mobile_version_on_desktop = '';
			$option_key = self::TDM_MOBILE_ON_DESKTOP;

			if (is_array($theme_options) and array_key_exists($option_key, $theme_options)) {
				$use_mobile_version_on_desktop = $theme_options[ $option_key ];
			}

			//self::$is_mobile = ((self::$mobile_detect->isMobile() and ! self::$mobile_detect->isTablet()) or ! empty($use_mobile_version_on_desktop)) and ! is_admin();
			return ($force_mobile_on_desktop && !empty($use_mobile_version_on_desktop)) || (self::$mobile_detect->isMobile() && !self::$mobile_detect->isTablet());
		}
		return false;
	}

	/**
	 * Sets the
	 *      'theme_root',
	 *      'theme_root_uri',
	 *      'setup_theme'
	 * for the mobile theme.
	 * These must be the necessary hooks to set up the theme settings (also see the 'setup_theme' callback function)
	 * The priority must be higher than any other previous settings, to overwrite them.
	 */
	private static function set_mobile_theme() {
		add_action('setup_theme', array(__CLASS__, 'mobile_theme_setup'), 999);
		add_filter('theme_root', array(__CLASS__, 'mobile_theme_root'), 999, 1);
		add_filter('theme_root_uri', array(__CLASS__, 'mobile_theme_root_uri'), 999, 1);
	}

	/**
	 * If mobile, sets the mobile theme settings.
	 */
	static function set_the_theme() {
		if ((self::is_mobile(true) && !is_admin())) {
			self::set_mobile_theme();
		}
	}

	/**
	 * Helper function used by the 'wp_cache_check_mobile' function of the wp super cache plugin, to determine
	 * the proper cache key
	 *
	 * @param string $cache_key - the current cache key.
	 *
	 * @return string
	 */
	static function get_theme_setting($cache_key) {
		if (self::is_mobile()) {
			return 'mobile';
		}
		return 'normal';
	}

	/**
	 * Custom hook function used for the following wp hooks:
	 *      'stylesheet',
	 *      'template',
	 *      'option_template',
	 *      'option_stylesheet'
	 *
	 * @param string $theme - theme id.
	 *
	 * @return mixed|void
	 */
	static function mobile($theme) {
		return apply_filters('td_mobile', '', $theme);
	}

	static function mobile_stylesheet_directory_uri($path) {
		return rtrim($path, '/');
	}

	/**
	 * Hook function used for the wp 'theme_root' hook.
	 *
	 * @param string $theme_root - theme root path.
	 *
	 * @return string
	 */
	static function mobile_theme_root($theme_root) {
		return self::$mobile_dir_path;
	}

	/**
	 * Hook function used for the wp 'theme_root_uri' hook.
	 *
	 * @param string $theme_root_uri - theme URI path.
	 *
	 * @return string
	 */
	static function mobile_theme_root_uri($theme_root_uri) {
		if (isset(self::$mobile_uri_path)) {
			return self::$mobile_uri_path;
		}
		return $theme_root_uri;
	}

	/**
	 * Hook function used for the wp 'setup_theme' hook.
	 * It executes before 'theme_root' and 'theme_root_uri'.
	 *
	 * The 'stylesheet', 'template', 'option_template', 'option_stylesheet' wp hooks are modified, so
	 * any existing settings can be saved before changes.
	 *
	 * The mobile theme directory path is set. It's too late to compute it on 'theme_root' or 'theme_root_uri' hooks,
	 * because they are called after 'theme_setup'.
	 */
	static function mobile_theme_setup() {
		$main_theme = wp_get_theme();

		if ( defined( 'MULTISITE' ) ) {
			$main_dir_path = $main_theme->get_template_directory();

			// @see get_template_directory()
			if ( ! file_exists( $main_dir_path ) && $main_theme->parent() ) {
				$main_dir_path = $main_theme->parent()->theme_root . '/' . $main_theme->stylesheet;
			}
		} else {
			$main_dir_path = $main_theme->get_template_directory();
		}

		$mobile_dir_path = $main_dir_path . self::$mobile_dir;

		if (file_exists($mobile_dir_path)) {

			self::$main_dir_path = $main_dir_path;
			self::$mobile_dir_path = $mobile_dir_path;

			self::$main_uri_path = get_template_directory_uri();
			self::$mobile_uri_path = self::$main_uri_path . self::$mobile_dir;

		} else {
			echo "The mobile theme path couldn't be set. Please check if you already have another mobile theme plugin active and disable it (ex. Jetpack Mobile Theme or similar). If you use a cache plugin clear it before checking the results.";
			die;
		}

		add_action('stylesheet', array(__CLASS__, 'mobile'));
		add_action('template', array(__CLASS__, 'mobile'));
		add_action('option_template', array(__CLASS__, 'mobile'));
		add_action('option_stylesheet', array(__CLASS__, 'mobile'));

		add_action('stylesheet_directory_uri', array(__CLASS__, 'mobile_stylesheet_directory_uri'));
	}
}
