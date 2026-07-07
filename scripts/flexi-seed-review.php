<?php
/**
 * Seed a flexi layout row on the /flexi/ review page.
 *
 * Usage (via WP-CLI):
 *   wp eval-file scripts/flexi-seed-review.php content_042
 *   wp eval-file scripts/flexi-seed-review.php content_042 --create-page
 */

if (! defined('ABSPATH')) {
    fwrite(STDERR, "Run via WP-CLI eval-file inside WordPress.\n");
    exit(1);
}

if (! function_exists('get_field') || ! function_exists('update_field')) {
    fwrite(STDERR, "ACF is required to seed flexi review rows.\n");
    exit(1);
}

$positional = array_values(array_filter($args ?? [], static function ($arg) {
    return is_string($arg) && $arg !== '' && ! str_starts_with($arg, '--');
}));

$layout = isset($positional[0]) ? sanitize_key($positional[0]) : '';
$create_page = in_array('--create-page', $args ?? [], true);

if ($layout === '') {
    fwrite(STDERR, "Usage: wp eval-file scripts/flexi-seed-review.php {layout} [--create-page]\n");
    exit(1);
}

$acf_file = get_template_directory() . '/acf-fields/partials/blocks/acf_' . $layout . '.php';
$template_file = get_template_directory() . '/template-parts/flexi/' . $layout . '.php';

if (! is_file($acf_file) || ! is_file($template_file)) {
    fwrite(STDERR, "Layout files missing for {$layout}. Expected acf + template pair.\n");
    exit(1);
}

$page = get_page_by_path('flexi', OBJECT, 'page');

if (! $page && $create_page) {
    $page_id = wp_insert_post([
        'post_title'   => 'Flexi blocks review',
        'post_name'    => 'flexi',
        'post_status'  => 'publish',
        'post_type'    => 'page',
        'post_content' => '',
    ], true);

    if (is_wp_error($page_id)) {
        fwrite(STDERR, 'Failed to create /flexi/ page: ' . $page_id->get_error_message() . "\n");
        exit(1);
    }

    $page = get_post($page_id);
}

if (! $page) {
    fwrite(STDERR, "Flexi review page not found. Re-run with --create-page or create a published page with slug flexi.\n");
    exit(1);
}

$post_id = (int) $page->ID;
$rows = get_field('flexible_content_blocks', $post_id);

if (! is_array($rows)) {
    $rows = [];
}

foreach ($rows as $row) {
    if (is_array($row) && ($row['acf_fc_layout'] ?? '') === $layout) {
        echo "Layout {$layout} already on /flexi/ (post {$post_id}).\n";
        exit(0);
    }
}

$rows[] = [
    'acf_fc_layout' => $layout,
];

$updated = update_field('flexible_content_blocks', $rows, $post_id);

if (! $updated) {
    fwrite(STDERR, "update_field failed for post {$post_id}.\n");
    exit(1);
}

echo "Added flexi layout {$layout} to /flexi/ (post {$post_id}).\n";
exit(0);
