<?php
$errortext = false;
$expires = Advanced_Ads_Admin_Licenses::get_instance()->get_license_expires($options_slug);
$expired = false;
$expired_error = __('Your license expired.', 'advanced-ads');

ob_start();
?><button type="button" class="button-secondary advads-license-activate"
	data-addon="<?php echo $index; ?>"
	data-pluginname="<?php echo $plugin_name; ?>"
	data-optionslug="<?php echo $options_slug; ?>"
	name="advads_license_activate"><?php _e('Update expiry date', 'advanced-ads'); ?></button>
	<?php
	$update_button = ob_get_clean();

	$expired_error .= $expired_renew_link = ' ' . sprintf(__('Click on %2$s if you renewed it or have a subscription or <a href="%1$s" target="_blank">renew your license</a>.', 'advanced-ads'), ADVADS_URL . 'checkout/?edd_license_key=' . esc_attr($license_key) . '#utm_source=advanced-ads&utm_medium=link&utm_campaign=settings-licenses', $update_button);
	if ('lifetime' !== $expires) {
	    $expires_time = strtotime($expires);
	    $days_left = ( $expires_time - time() ) / DAY_IN_SECONDS;
	}
	if ('lifetime' === $expires) {
	    // do nothing
	} elseif ($days_left <= 0) {
	    $plugin_url = isset($plugin_url) ? $plugin_url : ADVADS_URL;
	    $errortext = $expired_error;
	    $expired = true;
	} elseif (0 < $days_left && 31 > $days_left) {
	    $errortext = sprintf(__('(%d days left)', 'advanced-ads'), $days_left);
	}
	$show_active = ( $license_status !== false && $license_status == 'valid' && !$expired ) ? true : false;
	?>
<input type="text" class="regular-text advads-license-key" placeholder="<?php _e('License key', 'advanced-ads'); ?>"
       name="<?php echo ADVADS_SLUG . '-licenses'; ?>[<?php echo $index; ?>]"
       value="<?php echo esc_attr($license_key); ?>"
       <?php
       if ($license_status !== false && $license_status == 'valid' && !$expired) :
	   ?>
           readonly="readonly"<?php endif; ?>/>

<button type="button" class="button-secondary advads-license-deactivate"
<?php
if ($license_status !== 'valid') {
    echo ' style="display: none;" ';
}
?>
	data-addon="<?php echo $index; ?>"
	data-pluginname="<?php echo $plugin_name; ?>"
	data-optionslug="<?php echo $options_slug; ?>"
	name="advads_license_activate"><?php _e('Deactivate License', 'advanced-ads'); ?></button>

<button type="button" class="button-primary advads-license-activate"
	data-addon="<?php echo $index; ?>"
	data-pluginname="<?php echo $plugin_name; ?>"
	data-optionslug="<?php echo $options_slug; ?>"
	name="advads_license_activate"><?php echo ( $license_status === 'valid' && !$expired ) ? __('Update License', 'advanced-ads') : __('Activate License', 'advanced-ads'); ?></button>
	<?php
	if ('' === trim($license_key)) {
	    $errortext = __('Please enter a valid license key', 'advanced-ads');
	} elseif (!$expired && !$errortext) {
	    $errortext = ( $license_status == 'invalid' ) ? __('License key invalid', 'advanced-ads') : '';
	}
	?>
&nbsp;
<span class="advads-license-activate-active" <?php
if (!$show_active) {
    echo 'style="display: none;"';
}
?>><?php _e('active', 'advanced-ads'); ?></span>
<span class="advads-license-activate-error" <?php
if (!$errortext) {
    echo 'style="display: none;"';
} ?>><?php echo $errortext; ?></span>
<span class="advads-license-expired-error advads-error-message" style="display: none;"><?php echo $expired_error; ?></span>
