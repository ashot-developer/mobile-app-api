<?php
// If this file is called directly, or not uninstall, abort.
if ( ! defined( "ABSPATH" ) && ! defined( "WP_UNINSTALL_PLUGIN" ) ) {
	exit();
}

$svgAvatars_options_uninstall = get_option( "svgAvatars_options" );
if ( "true" === $svgAvatars_options_uninstall["delete_settings_on_uninstall"] ) {
	// delete plugin's options from the database
	delete_option( "svgAvatars_version" );
	delete_option( "svgAvatars_options" );
	delete_option( "svgAvatars_dynamic_string" );
}
?>