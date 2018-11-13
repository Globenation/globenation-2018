<p class="description"><?php _e( 'Everything connected to the ads layout and output.', 'advanced-ads' ); ?></p>
<?php $options = $ad->options( 'output' ); ?>
<div class="advads-option-list">
	<span class="label"><?php _e( 'Position', 'advanced-ads' ); ?></span>
	<div id="advanced-ad-output-position">
		<label><input type="radio" name="advanced_ad[output][position]" value="" title="<?php
		_e( '- default -', 'advanced-ads' );
		?>" <?php
			if ( empty( $options['position'] ) ) {
				checked( 1, 1 ); }
			?>/><?php _e( 'default', 'advanced-ads' ); ?></label>
		<label title="<?php _e( 'left', 'advanced-ads' ); ?>"><input type="radio" name="advanced_ad[output][position]" value="left"<?php
			if ( isset( $options['position'] ) ) {
				checked( $options['position'], 'left' ); }
			?>/>
			<img src="<?php echo ADVADS_BASE_URL; ?>admin/assets/img/output-left.png" width="60" height="45"/></label>
		<label title="<?php _e( 'center', 'advanced-ads' ); ?>"><input type="radio" name="advanced_ad[output][position]" value="center" <?php
			if ( isset( $options['position'] ) ) {
				checked( $options['position'], 'center' ); }
			?>/>
			<img src="<?php echo ADVADS_BASE_URL; ?>admin/assets/img/output-center.png" width="60" height="45"/></label>
		<label title="<?php _e( 'right', 'advanced-ads' ); ?>"><input type="radio" name="advanced_ad[output][position]" value="right" <?php
			if ( isset( $options['position'] ) ) {
				checked( $options['position'], 'right' ); }
			?>/>
			<img src="<?php echo ADVADS_BASE_URL; ?>admin/assets/img/output-right.png" width="60" height="45"/></label>
	<p><label><input type="checkbox" name="advanced_ad[output][clearfix]" value="1"<?php
	if ( isset( $options['clearfix'] ) ) {
		checked( $options['clearfix'], 1 ); }
	?>/><?php
		_e( 'Check this if you don\'t want the following elements to float around the ad. (adds a clearfix)', 'advanced-ads' );
		?></label></p>
	</div>
	<hr/>
	<span class="label"><?php _e( 'Margin', 'advanced-ads' ); ?></span>
	<div id="advanced-ad-output-margin">
		<label><?php _e( 'top:', 'advanced-ads' ); ?> <input type="number" value="<?php
			if ( isset( $options['margin']['top'] ) ) {
				echo $options['margin']['top']; }
			?>" name="advanced_ad[output][margin][top]"/>px</label>
		<label><?php _e( 'right:', 'advanced-ads' ); ?> <input type="number" value="<?php
			if ( isset( $options['margin']['right'] ) ) {
				echo $options['margin']['right']; }
			?>" name="advanced_ad[output][margin][right]"/>px</label>
		<label><?php _e( 'bottom:', 'advanced-ads' ); ?> <input type="number" value="<?php
			if ( isset( $options['margin']['bottom'] ) ) {
				echo $options['margin']['bottom']; }
			?>" name="advanced_ad[output][margin][bottom]"/>px</label>
		<label><?php _e( 'left:', 'advanced-ads' ); ?> <input type="number" value="<?php
			if ( isset( $options['margin']['left'] ) ) {
				echo $options['margin']['left']; }
			?>" name="advanced_ad[output][margin][left]"/>px</label>
		<p class="description"><?php _e( 'tip: use this to add a margin around the ad', 'advanced-ads' ); ?></p>
	</div>
	<hr class="advads-hide-in-wizard"/>
	<label class='label advads-hide-in-wizard' for="advads-output-wrapper-id"><?php _e( 'container ID', 'advanced-ads' ); ?></label>
	<div class="advads-hide-in-wizard">
	<input type="text" id="advads-output-wrapper-id" name="advanced_ad[output][wrapper-id]" value="<?php
	if ( isset( $options['wrapper-id'] ) ) {
		echo $options['wrapper-id']; }
	?>"/>
	<p class="description"><?php _e( 'Specify the id of the ad container. Leave blank for random or no id.', 'advanced-ads' ); ?></p>
	</div>
	<hr  class="advads-hide-in-wizard"/>
	<label class='label advads-hide-in-wizard' for="advads-output-wrapper-class"><?php _e( 'container classes', 'advanced-ads' ); ?></label>
	<div class="advads-hide-in-wizard">
	<input type="text" id="advads-output-wrapper-class" name="advanced_ad[output][wrapper-class]" value="<?php
	if ( isset( $options['wrapper-class'] ) ) {
		echo $options['wrapper-class']; }
	?>"/>
	<p class="description"><?php _e( 'Specify one or more classes for the container. Separate multiple classes with a space', 'advanced-ads' ); ?></p>
	</div>
	<hr class="advads-hide-in-wizard"/>
	<label for="advads-output-debugmode" class="label advads-hide-in-wizard"><?php _e( 'Enable debug mode', 'advanced-ads' ); ?></label>
	<div class="advads-hide-in-wizard">
	<input id="advads-output-debugmode" type="checkbox" name="advanced_ad[output][debugmode]" value="1" <?php
	if ( isset( $options['debugmode'] ) ) {
		checked( $options['debugmode'], 1 ); }
	?>/>

	<a href="<?php echo ADVADS_URL; ?>manual/ad-debug-mode/#utm_source=advanced-ads&utm_medium=link&utm_campaign=ad-debug-mode" target="_blank"><?php _e( 'Manual', 'advanced-ads' ); ?></a>
	</div>

	<?php do_action( 'advanced-ads-output-metabox-after', $ad ); ?>

</div>
