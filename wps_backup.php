<?php

/*
Plugin Name: WPS Backup
Plugin URI: https://wpscomplete.com/wps-backup/
Description: Backup migrate your WordPress websites locally and with our cloud API
Author: WPScomplete.com
Version: 0.6.5
Author URI: https://wpscomplete.com
*/


if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( !defined( 'WPS_BU_PLUGIN_DIR' ) ) {
	define( 'WPS_BU_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}
if ( !defined( 'WPS_BU_PLUGIN_URL' ) ) {
	define( 'WPS_BU_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( !defined( 'WPS_BU_PLUGIN_FILE' ) ) {
	define( 'WPS_BU_PLUGIN_FILE', __FILE__ );
}
if ( !defined( 'WPS_BU_PLUGIN_VERSION' ) ) {
	define( 'WPS_BU_PLUGIN_VERSION', '1.0' );
}
if ( !defined( 'WPS_BU_PLUGIN_FOLDER' ) ) {
	define( 'WPS_BU_PLUGIN_FOLDER', 'backups' );
}

class WPS_backup {

	public function __construct() {

		global $wps_backup_setup;
		if (!isset($wps_backup_setup) || !is_object($wps_backup_setup)) {
			$wps_backup_setup = new \stdClass();
		}
		$wps_backup_setup->version = WPS_BU_PLUGIN_VERSION;


		add_action( 'admin_notices', array( $this, 'global_note' ) );
	}

	function global_note() {
		if ( ! is_plugin_active( 'wps-complete/wps_complete.php' ) ) {
			?>
            <div id="message" class="error">
                <p><?php _e( 'Please install and active <a href="https://wordpress.org/plugins/wps-complete/advanced/">WPS Complete</a> to use WPS Backup plugin.', 'wpsc_am' ); ?></p>
            </div>
			<?php
		
			if ( is_plugin_active( 'wps-backup/wps_backup.php' ) ) {
				deactivate_plugins( 'wps-backup/wps_backup.php' );
				unset( $_GET['activate'] );
			}
		}
	}
}

$wps_backup = new WPS_backup;




function wpsc_backup_setup(){
	require( WPS_BU_PLUGIN_DIR . 'includes/wpsc_backup_setup.php' );
}

//********************************************
//includes
//********************************************

require( WPS_BU_PLUGIN_DIR . 'includes/data_actions/wpsc_data_management.php' );
require( WPS_BU_PLUGIN_DIR . 'includes/data_actions/wpsc_class_data_management.php' );
require( WPS_BU_PLUGIN_DIR . 'includes/wpsc_backup_functions.php' );






