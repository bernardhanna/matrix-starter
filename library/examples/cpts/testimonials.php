<?php
/**
 * Plugin Name: CPT – Testimonials
 */

if (!defined('ABSPATH')) exit;

/**
 * Requires Extended CPTs (register_extended_post_type).
 * If not already loaded, include your autoload or require the library before this runs.
 */

add_action('init', function () {
    register_extended_post_type(
        'testimonial',
        [
            'menu_icon'       => 'dashicons-testimonial',
            'supports'        => ['title', 'editor', 'excerpt', 'thumbnail', 'revisions'],
            'public'          => true,
            'publicly_queryable' => true,
            'show_ui'         => true,
            'show_in_menu'    => true,
            'show_in_rest'    => true,
            'has_archive'     => true,
            'rewrite'         => ['slug' => 'testimonials', 'with_front' => false],
            'menu_position'   => 24,
            'capability_type' => 'post',
            'map_meta_cap'    => true,
        ],
        [
            'singular' => 'Testimonial',
            'plural'   => 'Testimonials',
            'slug'     => 'testimonials',
        ]
    );
});
