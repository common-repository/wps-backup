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

echo '<div class="wrap">';

$_GET['tab'] = isset( $_GET['tab'] ) ? $_GET['tab'] : 'data_backup';


/*
*  Build the settings page funciton that runs everything
* 
* 
*/


function wpsc_bu_settings_page() {
   global $pagenow;

        switch( $_GET['tab'] ){
            case 'wpsc_bu_plugins':
            case 'plugins_backup':
                    wpsc_bu_admin_tabs('wpsc_bu_plugins');
                    wpsc_backup_sub_tabs( 'plugins_backup' );
                break;
            case 'plugins_restore':
                    wpsc_bu_admin_tabs('wpsc_bu_plugins');
                    wpsc_backup_sub_tabs( 'plugins_restore' );
            break;
             case 'plugins_remote':
                    wpsc_bu_admin_tabs('wpsc_bu_plugins');
                    wpsc_backup_sub_tabs( 'plugins_remote' );
            break;

            case 'admin_menu':
                    wpsc_bu_admin_tabs($_GET['tab']);
                break;
            case 'admin_toolbar':
                    wpsc_bu_admin_tabs($_GET['tab']);
                break;

            case 'data_backup':
                    wpsc_bu_admin_tabs('wpsc_database');
                    wpsc_backup_sub_tabs( 'data_backup' );
                break;
            case 'data_restore':
                    wpsc_bu_admin_tabs('wpsc_database');
                    wpsc_backup_sub_tabs( 'data_restore' );
                break;
            case 'data_remote':
                    wpsc_bu_admin_tabs('wpsc_database');
                    wpsc_backup_sub_tabs( 'data_remote' );
                break;
                
            case 'data_remote_files':
                    wpsc_bu_admin_tabs('wpsc_database');
                    wpsc_backup_sub_tabs( 'data_remote_files' );
                break;
             case 'data_restore_cloud_db':
                    wpsc_bu_admin_tabs('wpsc_database');
                    wpsc_backup_sub_tabs( 'data_restore_cloud_db' );
                break;
            case 'restore_data_restore_cloud_db':
                    wpsc_bu_admin_tabs('wpsc_database');
                    wpsc_backup_sub_tabs( 'restore_data_restore_cloud_db' );
                break;
                
                
                
         default:
            wpsc_bu_admin_tabs($_GET['tab']);
            wpsc_backup_sub_tabs( $_GET['tab'] );
       }
}

//call the setting page to be built
wpsc_bu_settings_page(); 


/*
*  Build nav tabs
* 
* 
*/


function wpsc_bu_admin_tabs( $current = 'wpsc_database' ) {
    $tabs = array( 	'wpsc_database' => 'Database',
                    'wpsc_bu_plugins' => 'Plugins',
                    'wpsc_instructions' => 'Instructions'
    			);
    echo '<div id="icon-themes" class="icon32"><br></div>';
    echo '<h2 class="nav-tab-wrapper">';
    foreach( $tabs as $tab => $name ){
        $class = ( $tab == $current ) ? ' nav-tab-active' : "";
        $url_include = "";
		  echo "<a class='nav-tab$class' href='?page=wpsc_backup&tab=$tab" . $url_include . "'>$name</a>";
    }
    echo '</h2>';
}




