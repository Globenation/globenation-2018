<?php

// module configuration

$path = dirname( __FILE__ );

return array(
	'classmap' => array(
		'Advanced_Ads_Privacy' => $path . '/classes/plugin.php',
		'Advanced_Ads_Privacy_Admin' => $path . '/admin/admin.php',
	),
	'textdomain' => null,
);