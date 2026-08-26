<?php
/**
 * Rolling Donut — Alpine live search (replaces FiboSearch).
 *
 * Searches WooCommerce products (incl. box-builder & bundle "box" products),
 * Pages and Blog posts, and returns a typed, mixed result set for the
 * header live-search dropdown.
 *
 * Single donuts are not sold on their own, so a flavour match is rewritten to
 * the box products that include that donut (WPC bundle items).
 */

/**
 * Build the combined live-search result set.
 *
 * @return array<int, array{id:int, title:string, url:string, image:string, price_html:string, type:string, type_label:string}>
 */
function matrix_rd_search_all_results(string $term, int $limit = 10): array {
    $term = trim(wp_strip_all_tags($term));
    if ($term === '') {
        return [];
    }

    $results = [];

    // 1) Products (simple, variable, bundles/woosb, donut_box_builder boxes).
    foreach (matrix_rd_product_search_collect_ids($term, 6) as $product_id) {
        if (! function_exists('wc_get_product')) {
            break;
        }
        $product = wc_get_product($product_id);
        if (! $product || ! $product->is_visible()) {
            continue;
        }

        $image_id = $product->get_image_id();
        $image    = $image_id
            ? (string) wp_get_attachment_image_url($image_id, 'woocommerce_thumbnail')
            : (function_exists('wc_placeholder_img_src') ? wc_placeholder_img_src('woocommerce_thumbnail') : '');

        $type_slug  = function_exists('matrix_rd_product_type_slug') ? matrix_rd_product_type_slug((int) $product_id) : null;
        $type_label = $type_slug === 'box' ? __('Box', 'matrix-starter') : __('Product', 'matrix-starter');

        $results[] = [
            'id'         => (int) $product_id,
            'title'      => html_entity_decode($product->get_name(), ENT_QUOTES, 'UTF-8'),
            'url'        => (string) $product->get_permalink(),
            'image'      => (string) $image,
            'price_html' => (string) $product->get_price_html(),
            'type'       => 'product',
            'type_label' => $type_label,
        ];
    }

    // 2) Pages (excluding WooCommerce system pages).
    foreach (matrix_rd_search_collect_post_ids('page', $term, 3) as $page_id) {
        $results[] = matrix_rd_search_format_post($page_id, 'page', __('Page', 'matrix-starter'));
    }

    // 3) Blog posts.
    foreach (matrix_rd_search_collect_post_ids('post', $term, 3) as $post_id) {
        $results[] = matrix_rd_search_format_post($post_id, 'post', __('Blog', 'matrix-starter'));
    }

    return array_slice($results, 0, max(1, min(30, $limit)));
}

/**
 * Format a page/post into a result row.
 *
 * @return array{id:int, title:string, url:string, image:string, price_html:string, type:string, type_label:string}
 */
function matrix_rd_search_format_post(int $post_id, string $type, string $type_label): array {
    $thumb_id = get_post_thumbnail_id($post_id);
    $image    = $thumb_id ? (string) wp_get_attachment_image_url($thumb_id, 'thumbnail') : '';

    return [
        'id'         => $post_id,
        'title'      => html_entity_decode(get_the_title($post_id), ENT_QUOTES, 'UTF-8'),
        'url'        => (string) get_permalink($post_id),
        'image'      => $image,
        'price_html' => '',
        'type'       => $type,
        'type_label' => $type_label,
    ];
}

/**
 * Whether the product is a standalone donut flavour (not a box/merch/rental).
 */
function matrix_rd_is_single_donut_product(int $product_id): bool {
    return function_exists('matrix_rd_product_type_slug')
        && matrix_rd_product_type_slug($product_id) === 'donut';
}

/**
 * Parent + variation IDs for matching a donut against WPC bundle items.
 *
 * @return int[]
 */
function matrix_rd_product_search_flavour_ids(int $product_id): array {
    $ids     = [$product_id];
    $product = function_exists('wc_get_product') ? wc_get_product($product_id) : null;
    if (! $product instanceof WC_Product) {
        return $ids;
    }

    if ($product->is_type('variation')) {
        $parent = $product->get_parent_id();
        if ($parent > 0) {
            $ids[] = $parent;
        }
    }

    if ($product->is_type('variable')) {
        foreach ($product->get_children() as $child_id) {
            $ids[] = (int) $child_id;
        }
    }

    return array_values(array_unique(array_filter(array_map('intval', $ids))));
}

/**
 * Box product IDs whose WPC bundle includes any of the given donut products.
 *
 * @param int[] $donut_ids
 * @return int[]
 */
