<br/><br/><p><?php _e('Position', 'advanced-ads'); ?></p>
<label title="<?php _e('default', 'advanced-ads'); ?>">
    <input type="radio" name="advads[placements][<?php echo $_placement_slug; ?>][options][placement_position]" value="" <?php
    checked($_position, 'default');
    ?>/><?php _e('default', 'advanced-ads'); ?>
</label>
<label title="<?php _e('left', 'advanced-ads'); ?>">
    <input type="radio" name="advads[placements][<?php echo $_placement_slug; ?>][options][placement_position]" value="left" <?php
    checked($_position, 'left');
    ?>/><?php _e('left', 'advanced-ads'); ?></label>
<label title="<?php _e('center', 'advanced-ads'); ?>">
    <input type="radio" name="advads[placements][<?php echo $_placement_slug; ?>][options][placement_position]" value="center" <?php
    checked($_position, 'center');
    ?>/><?php _e('center', 'advanced-ads'); ?></label>
<label title="<?php _e('right', 'advanced-ads'); ?>">
    <input type="radio" name="advads[placements][<?php echo $_placement_slug; ?>][options][placement_position]" value="right" <?php
    checked($_position, 'right');
    ?>/><?php _e('right', 'advanced-ads'); ?></label>
<p><label>
	<input type="checkbox" name="advads[placements][<?php echo $_placement_slug; ?>][options][placement_clearfix]" value="1"<?php
	checked($_clearfix, 1);
	?>/>
	<?php
	_e('Check this if you don\'t want the following elements to float around the ad. (adds a placement_clearfix)', 'advanced-ads');
	?>
    </label></p>
