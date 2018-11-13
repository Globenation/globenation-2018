<label title="<?php _e('default', 'advanced-ads'); ?>">
    <input type="radio" name="advads[placements][<?php echo $_placement_slug; ?>][options][ad_label]" value="default" <?php
    checked($_label, 'default');
    ?>/><?php _e('default', 'advanced-ads'); ?>
</label>
<label title="<?php _e('enabled', 'advanced-ads'); ?>">
    <input type="radio" name="advads[placements][<?php echo $_placement_slug; ?>][options][ad_label]" value="enabled" <?php
    checked($_label, 'enabled');
    ?>/><?php _e('enabled', 'advanced-ads'); ?>
</label>
<label title="<?php _e('disabled', 'advanced-ads'); ?>">
    <input type="radio" name="advads[placements][<?php echo $_placement_slug; ?>][options][ad_label]" value="disabled" <?php
    checked($_label, 'disabled');
    ?>/><?php _e('disabled', 'advanced-ads'); ?>
</label>
