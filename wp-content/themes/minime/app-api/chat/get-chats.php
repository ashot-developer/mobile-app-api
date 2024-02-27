<?php
if(!function_exists('fetch_chat_data')) {
    function fetch_chat_data() {
        $api_endpoint = 'https://http-chats-getall-7vxnir2s7q-uc.a.run.app';
        
            $response = wp_remote_get($api_endpoint);
    
            if (!is_wp_error($response)) {
                $body = wp_remote_retrieve_body($response);
                $data = json_decode($body, true);
    
                if (is_array($data) && !empty($data)) {
                    return $data;
                }
            }
    
        error_log('Failed to fetch API data after multiple attempts.');
    
        return array();
    }
}