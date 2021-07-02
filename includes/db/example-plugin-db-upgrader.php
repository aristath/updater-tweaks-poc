<?php
/**
 * This is an example implementation for a plugin database upgrading implementation.
 *
 * @package WordPress
 */

/**
 * Example function containing the upgrade code.
 * Just a WIP - POC, don't mind it.
 *
 * @return void
 */
function my_plugin_upgrade_db() {
	$upgrader = new WP_Upgrader_DB_Plugin( 'wordpress-reset/wordpress-reset.php' );
	$upgrader->register_migration(
		'0.1', // From.
		'1.5', // To.
		function() { error_log( 'Update task 1' ); }, // phpcs:ignore
	);

	$upgrader->register_migration(
		'0.1', // From.
		'1.0', // To.
		function() { error_log( 'Update task 2' ); }, // phpcs:ignore
	);

	$upgrader->register_migration(
		'1.0', // From.
		'1.1', // To.
		function() { error_log( 'Update task 3' ); }, // phpcs:ignore
	);

	$upgrader->register_migration(
		'1.0', // From.
		'1.1', // To.
		function() { error_log( 'Update task 4' ); }, // phpcs:ignore
	);

	$upgrader->register_migration(
		'1.0.1', // From.
		'1.1', // To.
		function() { error_log( 'Update task 5' ); }, // phpcs:ignore
	);
}
my_plugin_upgrade_db();
