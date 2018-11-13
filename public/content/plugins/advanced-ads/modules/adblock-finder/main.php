<?php

class_exists( 'Advanced_Ads', false ) || exit();

$is_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;

if ( ! is_admin() ) {
	new Advanced_Ads_Adblock_Finder;
} elseif ( ! $is_ajax ) {
	new Advanced_Ads_Adblock_Finder_Admin;
}