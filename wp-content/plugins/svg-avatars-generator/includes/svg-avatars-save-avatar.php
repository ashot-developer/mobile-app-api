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

// require validation functions
require_once( SVGAVATARS_PATH . "data/php/validate-avatar-data.php" );
// require_once get_template_directory() . '/app-api/minime-api.php';

function svgAvatars_save_avatar_not_logged_in_user() {

// getting and validating file name and image data from POST
// returned $file variable will be an array with ["name"] and ["type"]
$file = svgAvatars_validate_filename( $_POST["filename"] );
if ( $file["name"] === "invalid" ) {
	wp_die( "Line " . __LINE__ . ": " . "Received file name doesn't match required pattern." );
}
if ( $file["type"] === "invalid" ) {
	wp_die( "Line " . __LINE__ . ": " . "Received file type is not PNG or SVG." );
}

$file_name = $file["name"];

// common values
global $wp_filesystem;
// returned $data variable will be false if the data is extrinsical to PNG or SVG
$data =  svgAvatars_validate_imagedata( $_POST["imgdata"], $file["type"] );
$user_id = get_current_user_id();
$options = get_option( "svgAvatars_options" );
$uploads_dir_params = wp_get_upload_dir();
$uploads_dir = $uploads_dir_params["basedir"] . "/svg-avatars";
$uploads_url = $uploads_dir_params["baseurl"] . "/svg-avatars";

// PNG or SVG file format
if ( $file["type"] === "png" ) {
	// cheking that validated image data is not empty
	if ( $data == false ) {
		wp_die( "Line " . __LINE__ . ": " . "Received PNG or SVG file data is not valid." );
	}
	$data = base64_decode( $data );
	$ext = ".png";
} elseif ( $file["type"] === "svg" ) {
	$data = stripcslashes( $data );
	// cheking that validated code is SVG
	if ( strpos( $data, '<svg xmlns="http://www.w3.org/2000/svg" version="1.1"' ) === false || strrpos($data, "</svg>", -6) === false ) {
		wp_die( "Line " . __LINE__ . ": " . "Received PNG or SVG file data is not valid." );
	}
	$ext = ".svg";
} else {
	wp_die( "Line " . __LINE__ . ": " . "Received file type is not PNG or SVG." );
}

// Main condition for different type of integration
switch ( $options["integration"] ) {

	case "custom":
		/**
		* This section is for your own custom integration
		*/

		if ( ! $wp_filesystem->is_dir( $uploads_dir ) || ! $wp_filesystem->is_writable( $uploads_dir ) ) {
			wp_die( "Line " . __LINE__ . ": " . "The {$uploads_dir} directory does not exist or is not writable." );
		}

		// Generate a filename with the user identifier, timestamp, and a random component
		if(isset($_POST['parentUid'])) {
			$file_name = 'last-one';
			minime_save_avatar($_POST["imgdata"], $_POST['parentUid'], $_POST['childUid']);
		}

		// delete previously created avatar svg_avatar.{$ext} (if any)
		if ( $wp_filesystem->exists( $uploads_dir . "/" . $user_id . "/" . $file_name . $ext ) ) {
			$wp_filesystem->delete( $uploads_dir . "/" . $user_id . "/" . $file_name . $ext );
		}

		// save new avatar into the 'path_to/wp-content/uploads/svg-avatars/X/' directory,
		// where 'X' is an integer number equal to logged in user ID
		if ( ! $wp_filesystem->exists( $uploads_dir . "/" . $user_id ) ) {
			$wp_filesystem->mkdir( $uploads_dir . "/" . $user_id );
		}

		if ( ! $wp_filesystem->put_contents( $uploads_dir . "/" . $user_id . "/" . $file_name . $ext, $data ) ) {
			wp_die( "Line " . __LINE__ . ": " . "Error writing avatar on a disk." );
		}

		/**
		* You can place here an additional PHP code, for example, to store links of saved avatars
		* in your database. For example, please uncomment the line below to write the URL of
		* saved avatar into the database in 'usermeta' table as 'your_prefix_avatar' meta_key with
		* a desired prefix.
		*/
		

		
		// update_user_meta( $user_id, 'your_prefix_avatar', esc_url( $uploads_url . '/' . $user_id . '/' . $file_name . $ext ) );

		// avatar is saved, echo the result to show user a custom success message
		wp_die( "saved_custom" );

		break;

	case "local":
		/**
		* Locally saved avatars replace default Gravatars.
		* Also takes effect on integration with bbPress forum plugin.
		*/
		if ( ! $wp_filesystem->is_dir( $uploads_dir ) || ! $wp_filesystem->is_writable( $uploads_dir ) ) {
			wp_die( "Line " . __LINE__ . ": " . "The {$uploads_dir} directory does not exist or is not writable." );
		}

		// geting the previously saved file name
		$saved_file_name = get_user_meta( $user_id, "svgAvatars_avatar_url", true );
		$saved_file_name = basename( esc_url( $saved_file_name ) );

		// delete previously created avatar (if any)
		if ( ! empty( $saved_file_name ) ) {
			if ( $wp_filesystem->exists( $uploads_dir . "/" . $user_id . "/" . $saved_file_name ) ) {
				$wp_filesystem->delete( $uploads_dir . "/" . $user_id . "/" . $saved_file_name );
			}
		}

		// save new avatar into the 'path_to/wp-content/uploads/svg-avatars/X/' directory,
		// where 'X' is an integer number equal to logged in user ID
		if ( ! $wp_filesystem->exists( $uploads_dir . "/" . $user_id ) ) {
			$wp_filesystem->mkdir( $uploads_dir . "/" . $user_id );
		}

		if ( ! $wp_filesystem->put_contents( $uploads_dir . "/" . $user_id . "/" . $file_name . $ext, $data ) ) {
			wp_die( "Line " . __LINE__ . ": " . "Error writing avatar on a disk." );
		}

		// adding usermeta record with 'svgAvatars_avatar_url' meta_key
		update_user_meta( $user_id, "svgAvatars_avatar_url", esc_url( $uploads_url . "/" . $user_id . "/" . $file_name . $ext ) );

		wp_die( "saved" );

		break;

	case "BuddyBoss":
		/**
		* BuddyBoss is a fork of BuddyPress. It creates 'avatars' directory (hardcoded in the
		* bp-core/bp-core-avatars.php) in the 'path_to/wp-content/uploads/' one.
		* And it adds an integer number same as the unique user ID for name of subdirectory for each user.
		* BuddyBoss adds specific usermeta 'bp_profile_completion_widgets' meta_key into the database.
		* The function bp_core_xprofile_update_profile_completion_user_progress() does that.
		*/

		$human_name = '"BuddyBoss Platform plugin"';

		// check if BuddyBoss is active
		if ( ! function_exists( "bp_core_set_avatar_constants" ) || ! function_exists( "bp_core_xprofile_update_profile_completion_user_progress" ) ) {
			wp_die( "Line " . __LINE__ . ": " . "Integration error with {$human_name}. It looks like {$human_name} is not installed and/or activated." );
		}

		// BuddyBoss has its own avatar sizes
		if ( BP_AVATAR_FULL_WIDTH >= BP_AVATAR_FULL_HEIGHT ) {
			$bb_size_full = BP_AVATAR_FULL_WIDTH;
		} else {
			$bb_size_full = BP_AVATAR_FULL_HEIGHT;
		}
		if ( BP_AVATAR_THUMB_WIDTH >= BP_AVATAR_THUMB_HEIGHT ) {
			$bb_size_thumb = BP_AVATAR_THUMB_WIDTH;
		} else {
			$bb_size_thumb = BP_AVATAR_THUMB_HEIGHT;
		}

		// BuddyBoss has its own avatars upload directory
		$uploads_dir = bp_core_avatar_upload_path() . "/avatars";
		// if not exist, create this folder
		if ( ! $wp_filesystem->exists( $uploads_dir ) ) {
			$wp_filesystem->mkdir( $uploads_dir );
		}

		if ( ! $wp_filesystem->is_dir( $uploads_dir ) || ! $wp_filesystem->is_writable( $uploads_dir ) ) {
			wp_die( "Line " . __LINE__ . ": " . "The {$uploads_dir} directory does not exist or is not writable." );
		}

		bp_core_delete_existing_avatar( array("item_id" => $user_id, "object" => "user") );
		if ( ! $wp_filesystem->exists( $uploads_dir . "/" . $user_id ) ) {
			$wp_filesystem->mkdir( $uploads_dir . "/" . $user_id );
		}

		if ( $file["type"] === "png" ) {

			// BuddyBoss has its own avatar filenames
			$full_avatar_name_and_path = $uploads_dir . "/" . $user_id . "/" . $file_name . "-bpfull" . ".png";
			$thumb_avatar_name_and_path = $uploads_dir . "/" . $user_id . "/" . $file_name . "-bpthumb" . ".png";

			// writing the "-bpfull" avatar
			if ( ! $wp_filesystem->put_contents( $full_avatar_name_and_path, $data ) ) {
				wp_die( "Line " . __LINE__ . ": " . "Error writing '-bpfull' avatar on a disk." );
			}

			$editor = wp_get_image_editor( $full_avatar_name_and_path );
			// resizing and writing the "-bpthumb" avatar
			if ( ! is_wp_error( $editor ) ) {
				$editor->resize( $bb_size_thumb, $bb_size_thumb, false );
				$editor->save( $thumb_avatar_name_and_path );
			} else {
				if ( ! $wp_filesystem->put_contents( $thumb_avatar_name_and_path, $data ) ) {
					wp_die( "Line " . __LINE__ . ": " . "Error writing '-bpthumb' avatar on a disk." );
				}
			}

			// updating 'bp_profile_completion_widgets' usermeta
			bp_core_xprofile_update_profile_completion_user_progress();

			wp_die( "saved" );

		} elseif ( $file["type"] === "svg" ) {

			// BuddyBoss has its own avatar filenames
			$full_avatar_name_and_path = $uploads_dir . "/" . $user_id . "/" . $file_name . "-bpfull" . ".svg";
			$thumb_avatar_name_and_path = $uploads_dir . "/" . $user_id . "/" . $file_name . "-bpthumb" . ".svg";

			// writing the "-bpfull" avatar
			if ( ! $wp_filesystem->put_contents( $full_avatar_name_and_path, $data ) ) {
				wp_die( "Line " . __LINE__ . ": " . "Error writing '-bpfull' avatar on a disk." );
			}

			// resizing SVG and writing the "-bpthumb" avatar
			$search_str = 'version="1.1" width="' . $bb_size_full . 'px" height="' . $bb_size_full . 'px"';
			$replace_str = 'version="1.1" width="' . $bb_size_thumb . 'px" height="' . $bb_size_thumb . 'px"';
			$data = str_replace( $search_str, $replace_str, $data );
			if ( ! $wp_filesystem->put_contents( $thumb_avatar_name_and_path, $data ) ) {
				wp_die( "Line " . __LINE__ . ": " . "Error writing '-bpthumb' avatar on a disk." );
			}

			// updating 'bp_profile_completion_widgets' usermeta
			bp_core_xprofile_update_profile_completion_user_progress();

			wp_die( "saved" );

		} else {
			wp_die( "Line " . __LINE__ . ": " . "Received file type is not PNG or SVG." );
		}

		break;

	case "BuddyPress":
		/**
		* In general, BuddyPress creates 'avatars' directory (hardcoded in the
		* bp-core/bp-core-avatars.php) in the 'path_to/wp-content/uploads/' one.
		* And it adds an integer number same as the unique user ID for name of subdirectory for each user.
		* BuddyPress doesn't add any avatar data into the database.
		*/

		$human_name = '"BuddyPress plugin"';

		// check if BuddyPress is active
		if ( ! function_exists( "bp_core_set_avatar_constants" ) ) {
			wp_die( "Line " . __LINE__ . ": " . "Integration error with {$human_name}. It looks like {$human_name} is not installed and/or activated." );
		}

		// BuddyPress has its own avatar sizes
		if ( BP_AVATAR_FULL_WIDTH >= BP_AVATAR_FULL_HEIGHT ) {
			$bp_size_full = BP_AVATAR_FULL_WIDTH;
		} else {
			$bp_size_full = BP_AVATAR_FULL_HEIGHT;
		}
		if ( BP_AVATAR_THUMB_WIDTH >= BP_AVATAR_THUMB_HEIGHT ) {
			$bp_size_thumb = BP_AVATAR_THUMB_WIDTH;
		} else {
			$bp_size_thumb = BP_AVATAR_THUMB_HEIGHT;
		}

		// BuddyPress has its own avatars upload directory
		$uploads_dir = bp_core_avatar_upload_path() . "/avatars";
		// if not exist, create this folder
		if ( ! $wp_filesystem->exists( $uploads_dir ) ) {
			$wp_filesystem->mkdir( $uploads_dir );
		}

		if ( ! $wp_filesystem->is_dir( $uploads_dir ) || ! $wp_filesystem->is_writable( $uploads_dir ) ) {
			wp_die( "Line " . __LINE__ . ": " . "The {$uploads_dir} directory does not exist or is not writable." );
		}

		bp_core_delete_existing_avatar( array("item_id" => $user_id, "object" => "user") );
		if ( ! $wp_filesystem->exists( $uploads_dir . "/" . $user_id ) ) {
			$wp_filesystem->mkdir( $uploads_dir . "/" . $user_id );
		}

		if ( $file["type"] === "png" ) {

			// BuddyPress has its own avatar filenames
			$full_avatar_name_and_path = $uploads_dir . "/" . $user_id . "/" . $file_name . "-bpfull" . ".png";
			$thumb_avatar_name_and_path = $uploads_dir . "/" . $user_id . "/" . $file_name . "-bpthumb" . ".png";

			// writing the "-bpfull" avatar
			if ( ! $wp_filesystem->put_contents( $full_avatar_name_and_path, $data ) ) {
				wp_die( "Line " . __LINE__ . ": " . "Error writing '-bpfull' avatar on a disk." );
			}

			$editor = wp_get_image_editor( $full_avatar_name_and_path );
			// resizing and writing the "-bpthumb" avatar
			if ( ! is_wp_error( $editor ) ) {
				$editor->resize( $bp_size_thumb, $bp_size_thumb, false );
				$editor->save( $thumb_avatar_name_and_path );
			} else {
				if ( ! $wp_filesystem->put_contents( $thumb_avatar_name_and_path, $data ) ) {
					wp_die( "Line " . __LINE__ . ": " . "Error writing '-bpthumb' avatar on a disk." );
				}
			}
			wp_die( "saved" );

		} elseif ( $file["type"] === "svg" ) {

			// BuddyPress has its own avatar filenames
			$full_avatar_name_and_path = $uploads_dir . "/" . $user_id . "/" . $file_name . "-bpfull" . ".svg";
			$thumb_avatar_name_and_path = $uploads_dir . "/" . $user_id . "/" . $file_name . "-bpthumb" . ".svg";

			// writing the "-bpfull" avatar
			if ( ! $wp_filesystem->put_contents( $full_avatar_name_and_path, $data ) ) {
				wp_die( "Line " . __LINE__ . ": " . "Error writing '-bpfull' avatar on a disk." );
			}

			// resizing SVG and writing the "-bpthumb" avatar
			$search_str = 'version="1.1" width="' . $bp_size_full . 'px" height="' . $bp_size_full . 'px"';
			$replace_str = 'version="1.1" width="' . $bp_size_thumb . 'px" height="' . $bp_size_thumb . 'px"';
			$data = str_replace( $search_str, $replace_str, $data );
			if ( ! $wp_filesystem->put_contents( $thumb_avatar_name_and_path, $data ) ) {
				wp_die( "Line " . __LINE__ . ": " . "Error writing '-bpthumb' avatar on a disk." );
			}

			wp_die( "saved" );

		} else {
			wp_die( "Line " . __LINE__ . ": " . "Received file type is not PNG or SVG." );
		}

		break;

	case "LearnPress":
		/**
		* In general, LearnPress creates 'learn-press-profile' directory (hard-coded in the
		* lp-user-functions.php) in the 'path_to/wp-content/uploads/' one. It also adds an integer
		* number equal to unique user ID as a name of subdirectory for each user. After that it
		* saves an avatar with random file name in that directory and saves the part of URL
		* (learn-press-profile/X/random_name.jpg) into the database in the 'usermeta' table as
		* '_lp_profile_picture' meta_key. It also updates another '_lp_profile_picture_changed'
		* meta_key with 'yes' value.
		*/

		$human_name = '"LearnPress plugin"';

		if ( ! function_exists( "learn_press_update_user_profile_avatar" ) ) {
			wp_die( "Line " . __LINE__ . ": " . "Integration error with {$human_name}. It looks like {$human_name} is not installed and/or activated." );
		}

		// LearnPress has its own avatars upload directory
		$uploads_dir = $uploads_dir_params["basedir"] . "/learn-press-profile";
		if ( ! $wp_filesystem->exists( $uploads_dir ) ) {
			$wp_filesystem->mkdir( $uploads_dir );
		}

		if ( ! $wp_filesystem->is_dir( $uploads_dir ) || ! $wp_filesystem->is_writable( $uploads_dir ) ) {
			wp_die( "Line " . __LINE__ . ": " . "The {$uploads_dir} directory does not exist or is not writable." );
		}

		// geting the previously saved file name
		$saved_file_name = get_user_meta( $user_id, "_lp_profile_picture", true );
		$saved_file_name = basename( esc_url( $saved_file_name ) );

		// delete previously created avatar (if any)
		if ( ! empty( $saved_file_name ) ) {
			if ( $wp_filesystem->exists( $uploads_dir . "/" . $user_id . "/" . $saved_file_name ) ) {
				$wp_filesystem->delete( $uploads_dir . "/" . $user_id . "/" . $saved_file_name );
			}
		}

		// save new avatar into the 'path_to/wp-content/uploads/learn-press-profile/X/' directory,
		// where 'X' is an integer number equal to logged in user ID
		if ( ! $wp_filesystem->exists( $uploads_dir . "/" . $user_id ) ) {
			$wp_filesystem->mkdir( $uploads_dir . "/" . $user_id );
		}

		if ( ! $wp_filesystem->put_contents( $uploads_dir . "/" . $user_id . "/" . $file_name . $ext, $data ) ) {
			wp_die( "Line " . __LINE__ . ": " . "Error writing avatar on a disk." );
		}

		// adding usermeta record with '_lp_profile_picture' meta_key
		update_user_meta( $user_id, "_lp_profile_picture", "learn-press-profile/" . $user_id . "/" . $file_name . $ext );
		update_user_meta( $user_id, '_lp_profile_picture_changed', 'yes' );

		wp_die( "saved" );

		break;

	case "UltimateMember":
		/**
		* In general, Ultimate Member creates 'ultimatemember' directory in the
		* 'path_to/wp-content/uploads/' one. And it adds an integer number same as the unique
		* user ID for name of subdirectory for each user.
		* When user upload a profile picture, the plugin creates and saves several images with
		* different sizes, which are get from 'photo_thumb_sizes' option. After that it saves
		* in 'usermeta' table of the database the 'profile_photo.{extention}' value of
		* 'profile_photo' meta_key.
		* Also it changes value of 'um_member_directory_data' meta_key with 'profile_photo'
		* boolean 'true' value.
		*/

		$human_name = '"Ultimate Member plugin"';

		if ( ! class_exists( "UM" ) || ! class_exists( "um\core\User" ) ) {
			wp_die( "Line " . __LINE__ . ": " . "Integration error with {$human_name}. It looks like {$human_name} is not installed and/or activated." );
		}

		$UM_avatar_sizes = UM()->options()->get( "photo_thumb_sizes" );
		$user = new um\core\User();

		// Ultimate Member has its own avatars upload directory
		$uploads_dir = $uploads_dir_params["basedir"] . "/ultimatemember";
		// if not exist, create this folder
		if ( ! $wp_filesystem->exists( $uploads_dir ) ) {
			$wp_filesystem->mkdir( $uploads_dir );
		}

		if ( ! $wp_filesystem->is_dir( $uploads_dir ) || ! $wp_filesystem->is_writable( $uploads_dir ) ) {
			wp_die( "Line " . __LINE__ . ": " . "The {$uploads_dir} directory does not exist or is not writable." );
		}

		// create, if necessary, individual directory for logged in user
		if ( ! $wp_filesystem->exists( $uploads_dir . "/" . $user_id ) ) {
			$wp_filesystem->mkdir( $uploads_dir . "/" . $user_id );
		} else {
			// delete all previously created avatars, if any
			$old_files = glob( $uploads_dir . "/" . $user_id . "/*" ); // get all file names
			foreach( $old_files as $old_file ) {
				if( $wp_filesystem->is_file( $old_file ) ) {
					$wp_filesystem->delete( $old_file );
				}
			}
		}

		// writing the original sized avatar
		$original_sized_avatar = $uploads_dir . "/" . $user_id . "/" . "profile_photo.{$file['type']}";
		if ( ! $wp_filesystem->put_contents( $original_sized_avatar, $data ) ) {
			wp_die( "Line " . __LINE__ . ": " . "Error writing avatar on a disk." );
		}

		update_user_meta( $user_id, "profile_photo", "profile_photo.{$file['type']}" );

		if ( $file["type"] === "png" ) {

			// Ultimate Member has its own avatar sizes
			if ( is_array( $UM_avatar_sizes ) ) {
				// resizing and writing each "sized" avatar
				foreach ( $UM_avatar_sizes as $size ) {
					if ( is_numeric( $size ) ) {
						$editor = wp_get_image_editor( $original_sized_avatar );
						$sized_avatar = $uploads_dir . "/" . $user_id . "/" . "profile_photo-{$size}x{$size}.png";
						if ( ! is_wp_error( $editor ) ) {
							$editor->resize( $size, $size, false );
							$editor->save( $sized_avatar );
						} else {
							if ( ! $wp_filesystem->put_contents( $sized_avatar, $data ) ) {
								wp_die( "Line " . __LINE__ . ": " . "Error writing avatar on a disk." );
							}
						}
					}
				}
			}

			$user->on_update_usermeta( null, $user_id, "profile_photo", "profile_photo.png" );
			// and clear the user cache
			$user->remove_cache( $user_id );

			wp_die( "saved" );

		} elseif ( $file["type"] === "svg" ) {

			// Ultimate Member has its own avatar sizes
			if ( is_array( $UM_avatar_sizes ) ) {
				// resizing and writing each "sized" avatar
				foreach ( $UM_avatar_sizes as $size ) {
					if ( is_numeric( $size ) ) {
						$sized_avatar = $uploads_dir . "/" . $user_id . "/" . "profile_photo-{$size}x{$size}.svg";
						// resizing SVG
						$search_str = '/width="\d+px" height="\d+px"/';
						$replace_str = 'width="' . $size . 'px" height="' . $size . 'px"';
						$resized_data = preg_replace( $search_str, $replace_str, $data );
						if ( ! $wp_filesystem->put_contents( $sized_avatar, $resized_data ) ) {
							wp_die( "Line " . __LINE__ . ": " . "Error writing avatar on a disk." );
						}
					}
				}
			}

			$user->on_update_usermeta( null, $user_id, "profile_photo", "profile_photo.svg" );
			// and clear the user cache
			$user->remove_cache( $user_id );

			wp_die( "saved" );

		} else {
			wp_die( "Line " . __LINE__ . ": " . "Received file type is not PNG or SVG." );
		}

		break;

	case "UltimateMembershipPro":
		/**
		* In general, Ultimate Membership Pro creates a new post with 'attachment' type for
		* every new uploaded image as avatar which is saved in YEAR/MONTH/ directory in the
		* 'path_to/wp-content/uploads/' one. After that the plugin saves into the database
		* that post ID in the 'usermeta' table as 'ihc_avatar' meta_key.
		* However, the direct URL can also be fetched from the database by this plugin, so
		* we may simply save the new URL into 'ihc_avatar' meta_value.
		*/

		$human_name = '"Ultimate Membership Pro plugin"';

		// check if Ultimate Membership Pro is active
		if ( ! function_exists( "ihc_get_avatar_for_uid" ) ) {
			wp_die( "Line " . __LINE__ . ": " . "Integration error with {$human_name}. It looks like {$human_name} is not installed and/or activated." );
		}

		if ( ! $wp_filesystem->is_dir( $uploads_dir ) || ! $wp_filesystem->is_writable( $uploads_dir ) ) {
			wp_die( "Line " . __LINE__ . ": " . "The {$uploads_dir} directory does not exist or is not writable." );
		}

		if ( ! $wp_filesystem->exists( $uploads_dir . "/" . $user_id ) ) {
			$wp_filesystem->mkdir( $uploads_dir . "/" . $user_id );
		}

		// geting the previously saved file name
		$saved_file_name = get_user_meta( $user_id, "ihc_avatar", true );
		$saved_file_name = basename( esc_url( $saved_file_name ) );

		// delete previously created avatar (if any)
		if ( ! empty( $saved_file_name ) ) {
			if ( $wp_filesystem->exists( $uploads_dir . "/" . $user_id . "/" . $saved_file_name ) ) {
				$wp_filesystem->delete( $uploads_dir . "/" . $user_id . "/" . $saved_file_name );
			}
		}

		// replace "svgA" from file name with "ihc"
		$file_name = str_replace( "svgA", "ihc", $file_name );

		// save new avatar into the 'path_to/wp-content/uploads/svg-avatars/X/' directory,
		// where 'X' is an integer number equal to logged in user ID and update usermeta
		if ( ! $wp_filesystem->put_contents( $uploads_dir . "/" . $user_id . "/" . $file_name . $ext, $data ) ) {
			wp_die( "Line " . __LINE__ . ": " . "Error writing avatar on a disk." );
		}

		update_user_meta( $user_id, "ihc_avatar", esc_url( $uploads_url . "/" . $user_id . "/" . $file_name . $ext ) );

		wp_die( "saved" );

		break;

	case "UserPro":
		/*
		* In general, UserPro creates 'userpro' directory in the 'path_to/wp-content/uploads/' one.
		* And it adds an integer number same as the unique user ID for name of subdirectory for each user.
		* UserPro adds avatar URL into the database in the 'usermeta' table as 'profilepicture' meta_key.
		*/

		$human_name = '"UserPro plugin"';

		// check if UserPro is active
		if ( ! class_exists( "UserPro" ) ) {
			wp_die( "Line " . __LINE__ . ": " . "Integration error with {$human_name}. It looks like {$human_name} is not installed and/or activated." );
		}

		global $userpro;

		// UserPro has its own avatars upload directory
		$userpro->do_uploads_dir( $user_id );
		$uploads_dir = $userpro->get_uploads_dir($user_id);
		if ( ! $wp_filesystem->is_dir( $uploads_dir ) || ! $wp_filesystem->is_writable( $uploads_dir ) ) {
			wp_die( "Line " . __LINE__ . ": " . "The {$uploads_dir} directory does not exist or is not writable." );
		}

		// geting the previously saved file name
		$saved_file_name = get_user_meta( $user_id, "profilepicture", true );
		$saved_file_name = basename( esc_url( $saved_file_name ) );

		// delete previously created avatar (if any)
		if ( ! empty( $saved_file_name ) ) {
			if ( $wp_filesystem->exists( $uploads_dir . $saved_file_name ) ) {
				$wp_filesystem->delete( $uploads_dir . $saved_file_name );
			}
		}

		// save new file and update usermeta
		if ( ! $wp_filesystem->put_contents( $uploads_dir . $file_name . $ext, $data ) ) {
			wp_die( "Line " . __LINE__ . ": " . "Error writing avatar on a disk." );
		}

		update_user_meta( $user_id, "profilepicture", esc_url( $userpro->get_uploads_url($user_id) . $file_name . $ext ) );

		wp_die( "saved" );

		break;

	case "UPME":
		/**
		* In general, User Profiles Made Easy creates 'upme' directory in the
		* 'path_to/wp-content/uploads/' one. It saves avatars of all users in it without
		* separation.
		* UPME adds avatar URL into the database in the 'usermeta' table as 'user_pic' and
		* 'upme_user_pic_thumb' meta_keys.
		*/

		$human_name = '"User Profiles Made Easy plugin"';

		// check if User Profiles Made Easy is active
		if ( ! class_exists( "UPME" ) ) {
			wp_die( "Line " . __LINE__ . ": " . "Integration error with {$human_name}. It looks like {$human_name} is not installed and/or activated." );
		}

		global $upme_options;

		// User Profiles Made Easy has its own avatar thumbnail size
		if ( $upme_options->upme_settings["profile_image_resize_width"] && $upme_options->upme_settings["profile_image_resize_height"] ) {
			$upme_ava_w = intval( $upme_options->upme_settings["profile_image_resize_width"] );
			$upme_ava_h = intval( $upme_options->upme_settings["profile_image_resize_height"] );
			if ( $upme_ava_w < 100 ) { $upme_ava_w = 100; }
			if ( $upme_ava_h < 100 ) { $upme_ava_h = 100; }
			if ( $upme_ava_w >= $upme_ava_h ) {
				$upme_size_thumb = $upme_ava_w;
			} else {
				$upme_size_thumb = $upme_ava_h;
			}
		} else {
			$upme_size_thumb = 100;
		}

		// User Profiles Made Easy has its own avatars upload directory
		$uploads_dir = $uploads_dir_params["basedir"] . "/upme";
		$uploads_url = $uploads_dir_params["baseurl"] . "/upme";
		// if not exist, create this folder
		if ( ! $wp_filesystem->exists( $uploads_dir ) ) {
			$wp_filesystem->mkdir( $uploads_dir );
		}

		if ( ! $wp_filesystem->is_dir( $uploads_dir ) || ! $wp_filesystem->is_writable( $uploads_dir ) ) {
			wp_die( "Line " . __LINE__ . ": " . "The {$uploads_dir} directory does not exist or is not writable." );
		}

		// geting the previously saved full file name
		$saved_file_name = get_user_meta( $user_id, "user_pic", true );
		$saved_file_name = basename( esc_url( $saved_file_name ) );
		// only files created by avatar generator will be deleted
		$pos = strpos( $saved_file_name, "svgA" );
		if ($pos !== false) {
			// delete previously created avatar (if any)
			if ( ! empty( $saved_file_name ) ) {
				if ( $wp_filesystem->exists( $uploads_dir . "/" . $saved_file_name ) ) {
					$wp_filesystem->delete( $uploads_dir . "/" . $saved_file_name );
				}
			}
		}

		// geting the previously saved thumb file name
		$saved_file_name = get_user_meta( $user_id, "upme_user_pic_thumb", true );
		$saved_file_name = basename( esc_url( $saved_file_name ) );
		// only files created by avatar generator will be deleted
		$pos = strpos( $saved_file_name, "svgA" );
		if ($pos !== false) {
			// delete previously created avatar (if any)
			if ( ! empty( $saved_file_name ) ) {
				if ( $wp_filesystem->exists( $uploads_dir . "/" . $saved_file_name ) ) {
					$wp_filesystem->delete( $uploads_dir . "/" . $saved_file_name );
				}
			}
		}

		if ( $file["type"] === "png" ) {

			// User Profiles Made Easy has its own avatar "thumb" filename
			$full_avatar_name_and_path = $uploads_dir . "/" . $file_name . ".png";
			$thumb_avatar_name_and_path = $uploads_dir . "/" . $file_name . "_upme_thumb.png";

			// writing the "full" avatar
			if ( ! $wp_filesystem->put_contents( $full_avatar_name_and_path, $data ) ) {
				wp_die( "Line " . __LINE__ . ": " . "Error writing 'UPME full' avatar on a disk." );
			}

			$editor = wp_get_image_editor( $full_avatar_name_and_path );
			// resizing and writing the "-_upme_thumb" avatar
			if ( ! is_wp_error( $editor ) ) {
				$editor->resize( $upme_size_thumb, $upme_size_thumb, false );
				$editor->save( $thumb_avatar_name_and_path );
			} else {
				if ( ! $wp_filesystem->put_contents( $thumb_avatar_name_and_path, $data ) ) {
					wp_die( "Line " . __LINE__ . ": " . "Error writing '-_upme_thumb' avatar on a disk." );
				}
			}

			update_user_meta( $user_id, "user_pic", esc_url( $uploads_url . "/" . $file_name . ".png" ) );
			update_user_meta( $user_id, "upme_user_pic_thumb", esc_url( $uploads_url . "/" . $file_name . "_upme_thumb.png" ) );

			wp_die( "saved" );

		} elseif ( $file["type"] === "svg" ) {

			// User Profiles Made Easy has its own avatar "thumb" filename
			$full_avatar_name_and_path = $uploads_dir . "/" . $file_name . ".svg";
			$thumb_avatar_name_and_path = $uploads_dir . "/" . $file_name . "_upme_thumb.svg";

			// writing the "full" avatar
			if ( ! $wp_filesystem->put_contents( $full_avatar_name_and_path, $data ) ) {
				wp_die( "Line " . __LINE__ . ": " . "Error writing 'UPME full' avatar on a disk." );
			}

			// resizing SVG and writing the "-_upme_thumb" avatar
			$search_str = '/width="\d+px" height="\d+px"/';
			$replace_str = 'width="' . $upme_size_thumb . 'px" height="' . $upme_size_thumb . 'px"';
			$resized_data = preg_replace( $search_str, $replace_str, $data );
			if ( ! $wp_filesystem->put_contents( $thumb_avatar_name_and_path, $resized_data ) ) {
				wp_die( "Line " . __LINE__ . ": " . "Error writing '-_upme_thumb' avatar on a disk." );
			}

			update_user_meta( $user_id, "user_pic", esc_url( $uploads_url . "/" . $file_name . ".svg" ) );
			update_user_meta( $user_id, "upme_user_pic_thumb", esc_url( $uploads_url . "/" . $file_name . "_upme_thumb.svg" ) );

			wp_die( "saved" );

		} else {
			wp_die( "Line " . __LINE__ . ": " . "Received file type is not PNG or SVG." );
		}

		break;

	default:
		wp_die( "Line " . __LINE__ . ": " . "The wrong \"integration\" parameter. The passed value is \"{$options['integration']}\"." );
} // end of switch
}
add_action( "wp_ajax_nopriv_svgAvatars_save_avatar", "svgAvatars_save_avatar_not_logged_in_user" );

