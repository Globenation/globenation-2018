<label><input type="checkbox" name="advanced_ads[disable_ads]" value="1" <?php
if ( isset( $values['disable_ads'] ) ) {
	checked( $values['disable_ads'], true ); }
?>/><?php _e( 'Disable ads on this page', 'advanced-ads' ); ?></label>
