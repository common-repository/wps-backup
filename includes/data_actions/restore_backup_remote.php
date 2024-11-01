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

?>

<h3>Your WPS Cloud Account Backups:</h3>



<?php


//load the lits available REST API

function load_remote_storage(){
	global $wpdb;
	$description = 'wpscomplete_base_api';

	$keys = $wpdb->get_row( 
				$wpdb->prepare( "
					SELECT key_id, user_id, permissions, consumer_key, consumer_secret, nonces
					FROM {$wpdb->prefix}WPS_api_keys
					WHERE description = '%s'
			", $description ), ARRAY_A );


	$url = 'https://wpscomplete.com/wp-json/wpscb/v1/client_avaliable_backups/';

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

$remote_storage = load_remote_storage();
$remote_storage = json_decode( $remote_storage['body'] );
$fail_response = "You have not setup your WPS cloud backups yet.";
if( $remote_storage ){
	if( count( $remote_storage ) <= 0 ){
		echo $fail_response;
	} else {
		foreach($remote_storage as $key => $value){
			?>
			<p>
			<form action="" method="post">
				<input type="hidden" name="WPS-action" value="get-url-database-backups"/>
				<input type="hidden" name="url_required" value="<?php echo $value->url ?>"/>
				 <?php // wp_nonce_field( 'wpsc_generate_mysqli_backup_nonce', 'wpsc_generate_mysqli_backup_nonce' ); ?>
	    		<input type="submit" value="View backups" >
			</form>
			<?php echo $value->url ?>
			</p>
			<?php 
		}
	}
} else {
	echo $fail_response;
}



