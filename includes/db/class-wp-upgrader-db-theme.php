<?php
/**
 * Database tweaks.
 *
 * @package WordPress
 */

/**
 * The theme DB-Upgrader class.
 */
class WP_Upgrader_DB_Theme extends WP_Upgrader_DB {

	/**
	 * The option key.
	 * Used as an index in the array returned from the option.
	 *
	 * @access protected
	 *
	 * @var string
	 */
	protected $type = 'theme';

	/**
	 * Get the current version.
	 *
	 * @abstract
	 *
	 * @access protected
	 *
	 * @return string
	 */
	public function get_current_version() {
		return wp_get_theme( $this->name )->get( 'Version' );
	}
}
