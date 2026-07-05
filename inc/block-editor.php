<?php
/**
 * Block editor integration: editor styles, fonts, pattern category.
 *
 * @package Matrix_Starter
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register editor stylesheet and pattern category.
 */
function matrix_starter_block_editor_setup(): void
{
    $editor_css = get_template_directory() . '/dist/editor.css';
    if (file_exists($editor_css)) {
        $version = (string) filemtime($editor_css);
        add_editor_style(add_query_arg('ver', $version, 'dist/editor.css'));
    }

    register_block_pattern_category('matrix-starter', [
        'label' => esc_html__('Matrix Starter', 'matrix-starter'),
    ]);
}
add_action('after_setup_theme', 'matrix_starter_block_editor_setup', 20);

/**
 * Load Google Fonts in the block editor iframe.
 */
function matrix_starter_enqueue_block_editor_fonts(): void
{
    if (!function_exists('matrix_starter_google_fonts_url')) {
        return;
    }

    wp_enqueue_style(
        'matrix-google-fonts-editor',
        matrix_starter_google_fonts_url(),
        [],
        null
    );
}
add_action('enqueue_block_editor_assets', 'matrix_starter_enqueue_block_editor_fonts', 5);
