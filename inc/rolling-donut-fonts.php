<?php
/**
 * Rolling Donut brand fonts — Edmondsans + Laca (legacy CDN Fonts URLs).
 */

if (! defined('ABSPATH')) {
    exit;
}

/** Laca style IDs from legacy AssetsServiceProvider. */
const MATRIX_RD_LACA_CDN_STYLES = '51511,51510,51505,51504,51507,51506,51503,51502,51509,51508,51513,51512,51501,51500,51499,51498';

/**
 * Preconnect / prefetch font CDN.
 */
function matrix_rd_font_resource_hints(array $urls, string $relation_type): array {
    if ($relation_type === 'dns-prefetch' || $relation_type === 'preconnect') {
        $urls[] = 'https://fonts.cdnfonts.com';
    }

    return $urls;
}
add_filter('wp_resource_hints', 'matrix_rd_font_resource_hints', 10, 2);

/**
 * Register Edmondsans + Laca stylesheets (same source as legacy Bedrock theme).
 */
function matrix_rd_enqueue_font_stylesheets(): void {
    if (is_admin()) {
        return;
    }

    wp_enqueue_style(
        'matrix-rd-font-edmondsans',
        'https://fonts.cdnfonts.com/css/edmondsans',
        [],
        null
    );

    wp_enqueue_style(
        'matrix-rd-font-laca',
        'https://fonts.cdnfonts.com/css/laca?styles=' . MATRIX_RD_LACA_CDN_STYLES,
        [],
        null
    );
}
add_action('wp_enqueue_scripts', 'matrix_rd_enqueue_font_stylesheets', 5);

/**
 * Base typography overrides after main theme CSS.
 */
function matrix_rd_enqueue_font_base_styles(): void {
    if (is_admin()) {
        return;
    }

    $theme_version = get_option('theme_css_version', '1.0');

    wp_enqueue_style(
        'matrix-rd-fonts',
        get_template_directory_uri() . '/assets/css/rolling-donut-fonts.css',
        ['matrix-rd-font-edmondsans', 'matrix-rd-font-laca', 'matrix-starter'],
        $theme_version
    );
}
add_action('wp_enqueue_scripts', 'matrix_rd_enqueue_font_base_styles', 100);

/**
 * Page Gutenberg/legal copy after Matrix .entry-content tokens.
 */
function matrix_rd_enqueue_prose_styles(): void {
    if (is_admin() || ! is_page()) {
        return;
    }

    if (function_exists('is_account_page') && is_account_page()) {
        return;
    }

    $prose_css = get_template_directory() . '/assets/css/rolling-donut-prose.css';
    if (! is_readable($prose_css)) {
        return;
    }

    $deps = ['matrix-rd-fonts', 'matrix-starter'];
    if (wp_style_is('matrix-rd-legacy', 'enqueued') || wp_style_is('matrix-rd-legacy', 'registered')) {
        $deps[] = 'matrix-rd-legacy';
    }

    wp_enqueue_style(
        'matrix-rd-prose',
        get_template_directory_uri() . '/assets/css/rolling-donut-prose.css',
        $deps,
        (string) filemtime($prose_css)
    );
}
add_action('wp_enqueue_scripts', 'matrix_rd_enqueue_prose_styles', 110);
