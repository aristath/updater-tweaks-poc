<?php
/**
 * Database tweaks.
 *
 * @package WordPress
 */

function wp_register_db_upgrade_route( $type, $name, $version, $routine_id, $callback ) {
	$upgrader = new WP_Upgrader_DB( $type, $name );
	$upgrader->register_routine( $version, $routine_id, $callback );
}

// Require the db-upgrader classes.
require_once __DIR__ . '/class-wp-upgrader-db.php';

// example implementation for a plugin.
require_once __DIR__ . '/example-plugin-db-upgrader.php';
