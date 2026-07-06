<?php
/**
 * Plugin Name: CPT – Resources
 *
 * Copy to inc/cpts/post-types/resources.php (or require from your CPT loader).
 */

if (!defined('ABSPATH')) exit;

add_action('init', function () {

    register_extended_post_type(
        'resource',
        [
            'menu_icon'       => 'dashicons-media-document',
            'supports'        => ['title', 'editor', 'excerpt', 'thumbnail', 'revisions'],
            'public'          => true,
            'show_ui'         => true,
            'show_in_menu'    => true,
            'show_in_rest'    => true,
            'has_archive'     => true,
            'rewrite'         => ['slug' => 'resources', 'with_front' => false],
            'menu_position'   => 25,
            'capability_type' => 'post',
            'map_meta_cap'    => true,
        ],
        [
            'singular' => 'Resource',
            'plural'   => 'Resources',
            'slug'     => 'resources',
        ]
    );

    register_extended_taxonomy(
        'resource_category',
        'resource',
        [
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_in_rest'      => true,
            'show_admin_column' => true,
            'rewrite'           => ['slug' => 'resource-category', 'with_front' => false],
        ],
        [
            'singular' => 'Category',
            'plural'   => 'Categories',
            'slug'     => 'resource-category',
        ]
    );
});
