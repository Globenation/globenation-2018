<div class="advads-ad-block-check">
	<div class="message error update-message notice notice-alt notice-error" style="display: none;"><p><?php _e( 'Please disable your <strong>AdBlocker</strong> to prevent problems with your ad setup.', 'advanced-ads' ); ?></p></div>
</div>
<script>
jQuery( document ).ready( function() {
	if ( typeof advanced_ads_adblocker_test === 'undefined' ) {
		jQuery('.advads-ad-block-check .message').show();
	}
} );
</script>
