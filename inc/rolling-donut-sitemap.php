<?php
/**
 * HTML sitemap page — legacy template-sitemap (sitemap_navigation menu).
 */

/**
 * Assign the "Sitemap" WP menu to sitemap_navigation when not configured.
 */
function matrix_rd_assign_sitemap_menu_location(): void {
    $locations = get_nav_menu_locations();
    if (! empty($locations['sitemap_navigation'])) {
        return;
    }

    $menu = wp_get_nav_menu_object('sitemap');
    if (! $menu instanceof WP_Term) {
        return;
    }

    $locations['sitemap_navigation'] = (int) $menu->term_id;
    set_theme_mod('nav_menu_locations', $locations);
}
add_action('after_setup_theme', 'matrix_rd_assign_sitemap_menu_location', 20);

/**
 * Top-level sitemap menu items with children (legacy Navi structure).
 *
 * @return list<object{ID: int, url: string, label: string, active: bool, classes: string, children: list<object>}>
 */
function matrix_rd_sitemap_nav_tree(): array {
    $locations = get_nav_menu_locations();
    $menu_id   = ! empty($locations['sitemap_navigation'])
        ? (int) $locations['sitemap_navigation']
        : 0;

    if ($menu_id === 0) {
        $menu = wp_get_nav_menu_object('sitemap');
        if ($menu instanceof WP_Term) {
            $menu_id = (int) $menu->term_id;
        }
    }

    if ($menu_id === 0) {
        return [];
    }

    $raw = wp_get_nav_menu_items($menu_id, ['update_post_term_cache' => false]);
    if (! is_array($raw) || $raw === []) {
        return [];
    }

    $by_parent = [];
    foreach ($raw as $item) {
        if (! $item instanceof WP_Post || matrix_rd_sitemap_item_is_excluded($item)) {
            continue;
        }
        $parent = (int) $item->menu_item_parent;
        $by_parent[$parent][] = $item;
    }

    if (is_front_page()) {
        $current_url = trailingslashit(home_url('/'));
    } elseif (is_singular()) {
        $permalink   = get_permalink();
        $current_url = $permalink ? trailingslashit((string) $permalink) : '';
    } else {
        global $wp;
        $path        = isset($wp->request) ? (string) $wp->request : '';
        $current_url = $path !== '' ? trailingslashit(home_url($path)) : trailingslashit(home_url('/'));
    }

    $build = static function (int $parent_id) use (&$build, $by_parent, $current_url): array {
        $nodes = [];
        foreach ($by_parent[$parent_id] ?? [] as $item) {
            $url      = (string) $item->url;
            $item_url = trailingslashit($url);
            $classes  = implode(' ', array_filter((array) $item->classes));

            $nodes[] = (object) [
                'ID'       => (int) $item->ID,
                'url'      => $url,
                'label'    => (string) $item->title,
                'active'   => $item_url === $current_url,
                'classes'  => $classes,
                'children' => $build((int) $item->ID),
            ];
        }

        return $nodes;
    };

    return matrix_rd_sitemap_attach_box_products($build(0), $current_url);
}

/**
 * Utility pages that should not appear on the HTML sitemap.
 *
 * @return list<string>
 */
function matrix_rd_sitemap_excluded_slugs(): array {
    return [
        'thank-you-for-letting-us-know',
        'subscribed',
        'custom-order',
    ];
}

function matrix_rd_sitemap_item_is_excluded(WP_Post $item): bool {
    $excluded = matrix_rd_sitemap_excluded_slugs();
    $path     = (string) wp_parse_url((string) $item->url, PHP_URL_PATH);
    $slug     = basename(trim($path, '/'));

    if ($slug !== '' && in_array($slug, $excluded, true)) {
        return true;
    }

    if ($item->object === 'page' && (int) $item->object_id > 0) {
        $page = get_post((int) $item->object_id);
        if ($page instanceof WP_Post && in_array($page->post_name, $excluded, true)) {
            return true;
        }
    }

    return false;
}

/**
 * Nest published box products under the Donut Box sitemap item.
 *
 * @param list<object> $nodes
 * @return list<object>
 */
function matrix_rd_sitemap_attach_box_products(array $nodes, string $current_url): array {
    $boxes = matrix_rd_sitemap_box_nodes($current_url);
    if ($boxes === []) {
        return $nodes;
    }

    foreach ($nodes as $node) {
        $path = (string) wp_parse_url((string) $node->url, PHP_URL_PATH);
        if (basename(trim($path, '/')) !== 'donut-box') {
            continue;
        }

        $existing = [];
        foreach ($node->children as $child) {
            $existing[trailingslashit((string) $child->url)] = true;
        }

        foreach ($boxes as $box) {
            $box_url = trailingslashit((string) $box->url);
            if (! isset($existing[$box_url])) {
                $node->children[] = $box;
            }
        }
        break;
    }

    return $nodes;
}

/**
 * @return list<object{ID: int, url: string, label: string, active: bool, classes: string, children: list<object>}>
 */
function matrix_rd_sitemap_box_nodes(string $current_url): array {
    if (! taxonomy_exists('rd_product_type') || ! function_exists('matrix_rd_catalog_product_query_args')) {
        return [];
    }

    $posts = get_posts(matrix_rd_catalog_product_query_args([
        'orderby'        => 'title',
        'order'          => 'ASC',
        'suppress_filters' => false,
        'tax_query'      => [
            [
                'taxonomy' => 'rd_product_type',
                'field'    => 'slug',
                'terms'    => 'box',
            ],
        ],
    ]));

    if (! is_array($posts) || $posts === []) {
        return [];
    }

    $nodes = [];
    foreach ($posts as $post) {
        if (! $post instanceof WP_Post) {
            continue;
        }

        $url = get_permalink($post);
        if (! is_string($url) || $url === '') {
            continue;
        }

        $item_url = trailingslashit($url);
        $nodes[]  = (object) [
            'ID'       => (int) $post->ID,
            'url'      => $url,
            'label'    => get_the_title($post),
            'active'   => $item_url === $current_url,
            'classes'  => 'sitemap-box-product',
            'children' => [],
        ];
    }

    return $nodes;
}
