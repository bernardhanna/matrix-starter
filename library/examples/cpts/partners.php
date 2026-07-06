<?php
/**
 * Plugin Name: CPT – Partners
 *
 * Copy to inc/cpts/post-types/partners.php (or require from your CPT loader).
 */

if (!defined('ABSPATH')) exit;

add_action('init', function () {

    register_extended_post_type(
        'partner',
        [
            'menu_icon'       => 'dashicons-groups',
            'supports'        => ['title', 'editor', 'excerpt', 'thumbnail', 'revisions'],
            'public'          => true,
            'show_ui'         => true,
            'show_in_menu'    => true,
            'show_in_rest'    => true,
            'has_archive'     => true,
            'rewrite'         => ['slug' => 'partners', 'with_front' => false],
            'menu_position'   => 26,
            'capability_type' => 'post',
            'map_meta_cap'    => true,
        ],
        [
            'singular' => 'Partner',
            'plural'   => 'Partners',
            'slug'     => 'partners',
        ]
    );

    register_extended_taxonomy(
        'partner_category',
        'partner',
        [
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_in_rest'      => true,
            'show_admin_column' => true,
            'rewrite'           => ['slug' => 'partner-category', 'with_front' => false],
        ],
        [
            'singular' => 'Category',
            'plural'   => 'Categories',
            'slug'     => 'partner-category',
        ]
    );
});
