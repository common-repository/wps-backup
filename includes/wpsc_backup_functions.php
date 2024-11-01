<?php


/**
 * Funcitons for backup features
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

/*
* request backups fir site url
*/

function wpsc_request_url_backups(){
	// open the tab
	if( current_user_can('manage_options')  ){
		$url =  admin_url( 'admin.php?page=wpsc_backup&tab=data_remote_files&url='.$_POST['url_required'] );
	} else {
		$url =  admin_url( 'admin.php?page=wpsc_backup&tab=data_remote_files&WPS_message=error' );
	}
	wp_safe_redirect( $url ); 
}
add_action('WPS_action_get-url-database-backups','wpsc_request_url_backups');




function wpsc_restore_cloud_database_backup(){
	// open the tab
	if( current_user_can('manage_options')  ){
		$url =  admin_url( 'admin.php?page=wpsc_backup&tab=data_restore_cloud_db&url='.$_POST['original_url'].'&required_backup='.$_POST['backup_file_required'] );
	} else {
		$url =  admin_url( 'admin.php?page=wpsc_backup&tab=data_remote_files&WPS_message=error' );
	}
	wp_safe_redirect( $url ); 
}
add_action('WPS_action_restore-url-database-backups','wpsc_restore_cloud_database_backup');

// Delete backup api

function wpsc_delete_backup_api_key(){
	if ( ! wp_verify_nonce( $_POST['wpsc_delete_key_api'], 'wpsc_delete_key_api' ) ) {
		wp_die( __( 'Nonce verification failed.', 'wps' ), __( 'Error', 'wps' ), array( 'response' => 403 ) );
	}
	if( current_user_can('manage_options') && $result = WPS_api::delete_api_backup_key() ){
		$url =  admin_url( 'admin.php?page=WPS&tab=api_setup&wps_message=api_deleted' );
	} else {
		$url =  admin_url( 'admin.php?page=WPS&tab=api_setup&wps_message=error' );
	}
	wp_safe_redirect( $url ); 
}

add_action('WPS_action_delete-backup-api-key','wpsc_delete_backup_api_key');

/*
*
* Download database file from cloud
*
*/