function matrix_rd_boxes_containing_donuts(array $donut_ids): array {
    $donut_ids = array_values(array_unique(array_filter(array_map('intval', $donut_ids))));
    if ($donut_ids === [] || ! taxonomy_exists('rd_product_type')) {
        return [];
    }

    $flavour_ids = [];
    foreach ($donut_ids as $donut_id) {
        foreach (matrix_rd_product_search_flavour_ids($donut_id) as $id) {
            $flavour_ids[$id] = true;
        }
    }

    $box_ids = get_posts([
        'post_type'              => 'product',
        'post_status'            => 'publish',
        'posts_per_page'         => -1,
        'fields'                 => 'ids',
        'no_found_rows'          => true,
        'update_post_meta_cache' => false,
        'tax_query'              => [
            [
                'taxonomy' => 'rd_product_type',
                'field'    => 'slug',
                'terms'    => ['box'],
            ],
        ],
    ]);

    $matches = [];
    foreach ($box_ids as $box_id) {
        $box = function_exists('wc_get_product') ? wc_get_product((int) $box_id) : null;
        if (! $box instanceof WC_Product || ! method_exists($box, 'get_items')) {
            continue;
        }

        foreach ((array) $box->get_items() as $item) {
            $item_id = isset($item['id']) ? (int) $item['id'] : 0;
            if ($item_id <= 0) {
                continue;
            }
            if (isset($flavour_ids[$item_id])) {
                $matches[] = (int) $box_id;
                break;
            }
            $child = wc_get_product($item_id);
            if ($child instanceof WC_Product && $child->is_type('variation')) {
                $parent_id = (int) $child->get_parent_id();
                if ($parent_id > 0 && isset($flavour_ids[$parent_id])) {
                    $matches[] = (int) $box_id;
                    break;
                }
            }
        }
    }

    return array_values(array_unique($matches));
}

/**
 * Drop standalone donuts and replace them with the boxes that include them.
 *
 * @param int[] $product_ids
 * @return int[]
 */
function matrix_rd_product_search_rewrite_donuts_to_boxes(array $product_ids): array {
    $kept   = [];
    $donuts = [];

    foreach ($product_ids as $product_id) {
        $product_id = (int) $product_id;
        if ($product_id <= 0) {
            continue;
        }
        if (function_exists('matrix_rd_is_custom_order_product') && matrix_rd_is_custom_order_product($product_id)) {
            continue;
        }
        if (matrix_rd_is_single_donut_product($product_id)) {
            $donuts[] = $product_id;
            continue;
        }
        $kept[] = $product_id;
    }

    $boxes = matrix_rd_boxes_containing_donuts($donuts);

    return array_values(array_unique(array_merge($kept, $boxes)));
}

/**
 * Collect product IDs by title match, then SKU match.
 * Standalone donuts are omitted; matching flavours surface their parent boxes.
 *
 * @return int[]
 */
function matrix_rd_product_search_collect_ids(string $term, int $limit): array {
    $ids            = [];
    $candidate_cap  = max($limit * 4, 24);

    $title_query = new WP_Query([
        'post_type'              => 'product',
        'post_status'            => 'publish',
        's'                      => $term,
        'posts_per_page'         => $candidate_cap,
        'fields'                 => 'ids',
        'no_found_rows'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ]);

    foreach ($title_query->posts as $id) {
        $ids[] = (int) $id;
    }

    if (count($ids) < $candidate_cap) {
        $sku_query = new WP_Query([
            'post_type'              => 'product',
            'post_status'            => 'publish',
            'posts_per_page'         => $candidate_cap - count($ids),
            'fields'                 => 'ids',
            'post__not_in'           => $ids,
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'meta_query'             => [
                [
                    'key'     => '_sku',
                    'value'   => $term,
                    'compare' => 'LIKE',
                ],
            ],
        ]);

        foreach ($sku_query->posts as $id) {
            $ids[] = (int) $id;
        }
    }

    $ids = matrix_rd_product_search_rewrite_donuts_to_boxes($ids);

    return array_slice($ids, 0, $limit);
}

/**
 * Collect page/post IDs by search term.
 *
 * @return int[]
 */
