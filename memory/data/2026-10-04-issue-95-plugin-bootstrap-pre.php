<?php
/**
 * Plugin Name: GoNorth Place Data
 * Description: Canonical structured data layer for GoNorth place listings.
 * Version: 1.18.4
 * Author: GoNorth
 * Text Domain: gonorth-place-data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GONORTH_PLACE_DATA_VERSION', '1.18.4' );
define( 'GONORTH_PLACE_DATA_FILE', __FILE__ );

require_once __DIR__ . '/includes/class-gonorth-place-data.php';
require_once __DIR__ . '/includes/class-gonorth-place-attributes.php';
require_once __DIR__ . '/includes/class-gonorth-nearby-places.php';
require_once __DIR__ . '/includes/class-gonorth-place-seo.php';
require_once __DIR__ . '/includes/class-gonorth-trip-planner.php';
require_once __DIR__ . '/includes/class-gonorth-place-analytics.php';
require_once __DIR__ . '/includes/class-gonorth-place-content.php';
require_once __DIR__ . '/includes/class-gonorth-media-alt.php';
require_once __DIR__ . '/includes/class-gonorth-nextgen-images.php';
require_once __DIR__ . '/includes/class-gonorth-organization-schema.php';
require_once __DIR__ . '/includes/class-gonorth-attribute-landing-pages.php';
require_once __DIR__ . '/includes/class-gonorth-seasonal-hubs.php';
require_once __DIR__ . '/includes/class-gonorth-internal-links.php';
require_once __DIR__ . '/includes/class-gonorth-preferred-source.php';
require_once __DIR__ . '/includes/class-gonorth-google-place-sync.php';

add_action( 'plugins_loaded', array( 'GoNorth_Place_Data', 'init' ), 20 );
add_action( 'plugins_loaded', array( 'GoNorth_Place_Attributes', 'init' ), 21 );
add_action( 'plugins_loaded', array( 'GoNorth_Nearby_Places', 'init' ), 22 );
add_action( 'plugins_loaded', array( 'GoNorth_Place_SEO', 'init' ), 23 );
add_action( 'plugins_loaded', array( 'GoNorth_Trip_Planner', 'init' ), 24 );
add_action( 'plugins_loaded', array( 'GoNorth_Place_Analytics', 'init' ), 25 );
add_action( 'plugins_loaded', array( 'GoNorth_Place_Content', 'init' ), 26 );
add_action( 'plugins_loaded', array( 'GoNorth_Media_Alt', 'init' ), 27 );
add_action( 'plugins_loaded', array( 'GoNorth_Nextgen_Images', 'init' ), 28 );
add_action( 'plugins_loaded', array( 'GoNorth_Organization_Schema', 'init' ), 29 );
add_action( 'plugins_loaded', array( 'GoNorth_Attribute_Landing_Pages', 'init' ), 30 );
add_action( 'plugins_loaded', array( 'GoNorth_Seasonal_Hubs', 'init' ), 31 );
add_action( 'plugins_loaded', array( 'GoNorth_Internal_Links', 'init' ), 32 );
add_action( 'plugins_loaded', array( 'GoNorth_Preferred_Source', 'init' ), 33 );
register_activation_hook( __FILE__, array( 'GoNorth_Place_Data', 'activate' ) );

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once __DIR__ . '/includes/class-gonorth-place-data-cli.php';
	require_once __DIR__ . '/includes/class-gonorth-place-attributes-cli.php';
	WP_CLI::add_command( 'gonorth place-data', 'GoNorth_Place_Data_CLI' );
	WP_CLI::add_command( 'gonorth attributes', 'GoNorth_Place_Attributes_CLI' );
}


