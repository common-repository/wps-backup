<?php 


class WPS_data_management {



	/**
	 * generate backup plugins
	 *
	 * @since 1.0
	 * @return void
	 */

	public function wpsc_generate_plugins_backup( $destination ){
		$ready = false;
        $site_url = substr( explode(".", site_url('','https') )[0] , 8);

		

		// $dirpath = $dirpath.'/plugins';
	 // 	$ready = WPS_file_util::create_dir($dirpath);
	 	// var_dump($dirpath);

	 	// return;
	 	if($destination == 'local'){

	 		$dirpath = $this->setup_directory_for_writing('plugins','local');

		 	WPS_file_util::delete_files($dirpath."/");
		 	$ready = WPS_file_util::create_dir($dirpath);

		 	$source = WP_CONTENT_DIR.'/plugins/';
		 	self::recurse_copy( $source , $dirpath."/original");

		 	$zip_name = time() ."-". $site_url.'_plugins_backup.zip';

		 	// zip them to the original location
		 	$sourcePath = WP_CONTENT_DIR.'/'.WPS_PLUGIN_DIR_STORAGE.'/'.WPS_BU_PLUGIN_FOLDER."/temp/plugins/local/original";
		 	$outZipPath = WP_CONTENT_DIR.'/'.WPS_PLUGIN_DIR_STORAGE.'/'.WPS_BU_PLUGIN_FOLDER."/temp/plugins/local/".$zip_name;
			WPS_file_util::zipDir( $sourcePath , $outZipPath );

			$ready = true;

	 	}
	 	if( $destination == 'remote' ){

	 		$dirpath = $this->setup_directory_for_writing('plugins','remote');

	 		// $dirpath = $dirpath.'/remote';
		 	WPS_file_util::delete_files($dirpath."/");
		 	$ready = WPS_file_util::create_dir($dirpath);

		 	$source = WP_CONTENT_DIR.'/plugins/';
		 	self::recurse_copy( $source , $dirpath."/original");

		 	$zip_name = time() ."-". $site_url.'_plugins_backup.zip';

		 	// zip them to the original location
		 	$sourcePath = WP_CONTENT_DIR.'/'.WPS_PLUGIN_DIR_STORAGE.'/'.WPS_BU_PLUGIN_FOLDER."/temp/plugins/remote/original";
		 	$outZipPath = WP_CONTENT_DIR.'/'.WPS_PLUGIN_DIR_STORAGE.'/'.WPS_BU_PLUGIN_FOLDER."/temp/plugins/remote/".$zip_name;
			WPS_file_util::zipDir( $sourcePath , $outZipPath );

			$ready = true;

	 	}

	    return $ready;
	}


	// get current backup file from dirory options

	public static function get_current_local_backup( $backup_type ){
		$output = "";
		$filtered = array();
		$dirpath = WP_CONTENT_DIR.'/'.WPS_PLUGIN_DIR_STORAGE.'/'.WPS_BU_PLUGIN_FOLDER."/temp/".$backup_type."/local";
	    // WPS_file_util::create_dir($dirpath);
	    //to do
	    	//ensure html silence is golden is added
	    	// html page
	    if( is_dir( $dirpath ) ){
	        foreach (scandir($dirpath) as $file) {
	            $files[$file] = filemtime($dirpath . '/' . $file);
	        }
	        foreach ( $files as $key => $file ){
	            if ( strpos( $key, $backup_type .'_backup' ) !== false ){
	            	$filtered[] = $key;
	            }
	        }
	        return $filtered;
	    }
	    return false;
	}

 	/**
     * Copy directory content
     *
     * @since 1.0
     * @return void
     */


