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
        if (! $item instanceof WP_Post) {
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

    return $build(0);
}
