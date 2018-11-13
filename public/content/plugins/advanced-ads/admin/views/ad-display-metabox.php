<?php
/**
 * Render meta box for Display Conditions on ad edit page
 *
 * @package   Advanced_Ads_Admin
 * @author    Thomas Maier <thomas.maier@webgilde.com>
 * @license   GPL-2.0+
 * @link      https://wpadvancedads.com
 * @copyright since 2013 Thomas Maier, webgilde GmbH
 */

$display_conditions = Advanced_Ads_Display_Conditions::get_instance()->get_conditions();

// add mockup conditions if add-ons are missing.
$pro_conditions = array();
if ( ! defined( 'AAP_VERSION' ) ) {
	$pro_conditions[] = __( 'parent page', 'advanced-ads' );
	$pro_conditions[] = __( 'post meta', 'advanced-ads' );
	$pro_conditions[] = __( 'page template', 'advanced-ads' );
	$pro_conditions[] = __( 'url parameters', 'advanced-ads' );
}
if ( ! defined( 'AAR_VERSION' ) ) {
	$pro_conditions[] = __( 'accelerated mobile pages', 'advanced-ads' );
}
asort( $pro_conditions );

$options       = $ad->options( 'conditions' );
$empty_options = ( ! is_array( $options ) || ! count( $options ) );
if ( $empty_options ) :
	?><div class="advads-show-in-wizard">
		<p><?php esc_attr_e( 'Click on the button below if the ad should NOT show up on all pages when included automatically.', 'advanced-ads' ); ?></p>
		<button type="button" class="button button-secondary" id="advads-wizard-display-conditions-show"><?php esc_attr_e( 'Hide the ad on some pages', 'advanced-ads' ); ?></button>
	</div>
<?php endif; ?>
<div id="advads-display-conditions" 
<?php
if ( $empty_options ) :
	?>
	class="advads-hide-in-wizard"<?php endif; ?>>
	<?php
	// display help when no conditions are given.
	if ( $empty_options ) :
		$options = array();
		?>
	<p><button type="button" class="advads-video-link-inline button button-primary">
		<?php esc_attr_e( 'Watch video', 'advanced-ads' ); ?>
	</button>&nbsp;<a class="button button-secondary" href="<?php echo ADVADS_URL; ?>manual/display-conditions#utm_source=advanced-ads&utm_medium=link&utm_campaign=edit-display" target="_blank">
		<?php esc_attr_e( 'Visit the manual', 'advanced-ads' ); ?>
	</a></p>
		<?php
	endif;
	?>
	<p class="advads-jqueryui-error advads-error-message hidden">
	    <?php 
	    /*
	     * translators: %s is a link to a tutorial.
	     */
	    printf( __( 'There might be a problem with layouts and scripts in your dashboard. Please check <a href="%s" target="_blank">this article to learn more</a>.', 'advanced-ads' ), ADVADS_URL . 'manual/jquery-problem-in-dashboard/#utm_source=advanced-ads&utm_medium=link&utm_campaign=notice-jquery-error' ); ?></p>
	<p><?php esc_attr_e( 'A page with this ad on it must match all of the following conditions.', 'advanced-ads' ); ?></p>
	<table class="advads-conditions-table"><tbody>
	<?php
		$last_index = -1;
		$i          = 0;
	if ( is_array( $options ) ) :
		foreach ( $options as $_index => $_options ) :
			$show_or_force_warning = false;
			// get type attribute from previous option format.
			$_options['type'] = isset( $_options['type'] ) ? $_options['type'] : $_index;
			$connector        = ( ! isset( $_options['connector'] ) || 'or' !== $_options['connector'] ) ? 'and' : 'or';
			if ( isset( $_options['type'] ) && isset( $display_conditions[ $_options['type'] ]['metabox'] ) ) {
				$metabox = $display_conditions[ $_options['type'] ]['metabox'];
			} else {
				continue;
			}
			if ( method_exists( $metabox[0], $metabox[1] ) ) {
				/**
				 * Show warning for connector when
				 *  not set to OR already
				 *  this condition and the previous are on page level and not from the identical type
				 *  they are both set to SHOW
				 */
				$tax      = ( isset( $_options['type'] ) && isset( $display_conditions[ $_options['type'] ]['taxonomy'] ) ) ? $display_conditions[ $_options['type'] ]['taxonomy'] : false;
				$last_tax = ( isset( $options[ $last_index ]['type'] ) && isset( $display_conditions[ $options[ $last_index ]['type'] ]['taxonomy'] ) ) ? $display_conditions[ $options[ $last_index ]['type'] ]['taxonomy'] : false;
				if ( $tax && $last_tax && $last_tax === $tax
				&& ( ! isset( $_options['connector'] ) || 'or' !== $_options['connector'] )
				&& 'is' === $_options['operator'] && 'is' === $options[ $last_index ]['operator']
				&& $_options['type'] !== $options[ $last_index ]['type'] ) {

					$show_or_force_warning = true;
				}

				if ( $i > 0 ) :

					?>
			<tr class="advads-conditions-connector advads-conditions-connector-<?php echo $connector; ?>">
				<td colspan="3">
					<?php
					echo Advanced_Ads_Display_Conditions::render_connector_option( $i, $connector );
					if ( $show_or_force_warning ) {
						?>
				<p class="advads-error-message">
						<?php
						esc_attr_e( 'Forced to OR.', 'advanced-ads' );
						echo '&nbsp;<a target="_blank" href="' . ADVADS_URL . 'manual/display-conditions#manual-combining-multiple-conditions' . '">' . esc_attr__( 'manual', 'advanced-ads' ) . '</a>';
						?>
				</p>
						<?php

					}
					?>
			</td>
			</tr><?php endif; ?>
			<tr><td class="advads-conditions-type" data-condition-type="<?php echo $_options['type']; ?>"><?php echo $display_conditions[ $_options['type'] ]['label']; ?></td><td>
					<?php
					call_user_func( array( $metabox[0], $metabox[1] ), $_options, $i++ );
					?>
			</td><td><button type="button" class="advads-conditions-remove button">x</button></td></tr>
				<?php
			}
				$last_index = $_index;
			endforeach;
			endif;
	?>
			</tbody></table>
	<input type="hidden" id="advads-display-conditions-index" value="<?php echo is_array( $options ) ? count( $options ) : 0; ?>"/>
