<?php
/*
Plugin Name: France departments
Description: Return a list of all departments in France
Author: Nicolas PALLAS
Version: 1.0
*/

function fd_schedule_event() {
    if (!wp_next_scheduled ('fd_get_france_departments')) {
        wp_schedule_event(time(), 'weekly', 'fd_get_france_departments');
    }
}
register_activation_hook(__FILE__, 'fd_schedule_event');

function fd_clear_transient() {
    delete_transient('france_departments');
}
register_deactivation_hook(__FILE__, 'fd_clear_transient');

function fd_routes() {
    register_rest_route( 'mgn/v1', '/departments/', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'fd_get_france_departments',
        'permission_callback' => '__return_true',
    ]);
}
add_action('rest_api_init', 'fd_routes');

/**
 * @return WP_REST_Response
 */
function fd_get_france_departments(): WP_REST_Response {
    $france_departments = get_transient('france_departments');

    if (empty($france_departments)) {
        $curl_response = wp_remote_get('https://geo.api.gouv.fr/departements');

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
