<?php
/**
 * Plugin Name: GoNorth Place Data
 * Description: Canonical structured data layer for GoNorth place listings.
 * Version: 1.6.1
 * Author: GoNorth
 * Text Domain: gonorth-place-data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GONORTH_PLACE_DATA_VERSION', '1.6.1' );
define( 'GONORTH_PLACE_DATA_FILE', __FILE__ );

require_once __DIR__ . '/includes/class-gonorth-place-data.php';
require_once __DIR__ . '/includes/class-gonorth-place-attributes.php';
require_once __DIR__ . '/includes/class-gonorth-nearby-places.php';
require_once __DIR__ . '/includes/class-gonorth-place-seo.php';
require_once __DIR__ . '/includes/class-gonorth-trip-planner.php';
require_once __DIR__ . '/includes/class-gonorth-place-analytics.php';

add_action( 'plugins_loaded', array( 'GoNorth_Place_Data', 'init' ), 20 );
add_action( 'plugins_loaded', array( 'GoNorth_Place_Attributes', 'init' ), 21 );
add_action( 'plugins_loaded', array( 'GoNorth_Nearby_Places', 'init' ), 22 );
add_action( 'plugins_loaded', array( 'GoNorth_Place_SEO', 'init' ), 23 );
add_action( 'plugins_loaded', array( 'GoNorth_Trip_Planner', 'init' ), 24 );
add_action( 'plugins_loaded', array( 'GoNorth_Place_Analytics', 'init' ), 25 );
register_activation_hook( __FILE__, array( 'GoNorth_Place_Data', 'activate' ) );

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once __DIR__ . '/includes/class-gonorth-place-data-cli.php';
	require_once __DIR__ . '/includes/class-gonorth-place-attributes-cli.php';
	WP_CLI::add_command( 'gonorth place-data', 'GoNorth_Place_Data_CLI' );
	WP_CLI::add_command( 'gonorth attributes', 'GoNorth_Place_Attributes_CLI' );
}
