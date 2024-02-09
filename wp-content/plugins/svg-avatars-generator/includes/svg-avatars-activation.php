<?php
// If this file is called directly, abort.
if ( ! defined( "ABSPATH" ) ) {
	exit;
}

// factory defaults
global $svgAvatars_default_options;
$svgAvatars_default_options = array(
	"downloading_name" => "myAvatar", // the default file name for downloaded avatars
	"show_gender" => "both", // show either "both" genders or "boysonly" or "girlsonly"
	"delta_sat" => "10", // the step of saturation color change in HSV (HSB) mode (10% by default)
	"delta_val" => "6", // the step of value (brightness) color change in HSV (HSB) mode (6% by default)
	"save_format" => "png", // must be exactly "png" or "svg" for saving on a server
	"save_size" => "300", // the dimensions for avatar saved on a server (pixels)
	"png_one_download_size" => "200", // the dimensions of first option PNG file when download by user (pixels)
	"png_two_download_size" => "400", // the dimensions of second option PNG file when download by user (pixels)
	"png_ios_download_size" => "500", // the dimensions of PNG file when download by user on iOS devices (pixels)
	"png_win8tablet_download_size" => "400", // the dimensions of PNG file when download by user on Win8 phones and tablets (pixels)
	"svg_download_size" => "400", // the conditional dimensions of SVG file when download by user (pixels)
	"gravatar_size" => "200", // the dimensions of PNG file for Gravatar service (pixels)
	"hide_png_one_download" => "false", // true will disable download PNG with first dimensions
	"hide_png_two_download" => "false", // true will disable download PNG with second dimensions
	"hide_svg_download" => "true", // true will disable download SVG option
	"hide_svg_download_on_Android" => "true", // true will disable download SVG option on Android devices (not useful)
	"hide_gravatar" => "false", // true will disable the possibility to install created avatar as gravatar
	"color_theme" => "dark", // must be exactly "light", "dark" or "custom"
	"hide_share" => "false", // true will disable share option
	"share_image_size" => "400", // the dimensions of PNG file for share with Social networks (pixels)
	"pinterest" => "true", // false will disable Pinterest share option
	"twitter" => "true", // false will disable Twitter share option
	"share_link" => "", // custom share URL
	"share_title" => "", // custom share title
	"share_description"  => "", // custom share description
	"share_credit" => "Created on YourSite.com", // share credit on an avatar
	"use_on_spec_page" => "false",  // if false, load styles and scripts on all pages and posts
	"page_slug" => "",  // if empty, load styles and scripts on all pages and posts
	"remove_my_credit" => "false", // disable credit to svgAvatars.com
	"reset_to_default_options" => "false", // reset options to these defaults
	"delete_settings_on_uninstall" => "false", // delete all the options on uninstall of the plugin
	"welcome_slogan_1" => "Please create your profile avatar", // the default slogan on starting screen for logged in users
	"welcome_slogan_2" => "תיצור אווטר המתאים לך ביותר", // the default slogan on starting screen for not logged in users when there is any integration
	"welcome_slogan_3" => "Please create and download your avatar", // the default slogan on starting screen for not logged in users when there is no integration, when Save button is not available
	"integration" => "none", //the name of integrated plugin or "none" or "custom"
	"custom_heading" => "כל הכבוד!", // when the integration is set to "custom"
	"show_in_backend" => "false", //whether to show the avatars generator on a user profile page
	"zooming" => "not_changed", //on first init, avatar can be set bigger or smaller
	"debug" => "false", //whether to show messages in JS console on client side
	"add_buddypress_profile_subnav" => "false" //whether add 'create-svg-avatars' tab in 'profile' page in BuddyPress
	/**
	* not used since 1.6:
	* "welcome_slogan" => "Welcome to AVG Avatars Generator", //(welcome_slogan_1, _2, and _3 instead)
	* "hide_save" => "true", //("integration" instead)
	* "userpro" => "false" //("integration" controls)
	*/
);

// set default options when plugin is activated
function svgAvatars_generator_activation() {
	// check whether the WordPress file system API can be used
	if( "direct" === get_filesystem_method() ) {
		$creds = request_filesystem_credentials( content_url() . "/", "", false, false, array() );
		if ( ! WP_Filesystem( $creds ) ) {
			return false;
		}
	} else {
		// don't have direct write access.
		wp_die(
			sprintf(
				"<strong> " .
				esc_html__( "SVG Avatars Generator plugin", "svg-avatars-generator" ) .
				"</strong> " .
				esc_html__( "requires the direct file system access on your server, and has been deactivated! Unfortunately, the plugin cannot be used. Please contact the plugin's author.", "svg-avatars-generator") .
				"<br><br>" .
				esc_html__( "Back to", "svg-avatars-generator" ) .
				' <a href="%1$s">' .
				esc_html__( "WordPress Dashboard", "svg-avatars-generator" ) .
				"</a>.",
				esc_url( admin_url() )
			)
		);
	}

	// saving new installed version number
	update_option( "svgAvatars_version", SVGAVATARS_VERSION );

	global $svgAvatars_default_options;

	$saved = get_option( "svgAvatars_options" );
	if ( $saved === false ) {
		$saved = array();
	}

	// to prevent overwriting users values
	$result = array_merge( $svgAvatars_default_options, $saved );

	update_option( "svgAvatars_options", $result );

	// write the result JavaScript file into the wp-content/plugins/svg-avatars-generator/data/js/
	svgAvatars_save_result_file( $result );

	// creating directory for saving avatars: /wp-content/uploads/svg-avatars
	$uploads_dir_params = wp_get_upload_dir();
	$uploads_dir = $uploads_dir_params["basedir"] . "/svg-avatars";
	global $wp_filesystem;
	if ( ! $wp_filesystem->is_dir( $uploads_dir ) ) {
		wp_mkdir_p( $uploads_dir );
	}
}
register_activation_hook( SVGAVATARS_FILE, "svgAvatars_generator_activation" );

// check the version number and activate if necessary
function svgAvatars_check_version() {
	if( current_user_can( "manage_options" ) ) {
		// it's absolutely necessary to use "!=" instead of "!=="
		if ( version_compare( get_option( "svgAvatars_version" ), SVGAVATARS_VERSION, "!=" ) ) {
			svgAvatars_generator_activation();
		}
	}
}
add_action( "admin_init", "svgAvatars_check_version" );