<?php
if ( $empty_options ) :
	?>
	<p><?php esc_attr_e( 'If you want to display the ad everywhere, don\'t do anything here. ', 'advanced-ads' ); ?></p>
	<?php
endif;
?>
</div>
<fieldset 
<?php
if ( $empty_options ) :
	?>
	class="advads-hide-in-wizard"<?php endif; ?>>
	<legend><?php esc_attr_e( 'New condition', 'advanced-ads' ); ?></legend>
	<div id="advads-display-conditions-new">
	<select>
		<option value=""><?php esc_attr_e( '-- choose a condition --', 'advanced-ads' ); ?></option>
		<?php foreach ( $display_conditions as $_condition_id => $_condition ) : ?>
			<option value="<?php echo $_condition_id; ?>"><?php echo $_condition['label']; ?></option>
			<?php
		endforeach;
if ( count( $pro_conditions ) ) :
	?>
		<optgroup label="<?php esc_attr_e( 'Add-On features', 'advanced-ads' ); ?>">
		<?php
		foreach ( $pro_conditions as $_pro_condition ) :
			?>
			<option disabled="disabled"><?php echo $_pro_condition; ?></option>
			<?php
endforeach;
		?>
		</optgroup>
		<?php
		endif;
?>
	</select>
	<button type="button" class="button"><?php esc_attr_e( 'add', 'advanced-ads' ); ?></button>
	<span class="advads-loader" style="display: none;"></span>
	</div>
</fieldset>
<script>
	jQuery(document).ready(function ($) {
	$('#advads-display-conditions-new button').click(function () {
		var display_condition_type = $('#advads-display-conditions-new select').val();
		var display_condition_title = $('#advads-display-conditions-new select option:selected').text();
		var display_condition_index = parseInt($('#advads-display-conditions-index').val());
		if (!display_condition_type || '' == display_condition_type ){
		return;
		}
		$('#advads-display-conditions-new .advads-loader').show(); // show loader
		$('#advads-display-conditions-new button').hide(); // hide add button
		$.ajax({
		type: 'POST',
		url: ajaxurl,
		data: {
			action: 'load_display_conditions_metabox',
			type: display_condition_type,
			index: display_condition_index,
			nonce: advadsglobal.ajax_nonce
		},
		success: function (r, textStatus, XMLHttpRequest) {
			// add
			if (r) {
			var connector = '<input style="display:none;" type="checkbox" name="<?php echo Advanced_Ads_Display_Conditions::FORM_NAME; ?>[' + display_condition_index + '][connector]" checked="checked" value="or" id="advads-conditions-'+ display_condition_index +'-connector"><label for="advads-conditions-'+ display_condition_index +'-connector"><?php esc_attr_e( 'or', 'advanced-ads' ); ?></label>';
			var newline = '<tr class="advads-conditions-connector advads-conditions-connector-or"><td colspan="3">'+connector+'</td></tr><tr><td class="advads-conditions-type" data-condition-type="'+ display_condition_type +'">' + display_condition_title + '</td><td>' + r + '</td><td><button type="button" class="advads-conditions-remove button">x</button></td></tr>';
			$('#advads-display-conditions table tbody').append(newline);
			if ( advads_use_ui_buttonset() ) {
				$('#advads-display-conditions table tbody .advads-conditions-single.advads-buttonset').buttonset();
			}
			if ( jQuery.fn.advads_button ) {
				$('#advads-display-conditions table tbody .advads-conditions-connector input').advads_button();
			}
			// increase count
			display_condition_index++;
			$('#advads-display-conditions-index').val(display_condition_index);
			// reset select
			$('#advads-display-conditions-new select')[0].selectedIndex = 0;
			}
		},
		error: function (MLHttpRequest, textStatus, errorThrown) {
			$('#advads-display-conditions-new').append(errorThrown);
		},
		complete: function( MLHttpRequest, textStatus ) { 
			$('#advads-display-conditions-new .advads-loader').hide(); // hide loader
			$('#advads-display-conditions-new button').show(); // display add button
		}
		});
	});
	});
</script>
<?php
do_action( 'advanced-ads-display-conditions-after', $ad );