function matrix_rd_search_collect_post_ids(string $post_type, string $term, int $limit): array {
    $exclude = [];

    if ($post_type === 'page' && function_exists('wc_get_page_id')) {
        foreach (['cart', 'checkout', 'myaccount'] as $wc_page) {
            $wc_page_id = (int) wc_get_page_id($wc_page);
            if ($wc_page_id > 0) {
                $exclude[] = $wc_page_id;
            }
        }
    }

    $query = new WP_Query([
        'post_type'              => $post_type,
        'post_status'            => 'publish',
        's'                      => $term,
        'posts_per_page'         => $limit,
        'fields'                 => 'ids',
        'post__not_in'           => $exclude,
        'no_found_rows'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ]);

    return array_map('intval', $query->posts);
}

/**
 * URL for full site search results (all content types).
 */
function matrix_rd_product_search_results_url(string $term): string {
    $term = trim($term);
    if ($term === '') {
        return home_url('/');
    }

    return add_query_arg('s', rawurlencode($term), home_url('/'));
}

function matrix_rd_register_product_search_route(): void {
    register_rest_route('matrix-rd/v1', '/products/search', [
        'methods'             => 'GET',
        'permission_callback' => '__return_true',
        'args'                => [
            'q' => [
                'type'              => 'string',
                'required'          => true,
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'limit' => [
                'type'    => 'integer',
                'default' => 10,
                'minimum' => 1,
                'maximum' => 30,
            ],
        ],
        'callback' => static function (WP_REST_Request $request) {
            $term = (string) $request->get_param('q');
            if (strlen($term) < 2) {
                return new WP_REST_Response(['results' => [], 'view_all_url' => ''], 200);
            }

            $limit = (int) $request->get_param('limit');

            return new WP_REST_Response([
                'results'      => matrix_rd_search_all_results($term, $limit),
                'view_all_url' => matrix_rd_product_search_results_url($term),
            ], 200);
        },
    ]);
}
add_action('rest_api_init', 'matrix_rd_register_product_search_route');

/**
 * Full site search: hide standalone donut products (they are not sold alone).
 */
function matrix_rd_search_exclude_donut_products(WP_Query $query): void {
    if (is_admin() || ! $query->is_main_query() || ! $query->is_search()) {
        return;
    }
    if (! taxonomy_exists('rd_product_type')) {
        return;
    }

    $tax_query = $query->get('tax_query');
    if (! is_array($tax_query)) {
        $tax_query = [];
    }

    $tax_query[] = [
        'taxonomy' => 'rd_product_type',
        'field'    => 'slug',
        'terms'    => ['donut'],
        'operator' => 'NOT IN',
    ];

    $query->set('tax_query', $tax_query);
}
add_action('pre_get_posts', 'matrix_rd_search_exclude_donut_products', 25);

/**
 * Full site search: when the query matches donut flavours, insert the boxes
 * that include those flavours so "View all results" matches the live dropdown.
 *
 * @param WP_Post[] $posts
 * @return WP_Post[]
 */
function matrix_rd_search_inject_boxes_for_donuts(array $posts, WP_Query $query): array {
    if (is_admin() || ! $query->is_main_query() || ! $query->is_search()) {
        return $posts;
    }

    $term = trim((string) $query->get('s'));
    if ($term === '' || ! function_exists('wc_get_product')) {
        return $posts;
    }

    $donut_query = new WP_Query([
        'post_type'              => 'product',
        'post_status'            => 'publish',
        's'                      => $term,
        'posts_per_page'         => 24,
        'fields'                 => 'ids',
        'no_found_rows'          => true,
        'update_post_meta_cache' => false,
        'tax_query'              => [
            [
                'taxonomy' => 'rd_product_type',
                'field'    => 'slug',
                'terms'    => ['donut'],
            ],
        ],
    ]);

    $box_ids = matrix_rd_boxes_containing_donuts(array_map('intval', $donut_query->posts));
    if ($box_ids === []) {
        return $posts;
    }

    $seen = [];
    foreach ($posts as $post) {
        if ($post instanceof WP_Post) {
            $seen[(int) $post->ID] = true;
        }
    }

    $inject = [];
    foreach ($box_ids as $box_id) {
        if (isset($seen[$box_id])) {
            continue;
        }
        $box_post = get_post($box_id);
        if ($box_post instanceof WP_Post && $box_post->post_status === 'publish') {
            $inject[]        = $box_post;
            $seen[$box_id] = true;
        }
    }

    if ($inject === []) {
        return $posts;
    }

    return array_merge($inject, $posts);
}
add_filter('the_posts', 'matrix_rd_search_inject_boxes_for_donuts', 10, 2);

function matrix_rd_product_search_enqueue(): void {
    if (is_admin() || ! matrix_rd_nav_should_show()) {
        return;
    }

    // Register the Alpine component via an inline `alpine:init` listener attached
    // to the Alpine handle (same pattern as rdCartHeader). Inline scripts cannot
    // be deferred, so the component is always registered before Alpine walks the
    // DOM — even though theme/cache layers add `defer` to enqueued files, which
    // would otherwise make an external component file initialise too late.
    $config = wp_json_encode([
        'restUrl'  => esc_url_raw(rest_url('matrix-rd/v1/products/search')),
        'nonce'    => wp_create_nonce('wp_rest'),
        'minChars' => 2,
        'debounce' => 280,
    ]);

    $js = <<<'JS'
document.addEventListener('alpine:init', function () {
  var cfg = %%CFG%%;
  Alpine.data('matrixRdProductSearch', function () {
    return {
      query: '',
      results: [],
      viewAllUrl: '',
      loading: false,
      open: false,
      activeIndex: -1,
      debounceTimer: null,
      minChars: cfg.minChars || 2,
      debounceMs: cfg.debounce || 280,
      get restUrl() { return cfg.restUrl || ''; },
      get headers() { return { 'X-WP-Nonce': cfg.nonce || '' }; },
      init() {
        this.$watch('query', (value) => this.onQueryChange(value));
      },
      onQueryChange(value) {
        clearTimeout(this.debounceTimer);
        const q = (value || '').trim();
        if (q.length < this.minChars) {
          this.results = [];
          this.viewAllUrl = '';
          this.open = false;
          this.loading = false;
          this.activeIndex = -1;
          return;
        }
        this.debounceTimer = setTimeout(() => this.fetchResults(q), this.debounceMs);
      },
      async fetchResults(q) {
        if (!this.restUrl) return;
        this.loading = true;
        const url = new URL(this.restUrl, window.location.origin);
        url.searchParams.set('q', q);
        try {
          const res = await fetch(url.toString(), { headers: this.headers });
          if (!res.ok) throw new Error('Search request failed');
          const data = await res.json();
          this.results = Array.isArray(data.results) ? data.results : [];
          this.viewAllUrl = data.view_all_url || '';
          this.open = this.results.length > 0 || q.length >= this.minChars;
          this.activeIndex = this.results.length ? 0 : -1;
        } catch (e) {
          this.results = [];
          this.viewAllUrl = '';
          this.open = false;
          this.activeIndex = -1;
        } finally {
          this.loading = false;
        }
      },
      onFocus() {
        if (this.results.length) this.open = true;
      },
      onBlur() {
        window.setTimeout(() => {
          this.open = false;
          this.activeIndex = -1;
        }, 180);
      },
      onKeydown(event) {
        if (!this.open && event.key !== 'Escape') return;
        if (event.key === 'ArrowDown') {
          event.preventDefault();
          if (!this.results.length) return;
          this.open = true;
          this.activeIndex = Math.min(this.activeIndex + 1, this.results.length - 1);
          return;
        }
        if (event.key === 'ArrowUp') {
          event.preventDefault();
          this.activeIndex = Math.max(this.activeIndex - 1, 0);
          return;
        }
        if (event.key === 'Enter') {
          if (this.activeIndex >= 0 && this.results[this.activeIndex]) {
            event.preventDefault();
            window.location.href = this.results[this.activeIndex].url;
            return;
          }
          if (this.viewAllUrl) {
            event.preventDefault();
            window.location.href = this.viewAllUrl;
          }
          return;
        }
        if (event.key === 'Escape') {
          this.open = false;
          this.activeIndex = -1;
          if (this.$root && typeof this.$root.showSearch !== 'undefined') {
            this.$root.showSearch = false;
          }
        }
      },
      selectResult(index) {
        const item = this.results[index];
        if (item && item.url) window.location.href = item.url;
      },
    };
  });
});
JS;

    wp_add_inline_script('alpine', str_replace('%%CFG%%', (string) $config, $js));
}
add_action('wp_enqueue_scripts', 'matrix_rd_product_search_enqueue', 26);

/**
 * Dequeue FiboSearch assets when plugin remains active during migration.
 */
function matrix_rd_dequeue_fibosearch_assets(): void {
    if (! matrix_rd_nav_should_show()) {
        return;
    }

    $handles = [
        'dgwt-wcas',
        'jquery-dgwt-wcas',
        'fibosearch',
        'fibosearch-front',
    ];

    foreach ($handles as $handle) {
        wp_dequeue_style($handle);
        wp_dequeue_script($handle);
    }
}
add_action('wp_enqueue_scripts', 'matrix_rd_dequeue_fibosearch_assets', 100);
