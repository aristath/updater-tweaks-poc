<?php // phpcs:ignoreFile
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
	wp_register_db_upgrade_route(
		'plugin',                                     // The upgrade type. This is for a plugin.
		'wordpress-reset/wordpress-reset.php',        // The plugin name.
		'1.5',                                        // Version.
		'update_task_1',                              // Routine ID.
		function() { error_log( 'Update task 1' ); }, // The callback to run.
	);

	wp_register_db_upgrade_route(
		'plugin',
		'wordpress-reset/wordpress-reset.php',
		'1.0',
		'update_task_2',
		function() { return new WP_Error( 'example_failed_upgrade', 'EXAMPLE FAIL' ); },
	);

	wp_register_db_upgrade_route(
		'plugin',
		'wordpress-reset/wordpress-reset.php',
		'1.1',
		'update_task_3',
		function() { error_log( 'Update task 3' ); },
	);

	wp_register_db_upgrade_route(
		'plugin',
		'wordpress-reset/wordpress-reset.php',
		'1.1',
		'update_task_4',
		function() { error_log( 'Update task 4' ); },
	);

	wp_register_db_upgrade_route(
		'plugin',
		'wordpress-reset/wordpress-reset.php',
		'0.3.2',
		'update_task_5',
		function() { error_log( 'Update task 5' ); },
	);
}
my_plugin_upgrade_db();
