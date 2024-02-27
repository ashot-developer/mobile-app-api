<?php
// Hook into the 'wp_trash_post' or 'before_delete_post' action
add_action('wp_trash_post', 'send_data_to_external_api');
add_action('before_delete_post', 'send_data_to_external_api');

function send_data_to_external_api($post_id) {
    $post_type = get_post_type($post_id);
    if ('coupon_post_type' !== $post_type) {
        return;
    }

    $post_data = get_post($post_id);

    $requestBody = array(
        'id'    => $post_id,
    );

    $args = array(
		'body' => $requestBody
	);
	
	$response = wp_remote_post('https://http-coupons-remove-7vxnir2s7q-uc.a.run.app', $args);
	
	if (is_wp_error($response)) {
		echo 'Error: ' . $response->get_error_message();
	} else {
		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);
	}
}

// Add the action hook with your callback function
add_action('untrash_post', 'custom_untrash_post_action', 10, 1);

function custom_untrash_post_action($post_id) {
    $post_type = get_post_type($post_id);
    if ('coupon_post_type' !== $post_type) {
        return;
    }

    $post_data = get_post($post_id);

    $requestBody = array(
        'id'    => $post_id,
        'isActive' => true
    );

    $args = array(
		'body' => $requestBody
	);
	
	$response = wp_remote_post('https://http-coupons-remove-7vxnir2s7q-uc.a.run.app', $args);
	
	if (is_wp_error($response)) {
		echo 'Error: ' . $response->get_error_message();
	} else {
		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);
	}
}
