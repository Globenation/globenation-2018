<?php
/**
 * Plugin Name: tagDiv Mobile Theme
 * Plugin URI: http://forum.tagdiv.com/the-mobile-theme/
 * Description: tagDiv Mobile Theme. Loads a simple mobile theme on small devices. Please note that the theme is already responsive and this plugin is not required.
 * Author: tagDiv
 * Version: 1.1 | built on 12.10.2018 9:30
 * Author URI: http://tagdiv.com
 *
 * @package td-mobile-plugin
 */

if ( !file_exists(get_template_directory() . '/mobile/functions.php') ||
     !file_exists(get_template_directory() . '/includes/wp_booster/td_wp_booster_functions.php')) {

	add_action( 'admin_notices', 'td_mobile_msg' );

	/**
	 * Admin notice function to inform user that current activated theme does not have support for the mobile theme.
	 */
	function td_mobile_msg() {
		?>

		<div class="error">
			<p><?php echo '<strong style="color:red">The TagDiv Mobile Theme is not supported by the current activated theme. Please update your theme!</strong>' ?></p>
			<p><?php echo 'File message: ' . __FILE__ ?></p>
		</div>

	<?php
	}
	return;
}

//hash
define('TD_MOBILE_PLUGIN', '6744d78cb08fb46c9ae845e7a8cb6dc8');


// The mobile detection library used.
require_once 'Mobile_Detect.php';

// The mobile theme setting hooks.
require_once 'td_mobile_theme.php';


if (!defined('TD_MOBILE_EDITOR_META_KEY')) {
	define('TD_MOBILE_EDITOR_META_KEY', 'td_wp_editor');
}

// If mobile or mobile on desktop, and not admin, sets the mobile theme settings.
td_mobile_theme::set_the_theme();





add_action('td_global_after', 'td_on_global_after_mobile_panel');
function td_on_global_after_mobile_panel() {

	if (is_admin() && array_key_exists('theme_panel', td_global::$all_theme_panels_list) && array_key_exists('panels', td_global::$all_theme_panels_list['theme_panel'])) {





		$separator_panel = 'td-panel-separator-plugin';

		if (! in_array($separator_panel, td_global::$all_theme_panels_list['theme_panel']['panels'])) {
			td_global::$all_theme_panels_list['theme_panel']['panels'][$separator_panel] = array(
				'text' => 'PLUGINS\' SETTINGS',
				'type' => 'separator',
			);
		}

		td_global::$all_theme_panels_list['theme_panel']['panels']['td-mobile-plugin'] = array(
			'text' => 'MOBILE THEME',
			'ico_class' => 'td-ico-responsive',
			'file' => plugin_dir_path(__FILE__) . '/td_panel_settings.php',
			'type' => 'in_theme',
		);
	}
}



// If wp super cache plugin is activated, the 'wp_cache_check_mobile' function is used to determine the cache key used.
// It uses a string key 'mobile' for mobile devices, 'normal' otherwise
//
// @see wp_cache_check_mobile function.
if (file_exists(WP_CONTENT_DIR . '/plugins/wp-super-cache/wp-cache-phase1.php')) {

	/*
	 * That's how Jetpack is checking for its mobile theme.
	 *
	 * do_cacheaction('wp_cache_check_mobile') there's only in 'wp_cache_check_mobile' method of the wp-cache-phase1.php,
	 * which is actually bound to 'supercache_filename_str' cache action using add_cacheaction( 'supercache_filename_str', 'wp_cache_check_mobile' );
	 *
	 * Then do_cacheaction( 'supercache_filename_str', $extra_str ); is called only by 'supercache_filename' method of the wp-cache-phase1.php, which actually determines the
	 * cached filename.
	 *
	 * But, the 'supercache_filename' look like this
	 *
	 * function supercache_filename() {
	 * 	//Add support for https and http caching
	 * 	$is_https = ( ( isset( $_SERVER[ 'HTTPS' ] ) && 'on' ==  strtolower( $_SERVER[ 'HTTPS' ] ) ) || ( isset( $_SERVER[ 'HTTP_X_FORWARDED_PROTO' ] ) && 'https' == strtolower( $_SERVER[ 'HTTP_X_FORWARDED_PROTO' ] ) ) ); //Also supports https requests coming from an nginx reverse proxy
	 * 	$extra_str = $is_https ? '-https' : '';
	 *
	 * 	if ( function_exists( "apply_filters" ) ) {
	 * 		$extra_str = apply_filters( 'supercache_filename_str', $extra_str );
	 * 	} else {
	 * 		$extra_str = do_cacheaction( 'supercache_filename_str', $extra_str );
	 * 	}
	 * 	$filename = 'index' . $extra_str . '.html';
	 * 	return $filename;
	 * }
	 *
	 * Important! So finally, as you can see above, the 'supercache_filename_str' wp filter is used instead, whose callback is set by the 'wp_cache_phase2' method of wp-cache-phase2.php, AND NOT
	 * EXECUTED when the 'Lock Down' option is enabled.
	 */


	/**
	 * - Very iportant! The following code lines are for when it's impossible for the wp super cache plugin to detect the mobile device, or when we want to overwrite
	 * the normal functionality
	 * - Usually, when the plugin is updated, and the device browser is not too old, the mobile environment is detected
	 */
	if (function_exists('add_cacheaction') === true) {
		add_cacheaction('wp_cache_check_mobile', 'td_mobile_theme_setting');
	}


	// If WP Super Cache 'Lock Down' option is enabled, the cache plugin will use the 'supercache_filename_str' filter
	// to determine the cached filename. So, for that case it's necessary to add a supplementary filter callback 'td_formatted_mobile_theme_setting'
	//
	// Usually this filter is set by the 'wp_cache_phase2' method of wp-cache-phase2.php, who is called by 'wp_cache_postload' wp method
	if (defined('WPLOCKDOWN' ) && constant('WPLOCKDOWN') && function_exists('add_filter')) {
		add_filter('supercache_filename_str', 'td_formatted_mobile_theme_setting');
	}
}

