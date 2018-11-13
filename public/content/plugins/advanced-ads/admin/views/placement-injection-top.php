<?php
// show quick injection options
// check if the ad code contains the AdSense verification and Auto ads code
$is_page_level_ad_in_code_field = isset( $ad->type ) && 'plain' === $ad->type && strpos( $ad->content, 'enable_page_level_ads' );

if ( isset( $_GET['message'] ) && 6 === $_GET['message'] ) : ?>
<div id="advads-ad-injection-box" class="advads-ad-metabox postbox">
<span class="advads-loader" style="display: none;"></span>
	<div id="advads-ad-injection-message-placement-created" class="hidden">
	<p><?php _e( 'Congratulations! Your ad is now visible in the frontend.', 'advanced-ads' ); ?></p>
	<a class="advads-placement-link button button-primary" href="<?php echo admin_url( 'admin.php?page=advanced-ads-placements#single-placement-' ); ?>"><?php _e( 'Adjust the placement options', 'advanced-ads' ); ?></a>
	<p><?php printf( __( 'Ad not showing up? Take a look <a href="%s" target="_blank">here</a>', 'advanced-ads' ), ADVADS_URL . 'manual/ads-not-showing-up/#utm_source=advanced-ads&utm_medium=link&utm_campaign=edit-ad-not-visible' ); ?></p>
	</div>
	<div id="advads-ad-injection-box-placements">
		<h2><?php _e( 'Where do you want to display the ad?', 'advanced-ads' ); ?></h2>
					  <?php
						// show different placements if this is the AdSense Auto ads code
						if ( $is_page_level_ad_in_code_field ) :
							if ( Advanced_Ads_AdSense_Data::get_instance()->is_page_level_enabled() ) :
								?>
				<p>
								<?php
								sprintf(
									__( 'The AdSense verification and Auto ads code is already activated in the <a href="%s">AdSense settings</a>.', 'advanced-ads' ),
									admin_url( 'admin.php?page=advanced-ads-settings#top#adsense' )
								);
								?>
								</p><p>
								<?php
								esc_attr_e( 'No need to add the code manually here, unless you want to include it into certain pages only.', 'advanced-ads' );
			endif;
							?>
			<p><?php esc_attr_e( 'Click on the button below to add the Auto ads code to the header of your site.', 'advanced-ads' ); ?></p>
			<div class="advads-ad-injection-box-button-wrap"><button type="button" class="advads-ad-injection-button button-primary" data-placement-type="header" style="background-image: url(<?php
								echo ADVADS_BASE_URL . 'admin/assets/img/placements/header.png';
							?>)">
							<?php
								/**
								 * translators: this is a label in a button when a user uses an AdSense Auto ads code in a plain code field
								 * the button has barely space for the original English text, so keep it short
								 */
								esc_attr_e( 'inject Auto ads', 'advanced-ads' );
							?>
								</button></div>
			<div class="clear"></div>
			
						<?php else : ?>
			<p><?php _e( 'New placement', 'advanced-ads' ); ?></p>
			<div class="advads-ad-injection-box-button-wrap"><button type="button" class="advads-ad-injection-button button-primary" data-placement-type="post_top" style="background-image: url(<?php echo ADVADS_BASE_URL . 'admin/assets/img/placements/content-before.png'; ?>)"><?php _e( 'Before Content', 'advanced-ads' ); ?></button></div>
			<div class="advads-ad-injection-box-button-wrap"><button type="button" class="advads-ad-injection-button button-primary" data-placement-type="post_content" style="background-image: url(<?php echo ADVADS_BASE_URL . 'admin/assets/img/placements/content-within.png'; ?>)"><?php _e( 'Content', 'advanced-ads' ); ?></button></div>
			<div class="advads-ad-injection-box-button-wrap"><button type="button" class="advads-ad-injection-button button-primary" data-placement-type="post_bottom" style="background-image: url(<?php echo ADVADS_BASE_URL . 'admin/assets/img/placements/content-after.png'; ?>)"><?php _e( 'After Content', 'advanced-ads' ); ?></button></div>
			<div class="advads-ad-injection-box-button-wrap"><button type="button" class="advads-ad-injection-button button-primary" data-placement-type="default" style="background-image: url(<?php echo ADVADS_BASE_URL . 'admin/assets/img/placements/manual.png'; ?>)"><?php _e( 'PHP or Shortcode', 'advanced-ads' ); ?></button></div>
			<a href="<?php echo admin_url( 'widgets.php' ); ?>"><div class="advads-ad-injection-box-button-wrap"><button type="button" class="advads-ad-injection-button button-primary" style="background-image: url(<?php echo ADVADS_BASE_URL . 'admin/assets/img/placements/widget.png'; ?>)"><?php _e( 'Manage Sidebar', 'advanced-ads' ); ?></button></div></a>
			<a href="<?php echo ADVADS_URL . 'place-ads-in-website-header/#utm_source=advanced-ads&utm_medium=link&utm_campaign=edit-placements'; ?>" target="_blank"><div class="advads-ad-injection-box-button-wrap"><button type="button" class="advads-ad-injection-button button-primary" style="background-image: url(<?php echo ADVADS_BASE_URL . 'admin/assets/img/placements/ads-in-header.png'; ?>)"><?php _e( 'Header (Manual)', 'advanced-ads' ); ?></button></div></a>
			<?php
			if ( ! defined( 'AAP_VERSION' ) ) :
				?>
			<a href="<?php echo ADVADS_URL . 'manual/custom-position-placement/#utm_source=advanced-ads&utm_medium=link&utm_campaign=edit-placements'; ?>" target="_blank"><div class="advads-ad-injection-box-button-wrap"><button type="button" class="advads-ad-injection-button button-primary advads-pro-link" style="background-image: url(<?php echo ADVADS_BASE_URL . 'admin/assets/img/placements/custom-position.png'; ?>)"><?php _e( 'Custom Position', 'advanced-ads' ); ?></button></div></a><a href="<?php echo ADVADS_URL . 'add-ons/advanced-ads-pro/#utm_source=advanced-ads&utm_medium=link&utm_campaign=edit-created-injection-pro'; ?>" target="_blank"><div class="advads-ad-injection-box-button-wrap"><button type="button" class="advads-ad-injection-button button-primary advads-pro-link" style="background-image: url(<?php echo ADVADS_BASE_URL . 'admin/assets/img/placements/content-random.png'; ?>)"><?php _e( 'Show Pro Places', 'advanced-ads' ); ?></button></div></a>
				<?php
			else :
				?>
				<div class="advads-ad-injection-box-button-wrap"><button type="button" class="advads-ad-injection-button button-primary" data-placement-type="custom_position" style="background-image: url(<?php echo ADVADS_BASE_URL . 'admin/assets/img/placements/custom-position.png'; ?>)"><?php _e( 'Custom Position', 'advanced-ads' ); ?></button></div>
				<?php
			endif;
			if ( class_exists( 'Advanced_Ads_In_Feed', false ) ) :
				?>
				<div class="advads-ad-injection-box-button-wrap"><button type="button" class="advads-ad-injection-button button-primary" data-placement-type="adsense_in_feed" style="background-image: url(<?php echo ADVADS_BASE_URL . 'admin/assets/img/placements/adsense-in-feed.png'; ?>)"><?php _e( 'AdSense In-feed', 'advanced-ads' ); ?></button></div>
				<?php
			endif;

			if ( ! defined( 'AASADS_VERSION' ) ) :
				?>
			<a href="<?php echo ADVADS_URL . 'add-ons/sticky-ads/#utm_source=advanced-ads&utm_medium=link&utm_campaign=edit-created-injection-sticky'; ?>" target="_blank"><div class="advads-ad-injection-box-button-wrap"><button type="button" class="advads-ad-injection-button button-primary advads-pro-link" style="background-image: url(<?php echo ADVADS_BASE_URL . 'admin/assets/img/placements/sticky-sidebar-left.png'; ?>)"><?php _e( 'Show Sticky Places', 'advanced-ads' ); ?></button></div></a>
				<?php
			endif;

			if ( ! defined( 'AAPLDS_VERSION' ) ) :
				?>
			<a href="<?php echo ADVADS_URL . 'add-ons/popup-and-layer-ads/#utm_source=advanced-ads&utm_medium=link&utm_campaign=edit-created-injection-layer'; ?>" target="_blank"><div class="advads-ad-injection-box-button-wrap"><button type="button" class="advads-ad-injection-button button-primary advads-pro-link" style="background-image: url(<?php echo ADVADS_BASE_URL . 'admin/assets/img/placements/layer.png'; ?>)"><?php _e( 'Show PopUp', 'advanced-ads' ); ?></button></div></a>
				<?php
			else :
				?>
				<div class="advads-ad-injection-box-button-wrap"><button type="button" class="advads-ad-injection-button button-primary" data-placement-type="layer" style="background-image: url(<?php echo ADVADS_BASE_URL . 'admin/assets/img/placements/layer.png'; ?>)"><?php _e( 'PopUp & Layer', 'advanced-ads' ); ?></button></div>
				<?php
			endif;

			?>
			<a href="<?php echo admin_url( 'admin.php?page=advanced-ads-placements' ); ?>"><div class="advads-ad-injection-box-button-wrap"><button type="button" class="advads-ad-injection-button button-primary" style="background-image: url(<?php echo ADVADS_BASE_URL . 'admin/assets/img/placements/more.png'; ?>)"><?php _e( 'see all…', 'advanced-ads' ); ?></button></div></a>
			<?php

			ob_start();
			foreach ( $placements as $_placement_slug => $_placement ) :
				if ( ! isset( $_placement['type'] ) || ! isset( $_placement['name'] ) ) {
					continue;
				}
				if ( ! isset( $placement_types[ $_placement['type'] ] ) ) {
					$_placement['type'] = 'default';
				}

				$placement_img = '';
				if ( isset( $placement_types[ $_placement['type'] ]['image'] ) ) {
					$placement_img = 'style="background-image: url(' . $placement_types[ $_placement['type'] ]['image'] . ');"';
				}
				?>

				<div class="advads-ad-injection-box-button-wrap">
				<?php
				printf(
					'<button type="button" class="advads-ad-injection-button button-primary" data-placement-slug="%s" %s title="%s">%s</button>',
					$_placement_slug, $placement_img, $_placement['name'], $placement_types[ $_placement['type'] ]['title']
				);
													 echo $_placement['name'];
				?>
				</div>
				<?php
			endforeach;
			if ( $existing_p_output = ob_get_clean() ) :
				?>
				<div class="clear"></div>
				<p><?php _e( 'Existing placement', 'advanced-ads' ); ?></p>
				<?php echo $existing_p_output; ?>
			<?php endif; ?>

			<div class="clear"></div>
			<p><?php printf( __( 'Or use the shortcode %s to insert the ad into the content manually.', 'advanced-ads' ), '<input id="advads-ad-injection-shortcode" onclick="this.select();" value="[the_ad id=\'' . $post->ID . '\']"/>' ); ?>
			<?php printf( __( 'Learn more about your choices to display an ad in the <a href="%s" target="_blank">manual</a>.', 'advanced-ads' ), ADVADS_URL . 'manual/display-ads/#utm_source=advanced-ads&utm_medium=link&utm_campaign=edit-created' ); ?></p>
		<?php endif; ?>
	</div>
</div>
	<?php
endif;
