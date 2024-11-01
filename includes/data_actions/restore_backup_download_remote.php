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

<h3>Restore downloaded file:</h3>

<p>The following will happen:</p>
<ul>
	<li>Restore the backup database.</li>
		<ol>
			<li>If you where logged in as a different user on the last database backup, you will become logged out after the restore.</li>
			<li>It is likely you will be logged out unless the administrator credentials are the same and the backup has been recently created.</li>
			<ol>Migrations from another site
				<li>When migrating from another site, ensure you have an administrators account from this site. You will be locked out from your site otherwise.</li>
				<li>If this is a migration from another site, it is likely you need to update the plugins, themes, and uploads to match the original site.</li>
			</ol>
		</ol>
</ul>


<form action="" method="post" >
	<input type="hidden" name="WPS-action" value="restore-downloaded-cloud-backup"/>
	<!-- <input type="hidden" name="url" value="<?php echo $_GET['url'] ?>"/> -->
	<!-- <input type="hidden" name="required_backup" value="<?php echo $_GET['required_backup'] ?>"/> -->
	<!-- <input type="hidden" name="client_url" value="<?php echo $site_url ?>" > -->
	<?php wp_nonce_field( 'wpsc_restore_download_backup_cloud', 'wpsc_restore_download_backup_cloud' ); ?>
	<?php submit_button("Restore"); ?>
</form>