    static function recurse_copy($src,$dst) {

    	// check if the parent is the same as the new directory, this causes feedback loops
    		//this is not used at the moment
		// $parent =  explode('/', $src )[ ( count( explode('/', $src ) ) - 1)] ;
		// $child_parent = explode('/', $dst )[ ( count( explode('/', $dst ) ) - 2)];

        $dir = opendir($src); 
        @mkdir($dst);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                	// if($child_parent != $parent){ //stop from copying into itself
                   		self::recurse_copy($src . '/' . $file, $dst . '/' . $file);
               		// }
                }
                else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }


	/**
	 * generate backup mysql
	 *
	 * @since 1.0
	 * @return void
	 */

	public function wpsc_generate_mysql_backup(){
		global $wpdb,$wps_backup_setup,$table_prefix;

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
		return $sqlScript;
	}

	/**
	 * Setup the temp directory
	 *
	 * @since 1.0
	 * @return void
	 */


	public function setup_directory_for_writing($backup_dir = 'database' , $destination = 'remote'){

		$dirpath = WP_CONTENT_DIR.'/'.WPS_PLUGIN_DIR_STORAGE;
	    WPS_file_util::create_dir($dirpath);
	    $dirpath = $dirpath.'/'.WPS_BU_PLUGIN_FOLDER;
	    WPS_file_util::create_dir($dirpath);
	    $dirpath = $dirpath.'/temp';
	    $ready = WPS_file_util::create_dir($dirpath);
	    $dirpath = $dirpath.'/'.$backup_dir;
	    $ready = WPS_file_util::create_dir($dirpath);
	    $dirpath = $dirpath.'/'.$destination;
	    $ready = WPS_file_util::create_dir($dirpath);
	    if($ready){
	    	return $dirpath;	
	    }

	    return false;
	}

	


	/**
	 * Setup the restore directory
	 *
	 * @since 1.0
	 * @return void
	 */

	public function setup_directory_for_restoring(){

		//delete previous folder
		$target = WP_CONTENT_DIR.'/'.WPS_PLUGIN_DIR_STORAGE.'/'.WPS_BU_PLUGIN_FOLDER.'/temp/restore/';		
		if($target){
			WPS_file_util::delete_files($target); 
		}


		if( $dirpath = $this->setup_directory_for_writing() ){
			$dirpath = $dirpath.'/restore';
	    	$ready = WPS_file_util::create_dir($dirpath);
		}
	    if($ready){
	    	return $dirpath;	
	    }
	    return false;
	}


	/**
	 * Just returning the directory nicely
	 *
	 * @since 1.0
	 * @return void
	 */

	static function get_directory_for_restoring(){
		$target = WP_CONTENT_DIR.'/'.WPS_PLUGIN_DIR_STORAGE.'/'.WPS_BU_PLUGIN_FOLDER.'/temp/database/remote/restore/';		
	    return $target;	
	}



	/**
	 * Just returning the directory nicely
	 *
	 * @since 1.0
	 * @return void
	 */

	static function get_file_type_from_directory( $dirpath , $file_ext ){
		$files = array();
		$content = scandir( $dirpath );
		$ext_length = strlen( $file_ext );
		settype($ext_length,'int');
		foreach( $content as $value ){
			// echo "File: ".$value. "<br/>";
			// echo "File: ".$value. "<br/>";

			if( 'file' == filetype($dirpath."/".$value) ){
				if( substr( $value , -$ext_length ) == $file_ext ){
					$files[] = $value;
				}
			}
		}
		return $files;
	}


	public function process_file_download_transfer( $directory , $data , $filename ) {
    	$file_data = base64_decode( $data );
		$create_file = $directory."/".$filename;
		$db_file = fopen( $create_file ,'w' );
		$bytes = fwrite( $db_file , $file_data );
		$close = fclose( $db_file );
		return $data;
    }



  //   public function process_part_file_download_transfer( $directory , $data , $filename , $part ) {
  //   	$file_data = base64_decode( $data );
		// $create_file = $directory."/".$filename;
		// $db_file = fopen( $create_file ,'w' );
		// $bytes = fwrite( $db_file , $file_data );
		// $close = fclose( $db_file );
		// return $data;
  //   }



	/**
	 * Clear the temp directory
	 *
	 * @since 1.0
	 * @return void
	 */


	public function clearup_temp_directory( $dirpath ){
		foreach ( scandir( $dirpath ) as $file) {
            $files[$file] = filemtime($dirpath . '/' . $file);
        }
        $count = 0;
      	foreach ( $files as $key => $file ){
            if ( strpos( $key, '_backup' ) !== false ){
				@unlink( $dirpath . '/' . $key );
            }
        } 
	}

	/**
	 * Write the file
	 *
	 * @since 1.0
	 * @return void
	 */

	public function write_file_to_directory( $dirpath , $sqlScript ){

        $site_url = substr( explode(".", site_url('','https') )[0]  , 8);

	    $backup_file_name = time() . "-" . $site_url. '_backup.sql';
	    $fileHandler = fopen($dirpath ."/". $backup_file_name, 'w+');
	    $number_of_lines = fwrite($fileHandler, $sqlScript);
	    fclose($fileHandler); 
	    return $backup_file_name;
	}


	/**
	 * Write zip and delete original - only used for single files to be zipped for db backup
	 *
	 * @since 1.0
	 * @return void
	 */

	public function zip_backup_directory( $dirpath , $backup_file_name ){

		$site_url = substr( explode(".", site_url('','https') )[0] , 8);

	    if ( class_exists( 'ZipArchive' ) ) {
	    	$zip_name = time() ."-". $site_url.'_backup.zip';	
            $zip = new ZipArchive();
            $archive = $zip->open($dirpath."/" . $zip_name, ZipArchive::CREATE);
 			$pass = $zip->addFile($dirpath."/".$backup_file_name , $backup_file_name);
            $zip_created = $zip->close();
            @unlink( $dirpath . '/' . $backup_file_name );
            
            if($zip_created){
            	return $zip_name;
            }
            // $fileext = '.zip';
        } else {
            // $fileext = '.sql';
            // to do
            	//alternative if 'ZipArchive' is not present
        }
        return false;
	}


	/**
	 * Check the database is downloaded and ready to be restored
	 * @param $file_location = full directoty inclugin the name
	 * @param $required_size = expected size of file - if 0 then we are not checking for this
	 * @since 1.0
	 * @return void
	 */

	public function check_backup_db_file_exists( $required_size = 0 , $dirpath, $filename ) {
		if( is_file( $dirpath.'/'. $filename ) ){
			if($required_size != 0 ){
				if($required_size === filesize( $dirpath .'/'. $filename ) ){
					return true;
				} else {
					return false;
				}
			}
			return true;
		}
		return false;
	}


	/**
	 * Unzip our directory
	 * 					optionl $dst	
	 * @param 
	 * @param 
	 * @since 1.0
	 * @return true if file was unzipped
	 */


	public function unzip_file( $dirpath , $filename , $dst ){

		if( $dst == '' ){
			$output_dst = $dirpath;
		} else {
			$output_dst = $dst;
		}

    	$zip = new ZipArchive;
		$section = $zip->open($dirpath . $filename );
		$extract = $zip->extractTo( $output_dst );
		$extract =  $zip->close();
		return $extract;
	}


	/**
	 * Advanced unzip - prevent over write, alternative destination
	 * @param 
	 * @param 
	 * @since 1.0
	 * @return true if file was unzipped
	 */

	// public function unzip_file_advanced( $dirpath , $filename , $dst ){
 //    	$zip = new ZipArchive;
	// 	$section = $zip->open($dirpath . $filename );
	// 	$extract = $zip->extractTo($dst);
	// 	$extract =  $zip->close();
	// 	return $extract;
	// }


	/**
	 * Get the sql backup file
	 * @param 
	 * @param 
	 * @since 1.0
	 * @return true if file was unzipped
	 */


	public function fetch_sql_file( $dirpath ){
		foreach (scandir($dirpath) as $file) {
            if( explode('.',$file)[1] == 'sql'){
            	return $file;
            }
        }
	}




	public function wpsc_restore_db_to_site( $dirpath , $sql_backup_name ){
		global $wpdb;


		$tables = array();
		$sql = "SHOW TABLES";
		$results = $wpdb->get_results( $sql );

		foreach($results as $key => $value){
			foreach($value as $table => $name){
				$tables[] = $name;	
			}
		}
		$pre_import_tables = array();
		foreach ($tables as $table) {
			$pre_import_tables[] = $table;
		}


		$sql = '';
	    $error = '';

		$lines = file($dirpath . $sql_backup_name);
		$imported_tables = array();
	   	foreach ($lines as $line) {
	   		if (substr($line, 0, 2) == '--' || $line == '') {
        		continue;
    		}
            $sql .= $line;

            //prepare the total tables imported
            if ( substr( trim( $line ), 0, 20 ) == 'DROP TABLE IF EXISTS' ){
            	$table = substr( trim( $line ), 22 );
            	
            	$length = strlen( $table );
            	$length = $length - 2;
            	
            	$imported_tables[] = substr( trim( $table ), 0, $length );
            	
            } 

            if ( substr( trim( $line ), - 1, 1) == ';') {
                $result = $wpdb->query( $sql );
                if (! $result) {
                	if( $wpdb->last_error != " " ){
                    	$error .= $wpdb->last_error . PHP_EOL;
                	}
                }
                $sql = '';
            }
	   	}

	   	//to do
    		//improve errors check, complete as possible but log failures


		   // if ($error) {
	    // 		$response = array(
	    //     		"type" => "error",
	    //     		"message" => $error
	    // 		);
	    //     } else {
            $response = array(
                "type" => "success",
                "message" => "Database Restore Completed Successfully."
            );

        // }


		self::tables_not_in_restore_db( $pre_import_tables, $imported_tables );

        return $response;
	}


	private function tables_not_in_restore_db( $pre_import_tables, $imported_tables ){
		global $wpdb;
		$completed = false;
		foreach(  $pre_import_tables as $table ){
			if( ! in_array( $table ,  $imported_tables ) ){
		    	$sql =  "DROP TABLE IF EXISTS `" . $table . "`;";
				$result = $wpdb->query( $sql );
			}
		}
	}


	 /* 
     * PHP delete function that deals with directories recursively, deletes the directory as well,
     * This is a modified version for the ensuring that the wps backups are not removed
     */
    static function delete_files( $dirname , $parent = '' ) {
        $parent_path = '';
        $current_path = '';

        //stop the parent being deleted if it passed when called
        if( $parent != '' ){
            $parent_path = pathInfo( $parent )['dirname'];
            $current_path = pathInfo( $dirname )['dirname'];
        }

        $do_not_delete = false;
        $directory_array = explode('/', $dirname );
        if( in_array ( 'wps-complete', $directory_array ) || in_array ( 'wps-backup', $directory_array ) ){
        	$do_not_delete = true;
    	}

        if ( is_dir( $dirname ) ){

            $dir_handle = opendir($dirname);
                if (!$dir_handle || $do_not_delete){
                    return false;
                } 

                while($file = readdir($dir_handle)) {
                    if ($file != "." && $file != "..") {
                        if (!is_dir($dirname."/".$file)){
                            unlink($dirname."/".$file);
                        } else {
                            self::delete_files($dirname.'/'.$file , '');
                        }
                    }
                }
            closedir($dir_handle);

            //options to avoid deleting the parent
            if( $parent == '' ){
                    rmdir($dirname);
            }
            if( $parent == '' ){
                if( $parent_path != $current_path){
                    rmdir($dirname);
                }
            }
            return true;
        }
    }

}


