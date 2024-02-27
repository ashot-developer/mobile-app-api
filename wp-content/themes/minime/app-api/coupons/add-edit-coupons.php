<?php

function save_post_callback($id, $api_url){
	$couponData = get_field('coupon_data', $id);
	$couponID = $id;
	$couponImg = $couponData['coupon_image']['url'];
	$lat = $couponData['lat'];
	$lng = $couponData['lng'];
	$imageData = file_get_contents($couponImg);
	$base64Image = base64_encode($imageData);

	$requestBody = [
		'id' => $couponID,
		'image' => $base64Image,
		'lat' => $lat,
		'lng' => $lng
	];

	$args = array(
		'body' => $requestBody
	);
	
	$response = wp_remote_post($api_url, $args);
	
	if (is_wp_error($response)) {
		echo 'Error: ' . $response->get_error_message();
	} else {
		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);
	}
}

add_action('save_post', 'send_custom_post_data_to_external_api', 10, 3);

function send_custom_post_data_to_external_api($post_id, $post, $update) {
	global $post;
    if (isset($post) && $post->post_type === 'coupon_post_type') {
	
        if (!$update) {
			save_post_callback($post->ID, 'https://http-coupons-add-7vxnir2s7q-uc.a.run.app');
        } else {
			save_post_callback($post->ID, 'https://http-coupons-add-7vxnir2s7q-uc.a.run.app');
		}
    }
}