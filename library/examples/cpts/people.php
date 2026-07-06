<?php
/**
 * Plugin Name: CPT – People
 */

if (!defined('ABSPATH')) exit;

add_action('init', function () {

    /* CPT: people */
    register_extended_post_type(
        'people',
        [
            'menu_icon'       => 'dashicons-groups',
            'supports'        => ['title','editor','excerpt','thumbnail','revisions'],
            'public'          => true,
            'show_ui'         => true,
            'show_in_menu'    => true,
            'show_in_rest'    => true,
            'has_archive'     => true,
            'rewrite'         => ['slug' => 'people', 'with_front' => false],
            'menu_position'   => 23,
            'capability_type' => 'post',
            'map_meta_cap'    => true,
        ],
        [
            'singular' => 'Person',
            'plural'   => 'People',
            'slug'     => 'people',
        ]
    );

    /* Taxonomy: people_role */
    register_taxonomy(
        'people_role',
        ['people'],
        [
            'labels' => [
                'name'          => __('Roles', 'matrix-starter'),
                'singular_name' => __('Role', 'matrix-starter'),
            ],
            'public'            => true,
            'show_ui'           => true,
            'show_in_menu'      => true,
            'show_in_rest'      => true,
            'hierarchical'      => true,
            'rewrite'           => ['slug' => 'people-role', 'with_front' => false],
            'show_admin_column' => true,
        ]
    );

});