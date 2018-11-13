<?php

// module configuration

$path = dirname( __FILE__ );

return array(
	'classmap' => array(
		'Advanced_Ads_Ad_Blocker' => $path . '/classes/plugin.php',
		'Advanced_Ads_Ad_Blocker_Admin' => $path . '/admin/admin.php',
	),
	'textdomain' => null,
);