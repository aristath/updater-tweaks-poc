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
	$upgrader = new WP_Upgrader_DB_Plugin( 'my-plugin' );
	$upgrader->register_migration(
		'1.2', // From.
		'1.3', // To.
		'my_theme_up_4', // Update callback.
		'my_theme_down_4' // Downgrade callback.
	);

	$upgrader->register_migration(
		'0.1', // From.
		'1.0', // To.
		'my_theme_up_0', // Update callback.
		'my_theme_down_0' // Downgrade callback.
	);

	$upgrader->register_migration(
		'1.0', // From.
		'1.1', // To.
		'my_theme_up_1', // Update callback.
		'my_theme_down_1' // Downgrade callback.
	);

	$upgrader->register_migration(
		'1.0', // From.
		'1.1', // To.
		'my_theme_up_2', // Update callback.
		'my_theme_down_2' // Downgrade callback.
	);

	$upgrader->register_migration(
		'1.0.1', // From.
		'1.1', // To.
		'my_theme_up_3', // Update callback.
		'my_theme_down_3' // Downgrade callback.
	);

	$upgrader->upgrade( '1.0', '1.1' ); // WIP
}
my_plugin_upgrade_db();
