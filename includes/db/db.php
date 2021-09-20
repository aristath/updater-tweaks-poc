<?php
/**
 * Database tweaks.
 *
 * @package WordPress
 */

/**
 * Register a database upgrade route.
 *
 * @param string   $type       Can be "plugin" or "theme".
 * @param string   $name       The name of the plugin or theme.
 * @param string   $version    The version of the plugin or theme.
 * @param string   $routine_id The ID of the upgrade routine.
 * @param callable $callback   The callback to run in order to upgeade the db.
 */
function wp_register_db_upgrade_route( $type, $name, $version, $routine_id, $callback ) {
	$upgrader = new WP_Upgrader_DB( $type, $name );
	$upgrader->register_routine( $version, $routine_id, $callback );
}

// Require the db-upgrader classes.
require_once __DIR__ . '/class-wp-upgrader-db.php';

// example implementation for a plugin.
require_once __DIR__ . '/example-plugin-db-upgrader.php';