function wpsc_retreve_db_file(){
	global $wpdb, $table_prefix;

	$description = 'wpscomplete_base_api';

	$keys = $wpdb->get_row( 
				$wpdb->prepare( "
					SELECT key_id, user_id, permissions, consumer_key, consumer_secret, nonces
					FROM {$wpdb->prefix}WPS_api_keys
					WHERE description = '%s'
			", $description ), ARRAY_A );


	$url = 'https://wpscomplete.com/wp-json/wpscb/v1/retrieve_db_backup_file/';

	$site_url = site_url();
	$site_url = substr($site_url, 8);

	//start this process and request the file to be processed

	$response = wp_remote_post( $url, array(
	    'method'      => 'POST',
	    'timeout'     => 45,
	    'redirection' => 5,
	    'httpversion' => '1.0',
	    'blocking'    => true,
	    'prefix'	=>  $table_prefix,
	    // 'headers'     => array(	
	    // 	'accept'	=> 'application/json', // The API returns JSON				
	    // 	'content-type'  => 'application/binary', // Set content type to binary			
	    // ),
	    'headers' 	  => array(),
	    'body'        => array(
	        'consumer_key' 			=> $keys['consumer_key'],
	        'consumer_secret' 		=> $keys['consumer_secret'],
	        'backup_site_url' 		=> $_POST['url'],
	        'required_backup_file' 	=> $_POST['required_backup'],
	        'current_site_url'		=> $site_url,
	        'file_part'				=> 0,
	        'prefix'				=> $table_prefix,
	    ), 
	    'cookies'     => array()
	    )
	);

	if ( is_wp_error( $response ) ) {
	    $error_message = $response->get_error_message();
	    echo "Something went wrong: $error_message";
	} else {
		// print_r($response);
		// print_r($response['body']);
	    // return $response;
	}

	$file_info = base64_decode( json_decode( $response['body'] ) ); 
	$file_info = json_decode( $file_info );
	$file_size = $file_info[0];
	settype($file_size, 'int');

	// prepare folders
	$data_management = new WPS_data_management;
	$dirpath = $data_management->setup_directory_for_restoring();
	$file_name = $_GET['required_backup'];
	$download_file = $dirpath."/".$file_name;

	//max time limit for preparation
	$modify_time_allowed = 20;
	$file_modified = 0;

	for($i = 0; $i < $modify_time_allowed; $i++){

		$response = wp_remote_post( $url, array(
		    'method'      => 'POST',
		    'timeout'     => 45,
		    'redirection' => 5,
		    'httpversion' => '1.0',
		    'blocking'    => true,
		    // 'headers'     => array(	
		    // 	'accept'	=> 'application/json', // The API returns JSON				
		    // 	'content-type'  => 'application/binary', // Set content type to binary			
		    // ),
		    'headers' 	  => array(),
		    'body'        => array(
		        'consumer_key' 			=> $keys['consumer_key'],
		        'consumer_secret' 		=> $keys['consumer_secret'],
		        'backup_site_url' 		=> $_POST['url'],
		        'required_backup_file' 	=> $_POST['required_backup'],
		        'current_site_url'		=> $site_url,
		        'file_part'				=> 1,
		    ), 
		    'cookies'     => array()
		    )
		);

		if ( is_wp_error( $response ) ) {
		    $error_message = $response->get_error_message();
		    echo "Something went wrong: $error_message";
		} else {
			// print_r($response['body']);
		    // return $response;
		}

		$file_info = base64_decode( json_decode( $response['body'] ) ); 
		$file_info = json_decode( $file_info );
		if( $file_modified == $file_info[1]){
			break;
		}

		$file_modified = $file_info[1];
		sleep ( 1 );

		//to do break and returna message if not completed in time

		// if($i == ( $modify_time_allowed - 1 ) )){
		// 	//this has been going on too long
		// 	echo "there has been an error processing the download";
		// 	return;
		// }


		// update the files size before we download
		$file_size = $file_info[0];
		settype($file_size, 'int');
	}


	//manage the downlaod of the file
		//broken into 10000 byte sections

	$db_file = fopen( $download_file ,'w' );
	$file_divide = 10000;

	$i = 2;
	$divides = intdiv($file_size, $file_divide);
	for( $i = 2; $i <= ( $divides + 2 ); $i++){
		$response = wp_remote_post( $url, array(
		    'method'      => 'POST',
		    'timeout'     => 45,
		    'redirection' => 5,
		    'httpversion' => '1.0',
		    'blocking'    => true,
		    // 'headers'     => array(	
		    // 	'accept'	=> 'application/json', // The API returns JSON				
		    // 	'content-type'  => 'application/binary', // Set content type to binary			
		    // ),
		    'headers' 	  => array(),
		    'body'        => array(
		        'consumer_key' 			=> $keys['consumer_key'],
		        'consumer_secret' 		=> $keys['consumer_secret'],
		        'backup_site_url' 		=> $_POST['url'],
		        'required_backup_file' 	=> $_POST['required_backup'],
		        'current_site_url'		=> $site_url,
		        'file_part'				=> $i,
		    ), 
		    'cookies'     => array()
		    )
		);

		$file_data = base64_decode( $response['body'] );
		$bytes = fwrite( $db_file , $file_data );
	}
	$close = fclose( $db_file );



	if( $file_size != filesize( $download_file ) ){
		return 'file_hasnt_completed_download_correctly';
	}
	$download_complete = $data_management->check_backup_db_file_exists( 0 , $dirpath , $file_name );
		if( ! $download_complete ){
		    $url =  admin_url( 'admin.php?page=wpsc_backup&tab=data_remote&wps_message=error' );
	    } else {
	    	$url =  admin_url( 'admin.php?page=wpsc_backup&tab=restore_data_restore_cloud_db&wps_message=success_backup_download' );	
	    }
		wp_safe_redirect( $url ); 
}

add_action('WPS_action_restore-cloud-backup','wpsc_retreve_db_file');

/*
*
* Restore downloaded file
*
*/

function wpsc_restore_completed_db_download(){

	// prepare the file

	$data_management = new WPS_data_management;
	$dirpath = WPS_data_management::get_directory_for_restoring();
	var_dump($dirpath);
	$files = WPS_data_management::get_file_type_from_directory( $dirpath , 'zip');
	$file_name = $files[0];
	$unzip = $data_management->unzip_file( $dirpath , $file_name );

	if( $unzip ){
		$unzipped_file = $data_management->fetch_sql_file( $dirpath );
		//to do
			//log error but maybe not fail the restoration as that may corrupt the site
		$restore = $data_management->wpsc_restore_db_to_site( $dirpath , $unzipped_file );	


		//to do
			// redirect after restoration
			// do not declare error if one has failed

		//to do 
			// fix the two issues on the restore of the db

		// WPS_file_util::delete_files($dirpath); 
	 	// if( $restore['type'] == 'error' ){
	 		// var_dump("error: ",$restore);
	    	// $url =  admin_url( 'admin.php?page=wpsc_backup&tab=data_remote&wps_message=error' );
	    // } else {
	    	$url =  admin_url( 'admin.php?page=wpsc_backup&tab=data_remote&wps_message=success_backup_restore' );	
	    // }
		wp_safe_redirect( $url ); 

		//to do
			//delete the backup file after completed restoration
			// this maybe fine to do now the proceedure is working well....
	}
}
add_action('WPS_action_restore-downloaded-cloud-backup','wpsc_restore_completed_db_download');


//generate local backup

function wpsc_generate_local_plugin_backup( ){
	// nonce
	// wpsc_generate_local_plugin_backup_nonce

	$data_management = new WPS_data_management;
	$backup = $data_management->wpsc_generate_plugins_backup( $_POST['destination'] );

	//redirect after generation
		// false - message try again
		// true redirect to self with success message

	// if( current_user_can('manage_options')  ){
	// 	$url =  admin_url( 'admin.php?page=wpsc_backup&tab=data_remote_files&url='.$_POST['url_required'] );
	// } else {
		$url =  admin_url( 'admin.php?page=wpsc_backup&tab=plugins_backup&wps_message=plugins_backed_up' );
		
	// }
	wp_safe_redirect( $url ); 
}
add_action('WPS_action_generate-local-plugin-backup','wpsc_generate_local_plugin_backup');


function wpsc_restore_local_plugin_backup( ){
	// nonce
	// wpsc_generate_local_plugin_backup_nonce

	$src = WP_CONTENT_DIR.'/plugins';
	$dirpath = WP_CONTENT_DIR.'/wpsc_complete/backups/old_plugins';
	WPS_data_management::recurse_copy($src,$dirpath);
	WPS_data_management::delete_files( $src , $src );

	//extract the zip to a restore directory
	$sourcePath = WP_CONTENT_DIR.'/'.WPS_PLUGIN_DIR_STORAGE.'/'.WPS_BU_PLUGIN_FOLDER."/temp/plugins/local/";
	$file_name = WPS_data_management::get_current_local_backup( 'plugins' );

	$restorePath = WP_CONTENT_DIR.'/'.WPS_PLUGIN_DIR_STORAGE.'/'.WPS_BU_PLUGIN_FOLDER."/temp/plugins/local/restore/";
	WPS_file_util::delete_files( $restorePath );
	WPS_file_util::create_dir($restorePath);
	$unzip = WPS_data_management::unzip_file( $sourcePath , $file_name[0] , $restorePath );

	//copy the extracted folder into the plugins directory
	$copySource = WP_CONTENT_DIR.'/'.WPS_PLUGIN_DIR_STORAGE.'/'.WPS_BU_PLUGIN_FOLDER."/temp/plugins/local/restore/original/";
	$resource = scandir( $copySource );
	foreach( $resource as $dir ){
		if( substr($dir,0,1) != '.' ){
			WPS_file_util::recurse_copy( $copySource.$dir , $src."/".$dir );
		}
	}

	// if( current_user_can('manage_options')  ){
		$url =  admin_url( 'admin.php?page=wpsc_backup&tab=plugins_restore&wps_message=plugins_restored' );
	// } else {
		// $url =  admin_url( 'admin.php?page=wpsc_backup&tab=data_remote_files&WPS_message=error' );
	// }
	wp_safe_redirect( $url ); 
}
add_action('WPS_action_restore-local-plugin-backup','wpsc_restore_local_plugin_backup');



