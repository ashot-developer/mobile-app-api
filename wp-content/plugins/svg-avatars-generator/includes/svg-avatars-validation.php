<?php
// If this file is called directly, abort.
if ( ! defined( "ABSPATH" ) ) {
	exit;
}

// Sanitize and validate inputs. Accepts an array, return a sanitized array.
function svgAvatars_validate_options( $input ) {
	// getting previously saved options
	$stored = get_option( "svgAvatars_options" );

	// check if each option exists and set default if doesn't
	global $svgAvatars_default_options;
	foreach ( $svgAvatars_default_options as $key => $value) {
		if ( ! isset( $input[$key] ) ) {
			$input[$key] = $value; // reset to initial default value
		}
	}

	// sanitize welcome slogans
	$input["welcome_slogan_1"] = sanitize_text_field( $input["welcome_slogan_1"] );
	if ( empty( trim( $input["welcome_slogan_1"] ) ) ) {
		svgAvatars_settings_error( "welcome_slogan_1", esc_html__( "The welcome slogan cannot be empty.", "svg-avatars-generator" ) );
		$input["welcome_slogan_1"] = $stored["welcome_slogan_1"]; // reset
	}
	$input["welcome_slogan_2"] = sanitize_text_field( $input["welcome_slogan_2"] );
	if ( empty( trim( $input["welcome_slogan_2"] ) ) ) {
		svgAvatars_settings_error( "welcome_slogan_2", esc_html__( "The welcome slogan cannot be empty.", "svg-avatars-generator" ) );
		$input["welcome_slogan_2"] = $stored["welcome_slogan_2"]; // reset
	}
	$input["welcome_slogan_3"] = sanitize_text_field( $input["welcome_slogan_3"] );
	if ( empty( trim( $input["welcome_slogan_3"] ) ) ) {
		svgAvatars_settings_error( "welcome_slogan_3", esc_html__( "The welcome slogan cannot be empty.", "svg-avatars-generator" ) );
		$input["welcome_slogan_3"] = $stored["welcome_slogan_3"]; // reset
	}

	// validate input for loading assets on a specific page
	$allowed = array("true", "false");
	if ( ! in_array( $input["use_on_spec_page"], $allowed, true ) ) {
		$input["use_on_spec_page"] = $stored["use_on_spec_page"]; // reset
	}

	// sanitizing page slug
	if ( empty( trim( $input["page_slug"] ) ) ) {
		if ( $input["use_on_spec_page"] === "false" ) {
			$input["page_slug"] = "";
		} else {
			$input["page_slug"] = $stored["page_slug"]; // reset
			svgAvatars_settings_error( "page_slug", esc_html__( "The page slug cannot be empty if a specific page is used.", "svg-avatars-generator" ) );
		}
	} else {
		$input["page_slug"] = wp_filter_nohtml_kses( $input["page_slug"] );
	}

	// validate input for allowed genders
	$allowed = array("both", "boysonly", "girlsonly");
	if ( ! in_array( $input["show_gender"], $allowed, true ) ) {
		$input["show_gender"] = $stored["show_gender"]; // reset
	}

	// validate input for icon "zooming" option
	$allowed = array("three_steps_upscale", "two_steps_upscale", "one_step_upscale", "not_changed", "one_step_downscale", "two_steps_downscale", "three_steps_downscale");
	if ( ! in_array( $input["zooming"], $allowed, true ) ) {
		$input["zooming"] = $stored["zooming"]; // reset
	}

	// validate input for uploading to secure.gravatar.com
	$allowed = array("true", "false");
	if ( ! in_array( $input["hide_gravatar"], $allowed, true ) ) {
		$input["hide_gravatar"] = $stored["hide_gravatar"]; // reset
	}

	// validate size of PNG file for upload to secure.gravatar.com
	if ( ! is_numeric( $input["gravatar_size"] ) || intval( $input["gravatar_size"] ) < 100 ) {
		$input["gravatar_size"] = $stored["gravatar_size"]; // reset
	}
	$input["gravatar_size"] = intval( $input["gravatar_size"] );

	// validate input for removing link to svgavatars.com (true or false)
	$allowed = array("true", "false");
	if ( ! in_array( $input["remove_my_credit"], $allowed, true ) ) {
		$input["remove_my_credit"] = $stored["remove_my_credit"]; // reset
	}

	// sanitazing default file name for downloaded avatars
	// strip the string down to A-Z,a-z,0-9,_,-
	if ( empty( trim( $input["downloading_name"] ) ) ) {
		$input["downloading_name"] = $svgAvatars_default_options["downloading_name"]; // set default name
		svgAvatars_settings_error( "downloading_name", esc_html__( "The default avatar file name cannot be empty.", "svg-avatars-generator" ) );
	} else {
		$input["downloading_name"] = sanitize_html_class( $input["downloading_name"] );
	}

	// validate input for small PNG file for download
	$allowed = array("true", "false");
	if ( ! in_array( $input["hide_png_one_download"], $allowed, true ) ) {
		$input["hide_png_one_download"] = $stored["hide_png_one_download"]; // reset
	}

	// validate size of the first PNG file for download
	if ( ! is_numeric( $input["png_one_download_size"] ) || intval( $input["png_one_download_size"] ) < 20 ) {
		$input["png_one_download_size"] = $stored["png_one_download_size"]; // reset
	}
	$input["png_one_download_size"] = intval( $input["png_one_download_size"] );

	// validate input for big PNG file for download
	$allowed = array("true", "false");
	if ( ! in_array( $input["hide_png_two_download"], $allowed, true ) ) {
		$input["hide_png_two_download"] = $stored["hide_png_two_download"]; // reset
	}

	// validate size of the second PNG file for download
	if ( ! is_numeric( $input["png_two_download_size"] ) || intval( $input["png_two_download_size"] ) < 100 ) {
		$input["png_two_download_size"] = $stored["png_two_download_size"]; // reset
	}
	$input["png_two_download_size"] = intval( $input["png_two_download_size"] );

	// validate size of SVG file for download
	$allowed = array("true", "false");
	if ( ! in_array( $input["hide_svg_download"], $allowed, true ) ) {
		$input["hide_svg_download"] = $stored["hide_svg_download"]; // reset
	}

	// validate size of SVG file for download
	if ( ! is_numeric( $input["svg_download_size"] ) || intval( $input["svg_download_size"] ) < 20 ) {
		$input["svg_download_size"] = $stored["svg_download_size"]; // reset
	}
	$input["svg_download_size"] = intval( $input["svg_download_size"] );

	// validate size of PNG file for download on iOS devices
	if ( ! is_numeric( $input["png_ios_download_size"] ) || intval( $input["png_ios_download_size"] ) < 20 ) {
		$input["png_ios_download_size"] = $stored["png_ios_download_size"]; // reset
	}
	$input["png_ios_download_size"] = intval( $input["png_ios_download_size"] );

	// validate size of PNG file for download on Windows 8 tablets and phones
	if ( ! is_numeric( $input["png_win8tablet_download_size"] ) || intval( $input["png_win8tablet_download_size"] ) < 20 ) {
		$input["png_win8tablet_download_size"] = $stored["png_win8tablet_download_size"]; // reset
	}
	$input["png_win8tablet_download_size"] = intval( $input["png_win8tablet_download_size"] );

	// validate input for disable SVG file for download on Android devices
	$allowed = array("true", "false");
	if ( ! in_array( $input["hide_svg_download_on_Android"], $allowed, true ) ) {
		$input["hide_svg_download_on_Android"] = $stored["hide_svg_download_on_Android"]; // reset
	}

	// validate input for 'show_in_backend' option
	$allowed = array("true", "false");
	if ( ! in_array( $input["show_in_backend"], $allowed, true ) ) {
		$input["show_in_backend"] = $stored["show_in_backend"]; // reset
	}

	// validate size of avatars for saving on a server
	if ( ! is_numeric( $input["save_size"] ) || intval( $input["save_size"] ) < 20 ) {
		$input["save_size"] = $stored["save_size"]; // reset
	}
	$input["save_size"] = intval( $input["save_size"] );

	// validate input of file type for saving
	$allowed = array("png", "svg");
	if ( ! in_array( $input["save_format"], $allowed, true ) ) {
		$input["save_format"] = $stored["save_format"]; // reset
	}

	// validate input for "integration" option (since 1.6)
	// the array with allowed values
	$allowed = array("none", "local", "custom", "BuddyBoss", "BuddyPress", "LearnPress", "UltimateMember", "UltimateMembershipPro", "UserPro", "UPME");
	if ( ! in_array( $input["integration"], $allowed, true ) ) {
		$input["integration"] = $stored["integration"]; // reset
	}
	// check that integrated plugin is active
	switch ( $input["integration"] ) {
		case "BuddyBoss":
			$human_read = "BuddyBoss Platform";
			if ( ! function_exists( "bp_core_xprofile_update_profile_completion_user_progress" ) ) {
				svgAvatars_settings_error( "integration", sprintf( esc_html__( "%s plugin is not installed and/or activated! Please install and activate it first and try again.", "svg-avatars-generator" ), $human_read ) );
				$input["integration"] = $stored["integration"]; // reset
			}
			break;
		case "BuddyPress":
			if ( ! function_exists( "bp_core_set_avatar_constants" ) ) {
				svgAvatars_settings_error( "integration", sprintf( esc_html__( "%s plugin is not installed and/or activated! Please install and activate it first and try again.", "svg-avatars-generator" ), $input["integration"] ) );
				$input["integration"] = $stored["integration"]; // reset
			}
			break;
		case "LearnPress":
			if ( ! function_exists( "learn_press_update_user_profile_avatar" ) ) {
				svgAvatars_settings_error( "integration", sprintf( esc_html__( "%s plugin is not installed and/or activated! Please install and activate it first and try again.", "svg-avatars-generator" ), $input["integration"] ) );
				$input["integration"] = $stored["integration"]; // reset
			}
			break;
		case "UltimateMember":
			$human_read = "Ultimate Member";
			if ( ! class_exists( "UM" ) ) {
				svgAvatars_settings_error( "integration", sprintf( esc_html__( "%s plugin is not installed and/or activated! Please install and activate it first and try again.", "svg-avatars-generator" ), $human_read ) );
				unset( $human_read );
				$input["integration"] = $stored["integration"]; // reset
			}
			break;
		case "UltimateMembershipPro":
			$human_read = "Ultimate Membership Pro";
			if ( ! function_exists( "ihc_get_avatar_for_uid" ) ) {
				svgAvatars_settings_error( "integration", sprintf( esc_html__( "%s plugin is not installed and/or activated! Please install and activate it first and try again.", "svg-avatars-generator" ), $human_read ) );
				unset( $human_read );
				$input["integration"] = $stored["integration"]; // reset
			}
			break;
		case "UserPro":
			if ( ! class_exists( "UserPro" ) ) {
				svgAvatars_settings_error( "integration", sprintf( esc_html__( "%s plugin is not installed and/or activated! Please install and activate it first and try again.", "svg-avatars-generator" ), $input["integration"] ) );
				$input["integration"] = $stored["integration"]; // reset
			}
			break;
		case "UPME":
			$human_read = "User Profiles Made Easy";
			if ( ! class_exists( "UPME" ) ) {
				svgAvatars_settings_error( "integration", sprintf( esc_html__( "%s plugin is not installed and/or activated! Please install and activate it first and try again.", "svg-avatars-generator" ), $human_read ) );
				unset( $human_read );
				$input["integration"] = $stored["integration"]; // reset
			}
			break;
		default: // do nothing
			break;
	}

	// validate input for adding 'Create Avatar' tab in BuddyPress profile page
	if ( $input["add_buddypress_profile_subnav"] !== "true" ) {
		$input["add_buddypress_profile_subnav"] = "false";
	}

	// sanitize custom message heading
	if ( empty( trim( $input["custom_heading"] ) ) ) {
		$input["custom_heading"] = $svgAvatars_default_options["custom_heading"]; // set the default
		svgAvatars_settings_error( "custom_heading", esc_html__( "The heading of Custom message cannot be empty.", "svg-avatars-generator" ) );
	} else {
		$input["custom_heading"] = sanitize_text_field( $input["custom_heading"] );
	}

	// sanitize custom message text
	if ( empty( trim( $input["custom_text"] ) ) ) {
		$input["custom_text"] = $svgAvatars_default_options["custom_text"]; // set the default
		svgAvatars_settings_error( "custom_text", esc_html__( "The text of Custom message cannot be empty.", "svg-avatars-generator" ) );
	} else {
		$input["custom_text"] = sanitize_text_field( $input["custom_text"] );
	}

	// validate input for disable share functionality
	$allowed = array("true", "false");
	if ( ! in_array( $input["hide_share"], $allowed, true ) ) {
		$input["hide_share"] = $stored["hide_share"]; // reset
	}

	// validate size of image for share
	if ( ! is_numeric( $input["share_image_size"] ) || intval( $input["share_image_size"] ) < 200 ) {
		$input["share_image_size"] = $stored["share_image_size"]; // reset
	}
	$input["share_image_size"] = intval( $input["share_image_size"] );

	// validate input for Pinterest share
	$allowed = array("true", "false");
	if ( ! in_array( $input["pinterest"], $allowed, true ) ) {
		$input["pinterest"] = $stored["pinterest"]; // reset
	}

	// validate input for Twitter share
	$allowed = array("true", "false");
	if ( ! in_array( $input["twitter"], $allowed, true ) ) {
		$input["twitter"] = $stored["twitter"]; // reset
	}

	// validate URL input for share link
	if ( empty( trim( $input["share_link"] ) ) ) {
		$input["share_link"] = ""; // set it empty
	} else {
		if ( (bool)preg_match( "#http(s?)://(.+)#i", $input["share_link"] ) ) {
			$input["share_link"] = esc_url_raw( $input["share_link"] );
		} else {
			$input["share_link"] = $stored["share_link"]; // reset
			svgAvatars_settings_error( "share_link", esc_html__(  "The URL you entered did not appear to be a valid URL. Please enter a valid one.", "svg-avatars-generator" ) );
		}
	}

	// sanitize share title, description, and credit (strip HTML tags, and escape characters)
	$input["share_title"] = wp_filter_nohtml_kses( $input["share_title"] );
	$input["share_description"] = wp_filter_nohtml_kses( $input["share_description"] );
	$input["share_credit"] = wp_filter_nohtml_kses( $input["share_credit"] );

	// validate saturation delta
	if ( ! is_numeric( $input["delta_sat"] ) ||
		 ( intval( $input["delta_sat"] ) < 0 || intval( $input["delta_sat"] ) > 100 ) ) {
		$input["delta_sat"] = $stored["delta_sat"]; // reset
	}
	$input["delta_sat"] = intval( $input["delta_sat"] );

	// validate brightness delta
	if ( ! is_numeric( $input["delta_val"] ) ||
		 ( intval( $input["delta_val"] ) < 0 || intval( $input["delta_val"] ) > 100 ) ) {
		$input["delta_val"] = $stored["delta_val"]; // reset
	}
	$input["delta_val"] = intval( $input["delta_val"] );

	// validate input for choosen color scheme (dark, light or custom)
	$allowed = array("dark", "light", "custom");
	if ( ! in_array( $input["color_theme"], $allowed, true ) ) {
		$input["color_theme"] = $stored["color_theme"]; // reset
	}

	// validate input for restoring default options
	if ( $input["reset_to_default_options"] !== "true" ) {
		$input["reset_to_default_options"] = "false";
	}

	// validate input for deleting all plugin options on its uninstall
	if ( $input["delete_settings_on_uninstall"] !== "true" ) {
		$input["delete_settings_on_uninstall"] = "false";
	}

	// validate input for debug mode
	if ( $input["debug"] !== "true" ) {
		$input["debug"] = "false";
	}

	// if reset to factory defaults
	if ( $input["reset_to_default_options"] === "true" ) {
		foreach ( $svgAvatars_default_options as $key => $value) {
			$input[$key] = $value; // reset to factory default value
		}
	}

	// write the result JavaScript file into the wp-content/plugins/svg-avatars-generator/data/js/ with validated options
	svgAvatars_save_result_file( $input );

	// return validated and sanitized array
	return $input;
}

