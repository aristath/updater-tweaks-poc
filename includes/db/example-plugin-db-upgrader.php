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
		'1.5', // Version.
		'update_task_1', // Routine ID.
		function() { error_log( 'Update task 1' ); }, // phpcs:ignore
	);

	$upgrader->register_migration(
		'1.0', // Version.
		'update_task_2', // Routine ID.
		function() { error_log( 'Update task 2' ); return new WP_Error( 'example_failed_upgrade', 'EXAMPLE FAIL' ); }, // phpcs:ignore
	);

	$upgrader->register_migration(
		'1.1', // Version.
		'update_task_3', // Routine ID.
		function() { error_log( 'Update task 3' ); }, // phpcs:ignore
	);

	$upgrader->register_migration(
		'1.1', // Version.
		'update_task_4', // Routine ID.
		function() { error_log( 'Update task 4' ); }, // phpcs:ignore
	);

	$upgrader->register_migration(
		'0.3.2', // Version.
		'update_task_5', // Routine ID.
		function() { error_log( 'Update task 5' ); }, // phpcs:ignore
	);
}
my_plugin_upgrade_db();
