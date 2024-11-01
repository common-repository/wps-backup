<?php


/**
 * Setup the plugin
 *
 * Manage basic setups that are important
 *
 * @package     WPS Backup
 * @subpackage  
 * @copyright   Copyright (c) 2018, WPS Complete
 * @license     http://opensource.org/license/gpl-2.1.php GNU Public License
 * @since       1.0
 */


if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Load admin stylesheets
 *
 * @param string $hook Page hook.
 *
 * @return void
 */

function wpsc_bu_admin_styles( $hook ) {
	if($hook != 'wps-complete_page_wpsc_backup') {
        return;
    }
	wp_enqueue_style( 'wpsc_backup_styles',  WPS_AM_PLUGIN_URL . 'includes/css/wpsc_backup_styles.css', array(), WPS_AM_PLUGIN_VERSION );
}
add_action( 'admin_enqueue_scripts', 'wpsc_bu_admin_styles' );


/**
 * Load the page
 *
 * @param string $hook Page hook.
 *
 * @return void
 */

include 'wpsc_backup_page.php';



