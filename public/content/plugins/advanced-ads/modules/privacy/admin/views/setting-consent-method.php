<?php
//dynamically show activated cookie consent plugins here
?>
<ul>
<?php foreach ( $methods as $method => $description ): ?>
	<li><label><input type="radio" name="<?php echo Advanced_Ads_Privacy::OPTION_KEY; ?>[consent-method]" value="<?php echo $method; ?>" <?php checked( $method , $current_method ); ?> /><?php echo $description ?></label>
		<?php if ( $method === 'custom' ): ?>
		<input type="text" name="<?php echo Advanced_Ads_Privacy::OPTION_KEY; ?>[custom-cookie-name]" value="<?php esc_attr_e( $custom_cookie_name ); 
		?>" placeholder="<?php _e( 'Name', 'advanced-ads' ); ?>"/>
		<label><?php _e( 'contains', 'advanced-ads' ) ?> <input type="text" name="<?php
		echo Advanced_Ads_Privacy::OPTION_KEY; ?>[custom-cookie-value]" value="<?php esc_attr_e( $custom_cookie_value ); 
		?>" placeholder="<?php _e( 'Value', 'advanced-ads' ); ?>"/> </label>
		<?php endif; ?>
	</li>
<?php endforeach; ?>
</ul>

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label><input type="checkbox" name="<?php echo Advanced_Ads_Privacy::OPTION_KEY; ?>[show-non-personalized-adsense]" value="1" <?php checked( $show_non_personalized_adsense, 1 ); ?> /><?php _e( 'Show non-personalized AdSense ads until consent is given.', 'advanced-ads' ); ?></label>
