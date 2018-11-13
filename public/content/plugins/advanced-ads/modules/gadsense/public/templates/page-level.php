<?php if ( ! $privacy_enabled ) : ?>
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<script>
  (adsbygoogle = window.adsbygoogle || []).push({
    google_ad_client: "<?php echo $client_id; ?>",
    enable_page_level_ads: true
  });
</script>
<?php else: ?>
<script>
// Wait until 'advads.privacy' is available.
( window.advanced_ads_ready || jQuery( document ).ready ).call( null, function() {
	var npa_enabled = <?php echo $npa_enabled ? 1 : 0; ?>;
	if ( npa_enabled
		|| ( advads.privacy && advads.privacy.get_state() !== 'unknown' )
	) {
		var script = document.createElement( 'script' );
		script.async=1;
		script.src='//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js';
		var first = document.getElementsByTagName( 'script' )[0];
		first.parentNode.insertBefore( script, first );

		(adsbygoogle = window.adsbygoogle || []).push({
		google_ad_client: "<?php echo $client_id; ?>",
			enable_page_level_ads: true
		});
	}
} );
</script>
<?php endif ?>
