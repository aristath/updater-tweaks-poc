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
}
