<?php
/**
 * the view for the settings page
 */

// array with setting tabs for frontend
$setting_tabs = apply_filters(
	'advanced-ads-setting-tabs', array(
		'general' => array(
			'page'  => Advanced_Ads_Admin::get_instance()->plugin_screen_hook_suffix,
			'group' => ADVADS_SLUG,
			'tabid' => 'general',
			'title' => __( 'General', 'advanced-ads' ),
		),
	)
);
?><div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<?php Advanced_Ads_Checks::show_issues(); ?>
	
	<?php settings_errors(); ?>
	<div class="nav-tab-wrapper" id="advads-tabs">
		<?php foreach ( $setting_tabs as $_setting_tab_id => $_setting_tab ) : ?>
			<a class="nav-tab" id="<?php echo $_setting_tab_id; ?>-tab"
				href="#top#<?php echo $_setting_tab_id; ?>"><?php echo $_setting_tab['title']; ?></a>
		<?php endforeach; ?>
		<a class="nav-tab" id="support-tab"
				href="#top#support"><?php _e( 'Support', 'advanced-ads' ); ?></a>
	</div>
		<?php foreach ( $setting_tabs as $_setting_tab_id => $_setting_tab ) : ?>
			<div id="<?php echo $_setting_tab_id; ?>" class="advads-tab">
				<div id="advads-sub-menu-<?php echo $_setting_tab_id; ?>" class="advads-tab-sub-menu"></div>
				<form class="advads-settings-tab-main-form" method="post" action="options.php">
					<?php
					if ( isset( $_setting_tab['group'] ) ) {
						settings_fields( $_setting_tab['group'] );
					}
					do_settings_sections( $_setting_tab['page'] );

					do_action( 'advanced-ads-settings-form', $_setting_tab_id, $_setting_tab );
					if ( isset( $_setting_tab['group'] ) && 'advanced-ads-licenses' !== $_setting_tab['group'] ) {
						submit_button( __( 'Save settings on this page', 'advanced-ads' ) );
					}
					?>
				</form>
				<?php do_action( 'advanced-ads-settings-tab-after-form', $_setting_tab_id, $_setting_tab ); ?>
			<?php if ( 'general' === $_setting_tab_id ) : ?>
			<ul>
				<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=advanced-ads-import-export' ) ); ?>"><?php _e( 'Import &amp; Export', 'advanced-ads' ); ?></a></li>
			</ul>
			<?php endif; ?>
			</div>
		<?php endforeach; ?>
	<div id="support" class="advads-tab">
		<?php require_once ADVADS_BASE_PATH . 'admin/views/support.php'; ?>
	</div>
		<?php
			do_action( 'advanced-ads-additional-settings-form' );
			// print the filesystem credentials modal if needed
			Advanced_Ads_Filesystem::get_instance()->print_request_filesystem_credentials_modal();
		?>

</div>
<script>
// menu tabs
jQuery( '#advads-tabs' ).find( 'a' ).click(function () {
	jQuery( '#advads-tabs' ).find( 'a' ).removeClass( 'nav-tab-active' );
	jQuery( '.advads-tab' ).removeClass( 'active' );

	var id = jQuery( this ).attr( 'id' ).replace( '-tab', '' );
	jQuery( '#' + id ).addClass( 'active' );
	jQuery( this ).addClass( 'nav-tab-active' );
});

// activate specific or first tab

var active_tab = window.location.hash.replace( '#top#', '' );
if (active_tab == '' || active_tab == '#_=_') {
	active_tab = jQuery( '.advads-tab' ).attr( 'id' );
}
jQuery( '#' + active_tab ).addClass( 'active' );
jQuery( '#' + active_tab + '-tab' ).addClass( 'nav-tab-active' );
jQuery( '.nav-tab-active' ).click();
// set all tab urls
advads_set_tab_hashes();

// While user is already on the Settings page, find links (in admin menu,
// in the Checks at the top) to particular setting tabs and open them on click.
jQuery( '.toplevel_page_advanced-ads a[href*="#top"], .message a[href*="#top"]' ).click( function() {
	// Already on the Settings page, so simulate another click on a tab.
	if ( window.location.href.indexOf( 'page=advanced-ads-settings' ) ) {
		// Extract the tab id from the url.
		var tab = jQuery( this ).attr( 'href' ).split( 'advanced-ads-settings#top#' )[1];
		jQuery( '#advads-tabs' ).find( 'a#' + tab + '-tab' ).click();
	}
});

// dynamically generate the sub-menu
jQuery( '.advads-tab-sub-menu' ).each( function( key, e ){
	// abort if scrollIntoView is not supported; we can’t use anchors because they are used for tabs already
	if (typeof e.scrollIntoView !== "function") { return; };
	// get all h2 headlines
	advads_settings_parent_tab = jQuery( e ).parent( '.advads-tab');
	var headlines = advads_settings_parent_tab.find( 'h2' );
	// create list
	if( headlines.length ){
	advads_submenu_list = jQuery('<ul>');
	headlines.each( function( key, h ){
		// create anchor for this headline
		var headline_id = 'advads-tab-headline-' + advads_settings_parent_tab.attr( 'id' ) + key;
		jQuery( h ).attr( 'id', headline_id );
		// place the link in the top menu
		var text = text = h.textContent || h.innerText;
		jQuery( '<li><a onclick="document.getElementById(\'' + headline_id + '\').scrollIntoView()">' + text + '</a></li>' ).appendTo( advads_submenu_list );
	});
	// place the menu
	advads_submenu_list.appendTo( e );
	}
});

</script>
