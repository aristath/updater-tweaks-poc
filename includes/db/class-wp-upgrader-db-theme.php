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


	/**
	 * Runs migrations when needed.
	 *
	 * Hooked to the {@see 'upgrader_post_install'} filter.
	 *
	 * @access public
	 *
	 * @param bool  $response   Installation response.
	 * @param array $hook_extra Extra arguments passed to hooked filters.
	 * @param array $result     Installation result data.
	 *
	 * @return bool|WP_Error The passed in $return param or WP_Error.
	 */
	public function maybe_run_migrations( $response, $hook_extra, $result ) {
		return $response;
	}
}
