<?php 


?>


<form action="" method="post">
	<input type="hidden" name="WPS-action" value="restore-mysql-backup"/>
	<?php wp_nonce_field( 'wpsc_restore_mysqli_backup_nonce', 'wpsc_restore_mysqli_backup_nonce' ); ?>
	<?php submit_button('Restore') ?>
</form>


<?php
echo "<h3>Local backup:</h3> ";
$files = get_current_local_db_backup();
if($files){
	foreach($files as $file){
		echo $file . "<br/>";
	}
} else {
	echo "You have not made any backups yet.";
}


