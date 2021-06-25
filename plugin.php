<?php
/**
 * A pluin containg tweaks for the WordPress Updater/Upgrader
 * for plugins and themes.
 *
 * Plugin Name: Updater tweaks POC
 *
 * This plugin aims to address the following:
 *      - Plugin & Theme Rollbacks, safe-unzip
 *      - DB upgrades & downgrades
 *      - Plugin Dependencies
 *
 * @package WordPress
 */

/**
 * Include files.
 */
require_once __DIR__ . '/rollbacks.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/dependencies.php';
