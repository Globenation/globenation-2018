<?php if ( $is_main_site ): ?>
<input id="advanced-ads-use-adblocker" type="checkbox" value="1" name="<?php echo ADVADS_SLUG; ?>[use-adblocker]" <?php checked( $checked, 1, true ); ?>>
<?php else: ?>
<?php _e( 'The ad block disguise can only be set by the super admin on the main site in the network.', 'advanced-ads' ); ?>
<?php endif ?>
<p class="description"><?php _e( 'Prevents ad block software from breaking your website when blocking asset files (.js, .css).', 'advanced-ads' ); ?></p>
<?php if( ! defined('AAP_VERSION') ) : ?>
<p><?php printf(__( 'Learn how to display alternative content to ad block users <a href="%s" target="_blank">in the manual</a>.', 'advanced-ads' ), ADVADS_URL . '/manual/ad-blockers/#utm_source=advanced-ads&utm_medium=link&utm_campaign=adblock-manual' ); ?></p>
<?php endif;