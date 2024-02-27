<?php
require 'add-edit-coupons.php';
require 'delete-coupons.php';
// Registration custom post type
function create_coupon_post_type() {
    register_post_type('coupon_post_type', array(
        'labels' => array(
            'name' => __('קופונים'),
            'singular_name' => __('Minime Coupon'),
        ),
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'thumbnail', 'custom-fields'),
		'menu_icon' => 'dashicons-tag',
        'menu_position' => 20
    ));
}
add_action('init', 'create_coupon_post_type');

// Add a new column to the admin dashboard for custom post type
function custom_post_type_columns($columns) {

	$new_columns = array(
		'coupon_id' => 'מזהה קופון',
        'coupon_image' => 'תמונה',
		'lat' => 'Lat',
		'lng' => 'Long'
    );

    $date_column_position = array_search('date', array_keys($columns));
    $columns = array_merge(array_slice($columns, 0, $date_column_position), $new_columns, array_slice($columns, $date_column_position));

    return $columns;
}

add_filter('manage_coupon_post_type_posts_columns', 'custom_post_type_columns');

// Display content in the new column
function custom_post_type_custom_column($column, $post_id) {
	$couponData = get_field('coupon_data', $post_id);
    if ($column === 'coupon_image') {
       
	$couponImg = $couponData['coupon_image']['url'];
	
        echo "<img src='$couponImg'>";
    } elseif($column === 'lat') {
		$lat = $couponData['lat'];
        echo "<strong>$lat</strong>";
	} elseif($column === 'lng') {
		$lng = $couponData['lng'];
        echo "<strong>$lng</strong>";
	} elseif($column === 'coupon_id') {
        echo "<strong>$post_id</strong>";
	}
}

add_action('manage_coupon_post_type_posts_custom_column', 'custom_post_type_custom_column', 10, 2);


add_action( 'parse_request', 'cdxn_search_by_id' );
function cdxn_search_by_id( $wp ) {
    global $pagenow;
    if( !is_admin() && isset($_GET['post_type']) && 'edit.php' != $pagenow && 'coupon_post_type' !== $_GET['post_type']) {
		return;
	}
        
    if( !isset( $wp->query_vars['s'] ) ) {
		return;
	}
        
    $id = absint( substr( $wp->query_vars['s'], 0 ) );
    if( !$id ) {
		return; 
	}
        
    unset( $wp->query_vars['s'] );
    $wp->query_vars['p'] = $id;
}