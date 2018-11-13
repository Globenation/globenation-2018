<?php
if ( ! defined( 'ABSPATH' ) )
    exit;

if ( ! class_exists( '_WP_Editors' ) )
    require( ABSPATH . WPINC . '/class-wp-editor.php' );

function advads_shortcode_creator_l10n() {
    $strings = array(
        'title'  => _x( 'Add an ad', 'shortcode creator', 'advanced-ads' ),
        'ok'     => _x( 'Add shortcode', 'shortcode creator', 'advanced-ads' ),
        'cancel' => _x( 'Cancel', 'shortcode creator', 'advanced-ads' ),
    );
    $locale = _WP_Editors::$mce_locale;
    $translated = 'tinyMCE.addI18n("' . $locale . '.advads_shortcode", ' . json_encode( $strings ) . ");\n";

     return $translated;
}

$strings = advads_shortcode_creator_l10n();
