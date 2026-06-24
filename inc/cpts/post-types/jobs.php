<?php
/**
 * Plugin Name: CPT – Jobs
 *
 * Registers the `job` post type. The post data migrated from the old site but
 * the registration did not, so existing `job` posts were hidden from the admin.
 * A separate "Careers" page (slug `careers`) already exists, so this CPT uses
 * the `jobs` slug to avoid a rewrite collision.
 */

if (!defined('ABSPATH')) exit;

add_action('init', function () {

    /* CPT: job */
    register_extended_post_type(
        'job',
        [
            'menu_icon'       => 'dashicons-businessperson',
            'supports'        => ['title', 'editor', 'thumbnail', 'excerpt', 'revisions'],
            'public'          => true,
            'show_ui'         => true,
            'show_in_menu'    => true,
            'show_in_rest'    => true,
            // No public archive: the site already has a dedicated "Careers"
            // page (/careers/), and the generic archive.php template is not
            // built for this CPT. Single job posts still resolve at
            // /jobs/{slug}/ via the rewrite slug below.
            'has_archive'     => false,
            'rewrite'         => ['slug' => 'jobs', 'with_front' => false],
            'menu_position'   => 21,
            'capability_type' => 'post',
            'map_meta_cap'    => true,
        ],
        [
            'singular' => 'Job',
            'plural'   => 'Jobs',
            'slug'     => 'jobs',
        ]
    );

});
