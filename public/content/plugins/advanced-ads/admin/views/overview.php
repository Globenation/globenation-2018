<?php
/**
 * Advanced Ads overview page in the dashboard
 */

$title = __( 'Ads Dashboard', 'advanced-ads' );

?><div class="wrap">
	<h1><?php echo esc_html( $title ); ?></h1>

	<div id="advads-overview">
	<?php Advanced_Ads_Overview_Widgets_Callbacks::setup_overview_widgets(); ?>
	</div><!-- dashboard-widgets-wrap -->
	<?php do_action( 'advanced-ads-admin-overview-after' ); ?>
</div><!-- wrap -->
