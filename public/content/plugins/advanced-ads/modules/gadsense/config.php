<?php

// module configuration

$path = dirname( __FILE__ );

return array(
	'classmap' => array(
		'Advanced_Ads_Ad_Type_Adsense' => $path . '/includes/class-ad-type-adsense.php',
		'Advanced_Ads_AdSense_Data' => $path . '/includes/class-gadsense-data.php',
		'Advanced_Ads_AdSense_MAPI' => $path . '/includes/class-mapi.php',
		'Advanced_Ads_AdSense_Admin' => $path . '/admin/admin.php',
		'Advanced_Ads_AdSense_Public' => $path . '/public/public.php',
	),
	'textdomain' => null,
);