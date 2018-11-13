<?php
/**
 * Advanced Ads.
 *
 * @package   Advanced_Ads
 * @author    Thomas Maier <thomas.maier@webgilde.com>
 * @license   GPL-2.0+
 * @link      https://wpadvancedads.com
 * @copyright 2013-2018 Thomas Maier, webgilde GmbH
 *
 * @wordpress-plugin
 * Plugin Name:       Advanced Ads
 * Plugin URI:        https://wpadvancedads.com
 * Description:       Manage and optimize your ads in WordPress
 * Version:           1.10.9
 * Author:            Thomas Maier
 * Author URI:        https://wpadvancedads.com
 * Text Domain:       advanced-ads
 * Domain Path:       /languages
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// only load if not already existing (maybe included from another plugin)
if ( defined( 'ADVADS_BASE_PATH' ) ) {
	return ;
}

// load basic path to the plugin
define( 'ADVADS_BASE', plugin_basename( __FILE__ ) ); // plugin base as used by WordPress to identify it
define( 'ADVADS_BASE_PATH', plugin_dir_path( __FILE__ ) );
define( 'ADVADS_BASE_URL', plugin_dir_url( __FILE__ ) );
define( 'ADVADS_BASE_DIR', dirname( ADVADS_BASE ) ); // directory of the plugin without any paths
// general and global slug, e.g. to store options in WP
define( 'ADVADS_SLUG', 'advanced-ads' );
define( 'ADVADS_URL', 'https://wpadvancedads.com/' );
define( 'ADVADS_VERSION', '1.10.9' );

/*----------------------------------------------------------------------------*
 * Autoloading, modules and functions
 *----------------------------------------------------------------------------*/

// load public functions (might be used by modules, other plugins or theme)
require_once ADVADS_BASE_PATH . 'includes/functions.php';
require_once ADVADS_BASE_PATH . 'includes/load_modules.php';
require_once ADVADS_BASE_PATH . 'includes/cap_map.php';

Advanced_Ads_ModuleLoader::getLoader(); // enable autoloading

/*----------------------------------------------------------------------------*
 * Public-Facing and Core Functionality
 *----------------------------------------------------------------------------*/

Advanced_Ads::get_instance();
Advanced_Ads_ModuleLoader::loadModules( ADVADS_BASE_PATH . 'modules/' ); // enable modules, requires base class

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

if ( is_admin() ) {
	Advanced_Ads_Admin::get_instance();
}
