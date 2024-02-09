<?php
/******************************************************************************************
 * @name:       temp-avatar-save.php - part of jQuery script for creating vector avatars
 * @version:    1.7
 * @URL:        https://svgavatars.com
 * @copyright:  (c) 2014-2021 DeeThemes (https://codecanyon.net/user/DeeThemes)
 * @licenses:   https://codecanyon.net/licenses/regular
 *              https://codecanyon.net/licenses/extended
 *
 * Save temporary avatars on a server in the 'temp-avatars' directory
*******************************************************************************************/

// This script doesn't load WordPress, so the i18n and WP Filesystem API cannot be used.

// require validation functions
require_once( 'validate-avatar-data.php' );

// getting and validating file name and image data from POST
// returned $file will be an array with name and type
$file = svgAvatars_validate_filename( $_POST['filename'] );
if ( $file['name'] === 'invalid' ) {
	die( "Received file name doesn't match required pattern." );
}
if ( $file['type'] === 'invalid' ) {
	die( 'Received file type is not PNG or SVG.' );
}
$valid_data =  svgAvatars_validate_imagedata( $_POST['imgdata'], $file['type'] );

if ( $file['type'] === 'png' ) {
	// check that validated image data is not empty
	if ( $valid_data !== false ) {
		$dir = '../temp-avatars/';
		if ( is_dir( $dir ) && is_writable( $dir ) ) {
			$valid_data = base64_decode( $valid_data );
			file_put_contents( $dir . $file['name'] . '.png', $valid_data );
			die( 'saved' );
		} else {
			die( "The 'temp-avatars' directory does not exist or is not writable." );
		}
	} else {
		die( 'Received file PNG or SVG data is not valid.' );
	}
} elseif ( $file['type'] === 'svg' ) {
	// check that validated code is SVG
	if ( strpos( $valid_data, '<svg xmlns="http://www.w3.org/2000/svg" version="1.1"' ) !== false && strrpos($valid_data, '</svg>', -6) !== false ) {
		$valid_data = stripcslashes( $valid_data );
		$dir = '../temp-avatars/';
		if ( is_dir( $dir ) && is_writable( $dir ) ) {
			file_put_contents( $dir . $file['name'] . '.svg', $valid_data );
			die( 'saved' );
		} else {
			die( "The 'temp-avatars' directory does not exist or is not writable." );
		}
	} else {
		die( 'Received file PNG or SVG data is not valid.' );
	}
} else {
	die( 'Received file type is not PNG or SVG.' );
}

