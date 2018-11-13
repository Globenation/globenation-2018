<?php
/**
 * Add and edit an ad group (taxonomy)
 *
 * @package   Advanced_Ads_Admin
 * @author    Thomas Maier <thomas.maier@webgilde.com>
 * @license   GPL-2.0+
 * @link      https://wpadvancedads.com
 * @copyright since 2013 Thomas Maier, webgilde GmbH
 * @deprecated
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' ); }

if ( ! is_int( $group_id ) ) {
	?>
	<div id="message" class="updated"><p><strong><?php esc_attr_e( 'You did not select an item for editing.', 'advanced-ads' ); ?></strong></p></div>
	<?php
	return;
}

do_action( "{$taxonomy}_pre_edit_form", $tag, $taxonomy );
?>

<div class="wrap">
	<h1><?php echo $tax->labels->edit_item; ?></h1>
	<div id="ajax-response"></div>
	<form name="editgroup" id="editgroup" method="post" action="<?php echo Advanced_Ads_Groups_List::group_page_url(); ?>" class="validate"<?php do_action( $taxonomy . '_term_edit_form_tag' ); ?>>
		<input type="hidden" name="action" value="editedgroup" />
		<input type="hidden" name="group_id" value="<?php echo $group_id; ?>" />
		<input type="hidden" name="taxonomy" value="<?php echo esc_attr( $taxonomy ); ?>" />
<?php
wp_original_referer_field( true, 'previous' );
wp_nonce_field( 'update-group_' . $group_id );
?>
		<table class="form-table">
			<tr class="form-field form-required">
			    <th scope="row" valign="top"><label for="name"><?php echo esc_attr_x( 'Name', 'Taxonomy Name', 'advanced-ads' ); ?></label></th>
				<td><input name="name" id="name" type="text" value="
				<?php
				if ( isset( $tag->name ) ) {
					echo esc_attr( $tag->name ); }
				?>
				" size="40" aria-required="true" /></td>
			</tr>
<?php if ( ! global_terms_enabled() ) { ?>
				<tr class="form-field">
					<th scope="row" valign="top"><label for="slug"><?php echo esc_attr_x( 'Slug', 'Taxonomy Slug', 'advanced-ads' ); ?></label></th>
					<td><input name="slug" id="slug" type="text" value="<?php
					if ( isset( $tag->slug ) ) {
						echo esc_attr( apply_filters( 'editable_slug', $tag->slug ) ); }
					?>" size="40" />
					<p class="description"><?php esc_attr_e( 'An id-like string with only letters in lower case, numbers, and hyphens.', 'advanced-ads' ); ?></p></td>
				</tr>
	<?php
}
	$text = ( isset( $tag->description ) ) ? $tag->description : '';
?>
			<tr class="form-field">
			    <th scope="row" valign="top"><label for="description"><?php echo esc_attr_x( 'Description', 'Taxonomy Description', 'advanced-ads' ); ?></label></th>
				<td><textarea name="description" id="description" rows="5" cols="50" class="large-text"><?php echo esc_textarea( $text ); ?></textarea></td>
			</tr>
			<?php

			do_action( $taxonomy . '_edit_form_fields', $tag, $taxonomy );
			?>
		</table>
		<?php
		do_action( $taxonomy . '_edit_form', $tag, $taxonomy );

		if ( 0 === $group_id ) {
			submit_button(esc_attr__( 'Create new Ad Group', 'advanced-ads' ) );
		} else {
			submit_button( esc_attr__( 'Update', 'advanced-ads' ) );
		}
		?>
	</form>
</div>
<script type="text/javascript">
	try {
		document.forms.edittag.name.focus();
	} catch (e) {
	}
</script>
