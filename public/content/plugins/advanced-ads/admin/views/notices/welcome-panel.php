<div id="aa-welcome-panel">
<h2><?php _e( 'Welcome to Advanced Ads!', 'advanced-ads' ); ?></h2>
<div class="aa-welcome-panel-column-container">
<div class="aa-welcome-panel-column">
	<h3><?php _e( 'Get Started', 'advanced-ads' ); ?></h3>
	<a href="<?php echo admin_url( 'post-new.php?post_type=advanced_ads' ); ?>" class="button button-primary"><?php _e( 'Create your first ad', 'advanced-ads' ); ?></a>
	<ul>
		<li><a href="<?php echo ADVADS_URL . 'manual/first-ad/#utm_source=advanced-ads&utm_medium=link&utm_campaign=welcome-first-ad'; ?>" target="_blank"><?php _e( 'First ad tutorial', 'advanced-ads' ); ?></a></li>
	</ul>
</div>
<div class="aa-welcome-panel-column">
	<h3><?php _e( 'AdSense Options', 'advanced-ads' ); ?></h3>
	<a href="<?php echo admin_url( 'admin.php?page=advanced-ads-settings#top#adsense' ); ?>" class="button button-primary"><?php _e( 'Import ads from AdSense', 'advanced-ads' ); ?></a>
	<ul>
		<li><a href="<?php echo ADVADS_URL . 'adsense-auto-ads-wordpress/#utm_source=advanced-ads&utm_medium=link&utm_campaign=welcome-auto-ads'; ?>" target="_blank"><?php _e( 'Setting up Auto ads', 'advanced-ads' ); ?></a></li>
		<li><a href="<?php echo ADVADS_URL . 'place-adsense-ad-unit-manually/#utm_source=advanced-ads&utm_medium=link&utm_campaign=welcome-adsense'; ?>" target="_blank"><?php _e( 'Setting up AdSense ads manually', 'advanced-ads' ); ?></a></li>
	</ul>
</div>
<div class="aa-welcome-panel-column aa-welcome-panel-last">
	<h3><?php _e( 'Get Help', 'advanced-ads' ); ?></h3>
	<ul>
		<li><?php printf( __( '<a href="%s" target="_blank">Manual</a>', 'advanced-ads' ), ADVADS_URL . 'manual/#utm_source=advanced-ads&utm_medium=link&utm_campaign=welcome-manual' ); ?></li>
		<li><a href="<?php echo ADVADS_URL . 'support/#utm_source=advanced-ads&utm_medium=link&utm_campaign=welcome-support'; ?>" target="_blank"><?php _e( 'Reach out for help', 'advanced-ads' ); ?></a></li>
	</ul>
</div>
</div>
</div>
