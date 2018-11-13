<?php
/**
 * array with admin notices
 */
$advanced_ads_admin_notices = apply_filters( 'advanced-ads-notices', array(
    // email tutorial
    'nl_intro' => array(
	'type' => 'info',
	// 'text' => sprintf(__( 'Advanced Ads successfully installed. <a href="%s" class="button button-primary">Create your first ad</a>', 'advanced-ads' ), admin_url( 'post-new.php?post_type=advanced_ads' )),
	'text' => Advanced_Ads_Admin_Notices::get_welcome_panel(),
	'global' => true
    ),
    // email tutorial
    'nl_first_steps' => array(
	'type' => 'subscribe',
	'text' => __( 'Thank you for activating <strong>Advanced Ads</strong>. Would you like to receive the first steps via email?', 'advanced-ads' ),
	'confirm_text' => __( 'Yes, send it', 'advanced-ads' ),
	'global' => true
    ),
    // free add-ons
    'nl_free_addons' => array(
	'type' => 'subscribe',
	'text' => __( 'Thank you for using <strong>Advanced Ads</strong>. Stay informed and receive <strong>2 free add-ons</strong> for joining the newsletter.', 'advanced-ads' ),
	'confirm_text' => __( 'Add me now', 'advanced-ads' ),
	'global' => true
    ),
    // adsense newsletter group
    'nl_adsense' => array(
	'type' => 'subscribe',
	'text' => __( 'Learn more about how and <strong>how much you can earn with AdSense</strong> and Advanced Ads from my dedicated newsletter.', 'advanced-ads' ),
	'confirm_text' => __( 'Subscribe me now', 'advanced-ads' ),
	'global' => true
    ),
    // if users updated from a previous version to 1.7
    '1.7' => array(
	'type' => 'update',
	'text' => 'Advanced Ads 1.7 made changes to the Display Conditions interface. Please check your settings and the <a href="https://wpadvancedads.com/manual/display-conditions/" target="_blank">manual</a>, if you are using them.',
    ),
    // missing license codes
    'license_invalid' => array(
	'type' => 'plugin_error',
	'text' => __( 'One or more license keys for <strong>Advanced Ads add-ons are invalid or missing</strong>.', 'advanced-ads' ) . ' ' . sprintf( __( 'Please add valid license keys <a href="%s">here</a>.', 'advanced-ads' ), get_admin_url( 1, 'admin.php?page=advanced-ads-settings#top#licenses' ) ),
    ),
    // please review
    'review' => array(
	'type' => 'info',
	'text' => '<img src="' . ADVADS_BASE_URL . 'admin/assets/img/thomas.png" alt="Thomas" width="80" height="115" class="advads-review-image"/>'
		. '<p>' . sprintf(__( 'You’ve successfully <strong>created %s ads using Advanced Ads</strong>.', 'advanced-ads' ), Advanced_Ads::get_number_of_ads() ) . '</p>'
		. '<p>' . __( 'Do you find Advanced Ads useful and would like to keep us motivated? Please help us with a review.', 'advanced-ads' ) . '</p>'
		. '<p><em>Thomas & Team</em></p>'
		. '<p>' 
			. '<span class="dashicons dashicons-external"></span>&nbsp;<strong><a href="https://wordpress.org/support/plugin/advanced-ads/reviews/?rate=5#new-post" target=_"blank">' . __( 'Sure, I’ll rate the plugin', 'advanced-ads' ) . '</a></strong>'
			. ' &nbsp;&nbsp;<span class="dashicons dashicons-smiley"></span>&nbsp;<a href="javascript:void(0)" target=_"blank" class="advads-notice-dismiss">' . __( 'I already did', 'advanced-ads' ) . '</a>'
			. ' &nbsp;&nbsp;<span class="dashicons dashicons-sos"></span>&nbsp;<a href="'. ADVADS_URL . 'support/#utm_source=advanced-ads&utm_medium=link&utm_campaign=notice-review" target=_"blank">' . __( 'I am not happy, please help', 'advanced-ads' ) . '</a>'
			. '<br/><br/><span class="dashicons dashicons-clock"></span>&nbsp;<a href="javascript:void(0)" target=_"blank" class="advads-notice-hide">' . __( 'Ask me later', 'advanced-ads' ) . '</a>'
		. '</p>',
	'global' => false
    ),
    // adblocker assets expired
    'assets_expired' => array(
	'type' => 'update',
	'text' => sprintf( __('Some assets were changed. Please <strong>rebuild the asset folder</strong> in the <a href="%s">Advanced Ads settings</a> to update the ad blocker disguise.', 'advanced-ads' ), admin_url( 'admin.php?page=advanced-ads-settings' ) ),
	'global' => true
    ),

));

