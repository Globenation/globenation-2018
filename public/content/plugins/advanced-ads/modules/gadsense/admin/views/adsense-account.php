<?php
$MAPI = Advanced_Ads_AdSense_MAPI::get_instance();
$options = $this->data->get_options();
$adsense_id = $this->data->get_adsense_id();
$nonce = wp_create_nonce( 'advads-mapi' );
$mapi_options = Advanced_Ads_AdSense_MAPI::get_option();

$CID = Advanced_Ads_AdSense_MAPI::CID;

$use_user_app = Advanced_Ads_AdSense_MAPI::use_user_app();
if ( $use_user_app ) {
	$CID = ADVANCED_ADS_MAPI_CID;
}

$auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?scope=' .
			urlencode( 'https://www.googleapis.com/auth/adsense.readonly' ) .
			'&client_id=' . $CID . 
			'&redirect_uri=' . urlencode( 'urn:ietf:wg:oauth:2.0:oob' ) . 
			'&access_type=offline&include_granted_scopes=true&prompt=select_account&response_type=code';

$can_connect = true;

if ( $use_user_app && !( ( defined( 'ADVANCED_ADS_MAPI_CID' ) && '' != ADVANCED_ADS_MAPI_CID ) && ( defined( 'ADVANCED_ADS_MAPI_CIS' ) && '' != ADVANCED_ADS_MAPI_CIS ) ) ) {
	$can_connect = false;
}

$has_token = Advanced_Ads_AdSense_MAPI::has_token( $adsense_id );

	?>
<div id="full-adsense-settings-div" <?php if ( empty( $adsense_id ) ) echo 'style="display:none"' ?>>
	<input type="text" <?php if ( $has_token ) echo 'readonly' ?> name="<?php echo GADSENSE_OPT_NAME; ?>[adsense-id]" style="margin-right:.8em" id="adsense-id" size="32" value="<?php echo $adsense_id; ?>" />
	<?php if ( !empty( $adsense_id ) && !$has_token ) : ?>
	<a id="connect-adsense" class="button-primary  <?php echo ! Advanced_Ads_Checks::php_version_minimum() ? 'disabled ' : ''; ?>preventDefault" <?php if ( ! $can_connect || ! Advanced_Ads_Checks::php_version_minimum() ) echo 'disabled'; ?>><?php _e( 'Connect to AdSense', 'advanced-ads' ) ?></a>
	<?php endif; ?>
	<?php if ( $has_token ) : ?>
	<a id="revoke-token" class="button-secondary preventDefault"><?php _e( 'Revoke API acccess', 'advanced-ads' ) ?></a>
	<div id="gadsense-freeze-all" style="position:fixed;top:0;bottom:0;right:0;left:0;background-color:rgba(255,255,255,.5);text-align:center;display:none;">
		<img alt="..." src="<?php echo ADVADS_BASE_URL . 'admin/assets/img/loader.gif'; ?>" style="margin-top:40vh" />
	</div>
	<?php endif; ?>
	<p class="description"><?php _e( 'Your AdSense Publisher ID <em>(pub-xxxxxxxxxxxxxx)</em>', 'advanced-ads' ) ?></p>
</div>
<?php if ( empty( $adsense_id ) ) : ?>
<div id="auto-adsense-settings-div" <?php if ( !empty( $adsense_id ) ) echo 'style="display:none;"' ?>>
	<div class="widget-col">
		<h3><?php _e( 'Yes, I have an AdSense account', 'advanced-ads' ) ?></h3>
		<a id="connect-adsense" class="button-primary <?php echo ! Advanced_Ads_Checks::php_version_minimum() ? 'disabled ' : ''; ?>preventDefault" <?php echo ! Advanced_Ads_Checks::php_version_minimum() ? 'disabled' : ''; ?>><?php _e( 'Connect to AdSense', 'advanced-ads' ) ?></a>
		<a id="adsense-manual-config" class="button-secondary preventDefault"><?php _e( 'Configure everything manually', 'advanced-ads' ) ?></a>
	</div>
	<div class="widget-col">
		<h3><?php _e( "No, I still don't have an AdSense account", 'advanced-ads' ) ?></h3>
		<a class="button button-secondary" target="_blank" href="<?php echo self::ADSENSE_NEW_ACCOUNT_LINK; ?>"><?php _e( 'Get a free AdSense account', 'advanced-ads' ); ?></a>
	</div>
