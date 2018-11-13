<?php
$ad_list_filters = Advanced_Ads_Ad_List_Filters::get_instance();
$all_filters     = $ad_list_filters->get_all_filters();

$ad_type  = isset( $_REQUEST['adtype'] ) ? $_REQUEST['adtype'] : '';
$ad_size  = isset( $_REQUEST['adsize'] ) ? $_REQUEST['adsize'] : '';
$ad_date  = isset( $_REQUEST['addate'] ) ? $_REQUEST['addate'] : '';
$ad_group = isset( $_REQUEST['adgroup'] ) ? $_REQUEST['adgroup'] : '';

// hide the filter button. Can not filter correctly with "trashed" posts.
if ( isset( $_REQUEST['post_status'] ) && 'trash' === $_REQUEST['post_status'] ) {
	echo '<style type="text/css">#post-query-submit{display:none;}</style>';
}

?>
<?php if ( ! empty( $all_filters['all_types'] ) ) : ?>
<select id="advads-filter-type" name="adtype">
	<option value="">- <?php _e( 'all ad types', 'advanced-ads' ); ?> -</option>
	<?php foreach ( $all_filters['all_types'] as $key => $value ) : ?>
	<option <?php selected( $ad_type, $key ); ?> value="<?php echo esc_attr( $key ); ?>"><?php echo $value; ?></option>
	<?php endforeach; ?>
</select>
<?php endif; ?>
<?php if ( ! empty( $all_filters['all_sizes'] ) ) : ?>
<select id="advads-filter-size" name="adsize">
	<option value="">- <?php _e( 'all ad sizes', 'advanced-ads' ); ?> -</option>
	<?php foreach ( $all_filters['all_sizes'] as $key => $value ) : ?>
	<option <?php selected( $ad_size, $key ); ?> value="<?php echo esc_attr( $key ); ?>"><?php echo $value; ?></option>
	<?php endforeach; ?>
</select>
<?php endif; ?>
<?php if ( ! empty( $all_filters['all_dates'] ) ) : ?>
<select id="advads-filter-date" name="addate">
	<option value="">- <?php _e( 'all ad dates', 'advanced-ads' ); ?> -</option>
	<?php foreach ( $all_filters['all_dates'] as $key => $value ) : ?>
	<option <?php selected( $ad_date, $key ); ?> value="<?php echo esc_attr( $key ); ?>"><?php echo $value; ?></option>
	<?php endforeach; ?>
</select>
<?php endif; ?>
<?php if ( ! empty( $all_filters['all_groups'] ) ) : ?>
<select id="advads-filter-group" name="adgroup">
	<option value="">- <?php _e( 'all ad groups', 'advanced-ads' ); ?> -</option>
	<?php foreach ( $all_filters['all_groups'] as $key => $value ) : ?>
	<option <?php selected( $ad_group, $key ); ?> value="<?php echo esc_attr( $key ); ?>"><?php echo $value; ?></option>
	<?php endforeach; ?>
</select>
<?php endif; ?>
