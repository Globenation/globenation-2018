<?php
$visitor_conditions = Advanced_Ads_Visitor_Conditions::get_instance()->get_conditions();

// add mockup conditions if add-ons are missing
$pro_conditions = array();
if ( ! defined( 'AAP_VERSION' ) ) {
	$pro_conditions[] = __( 'browser language', 'advanced-ads' );
	$pro_conditions[] = __( 'cookie', 'advanced-ads' );
	$pro_conditions[] = __( 'max. ad clicks', 'advanced-ads' );
	$pro_conditions[] = __( 'max. ad impressions', 'advanced-ads' );
	$pro_conditions[] = __( 'new visitor', 'advanced-ads' );
	$pro_conditions[] = __( 'page impressions', 'advanced-ads' );
	$pro_conditions[] = __( 'referrer url', 'advanced-ads' );
	$pro_conditions[] = __( 'user agent', 'advanced-ads' );
	$pro_conditions[] = __( 'user can (capabilities)', 'advanced-ads' );
}
if ( ! defined( 'AAGT_VERSION' ) ) {
	$pro_conditions[] = __( 'geo location', 'advanced-ads' );
}
if ( ! defined( 'AAR_VERSION' ) ) {
	$pro_conditions[] = __( 'browser width', 'advanced-ads' );
}
asort( $pro_conditions );

$options       = $ad->options( 'visitors' );
$empty_options = ( ! is_array( $options ) || ! count( $options ) );
if ( $empty_options ) :
	?><div class="advads-show-in-wizard">
		<p><?php _e( 'Click on the button below if the ad should NOT be visible to all visitors', 'advanced-ads' ); ?></p>
		<button type="button" class="button button-secondary" id="advads-wizard-visitor-conditions-show"><?php _e( 'Hide the ad from some users', 'advanced-ads' ); ?></button>
	</div>
	<?php
endif;
?>
<div id="advads-visitor-conditions"
<?php
if ( $empty_options ) :
	?>
	 class="advads-hide-in-wizard"<?php endif; ?>>
	<p class="description"><?php _e( 'Display conditions that are based on the user. Use with caution on cached websites.', 'advanced-ads' ); ?></p>
										<?php
										// display help when no conditions are given
										if ( $empty_options ) :
											$options = array();
											?>
		<p><a class="button button-primary" href="<?php echo ADVADS_URL; ?>manual/visitor-conditions#utm_source=advanced-ads&utm_medium=link&utm_campaign=edit-visitor" target="_blank">
											<?php _e( 'Visit the manual', 'advanced-ads' ); ?>
		</a></p><?php endif;
	?><table class="advads-conditions-table"><tbody>
	<?php
	if ( isset( $options ) ) :
		$i = 0;
		foreach ( $options as $_options ) :
			if ( isset( $visitor_conditions[ $_options['type'] ]['metabox'] ) ) {
				$metabox = $visitor_conditions[ $_options['type'] ]['metabox'];
			} else {
				continue;
			}
			$connector = ( ! isset( $_options['connector'] ) || 'or' !== $_options['connector'] ) ? 'and' : 'or';
			if ( method_exists( $metabox[0], $metabox[1] ) ) {
				if ( $i > 0 ) :
					?>
		<tr class="advads-conditions-connector advads-conditions-connector-<?php echo $connector; ?>">
		<td colspan="3">
					<?php
					echo Advanced_Ads_Visitor_Conditions::render_connector_option( $i, $connector );
					?>
		</td></tr>
			<?php endif; ?>
		<tr><td class="advads-conditions-type"><?php echo $visitor_conditions[ $_options['type'] ]['label']; ?></td><td>
														  <?php
															call_user_func( array( $metabox[0], $metabox[1] ), $_options, $i++ );
															?>
		</td><td><button type="button" class="advads-conditions-remove button">x</button></td></tr>
				<?php
			}
		endforeach;
	endif;
	?>
	</tbody></table>
	<input type="hidden" id="advads-visitor-conditions-index" value="<?php echo isset( $options ) ? count( $options ) : 0; ?>"/>
</div>
<?php
if ( $empty_options ) :
	?>
	<p><?php _e( 'Visitor conditions limit the number of users who can see your ad. There is no need to set visitor conditions if you want all users to see the ad.', 'advanced-ads' ); ?></p>
	<?php
elseif ( Advanced_Ads_Checks::cache() && ! defined( 'AAP_VERSION' ) ) :
	?>
	<p><span class="advads-error-message"><?php _e( 'It seems that a caching plugin is activated.', 'advanced-ads' ); ?></span>&nbsp;
	<?php
	printf( __( 'Check out cache-busting in <a href="%s" target="_blank">Advanced Ads Pro</a> if dynamic features get cached.', 'advanced-ads' ), ADVADS_URL . 'add-ons/advanced-ads-pro/#utm_source=advanced-ads&utm_medium=link&utm_campaign=edit-visitor' );
	?>
	</p>
	<?php
endif;
?>
<fieldset<?php
if ( $empty_options ) :
	?> class="advads-hide-in-wizard"<?php endif; ?>>
	<legend><?php _e( 'New condition', 'advanced-ads' ); ?></legend>
