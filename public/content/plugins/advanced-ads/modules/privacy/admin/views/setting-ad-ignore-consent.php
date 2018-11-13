<div class="advads-option-list">
    <span class="label"><?php _e( 'privacy', 'advanced-ads' ); ?></span>
    <div id="advanced-ads-ad-parameters-privacy">
	<label>
	    <input name="advanced_ad[privacy][ignore-consent]" type="checkbox" value="1" <?php checked( $ignore_consent, true ); ?>/>
	    <?php printf( __( 'Ignore <a href="%s">general Privacy settings</a> and display the ad even without consent.', 'advanced-ads' ), esc_url( admin_url( 'admin.php?page=advanced-ads-settings#top#privacy' ) ) ); ?>
	</label>
    </div>
</div>
<hr/>
