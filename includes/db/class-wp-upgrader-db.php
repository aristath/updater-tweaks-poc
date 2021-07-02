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
	 * An array containing all registered routines for this upgrader.
	 *
	 * @access protected
	 *
	 * @var array
	 */
	protected $routines = array();

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
	protected $version;

	/**
	 * Constructor.
	 *
	 * @access public
	 *
	 * @param string $name    The plugin/theme name.
	 */
	public function __construct( $name ) {
		$this->name = $name;

		add_action( 'init', array( $this, 'run_routines' ) );
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
	 * Set the version for a specific plugin/theme.
	 *
	 * @access protected
	 *
	 * @param string $version    The routine's version.
	 * @param string $routine_id The routine's unique ID.
	 *
	 * @return bool Returns the result of update_option.
	 */
	protected function set_successful_routine( $version = null, $routine_id = null ) {
		if ( ! $version || ! $routine_id ) {
			return;
		}
		$option_value = $this->get_option();
		if ( ! isset( $option_value[ $this->type ] ) ) {
			$option_value[ $this->type ] = array();
		}
		$option_value[ $this->type ][ $this->name ][ $version ]   = array();
		$option_value[ $this->type ][ $this->name ][ $version ][] = $routine_id;

		return update_option( $this->option_name, $option_value );
	}

	/**
	 * Register a migration step.
	 *
	 * @access public
	 *
	 * @param string          $version    The version to which we're ending.
	 * @param string          $routine_id A unique routine ID.
	 * @param string|callable $callback   A callback to run on upgrade.
	 *
	 * @return void
	 */
	public function register_migration( $version, $routine_id, $callback = null ) {
		if ( ! isset( $this->routines[ $version ] ) ) {
			$this->routines[ $version ] = array();
		}
		$this->routines[ $version ][ $routine_id ] = $callback;
		uksort( $this->routines, 'version_compare' );
	}

	/**
	 * Get routines that haven't run for this version.
	 *
	 * @access public
	 *
	 * @return array
	 */
	public function get_applicable_routines() {
		$applied_routines    = $this->get_applied_routines();
		$applicable_routines = array();

		foreach ( $this->routines as $version => $routines ) {
			if ( ! isset( $applied_routines[ $version ] ) ) {
				$applicable_routines[ $version ] = $routines;
				continue;
			}
			foreach ( $routines as $routine_id => $callback ) {
				if ( ! isset( $applied_routines[ $version ][ $routine_id ] ) ) {
					$applicable_routines[ $version ][ $routine_id ] = $callback;
				}
			}
		}
		return $applicable_routines;
	}

	/**
	 * Get an array of applied routines.
	 *
	 * @access public
	 *
	 * @return array
	 */
	public function get_applied_routines() {
		$option_value = $this->get_option();
		if ( ! isset( $option_value[ $this->type ] ) ) {
			$option_value[ $this->type ] = array();
		}
		if ( ! isset( $option_value[ $this->type ][ $this->name ] ) ) {
			$option_value[ $this->type ][ $this->name ] = array();
		}
		return $option_value[ $this->type ][ $this->name ];
	}

	/**
	 * Run routines.
	 *
	 * @access public
	 *
	 * @return true|WP_Error
	 */
	public function run_routines() {

		// Get the routines.
		$applicable_routines = $this->get_applicable_routines();

		foreach ( $applicable_routines as $routines_version => $routines ) {
			foreach ( $routines as $routine_id => $routine_callback ) {
				if ( $routine_callback && ! is_callable( $routine_callback ) ) {
					return new WP_Error(
						'upgrade_routine_invalid',
						__( 'Upgrade routine is invalid' ) // TODO: Add details for the plugin/theme name, the version & the routine ID.
					);
				}

				$routine = call_user_func( $routine_callback );
				if ( is_wp_error( $routine ) ) {
					return $routine;
				}

				$this->set_successful_routine( $routines_version, $routine_id );
			}
		}
		return true;
	}
}
