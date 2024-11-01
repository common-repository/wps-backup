<?php

/**
 * Instructions for the plugin
 *
 * 
 *
 * @package     WPS Admin Menus
 * @subpackage  
 * @copyright   Copyright (c) 2018, WPS Complete
 * @license     http://opensource.org/license/gpl-2.1.php GNU Public License
 * @since       1.0
 */



if ( ! defined( 'ABSPATH' ) ) { exit; }



echo '<div class="wrap">';

// if ( class_exists( 'ZipArchive' ) ){
// 	echo 'ZipArchive exists';
// }

?>

<h3>Index</h3>

<ol>
  <li>Local Backup database</li>
  <li>Local Restore database</li>
  <li>Create Remote backups</li>
  <li>Restore Remote database</li>
</ol>

<h3>Content </h3>

<ol>
	<li>Local Backup database</li>
	<ul>
  		<li>Select backup to perform a backup of your website database at that moment Data tab -> Backup tab -> Select Backup.</li>
  	</ul>
  	<li>Local Restore database</li>
  	<ul>
  		<li>Restore your website to the last backup you made Data tab -> Restore tab -> Select Restore.</li>
  	</ul>
  	<li>Create Remote backups</li>
  	<ul>
  		<li>Connect your api key to wps complete</li>
  		<li>Setup your preferences</li>
  		<li>Allow for the database to be performed</li>
  	</ul>
  	<li>Restore Remote database</li>
  	<ul>
  		<li>Select your backups from the remote backups tab</li>
  		<li>Your chosen backup is then downloaded</li>
  		<li>Select restore</li>
  	</ul>
</ol>






<?php
echo '</div>';