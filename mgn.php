<?php
/*
Plugin Name: France departments
Description: Return a list of all departments in France
Author: Nicolas PALLAS
Version: 1.0
*/
define('FRANCE_DEPARTMENTS_PATH', plugin_dir_path(__FILE__));
define('FRANCE_DEPARTMENTS_URL', plugin_dir_url(__FILE__));
define('FRANCE_DEPARTMENTS_BASENAME', plugin_basename(__FILE__));

function fd_routes() {
    register_rest_route( 'mgn/v1', '/departments/', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'fd_get_france_departments',
    ]);
}
add_action('rest_api_init', 'fd_routes');

/**
 * @return WP_REST_Response
 */
function fd_get_france_departments(): WP_REST_Response {
    $france_departments = get_transient('france_departments');

    if (empty($france_departments)) {
        $curl = new WP_Http_Curl();
        $curl_response = $curl->request('https://geo.api.gouv.fr/departements', [
            'filename' => '',
            'stream' => false,
            'decompress' => true,
            'user-agent' => $_SERVER['HTTP_USER_AGENT'],
        ]);

        if ($curl_response['response']['code'] === 200) {
            $france_departments = $curl_response['body'];
            set_transient('france_departments', $france_departments, WEEK_IN_SECONDS);
        }
    }

    // uncomment to know the number of days before the expiration of the transient
//    $expires = (int) get_option('_transient_timeout_france_departments', 0);
//    $time_left_in_seconds = $expires - time();

    return new WP_REST_Response(json_decode($france_departments), 200);
}
