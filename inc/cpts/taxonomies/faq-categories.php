<?php

// Register FAQ Categories taxonomy
add_action('init', function() {
    register_extended_taxonomy('faq_category', 'faqs', [
        'hierarchical' => true,
        'labels' => [
            'singular' => 'FAQ Category',
            'plural'   => 'FAQ Categories',
        ],
        'rewrite' => ['slug' => 'faq-category'],
        'show_in_rest' => true, // Enable Gutenberg support
    ]);
});
