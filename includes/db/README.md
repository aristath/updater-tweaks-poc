# Database Upgrader for Plugins & Themes

This POC implementation allows plugins & themes to register migration paths for database entries.

## `wp_register_db_upgrade_route`

This function is a proxy for the `WP_Upgrader_DB` class.

```php
wp_register_db_upgrade_route( $type, $name, $version, $routine_id, $callback );
```

## `WP_Upgrader_DB`

```php
$upgrader = new WP_Upgrader_DB( $type, $name );
$upgrader->register_routine( $version, $routine_id, $callback );
```

* `$type` (string): `plugin`|`theme`
* `$name` (string): The plugin's slug.
* `$version` (string): The version for which this migration should apply.
* `$routine_id` (string): A unique ID for the upgrade routine.
* `$callback` (callable|string): The name of a function if string, or anything else that can be used as a callable.

Example using the `wp_register_db_upgrade_route` function:
```php
function my_plugin_v110_upgrade() { /* Do something. */ }
add_action( 'after_setup_theme', function() {
	wp_register_db_upgrade_route( 'plugin', 'my-plugin', '1.1', 'my_plugin_v110_upgrade', 'my_plugin_v110_upgrade' );
	wp_register_db_upgrade_route( 'plugin', 'my-plugin', '1.2.0', 'my_plugin_v120_upgrade', function() { /* Do something. */ } );
} );
```

Example using the `WP_Upgrader_DB` class:
```php
function my_plugin_v110_upgrade() { /* Do something. */ }
add_action( 'after_setup_theme', function() {
	$upgrader = new WP_Upgrader_DB( 'plugin', 'my-plugin' );
	$upgrader->register_routine( '1.1', 'my_plugin_v110_upgrade', 'my_plugin_v110_upgrade' );
	$upgrader->register_routine( '1.2.0', 'my_plugin_v120_upgrade', function() { /* Do something. */ } );
} );
```