</div>
<style type="text/css">
	#adsense table h3 {
		margin-top: 0;
		margin-bottom: .2em;
	}
	#adsense table button {
		margin-bottom: .8em;
	}
	#adsense .form-table tr {
		display: none;
	}
	#adsense .form-table tr:first-of-type {
		display: table-row;
	}
	#auto-adsense-settings-div .widget-col {
		float: left;
		margin: 0px 5px 5px 0px;
	}
	#auto-adsense-settings-div:after {
		display: block;
		content: "";
		clear: left; 
	}
	#auto-adsense-settings-div .widget-col:first-child {
		margin-right: 20px;
		border-right: 1px solid #cccccc;
		padding: 0px 20px 0px 0px;
		position: relative;
	}
	#auto-adsense-settings-div .widget-col:first-child:after {
		position: absolute;
		content: "or";
		display: block;
		top: 20px;
		right: -10px;
		background: #ffffff;
		color: #cccccc;
		font-size: 20px; 
	}
	@media screen and (max-width: 1199px) {  
		#auto-adsense-settings-div .widget-col { float: none; margin-right: 0;  }
		#auto-adsense-settings-div .widget-col:first-child { margin: 0px 0px 20px 0px; padding: 0px 0px 20px 0px; border-bottom: 1px solid #cccccc; border-right: 0; }
		#auto-adsense-settings-div .widget-col:first-child:after { top: auto; right: auto; bottom: -10px; left: 20px; display: inline-block; padding: 0px 5px 0px 5px; }
	} 	
</style>
<?php else : ?>
<p><?php printf(__( 'Problems with AdSense? Check out the <a href="%s" target="_blank">manual</a> and get a free setup check.', 'advanced-ads' ), ADVADS_URL . 'adsense-ads/#utm_source=advanced-ads&utm_medium=link&utm_campaign=adsense-manual-check' ); ?></p>
<?php endif; ?>
<?php if ( ! Advanced_Ads_Checks::php_version_minimum() ) : ?>
<p class="advads-error-message"><?php _e( 'Can not connect AdSense account. PHP version is too low.', 'advanced-ads' ) ?></p>
<?php endif; ?>
<script type="text/javascript">
	var AdsenseMAPI = {
		nonce: '<?php echo $nonce ?>',
		oAuth2: '<?php echo $auth_url ?>',
	}; 
</script>
<div id="gadsense-modal">
	<div id="gadsense-modal-outer">
		<div id="gadsense-modal-inner">
			<div id="gadsense-modal-content">
				<div id="gadsense-modal-content-inner">
					<i class="dashicons dashicons-dismiss"></i>
					<label style="font-size:1.1em;font-weight:600;margin-bottom:.3em;display:block;"><?php _e( 'Please enter the confirmation code.', 'advanced-ads' ) ?></label>
					<input type="text" class="widefat" id="mapi-code" value="" />
					<p><label><input type="checkbox" value="1" id="mapi-autoads"<?php echo ( $options['page-level-enabled'] ) ? ' checked="checked"' : ''; ?> />&nbsp;<?php _e( 'Insert the AdSense header code used for verification and the Auto Ads feature.', 'advanced-ads' ) ?></label></p>
					<p class="submit">
						<button id="mapi-confirm-code" class="button-primary preventDefault"><?php _e( 'Submit code', 'advanced-ads' ) ?></button>
					</p>
					<div id="gadsense-overlay">
						<img alt="..." src="<?php echo ADVADS_BASE_URL . 'admin/assets/img/loader.gif'; ?>" style="margin-top:3em" />
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<style type="text/css">
#gadsense-overlay {
	display:none;
	background-color:rgba(255,255,255,.5);
	position:absolute;
	width: 100%;
	height: 100%;
	top: 0;
	left: 0;
	text-align:center;
}
#gadsense-modal {
	display: none;
	background-color: rgba(0,0,0,.5);
	position:fixed;
	top:0;
	left:0;
	right:0;
	bottom:0;
}
#gadsense-modal-outer {
	position: relative;
	width: 60%;
	height: 100%;
	margin-left: 20%;
}
#gadsense-modal-inner {
	display: table;
	width: 100%;
	height: 100%;
}
#gadsense-modal-content {
	display: table-cell;
	vertical-align: middle;
}
#gadsense-modal-content-inner {
    padding: 1em;
    background-color: #f0f0f0;
    position: relative;
    border: 3px solid #808b94;
}
#gadsense-modal-content-inner .dashicons-dismiss {
	background-color: #fff;
	border-radius: 100%;
	cursor: pointer;
	top: -.5em;
	right: -.5em;
	position: absolute;
	z-index: 2;
}
</style>