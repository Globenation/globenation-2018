<?php $types = Advanced_Ads::get_instance()->ad_types; ?>
<?php
/**
 * when changing ad type ad parameter content is loaded via ajax
 *
 * @filesource admin/assets/js/admin.js
 * @filesource includes/class-ajax-callbacks.php ::load_ad_parameters_metabox
 * @filesource classes/ad-type-content.php :: renter_parameters()
 */
do_action( 'advanced-ads-ad-params-before', $ad, $types );
?>
<div id="advanced-ads-tinymce-wrapper" style="display:none;">
	<?php
		$args = array(
			// used here instead of textarea_rows, because of display:none
			'editor_height'    => 300,
			'drag_drop_upload' => true,
		);
		wp_editor( '', 'advanced-ads-tinymce', $args );
		?>
</div>
<div id="advanced-ads-ad-parameters" class="advads-option-list">
	<?php
	$type = ( isset( $types[ $ad->type ] ) ) ? $types[ $ad->type ] : current( $types );
	$type->render_parameters( $ad );

	if ( 'dummy' !== $ad->type ) :
		include ADVADS_BASE_PATH . 'admin/views/ad-parameters-size.php';
	endif;
	?>
	</div>
<?php
do_action( 'advanced-ads-ad-params-after', $ad, $types );
