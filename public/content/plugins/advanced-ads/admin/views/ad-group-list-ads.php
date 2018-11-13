<?php 
/**
 * Render a list of ads included in an ad group
 *
 * @package   Advanced_Ads_Admin
 * @author    Thomas Maier <thomas.maier@webgilde.com>
 * @license   GPL-2.0+
 * @link      https://wpadvancedads.com
 * @copyright since 2013 Thomas Maier, webgilde GmbH
 */
 
?><table class="advads-group-ads">
    <thead><tr><th><?php esc_attr_e( 'Ad', 'advanced-ads' );
?></th><th colspan="2"><?php esc_attr_e( 'weight', 'advanced-ads' ); ?></th></tr></thead>
	<tbody>
<?php
if ( count( $ad_form_rows ) ) {
	foreach ( $ad_form_rows as $_row ) {
		echo $_row;
	}
}
?>
	</tbody>
</table>

<?php if ( $ads_for_select ) : ?>
	<fieldset class="advads-group-add-ad">
		<legend><?php esc_attr_e( 'New Ad', 'advanced-ads' ); ?></legend>
		<select class="advads-group-add-ad-list-ads">
			<?php
			foreach ( $ads_for_select as $_ad_id => $_ad_title ) {
				echo '<option value="advads-groups[' . $group->id . '][ads][' . $_ad_id . ']">' . $_ad_title . '</option>';
			}
			?>
		</select>
		<?php echo $new_ad_weights; ?>
		<button type="button" class="button"><?php esc_attr_e( 'add', 'advanced-ads' ); ?></button>
	</fieldset>
	<?php
endif;
