<?php
define('WP_USE_THEMES', false);

$wp_load_path = $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';

if (file_exists($wp_load_path)) {
    require_once($wp_load_path);
} else {
    die('wp-load.php not found. Path: ' . $wp_load_path);
}


if (isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];

    $action = $_POST['user_blocked'] ? 0 : 1;
    $msg = $_POST['user_blocked'] ? 'בוטלה החסימה של המשתמש' : 'המשתמש נחסם';

    $api_endpoint = 'https://http-user-block-mlyjb3tq5a-uc.a.run.app';

    $api_data = array(
        'userUid' => $user_id,
        'isBlocked' => $action,
    );
    
    $response = wp_remote_post($api_endpoint, array(
        'body' => $api_data,
    ));

    if (is_wp_error($response)) {
        echo 'Error sending request to the API: ' . $response->get_error_message();
    } else {
        $api_response = $response;

        if ($api_response) {
            wp_redirect(add_query_arg('success', $msg, wp_get_referer()));
            exit();
        } else {
            echo 'API request was not successful. Check the API response.';
        }
    }
}
