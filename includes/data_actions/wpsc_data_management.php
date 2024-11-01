<?php 

function sanitize_db_field($value) {
	        return '"' . preg_replace( "/".PHP_EOL."/", "\n", addslashes($value) ) . '"';
	}


/**
 * generate backup mysql
 *
 * @since 1.0
 * @return void
 */


function wpsc_generate_mysql_backup(){	
	global $wpdb , $wps_backup_setup , $table_prefix;

	if ( ! wp_verify_nonce( $_POST['wpsc_generate_mysqli_backup_nonce'], 'wpsc_generate_mysqli_backup_nonce' ) ) {
		wp_die( __( 'Nonce verification failed.', 'wps' ), __( 'Error', 'wps' ), array( 'response' => 403 ) );
	}


	$sqlScript = "";

	$sqlScript  
 			.= "-- WPS backup {$wps_backup_setup->version}" . PHP_EOL
        	. '-- MySQL dump' . PHP_EOL
        	. "-- WP prefix:{$table_prefix}" . PHP_EOL
        	. '-- ' . date('Y-m-d H:i:s') . PHP_EOL . PHP_EOL;

	$sqlScript .= 'SET NAMES utf8;' . PHP_EOL;
	$sqlScript .= 'SET foreign_key_checks = 0;' . PHP_EOL . PHP_EOL;


	$tables = array();
	$sql = "SHOW TABLES";
	$results = $wpdb->get_results( $sql );

	foreach($results as $key => $value){
		foreach($value as $table => $name){
			$tables[] = $name;	
		}
	}
	
	foreach ($tables as $table) {
    
	    $sqlScript .= "\n\n" . "DROP TABLE IF EXISTS `" . $table . "`;". PHP_EOL . PHP_EOL ;

	    $sql = "SHOW CREATE TABLE $table";
		$results = $wpdb->get_results( $sql );
	    $sqlScript .= PHP_EOL . PHP_EOL  . $results[0]->{'Create Table'} . ";" . PHP_EOL . PHP_EOL ;

	    $sql = "SELECT * FROM $table";

		$results = $wpdb->get_results( 'DESCRIBE '.$table , ARRAY_A  );
		$columns = array();
    	foreach($results as $row) {
        	$columns[] = $row['Field'];
    	}
    	$columnCount = count($columns);

    	$results = $wpdb->get_results( $sql );
	    foreach($results as $result => $column_content ){
    		$sqlScript .= "INSERT INTO "."`". "$table" . "`" . " VALUES(";
				$i = 0;
				foreach($column_content as $key => $entry){
					if ( isset( $entry ) ) {
						$sqlScript .=  sanitize_db_field($entry) ;
            		} else {
                		$sqlScript .= '""';
            		}
            		if ($i < ($columnCount - 1)) {
                		$sqlScript .= ',';
            		}			
					$i++;
				}
			$sqlScript .= ");" . PHP_EOL ;
	    }
	}
	    
    $dirpath = WP_CONTENT_DIR.'/'.WPS_PLUGIN_DIR_STORAGE.'/'.WPS_BU_PLUGIN_FOLDER."/temp/database/local";
    WPS_file_util::delete_files($dirpath);
// return;
	if(!empty($sqlScript)){
		$dirpath = WP_CONTENT_DIR.'/'.WPS_PLUGIN_DIR_STORAGE;
	    WPS_file_util::create_dir($dirpath);
	    $dirpath = $dirpath.'/'.WPS_BU_PLUGIN_FOLDER;
	    WPS_file_util::create_dir($dirpath);
        $dirpath = $dirpath.'/temp';
        WPS_file_util::create_dir($dirpath);
        $dirpath = $dirpath.'/database';
        WPS_file_util::create_dir($dirpath);
        $dirpath = $dirpath.'/local';
        WPS_file_util::create_dir($dirpath);
	    //to do
	    	//ensure html silence is golden is added

	    foreach (scandir($dirpath) as $file) {
            $files[$file] = filemtime($dirpath . '/' . $file);
        }

        $count = 0;
      	foreach ( $files as $key => $file ){
            if ( strpos( $key, '_backup_' ) !== false ){
				@unlink( $dirpath . '/' . $key );
            }
        }
        
        $site_url = site_url('','https');
        $site_url = explode(".",$site_url)[0];
        $site_url = substr( $site_url , 8);

	    $backup_file_name = time() . "-" . $site_url. '_backup.sql';
	    $fileHandler = fopen($dirpath ."/". $backup_file_name, 'w+');
	    $number_of_lines = fwrite($fileHandler, $sqlScript);
	    fclose($fileHandler); 

       	if ( class_exists( 'ZipArchive' ) ) 
        {
            $zip = new ZipArchive();
            $archive = $zip->open($dirpath."/" . time() ."-". $site_url.'_backup.zip', ZipArchive::CREATE);
 			$pass = $zip->addFile($dirpath."/".$backup_file_name,$backup_file_name);
            $zip_created = $zip->close();
            @unlink( $dirpath . '/' . $backup_file_name );
            // $fileext = '.zip';
        } else {
            // $fileext = '.sql';
        }
	}

	//sucess / fail
 	if(!$zip_created){
    	$url =  admin_url( 'admin.php?page=wpsc_backup&tab=data_backup&wps_message=error' );
    } else {
    	$url =  admin_url( 'admin.php?page=wpsc_backup&tab=data_backup&wps_message=success_backup_create' );	
    }
    
	wp_safe_redirect( $url ); 
}

