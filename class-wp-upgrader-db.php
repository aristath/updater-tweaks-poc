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
	 * The option-name where versions are stored in the database.
	 *
	 * @access private
	 *
	 * @var string
	 */
	private $option_name = 'db_versions';

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
}
