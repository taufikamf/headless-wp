<?php
/*
Plugin Name: Headless Plugin
Description: Custom setup for JSON API
Author: Your Name
Version: 0.1.0
Text Domain: headless-plugin
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

// ADD CODE FOR CUSTOM POST TYPES HERE

// create events post type
add_action('init', 'create_post_type_events');
function create_post_type_events()
{
    $labels = array(
        'name'               => _x('Events', 'post type general name', 'headless-plugin'),
        'singular_name'      => _x('Event', 'post type singular name', 'headless-plugin'),
        'menu_name'          => _x('Events', 'admin menu', 'headless-plugin'),
        'name_admin_bar'     => _x('Event', 'add new on admin bar', 'headless-plugin'),
        'add_new'            => _x('Add New', 'Event', 'headless-plugin'),
        'add_new_item'       => __('Add New Event', 'headless-plugin'),
        'new_item'           => __('New Event', 'headless-plugin'),
        'edit_item'          => __('Edit Event', 'headless-plugin'),
        'view_item'          => __('View Event', 'headless-plugin'),
        'all_items'          => __('All Events', 'headless-plugin'),
        'search_items'       => __('Search Events', 'headless-plugin'),
        'parent_item_colon'  => __('Parent Events:', 'headless-plugin'),
        'not_found'          => __('No Events found.', 'headless-plugin'),
        'not_found_in_trash' => __('No Events found in Trash.', 'headless-plugin')
    );

    $args = array(
        'labels'             => $labels,
        'description'        => __('Description.', 'headless-plugin'),
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'event'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'show_in_rest'       => true,
        'rest_base'          => 'events',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
        'supports'           => array('title', 'editor', 'custom-fields'),
    );

    register_post_type('events', $args);
}

function events_endpoint($request_data)
{
    $args = array(
        'post_type' => 'events',
        'posts_per_page' => -1,
        'numberposts' => -1,
        'post_status' => 'publish',
    );
    $posts = get_posts($args);
    foreach ($posts as $key => $post) {
        $posts[$key]->acf = get_fields($post->ID);
    }
    return  $posts;
}
add_action('rest_api_init', function () {
    register_rest_route('mwdw/v1', '/events/', array(
        'methods' => 'GET',
        'callback' => 'events_endpoint'
    ));
});

// add ACF object to default posts endpoint
add_filter('rest_prepare_post', 'acf_to_rest_api', 10, 3);
// adds ACF object to events endpoint
add_filter('rest_prepare_events', 'acf_to_rest_api', 10, 3);
function acf_to_rest_api($response, $post, $request)
{
    if (!function_exists('get_fields')) {
        return $response;
    }

    if (isset($post)) {
        $acf = get_fields($post->id);
        $response->data['acf'] = $acf;
    }
    return $response;
}
