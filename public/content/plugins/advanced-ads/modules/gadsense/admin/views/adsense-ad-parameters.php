<?php
if ( ! defined( 'WPINC' ) ) {
	die();
}
$is_responsive = ('responsive' == $unit_type) ? true : false;
$is_link_responsive_unit = ('link-responsive' == $unit_type) ? true : false;
$is_matched_content = ('matched-content' == $unit_type) ? true : false;
$use_manual_css = ('manual' == $unit_resize) ? true : false;
if ( $is_responsive || $is_link_responsive_unit || $is_matched_content ) {
    echo '<style type="text/css"> #advanced-ads-ad-parameters-size {display: none;}	</style>';
}

$MAPI = Advanced_Ads_AdSense_MAPI::get_instance();
$use_user_app = Advanced_Ads_AdSense_MAPI::use_user_app();

$use_paste_code = true;
$use_paste_code = apply_filters( 'advanced-ads-gadsense-use-pastecode', $use_paste_code );

$db = Advanced_Ads_AdSense_Data::get_instance();
$adsense_id = $db->get_adsense_id();
$sizing_array = $db->get_responsive_sizing();

$gadsense_options = $db->get_options();
$mapi_options = Advanced_Ads_AdSense_MAPI::get_option();
$mapi_nonce = wp_create_nonce( 'advads-mapi' );
$has_token = Advanced_Ads_AdSense_MAPI::has_token( $adsense_id );
$quota = $MAPI->get_quota();

$mapi_ad_codes = $mapi_options['ad_codes'];
$mapi_ad_codes['length'] = count( $mapi_ad_codes );

?>
<?php if ( $has_token ) : ?>
<script type="text/javascript">
	var AdsenseMAPI = {
		nonce: '<?php echo $mapi_nonce ?>',
		codes: <?php echo json_encode( $mapi_ad_codes ) ?>,
		quota: <?php echo json_encode( $quota ) ?>,
		pubId: '<?php echo $pub_id ?>',
		adStatus: '<?php echo $ad->status ?>',
		unsupportedUnits: <?php echo wp_json_encode( $mapi_options['unsupported_units'] ); ?>,
		unsupportedLink: '<?php echo Advanced_Ads_AdSense_MAPI::UNSUPPORTED_TYPE_LINK; ?>',
		unsupportedText: '<?php /**
		     * translators: this is a label for an ad that we can currently not import from the AdSense account
		 */
		esc_html_e( 'unsupported', 'advanced-ads' ); ?>'
	}; 
</script>
<?php endif; ?>
<input type="hidden" id="advads-ad-content-adsense" name="advanced_ad[content]" value="<?php echo esc_attr( $json_content ); ?>" />
<input type="hidden" name="unit_id" id="unit_id" value="<?php echo esc_attr( $unit_id ); ?>" />
<?php if( empty( $pub_id ) ) :
    ?><p><a class="button button-primary" href="<?php echo admin_url( 'admin.php?page=advanced-ads-settings#top#adsense' ); ?>"><?php _e( 'Connect to AdSense', 'advanced-ads' ); 
    ?></a></p><?php
endif;
if ( $use_paste_code ) : ?>
<div class="advads-adsense-code" style="display: none;">
	<p class="description"><?php _e( 'Copy the ad code from your AdSense account, paste it into the area below and click on <em>Get details</em>.', 'advanced-ads' ); ?></p>
	<textarea rows="10" cols="40" class="advads-adsense-content"></textarea>
	<button class="button button-primary advads-adsense-submit-code"><?php _e( 'Get details', 'advanced-ads' ); ?></button>&nbsp;&nbsp;
	<?php if ( !$has_token ) : ?>
	<a style="vertical-align:sub;font-weight:600;font-style:italic;" href="<?php echo admin_url( 'admin.php?page=advanced-ads-settings#top#adsense' ) ?>"><?php _e( 'connect to your AdSense account', 'advanced-ads' ) ?></a>
	<?php endif; ?>
	<div id="pastecode-msg"></div>
</div>
<?php if ( $has_token && Advanced_Ads_Checks::php_version_minimum() ) require_once GADSENSE_BASE_PATH . 'admin/views/mapi-ad-selector.php'; ?>

<p>
	<span class="advads-adsense-show-code">
		<a href="#"><?php _e( 'Insert new AdSense code', 'advanced-ads' ); ?></a>
	</span>
	<?php if ( $has_token && Advanced_Ads_Checks::php_version_minimum() ) : ?>
	<span id="mapi-open-selector">
		<?php _e( 'or', 'advanced-ads' ); ?><a href="#" class="prevent-default"><?php _e( 'Get ad code from your linked account', 'advanced-ads' ); ?></a>
	</span>
	<?php endif; ?>
</p>
	<?php if ( $has_token && ! Advanced_Ads_Checks::php_version_minimum() ) : ?>
	<p class="advads-error-message"><?php _e( 'Your PHP version is too low to connect an AdSense account', 'advanced-ads' ); ?></p>
	<?php endif; ?>