/**
 * Wrapper function.
 *
 * @see TdMobileTheme::get_theme_setting
 *
 * @param string $cache_key - the current cache key.
 *
 * @return string
 */
function td_mobile_theme_setting($cache_key) {
	return td_mobile_theme::get_theme_setting($cache_key);
}

/**
 * Callback of the filter 'supercache_filename_str' (WP Super Cache).
 * Returns the extension that should be added to the cached filename when WP Super Cache 'Lock Down' option is enabled.
 *
 * @param $cache_key
 *
 * @return string
 */
function td_formatted_mobile_theme_setting($cache_key) {
	$theme_setting = td_mobile_theme::get_theme_setting($cache_key);

	switch ($theme_setting) {
		case 'mobile':  return '-' . $theme_setting;
		case 'normal' : return '';
	}
}

/*
 * wp hook - 'current_screen' has the global $current_screen set (the global variable isn't set on 'init' wp hook - the wp hook used before)
 */
add_action('current_screen', 'td_on_current_screen');
/**
 * Hook callback function for rendering a wp editor
 */
function td_on_current_screen() {

	// The screen must be checked, because without it, the mobile wp tinymce editor is created, and
	// the save_post callbacks are called (@see td_wp_editor class)
	global $current_screen;

	//var_dump( $current_screen );

	//if (!td_mobile_theme::is_mobile() and is_admin() and current_user_can('edit_pages')) {
	if (is_admin() and current_user_can('edit_pages') and isset($current_screen) and $current_screen->post_type === 'page') {

		// The mobile editor class.
		$editor_file_path = get_template_directory() . '/includes/wp_booster/wp-admin/tinymce/td_wp_editor.php';
		if (!file_exists($editor_file_path)) {
			return;
		}
		require_once ($editor_file_path);
		new td_wp_editor('', 'mobile_tinymce_editor', array(), TD_MOBILE_EDITOR_META_KEY, 'Mobile Editor', array('page'));
	}
}

/**
 * woocommerce_product_archive_description is a WC function template that's overwritten
 * @see woocommerce/includes/wc-templates-functions.php
 */

if (!function_exists('woocommerce_product_archive_description')) {
	function woocommerce_product_archive_description() {
		if ( is_post_type_archive( 'product' ) && get_query_var( 'paged' ) < 2 ) {
			$shop_page = get_post( wc_get_page_id( 'shop' ) );
			if ( $shop_page ) {
				$description = wc_format_content( get_post_meta( $shop_page->ID, TD_MOBILE_EDITOR_META_KEY, true ) );
				if ( $description ) {
					echo '<div class="page-description">' . $description . '</div>';
				}
			}
		}
	}
}

/*
 * wp hook - get the post meta value as custom content before normal priority (10), to allow Visual Composer applying
 * shortcodes over this custom content. This way the VC plugin will load only the necessary resources (js,css,etc) corresponding
 * to this custom content, and not to the regular post content
 */
add_filter('the_content', 'td_mobile_get_the_content_for_vc', 9);
/**
 * Hook callback function that change the post content for VC
 *
 * @param $content
 *
 * @return mixed
 */
