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
		if ( isset( $hook_extra['plugin'] ) ) {
			$plugin      = get_plugin_data( $result['local_destination'] . '/' . $hook_extra['plugin'] );
			$old_version = $this->current_version;
			$new_version = $plugin['Version'];

			if (
				$response && // The response is not an error.
				$this->name === $hook_extra['plugin'] && // We're updating the right thing.
				! empty( $new_version ) && // The new version exists.
				! empty( $old_version ) && // The old version exists.
				$new_version !== $old_version // The new version is not the same as the old version.
			) {
				$this->run_migrations( $old_version, $new_version );
			}

			$this->set_version( $new_version );
		}
		return $response;
	}
}
