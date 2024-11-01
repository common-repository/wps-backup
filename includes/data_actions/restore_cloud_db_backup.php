<?php 


/**
 * Page for restoration of the database pulled from the cloud wps server
 *
 * @package     WPS Complete
 * @subpackage  WPS Backup
 * @copyright   Copyright (c) 2018, WPS Complete
 * @license     http://opensource.org/license/gpl-2.1.php GNU Public License
 * @since       1.0
 */

?>

<h3>Restore cloud backup file: <?php echo $_GET['required_backup']?></h3>

<p>The following will happen:</p>
<ul>
	<li>Download the selected backup file.</li>
		<ul>
			<li>The time to do this can vary depending on the file size.</li>
			<li>If you are migrating from another site, the file will be prepared before download. This can require addtional loading time.</li>
			<li>Once downloaded, this page will reload with the final restore page.</li>
		</ul>
</ul>

<form action="" method="post" >
	<input type="hidden" name="WPS-action" value="restore-cloud-backup"/>
	<input type="hidden" name="url" value="<?php echo $_GET['url'] ?>"/>
	<input type="hidden" name="required_backup" value="<?php echo $_GET['required_backup'] ?>"/>
	<!-- <input type="hidden" name="client_url" value="<?php echo $site_url ?>" > -->
	<?php wp_nonce_field( 'wpsc_restore_backup_cloud', 'wpsc_restore_backup_cloud' ); ?>
	<?php submit_button("Download"); ?>
</form>