<?php 


/**
 * List the backups available for the site url selected
 *
 * Manage basic setups that are important
 *
 * @package     WPS Backup
 * @subpackage  
 * @copyright   Copyright (c) 2018, WPS Complete
 * @license     http://opensource.org/license/gpl-2.1.php GNU Public License
 * @since       1.0
 */


$site_manual_backup = site_url( '', 'https' );
$site_manual_backup = substr($site_manual_backup,8);

?>

<h3>Site backup files:</h3>
<?php if( $site_manual_backup == $_GET['url'] ){ ?>
	<form action="" method="post">
	    <input type="hidden" name="WPS-action" value="test-new-api-key"/>
	    <?php // wp_nonce_field( 'wpsc_generate_mysqli_backup_nonce', 'wpsc_generate_mysqli_backup_nonce' ); ?>
	    <?php submit_button('Manual Backup') ?>
	</form>
<?php } ?>

<?php


function load_remote_site_files(){
	global $wpdb;

	$description = 'wpscomplete_base_api';

	$keys = $wpdb->get_row( 
				$wpdb->prepare( "
					SELECT key_id, user_id, permissions, consumer_key, consumer_secret, nonces
					FROM {$wpdb->prefix}WPS_api_keys
					WHERE description = '%s'
			", $description ), ARRAY_A );


	$url = 'https://wpscomplete.com/wp-json/wpscb/v1/client_site_backups/';

	// api request to the server
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
	        'consumer_key' 		=> $keys['consumer_key'],
	        'consumer_secret' 	=> $keys['consumer_secret'],
	        'url' 				=> $_GET['url'],
	    ), 
	    'cookies'     => array()
	    )
	);

	// return the values
	if ( is_wp_error( $response ) ) {
	    $error_message = $response->get_error_message();
	    echo "Something went wrong: $error_message";
	} else {
	    return $response;
	}


}

$remote_storage = load_remote_site_files();
$decode = json_decode( $remote_storage['body'] , true );
$remote_storage = json_decode( $decode , true );

foreach($remote_storage as $key => $value){
	if( substr($value,-4) == '.zip' ) {
		?>

		<p>
		<form action="" method="post">
			<input type="hidden" name="WPS-action" value="restore-url-database-backups"/>
			<input type="hidden" name="original_url" value="<?php echo $_GET['url'] ?>"/>
			<input type="hidden" name="backup_file_required" value="<?php echo $value ?>"/>
			 <?php // wp_nonce_field( 'wpsc_generate_mysqli_backup_nonce', 'wpsc_generate_mysqli_backup_nonce' ); ?>
    		<input type="submit" value="Restore to this site" >
		</form>
		<?php echo $value ?>
		&nbsp;&nbsp;&nbsp;: 
		<?php 
		$time = substr( $value,0,10);
		settype( $time , 'int' );
		echo date('Y-m-d H:i:s', $time );  
		?>
		</p>
		<?php 
	}		
}
