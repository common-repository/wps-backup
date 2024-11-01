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

if ( ! defined( 'ABSPATH' ) ) { exit; }

?>




<form action="" method="post">
	<input type="hidden" name="WPS-action" value="restore-local-plugin-backup"/>
	<?php wp_nonce_field( 'wpsc_restore_mysqli_backup_nonce', 'wpsc_restore_mysqli_backup_nonce' ); ?>
	<?php submit_button('Restore Plugins') ?>
</form>


<?php
echo "<h3>Local backup:</h3> ";
$dm = new WPS_data_management;
$files = $dm->get_current_local_backup( 'plugins' );

if($files){
	foreach($files as $file){
		echo $file . "<br/>";
	}
} else {
	echo "You have not made any backups yet.";
}


