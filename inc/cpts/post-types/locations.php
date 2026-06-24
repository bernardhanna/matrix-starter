<?php
/**
 * Plugin Name: CPT – Locations
 *
 * Registers the `location` post type used to populate /our-shops/
 * (template-parts/locations/list.php). The 7 location posts migrated from the
 * old site but the registration did not, so they were hidden from the admin.
 *
 * The shop listing lives on the existing /our-shops/ page and orders cards by
 * `menu_order`, so `page-attributes` is enabled and `has_archive` is disabled.
 * Single locations resolve at /location/{slug}/ to avoid colliding with the
 * /our-shops/ page slug.
 */

if (!defined('ABSPATH')) exit;

add_action('init', function () {

    register_extended_post_type(
        'location',
        [
            'menu_icon'       => 'dashicons-store',
            'supports'        => ['title', 'editor', 'thumbnail', 'page-attributes', 'revisions'],
            'public'          => true,
            'show_ui'         => true,
            'show_in_menu'    => true,
            'show_in_rest'    => true,
            'has_archive'     => false,
            'rewrite'         => ['slug' => 'location', 'with_front' => false],
            'menu_position'   => 22,
            'capability_type' => 'post',
            'map_meta_cap'    => true,
        ],
        [
            'singular' => 'Location',
            'plural'   => 'Locations',
            'slug'     => 'locations',
        ]
    );

});
