<?php
/**
* IMPORTANT:
* Any messages inside the wp_die() function are deliberately deprived of the possibility of
* internationalization, since support is provided by the plugin author only in English.
*/

// If this file is called directly, abort.
if ( ! defined( "ABSPATH" ) ) {
	exit;
}

// require IXR - The Incutio XML-RPC Library, which is a part of WordPress
require_once ABSPATH . WPINC . '/class-IXR.php';

// require validation functions
require_once( SVGAVATARS_PATH . "data/php/validate-avatar-data.php" );

// we need two different handlers for one function "svgAvatars_send_gravatar"
function svgAvatars_send_gravatar_not_logged_in_user() {
	// check for security nonce
	check_ajax_referer( "svgAvatars_in_AJAX" );

	svgAvatars_send_gravatar();
}
add_action( "wp_ajax_nopriv_svgAvatars_send_gravatar", "svgAvatars_send_gravatar_not_logged_in_user" );

// we need two different handlers for one function "svgAvatars_send_gravatar"
function svgAvatars_send_gravatar_logged_in_user() {
	// check for security nonce
	check_ajax_referer( "svgAvatars_in_AJAX" );

	svgAvatars_send_gravatar();
}
add_action( "wp_ajax_svgAvatars_send_gravatar", "svgAvatars_send_gravatar_logged_in_user" );

// send created avatar to secure.gravatar.com
function svgAvatars_send_gravatar() {
	if ( ! class_exists( "IXR_Client" ) ) {
		wp_die( "IXR_Client class doesn't exist. Something is wrong with WordPress installation." );
	}

	if ( ! isset( $_POST['imgdata'] ) ) {
		wp_die( 'Received PNG file data is empty.' );
	}

	if ( ! isset( $_POST['datastring1'] ) || empty( $_POST['datastring1'] ) ) {
		wp_die( 'gravatar_email_fail' );
	}
	$email = sanitize_email( $_POST['datastring1'] );
	$hash = md5( strtolower( trim( $email ) ) );

	if ( ! isset( $_POST['datastring2'] ) || empty( $_POST['datastring2'] ) ) {
		wp_die( 'gravatar_password_fail' );
	}
	$password = $_POST['datastring2'];

	if ( isset( $_POST['rating'] ) && in_array( $_POST['rating'], array("0", "1", "2", "3"), true ) ) {
		$rating = $_POST['rating'];
	} else {
		wp_die( 'Received Gravatar rating value is out of required range and/or invalid' );
	}

	// validating PNG image data (since Gravatar doesn't accept SVG)
	$valid_data = svgAvatars_validate_imagedata( $_POST['imgdata'], 'png' );
	if ( $valid_data == false ) {
		wp_die( 'Received PNG data is not valid.' );
	}

	// create the XML-RPC request
	$request = new IXR_Client( "secure.gravatar.com", "/xmlrpc?user=" . $hash );

	// create and call the first query for saving gravatar
	$params = array(
		"data" => $valid_data,
		"rating" => $rating,
		"password" => $password
	);
	$request->query("grav.saveData", $params);
	if ( $request->isError() ) {
		wp_die( trim( 'gravatar_faultcode' . htmlentities( $request->getErrorCode() ) ) );
	}

	// create and call the second query for using as default gravatar
	$params = array(
		"userimage" => $request->getResponse(),
		"addresses" => array( $email ),
		"password" => $password
	);
	$request->query("grav.useUserimage", $params);
	if ( $request->isError() ) {
		wp_die( trim( 'gravatar_faultcode' . htmlentities( $request->getErrorCode() ) ) );
	}

	// all is fine
	wp_die( "gravatar_success" );
}