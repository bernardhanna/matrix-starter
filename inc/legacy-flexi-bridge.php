<?php
/**
 * Legacy Rolling Donuts flexi bridge.
 *
 * Supports ACF flexible content stored as `flexible_content` (Radicles/Bedrock)
 * with layout slugs from the old theme, alongside matrix-starter `flexible_content_blocks`.
 */

if (!defined('ABSPATH')) {
    exit;
}

/** @return string[] */
function matrix_legacy_flexi_field_names(): array
{
    return ['flexible_content_blocks', 'flexible_content'];
}

/**
 * Render one flexi row using matrix or legacy template paths.
 */
function matrix_render_flexi_layout(string $layout): void
{
    $layout = sanitize_title($layout);
    if ($layout === '') {
        return;
    }

    $candidates = [
        'template-parts/flexi/' . $layout,
        'template-parts/flexi/legacy/' . $layout,
    ];

    foreach ($candidates as $part) {
        $file = get_template_directory() . '/' . $part . '.php';
        if (is_readable($file)) {
            get_template_part($part);
            return;
        }
    }

    error_log("[matrix-starter] Missing flexi template for layout: {$layout}");
}

/**
 * Load flexible content for current post (legacy + matrix field names).
 */
function load_flexible_content_templates($post_id = null): void
{
    if (!$post_id) {
        $post_id = is_home() ? (int) get_option('page_for_posts') : get_the_ID();
    }

    foreach (matrix_legacy_flexi_field_names() as $field_name) {
        if ($post_id && have_rows($field_name, $post_id)) {
            while (have_rows($field_name, $post_id)) {
                the_row();
                matrix_render_flexi_layout((string) get_row_layout());
            }
            return;
        }
    }

    if ($post_id && function_exists('matrix_rd_load_flexi_from_meta')) {
        $layouts = matrix_rd_flexi_layouts($post_id);
        if ($layouts !== []) {
            matrix_rd_load_flexi_from_meta($post_id);
        }
    }
}
