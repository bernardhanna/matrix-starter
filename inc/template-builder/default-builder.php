<?php
/**
 * ACF defaults + Woo Single Product builder defaults (fixed layout names).
 * Works on Add New, respects existing rows.
 */

function matrix_resolve_post_type_for_acf($post_id) {
    if (is_numeric($post_id)) {
        $type = get_post_type((int) $post_id);
        if ($type) return $type;
    }
    if (function_exists('get_current_screen')) {
        $screen = get_current_screen();
        if ($screen && !empty($screen->post_type)) return $screen->post_type;
    }
    if (!empty($_GET['post_type'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return sanitize_key($_GET['post_type']); // phpcs:ignore
    }
    return 'post';
}

/** -------------------- HERO defaults -------------------- */
add_filter('acf/load_value/name=hero_content_blocks', function ($value, $post_id, $field) {
    if (!empty($value)) return $value;

    $type = matrix_resolve_post_type_for_acf($post_id);

    if ($type === 'page') {
        if (is_numeric($post_id)) {
            $front = (int) get_option('page_on_front');
            $blog  = (int) get_option('page_for_posts');
            if ($post_id == $front || $post_id == $blog) return $value;
        }
        return [ ['acf_fc_layout' => 'acf_subpage_hero'] ];
    }

    if ($type === 'post')   return [ ['acf_fc_layout' => 'banner_image'] ];
    if ($type === 'brands') return [ ['acf_fc_layout' => 'acf_subpage_hero'] ];

    return $value;
}, 10, 3);

/** -------------------- Generic flex defaults -------------------- */
add_filter('acf/load_value/name=flexible_content_blocks', function ($value, $post_id, $field) {
    if (!empty($value)) return $value;

    $type = matrix_resolve_post_type_for_acf($post_id);

    if ($type === 'post') {
        return [ ['acf_fc_layout' => 'single_post_content'] ];
    }

    if ($type === 'brands') {
        return [
            ['acf_fc_layout' => 'acf_key_points'],
            ['acf_fc_layout' => 'acf_brand_range'],
            ['acf_fc_layout' => 'acf_partners'],
            ['acf_fc_layout' => 'acf_cta_001'],
        ];
    }

    // pages: add if desired
    return $value;
}, 10, 3);

/** -------------------- Woo Single Product builder defaults -------------------- */
/**
 * IMPORTANT: Layout names must match your ACF Builder layout names exactly.
 * From your snippet: FieldsBuilder('woo_single_main', ...).
 * So use 'woo_single_main' (NOT 'acf_woo_single_main').
 * Same for related: 'woo_single_related' if that layout exists.
 */
function matrix_woo_single_default_rows() {
    return [
        [ 'acf_fc_layout' => 'woo_single_main' ],     // ✅ correct layout name
        [ 'acf_fc_layout' => 'woo_single_related' ],  // ✅ correct layout name (ensure you have this layout file)
    ];
}

function matrix_seed_woo_single_product_blocks($value, $post_id, $field) {
    // Respect existing rows
    if (is_array($value) && !empty($value)) {
        return $value;
    }

    // Only for Woo products
    if (matrix_resolve_post_type_for_acf($post_id) !== 'product') {
        return $value;
    }

    return matrix_woo_single_default_rows();
}

/** Hook by FIELD NAME used in your Builder group */
add_filter('acf/load_value/name=woo_single_product_blocks', 'matrix_seed_woo_single_product_blocks', 10, 3);
/** If you ever renamed the field, this catches the common alt-name */
add_filter('acf/load_value/name=single_product_blocks',     'matrix_seed_woo_single_product_blocks', 10, 3);

/** -------------------- OPTIONAL: small debug helper --------------------
 * Shows the actual layout names that ACF thinks exist on the product builder field.
 * Check the notice when editing/adding a product once. Remove/comment when done.
 */
add_action('admin_notices', function () {
    if (!is_admin() || !function_exists('get_current_screen')) return;
    $screen = get_current_screen();
    if (!$screen || $screen->post_type !== 'product') return;

    if (!function_exists('acf_get_field')) return;
    $fld = acf_get_field('woo_single_product_blocks');
    if (!$fld) $fld = acf_get_field('single_product_blocks');
    if (!$fld || empty($fld['layouts'])) return;

    $names = [];
    foreach ((array) $fld['layouts'] as $layout) {
        if (isset($layout['name'])) $names[] = $layout['name'];
    }
    if (!$names) return;

    echo '<div class="notice notice-info"><p><strong>ACF Woo Single Builder layouts detected:</strong> '
       . esc_html(implode(', ', $names))
       . '</p><p>If your defaults don\'t show, make sure the seeded names in <code>matrix_woo_single_default_rows()</code> match this list.</p></div>';
});
