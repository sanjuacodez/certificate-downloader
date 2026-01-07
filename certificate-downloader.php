<?php
/**
 * Plugin Name: Certificate Downloader
 * Plugin URI:  https://sanjayshankar.me
 * Description: A high-performance WordPress plugin allowing admins to manage student eligibility via custom database tables and providing students a frontend portal to verify details, preview, and download/print certificates.
 * Version:     1.0.1
 * Author:      Sanjay Shankar
 * Author URI:  https://sanjayshankar.me
 * License:     MIT
 * Text Domain: certificate-downloader
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Autoload dependencies
if ( file_exists( plugin_dir_path( __FILE__ ) . 'vendor/autoload.php' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
}

/**
 * The code that runs during plugin activation.
 */
function activate_certificate_downloader() {
	SJS_Cert\Core\Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_certificate_downloader() {
	SJS_Cert\Core\Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_certificate_downloader' );
register_deactivation_hook( __FILE__, 'deactivate_certificate_downloader' );

/**
 * Begins execution of the plugin.
 */
function run_certificate_downloader() {
	$plugin = new SJS_Cert\Core\Plugin();
	$plugin->run();
}

run_certificate_downloader();
