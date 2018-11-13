<?php
/**
 * Renders the ad group page in WP Admin
 *
 * @package   Advanced_Ads_Admin
 * @author    Thomas Maier <thomas.maier@webgilde.com>
 * @license   GPL-2.0+
 * @link      https://wpadvancedads.com
 * @copyright since 2013 Thomas Maier, webgilde GmbH
 *
 */

$ad_groups_list = new Advanced_Ads_Groups_List();

// create new group.
if ( isset( $_REQUEST['advads-group-add-nonce'] ) ) {
	$create_result = $ad_groups_list->create_group();
	// display error message.
	if ( is_wp_error( $create_result ) ) {
		$error_string = $create_result->get_error_message();
		echo '<div id="message" class="error"><p>' . $error_string . '</p></div>';
	} else {
		echo '<div id="message" class="updated"><p>' . esc_attr__( 'Ad Group successfully created', 'advanced-ads' ) . '</p></div>';
	}
}
// save updated groups.
if ( isset( $_REQUEST['advads-group-update-nonce'] ) ) {
	$udpate_result = $ad_groups_list->update_groups();
	// display error message.
	if ( is_wp_error( $udpate_result ) ) {
		$error_string = $udpate_result->get_error_message();
		echo '<div id="message" class="error"><p>' . $error_string . '</p></div>';
	} else {
		echo '<div id="message" class="updated"><p>' . esc_attr__( 'Ad Groups successfully updated', 'advanced-ads' ) . '</p></div>';
	}
}

?><div class="wrap nosubsub">
	<h1 class="wp-heading-inline">
	<?php
	echo esc_html( $title );
	?>
	</h1>
	<?php

	if ( ! empty( $_REQUEST['s'] ) ) {
		printf( '<span class="subtitle">' . __( 'Search results for &#8220;%s&#8221;', 'advanced-ads' ) . '</span>', esc_html( wp_unslash( $_REQUEST['s'] ) ) );
	} else {
		echo ' <a href="' . Advanced_Ads_Groups_List::group_page_url( array( 'action' => 'edit' ) ) . '" id="advads-new-ad-group-link" class="add-new-h2">' . $tax->labels->add_new_item . '</a>';
	}
	?>
	<form id="advads-new-group-form" action="" method="post" style="display:none;">
		<?php wp_nonce_field( 'add-advads-groups', 'advads-group-add-nonce' ); ?>
	    <input type="text" name="advads-group-name" placeholder="<?php esc_attr_e( 'Group title', 'advanced-ads' ); ?>"/>
		<input class="button button-primary" type="submit" value="<?php esc_attr_e( 'save', 'advanced-ads' ); ?>"/>
	</form>
	<p><?php esc_attr_e( 'Ad Groups are a very flexible method to bundle ads. You can use them to display random ads in the frontend or run split tests, but also just for informational purposes. Not only can an Ad Groups have multiple ads, but an ad can belong to multiple ad groups.', 'advanced-ads' ); ?></p>
	<p><?php
	
	/*
	 * translators: %s is a URL
	 */
	printf( __( 'Find more information about ad groups in the <a href="%s" target="_blank">manual</a>.', 'advanced-ads' ), ADVADS_URL . 'manual/ad-groups/#utm_source=advanced-ads&utm_medium=link&utm_campaign=groups' ); 
	?></p>
	<?php if ( isset( $message ) ) : ?>
		<div id="message" class="updated"><p><?php echo $message; ?></p></div>
		<?php
		$_SERVER['REQUEST_URI'] = esc_url( remove_query_arg( array( 'message' ), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
	endif;
?>
	<div id="ajax-response"></div>

	<div id="col-container">
		<div class="col-wrap">
			<div class="tablenav top">
				<form class="search-form" action="" method="get">
					<!--input type="hidden" name="taxonomy" value="<?php echo esc_attr( $taxonomy ); ?>" /-->
					<input type="hidden" name="page" value="advanced-ads-groups" />
					<?php $wp_list_table->search_box( $tax->labels->search_items, 'tag' ); ?>
				</form>
			</div>
			<div id="advads-ad-group-list">
				<form action="" method="post">
					<?php wp_nonce_field( 'update-advads-groups', 'advads-group-update-nonce' ); ?>
					<table class="wp-list-table widefat fixed adgroups">
						<?php $ad_groups_list->render_header(); ?>
						<?php $ad_groups_list->render_rows(); ?>
					</table>
			<input type="hidden" name="advads-last-edited-group" id="advads-last-edited-group" value="0"/>
					<div class="tablenav bottom">
						<?php submit_button( __( 'Update Groups', 'advanced-ads' ) ); ?>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
