<?php
if ( class_exists( 'Advanced_Ads', false ) ) {

	// only load if not already existing (maybe included from another plugin)
	if ( defined( 'ADVADS_AB_BASE_PATH' ) ) {
	    return ;
	}

	// load basic path to the plugin
	define( 'ADVADS_AB_BASE_PATH', plugin_dir_path( __FILE__ ) );
	// general and global slug, e.g. to store options in WP, textdomain
	define( 'ADVADS_AB_SLUG', 'advanced-ads-ab-module' );

	Advanced_Ads_Ad_Blocker::get_instance();

	$is_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;

	if ( is_admin() && ! $is_ajax ) {
	    Advanced_Ads_Ad_Blocker_Admin::get_instance();
	}
}



