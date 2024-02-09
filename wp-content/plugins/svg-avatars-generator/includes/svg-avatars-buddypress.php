<?php
// If this file is called directly, abort.
if ( ! defined( "ABSPATH" ) ) {
	exit;
}

if ( class_exists( "Buddypress" ) ) {
	add_action( "bp_setup_nav", "svgAvatars_bb_profile_tab_create_avatar_subnav" );
}

// create subnav in 'profile' tab in 'members' component
function svgAvatars_bb_profile_tab_create_avatar_subnav() {
	$options = get_option( "svgAvatars_options" );
	if ( $options["add_buddypress_profile_subnav"] === "true" ) {
		// get the profile slug
		$profile_slug = bp_get_profile_slug();

		// add subtub 'Create Avatar' into 'Profile'
		bp_core_new_subnav_item( array(
			"name" => esc_html__( "Create Avatar", "svg-avatars-generator" ),
			"slug" => "create-svg-avatar",
			"parent_url" => trailingslashit( bp_loggedin_user_domain() . $profile_slug ),
			"parent_slug" => $profile_slug,
			"screen_function" => "svgAvatars_bb_show_avatar_subnav",
			"position" => 30,
			"user_has_access" => bp_core_can_edit_settings()
		), "members" );
	}
}

function svgAvatars_bb_show_avatar_subnav() {
	// add content here
	add_action( "bp_template_content", "svgAvatars_bb_avatar_subnav_content" );
	// call the members plugins.php template
	bp_core_load_template( "buddypress/members/single/plugins" );
}

function svgAvatars_bb_avatar_subnav_content() {
	echo '<div id="svgAvatars"></div>';
}