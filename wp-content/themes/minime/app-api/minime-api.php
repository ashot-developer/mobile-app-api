<?php
require 'router/router.php';

// On confirmation save terms and conditions into firebase
if (!function_exists('minime_save_terms')) {
    function minime_save_terms($data) {
        $terms_and_conditions_text = get_field('terms_and_conditions', 'option');
        $termsID = $terms_and_conditions_text->ID;
        $uID = $data['childUid'];
        $post = get_post($termsID);
    
        if( empty( $post ) ) {
            return null;
        }
        $postText = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", strip_tags($post->post_content));
        $args = array(
            'body' => array(
                'childUid' => $uID,
                'id' => $termsID,
                'text' => $postText
            ),
        );
        
        $response = wp_remote_post('https://tac-7vxnir2s7q-uc.a.run.app', $args);
        
        if (is_wp_error($response)) {
            echo 'Error: ' . $response->get_error_message();
        } else {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            wp_send_json_success($body);
        }
    }
}


// Save avatar by parent id and child id
if (!function_exists('minime_save_avatar')) {
    function minime_save_avatar($imageBase64, $parentID, $childID) {
        $args = array(
            'body' => array(
                'childUid' => $childID,
                'parentUid' => $parentID,
                'image' => $imageBase64,
            ),
        );
        
        $response = wp_remote_post('https://avatar-7vxnir2s7q-uc.a.run.app', $args);
        
        if (is_wp_error($response)) {
            echo 'Error: ' . $response->get_error_message();
        } else {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
        }
    }
}


require_once 'coupons/coupons.php';
require_once 'users/users.php';
require_once 'chat/chat.php';

function enqueue_admin_custom_css() {
    wp_enqueue_style( 'admin-custom', get_stylesheet_directory_uri() . '/assets/css/minime.css', array(), rand(11,9999), 'all' );
}
add_action( 'admin_enqueue_scripts', 'enqueue_admin_custom_css' );