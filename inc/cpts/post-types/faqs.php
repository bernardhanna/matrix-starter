<?php

// Register FAQs custom post type
add_action('init', function() {
    register_extended_post_type('faqs', [
        'menu_icon' => 'dashicons-editor-help',
        'supports' => ['title', 'editor'],
        'has_archive' => true,
        'rewrite' => ['slug' => 'questions-answers'],
        'show_in_rest' => true, // Enable Gutenberg support
    ], [
        'singular' => 'FAQ',
        'plural'   => 'FAQs',
        'slug'     => 'questions-answers'
    ]);
});
