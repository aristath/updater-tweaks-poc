<?php
/**
 * Database tweaks.
 *
 * @package WordPress
 */

/**
 * The plugin & theme DB-Upgrader class.
 */
abstract class WP_Upgrader_DB {

	/**
	 * The option key.
	 * Used as an index in the array returned from the option.
	 * Can be "plugin" or "theme". Future APIs can use different keys.
	 *
	 * @access protected
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * The plugin/theme name.
	 *
	 * @access protected
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * An array containing all registered migrations for this upgrader.
	 *
	 * @access protected
	 *
	 * @var array
	 */
	protected $migrations = array();

	/**
	 * The option-name where versions are stored in the database.
	 *
	 * @access private
	 *
	 * @var string
	 */
	private $option_name = 'db_versions';

	/**
	 * The current version.
	 *
	 * @access protected
	 *
	 * @var string
	 */
	protected $current_version;

	/**
	 * The new version.
	 *
	 * @access protected
	 *
	 * @var string
	 */
	protected $new_version;

	/**
	 * Constructor.
	 *
	 * @access public
	 *
	 * @param string $name The plugin name.
	 */
	public function __construct( $name ) {
		$this->name            = $name;
		$this->current_version = $this->get_current_version();

		add_action( 'upgrader_install_package_result', array( $this, 'set_new_version' ), 1, 2 );
		add_action( 'upgrader_install_package_result', array( $this, 'maybe_run_migrations' ), 100, 2 );
	}

	/**
	 * Gets the option value.
	 *
	 * @access protected
	 *
	 * @return array
	 */
	protected function get_option() {
		return (array) get_option( $this->option_name, array() );
	}

	/**
	 * Get the plugin or theme versions.
	 *
	 * @access protected
	 *
	 * @return array Returns the value of the saved option, using the $type var.
	 */
	protected function get_versions() {
		$option                = $this->get_option();
		$option[ $this->type ] = isset( $option[ $this->type ] )
			? (array) $option[ $this->type ]
			: array();

		return $option;
	}

	/**
	 * Get the version for a specific plugin/theme.
	 *
	 * @access protected
	 *
	 * @return string|false
	 */
	protected function get_version() {
		$versions = $this->get_versions();
		return isset( $versions[ $this->name ] ) ? $versions[ $this->name ] : false;
	}

	/**
	 * Set the version for a specific plugin/theme.
	 *
	 * @access protected
	 *
	 * @param string $version The version to set.
	 *
	 * @return bool Returns the result of update_option.
	 */
	protected function set_version( $version ) {
		$option_value = $this->get_option();
		if ( ! isset( $option_value[ $this->type ] ) ) {
			$option_value[ $this->type ] = array();
		}
		$option_value[ $this->type ][ $this->name ] = $version;

		return update_option( $this->option_name, $option_value );
	}

	/**
	 * Register a migration step.
	 *
	 * @access public
	 *
	 * @param string          $from               The version from which we're starting.
	 * @param string          $to                 The version to which we're ending.
	 * @param string|callable $upgrade_callback   A callback to run on upgrade.
	 * @param string|callable $downgrade_callback A callback to run on downgrade.
	 *
	 * @return void
	 */
	public function register_migration( $from, $to, $upgrade_callback = null, $downgrade_callback = null ) {
		if ( ! isset( $this->migrations[ $from ] ) ) {
			$this->migrations[ $from ] = array();
		}
		if ( ! isset( $this->migrations[ $from ][ $to ] ) ) {
			$this->migrations[ $from ][ $to ] = array();
		}
		$this->migrations[ $from ][ $to ][] = array( $upgrade_callback, $downgrade_callback );
	}

	/**
	 * Get an ordered array of migrations to run.
	 *
	 * @access protected
	 *
	 * @param string $from The version from which we're starting.
	 * @param string $to   The version to which we're ending.
	 *
	 * @return array
	 */
	protected function get_registered_migrations( $from, $to ) {
		$filtered_migrations = array();

		uksort( $this->migrations, 'version_compare' );

		foreach ( $this->migrations as $step_from => $migrations_steps ) {
			if ( $from !== $step_from && version_compare( $from, $step_from ) >= 0 ) {
				continue;
			}

			uksort( $migrations_steps, 'version_compare' );

			foreach ( $migrations_steps as $step_to => $migrations ) {
				if ( $to !== $step_to && version_compare( $to, $step_to ) <= 0 ) {
					continue;
				}

				$filtered_migrations = array_merge( $filtered_migrations, $migrations );
			}
		}

		return $filtered_migrations;
	}

	/**
	 * Run migrations.
	 *
	 * If $from is greater than $to, then it's an upgrade.
	 * If $to is greater than $from, then it's a dowgrade.
	 *
	 * @access public
	 *
	 * @param string $from The version from which we're starting.
	 * @param string $to   The version to which we're ending.
	 *
	 * @return void
	 */
	public function run_migrations( $from, $to ) {
		$is_upgrade   = version_compare( $from, $to ) <= 0;
		$is_downgrade = ! $is_upgrade;

		// Get the steps.
		if ( $is_downgrade ) {
			$steps = $this->get_registered_migrations( $to, $from );
		} else {
			$steps = $this->get_registered_migrations( $from, $to );
		}

		// Reverse steps order if we're downgrading.
		if ( $is_downgrade ) {
			$steps = array_reverse( $steps );
		}

		// Run the callbacks.
		foreach ( $steps as $step ) {
			$callback = $is_downgrade ? $step[1] : $step[0];
			if ( $callback && is_callable( $callback ) ) {
				call_user_func( $callback );
			}
		}
	}

	/**
	 * Get the current version.
	 * In most cases (plugins & themes) this will be done using the versions saved in files.
	 *
	 * @abstract
	 *
	 * @access protected
	 *
	 * @return string
	 */
	abstract protected function get_current_version();

	/**
	 * Set the $current_version attribute.
	 *
	 * @access public
	 *
	 * @param bool|WP_Error $result     Result from `WP_Upgrader::install_package()`.
	 * @param array         $hook_extra Array of data for plugin/theme being updated.
	 *
	 * @return bool|WP_Error
	 */
	public function set_new_version( $result, $hook_extra ) {
		if ( isset( $hook_extra[ $this->type ] ) && $this->name === $hook_extra[ $this->type ] ) {
			$this->new_version = $this->get_current_version();
		}
		return $result;
	}

	/**
	 * Runs migrations when needed.
	 *
	 * @access public
	 *
	 * @param bool|WP_Error $result     Result from `WP_Upgrader::install_package()`.
	 * @param array         $hook_extra Array of data for plugin/theme being updated.
	 *
	 * @return bool|WP_Error
	 */
	public function maybe_run_migrations( $result, $hook_extra ) {
		if (
			! is_wp_error( $result ) && // The response is not an error.
			isset( $hook_extra[ $this->type ] ) && // We're running the right type of upgrade.
			$this->name === $hook_extra[ $this->type ] && // We're updating the right thing.
			! empty( $this->new_version ) && // The new version exists.
			! empty( $this->current_version ) && // The old version exists.
			$this->new_version !== $this->current_version // The new version is not the same as the old version.
		) {
			$this->run_migrations( $this->current_version, $this->new_version );
		}
		return $result;
	}
}
