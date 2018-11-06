<!-- PLUGIN SETTINGS -->
<?php

echo td_panel_generator::box_start('Mobile settings', true);

if (defined('TD_DEPLOY_MODE') && TD_DEPLOY_MODE === 'dev') {

?>

<div class="td-box-row">
	<div class="td-box-description td-box-full">
		<span class="td-box-title">More information:</span>
		<p>Description</p>
	</div>
	<div class="td-box-row-margin-bottom"></div>
</div>


<!-- Enable mobile -->
<div class="td-box-row">
	<div class="td-box-description">
		<span class="td-box-title">SHOW MOBILE</span>
		<p>Show or hide the mobile version on desktop</p>
	</div>
	<div class="td-box-control-full">
		<?php
		echo td_panel_generator::checkbox(array(
			'ds' => 'td_option',
			'option_id' => td_mobile_theme::TDM_MOBILE_ON_DESKTOP,
			'true_value' => 'no',
			'false_value' => ''
		));
		?>
	</div>
</div>

<div class="td-box-section-separator"></div>

<?php } ?>

<!-- MAIN MENU -->
<div class="td-box-row">
	<div class="td-box-description">
		<span class="td-box-title">Main menu</span>
		<p>Select a menu for the mobile menu section</p>
	</div>
	<div class="td-box-control-full">
		<?php
		echo td_panel_generator::dropdown(array(
			'ds' => 'wp_theme_menu_spot',
			'option_id' => 'header-menu-mobile',
			'values' => td_panel_generator::get_user_created_menus()
		));
		?>
	</div>
</div>

<!-- FOOTER MENU -->
<div class="td-box-row">
	<div class="td-box-description">
		<span class="td-box-title">Footer menu</span>
		<p>Select a menu for the sub-footer menu section</p>
	</div>
	<div class="td-box-control-full">
		<?php
		echo td_panel_generator::dropdown(array(
			'ds' => 'wp_theme_menu_spot',
			'option_id' => 'footer-menu-mobile',
			'values' => td_panel_generator::get_user_created_menus()
		));
		?>
	</div>
</div>

<!-- Sign In / Join: enable disable -->
<div class="td-box-row">
	<div class="td-box-description">
		<span class="td-box-title">Show sign in / join</span>
		<p>Show or hide the Sign In / Join section</p>
	</div>
	<div class="td-box-control-full">
		<?php
		echo td_panel_generator::checkbox(array(
			'ds' => 'td_option',
			'option_id' => 'tds_login_mobile',
			'true_value' => '',
			'false_value' => 'hide'
		));
		?>
	</div>
</div>

<?php echo td_panel_generator::box_end();?>



<!-- COLORS -->
<?php require_once('panel/td_panel_colors.php'); ?>

<!-- MENU BACKGROUND -->
<?php require_once('panel/td_panel_menu_background.php'); ?>

<!-- ADS -->
<?php require_once('panel/td_panel_ads.php'); ?>

<!-- EXCERPTS -->
<?php require_once('panel/td_panel_excerpts.php'); ?>

<!-- CUSTOM CODE -->
<?php require_once('panel/td_panel_custom_code.php'); ?>











