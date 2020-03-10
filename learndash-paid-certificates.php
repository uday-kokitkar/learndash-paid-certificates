<?php
/**
 * Plugin Name: LearnDash Paid Certificates
 * Plugin URI: https://example.com/
 * Description: Sell certificates separately using WooCommerce.
 * Version: 1.0.0
 * Author: Uday Kokitkar
 * Author URI: https://example.com/
 * Text Domain: learndash-paid-certificates
 * Domain Path: /languages/
 * License: GPL
 * WC tested up to: 3.9.2
 *
 * @package LDPC
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'LDPC_PLUGIN_FILE' ) ) {
	define( 'LDPC_PLUGIN_FILE', __FILE__ );
}

// Include the main LDPC class.
if ( ! class_exists( 'Learndash_Paid_Certificates', false ) ) {
	include_once dirname( __FILE__ ) . '/includes/class-learndash-paid-certificates.php';
}

/**
 * Returns the main instance of LDPC.
 *
 * @since  1.0.0
 * @return LDPC class object.
 */
function ldpc() {
	return Learndash_Paid_Certificates::instance();
}

/**
 * Load autoloader.
 *
 * The new packages and autoloader require PHP 5.6+.
 */
if ( version_compare( PHP_VERSION, '5.6.0', '>=' ) && function_exists( 'spl_autoload_register' ) ) {
	require_once __DIR__ . '/includes/class-ldpc-autoloader.php';

	add_action(
		'plugins_loaded',
		function() {
			// Let's start. Who let the dogs out. Woof, woof, woof, woof, woof.
			$GLOBALS['ldpc'] = ldpc();
		}
	);
}
