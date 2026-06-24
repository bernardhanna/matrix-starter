<?php
/**
 * Rolling Donut footer helpers and assets.
 */

function matrix_rd_footer_show_newsletter(): bool {
    if (function_exists('is_cart') && is_cart()) {
        return false;
    }
    if (function_exists('is_checkout') && is_checkout()) {
        return false;
    }
    if (function_exists('is_order_received_page') && is_order_received_page()) {
        return false;
    }
    if (function_exists('is_product') && is_product()) {
        return false;
    }
    return true;
}

/**
 * @return list<array{url: string, title: string, target: string}>
 */
function matrix_rd_footer_menu_links(string $repeater, string $link_subfield): array {
    $rows = matrix_rd_acf_repeater_rows($repeater, [$link_subfield], 'option');
    $out  = [];

    foreach ($rows as $row) {
        $link = matrix_rd_acf_link($row[$link_subfield] ?? null);
        if ($link['url'] !== '') {
            $out[] = $link;
        }
    }

    return $out;
}

function matrix_rd_footer_enqueue_assets(): void {
    if (is_admin()) {
        return;
    }

    wp_enqueue_style(
        'fontawesome-6',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css',
        [],
        '6.5.2'
    );

    if (matrix_rd_footer_show_newsletter()) {
        $newsletter_css = get_template_directory() . '/assets/css/rolling-donut-newsletter.css';
        if (is_readable($newsletter_css)) {
            wp_enqueue_style(
                'matrix-rd-newsletter',
                get_template_directory_uri() . '/assets/css/rolling-donut-newsletter.css',
                ['matrix-starter'],
                (string) filemtime($newsletter_css)
            );
        }
    }

    if (is_front_page()) {
        wp_enqueue_style('slick-css');
        wp_enqueue_script('slick-js');
    }
}
add_action('wp_enqueue_scripts', 'matrix_rd_footer_enqueue_assets', 28);