//subnav used for tasks
function wpsc_backup_sub_tabs( $current = 'data_backup' ){

	// var_dump($_POST, $_GET);
	// echo "<pre>".print_r($current,true)."</pre>";
	// $current = $current == null ? 'data_backup': $current;
    $url_include = "";
    $tabs = array();

    if($current == 'wpsc_database'){
        $current = 'data_backup';
    }
    if($current == 'wpsc_instructions'){
        return;
    }

    switch( $current ){

        //database
        case 'data_actions':
                $tabs['data_backup'] = 'Local Backup';
                $tabs['data_restore'] = 'Local Restore';
                $tabs['data_remote'] = 'Cloud Backups';
                // $tabs['data_setup'] = 'Setup';
            break;
        case 'data_remote_files':
                $tabs['data_backup'] = 'Local Backup';
                $tabs['data_restore'] = 'Local Restore';
                $tabs['data_remote_files'] = 'Cloud File Backups: '.$_GET['url'];
                $tabs['data_remote'] = 'Cloud Backups';
                // $tabs['data_setup'] = 'Setup';
            break;
        case 'data_restore_cloud_db':
                $tabs['data_backup'] = 'Local Backup';
                $tabs['data_restore'] = 'Local Restore';
                $tabs['data_restore_cloud_db'] = 'Restoration Progress: Download';
                $tabs['data_remote'] = 'Cloud Backups';
                // $tabs['data_setup'] = 'Setup';
            break;
        case 'restore_data_restore_cloud_db':
                $tabs['data_backup'] = 'Local Backup';
                $tabs['data_restore'] = 'Local Restore';
                $tabs['restore_data_restore_cloud_db'] = 'Restoration Progress: Restore';
                $tabs['data_remote'] = 'Cloud Backups';
                // $tabs['data_setup'] = 'Setup';
            break;

        //plugins
            case 'plugins_backup':
                $tabs['plugins_backup'] = 'Local Backup';
                $tabs['plugins_restore'] = 'Local Restore';
                $tabs['plugins_remote'] = 'Cloud Backups';
                // $tabs['data_setup'] = 'Setup';
            break;
            case 'plugins_restore':
                $tabs['plugins_backup'] = 'Local Backup';
                $tabs['plugins_restore'] = 'Local Restore';
                $tabs['plugins_remote'] = 'Cloud Backups';
                // $tabs['data_setup'] = 'Setup';
            break;
             case 'plugins_remote':
                $tabs['plugins_backup'] = 'Local Backup';
                $tabs['plugins_restore'] = 'Local Restore';
                $tabs['plugins_remote'] = 'Cloud Backups';
                // $tabs['data_setup'] = 'Setup';
            break;
        case 'plugins_remote_files':
                $tabs['plugins_backup'] = 'Local Backup';
                $tabs['plugins_restore'] = 'Local Restore';
                $tabs['plugins_remote_files'] = 'Cloud File Backups: '.$_GET['url'];
                $tabs['plugins_remote'] = 'Cloud Backups';
                // $tabs['data_setup'] = 'Setup';
            break;
            //downlaod
        case 'plugins_restore_cloud_db':
                $tabs['plugins_backup'] = 'Local Backup';
                $tabs['plugins_restore'] = 'Local Restore';
                $tabs['plugins_restore_cloud_plugins'] = 'Restoration Progress: Download';
                $tabs['plugins_remote'] = 'Cloud Backups';
                // $tabs['data_setup'] = 'Setup';
            break;
            //restore
        case 'restore_plugins_restore_cloud_db':
                $tabs['plugins_backup'] = 'Local Backup';
                $tabs['plugins_restore'] = 'Local Restore';
                $tabs['restore_plugins_restore_cloud_plugins'] = 'Restoration Progress: Restore';
                $tabs['plugins_remote'] = 'Cloud Backups';
                // $tabs['data_setup'] = 'Setup';
            break;

        //others

        case 'wpsc_instructions':
            break; 
        default:
                $tabs['data_backup'] = 'Local Backup';
                $tabs['data_restore'] = 'Local Restore';
                $tabs['data_remote'] = 'Cloud Backups';
                // $tabs['data_setup'] = 'Setup';
    }

    echo '<div id="icon-themes" class="icon32"><br></div>';
    echo '<h2 class="nav-tab-wrapper">';
    foreach( $tabs as $tab => $name ){
        $class = ( $tab == $current ) ? ' nav-tab-active' : "";
        echo "<a class='nav-tab$class' href='?page=wpsc_backup&tab=$tab" . $url_include . "'>$name</a>";
    }
    echo '</h2>';

}

// wpsc_backup_sub_tabs( $_GET['tab'] );


/*
*  Select content to display
* 
* 
*/
// var_dump($_GET);

switch ( $_GET['tab'] ) {
    case 'data_backup':
    case 'wpsc_database':
       		include "data_actions/generate_backup.php";
        break;
    case 'data_setup':
            include "data_actions/setup_backup.php";
        break;
    case 'data_restore_cloud_db':
            include "data_actions/restore_cloud_db_backup.php";
        break;
    case 'data_remote_files':
    	   include "data_actions/restore_backup_files_list.php";
        break;
    case 'data_restore':
           include "data_actions/restore_backup.php";
        break;
    case 'data_remote':
           include "data_actions/restore_backup_remote.php";
        break;
    case 'wpsc_instructions':
           include "wpsc_instructions_tab.php";
        break;
    case 'restore_data_restore_cloud_db':
           include "data_actions/restore_backup_download_remote.php";
        break;
        
        
    
     
    //plugins
    case 'wpsc_bu_plugins':
    case 'plugins_backup':
            include "data_actions/plugins_backup.php";
        break;
    case 'plugins_restore_cloud_db':
            include "data_actions/restore_cloud_plugins_backup.php";
        break;
    case 'plugins_remote_files':
           include "data_actions/plugins_restore_backup_files_list.php";
        break;
    case 'plugins_restore':
           include "data_actions/plugins_restore_backup.php";
        break;
    case 'plugins_remote':
           include "data_actions/plugins_restore_backup_remote.php";
        break;


       default:
          // include "wpsc_menu_tab.php";


}


/*
* Close page content
* 
* 
*/


echo "</div>";






