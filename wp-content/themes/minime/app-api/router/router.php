<?php 
require 'routes.php';

add_action( 'rest_api_init', function () {
    $terms = ROUTES['terms'];
    register_rest_route( $terms['app'], $terms['params'], $terms['termsCM'] );
} );