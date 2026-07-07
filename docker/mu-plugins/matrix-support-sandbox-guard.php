<?php
/**
 * Plugin Name: Matrix Support Sandbox Guard
 * Description: noindex, robots block, discourage search engines — support sandboxes only.
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!getenv('MATRIX_SUPPORT_SANDBOX') && !defined('MATRIX_SUPPORT_SANDBOX')) {
    return;
}

add_action('init', function () {
    if (get_option('blog_public') !== '0') {
        update_option('blog_public', '0');
    }
}, 1);

add_action('send_headers', function () {
    if (!headers_sent()) {
        header('X-Robots-Tag: noindex, nofollow, noarchive', true);
    }
});

add_filter('robots_txt', function ($output) {
    return "User-agent: *\nDisallow: /\n";
}, 999);

add_action('wp_head', function () {
    echo '<meta name="robots" content="noindex,nofollow,noarchive" />' . "\n";
}, 0);
