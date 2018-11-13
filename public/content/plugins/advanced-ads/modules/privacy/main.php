<?php
if ( class_exists( 'Advanced_Ads', false ) ) {

	// only load if not already existing (maybe included from another plugin)
	if ( defined( 'ADVADS_PRIVACY_SLUG' ) ) {
	    return ;
	}

	// general and global slug, e.g. to store options in WP
	define( 'ADVADS_PRIVACY_SLUG', 'advanced-ads-privacy' );
	
	Advanced_Ads_Privacy::get_instance();
	
	if ( is_admin() ) {
	    Advanced_Ads_Privacy_Admin::get_instance();
	}
}



