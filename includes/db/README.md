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
Example:

```php
$upgrader = new WP_Upgrader_DB_Plugin( $plugin_name );
$upgrader->register_migration( $version, $routine_id, $callback );
```

* `$plugin_name` (string): The plugin's slug.
* `$version` (string): The version for which this migration should apply.
* `$routine_id` (string): A unique ID for the upgrade routine.
* `$callback` (callable|string): The name of a function if string, or anything else that can be used as a callable.

## `WP_Upgrader_DB_Theme`

Usage is exactly the same as `WP_Upgrader_DB_Plugin`.
