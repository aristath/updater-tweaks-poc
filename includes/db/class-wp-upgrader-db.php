<?php
/**
 * Database tweaks.
 *
 * @package WordPress
 */

/**
 * The plugin & theme DB-Upgrader class.
 */
class WP_Upgrader_DB {

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
	 * The plugin/theme id.
	 *
	 * @access protected
	 *
	 * @var string
	 */
	protected $id;

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
	 * Data in this option gets stored as an array:
	$value = [
		'plugin' => [
			'my_plugin' => [
				'1.0.0' => [ 'my_plugin_routine_1_id' ],
				'1.1.0' => [ 'my_plugin_routine_2_id', 'my_plugin_routine_2_id' ],
			],
		],
		'my_theme'  => [
			'my-theme' => [
				'1.2' => [ 'my_theme_routine_1' ],
			]
		]
	];
	 *
	 * @access private
	 *
	 * @var string
	 */
	private $option_name = 'db_upgrade_routines';

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
	 * @param string $type Can be plugin|theme.
	 * @param string $id   The plugin/theme id.
	 */
	public function __construct( $type = 'plugin', $id ) {
		$this->type = $type;
		$this->id   = $id;
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
	 * Mark successfully completed routines.
	 *
	 * @access protected
	 *
	 * @param string $version    The routine's version.
	 * @param string $routine_id The routine's unique ID.
	 *
	 * @return bool Returns the result of update_option.
	 */
	protected function set_successful_routine( $version = null, $routine_id = null ) {

		// Early exit if $version and $routine are not defined.
		if ( ! $version || ! $routine_id ) {
			return false;
		}
		$option_value = $this->get_option();
		if ( ! isset( $option_value[ $this->type ] ) ) {
			$option_value[ $this->type ] = array();
		}
		$option_value[ $this->type ][ $this->id ][ $version ]   = array();
		$option_value[ $this->type ][ $this->id ][ $version ][] = $routine_id;

		// Update the option.
		return update_option( $this->option_name, $option_value );
	}

	/**
	 * Register a migration step.
	 *
	 * @access public
	 *
	 * @param string          $version    The version for this routine.
	 * @param string          $routine_id A unique routine ID.
	 * @param string|callable $callback   A callback to run on upgrade.
	 *
	 * @return void
	 */
	public function register_routine( $version, $routine_id, $callback = null ) {
		if ( ! isset( $this->routines[ $version ] ) ) {
			$this->routines[ $version ] = array();
		}

		// Add the routine.
		$this->routines[ $version ][ $routine_id ] = $callback;

		// Make sure routines are sorted by version.
		uksort( $this->routines, 'version_compare' );
	}

	/**
	 * Get routines that haven't completed.
	 *
	 * @access public
	 *
	 * @return array
	 */
	public function get_applicable_routines() {
		$applicable_routines = $this->routines;

		// Get an array of successfully applied routines.
		$applied_routines = $this->get_successful_routines();

		// Build the array of routines that have not yet been applied.
		foreach ( $applicable_routines as $version => $routines ) {
			if ( ! empty( $applied_routines[ $version ] ) ) {

				// Remove successfully completed routines from the pool of routines to run.
				foreach ( $applied_routines[ $version ] as $routine_id ) {
					unset( $applicable_routines[ $version ][ $routine_id ] );
				}
			}

			// Remove empty items from the array.
			if ( empty( $applicable_routines[ $version ] ) ) {
				unset( $applicable_routines[ $version ] );
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
	public function get_successful_routines() {
		$option_value = $this->get_option();
		if (
			empty( $option_value[ $this->type ] ) ||
			empty( $option_value[ $this->type ][ $this->id ] )
		) {
			return array();
		}

		return $option_value[ $this->type ][ $this->id ];
	}

	/**
	 * Run routines.
	 *
	 * @access public
	 *
	 * @return true|WP_Error
	 */
	public function run_routines() {

		// Get an array of applicable routines.
		$applicable_routines = $this->get_applicable_routines();

		// Loop all applicable routines, starting from oldest version to newest.
		foreach ( $applicable_routines as $routines_version => $routines ) {

			// Loop all routines for a specific version.
			foreach ( $routines as $routine_id => $routine_callback ) {

				// Early exit with an error if the callback is invalid.
				if ( ! is_callable( $routine_callback ) ) {
					return new WP_Error(
						'upgrade_routine_invalid',
						sprintf(
							/* translators: %1$s: ID of the failed routine. %2$s: Can be plugin/theme. %3$s: The plugin/theme name. */
							__( 'Upgrade routine %1$s for %2$s %3$s is invalid' ),
							esc_html( $routine_id ), // The routine ID.
							esc_html( $this->type ), // Can be plugin/theme.
							esc_html( $this->id ) // The plugin/theme name.
						)
					);
				}

				$routine = call_user_func( $routine_callback );

				// Early eixt if there was an error with the callback routine.
				// This prevents us from running a routine unless all previous ones have succeeded.
				if ( is_wp_error( $routine ) ) {
					return $routine;
				}

				// Set the routine as successful so it doesn't run again.
				$this->set_successful_routine( $routines_version, $routine_id );
			}
		}
		return true;
	}
}