<div id="advads-visitor-conditions-new">
<select>
	<option value=""><?php _e( '-- choose a condition --', 'advanced-ads' ); ?></option>
	<?php foreach ( $visitor_conditions as $_condition_id => $_condition ) : ?>
		<?php if ( empty( $_condition['disabled'] ) ) : ?>
		<option value="<?php echo $_condition_id; ?>"><?php echo $_condition['label']; ?></option>
	<?php endif; ?>
		<?php
	endforeach;
if ( count( $pro_conditions ) ) :
	?>
	<optgroup label="<?php _e( 'Add-On features', 'advanced-ads' ); ?>">
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
<button type="button" class="button"><?php _e( 'add', 'advanced-ads' ); ?></button>
<span class="advads-loader" style="display: none;"></span>
</div>
</fieldset>
<?php if ( ! defined( 'AAR_SLUG' ) ) : ?>
<p class="advads-hide-in-wizard"><?php printf( __( 'Define the exact browser width for which an ad should be visible using the <a href="%s" target="_blank">Responsive add-on</a>.', 'advanced-ads' ), ADVADS_URL . 'add-ons/responsive-ads/#utm_source=advanced-ads&utm_medium=link&utm_campaign=edit-visitor' ); ?></p>
	<?php
endif;
?>
<script>
jQuery( document ).ready(function ($) {
	$('#advads-visitor-conditions-new button').click(function(){
		var visitor_condition_type = $('#advads-visitor-conditions-new select').val();
		var visitor_condition_title = $('#advads-visitor-conditions-new select option:selected').text();
		var visitor_condition_index = parseInt( $('#advads-visitor-conditions-index').val() );
		if( ! visitor_condition_type ) return;
		$('#advads-visitor-conditions-new .advads-loader').show();
		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'load_visitor_conditions_metabox',
				type: visitor_condition_type,
				index: visitor_condition_index,
				nonce: advadsglobal.ajax_nonce
			},
			success: function (r, textStatus, XMLHttpRequest) {
				// add
				if (r) {
					var connector = '<input type="checkbox" name="<?php echo Advanced_Ads_Visitor_Conditions::FORM_NAME; ?>[' + visitor_condition_index + '][connector]" value="or" id="advads-visitor-conditions-'+ visitor_condition_index +'-connector"><label for="advads-visitor-conditions-'+ visitor_condition_index +'-connector"><?php _e( 'and', 'advanced-ads' ); ?></label>';
					var newline = '<tr class="advads-conditions-connector advads-conditions-connector-and"><td colspan="3">'+connector+'</td></tr><tr><td>' + visitor_condition_title + '</td><td>' + r + '</td><td><button type="button" class="advads-conditions-remove button">x</button></td></tr>';
					$( '#advads-visitor-conditions table tbody' ).append( newline );
					if ( advads_use_ui_buttonset() ) { // only used in Pro right now
						$('#advads-visitor-conditions table tbody .advads-conditions-single.advads-buttonset').buttonset();
					}
					if ( jQuery.fn.advads_button ) {
						$('#advads-visitor-conditions table tbody .advads-conditions-connector input').advads_button();
					}
					// increase count
					visitor_condition_index++;
					$('#advads-visitor-conditions-index').val( visitor_condition_index );
					$('#advads-visitor-conditions-new .advads-loader').hide();
				}
			},
			error: function (MLHttpRequest, textStatus, errorThrown) {
				$( '#advads-visitor-conditions-new' ).append( errorThrown );
				$('#advads-visitor-conditions-new .advads-loader').hide();
			}
		});
	});
});
</script>
<?php
$options = $ad->options( 'visitor' );
if ( isset( $options['mobile'] ) && '' !== $options['mobile'] ) :
	?>
	<p style="color: red;"><?php _e( 'The visitor conditions below are deprecated. Please use the new version of visitor conditions to replace it.', 'advanced-ads' ); ?></p>
<ul id="advanced-ad-visitor-mobile">
	<li>
		<input type="radio" name="advanced_ad[visitor][mobile]"
			   id="advanced-ad-visitor-mobile-all" value=""
				<?php checked( empty( $options['mobile'] ), 1 ); ?>/>
		<label for="advanced-ad-visitor-mobile-all"><?php _e( 'Display on all devices', 'advanced-ads' ); ?></label>
		<input type="radio" name="advanced_ad[visitor][mobile]"
			   id="advanced-ad-visitor-mobile-only" value="only"
				<?php checked( $options['mobile'], 'only' ); ?>/>
		<label for="advanced-ad-visitor-mobile-only"><?php _e( 'only on mobile devices', 'advanced-ads' ); ?></label>
		<input type="radio" name="advanced_ad[visitor][mobile]"
			   id="advanced-ad-visitor-mobile-no" value="no"
				<?php checked( $options['mobile'], 'no' ); ?>/>
		<label for="advanced-ad-visitor-mobile-no"><?php _e( 'not on mobile devices', 'advanced-ads' ); ?></label>
	</li>
</ul>
<?php endif; ?>
<?php do_action( 'advanced-ads-visitor-conditions-after', $ad ); ?>
