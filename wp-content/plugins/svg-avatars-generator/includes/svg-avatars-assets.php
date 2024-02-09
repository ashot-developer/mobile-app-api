<?php
// If this file is called directly, abort.
if ( ! defined( "ABSPATH" ) ) {
	exit;
}

//register and enqueue styles and scripts
function svgAvatars_register_main_assets() {
	wp_register_style(
		"svgAvatars-roboto-font",
		"//fonts.googleapis.com/css?family=Roboto+Condensed:400,300,700|Roboto:400,300,500,700&subset=latin,cyrillic-ext,cyrillic,latin-ext",
		false
	);
	wp_register_style(
		"svgAvatars-spectrum-css",
		SVGAVATARS_URL . "data/css/spectrum.css",
		false,
		SVGAVATARS_VERSION
	);
	wp_register_style(
		"svgAvatars-styles",
		SVGAVATARS_URL . "data/css/svgavatars.css",
		false,
		SVGAVATARS_VERSION
	);
	wp_register_style(
		"svgAvatars-custom-colors",
		SVGAVATARS_URL . "data/css/svgavatars-custom-colors.css",
		array( "svgAvatars-styles" ),
		SVGAVATARS_VERSION
	);
	wp_register_style(
		"svgAvatars-custom",
		SVGAVATARS_URL . "data/css/svgavatars-custom.css",
		array( "svgAvatars-styles" ),
		SVGAVATARS_VERSION
	);
	wp_register_script(
		"svgAvatars-tools",
		SVGAVATARS_URL . "data/js/svgavatars.tools.js",
		array( "jquery" ),
		SVGAVATARS_VERSION,
		true
	);
	wp_localize_script(
		"svgAvatars-tools",
		"svgAvatarsWPGlobals",
		array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'svgAvatars_in_AJAX' ),
			'is_logged'=> is_user_logged_in()
		)
	);
	wp_register_script(
		"svgAvatars-defaults",
		SVGAVATARS_URL . "data/js/svgavatars.defaults.js",
		array( "jquery", "svgAvatars-tools" ),
		get_option( "svgAvatars_dynamic_string", "" ),
		true
	);
	wp_register_script(
		"svgAvatars-core",
		SVGAVATARS_URL . "data/js/svgavatars.core.js",
		array( "jquery", "svgAvatars-tools", "svgAvatars-defaults" ),
		SVGAVATARS_VERSION,
		true
	);
}
add_action( 'init', 'svgAvatars_register_main_assets' );

function svgAvatars_enqueue_main_assets( $opt ) {
	wp_enqueue_style( "svgAvatars-roboto-font" );
	wp_enqueue_style( "svgAvatars-spectrum-css" );
	wp_enqueue_style( "svgAvatars-styles" );
	if ( $opt["color_theme"] === "custom" ) {
		wp_enqueue_style( "svgAvatars-custom-colors" );
	}
	wp_enqueue_style( "svgAvatars-custom" );
	wp_enqueue_script( "jquery" );
	wp_enqueue_script( "svgAvatars-tools" );
	wp_enqueue_script( "svgAvatars-defaults" );
	wp_enqueue_script( "svgAvatars-core" );
}

function svgAvatars_main_assets_in_frontend() {
	$options = get_option( "svgAvatars_options" );
	if ( $options["use_on_spec_page"] === "true" && ! empty( $options["page_slug"] ) ) {
		$slug = explode( ',', $options["page_slug"] );
		foreach ($slug as $value) {
			if ( ! empty( trim( $value ) ) && is_page( trim( $value ) ) ) {
				svgAvatars_enqueue_main_assets( $options );
			}
		}
		// BuddyPress assets enqueue for 'create-svg-avatar' tab on 'profile' page
		if ( class_exists( "Buddypress" ) && $options["add_buddypress_profile_subnav"] === "true") {
			if ( function_exists( "bp_current_action" ) && "create-svg-avatar" === bp_current_action() ) {
				svgAvatars_enqueue_main_assets( $options );
			}
		}
	} else {
		svgAvatars_enqueue_main_assets( $options );
	}
}
add_action( "wp_enqueue_scripts", "svgAvatars_main_assets_in_frontend" );

function svgAvatars_main_assets_in_backend() {
	global $pagenow;
	if ( $pagenow === "options-general.php" ) {
		wp_enqueue_script(
			"svgAvatars-admin-js",
			SVGAVATARS_URL . "data/js/svgavatars.admin.js",
			array( "jquery" ),
			SVGAVATARS_VERSION,
			true
		);
		wp_localize_script( "svgAvatars-admin-js", "svgAvatars_confirms_in_settings",
			array(
				"factory_defaults_msg" => esc_html__( "Are you sure you want to set factory defaults for ALL the options?", "svg-avatars-generator" ),
				"delete_options_msg" => esc_html__( "Are you sure you want to delete ALL the options upon deleting this plugin?", "svg-avatars-generator" )
			)
		);
	}

	$options = get_option( "svgAvatars_options" );

	if ( $pagenow === "profile.php" && $options['show_in_backend'] === "true" ) {
		svgAvatars_enqueue_main_assets( $options );
	}
}
add_action( 'admin_enqueue_scripts', 'svgAvatars_main_assets_in_backend' );
