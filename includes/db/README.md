# Database Upgrader for Plugins & Themes

This POC implementation allows plugins & themes to register migration paths for database entries.

## `WP_Upgrader_DB`

This is an abstract class and should be extended for plugins/themes.
Don't call this directly, instead call one of its children:
* `WP_Upgrader_DB_Plugin`
* `WP_Upgrader_DB_Theme`

The reason we use children classes is to avoid conflicts in callback registrations in case there is a plugin and a theme with the same name.

## `WP_Upgrader_DB_Plugin`
Allows us to run migrations on a plugin.

```php
$upgrader = new WP_Upgrader_DB_Plugin( $plugin_name );
$upgrader->register_routine( $version, $routine_id, $callback );
```

* `$plugin_name` (string): The plugin's slug.
* `$version` (string): The version for which this migration should apply.
* `$routine_id` (string): A unique ID for the upgrade routine.
* `$callback` (callable|string): The name of a function if string, or anything else that can be used as a callable.

Example:
```php
function my_plugin_v110_upgrade() {
	// Do something.
}
add_action( 'after_setup_plugin', function() {
	$upgrader = new WP_Upgrader_DB_Plugin( 'my-plugin' );
	$upgrader->register_routine( '1.1', 'my_plugin_v110_upgrade', 'my_plugin_v110_upgrade' );
	$upgrader->register_routine( '1.2.0', 'my_plugin_v120_upgrade', function() {
		// Do something.
	} );
});
```
Or using a class:

```php
class My_Plugin_Migrations {
	$id = 'my_plugin';
	$routines = [
		'1.0.0' => 'callback_100',
		'1.1.0' => 'callback_110',
		'1.1.1' => 'callback_111',
	];
	public function __construct() {
		$upgrader = new WP_Upgrader_DB_Plugin( $this->id );
		foreach ( $this->routines as $version => $callback_method ) {
			$upgrader->register_routine(
				$version,
				"{$this->id}_{$callback_method}",
				[ $this, $callback_method ]
			);
		}
	}
	protected function callback_100() { /* Do something. */ }
	protected function callback_110() { /* Do something. */ }
	protected function callback_111() { /* Do something. */ }
}
```
## `WP_Upgrader_DB_Theme`

Usage is exactly the same as `WP_Upgrader_DB_Plugin`.
