<?php
// If this file is called directly, abort.
if ( ! defined( "ABSPATH" ) ) {
	exit;
}

// replace Gravatars with locally saved avatars
class svgAvatarsLocalAvatarsCLass {

	private $meta_key = "svgAvatars_avatar_url";

	public function __construct() {
		add_filter( "get_avatar" , array( $this, "return_avatar" ), 1, 5 );
	}

	public function return_avatar( $avatar="", $id_or_email="", $size, $default, $alt ) {
		$user = false;
		// we need to check for ID and email and object
		if ( is_numeric( $id_or_email ) ) {
			$id = (int) $id_or_email;
			$user = get_user_by( "id" , $id );
		} elseif ( is_object( $id_or_email ) ) {
			if ( ! empty( $id_or_email->user_id ) ) {
				$id = (int) $id_or_email->user_id;
				$user = get_user_by( "id" , $id );
			}
		} else {
			$user = get_user_by( "email", $id_or_email );
		}

		// user exists
		if ( $user && is_object( $user ) ) {
			if ( isset( $user->data->ID ) ) {
				$avatar_meta = get_user_meta($user->data->ID, $this->meta_key, true);
				if ( ! empty( $avatar_meta ) ) {
					if ( strpos( $avatar_meta, "http" ) === 0 ) {
						// add random value to prevent new avatar from browser cache
						$rnd = rand(10000, 20000);
						$avatar_url = $avatar_meta . '?' . $rnd;
					}
				}
				if ( ! empty( $avatar_url ) ) {
					$size = esc_attr( $size );
					$avatar_url = esc_attr( $avatar_url );
					$alt = esc_attr( $alt );
					$avatar = "<img class='avatar avatar-{$size} photo' src='{$avatar_url}' height='{$size}' width='{$size}' alt='{$alt}'/>";
				}
			}
		}
		return $avatar;
	}
}

// show avatars generator on user profile page
function svgAvatars_show_in_backend() {
	$options = get_option( "svgAvatars_options" );
  ?>
    <script type="text/javascript">
        window.jQuery(document).ready(function($) {
			$('.user-profile-picture').after('<tr class="user-profile-avatar-generator"><th><?php esc_html_e( "Create your new avatar", "svg-avatars-generator" ) ?></th><td><div id="svgAvatars"></div><p class="description"><?php if ($options["integration"] !== "none") {esc_html_e( "Please reload this page after saving your new avatar to see it as \"Profile Picture\".", "svg-avatars-generator" );}  ?></p></td></tr>');
		});
    </script>
  <?php
}

