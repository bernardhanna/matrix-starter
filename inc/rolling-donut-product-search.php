<?php
/**
 * Rolling Donut — Alpine live search (replaces FiboSearch).
 *
 * Searches WooCommerce products (incl. box-builder & bundle "box" products),
 * Pages and Blog posts, and returns a typed, mixed result set for the
 * header live-search dropdown.
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

        $results[] = [
            'id'         => (int) $product_id,
            'title'      => html_entity_decode($product->get_name(), ENT_QUOTES, 'UTF-8'),
            'url'        => (string) $product->get_permalink(),
            'image'      => (string) $image,
            'price_html' => (string) $product->get_price_html(),
            'type'       => 'product',
            'type_label' => __('Product', 'matrix-starter'),
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
 * Collect product IDs by title match, then SKU match.
 *
 * @return int[]
 */
function matrix_rd_product_search_collect_ids(string $term, int $limit): array {
    $ids = [];

    $title_query = new WP_Query([
        'post_type'              => 'product',
        'post_status'            => 'publish',
        's'                      => $term,
        'posts_per_page'         => $limit,
        'fields'                 => 'ids',
        'no_found_rows'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ]);

    foreach ($title_query->posts as $id) {
        $ids[] = (int) $id;
    }

    if (count($ids) < $limit) {
        $sku_query = new WP_Query([
            'post_type'              => 'product',
            'post_status'            => 'publish',
            'posts_per_page'         => $limit - count($ids),
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

    return array_slice(array_values(array_unique($ids)), 0, $limit);
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
