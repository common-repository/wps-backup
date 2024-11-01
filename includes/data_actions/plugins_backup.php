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

<form action="" method="post">
	<input type="hidden" name="WPS-action" value="generate-local-plugin-backup"/>
	<input type="hidden" name="destination" value="local"/>
	<?php wp_nonce_field( 'wpsc_generate_local_plugin_backup_nonce', 'wpsc_generate_local_plugin_backup_nonce' ); ?>
	<?php submit_button('Generate') ?>
</form>

<?php 

echo "<h3>Last local backup:</h3>";	
$dm = new WPS_data_management;
$files = $dm->get_current_local_backup( 'plugins' );

if($files){
	foreach($files as $file){
		echo $file . "<br/>";
	}
} else {
	echo "You have not made any backups yet.";
}


//restore plugins

	// open plugin zip folder

	// copy each folder accross

	//do not copy 
		//wps-complete
		//wps-backup

	// make a copy of the original plugins?
		// maybe user may lose a plugin they didnt intend to delete?

	//add link to delete the old plugins
		//if the folder is present. 





