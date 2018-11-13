<?php $types = Advanced_Ads::get_instance()->ad_types; ?>
<?php if ( empty( $types ) ) : ?>
	<p><?php _e( 'No ad types defined', 'advanced-ads' ); ?></p>
<?php else : ?>
	<ul id="advanced-ad-type">
		<?php
			// choose first type if none set
			$type = ( isset( $ad->type ) ) ? $ad->type : current( $types )->ID;
		foreach ( $types as $_type ) :
			?>
			<li class="advanced-ads-type-list-<?php echo $_type->ID; ?>">
				<input type="radio" name="advanced_ad[type]" id="advanced-ad-type-<?php 
					echo $_type->ID; ?>" value="<?php echo $_type->ID; ?>" <?php checked( $type, $_type->ID ); ?>/>
				<label for="advanced-ad-type-<?php echo $_type->ID; ?>"><?php echo ( empty( $_type->title ) ) ? $_type->ID : $_type->title; ?></label>
				<?php
				if ( ! empty( $_type->description ) ) :
					?>
					<span class="description"><?php echo $_type->description; ?></span><?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>
<script>
jQuery( document ).on('change', '#advanced-ad-type input', function () {
	advads_update_ad_type_headline();
});

// dynamically move ad type to the meta box title
advads_main_metabox_title = jQuery('#ad-main-box h2').text();
function advads_update_ad_type_headline(){
	var advads_selected_type = jQuery('#advanced-ad-type input:checked + label').text();
	jQuery('#ad-main-box h2').html( advads_main_metabox_title + ': ' + advads_selected_type );
}
advads_update_ad_type_headline();
</script>