add_action('WPS_action_generate-mysql-backup','wpsc_generate_mysql_backup', 1);

//to do
	// merge this to use the class version 

function wpsc_restore_mysql_backup(){

	if ( ! wp_verify_nonce( $_POST['wpsc_restore_mysqli_backup_nonce'], 'wpsc_restore_mysqli_backup_nonce' ) ) {
		wp_die( __( 'Nonce verification failed.', 'wps' ), __( 'Error', 'wps' ), array( 'response' => 403 ) );
	}

	global $wpdb;
	global $wps_backup_setup;

	$backup_file="";
	$files = get_current_local_db_backup();
	foreach($files as $file){
 		$backup_file = $file;
	}

	$dirpath = WP_CONTENT_DIR . '/' . WPS_PLUGIN_DIR_STORAGE . '/' . WPS_BU_PLUGIN_FOLDER .'/temp/database/local'; 

    // var_dump( file_exists( $dirpath ) );

	$sql = '';
    $error = '';

    if (file_exists($dirpath)) {
    	$zip = new ZipArchive;
		$ready = $zip->open($dirpath . "/" . $backup_file );
        // var_dump($dirpath . "/" . $backup_file);
		$zip->extractTo($dirpath.'/');
		$zip->close();

		$sql_backup = explode('.',$backup_file);
		$sql_backup = $sql_backup[0].".sql";

		$lines = file($dirpath ."/". $sql_backup);
	   	foreach ($lines as $line) {
	   		if (substr($line, 0, 2) == '--' || $line == '') {
        		continue;
    		}
            $sql .= $line;
            if (substr(trim($line), - 1, 1) == ';') {
                $result = $wpdb->query( $sql );
                if (! $result) {
                	echo $wpdb->last_query;
                    $error .= $wpdb->last_error . PHP_EOL;
                }
                $sql = '';
            }
	   	}

	   if ($error) {
    		$response = array(
        		"type" => "error",
        		"message" => $error
    		);
        } else {
            $response = array(
                "type" => "success",
                "message" => "Database Restore Completed Successfully."
            );
        }

		$files = get_current_local_db_backup();
		foreach($files as $file){
			$file_parts = pathinfo($file);
			if($file_parts['extension'] == 'sql'){
		 		@unlink( $dirpath . '/' . $file );
			}
		}
    } else {
    	echo "File doesn't exist";
    }

    //to do
    	//improve errors check, complete as possible but log failures

    // if(!$error){
    	// $url =  admin_url( 'admin.php?page=wpsc_backup&tab=data_restore&wps_message=error' );
    // } else {
    	$url =  admin_url( 'admin.php?page=wpsc_backup&tab=data_restore&wps_message=success_backup_restore' );	
    // }
    
	wp_safe_redirect( $url ); 
}
add_action('WPS_action_restore-mysql-backup','wpsc_restore_mysql_backup', 1);



function get_current_local_db_backup(){
	$output = "";
	$filtered = array();
	$dirpath = WP_CONTENT_DIR.'/'.WPS_PLUGIN_DIR_STORAGE.'/'.WPS_BU_PLUGIN_FOLDER."/temp/database/local";
    // WPS_file_util::create_dir($dirpath);
    //to do
    	//ensure html silence is golden is added
    	// html page
    if( is_dir( $dirpath ) ){
        foreach (scandir($dirpath) as $file) {
            $files[$file] = filemtime($dirpath . '/' . $file);
        }
        foreach ( $files as $key => $file ){
            if ( strpos( $key, '_backup' ) !== false ){
            	$filtered[] = $key;
            }
        }
        return $filtered;
    }
    return false;
}
