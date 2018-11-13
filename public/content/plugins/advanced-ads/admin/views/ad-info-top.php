<?php // display ad wizard controls
?><button type="button" id="advads-start-wizard" class="dashicons-before dashicons-controls-play page-title-action"><?php _e( 'Start Wizard', 'advanced-ads' ); ?></button>
<button type="button" id="advads-stop-wizard" class="advads-stop-wizard dashicons-before dashicons-no page-title-action hidden"><?php _e( 'Stop Wizard', 'advanced-ads' ); ?></button>
<script>
	// move wizard button to head
	jQuery('#advads-start-wizard').appendTo('h1');
	jQuery('.advads-stop-wizard').insertAfter('h1');
</script>
<?php
// show wizard welcome message
if ( $this->show_wizard_welcome() || ! Advanced_Ads::get_number_of_ads() ) :
	?>
<div  class="advads-ad-metabox postbox">
	<?php
	if ( ! Advanced_Ads::get_number_of_ads() ) {
		include ADVADS_BASE_PATH . 'admin/views/ad-list-no-ads.php';
	} if ( $this->show_wizard_welcome() ) :
		?>
<div id="advads-wizard-welcome">
	<br/>
		<?php
		/*
		<h2><?php _e( 'Welcome to the Wizard', 'advanced-ads' ); ?></h2>
		<p><?php _e( 'The Wizard helps you to quickly create and publish an ad. Therefore, only the most common options are visible.', 'advanced-ads' ); ?></p>*/
		?>
	<a class="advads-stop-wizard dashicons-before dashicons-no" style="line-height: 1.6em; cursor: pointer;"><?php _e( 'Stop Wizard and show all options', 'advanced-ads' ); ?></a>
</div>
<script>
	// move wizard button to head
	jQuery('#advads-hide-wizard-welcome').click( function(){ jQuery( '#advads-wizard-welcome' ).remove(); });
	jQuery('#advads-end-wizard').insertBefore('h1');
</script>
		<?php
	endif;
	?>
	</div>
	<?php
endif;
