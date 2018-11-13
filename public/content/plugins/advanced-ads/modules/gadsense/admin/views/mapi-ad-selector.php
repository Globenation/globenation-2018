<?php
$G_Data = Advanced_Ads_AdSense_Data::get_instance();
$adsense_id = $G_Data->get_adsense_id();
$mapi_options = Advanced_Ads_AdSense_MAPI::get_option();
$MAPI = Advanced_Ads_AdSense_MAPI::get_instance();
$use_user_app = Advanced_Ads_AdSense_MAPI::use_user_app();
$quota = $MAPI->get_quota();
$can_connect = $use_user_app || 0 < $quota['count'];
$can_connect = true;
$ad_units = $mapi_options['accounts'][$adsense_id]['ad_units'];

$unsupported_ad_type_link = Advanced_Ads_AdSense_MAPI::UNSUPPORTED_TYPE_LINK;

?>
<div id="mapi-wrap">
	<button type="button" id="mapi-close-selector" class="notice-dismiss"></button>
	<?php if ( !empty( $ad_units ) ) : ?>
	<i id="mapi-update-unit-lists" class="dashicons dashicons-update mapiaction" data-mapiaction="updateList" style="color:#0085ba;cursor:pointer;font-size:20px;" title="<?php 
	    esc_attr_e( 'Update the ad units list', 'advanced-ads' ) ?>"></i>
	<?php endif; ?>
	<div id="mapi-loading-overlay">
		<img alt="..." src="<?php echo ADVADS_BASE_URL . 'admin/assets/img/loader.gif'; ?>" style="margin-top:8em;" />
	</div>
	
	<?php if ( $can_connect ) : ?>
	<?php if ( !empty( $ad_units ) ) : ?>
	<div id="mapi-list-header">
		<span><?php echo esc_attr_x( 'Ad unit', 'AdSense ad', 'advanced-ads' ); ?></span>
		<span><?php esc_html_e( 'Name', 'advanced-ads' ); ?></span>
		<span><?php echo esc_html_x( 'Slot ID', 'AdSense ad', 'advanced-ads' ); ?></span>
		<span><?php echo esc_html_x( 'Type', 'AdSense ad', 'advanced-ads' ); ?></span>
		<span><?php esc_html_e( 'Size', 'advanced-ads' ); ?></span>
	</div>
	<?php endif; ?>
	<div id="mapi-table-wrap">
		<table class="widefat striped">
			<?php if ( empty( $ad_units ) ) : ?>
			<thead>
				<tr>
					<th><?php echo esc_attr_x( 'Ad unit', 'AdSense ad', 'advanced-ads' ); ?></th>
					<th><?php esc_html_e( 'Name', 'advanced-ads' ); ?></th>
					<th><?php echo esc_html_x( 'Slot ID', 'AdSense ad', 'advanced-ads' ); ?></th>
					<th><?php echo esc_html_x( 'Type', 'AdSense ad', 'advanced-ads' ); ?></th>
					<th><?php esc_html_e( 'Size', 'advanced-ads' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<tr>
				<td colspan="5" style="text-align:center;">
					<?php esc_attr_e( 'No ad units found', 'advanced-ads' ) ?>
				    <i id="mapi-no-ad-units-found" class="dashicons dashicons-update mapiaction" data-mapiaction="updateList" style="color:#0085ba;cursor:pointer;font-size:20px;" title="<?php esc_attr_e( 'Update the ad units list', 'advanced-ads' ) ?>"></i>
				</td>
			</tr>
			<?php else : $sorted_adunits = Advanced_Ads_AdSense_MAPI::get_sorted_adunits( $ad_units ); ?>
			<tbody>
				<?php foreach ( $sorted_adunits as $name => $unit ) : $unsupported_class = array_key_exists( $unit['id'], $mapi_options['unsupported_units'] ) ? ' disabled' : ''; ?>
					<tr data-slotid="<?php echo esc_attr( $unit['id'] ); ?>">
						<td>
							<i data-slotid="<?php echo esc_attr( $unit['id'] ); ?>" class="dashicons dashicons-download mapiaction<?php echo $unsupported_class; ?>" data-mapiaction="getCode" title="<?php esc_attr_e( 'Get the code for this ad', 'advanced-ads' ) ?>"></i>
							<i data-slotid="<?php echo esc_attr( $unit['id'] ); ?>" class="dashicons dashicons-update mapiaction" data-mapiaction="updateCode" title="<?php esc_attr_e( 'Update and get the code for this ad from Google', 'advanced-ads' ) ?>"></i>
						</td>
						<td><?php echo $name; ?></td>
						<td class="unitcode"><?php
						if ( array_key_exists( $unit['id'], $mapi_options['unsupported_units'] ) ) {
							echo '<span class="unsupported"><span>' . esc_html( $unit['code'] ) . '</span></span>';
						} else {
							echo '<span><span>' . esc_html( $unit['code'] ) . '</span></span>';
						}
						?></td>
						<td class="unittype"><?php
						if ( array_key_exists( $unit['id'], $mapi_options['unsupported_units'] ) ) {
							echo '<a href="' . esc_url( $unsupported_ad_type_link ) . '" target="_blank" data-type="' . esc_attr( Advanced_Ads_AdSense_MAPI::format_ad_data( $unit['contentAdsSettings']['type'], 'type' ) ) . '">';
							esc_html_e( 'unsupported', 'advanced-ads' );
							echo '</a>';
						} else {
							echo Advanced_Ads_AdSense_MAPI::format_ad_data( $unit['contentAdsSettings']['type'], 'type' );
						}
						?></td>
						<td class="unitsize"><?php
						if ( array_key_exists( $unit['id'], $mapi_options['unsupported_units'] ) ) {
							echo '<a href="' . esc_url( $unsupported_ad_type_link ) . '" target="_blank" data-size="' . Advanced_Ads_AdSense_MAPI::format_ad_data( $unit['contentAdsSettings']['size'], 'size' ) . '">';
							esc_html_e( 'unsupported', 'advanced-ads' );
							echo '</a>';
						} else {
							echo Advanced_Ads_AdSense_MAPI::format_ad_data( $unit['contentAdsSettings']['size'], 'size' ); 
						}
						?></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			</tbody>
		</table>
	</div>
	<?php endif; ?>
	
	<p class="advads-error-message" id="remote-ad-code-error" style="display:none;"><strong><?php esc_attr_e( 'Unrecognized ad code', 'advanced-ads' ); ?></strong></p>
	<p class="advads-error-message" id="remote-ad-code-msg"></p>
	<div style="display:none;" id="remote-ad-unsupported-ad-type"><p><i class="dashicons dashicons-warning"></i><b class="advads-error-message"><?php 
		esc_attr_e( 'This ad type can currently not be imported from AdSense.', 'advanced-ads' ) ?></b>&nbsp;<a href="<?php echo ADVADS_URL . 'adsense-ad-type-not-available/#utm_source=advanced-ads&utm_medium=link&utm_campaign=adsense-type-not-available'; ?>" target="_blank"><?php 
		esc_attr_e( 'Learn more and help us to enable it here.', 'advanced-ads' ) ?></a></p>
		<?php esc_attr_e( 'In the meantime, you can use AdSense with one of these methods:', 'advanced-ads' ) ?>
		<ul>
		<li><?php _e( 'Click on <em>Insert new AdSense code</em> and copy the code from your AdSense account into it.', 'advanced-ads' ) ?></li>
		<li><?php _e( 'Create an ad on the fly. Just select the <em>Normal</em> or <em>Responsive</em> type and the size.', 'advanced-ads' ) ?></li>
		<li><?php _e( 'Choose a <em>Normal</em>, <em>Responsive</em> or <em>Link Unit</em> ad from your AdSense account.', 'advanced-ads' ) ?></li>
		</ul>
	</div>
</div>
<?php if ( 8 < count( $ad_units ) ) : ?>
<style type="text/css">
#mapi-table-wrap {
	height: 22.2em;
	overflow: auto;
}
#mapi-wrap table {
	position: absolute;
}
</style>
<?php endif; ?>