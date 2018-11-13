<?php

// Module configuration.

$path = dirname( __FILE__ );

return array(
	'classmap' => array(
		'Advanced_Ads_Adblock_Finder' => $path . '/public/public.php',
		'Advanced_Ads_Adblock_Finder_Admin' => $path . '/admin/admin.php',
	),
	'textdomain' => null,
);