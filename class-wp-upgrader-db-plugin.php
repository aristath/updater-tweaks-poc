<?php
/**
 * Database tweaks.
 *
 * @package WordPress
 */

/**
 * The plugin DB-Upgrader class.
 */
class WP_Upgrader_DB_Plugin extends WP_Upgrader_DB {

	/**
	 * The option key.
	 * Used as an index in the array returned from the option.
	 *
	 * @access protected
	 *
	 * @var string
	 */
	protected $type = 'plugin';

	/**
	 * Constructor.
	 *
	 * @access public
	 *
	 * @param string $name The plugin name.
	 */
	public function __construct( $name ) {
		$this->name = $name;
	}
}
