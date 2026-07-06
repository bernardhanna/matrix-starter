<?php
/**
 * Plugin Name: CPT – Running Groups
 */

if (!defined('ABSPATH')) exit;

add_action('init', function () {

    /* CPT: running_group */
    register_extended_post_type(
        'running_group',
        [
            'menu_icon'       => 'dashicons-groups',
            'supports'        => ['title','editor','excerpt','thumbnail','revisions'],
            'public'          => true,
            'show_ui'         => true,
            'show_in_menu'    => true,
            'show_in_rest'    => true,
            'has_archive'     => true,
            'rewrite'         => ['slug' => 'running-groups', 'with_front' => false],
            'menu_position'   => 20,
            'capability_type' => 'post',
            'map_meta_cap'    => true,
        ],
        [
            'singular' => 'Running Group',
            'plural'   => 'Running Groups',
            'slug'     => 'running-groups',
        ]
    );

});