<?php endif; ?>
<p id="adsense-ad-param-error"></p>
<?php ob_start(); ?>
<label class="label"><?php _e( 'Ad Slot ID', 'advanced-ads' ); ?></label>
<div>
    <input type="text" name="unit-code" id="unit-code" value="<?php echo $unit_code; ?>" />
    <input type="hidden" name="advanced_ad[output][adsense-pub-id]" id="advads-adsense-pub-id" value="" />
    <?php if( $pub_id ) : ?>
	<?php printf(__( 'Publisher ID: %s', 'advanced-ads' ), $pub_id ); ?>
    <?php endif; ?>
	<p id="advads-pubid-in-slot" class="advads-error-message description"
		<?php echo ! ( 0 === strpos( $pub_id, 'pub-' ) && false !== strpos( $unit_code, substr( $pub_id, 4 ) ) ) ? 'style="display:none"' : ''; ?>
		><?php _e( 'The ad slot ID is either a number or empty and not the same as the publisher ID.', 'advanced-ads' ) ?></p>
</div>
<hr/>
<?php
$unit_code_markup = ob_get_clean();
echo apply_filters( 'advanced-ads-gadsense-unit-code-markup', $unit_code_markup, $unit_code );
if( $pub_id_errors ) : ?>
	    <p>
	<span class="advads-error-message">
	    <?php echo $pub_id_errors; ?>
	</span>
	<?php printf(__( 'Please <a href="%s" target="_blank">change it here</a>.', 'advanced-ads' ), admin_url( 'admin.php?page=advanced-ads-settings#top#adsense' )); ?>
    </p>
<?php endif; ?>
    <label class="label" id="unit-type-block"><?php _e( 'Type', 'advanced-ads' ); ?></label>
    <div>
	<select name="unit-type" id="unit-type">
	    <option value="normal" <?php selected( $unit_type, 'normal' ); ?>><?php _e( 'Normal', 'advanced-ads' ); ?></option>
	    <option value="responsive" <?php selected( $unit_type, 'responsive' ); ?>><?php _e( 'Responsive', 'advanced-ads' ); ?></option>
	    <option value="matched-content" <?php selected( $unit_type, 'matched-content' ); ?>><?php _e( 'Responsive (Matched Content)', 'advanced-ads' ); ?></option>
	    <option value="link" <?php selected( $unit_type, 'link' ); ?>><?php _e( 'Link ads', 'advanced-ads' ); ?></option>
	    <option value="link-responsive" <?php selected( $unit_type, 'link-responsive' ); ?>><?php _e( 'Link ads (Responsive)', 'advanced-ads' ); ?></option>
	    <option value="in-article" <?php selected( $unit_type, 'in-article' ); ?>><?php _e( 'InArticle', 'advanced-ads' ); ?></option>
	    <option value="in-feed" <?php selected( $unit_type, 'in-feed' ); ?>><?php _e( 'InFeed', 'advanced-ads' ); ?></option>
	</select>
	<a href="<?php echo ADVADS_URL . 'adsense-ads/#utm_source=advanced-ads&utm_medium=link&utm_campaign=adsense-ad-types'; ?>" target="_blank"><?php _e( 'manual', 'advanced-ads' ); ?></a>
    </div>
    <hr/>
    <label class="label" <?php if ( ! $is_responsive || 2 > count( $sizing_array ) ) { echo 'style="display: none;"'; } ?> id="resize-label"><?php _e( 'Resizing', 'advanced-ads' ); ?></label>
    <div <?php if ( ! $is_responsive || 2 > count( $sizing_array ) ) { echo 'style="display: none;"'; } ?>>
	<select name="ad-resize-type" id="ad-resize-type">
	<?php foreach ( $sizing_array as $key => $desc ) : ?>
	    <option value="<?php echo $key; ?>" <?php selected( $key, $unit_resize ); ?>><?php echo $desc; ?></option>
	<?php endforeach; ?>
	</select>
    </div>
    <label class="label advads-adsense-layout" <?php if ( 'in-feed' !== $unit_type ) { echo 'style="display: none;"'; } ?> id="advads-adsense-layout"><?php _e( 'Layout', 'advanced-ads' ); ?></label>
    <div <?php if ( 'in-feed' !== $unit_type ) { echo 'style="display: none;"'; } ?>>
	<input name="ad-layout" id="ad-layout" value="<?php echo isset( $layout ) ? $layout : ''; ?>"/>
    </div>
    <label class="label advads-adsense-layout-key" <?php if ( 'in-feed' !== $unit_type ) { echo 'style="display: none;"'; } ?> id="advads-adsense-layout-key"><?php _e( 'Layout-Key', 'advanced-ads' ); ?></label>
    <div <?php if ( 'in-feed' !== $unit_type ) { echo 'style="display: none;"'; } ?>>
	<input name="ad-layout-key" id="ad-layout-key" value="<?php echo isset( $layout_key ) ? $layout_key : ''; ?>"/>
    </div>
    <hr/>
	<label class="label clearfix-before" <?php if ( ! $is_responsive ) { echo 'style="display: none;"'; } ?>><?php _e( 'Clearfix', 'advanced-ads' ); ?></label>
	<div class="clearfix-before" <?php if ( ! $is_responsive ) { echo 'style="display: none;"'; } ?>>
	<label><input type="checkbox" name="advanced_ad[output][clearfix_before]" value="1" <?php checked( ! empty( $options['output']['clearfix_before'] ), true ); ?> /><?php
		_e( 'Enable this box if responsive ads cover something on your site', 'advanced-ads' ); ?></label>
	</div>
	<hr class="clearfix-before" <?php if ( ! $is_responsive ) { echo 'style="display: none;"'; } ?> />
    <?php do_action( 'advanced-ads-gadsense-extra-ad-param', $extra_params, $content, $ad );