function td_mobile_get_the_content_for_vc($content) {
	if (td_mobile_theme::is_mobile(true) and is_page()) {
		global $post;

		$the_content = get_the_content( $post );

		// The woo pages (cart, checkout and my account) are excluded from this applying filter, because their content must be shown as it is.
		// Maybe it's better to have a section in the mobile theme panel, to customize the pages that need to be excluded.
		if ( false !== array_search( $the_content, array(
				'[woocommerce_cart]',
				'[woocommerce_checkout]',
				'[woocommerce_my_account]'
			) )
		) {
			return $content;
		}

		// Important! 'td_mobile_get_the_content_for_vc' MUST BE REMOVED TO 'the_content', for not having an infinite cycle
		remove_filter('the_content', 'td_mobile_get_the_content_for_vc', 9);

		$mobile_content = get_post_meta( $post->ID, TD_MOBILE_EDITOR_META_KEY, true );


		// Important! The wptexturize function replace && with #038 in the content. Strangely, if affects the js script of some plugins (ex rev_slider)
		// So, for the mobile content, we remove it, apply 'the_content' filter and then add it again.
		$wptexturize_priority = has_filter('the_content', 'wptexturize');
		if ( false !== $wptexturize_priority ) {
			remove_filter( 'the_content', 'wptexturize', $wptexturize_priority );
		}

//		// 'wpautop' also generates issues for js script
//		$wpautop_priority = has_filter( 'the_content', 'wpautop' );
//		if ( false !== $wpautop_priority ) {
//			remove_filter( 'the_content', 'wpautop', $wpautop_priority );
//		}

		$mobile_content = apply_filters('the_content', $mobile_content);
		$mobile_content = str_replace(']]>', ']]&gt;', $mobile_content);

		// Important! 'td_mobile_get_the_content_for_vc' MUST BE REATTACHED TO 'the_content', because it's necessary for plugins that extract the post content multiple times (ex.yoast plugin)
		add_filter('the_content', 'td_mobile_get_the_content_for_vc', 9);

		return $mobile_content;
	}
	return $content;
}

//add_filter( 'no_texturize_shortcodes', 'shortcodes_to_exempt_from_wptexturize' );
//function shortcodes_to_exempt_from_wptexturize( $shortcodes ) {
//    $shortcodes[] = 'rev_slider';
//    return $shortcodes;
//}

//add_action('init', 'td_on_init');
//function td_on_init() {
//
//	add_filter( 'no_texturize_tags', 'td_on_no_texturize_tags' );
//	function td_on_no_texturize_tags( $tags ) {
//
//		$tags[] = '&&';
//		return $tags;
//	}
//}



// wp hook - it registers mobile menu locations
add_action('init', 'on_td_init_register_nav_menu' , 11);
/**
 * wp hook callback function
 * It registers additional mobile menu locations.
 */
function on_td_init_register_nav_menu() {
	register_nav_menus(
		array(
			'header-menu-mobile' => 'Header Menu Mobile (main)',
			'footer-menu-mobile' => 'Footer Menu Mobile',
		)
	);
}

/**
 * It gets the main theme key.
 * @return mixed|void
 */
function td_get_actual_current_theme() {
	$removed = remove_action('option_stylesheet', array('td_mobile_theme', 'mobile'));
	$stylesheet = get_option('stylesheet');

	if ($removed) {
		add_action('option_stylesheet', array('td_mobile_theme', 'mobile'));
	}
	return $stylesheet;
}

function td_get_menu_locations() {
	$theme_slug = td_get_actual_current_theme();
	$mods = get_option("theme_mods_{$theme_slug}");

	if (isset($mods['nav_menu_locations']) && !empty($mods['nav_menu_locations'])) {
		return $mods['nav_menu_locations'];
	}
	return false;
}

function td_wp_nav_menu($args) {
	$location = td_get_menu_locations();
	if ($location !== false && array_key_exists($args['theme_location'], $location)) {
		$menu_id = $location[ $args['theme_location'] ];
		$args['menu'] = $menu_id;
	}
	wp_nav_menu($args);
}



// This is done just for having admin capabilities for mobile theme in the theme panel
add_action('td_global_after', 'on_td_global_after');
function on_td_global_after() {
	//if (!td_mobile_theme::is_mobile() and is_admin()) {
	if (is_admin()) {

		require_once (get_template_directory() . '/mobile/includes/td_global_mob.php');
		require_once (get_template_directory() . '/mobile/includes/td_config_mob.php');

		td_config_mob::on_td_global_after_config();
	}
}

/**
 * Here we need to catch the td_ajax_search ajax request, and to call the mobile version of
 * the ajax search.
 *
 * These hooks could not be implemented into the mobile theme, because the mobile theme is not set
 * for admin url (the main theme is set for admin url requests)
 */
add_action('wp_ajax_nopriv_td_ajax_search', 'td_on_mobile_ajax_search');
add_action('wp_ajax_td_ajax_search',        'td_on_mobile_ajax_search');

function td_on_mobile_ajax_search() {

	if (td_mobile_theme::is_mobile(true)) {

		require_once(get_template_directory() . '/mobile/includes/td_ajax_mob.php');
		//require_once(get_template_directory() . '/mobile/includes/modules/td_module_mob_1.php');

		die(json_encode(td_ajax_mob::on_ajax_search()));
	}
}

