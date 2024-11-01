<?php


?>

<form action="" method="post">
	<input type="hidden" name="WPS-action" value="generate-mysql-backup"/>
	<?php wp_nonce_field( 'wpsc_generate_mysqli_backup_nonce', 'wpsc_generate_mysqli_backup_nonce' ); ?>
	<?php submit_button('Generate') ?>
</form>

<?php 

//load current backup 
	//get the file name in the backup directory
	echo "<h3>Last local backup:</h3>";	
	$files = get_current_local_db_backup();
	if($files){
		foreach($files as $file){

			echo $file . "<br/>";
		}
	} else {
		echo "You haven't any backups yet." . "<br/>";
	}


