<?php
/**
 * Rollbacks tweaks.
 *
 * @package WordPress
 */

add_filter(
	'upgrader_pre_install',
	/**
	 * Move the plugin/theme being upgraded into a rollback directory.
	 *
	 * @global WP_Filesystem_Base $wp_filesystem WordPress filesystem subclass.
	 *
	 * @param bool  $response   Boolean response to 'upgrader_pre_install' filter.
	 *                          Default is true.
	 * @param array $hook_extra Array of data for plugin/theme being updated.
	 *
	 * @return bool|WP_Error
	 */
	function( $response, $hook_extra ) {
		global $wp_filesystem;

		// Early exit if $hook_extra is empty,
		// or if this is an installation and not update.
		if ( empty( $hook_extra ) || ( isset( $hook_extra['action'] ) && 'install' === $hook_extra['action'] ) ) {
			return $response;
		}

		if ( isset( $hook_extra['plugin'] ) || isset( $hook_extra['theme'] ) ) {
			$rollback_dir       = $wp_filesystem->wp_content_dir() . 'upgrade/rollback/';
			$rollback_subfolder = isset( $hook_extra['plugin'] ) ? 'plugins' : 'themes';
			$slug               = isset( $hook_extra['plugin'] ) ? dirname( $hook_extra['plugin'] ) : $hook_extra['theme'];
			$src                = isset( $hook_extra['plugin'] ) ? $wp_filesystem->wp_plugins_dir() . '/' . $slug : get_theme_root() . '/' . $slug;

			// Create the rollbacks dir if it doesn't exist.
			if ( $wp_filesystem->mkdir( $rollback_dir ) && $wp_filesystem->mkdir( "$rollback_dir/$rollback_subfolder/" ) ) {

				// Move the plugin or theme to its rollback folder.
				$wp_filesystem->move( $src, "$rollback_dir/$rollback_subfolder/$slug", true );
			}
		}

		return $response;
	},
	15,
	2
);

add_filter(
	'upgrader_install_package_result',
	/**
	 * Move rollback folder to original location.
	 *
	 * @global WP_Filesystem_Base $wp_filesystem WordPress filesystem subclass.
	 *
	 * @param bool|WP_Error $result     Result from `WP_Upgrader::install_package()`.
	 * @param array         $hook_extra Array of data for plugin/theme being updated.
	 *
	 * @return bool|WP_Error
	 */
	function( $result, $hook_extra ) {
		global $wp_filesystem;

		/**
		 * WARNING:
		THIS IS JUST FOR TESTING!!!
		 */
		if ( defined( 'TEST_ROLLBACKS' ) && TEST_ROLLBACKS ) {
			$result = new WP_Error( 'test', 'test' );
		}

		if ( ! is_wp_error( $result ) ) {
			return $result;
		}

		// Exit early on plugin/theme installation.
		if ( isset( $hook_extra['action'] ) && 'install' === $hook_extra['action'] ) {
			return new WP_Error( 'extract_rollback_install', __( 'Rollback Update Failure exit for installation not update', 'rollback-update-failure' ) );
		}

		if ( isset( $hook_extra['plugin'] ) || isset( $hook_extra['theme'] ) ) {
			$rollback_dir       = $wp_filesystem->wp_content_dir() . 'upgrade/rollback/';
			$rollback_subfolder = isset( $hook_extra['plugin'] ) ? 'plugins' : 'themes';
			$slug               = isset( $hook_extra['plugin'] ) ? dirname( $hook_extra['plugin'] ) : $hook_extra['theme'];
			$destination_dir    = isset( $hook_extra['plugin'] ) ? $wp_filesystem->wp_plugins_dir() . '/' . $slug : get_theme_root() . '/' . $slug;
			$src                = "$rollback_dir/$rollback_subfolder/$slug";

			if ( $wp_filesystem->is_dir( $src ) ) {

				// Cleanup.
				if ( $wp_filesystem->is_dir( $destination_dir ) ) {
					$wp_filesystem->delete( $destination_dir, true );
				}

				// Move it.
				$wp_filesystem->move( $src, $destination_dir, true );
			}
		}
		return $result;
	},
	15,
	2
);
