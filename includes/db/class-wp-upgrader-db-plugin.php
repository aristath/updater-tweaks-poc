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
		$this->name            = $name;
		$this->current_version = $this->get_current_version();
	}

	/**
	 * Get the current version.
	 *
	 * @access protected
	 *
	 * @return string
	 */
	protected function get_current_version() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$all_plugins = get_plugins();

		foreach ( $all_plugins as $plugin => $plugin_data ) {
			if ( $this->name === $plugin ) {
				return isset( $plugin_data['Version'] ) ? $plugin_data['Version'] : '';
			}
		}
		return '';
	}
}