function svgAvatars_save_avatar_logged_in_user() {
	// getting and validating file name and image data from POST
// returned $file variable will be an array with ["name"] and ["type"]
$file = svgAvatars_validate_filename( $_POST["filename"] );
if ( $file["name"] === "invalid" ) {
	wp_die( "Line " . __LINE__ . ": " . "Received file name doesn't match required pattern." );
}
if ( $file["type"] === "invalid" ) {
	wp_die( "Line " . __LINE__ . ": " . "Received file type is not PNG or SVG." );
}

$file_name = $file["name"];

// common values
global $wp_filesystem;
// returned $data variable will be false if the data is extrinsical to PNG or SVG
$data =  svgAvatars_validate_imagedata( $_POST["imgdata"], $file["type"] );
$user_id = get_current_user_id();
$options = get_option( "svgAvatars_options" );
$uploads_dir_params = wp_get_upload_dir();
$uploads_dir = $uploads_dir_params["basedir"] . "/svg-avatars";
$uploads_url = $uploads_dir_params["baseurl"] . "/svg-avatars";

// PNG or SVG file format
if ( $file["type"] === "png" ) {
	// cheking that validated image data is not empty
	if ( $data == false ) {
		wp_die( "Line " . __LINE__ . ": " . "Received PNG or SVG file data is not valid." );
	}
	$data = base64_decode( $data );
	$ext = ".png";
} elseif ( $file["type"] === "svg" ) {
	$data = stripcslashes( $data );
	// cheking that validated code is SVG
	if ( strpos( $data, '<svg xmlns="http://www.w3.org/2000/svg" version="1.1"' ) === false || strrpos($data, "</svg>", -6) === false ) {
		wp_die( "Line " . __LINE__ . ": " . "Received PNG or SVG file data is not valid." );
	}
	$ext = ".svg";
} else {
	wp_die( "Line " . __LINE__ . ": " . "Received file type is not PNG or SVG." );
}

// Main condition for different type of integration
switch ( $options["integration"] ) {

	case "custom":
		/**
		* This section is for your own custom integration
		*/

		if ( ! $wp_filesystem->is_dir( $uploads_dir ) || ! $wp_filesystem->is_writable( $uploads_dir ) ) {
			wp_die( "Line " . __LINE__ . ": " . "The {$uploads_dir} directory does not exist or is not writable." );
		}

		// Generate a filename with the user identifier, timestamp, and a random component
		
		if(isset($_POST['parentUid'])) {
			$file_name = 'last-one';
			minime_save_avatar($_POST["imgdata"], $_POST['parentUid'], $_POST['childUid']);
		}

		// delete previously created avatar svg_avatar.{$ext} (if any)
		if ( $wp_filesystem->exists( $uploads_dir . "/" . $user_id . "/" . $file_name . $ext ) ) {
			$wp_filesystem->delete( $uploads_dir . "/" . $user_id . "/" . $file_name . $ext );
		}

		// save new avatar into the 'path_to/wp-content/uploads/svg-avatars/X/' directory,
		// where 'X' is an integer number equal to logged in user ID
		if ( ! $wp_filesystem->exists( $uploads_dir . "/" . $user_id ) ) {
			$wp_filesystem->mkdir( $uploads_dir . "/" . $user_id );
		}

		if ( ! $wp_filesystem->put_contents( $uploads_dir . "/" . $user_id . "/" . $file_name . $ext, $data ) ) {
			wp_die( "Line " . __LINE__ . ": " . "Error writing avatar on a disk." );
		}

		/**
		* You can place here an additional PHP code, for example, to store links of saved avatars
		* in your database. For example, please uncomment the line below to write the URL of
		* saved avatar into the database in 'usermeta' table as 'your_prefix_avatar' meta_key with
		* a desired prefix.
		*/
		

		
		// update_user_meta( $user_id, 'your_prefix_avatar', esc_url( $uploads_url . '/' . $user_id . '/' . $file_name . $ext ) );

		// avatar is saved, echo the result to show user a custom success message
		wp_die( "saved_custom" );

		break;

	case "local":
		/**
		* Locally saved avatars replace default Gravatars.
		* Also takes effect on integration with bbPress forum plugin.
		*/
		if ( ! $wp_filesystem->is_dir( $uploads_dir ) || ! $wp_filesystem->is_writable( $uploads_dir ) ) {
			wp_die( "Line " . __LINE__ . ": " . "The {$uploads_dir} directory does not exist or is not writable." );
		}

		// geting the previously saved file name
		$saved_file_name = get_user_meta( $user_id, "svgAvatars_avatar_url", true );
		$saved_file_name = basename( esc_url( $saved_file_name ) );

		// delete previously created avatar (if any)
		if ( ! empty( $saved_file_name ) ) {
			if ( $wp_filesystem->exists( $uploads_dir . "/" . $user_id . "/" . $saved_file_name ) ) {
				$wp_filesystem->delete( $uploads_dir . "/" . $user_id . "/" . $saved_file_name );
			}
		}

		// save new avatar into the 'path_to/wp-content/uploads/svg-avatars/X/' directory,
		// where 'X' is an integer number equal to logged in user ID
		if ( ! $wp_filesystem->exists( $uploads_dir . "/" . $user_id ) ) {
			$wp_filesystem->mkdir( $uploads_dir . "/" . $user_id );
		}

		if ( ! $wp_filesystem->put_contents( $uploads_dir . "/" . $user_id . "/" . $file_name . $ext, $data ) ) {
			wp_die( "Line " . __LINE__ . ": " . "Error writing avatar on a disk." );
		}

		// adding usermeta record with 'svgAvatars_avatar_url' meta_key
		update_user_meta( $user_id, "svgAvatars_avatar_url", esc_url( $uploads_url . "/" . $user_id . "/" . $file_name . $ext ) );

		wp_die( "saved" );

		break;

	case "BuddyBoss":
		/**
		* BuddyBoss is a fork of BuddyPress. It creates 'avatars' directory (hardcoded in the
		* bp-core/bp-core-avatars.php) in the 'path_to/wp-content/uploads/' one.
		* And it adds an integer number same as the unique user ID for name of subdirectory for each user.
		* BuddyBoss adds specific usermeta 'bp_profile_completion_widgets' meta_key into the database.
		* The function bp_core_xprofile_update_profile_completion_user_progress() does that.
		*/

		$human_name = '"BuddyBoss Platform plugin"';

		// check if BuddyBoss is active
		if ( ! function_exists( "bp_core_set_avatar_constants" ) || ! function_exists( "bp_core_xprofile_update_profile_completion_user_progress" ) ) {
			wp_die( "Line " . __LINE__ . ": " . "Integration error with {$human_name}. It looks like {$human_name} is not installed and/or activated." );
		}

		// BuddyBoss has its own avatar sizes
		if ( BP_AVATAR_FULL_WIDTH >= BP_AVATAR_FULL_HEIGHT ) {
			$bb_size_full = BP_AVATAR_FULL_WIDTH;
		} else {
			$bb_size_full = BP_AVATAR_FULL_HEIGHT;
		}
		if ( BP_AVATAR_THUMB_WIDTH >= BP_AVATAR_THUMB_HEIGHT ) {
			$bb_size_thumb = BP_AVATAR_THUMB_WIDTH;
		} else {
			$bb_size_thumb = BP_AVATAR_THUMB_HEIGHT;
		}

		// BuddyBoss has its own avatars upload directory
		$uploads_dir = bp_core_avatar_upload_path() . "/avatars";
		// if not exist, create this folder
		if ( ! $wp_filesystem->exists( $uploads_dir ) ) {
			$wp_filesystem->mkdir( $uploads_dir );
		}

		if ( ! $wp_filesystem->is_dir( $uploads_dir ) || ! $wp_filesystem->is_writable( $uploads_dir ) ) {
			wp_die( "Line " . __LINE__ . ": " . "The {$uploads_dir} directory does not exist or is not writable." );
		}

		bp_core_delete_existing_avatar( array("item_id" => $user_id, "object" => "user") );
		if ( ! $wp_filesystem->exists( $uploads_dir . "/" . $user_id ) ) {
			$wp_filesystem->mkdir( $uploads_dir . "/" . $user_id );
		}

		if ( $file["type"] === "png" ) {

			// BuddyBoss has its own avatar filenames
			$full_avatar_name_and_path = $uploads_dir . "/" . $user_id . "/" . $file_name . "-bpfull" . ".png";
			$thumb_avatar_name_and_path = $uploads_dir . "/" . $user_id . "/" . $file_name . "-bpthumb" . ".png";

			// writing the "-bpfull" avatar
			if ( ! $wp_filesystem->put_contents( $full_avatar_name_and_path, $data ) ) {
				wp_die( "Line " . __LINE__ . ": " . "Error writing '-bpfull' avatar on a disk." );
			}

			$editor = wp_get_image_editor( $full_avatar_name_and_path );
			// resizing and writing the "-bpthumb" avatar
			if ( ! is_wp_error( $editor ) ) {
				$editor->resize( $bb_size_thumb, $bb_size_thumb, false );
				$editor->save( $thumb_avatar_name_and_path );
			} else {
				if ( ! $wp_filesystem->put_contents( $thumb_avatar_name_and_path, $data ) ) {
					wp_die( "Line " . __LINE__ . ": " . "Error writing '-bpthumb' avatar on a disk." );
				}
			}

			// updating 'bp_profile_completion_widgets' usermeta
			bp_core_xprofile_update_profile_completion_user_progress();

			wp_die( "saved" );

		} elseif ( $file["type"] === "svg" ) {

			// BuddyBoss has its own avatar filenames
			$full_avatar_name_and_path = $uploads_dir . "/" . $user_id . "/" . $file_name . "-bpfull" . ".svg";
			$thumb_avatar_name_and_path = $uploads_dir . "/" . $user_id . "/" . $file_name . "-bpthumb" . ".svg";

			// writing the "-bpfull" avatar
			if ( ! $wp_filesystem->put_contents( $full_avatar_name_and_path, $data ) ) {
				wp_die( "Line " . __LINE__ . ": " . "Error writing '-bpfull' avatar on a disk." );
			}

			// resizing SVG and writing the "-bpthumb" avatar
			$search_str = 'version="1.1" width="' . $bb_size_full . 'px" height="' . $bb_size_full . 'px"';
			$replace_str = 'version="1.1" width="' . $bb_size_thumb . 'px" height="' . $bb_size_thumb . 'px"';
			$data = str_replace( $search_str, $replace_str, $data );
			if ( ! $wp_filesystem->put_contents( $thumb_avatar_name_and_path, $data ) ) {
				wp_die( "Line " . __LINE__ . ": " . "Error writing '-bpthumb' avatar on a disk." );
			}

			// updating 'bp_profile_completion_widgets' usermeta
			bp_core_xprofile_update_profile_completion_user_progress();

			wp_die( "saved" );

		} else {
			wp_die( "Line " . __LINE__ . ": " . "Received file type is not PNG or SVG." );
		}

		break;

	case "BuddyPress":
		/**
		* In general, BuddyPress creates 'avatars' directory (hardcoded in the
		* bp-core/bp-core-avatars.php) in the 'path_to/wp-content/uploads/' one.
		* And it adds an integer number same as the unique user ID for name of subdirectory for each user.
		* BuddyPress doesn't add any avatar data into the database.
		*/

		$human_name = '"BuddyPress plugin"';

		// check if BuddyPress is active
		if ( ! function_exists( "bp_core_set_avatar_constants" ) ) {
			wp_die( "Line " . __LINE__ . ": " . "Integration error with {$human_name}. It looks like {$human_name} is not installed and/or activated." );
		}

		// BuddyPress has its own avatar sizes
		if ( BP_AVATAR_FULL_WIDTH >= BP_AVATAR_FULL_HEIGHT ) {
			$bp_size_full = BP_AVATAR_FULL_WIDTH;
		} else {
			$bp_size_full = BP_AVATAR_FULL_HEIGHT;
		}
		if ( BP_AVATAR_THUMB_WIDTH >= BP_AVATAR_THUMB_HEIGHT ) {
			$bp_size_thumb = BP_AVATAR_THUMB_WIDTH;
		} else {
			$bp_size_thumb = BP_AVATAR_THUMB_HEIGHT;
		}

		// BuddyPress has its own avatars upload directory
		$uploads_dir = bp_core_avatar_upload_path() . "/avatars";
		// if not exist, create this folder
		if ( ! $wp_filesystem->exists( $uploads_dir ) ) {
			$wp_filesystem->mkdir( $uploads_dir );
		}

		if ( ! $wp_filesystem->is_dir( $uploads_dir ) || ! $wp_filesystem->is_writable( $uploads_dir ) ) {
			wp_die( "Line " . __LINE__ . ": " . "The {$uploads_dir} directory does not exist or is not writable." );
		}

		bp_core_delete_existing_avatar( array("item_id" => $user_id, "object" => "user") );
		if ( ! $wp_filesystem->exists( $uploads_dir . "/" . $user_id ) ) {
			$wp_filesystem->mkdir( $uploads_dir . "/" . $user_id );
		}

		if ( $file["type"] === "png" ) {

			// BuddyPress has its own avatar filenames
			$full_avatar_name_and_path = $uploads_dir . "/" . $user_id . "/" . $file_name . "-bpfull" . ".png";
			$thumb_avatar_name_and_path = $uploads_dir . "/" . $user_id . "/" . $file_name . "-bpthumb" . ".png";

			// writing the "-bpfull" avatar
			if ( ! $wp_filesystem->put_contents( $full_avatar_name_and_path, $data ) ) {
				wp_die( "Line " . __LINE__ . ": " . "Error writing '-bpfull' avatar on a disk." );
			}

			$editor = wp_get_image_editor( $full_avatar_name_and_path );
			// resizing and writing the "-bpthumb" avatar
			if ( ! is_wp_error( $editor ) ) {
				$editor->resize( $bp_size_thumb, $bp_size_thumb, false );
				$editor->save( $thumb_avatar_name_and_path );
			} else {
				if ( ! $wp_filesystem->put_contents( $thumb_avatar_name_and_path, $data ) ) {
					wp_die( "Line " . __LINE__ . ": " . "Error writing '-bpthumb' avatar on a disk." );
				}
			}
			wp_die( "saved" );

		} elseif ( $file["type"] === "svg" ) {

			// BuddyPress has its own avatar filenames
			$full_avatar_name_and_path = $uploads_dir . "/" . $user_id . "/" . $file_name . "-bpfull" . ".svg";
			$thumb_avatar_name_and_path = $uploads_dir . "/" . $user_id . "/" . $file_name . "-bpthumb" . ".svg";

			// writing the "-bpfull" avatar
			if ( ! $wp_filesystem->put_contents( $full_avatar_name_and_path, $data ) ) {
				wp_die( "Line " . __LINE__ . ": " . "Error writing '-bpfull' avatar on a disk." );
			}

			// resizing SVG and writing the "-bpthumb" avatar
			$search_str = 'version="1.1" width="' . $bp_size_full . 'px" height="' . $bp_size_full . 'px"';
			$replace_str = 'version="1.1" width="' . $bp_size_thumb . 'px" height="' . $bp_size_thumb . 'px"';
			$data = str_replace( $search_str, $replace_str, $data );
			if ( ! $wp_filesystem->put_contents( $thumb_avatar_name_and_path, $data ) ) {
				wp_die( "Line " . __LINE__ . ": " . "Error writing '-bpthumb' avatar on a disk." );
			}

			wp_die( "saved" );

		} else {
			wp_die( "Line " . __LINE__ . ": " . "Received file type is not PNG or SVG." );
		}

		break;

	case "LearnPress":
		/**
		* In general, LearnPress creates 'learn-press-profile' directory (hard-coded in the
		* lp-user-functions.php) in the 'path_to/wp-content/uploads/' one. It also adds an integer
		* number equal to unique user ID as a name of subdirectory for each user. After that it
		* saves an avatar with random file name in that directory and saves the part of URL
		* (learn-press-profile/X/random_name.jpg) into the database in the 'usermeta' table as
		* '_lp_profile_picture' meta_key. It also updates another '_lp_profile_picture_changed'
		* meta_key with 'yes' value.
		*/

		$human_name = '"LearnPress plugin"';

		if ( ! function_exists( "learn_press_update_user_profile_avatar" ) ) {
			wp_die( "Line " . __LINE__ . ": " . "Integration error with {$human_name}. It looks like {$human_name} is not installed and/or activated." );
		}

		// LearnPress has its own avatars upload directory
		$uploads_dir = $uploads_dir_params["basedir"] . "/learn-press-profile";
		if ( ! $wp_filesystem->exists( $uploads_dir ) ) {
			$wp_filesystem->mkdir( $uploads_dir );
		}

		if ( ! $wp_filesystem->is_dir( $uploads_dir ) || ! $wp_filesystem->is_writable( $uploads_dir ) ) {
			wp_die( "Line " . __LINE__ . ": " . "The {$uploads_dir} directory does not exist or is not writable." );
		}

		// geting the previously saved file name
		$saved_file_name = get_user_meta( $user_id, "_lp_profile_picture", true );
		$saved_file_name = basename( esc_url( $saved_file_name ) );

		// delete previously created avatar (if any)
		if ( ! empty( $saved_file_name ) ) {
			if ( $wp_filesystem->exists( $uploads_dir . "/" . $user_id . "/" . $saved_file_name ) ) {
				$wp_filesystem->delete( $uploads_dir . "/" . $user_id . "/" . $saved_file_name );
			}
		}

		// save new avatar into the 'path_to/wp-content/uploads/learn-press-profile/X/' directory,
		// where 'X' is an integer number equal to logged in user ID
		if ( ! $wp_filesystem->exists( $uploads_dir . "/" . $user_id ) ) {
			$wp_filesystem->mkdir( $uploads_dir . "/" . $user_id );
		}

		if ( ! $wp_filesystem->put_contents( $uploads_dir . "/" . $user_id . "/" . $file_name . $ext, $data ) ) {
			wp_die( "Line " . __LINE__ . ": " . "Error writing avatar on a disk." );
		}

		// adding usermeta record with '_lp_profile_picture' meta_key
		update_user_meta( $user_id, "_lp_profile_picture", "learn-press-profile/" . $user_id . "/" . $file_name . $ext );
		update_user_meta( $user_id, '_lp_profile_picture_changed', 'yes' );

		wp_die( "saved" );

		break;

	case "UltimateMember":
		/**
		* In general, Ultimate Member creates 'ultimatemember' directory in the
		* 'path_to/wp-content/uploads/' one. And it adds an integer number same as the unique
		* user ID for name of subdirectory for each user.
		* When user upload a profile picture, the plugin creates and saves several images with
		* different sizes, which are get from 'photo_thumb_sizes' option. After that it saves
		* in 'usermeta' table of the database the 'profile_photo.{extention}' value of
		* 'profile_photo' meta_key.
		* Also it changes value of 'um_member_directory_data' meta_key with 'profile_photo'
		* boolean 'true' value.
		*/

		$human_name = '"Ultimate Member plugin"';

		if ( ! class_exists( "UM" ) || ! class_exists( "um\core\User" ) ) {
			wp_die( "Line " . __LINE__ . ": " . "Integration error with {$human_name}. It looks like {$human_name} is not installed and/or activated." );
		}

		$UM_avatar_sizes = UM()->options()->get( "photo_thumb_sizes" );
		$user = new um\core\User();

		// Ultimate Member has its own avatars upload directory
		$uploads_dir = $uploads_dir_params["basedir"] . "/ultimatemember";
		// if not exist, create this folder
		if ( ! $wp_filesystem->exists( $uploads_dir ) ) {
			$wp_filesystem->mkdir( $uploads_dir );
		}

		if ( ! $wp_filesystem->is_dir( $uploads_dir ) || ! $wp_filesystem->is_writable( $uploads_dir ) ) {
			wp_die( "Line " . __LINE__ . ": " . "The {$uploads_dir} directory does not exist or is not writable." );
		}

		// create, if necessary, individual directory for logged in user
		if ( ! $wp_filesystem->exists( $uploads_dir . "/" . $user_id ) ) {
			$wp_filesystem->mkdir( $uploads_dir . "/" . $user_id );
		} else {
			// delete all previously created avatars, if any
			$old_files = glob( $uploads_dir . "/" . $user_id . "/*" ); // get all file names
			foreach( $old_files as $old_file ) {
				if( $wp_filesystem->is_file( $old_file ) ) {
					$wp_filesystem->delete( $old_file );
				}
			}
		}

		// writing the original sized avatar
		$original_sized_avatar = $uploads_dir . "/" . $user_id . "/" . "profile_photo.{$file['type']}";
		if ( ! $wp_filesystem->put_contents( $original_sized_avatar, $data ) ) {
			wp_die( "Line " . __LINE__ . ": " . "Error writing avatar on a disk." );
		}

		update_user_meta( $user_id, "profile_photo", "profile_photo.{$file['type']}" );

		if ( $file["type"] === "png" ) {

			// Ultimate Member has its own avatar sizes
			if ( is_array( $UM_avatar_sizes ) ) {
				// resizing and writing each "sized" avatar
				foreach ( $UM_avatar_sizes as $size ) {
					if ( is_numeric( $size ) ) {
						$editor = wp_get_image_editor( $original_sized_avatar );
						$sized_avatar = $uploads_dir . "/" . $user_id . "/" . "profile_photo-{$size}x{$size}.png";
						if ( ! is_wp_error( $editor ) ) {
							$editor->resize( $size, $size, false );
							$editor->save( $sized_avatar );
						} else {
							if ( ! $wp_filesystem->put_contents( $sized_avatar, $data ) ) {
								wp_die( "Line " . __LINE__ . ": " . "Error writing avatar on a disk." );
							}
						}
					}
				}
			}

			$user->on_update_usermeta( null, $user_id, "profile_photo", "profile_photo.png" );
			// and clear the user cache
			$user->remove_cache( $user_id );

			wp_die( "saved" );

		} elseif ( $file["type"] === "svg" ) {

			// Ultimate Member has its own avatar sizes
			if ( is_array( $UM_avatar_sizes ) ) {
				// resizing and writing each "sized" avatar
				foreach ( $UM_avatar_sizes as $size ) {
					if ( is_numeric( $size ) ) {
						$sized_avatar = $uploads_dir . "/" . $user_id . "/" . "profile_photo-{$size}x{$size}.svg";
						// resizing SVG
						$search_str = '/width="\d+px" height="\d+px"/';
						$replace_str = 'width="' . $size . 'px" height="' . $size . 'px"';
						$resized_data = preg_replace( $search_str, $replace_str, $data );
						if ( ! $wp_filesystem->put_contents( $sized_avatar, $resized_data ) ) {
							wp_die( "Line " . __LINE__ . ": " . "Error writing avatar on a disk." );
						}
					}
				}
			}

			$user->on_update_usermeta( null, $user_id, "profile_photo", "profile_photo.svg" );
			// and clear the user cache
			$user->remove_cache( $user_id );

			wp_die( "saved" );

		} else {
			wp_die( "Line " . __LINE__ . ": " . "Received file type is not PNG or SVG." );
		}

		break;

	case "UltimateMembershipPro":
		/**
		* In general, Ultimate Membership Pro creates a new post with 'attachment' type for
		* every new uploaded image as avatar which is saved in YEAR/MONTH/ directory in the
		* 'path_to/wp-content/uploads/' one. After that the plugin saves into the database
		* that post ID in the 'usermeta' table as 'ihc_avatar' meta_key.
		* However, the direct URL can also be fetched from the database by this plugin, so
		* we may simply save the new URL into 'ihc_avatar' meta_value.
		*/

		$human_name = '"Ultimate Membership Pro plugin"';

		// check if Ultimate Membership Pro is active
		if ( ! function_exists( "ihc_get_avatar_for_uid" ) ) {
			wp_die( "Line " . __LINE__ . ": " . "Integration error with {$human_name}. It looks like {$human_name} is not installed and/or activated." );
		}

		if ( ! $wp_filesystem->is_dir( $uploads_dir ) || ! $wp_filesystem->is_writable( $uploads_dir ) ) {
			wp_die( "Line " . __LINE__ . ": " . "The {$uploads_dir} directory does not exist or is not writable." );
		}

		if ( ! $wp_filesystem->exists( $uploads_dir . "/" . $user_id ) ) {
			$wp_filesystem->mkdir( $uploads_dir . "/" . $user_id );
		}

		// geting the previously saved file name
		$saved_file_name = get_user_meta( $user_id, "ihc_avatar", true );
		$saved_file_name = basename( esc_url( $saved_file_name ) );

		// delete previously created avatar (if any)
		if ( ! empty( $saved_file_name ) ) {
			if ( $wp_filesystem->exists( $uploads_dir . "/" . $user_id . "/" . $saved_file_name ) ) {
				$wp_filesystem->delete( $uploads_dir . "/" . $user_id . "/" . $saved_file_name );
			}
		}

		// replace "svgA" from file name with "ihc"
		$file_name = str_replace( "svgA", "ihc", $file_name );

		// save new avatar into the 'path_to/wp-content/uploads/svg-avatars/X/' directory,
		// where 'X' is an integer number equal to logged in user ID and update usermeta
		if ( ! $wp_filesystem->put_contents( $uploads_dir . "/" . $user_id . "/" . $file_name . $ext, $data ) ) {
			wp_die( "Line " . __LINE__ . ": " . "Error writing avatar on a disk." );
		}

		update_user_meta( $user_id, "ihc_avatar", esc_url( $uploads_url . "/" . $user_id . "/" . $file_name . $ext ) );

		wp_die( "saved" );

		break;

	case "UserPro":
		/*
		* In general, UserPro creates 'userpro' directory in the 'path_to/wp-content/uploads/' one.
		* And it adds an integer number same as the unique user ID for name of subdirectory for each user.
		* UserPro adds avatar URL into the database in the 'usermeta' table as 'profilepicture' meta_key.
		*/

		$human_name = '"UserPro plugin"';

		// check if UserPro is active
		if ( ! class_exists( "UserPro" ) ) {
			wp_die( "Line " . __LINE__ . ": " . "Integration error with {$human_name}. It looks like {$human_name} is not installed and/or activated." );
		}

		global $userpro;

		// UserPro has its own avatars upload directory
		$userpro->do_uploads_dir( $user_id );
		$uploads_dir = $userpro->get_uploads_dir($user_id);
		if ( ! $wp_filesystem->is_dir( $uploads_dir ) || ! $wp_filesystem->is_writable( $uploads_dir ) ) {
			wp_die( "Line " . __LINE__ . ": " . "The {$uploads_dir} directory does not exist or is not writable." );
		}

		// geting the previously saved file name
		$saved_file_name = get_user_meta( $user_id, "profilepicture", true );
		$saved_file_name = basename( esc_url( $saved_file_name ) );

		// delete previously created avatar (if any)
		if ( ! empty( $saved_file_name ) ) {
			if ( $wp_filesystem->exists( $uploads_dir . $saved_file_name ) ) {
				$wp_filesystem->delete( $uploads_dir . $saved_file_name );
			}
		}

		// save new file and update usermeta
		if ( ! $wp_filesystem->put_contents( $uploads_dir . $file_name . $ext, $data ) ) {
			wp_die( "Line " . __LINE__ . ": " . "Error writing avatar on a disk." );
		}

		update_user_meta( $user_id, "profilepicture", esc_url( $userpro->get_uploads_url($user_id) . $file_name . $ext ) );

		wp_die( "saved" );

		break;

	case "UPME":
		/**
		* In general, User Profiles Made Easy creates 'upme' directory in the
		* 'path_to/wp-content/uploads/' one. It saves avatars of all users in it without
		* separation.
		* UPME adds avatar URL into the database in the 'usermeta' table as 'user_pic' and
		* 'upme_user_pic_thumb' meta_keys.
		*/

		$human_name = '"User Profiles Made Easy plugin"';

		// check if User Profiles Made Easy is active
		if ( ! class_exists( "UPME" ) ) {
			wp_die( "Line " . __LINE__ . ": " . "Integration error with {$human_name}. It looks like {$human_name} is not installed and/or activated." );
		}

		global $upme_options;

		// User Profiles Made Easy has its own avatar thumbnail size
		if ( $upme_options->upme_settings["profile_image_resize_width"] && $upme_options->upme_settings["profile_image_resize_height"] ) {
			$upme_ava_w = intval( $upme_options->upme_settings["profile_image_resize_width"] );
			$upme_ava_h = intval( $upme_options->upme_settings["profile_image_resize_height"] );
			if ( $upme_ava_w < 100 ) { $upme_ava_w = 100; }
			if ( $upme_ava_h < 100 ) { $upme_ava_h = 100; }
			if ( $upme_ava_w >= $upme_ava_h ) {
				$upme_size_thumb = $upme_ava_w;
			} else {
				$upme_size_thumb = $upme_ava_h;
			}
		} else {
			$upme_size_thumb = 100;
		}

		// User Profiles Made Easy has its own avatars upload directory
		$uploads_dir = $uploads_dir_params["basedir"] . "/upme";
		$uploads_url = $uploads_dir_params["baseurl"] . "/upme";
		// if not exist, create this folder
		if ( ! $wp_filesystem->exists( $uploads_dir ) ) {
			$wp_filesystem->mkdir( $uploads_dir );
		}

		if ( ! $wp_filesystem->is_dir( $uploads_dir ) || ! $wp_filesystem->is_writable( $uploads_dir ) ) {
			wp_die( "Line " . __LINE__ . ": " . "The {$uploads_dir} directory does not exist or is not writable." );
		}

		// geting the previously saved full file name
		$saved_file_name = get_user_meta( $user_id, "user_pic", true );
		$saved_file_name = basename( esc_url( $saved_file_name ) );
		// only files created by avatar generator will be deleted
		$pos = strpos( $saved_file_name, "svgA" );
		if ($pos !== false) {
			// delete previously created avatar (if any)
			if ( ! empty( $saved_file_name ) ) {
				if ( $wp_filesystem->exists( $uploads_dir . "/" . $saved_file_name ) ) {
					$wp_filesystem->delete( $uploads_dir . "/" . $saved_file_name );
				}
			}
		}

		// geting the previously saved thumb file name
		$saved_file_name = get_user_meta( $user_id, "upme_user_pic_thumb", true );
		$saved_file_name = basename( esc_url( $saved_file_name ) );
		// only files created by avatar generator will be deleted
		$pos = strpos( $saved_file_name, "svgA" );
		if ($pos !== false) {
			// delete previously created avatar (if any)
			if ( ! empty( $saved_file_name ) ) {
				if ( $wp_filesystem->exists( $uploads_dir . "/" . $saved_file_name ) ) {
					$wp_filesystem->delete( $uploads_dir . "/" . $saved_file_name );
				}
			}
		}

		if ( $file["type"] === "png" ) {

			// User Profiles Made Easy has its own avatar "thumb" filename
			$full_avatar_name_and_path = $uploads_dir . "/" . $file_name . ".png";
			$thumb_avatar_name_and_path = $uploads_dir . "/" . $file_name . "_upme_thumb.png";

			// writing the "full" avatar
			if ( ! $wp_filesystem->put_contents( $full_avatar_name_and_path, $data ) ) {
				wp_die( "Line " . __LINE__ . ": " . "Error writing 'UPME full' avatar on a disk." );
			}

			$editor = wp_get_image_editor( $full_avatar_name_and_path );
			// resizing and writing the "-_upme_thumb" avatar
			if ( ! is_wp_error( $editor ) ) {
				$editor->resize( $upme_size_thumb, $upme_size_thumb, false );
				$editor->save( $thumb_avatar_name_and_path );
			} else {
				if ( ! $wp_filesystem->put_contents( $thumb_avatar_name_and_path, $data ) ) {
					wp_die( "Line " . __LINE__ . ": " . "Error writing '-_upme_thumb' avatar on a disk." );
				}
			}

			update_user_meta( $user_id, "user_pic", esc_url( $uploads_url . "/" . $file_name . ".png" ) );
			update_user_meta( $user_id, "upme_user_pic_thumb", esc_url( $uploads_url . "/" . $file_name . "_upme_thumb.png" ) );

			wp_die( "saved" );

		} elseif ( $file["type"] === "svg" ) {

			// User Profiles Made Easy has its own avatar "thumb" filename
			$full_avatar_name_and_path = $uploads_dir . "/" . $file_name . ".svg";
			$thumb_avatar_name_and_path = $uploads_dir . "/" . $file_name . "_upme_thumb.svg";

			// writing the "full" avatar
			if ( ! $wp_filesystem->put_contents( $full_avatar_name_and_path, $data ) ) {
				wp_die( "Line " . __LINE__ . ": " . "Error writing 'UPME full' avatar on a disk." );
			}

			// resizing SVG and writing the "-_upme_thumb" avatar
			$search_str = '/width="\d+px" height="\d+px"/';
			$replace_str = 'width="' . $upme_size_thumb . 'px" height="' . $upme_size_thumb . 'px"';
			$resized_data = preg_replace( $search_str, $replace_str, $data );
			if ( ! $wp_filesystem->put_contents( $thumb_avatar_name_and_path, $resized_data ) ) {
				wp_die( "Line " . __LINE__ . ": " . "Error writing '-_upme_thumb' avatar on a disk." );
			}

			update_user_meta( $user_id, "user_pic", esc_url( $uploads_url . "/" . $file_name . ".svg" ) );
			update_user_meta( $user_id, "upme_user_pic_thumb", esc_url( $uploads_url . "/" . $file_name . "_upme_thumb.svg" ) );

			wp_die( "saved" );

		} else {
			wp_die( "Line " . __LINE__ . ": " . "Received file type is not PNG or SVG." );
		}

		break;

	default:
		wp_die( "Line " . __LINE__ . ": " . "The wrong \"integration\" parameter. The passed value is \"{$options['integration']}\"." );
} // end of switch
}
add_action( "wp_ajax_svgAvatars_save_avatar", "svgAvatars_save_avatar_logged_in_user" );
?>