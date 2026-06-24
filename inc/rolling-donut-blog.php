<?php
/**
 * Rolling Donut blog / single post helpers (legacy Sage single.blade.php).
 */

/**
 * Estimated reading time in minutes (legacy reading_time()).
 */
function matrix_rd_reading_time(string $content): int {
    $word_count = str_word_count(wp_strip_all_tags($content));

    return max(1, (int) ceil($word_count / 250));
}

/**
 * Enqueue blog-specific assets on single posts.
 */
function matrix_rd_blog_enqueue_assets(): void {
    if (is_admin() || ! is_singular('post')) {
        return;
    }

    $theme_version = get_option('theme_css_version', '1.0');
    $blog_css      = get_template_directory() . '/assets/css/rolling-donut-blog.css';

    if (is_readable($blog_css)) {
        wp_enqueue_style(
            'matrix-rd-blog',
            get_template_directory_uri() . '/assets/css/rolling-donut-blog.css',
            ['matrix-rd-legacy', 'matrix-starter'],
            $theme_version
        );
    }
}
add_action('wp_enqueue_scripts', 'matrix_rd_blog_enqueue_assets', 35);
