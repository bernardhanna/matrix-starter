<?php
/**
 * Rolling Donut breadcrumb helpers — consistent markup on all interior pages.
 */

/**
 * Whether theme breadcrumbs should render (ACF option; on by default).
 */
function matrix_rd_breadcrumbs_enabled(): bool
{
    if (! function_exists('get_field')) {
        return true;
    }

    $enabled = get_field('enable_breadcrumbs', 'option');

    if ($enabled === null || $enabled === '') {
        return true;
    }

    return (bool) $enabled;
}

/**
 * @return list<array{0: string, 1: string}>
 */
function matrix_rd_get_breadcrumb_crumbs(): array
{
    if (is_front_page()) {
        return [];
    }

    if (class_exists('WPSEO_Breadcrumbs')) {
        $links = WPSEO_Breadcrumbs::get_instance()->get_links();
        if (is_array($links) && $links !== []) {
            $crumbs = [];
            foreach ($links as $link) {
                if (! is_array($link)) {
                    continue;
                }

                $text = isset($link['text']) ? wp_strip_all_tags((string) $link['text']) : '';
                if ($text === '') {
                    continue;
                }

                $crumbs[] = [$text, isset($link['url']) ? (string) $link['url'] : ''];
            }

            if ($crumbs !== []) {
                return $crumbs;
            }
        }
    }

    return matrix_rd_build_breadcrumb_crumbs_fallback();
}

/**
 * @return list<array{0: string, 1: string}>
 */
function matrix_rd_build_breadcrumb_crumbs_fallback(): array
{
    $crumbs = [[__('Home', 'matrix-starter'), home_url('/')]];

    if (is_home()) {
        $posts_page_id = (int) get_option('page_for_posts');
        $crumbs[]      = [
            $posts_page_id > 0 ? get_the_title($posts_page_id) : __('Blog', 'matrix-starter'),
            '',
        ];

        return $crumbs;
    }

    if (is_singular()) {
        $post = get_queried_object();
        if ($post instanceof WP_Post && is_post_type_hierarchical($post->post_type)) {
            foreach (array_reverse(get_post_ancestors($post)) as $ancestor_id) {
                $crumbs[] = [get_the_title($ancestor_id), get_permalink($ancestor_id)];
            }
        }

        $crumbs[] = [get_the_title(), ''];

        return $crumbs;
    }

    if (is_category() || is_tag() || is_tax()) {
        $term = get_queried_object();
        if ($term instanceof WP_Term && is_taxonomy_hierarchical($term->taxonomy)) {
            foreach (array_reverse(get_ancestors($term->term_id, $term->taxonomy, 'taxonomy')) as $ancestor_id) {
                $ancestor = get_term($ancestor_id, $term->taxonomy);
                if ($ancestor instanceof WP_Term && ! is_wp_error($ancestor)) {
                    $crumbs[] = [$ancestor->name, get_term_link($ancestor)];
                }
            }
        }

        $crumbs[] = [single_term_title('', false), ''];

        return $crumbs;
    }

    if (is_post_type_archive()) {
        $crumbs[] = [post_type_archive_title('', false), ''];

        return $crumbs;
    }

    if (is_search()) {
        $crumbs[] = [
            sprintf(
                /* translators: %s: search query */
                __('Search: %s', 'matrix-starter'),
                get_search_query()
            ),
            '',
        ];

        return $crumbs;
    }

    if (is_404()) {
        $crumbs[] = [__('Page not found', 'matrix-starter'), ''];

        return $crumbs;
    }

    $title = wp_strip_all_tags(get_the_archive_title());
    $title = preg_replace('/^\s*Archives?:\s*/i', '', (string) $title);
    $crumbs[] = [$title !== '' ? $title : __('Archive', 'matrix-starter'), ''];

    return $crumbs;
}

/**
 * Render breadcrumb navigation (matches WooCommerce shop styling).
 *
 * @param list<array{0: string, 1: string}>|null $crumbs
 */
function matrix_rd_render_breadcrumbs(?array $crumbs = null): void
{
    if (! matrix_rd_breadcrumbs_enabled()) {
        return;
    }

    if ($crumbs === null) {
        $crumbs = matrix_rd_get_breadcrumb_crumbs();
    }

    if ($crumbs === []) {
        return;
    }

    get_template_part('template-parts/header/breadcrumbs', null, [
        'crumbs' => $crumbs,
    ]);
}

/**
 * Convert WooCommerce breadcrumb rows to theme crumbs.
 *
 * @param list<array{0: string, 1?: string}> $breadcrumb
 * @return list<array{0: string, 1: string}>
 */
function matrix_rd_breadcrumb_crumbs_from_woocommerce(array $breadcrumb): array
{
    $crumbs = [];

    foreach ($breadcrumb as $crumb) {
        if (! is_array($crumb) || ! isset($crumb[0])) {
            continue;
        }

        $crumbs[] = [(string) $crumb[0], isset($crumb[1]) ? (string) $crumb[1] : ''];
    }

    return $crumbs;
}
