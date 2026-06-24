<?php
/**
 * Plugin Name: CPT – Testimonials
 *
 * Registers the `testimonial` post type used by the kudos flexi block
 * (template-parts/flexi/kudos_block.php) via its `selected_kudos` field.
 *
 * The testimonial posts migrated from the old site (Phoebe, Renata, Anna,
 * Karen) but the registration did not, so they were hidden from the admin.
 * The quote is the post content and the person's name is the post title;
 * `kudos_job` / `kudos_image` come from the testimonial ACF field group.
 *
 * Testimonials are never shown on their own URL (they only appear inside the
 * kudos block), so the type is non-public but still has an admin UI and REST
 * support (for the block editor + ACF relationship pickers).
 */

if (!defined('ABSPATH')) exit;

add_action('init', function () {

    register_extended_post_type(
        'testimonial',
        [
            'menu_icon'           => 'dashicons-format-quote',
            'supports'            => ['title', 'editor', 'revisions'],
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_rest'        => true,
            'has_archive'         => false,
            'publicly_queryable'  => false,
            'exclude_from_search' => true,
            'menu_position'       => 23,
            'capability_type'     => 'post',
            'map_meta_cap'        => true,
        ],
        [
            'singular' => 'Testimonial',
            'plural'   => 'Testimonials',
            'slug'     => 'testimonials',
        ]
    );

});
