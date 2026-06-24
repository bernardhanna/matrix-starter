<?php
/**
 * Rolling Donut ACF helpers (repeaters stored without full field-group hydration).
 */

/**
 * @param int|string|null $context Post ID, 'option', or null for current post.
 */
function matrix_rd_acf_context_prefix($context): string {
    return $context === 'option' ? 'options_' : '';
}

/**
 * Row count for an ACF repeater when get_field() returns only the count string.
 *
 * @param int|string|null $context
 */
function matrix_rd_acf_repeater_count(string $field, $context = null): int {
    if ($context === null) {
        $context = get_queried_object_id() ?: 0;
    }

    if (function_exists('have_rows') && have_rows($field, $context)) {
        $n = 0;
        while (have_rows($field, $context)) {
            the_row();
            $n++;
        }
        return $n;
    }

    $prefix = matrix_rd_acf_context_prefix($context);
    $raw    = $context === 'option'
        ? get_option($prefix . $field)
        : get_post_meta((int) $context, $field, true);

    if (is_array($raw)) {
        return count($raw);
    }
    if (is_numeric($raw)) {
        return (int) $raw;
    }

    return 0;
}

/**
 * Read one repeater sub-field from stored flat meta.
 *
 * @param int|string|null $context
 */
function matrix_rd_acf_repeater_sub_value(string $field, int $index, string $subfield, $context = null) {
    if ($context === null) {
        $context = get_queried_object_id() ?: 0;
    }

    $key = matrix_rd_acf_context_prefix($context) . "{$field}_{$index}_{$subfield}";

    if ($context === 'option') {
        return get_option($key);
    }

    return get_post_meta((int) $context, "{$field}_{$index}_{$subfield}", true);
}

/**
 * Build repeater rows from flat meta when ACF sub-fields are not registered.
 *
 * @param list<string> $subfields
 * @param int|string|null $context
 * @return list<array<string, mixed>>
 */
function matrix_rd_acf_repeater_rows(string $field, array $subfields, $context = null): array {
    if ($context === null) {
        $context = get_queried_object_id() ?: 0;
    }

    if (function_exists('have_rows') && have_rows($field, $context)) {
        $rows = [];
        while (have_rows($field, $context)) {
            the_row();
            $row = [];
            foreach ($subfields as $sub) {
                $row[$sub] = get_sub_field($sub);
            }
            $rows[] = $row;
        }
        if ($rows !== []) {
            return $rows;
        }
    }

    $count = matrix_rd_acf_repeater_count($field, $context);
    $rows  = [];

    for ($i = 0; $i < $count; $i++) {
        $row = [];
        foreach ($subfields as $sub) {
            $row[$sub] = matrix_rd_acf_repeater_sub_value($field, $i, $sub, $context);
        }
        $rows[] = $row;
    }

    return $rows;
}

/**
 * @return array{url: string, title: string, target: string}
 */
function matrix_rd_acf_link(mixed $value): array {
    if (is_string($value) && $value !== '') {
        $value = maybe_unserialize($value);
    }
    if (! is_array($value)) {
        return ['url' => '', 'title' => '', 'target' => '_self'];
    }

    $url = isset($value['url']) ? (string) $value['url'] : '';
    if ($url !== '' && str_starts_with($url, '/')) {
        $url = home_url($url);
    }

    $target = isset($value['target']) && $value['target'] !== ''
        ? (string) $value['target']
        : '_self';

    return [
        'url'    => $url,
        'title'  => isset($value['title']) ? (string) $value['title'] : '',
        'target' => $target,
    ];
}

/**
 * ACF link field or plain URL string (e.g. get_directions_link on locations).
 *
 * @return array{url: string, title: string, target: string}
 */
function matrix_rd_acf_url_or_link(mixed $value, string $default_title = ''): array {
    if (is_string($value) && $value !== '') {
        return [
            'url'    => $value,
            'title'  => $default_title,
            'target' => '_blank',
        ];
    }

    $link = matrix_rd_acf_link($value);
    if ($link['title'] === '' && $default_title !== '') {
        $link['title'] = $default_title;
    }

    return $link;
}

/**
 * Permalink for the dedicated FAQs page (slug: frequently-asked-questions).
 */
function matrix_rd_faqs_page_url(): string {
    $page = get_page_by_path('frequently-asked-questions');
    if ($page instanceof WP_Post) {
        return (string) get_permalink($page);
    }

    return home_url('/frequently-asked-questions/');
}

/**
 * Normalize ACF faq_button with fallback to the FAQs page when unset or placeholder.
 *
 * @return array{url: string, title: string, target: string}
 */
function matrix_rd_faq_view_all_link(mixed $value = null, ?int $post_id = null): array {
    if ($value === null) {
        $value = $post_id ? get_field('faq_button', $post_id) : null;
    }

    $link = matrix_rd_acf_link($value);
    $url  = $link['url'];

    if ($url === '' || $url === '#' || str_starts_with($url, '#')) {
        $link['url'] = matrix_rd_faqs_page_url();
    } else {
        $path = (string) wp_parse_url($url, PHP_URL_PATH);
        $faqs_archive = function_exists('get_post_type_archive_link')
            ? (string) get_post_type_archive_link('faq')
            : '';

        if (
            preg_match('#/faqs/?$#', $path)
            || ($faqs_archive !== '' && untrailingslashit($url) === untrailingslashit($faqs_archive))
        ) {
            $link['url'] = matrix_rd_faqs_page_url();
        }
    }

    if ($link['title'] === '') {
        $link['title'] = __('View all', 'matrix-starter');
    }

    return $link;
}

/**
 * Attachment ID or URL for ACF file fields.
 */
function matrix_rd_acf_post(mixed $value): ?WP_Post {
    if ($value instanceof WP_Post) {
        return $value;
    }
    if (is_numeric($value)) {
        $post = get_post((int) $value);
        return $post instanceof WP_Post ? $post : null;
    }
    return null;
}

/**
 * Normalize ACF post-object / relationship values (IDs or posts) to WP_Post list.
 *
 * @return list<WP_Post>
 */
function matrix_rd_acf_posts(mixed $value): array {
    if ($value === null || $value === '' || $value === false) {
        return [];
    }
    if (! is_array($value)) {
        $post = matrix_rd_acf_post($value);
        return $post ? [$post] : [];
    }

    $posts = [];
    foreach ($value as $item) {
        $post = matrix_rd_acf_post($item);
        if ($post) {
            $posts[] = $post;
        }
    }

    return $posts;
}

/**
 * Normalize ACF taxonomy fields (term objects or IDs) to WP_Term list.
 *
 * @return list<WP_Term>
 */
function matrix_rd_acf_terms(mixed $value, string $taxonomy = 'product_cat'): array {
    if (is_string($value) && $value !== '') {
        $value = maybe_unserialize($value);
    }
    if (! is_array($value)) {
        return [];
    }

    $terms = [];
    foreach ($value as $item) {
        if ($item instanceof WP_Term) {
            $terms[] = $item;
            continue;
        }
        if (is_numeric($item)) {
            $term = get_term((int) $item, $taxonomy);
            if ($term instanceof WP_Term && ! is_wp_error($term)) {
                $terms[] = $term;
            }
        }
    }

    return $terms;
}

function matrix_rd_acf_file_url(mixed $value): string {
    if (empty($value)) {
        return '';
    }
    if (is_numeric($value)) {
        $url = wp_get_attachment_url((int) $value);
        return is_string($url) ? $url : '';
    }
    return is_string($value) ? $value : '';
}
