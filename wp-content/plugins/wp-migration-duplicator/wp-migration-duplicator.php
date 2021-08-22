<?php

/**
 * The plugin bootstrap file
 *
 *
 * @link              https://www.webtoffee.com/
 * @since             1.0.0
 * @package           Wp_Migration_Duplicator
 *
 * @wordpress-plugin
 * Plugin Name:       WordPress Migration & Duplicator
 * Plugin URI:        https://wordpress.org/plugins/wp-migration-duplicator/
 * Description:       Migrate WordPress Contents and database quickly with ease.
 * Version:           1.1.5
 * Author:            WebToffee
 * Author URI:        https://www.webtoffee.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-migration-duplicator
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if(!defined('WP_MIGRATION_DUPLICATOR_VERSION')) //check plugin file already included
{
    define('WT_MGDP_PLUGIN_DEVELOPMENT_MODE', false );
    define('WT_MGDP_PLUGIN_BASENAME', plugin_basename(__FILE__) );
    define('WT_MGDP_PLUGIN_PATH', plugin_dir_path(__FILE__) );
    define('WT_MGDP_PLUGIN_URL', plugin_dir_url(__FILE__));
    define('WT_MGDP_PLUGIN_FILENAME',__FILE__);
    define('WT_MGDP_POST_TYPE','wp_migration_duplicator');
    define('WT_MGDP_DOMAIN','wp-migration-duplicator');

    /**
     * Currently plugin version.
     */
    define('WP_MIGRATION_DUPLICATOR_VERSION', '1.1.5' );
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-migration-duplicator-activator.php
 */
function activate_wp_migration_duplicator() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-migration-duplicator-activator.php';
	Wp_Migration_Duplicator_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-migration-duplicator-deactivator.php
 */
function deactivate_wp_migration_duplicator() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-migration-duplicator-deactivator.php';
	Wp_Migration_Duplicator_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wp_migration_duplicator' );
register_deactivation_hook( __FILE__, 'deactivate_wp_migration_duplicator' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-migration-duplicator.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wp_migration_duplicator() {

	$plugin = new Wp_Migration_Duplicator();
	$plugin->run();

}
run_wp_migration_duplicator();
