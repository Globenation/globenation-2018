<input name="<?php echo Advanced_Ads_Privacy::OPTION_KEY; ?>[enabled]" type="checkbox" value="1" <?php checked( $module_enabled, 1 ); ?>/>&nbsp;
<a href="<?php echo ADVADS_URL . 'manual/ad-cookie-consent/#utm_source=advanced-ads&utm_medium=link&utm_campaign=privacy-tab'; ?>" target="_blank"><?php _e( 'Manual', 'advanced-ads' ); ?></a>
<?php if( Advanced_Ads_Checks::cache() && ! defined('AAP_VERSION') ) :
    ?><p><span class="advads-error-message"><?php _e( 'It seems that a caching plugin is activated.', 'advanced-ads' ); ?></span>&nbsp;<?php
	_e( 'Your users’ consent might get cached and show ads to users who didn’t give their consent yet. ', 'advanced-ads' );
	?> <?php printf( __( 'Cache-busting in <a href="%s" target="_blank">Advanced Ads Pro</a> solves that.', 'advanced-ads' ), ADVADS_URL . 'add-ons/advanced-ads-pro/#utm_source=advanced-ads&utm_medium=link&utm_campaign=privacy-cache' ); ?></p><?php
endif;