<?php
/**
 * A pluin containg tweaks for the WordPress Updater/Upgrader
 * for plugins and themes.
 *
 * Plugin Name: Updater tweaks POC
 *
 * This plugin aims to address the following:
 *      - Plugin & Theme Rollbacks, safe-unzip
 *      - DB upgrades
 *      - Plugin Dependencies
 *
 * @package WordPress
 */

/**
 * Include files.
 */
require_once __DIR__ . '/includes/safe-unzip-rollback/rollbacks.php';
require_once __DIR__ . '/includes/db/db.php';
require_once __DIR__ . '/includes/dependencies/dependencies.php';
