<?php

/**
 * a couple of checks to see if there is any critical issue
 * listed on support and settings page
 */

$messages = array();
if ( ! Advanced_Ads_Checks::php_version_minimum() ) :
	$messages[] = sprintf( __( 'Your <strong>PHP version (%1$s) is too low</strong>. Advanced Ads is built for PHP %2$s and higher. It might work, but updating PHP is highly recommended. Please ask your hosting provider for more information.', 'advanced-ads' ), phpversion(), Advanced_Ads_Checks::MINIMUM_PHP_VERSION );
	endif;
if ( Advanced_Ads_Checks::cache() && ! defined( 'AAP_VERSION' ) ) :
	$messages[] = sprintf( __( 'Your <strong>website uses cache</strong>. Some dynamic features like ad rotation or visitor conditions might not work properly. Use the cache-busting feature of <a href="%s" target="_blank">Advanced Ads Pro</a> to load ads dynamically.', 'advanced-ads' ), ADVADS_URL . 'add-ons/advanced-ads-pro/#utm_source=advanced-ads&utm_medium=link&utm_campaign=support' );
	endif;
if ( Advanced_Ads_Checks::wp_update_available() ) :
	$messages[] = __( 'There is a <strong>new WordPress version available</strong>. Please update.', 'advanced-ads' );
	endif;
if ( Advanced_Ads_Checks::plugin_updates_available() ) :
	$messages[] = __( 'There are <strong>plugin updates available</strong>. Please update.', 'advanced-ads' );
	endif;
if ( Advanced_Ads_Checks::active_autoptimize() && ! defined( 'AAP_VERSION' ) ) :
	$messages[] = sprintf( __( '<strong>Autoptimize plugin detected</strong>. While this plugin is great for site performance, it is known to alter code, including scripts from ad networks. <a href="%s" target="_blank">Advanced Ads Pro</a> has a build-in support for Autoptimize.', 'advanced-ads' ), ADVADS_URL . 'add-ons/advanced-ads-pro/#utm_source=advanced-ads&utm_medium=link&utm_campaign=support' );
	endif;
if ( count( Advanced_Ads_Checks::conflicting_plugins() ) ) :
	$messages[] = sprintf( __( 'Plugins that are known to cause (partial) problems: <strong>%1$s</strong>. <a href="%2$s" target="_blank">Learn more</a>.', 'advanced-ads' ), implode( ', ', Advanced_Ads_Checks::conflicting_plugins() ), ADVADS_URL . 'manual/known-plugin-conflicts/#utm_source=advanced-ads&utm_medium=link&utm_campaign=support' );
	endif;
if ( count( Advanced_Ads_Checks::php_extensions() ) ) :
	$messages[] = sprintf( __( 'Missing PHP extensions could cause issues. Please ask your hosting provider to enable them: %s', 'advanced-ads' ), implode( ', ', Advanced_Ads_Checks::php_extensions() ) );
	endif;
if ( Advanced_Ads_Checks::ads_disabled() ) :
	$messages[] = sprintf( __( 'Ads are disabled for all or some pages. See "disabled ads" in <a href="%s">settings</a>.', 'advanced-ads' ), admin_url( 'admin.php?page=advanced-ads-settings#top#general' ) );
	endif;
if ( defined( 'IS_WPCOM' ) ) :
	$messages[] = __( 'This site is hosted on wordpress.com.', 'advanced-ads' );
	endif;

if ( Advanced_Ads_Checks::get_defined_constants() ) {
	$messages[] = '<a href="' . admin_url( 'admin.php?page=advanced-ads-settings#top#support' ) . '">' . esc_html__( 'Advanced Ads related constants enabled', 'advanced-ads' ) . '</a>';
}

	Advanced_Ads_Checks::jquery_ui_conflict();

	$messages = apply_filters( 'advanced-ads-support-messages', $messages );

if ( count( $messages ) ) :
	?><div class="message error">
	<?php
	foreach ( $messages as $_message ) :
		?>
	<p><?php echo $_message; ?></p>
		<?php
endforeach;
	?>
	</div>
	<?php
	endif;