// adding settings error for admin notice
function svgAvatars_settings_error ( $setting, $msg ) {
	if ( function_exists( "add_settings_error" ) ) {
		add_settings_error( $setting, "invalid_" . $setting, $msg );
	}
}

// function for saving the resulting JS file: svgavatars.defaults.js
// this file contains defaults, and translation functions
function svgAvatars_save_result_file( $options ) {
	if( ! current_user_can( "manage_options" ) ) {
		wp_die( esc_html__( "You can't manage options.", "svg-avatars-generator" ) );
	}
	global $wp_filesystem;
	$filename = SVGAVATARS_PATH . "data/js/svgavatars.defaults.js";
	if ( ! $wp_filesystem->exists( $filename ) || ! $wp_filesystem->is_writable( $filename ) ) {
		wp_die(
			sprintf(
				'<div class="error"><h3>' .
				esc_html__( '"svgavatars.defaults.js" could not be created', "svg-avatars-generator" ) .
				"</h3>" .
				esc_html__( "The file", "svg-avatars-generator" ) .
				" <b>%s</b> " .
				esc_html__( "cannot be saved.", "svg-avatars-generator" ) .
				"<br>" .
				esc_html__( "Please check that the file exists and/or you need to make this file writable before you can use the plugin. See the ", "svg-avatars-generator" ) .
				' <a href="http://codex.wordpress.org/Changing_File_Permissions">' .
				esc_html__( "Codex", "svg-avatars-generator" ) .
				"</a> " .
				esc_html__( "for more information.", "svg-avatars-generator" ) .
				"</div>",
				$filename
			)
		);
	}

	// saving a random value to use in declarations for preventing files cache
	update_option( "svgAvatars_dynamic_string", substr( md5(uniqid(rand(), true)), 0, 12) );

	// preparing parameters for resulting file
	if ( ! empty( $options ) ) {
		$trimmed = trim( $options["downloading_name"] );
		if ( empty( $trimmed ) ) {
			$downloading_name = "'myAvatar'";
		} else {
			$downloading_name = "'" . $options["downloading_name"] . "'";
		}
		$trimmed = trim( $options["share_link"] );
		if ( empty( $trimmed ) ) {
			$share_link = "document.URL";
		} else {
			$share_link = "'" . $options["share_link"] . "'";
		}
		$trimmed = trim( $options["share_title"] );
		if ( empty( $trimmed ) ) {
			$share_title = "document.title";
		} else {
			$share_title = "'" . $options["share_title"] . "'";
		}
		$trimmed = trim( $options["share_description"] );
		if ( empty( $trimmed ) ) {
			$share_description = "''";
		} else {
			$share_description = "'" . $options["share_description"] . "'";
		}
		$trimmed = trim( $options["share_credit"] );
		if ( empty( $trimmed ) ) {
			$share_credit = "''";
		} else {
			$share_credit = "'" . $options["share_credit"] . "'";
		}
		if ($options['integration'] === 'none') {
			$welcome_slogan_logged_in  = str_replace( '"', '\"', $options["welcome_slogan_3"]);
			$welcome_slogan_logged_out = str_replace( '"', '\"', $options["welcome_slogan_3"]);
		} else {
			$welcome_slogan_logged_in  = str_replace( '"', '\"', $options["welcome_slogan_1"]);
			$welcome_slogan_logged_out = str_replace( '"', '\"', $options["welcome_slogan_2"]);
		}

		switch ($options["zooming"]) {
			case "three_steps_upscale":
				$zooming = 3;
				break;
			case "two_steps_upscale":
				$zooming = 2;
				break;
			case "one_step_upscale":
				$zooming = 1;
				break;
			case "one_step_downscale":
				$zooming = -1;
				break;
			case "two_steps_downscale":
				$zooming = -2;
				break;
			case "three_steps_downscale":
				$zooming = -3;
				break;
			case "not_changed":
			default:
				$zooming = 0;
		}

		// Setting of specific avatar size for other plugins integration
		$save_size = $options["save_size"];
		// check if BuddyBoss is active
		if ( $options["integration"] === "BuddyBoss" && function_exists( "bp_core_xprofile_update_profile_completion_user_progress" ) ) {
			// set the saving avatar size based on a max value
			if ( BP_AVATAR_FULL_WIDTH >= BP_AVATAR_FULL_HEIGHT ) {
				$save_size = BP_AVATAR_FULL_WIDTH;
			} else {
				$save_size = BP_AVATAR_FULL_HEIGHT;
			}
		}
		// check if BuddyPress is active
		if ( $options["integration"] === "BuddyPress" && function_exists( "bp_core_set_avatar_constants" ) ) {
			// set the saving avatar size based on a max value
			if ( BP_AVATAR_FULL_WIDTH >= BP_AVATAR_FULL_HEIGHT ) {
				$save_size = BP_AVATAR_FULL_WIDTH;
			} else {
				$save_size = BP_AVATAR_FULL_HEIGHT;
			}
		}
		// check if LearnPress is active
		if ( $options["integration"] === "LearnPress" && function_exists( "learn_press_get_avatar_thumb_size" ) ) {
			$LP_sizes = learn_press_get_avatar_thumb_size();
			if ( is_array( $LP_sizes ) ) {
				// get the max size of UM avatar sizes
				$save_size = max( $LP_sizes );
			}
			unset( $LP_sizes );
		}
		// check if Ultimate Member is active
		if ( $options["integration"] === "UltimateMember" && class_exists( "UM" ) ) {
			// set the saving avatar size based on a max value
			$UM_sizes = UM()->options()->get( "photo_thumb_sizes" );
			if ( is_array( $UM_sizes ) ) {
				// get the max size of UM avatar sizes
				$save_size = max( $UM_sizes );
				// add 100 px to UM max size and check what size is bigger and get it
				$save_size = max( $save_size + 100, $options["save_size"] );
			}
			unset( $UM_sizes );
		}
		// check if User Profiles Made Easy is active
		if ( $options["integration"] === "UPME"  && class_exists( "UPME" ) ) {
			global $upme_options;
			// set the saving avatar size based on a max value
			if ( $upme_options->upme_settings["profile_image_resize_width"] && $upme_options->upme_settings["profile_image_resize_height"] ) {
				$upme_ava_w = intval( $upme_options->upme_settings["profile_image_resize_width"] );
				$upme_ava_h = intval( $upme_options->upme_settings["profile_image_resize_height"] );
				if ( $upme_ava_w < 100 ) { $upme_ava_w = 100; }
				if ( $upme_ava_h < 100 ) { $upme_ava_h = 100; }
				if ( $upme_ava_w >= $upme_ava_h ) {
					$upme_ava_size = $upme_ava_w;
				} else {
					$upme_ava_size = $upme_ava_h;
				}
				if ( $save_size < $upme_ava_size ) {
					$save_size = $upme_ava_size;
				}
			}
		}

		$path_from_root = str_replace( SVGAVATARS_THIS_HOST, "", str_replace( "\\", "/", SVGAVATARS_URL));
		$path_from_root = str_replace( "https://", "", $path_from_root);
		$path_from_root = str_replace( "http://", "", $path_from_root);

		$svgavatars_options = "/**" . PHP_EOL
			. " * " . esc_html__( "PLEASE DO NOT EDIT THIS FILE!", "svg-avatars-generator" ) . PHP_EOL

			. " * " . esc_html__( "Make your changes in the WP dashboard on SVG Avatars Settings page.", "svg-avatars-generator" ) . PHP_EOL

			. " */" . PHP_EOL

			. "function svgAvatarsOptions() {" . PHP_EOL

			. '  "use strict";' . PHP_EOL

			. "  var options = {" . PHP_EOL

			. "    pathToFolder: '" . $path_from_root . "data/" . "'," . PHP_EOL

			. "    downloadingName: " . $downloading_name . "," . PHP_EOL

			. "    showGender: '" . $options["show_gender"] . "'," . PHP_EOL

			. "    saturationDelta: " . $options["delta_sat"] / 100 . "," . PHP_EOL

			. "    brightnessDelta: " . $options["delta_val"] / 100 . "," . PHP_EOL

			. "    saveFileFormat: '" . $options["save_format"] . "'," . PHP_EOL

			. "    savingSize: " . $save_size . "," . PHP_EOL

			. "    svgDownloadSize: " . $options["svg_download_size"] . "," . PHP_EOL

			. "    pngFirstDownloadSize: " . $options["png_one_download_size"] . "," . PHP_EOL

			. "    pngSecondDownloadSize: " . $options["png_two_download_size"] . "," . PHP_EOL

			. "    pngiOSDownloadSize: " . $options["png_ios_download_size"] . "," . PHP_EOL

			. "    pngWin8TabletDownloadSize: " . $options["png_win8tablet_download_size"] . "," . PHP_EOL

			. "    gravatarSize: " . $options["gravatar_size"] . "," . PHP_EOL

			. "    hideSvgDownloadOnAndroid: " . $options["hide_svg_download_on_Android"] . "," . PHP_EOL

			. "    hideSvgDownloadButton: " . $options["hide_svg_download"] . "," . PHP_EOL

			. "    hidePngFirstDownloadButton: " . $options["hide_png_one_download"] . "," . PHP_EOL

			. "    hidePngSecondDownloadButton: " . $options["hide_png_two_download"] . "," . PHP_EOL

			. "    hideGravatar: " . $options["hide_gravatar"] . "," . PHP_EOL

			. "    colorScheme: '" . $options["color_theme"] . "'," . PHP_EOL

			. "    hideShareButton: " . $options["hide_share"] . "," . PHP_EOL

			. "    shareImageSize: " . $options["share_image_size"] . "," . PHP_EOL

			. "    twitter: " . $options["twitter"] . "," . PHP_EOL

			. "    pinterest: " . $options["pinterest"] . "," . PHP_EOL

			. "    shareLink: " . $share_link . "," . PHP_EOL

			. "    shareTitle: " . $share_title . "," . PHP_EOL

			. "    shareDescription: " . $share_description . "," . PHP_EOL

			. "    shareCredit: " . $share_credit . "," . PHP_EOL

			. "    removeCredit: " . $options["remove_my_credit"] . "," . PHP_EOL

			. "    integration: '" . $options["integration"] . "'," . PHP_EOL

			. "    debug: " . $options["debug"] . "," . PHP_EOL

			. "    zooming: " . $zooming . PHP_EOL

			. "  };" . PHP_EOL

			. "  return options;" . PHP_EOL

			. "}" . PHP_EOL;

		$svgavatars_translation = 'function svgAvatarsTranslation() {' . PHP_EOL

			. '  "use strict";' . PHP_EOL

			. '  var text = {' . PHP_EOL

			. '    welcomeSloganLoggedIn: "<h2>' . $welcome_slogan_logged_in . '</h2>",' . PHP_EOL

			. '    welcomeSloganLoggedOut: "<h2>' . $welcome_slogan_logged_out . '</h2>",' . PHP_EOL

			. '    welcomeMsg: "<p>' . esc_html__( "תבחר בהאם האווטר יהיה בדמות של ילד או של ילדה", "svg-avatars-generator" ) . '</p>",' . PHP_EOL

			. '    waitMsg: "<p>' . esc_html__( "המתן...", "svg-avatars-generator" ) . '</p>",' . PHP_EOL

			. '    randomMsg: "' . esc_html__( "random", "svg-avatars-generator" ) . '",' . PHP_EOL

			. '    resetMsg: "' . esc_html__( "reset", "svg-avatars-generator" ) . '",' . PHP_EOL

			. '    saveMsg: "' . esc_html__( "שמירה", "svg-avatars-generator" ) . '",' . PHP_EOL

			. '    shareMsg: "' . esc_html__( "share", "svg-avatars-generator" ) . '",' . PHP_EOL

			. '    gravatarMsg: "' . esc_html__( "Gravatar", "svg-avatars-generator" ) . '",' . PHP_EOL

			. '    downloadMsg: "' . esc_html__( "download", "svg-avatars-generator" ) . '",' . PHP_EOL

			. '    svgFormatMsg: "' . esc_html__( "SVG - vector format", "svg-avatars-generator" ) . '",' . PHP_EOL

			. '    confirmMsg: "<h3>' . esc_html__( "Are you sure?", "svg-avatars-generator" ) . '</h3><p>' . esc_html__( "All the current changes will be lost.", "svg-avatars-generator" ) . '</p>",' . PHP_EOL

			. '    iosMsg: "<p>' . esc_html__( "Please tap and hold the avatar and choose Save", "svg-avatars-generator" ) . '</p>",' . PHP_EOL

			. '    gravatarTitle: "<h3>' . esc_html__( "You can upload the created avatar to Gravatar.com", "svg-avatars-generator" ) . '</h3><p>' . esc_html__( "Please enter your Gravatar email and password", "svg-avatars-generator" ) . '</p>",' . PHP_EOL

			. '    gravatarEmail: "' . esc_html__( "Your Gravatar email", "svg-avatars-generator" ) . '",' . PHP_EOL

			. '    gravatarPwd: "' . esc_html__( "Your Gravatar password", "svg-avatars-generator" ) . '",' . PHP_EOL

			. '    gravatarRating: "' . esc_html__( "Rating:", "svg-avatars-generator" ) . '",' . PHP_EOL

			. '    gravatarNote: "<p><small>' . esc_html__( "Note: Your email and password will NEVER be stored on our server", "svg-avatars-generator" ) . '</small></p>",' . PHP_EOL

			. '    installMsg: "' . esc_html__( "upload", "svg-avatars-generator" ) . '",' . PHP_EOL

			. '    alertSvgSupportError: "' . esc_html__( "Sorry, but your browser does not support SVG (Scalable Vector Graphic). Avatar Generator cannot start.", "svg-avatars-generator" ) . '",' . PHP_EOL

			. '    alertJsonError: "<h3>' . esc_html__( "Error loading of JSON graphic data!", "svg-avatars-generator" ) . '</h3><p>' . esc_html__( "Please try to reload the page. If the error still exists, please contact the administrator.", "svg-avatars-generator" ) . '</p>",' . PHP_EOL

			. '    alertSuccess: "<h3>' . esc_html__( "כל הכבוד!", "svg-avatars-generator" ) . '</h3><p>' . esc_html__( "You have successfully created the new avatar. You can check it on your profile page.", "svg-avatars-generator" ) . '</p>",' . PHP_EOL

			. '    alertSuccessCustom: "' . "<h3>" . str_replace( '"', '\"', $options["custom_heading"]) . "</h3><p>" . str_replace( '"', '\"', $options["custom_text"]) . '</p>",' . PHP_EOL

			. '    alertErrorCommon: "<h3>' . esc_html__( "An error occurred!", "svg-avatars-generator" ) . '</h3><p>' . esc_html__( "Please try again.", "svg-avatars-generator" ) . '</p><p>' . esc_html__( "If the error still exists, please reload the page, recreate your avatar and try again. If this doesn't help, please contact the administrator.", "svg-avatars-generator" ) . '</p>",' . PHP_EOL

			. '    alertLoginFail: "<h3>' . esc_html__( "You are not logged in!", "svg-avatars-generator" ) . '</h3><p>' . esc_html__( "Please login or register first, then reload this page and create your avatar again.", "svg-avatars-generator" ) . '</p>",' . PHP_EOL

			. '    alertSuccessGravatar: "<h3>' . esc_html__( "כל הכבוד!", "svg-avatars-generator" ) . '</h3><p>' . esc_html__( "You have successfully changed your Gravatar. Please allow 5 to 10 minutes for avatar changes take effect.", "svg-avatars-generator" ) . '</p>",' . PHP_EOL

			. '    alertErrorEmailFail: "<h3>' . esc_html__( "Email is empty!", "svg-avatars-generator" ) . '</h3><p>' . esc_html__( "Please enter your email and try again.", "svg-avatars-generator" ) . '</p>",' . PHP_EOL

			. '    alertErrorPasswordFail: "<h3>' . esc_html__( "Password is empty!", "svg-avatars-generator" ) . '</h3><p>' . esc_html__( "Please enter your password and try again.", "svg-avatars-generator" ) . '</p>",' . PHP_EOL

			. '    alertErrorFaultCode8: "<h3>' . esc_html__( "There is an internal error on 'secure.gravatar.com' site!", "svg-avatars-generator" ) . '</h3><p>' . esc_html__( "Please try later.", "svg-avatars-generator" ) . '</p>",' . PHP_EOL

			. '    alertErrorFaultCode9: "<h3>' . esc_html__( "Incorrect Email or Password!", "svg-avatars-generator" ) . '</h3><p>' . esc_html__( "Please check them and try again.", "svg-avatars-generator" ) . '</p>",' . PHP_EOL

			. '    alertErrorImageCreate: "<h3>' . esc_html__( "The image is broken!", "svg-avatars-generator" ) . '</h3><p>' . esc_html__( "Please try again.", "svg-avatars-generator" ) . '</p>",' . PHP_EOL

			. '    authoredMsg: "' . esc_html__( "Graphic engine by ", "svg-avatars-generator" ) . '",' . PHP_EOL

			. '    okMsg: "' . esc_html__( "ok", "svg-avatars-generator" ) . '",' . PHP_EOL

			. '    cancelMsg: "' . esc_html__( "cancel", "svg-avatars-generator" ) . '",' . PHP_EOL

			. '    closeMsg: "' . esc_html__( "סגור", "svg-avatars-generator" ) . '",' . PHP_EOL

			. '    tryAgainMsg: "' . esc_html__( "try again", "svg-avatars-generator" ) . '",' . PHP_EOL

			. '    blockTitles: {' . PHP_EOL

			. '      face: "' . esc_html__( "פנים", "svg-avatars-generator" ) . '",' . PHP_EOL

			. '      eyes: "' . esc_html__( "עיניים", "svg-avatars-generator" ) . '",' . PHP_EOL

			. '      hair: "' . esc_html__( "שיער", "svg-avatars-generator" ) . '",' . PHP_EOL

			. '      clothes: "' . esc_html__( "בגדים", "svg-avatars-generator" ) . '",' . PHP_EOL

			. '      backs: "' . esc_html__( "רקעים", "svg-avatars-generator" ) . '"' . PHP_EOL

			. '    },' . PHP_EOL

			. '    bodyZoneTitles: {' . PHP_EOL

			. '      backs: "' . esc_html_x( "basic", "for graphic backgrounds", "svg-avatars-generator" ) . '",' . PHP_EOL

			. '      faceshape: "' . esc_html__( "צורת פנים", "svg-avatars-generator" ) . '",' . PHP_EOL

			. '      chinshadow: "",' . PHP_EOL

			. '      facehighlight: "",' . PHP_EOL

			. '      humanbody: "",' . PHP_EOL

			. '      clothes: "' . esc_html_x( "בסיסיים", "for clothes", "svg-avatars-generator" ) . '",' . PHP_EOL

			. '      hair: "' . esc_html__( "על הראש", "svg-avatars-generator" ) . '",' . PHP_EOL

			. '      ears: "' . esc_html__( "אוזניים", "svg-avatars-generator" ) . '",' . PHP_EOL

			. '      eyebrows: "' . esc_html__( "גבות", "svg-avatars-generator" ) . '",' . PHP_EOL

			. '      eyesback: "",' . PHP_EOL

			. '      eyesiris: "' . esc_html__( "קשתית", "svg-avatars-generator" ) . '",' . PHP_EOL

			. '      eyesfront: "' . esc_html__( "צורת עין", "svg-avatars-generator" ) . '",' . PHP_EOL

			. '      mouth: "' . esc_html__( "פה", "svg-avatars-generator" ) . '",' . PHP_EOL

			. '      nose: "' . esc_html__( "אף", "svg-avatars-generator" ) . '",' . PHP_EOL

			. '      glasses: "' . esc_html__( "משקפיים", "svg-avatars-generator" ) . '",' . PHP_EOL

			. '      mustache: "' . esc_html__( "mustache", "svg-avatars-generator" ) . '",' . PHP_EOL

			. '      beard: "' . esc_html__( "זקן", "svg-avatars-generator" ) . '"' . PHP_EOL

			. '    }' . PHP_EOL

			. "  };" . PHP_EOL

			. "  return text;" . PHP_EOL

			. "}" . PHP_EOL;

		// save result file to a disk
		$combined_code = $svgavatars_options . $svgavatars_translation;
		$res = $wp_filesystem->put_contents( $filename, $combined_code );
		if ( $res === false ) {
			add_settings_error(
				"svgAvatars_file_save_error",
				"settings_updated",
				esc_html__( "An error occurred! The resulting JavaScript file \"svgavatars.defaults.js\" is not saved. Please try again.", "svg-avatars-generator" ),
				"error"
			);
		}
	} // end check not empty
}
