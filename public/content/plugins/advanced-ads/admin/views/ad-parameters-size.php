<span class="label"><?php _e( 'size', 'advanced-ads' ); ?></span>
<div id="advanced-ads-ad-parameters-size">
	<label><?php _e( 'width', 'advanced-ads' ); ?><input type="number" value="<?php echo isset( $ad->width ) ? $ad->width : 0; ?>" name="advanced_ad[width]">px</label>
	<label><?php _e( 'height', 'advanced-ads' ); ?><input type="number" value="<?php echo isset( $ad->height ) ? $ad->height : 0; ?>" name="advanced_ad[height]">px</label>
	<?php
	$show_reserve_space   = in_array( $type->ID, array( 'plain', 'content', 'group', 'adsense' ) );
	$enable_reserve_space = $show_reserve_space && ! empty( $ad->output['add_wrapper_sizes'] );
	?>
	<label <?php
	if ( ! $show_reserve_space ) {
		echo 'style="display:none;"'; }
	?>><input type="checkbox" id="advads-wrapper-add-sizes" name="advanced_ad[output][add_wrapper_sizes]" value="true" <?php checked( $enable_reserve_space ); ?>><?php _e( 'reserve this space', 'advanced-ads' ); ?></label>
	<?php
	if ( 'image' === $type->ID ) :
		Advanced_Ads_Ad_Type_Image::show_original_image_size( $ad );
	endif;
	?>
</div>
<hr/>
