<?php
/*
Plugin Name: Exif-Remove
Plugin URI: http://www.mynakedgirlfriend.de/wordpress/exif-remove/
Description: Automatically remove exif data after uploading JPG files
Author: Thomas Schulte
Version: 1.3
Author URI: http://www.mynakedgirlfriend.de

Copyright (C) 2010 Thomas Schulte

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

$version = get_option('ts_exifremoveupload_version');
if($version == '') {
	add_option('ts_exifremoveupload_version','1.3','Version of the plugin Exif-Remove','yes');
}

  
/* actions */
add_action( 'admin_menu', 'ts_exifremoveupload_options_page' ); // add option page
if(get_option('ts_exifremoveupload_yesno') == 'yes') {
	add_action('wp_handle_upload', 'ts_exifremoveupload_clean'); // apply our modifications
}

  
/* add option page */
function ts_exifremoveupload_options_page() {
	if(function_exists('add_options_page')){
		add_options_page('Exif-Remove','Exif-Remove',8,'exif-remove-upload','ts_exifremoveupload_options');
	}
}


/* the real option page */
function ts_exifremoveupload_options(){
	if(isset($_POST['ts_exifremoveupload_options_update'])) {
		$yesno = $_POST['yesno'];
    
		if($yesno == 'yes') {
			update_option('ts_exifremoveupload_yesno','yes');
		}else {
			update_option('ts_exifremoveupload_yesno','no');
		}

		echo('<div id="message" class="updated fade"><p><strong>Your options were saved.</strong></p></div>');
	}

	$yesno = get_option('ts_exifremoveupload_yesno');
  
	echo('<div class="wrap">');
	echo('<form method="post" accept-charset="utf-8">');
    
	echo('<h2>Exif-Remove Options</h2>');
	echo('<p>This plugin does exactly what it says: it will remove Exif data from uploaded images (JPG only). Nothing more, nothing less.</p>');
	echo('<p>Your file will be modified, there will not be a copy or backup with the original content.</p>');
	echo('<p>Set the option \'Clean\' to no if you don\'t want to remove Exif information, this way you shouldn\'t deactivate the plugin in case you don\'t want to clean your images for a while.</p>');
	echo('<br>');
	echo('
		<h3>Settings</h3>
		<table class="form-table">
			<tr>
				<td>Clean:&nbsp;</td>
				<td>
					<select name="yesno" id="yesno">  
						<option value="no" label="no"'); if ($yesno == 'no') echo(' selected=selected'); echo('>no</option>
						<option value="yes" label="yes"'); if ($yesno == 'yes') echo(' selected=selected'); echo('>yes</option>
					</select>
				</td>
			</tr>
		</table>');  
  
	echo('
		<p class="submit">
			<input type="hidden" name="action" value="update" />
			<input type="submit" name="ts_exifremoveupload_options_update" value="Update Options &raquo;" />
		</p>');

	echo('</form>');
	echo('</div>');
}



function ts_exifremoveupload_clean($array) {
	// $array contains file, url, type

	if ($array['type'] == 'image/jpeg' || $array['type'] == 'image/jpg') {
		$ch = curl_init();

		$data = array('inputfile' => "@" . $array['file']);

		curl_setopt($ch, CURLOPT_URL, 'http://www.exif-remove.de/service.php');
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec($ch);
		curl_close($ch);

		$fp = fopen($array['file'], "wb");
		fwrite($fp, $result);
		fclose($fp);
	}

	return $array;
}

?>

