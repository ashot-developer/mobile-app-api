<?php
/*
* Plugin Name: SVG Avatars Generator
* Plugin URI: https://svgavatars.com
* Description: SVG Avatars Generator lets your visitors create, save, and download their graphic avatars.
* Version: 1.7.3
* JavaScript and CSS main assets version: 1.7
* Author: DeeThemes
* Author URI: https://1.envato.market/c/1301577/275988/4415?u=https%3A%2F%2Fcodecanyon.net%2Fuser%2Fdeethemes
* Text Domain: svg-avatars-generator
* Domain Path: /languages
* Copyright © 2014-2021 | DeeThemes | https://svgavatars.com
*
* Prefixes used for the plugin: svgAvatars_ and SVGAVATARS_
*/

// If this file is called directly, abort.
if ( ! defined( "ABSPATH" ) ) {
	exit;
}

// plugin's constants
define( "SVGAVATARS_VERSION", "1.7.3" );
define( "SVGAVATARS_FILE", __FILE__ );
define( "SVGAVATARS_PATH", plugin_dir_path( __FILE__ ) );
define( "SVGAVATARS_PLUGIN_SLUG", plugin_basename( __FILE__ ) );
define( "SVGAVATARS_URL", plugin_dir_url( __FILE__ ) );
define( 'SVGAVATARS_THIS_HOST', parse_url( get_site_url() , PHP_URL_HOST) );

// load text domain
function svgAvatars_load_plugin_textdomain() {
	// make available for translation and for local avatars
	load_plugin_textdomain( "svg-avatars-generator", false, dirname( SVGAVATARS_PLUGIN_SLUG ) . "/languages" );
}
add_action( "init", "svgAvatars_load_plugin_textdomain" );

// check for mandatory params
function svgAvatars_check_mandatory_params() {
	// require minimum version of WordPress
	global $wp_version;
	if ( version_compare( $wp_version, "5.2", "<" ) ) {
		if( is_plugin_active( SVGAVATARS_PLUGIN_SLUG ) ) {
			deactivate_plugins( SVGAVATARS_PLUGIN_SLUG );
			wp_die(
				sprintf(
					"<strong>" .
					esc_html__( "SVG Avatars Generator plugin", "svg-avatars-generator" ) .
					"</strong> " .
					esc_html__( "requires WordPress 5.2 or higher, and has been deactivated! Please upgrade WordPress and try again.", "svg-avatars-generator") .
					"<br><br>" .
					esc_html__( "Back to", "svg-avatars-generator" ) .
					' <a href="%1$s">' .
					esc_html__( "WordPress Dashboard", "svg-avatars-generator" ) .
					"</a>.",
					esc_url( admin_url() )
				)
			);
		}
	}
	// check whether the WordPress file system API can be used
	if( "direct" === get_filesystem_method() ) {
		$creds = request_filesystem_credentials( content_url() . "/", "", false, false, array() );
		if ( ! WP_Filesystem( $creds ) ) {
			return false;
		}
	} else {
		// don't have direct write access.
		if( is_plugin_active( SVGAVATARS_PLUGIN_SLUG ) ) {
			deactivate_plugins( SVGAVATARS_PLUGIN_SLUG );
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
	}
}
add_action( "admin_init", "svgAvatars_check_mandatory_params" );

// activation of the plugin
require_once SVGAVATARS_PATH . 'includes/svg-avatars-activation.php';

// admin part of the plugin
require_once SVGAVATARS_PATH . 'includes/svg-avatars-admin.php';

// styles and scripts
require_once SVGAVATARS_PATH . 'includes/svg-avatars-assets.php';

// validation and sanitization
require_once SVGAVATARS_PATH . 'includes/svg-avatars-validation.php';

// saving ready avatars on a server
require_once SVGAVATARS_PATH . 'includes/svg-avatars-save-avatar.php';

// send created avatar to https://secure.gravatar.com
require_once SVGAVATARS_PATH . 'includes/svg-avatars-send-gravatar.php';

// for using local avatars instead of Gravatars
require_once SVGAVATARS_PATH . 'includes/svg-avatars-local-avatars.php';

// BuddyPress deeper integration
require_once SVGAVATARS_PATH . 'includes/svg-avatars-buddypress.php';


// actions on init
function svgAvatars_init() {
	$options = get_option( "svgAvatars_options" );

	// use local avatar files
	if ( $options["integration"] === "local" ) {
		$local_avatar = new svgAvatarsLocalAvatarsCLass();
	}

	// whether to show the avatar generator on the user profile page
	if ( is_admin() && $options['show_in_backend'] === "true" ) {
		add_action( 'profile_personal_options', 'svgAvatars_show_in_backend' );
	}
}
add_action( "wp_loaded", "svgAvatars_init" );

// shortcode function for SVG Avatars Generator
function svgAvatars_generator_shortcode ( $atts = null, $content = null ) {
	return '<div id="svgAvatars"></div>';
}
add_shortcode( "svgAvatars", "svgAvatars_generator_shortcode" );
add_shortcode( "svgavatars", "svgAvatars_generator_shortcode" );

?>