<?php
if(!function_exists('fetch_api_data')) {
    function fetch_api_data() {
        $api_endpoint = 'https://http-children-getall-mlyjb3tq5a-uc.a.run.app';
        
        $timeout = 10;
        for ($i = 0; $i < 3; $i++) {
            $response = wp_remote_get($api_endpoint, array('timeout' => $timeout));

            if (!is_wp_error($response)) {
                $body = wp_remote_retrieve_body($response);
                $data = json_decode($body, true);
    
                if (is_array($data) && !empty($data)) {
                    return $data;
                }
            }
    
            sleep(1);
        }
    
        error_log('Failed to fetch API data after multiple attempts.');
    
        return array();
    }
    
}

if(!function_exists('custom_date_compare')) {
function custom_date_compare($a, $b) {
    $orderby = isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : 'last_login_date';
    $order = isset($_GET['order']) && in_array(strtoupper($_GET['order']), ['ASC', 'DESC']) ? strtoupper($_GET['order']) : 'DESC';

    $date_a = $a[$orderby];
    $date_b = $b[$orderby];

    if ($date_a == $date_b) {
        return 0;
    }

    if ($order === 'ASC') {
        return strtotime($date_a) > strtotime($date_b) ? 1 : -1;
    } else {
        return strtotime($date_a) < strtotime($date_b) ? 1 : -1;
    }
}
}

if(!function_exists('format_date')) {
    function format_date($d) {
        $formattedDate = date('Y-m-d H:i:s', $d);

        return $formattedDate;
    }
}