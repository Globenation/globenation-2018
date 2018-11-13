<?php
$TZ = Advanced_Ads_Admin::timezone_get_name( Advanced_Ads_Admin::get_wp_timezone() );
?><div id="advanced-ads-expiry-date" class="misc-pub-section curtime misc-pub-curtime">
	<label onclick="advads_toggle_box('#advanced-ads-expiry-date-enable', '#advanced-ads-expiry-date .inner')">
		<input type="checkbox" id="advanced-ads-expiry-date-enable" name="advanced_ad[expiry_date][enabled]" value="1" <?php checked( $enabled, 1 ); ?>/><?php _e( 'Set expiry date', 'advanced-ads' ); ?>
	</label>
	<br/>

	<div class="inner" <?php
	if ( ! $enabled ) :
		?> style="display:none;"<?php endif; ?>><?php
			$month = '<label><span class="screen-reader-text">' . __( 'Month', 'advanced-ads' ) . '</span><select id="advads-exp-mm" name="advanced_ad[expiry_date][month]"' . ">\n";
		for ( $i = 1; $i < 13; $i = $i + 1 ) {
			$monthnum = zeroise( $i, 2 );
			$month   .= "\t\t\t" . '<option value="' . $monthnum . '" ' . selected( $curr_month, $monthnum, false ) . '>';
			$month   .= sprintf(
				_x( '%1$s-%2$s', '1: month number (01, 02, etc.), 2: month abbreviation', 'advanced-ads' ),
				$monthnum, $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) )
			) . "</option>\n";
		}
			$month .= '</select></label>';

			$day    = '<label><span class="screen-reader-text">' . __( 'Day', 'advanced-ads' ) . '</span><input type="text" id="advads-exp-jj" name="advanced_ad[expiry_date][day]" value="' . $curr_day . '" size="2" maxlength="2" autocomplete="off" /></label>';
			$year   = '<label><span class="screen-reader-text">' . __( 'Year', 'advanced-ads' ) . '</span><input type="text" id="advads-exp-aa" name="advanced_ad[expiry_date][year]" value="' . $curr_year . '" size="4" maxlength="4" autocomplete="off" /></label>';
			$hour   = '<label><span class="screen-reader-text">' . __( 'Hour', 'advanced-ads' ) . '</span><input type="text" id="advads-exp-hh" name="advanced_ad[expiry_date][hour]" value="' . $curr_hour . '" size="2" maxlength="2" autocomplete="off" /></label>';
			$minute = '<label><span class="screen-reader-text">' . __( 'Minute', 'advanced-ads' ) . '</span><input type="text" id="advads-exp-mn" name="advanced_ad[expiry_date][minute]" value="' . $curr_minute . '" size="2" maxlength="2" autocomplete="off" /></label>';

		?>
		<fieldset id="advads-exp-timestampdiv">
		<div class="timestamp-wrap">
			<?php printf( _x( '%1$s %2$s, %3$s @ %4$s %5$s', 'order of expiry date fields 1: month, 2: day, 3: year, 4: hour, 5: minute', 'advanced-ads' ), $month, $day, $year, $hour, $minute ); ?>
		</div>
		</fieldset>
		(<?php echo $TZ; ?>)
	</div>
</div>
