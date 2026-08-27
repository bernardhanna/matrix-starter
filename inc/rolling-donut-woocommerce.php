<?php
/**
 * Rolling Donut WooCommerce helpers.
 */

require_once __DIR__ . '/rolling-donut-myaccount-auth.php';
require_once __DIR__ . '/rolling-donut-account-captcha.php';
require_once __DIR__ . '/rolling-donut-cart.php';
require_once __DIR__ . '/rolling-donut-side-cart.php';
require_once __DIR__ . '/rolling-donut-checkout.php';
require_once __DIR__ . '/rolling-donut-custom-order-access.php';
require_once __DIR__ . '/helpers/admin-order-shipping-zone.php';
require_once __DIR__ . '/helpers/admin-collection-address.php';
require_once __DIR__ . '/helpers/admin-order-list.php';

function matrix_rd_register_product_taxonomy(): void {
    if (taxonomy_exists('rd_product_type')) {
        return;
    }

    register_taxonomy('rd_product_type', 'product', [
        'labels'       => [
            'name'          => __('Product Types', 'matrix-starter'),
            'singular_name' => __('Product Type', 'matrix-starter'),
            'menu_name'     => __('Product Types', 'matrix-starter'),
            'all_items'     => __('All Product Types', 'matrix-starter'),
            'edit_item'     => __('Edit Product Type', 'matrix-starter'),
            'view_item'     => __('View Product Type', 'matrix-starter'),
            'add_new_item'  => __('Add New Product Type', 'matrix-starter'),
            'new_item_name' => __('New Product Type Name', 'matrix-starter'),
            'search_items'  => __('Search Product Types', 'matrix-starter'),
        ],
        'public'       => true,
        'hierarchical' => true,
        'show_ui'      => true,
        'show_in_rest' => true,
        'rewrite'      => ['slug' => 'product-type'],
        // Replace the default multi-checkbox box with a single-select dropdown
        // (matrix_rd_product_type_meta_box) to match the legacy extended-cpts
        // "Product Type" dropdown — a product is exactly one type.
        'meta_box_cb'  => false,
    ]);
}
add_action('init', 'matrix_rd_register_product_taxonomy', 5);

/**
 * Single-select "Product Type" dropdown meta box on the product editor sidebar.
 * Restores the legacy extended-cpts dropdown behaviour for the rd_product_type
 * taxonomy (Donut / Box / Merch / Rental). The selection is saved as a single
 * taxonomy term, which the theme + box-builder plugins read via has_term().
 */
function matrix_rd_add_product_type_meta_box(): void {
    add_meta_box(
        'rd_product_type_select',
        __('Product Type', 'matrix-starter'),
        'matrix_rd_product_type_meta_box',
        'product',
        'side',
        'core'
    );
}
add_action('add_meta_boxes_product', 'matrix_rd_add_product_type_meta_box');

function matrix_rd_product_type_meta_box(WP_Post $post): void {
    $terms = get_terms([
        'taxonomy'   => 'rd_product_type',
        'hide_empty' => false,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ]);

    if (is_wp_error($terms)) {
        echo '<p>'.esc_html__('Unable to load product types.', 'matrix-starter').'</p>';

        return;
    }

    $current = wp_get_object_terms($post->ID, 'rd_product_type', ['fields' => 'ids']);
    $current_id = (! is_wp_error($current) && ! empty($current)) ? (int) $current[0] : 0;

    wp_nonce_field('matrix_rd_save_product_type', 'matrix_rd_product_type_nonce');
    ?>
    <p>
        <label class="screen-reader-text" for="matrix_rd_product_type"><?php esc_html_e('Product Type', 'matrix-starter'); ?></label>
        <select name="matrix_rd_product_type" id="matrix_rd_product_type" style="width:100%;">
            <option value="0"<?php selected($current_id, 0); ?>><?php esc_html_e('— None —', 'matrix-starter'); ?></option>
            <?php foreach ($terms as $term) : ?>
                <option value="<?php echo esc_attr((string) $term->term_id); ?>"<?php selected($current_id, (int) $term->term_id); ?>>
                    <?php echo esc_html($term->name); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
    <p class="description"><?php esc_html_e('Choose the Rolling Donut product type. Controls donut/box behaviour across the site.', 'matrix-starter'); ?></p>
    <?php
}

function matrix_rd_save_product_type(int $post_id): void {
    if (! isset($_POST['matrix_rd_product_type_nonce'])
        || ! wp_verify_nonce(sanitize_key($_POST['matrix_rd_product_type_nonce']), 'matrix_rd_save_product_type')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (! current_user_can('edit_post', $post_id)) {
        return;
    }

    if (! isset($_POST['matrix_rd_product_type'])) {
        return;
    }

    $term_id = (int) $_POST['matrix_rd_product_type'];

    if ($term_id <= 0) {
        wp_set_object_terms($post_id, [], 'rd_product_type');

        return;
    }

    wp_set_object_terms($post_id, [$term_id], 'rd_product_type', false);
}
add_action('save_post_product', 'matrix_rd_save_product_type');

/**
 * Skip Matrix PACE page heroes (template-parts/page/hero.php) on RD routes.
 */
function matrix_rd_skip_pace_hero(?int $post_id = null): bool {
    if (function_exists('is_shop') && is_shop()) {
        return true;
    }
    if (function_exists('is_cart') && is_cart()) {
        return true;
    }
    if (function_exists('is_checkout') && is_checkout()) {
        return true;
    }
    if (function_exists('is_product') && is_product()) {
        return true;
    }
    if (is_front_page()) {
        return true;
    }
    if (is_404()) {
        return true;
    }
    if (is_home() || is_category() || is_singular('post')) {
        return true;
    }

    $rd_page_slugs = [
        'about-us',
        'contact-us',
        'our-shops',
        'weddings-events',
        'merch',
        'donut-box',
    ];

    if (is_page($rd_page_slugs)) {
        return true;
    }

    if ($post_id === null) {
        $post_id = get_queried_object_id() ?: 0;
    }

    return false;
}

/**
 * Remove Matrix WooCommerce hero hooks; RD uses woocommerce-header.php / page-header-rd.
 */
function matrix_rd_disable_matrix_woo_heroes(): void {
    remove_action('woocommerce_before_main_content', 'matrix_add_shop_hero_section', 5);
    remove_action('woocommerce_before_main_content', 'matrix_add_cart_hero_section', 5);
    remove_action('woocommerce_before_single_product', 'matrix_add_product_hero_section', 5);
}
add_action('init', 'matrix_rd_disable_matrix_woo_heroes', 20);

/**
 * Remove WooCommerce breadcrumb trail from shop/product/cart templates (RD uses title-only header).
 */
function matrix_rd_remove_woocommerce_breadcrumbs(): void {
    remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
}
add_action('init', 'matrix_rd_remove_woocommerce_breadcrumbs', 20);

function get_rd_product_type(int $product_id): string {
    $terms = get_the_terms($product_id, 'rd_product_type');
    if ($terms && ! is_wp_error($terms)) {
        return (string) $terms[0]->name;
    }
    return 'No RD Product Type';
}

/**
 * @return string|null rd_product_type term slug (box, donut, merch, rental).
 */
function matrix_rd_product_type_slug(int $product_id): ?string {
    $terms = get_the_terms($product_id, 'rd_product_type');
    if (! $terms || is_wp_error($terms)) {
        return null;
    }
    return (string) $terms[0]->slug;
}

/**
 * There is no page at /shop/. WordPress 404-guesses that slug onto the live
 * "Shop Closed" page. Send it to the real Woo shop (Our Donuts) instead.
 */
function matrix_rd_redirect_legacy_shop_path(): void {
    if (is_admin() || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST)) {
        return;
    }

    $path = trim((string) wp_parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH), '/');
    if ($path !== 'shop') {
        return;
    }

    $dest = function_exists('wc_get_page_permalink') ? (string) wc_get_page_permalink('shop') : '';
    if ($dest === '' || $dest === '0') {
        $dest = home_url('/our-donuts/');
    }

    wp_safe_redirect($dest, 301);
    exit;
}
add_action('template_redirect', 'matrix_rd_redirect_legacy_shop_path', 0);

/**
 * Standalone donut flavours are not sold on their own — send their product
 * URLs to the Our Donuts shop instead of rendering a single-product page.
 */
function matrix_rd_redirect_single_donut_product_pages(): void {
    if (is_admin() || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST)) {
        return;
    }
    if (! function_exists('is_product') || ! is_product()) {
        return;
    }

    $product_id = (int) get_queried_object_id();
    if ($product_id <= 0) {
        return;
    }

    if (function_exists('wc_get_product')) {
        $product = wc_get_product($product_id);
        if ($product instanceof WC_Product && $product->is_type('variation')) {
            $product_id = (int) $product->get_parent_id();
        }
    }

    $is_donut = function_exists('matrix_rd_is_single_donut_product')
        ? matrix_rd_is_single_donut_product($product_id)
        : matrix_rd_product_type_slug($product_id) === 'donut';

    if (! $is_donut) {
        return;
    }

    wp_safe_redirect(home_url('/our-donuts/'), 301);
    exit;
}
add_action('template_redirect', 'matrix_rd_redirect_single_donut_product_pages', 5);

/**
 * Legacy Custom Order product id (slug "custom-order"), resolved from the
 * product permalink with a hard-coded fallback used across box-builder code.
 */
function matrix_rd_custom_order_product_id(): int {
    static $id = null;
    if ($id !== null) {
        return $id;
    }

    $id = 0;
    $post = get_page_by_path('custom-order', OBJECT, 'product');
    if ($post instanceof WP_Post) {
        $id = (int) $post->ID;

        return $id;
    }

    if (function_exists('wc_get_product')) {
        $legacy = wc_get_product(3947);
        if ($legacy instanceof WC_Product && $legacy->get_slug() === 'custom-order') {
            $id = 3947;
        }
    }

    return $id;
}

function matrix_rd_is_custom_order_product(int $product_id): bool {
    if ($product_id <= 0) {
        return false;
    }

    $custom_id = matrix_rd_custom_order_product_id();
    if ($custom_id > 0 && $product_id === $custom_id) {
        return true;
    }

    if (! function_exists('wc_get_product')) {
        return false;
    }

    $product = wc_get_product($product_id);

    return $product instanceof WC_Product && $product->get_slug() === 'custom-order';
}

function matrix_rd_cart_contains_custom_order(): bool {
    if (! function_exists('WC') || ! WC()->cart) {
        return false;
    }

    $custom_id = matrix_rd_custom_order_product_id();
    if ($custom_id <= 0) {
        return false;
    }

    foreach (WC()->cart->get_cart() as $item) {
        if ((int) ($item['product_id'] ?? 0) === $custom_id) {
            return true;
        }
    }

    return false;
}

function matrix_rd_custom_order_login_url(string $return_url): string {
    $account = function_exists('wc_get_page_permalink')
        ? (string) wc_get_page_permalink('myaccount')
        : '';

    // Apache/Local 403s query strings that embed a full http:// URL.
    $relative = function_exists('wp_make_link_relative')
        ? wp_make_link_relative($return_url)
        : (string) (wp_parse_url($return_url, PHP_URL_PATH) ?: '');
    if ($relative === '') {
        $relative = '/product/custom-order/';
    }

    if ($account === '') {
        return wp_login_url($relative);
    }

    return add_query_arg('redirect', $relative, $account);
}

/**
 * Staff-only Custom Order builder. Shared-cart recipients are guests and must
 * still reach cart/checkout — never this product page.
 */
function matrix_rd_redirect_guest_custom_order_page(): void {
    if (is_admin() || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST)) {
        return;
    }
    if (! function_exists('is_product') || ! is_product()) {
        return;
    }

    $product_id = (int) get_queried_object_id();
    if (! matrix_rd_is_custom_order_product($product_id)) {
        return;
    }

    $permalink = get_permalink($product_id);
    $login_url = matrix_rd_custom_order_login_url(
        is_string($permalink) && $permalink !== '' ? $permalink : home_url('/custom-order/')
    );
    $cart_url = function_exists('wc_get_cart_url') ? (string) wc_get_cart_url() : home_url('/cart/');

    $destination = matrix_rd_guest_custom_order_redirect_url(
        is_user_logged_in(),
        matrix_rd_cart_contains_custom_order(),
        $cart_url,
        $login_url
    );

    if ($destination === null || $destination === '') {
        return;
    }

    wp_safe_redirect($destination, 302);
    exit;
}
add_action('template_redirect', 'matrix_rd_redirect_guest_custom_order_page', 6);

/**
 * Keep Custom Order out of shop loops, related products, and catalog widgets.
 * Cart restore / shared-cart retrieve still work because those use purchasable,
 * not visibility.
 */
function matrix_rd_hide_custom_order_from_catalog(bool $visible, $product_id): bool {
    if (matrix_rd_is_custom_order_product((int) $product_id)) {
        return false;
    }

    return $visible;
}
add_filter('woocommerce_product_is_visible', 'matrix_rd_hide_custom_order_from_catalog', 10, 2);

function matrix_rd_exclude_custom_order_from_sitemaps(array $ids): array {
    $custom_id = matrix_rd_custom_order_product_id();
    if ($custom_id > 0) {
        $ids[] = $custom_id;
    }

    return $ids;
}
add_filter('wpseo_exclude_from_sitemap_by_post_ids', 'matrix_rd_exclude_custom_order_from_sitemaps');

function matrix_rd_custom_order_robots(array $robots): array {
    if (function_exists('is_product') && is_product()
        && matrix_rd_is_custom_order_product((int) get_queried_object_id())
    ) {
        $robots['noindex'] = true;
        $robots['nofollow'] = true;
    }

    return $robots;
}
add_filter('wp_robots', 'matrix_rd_custom_order_robots');

/**
 * Guests must not create a Custom Order via the box-builder AJAX endpoint.
 * Shared-cart retrieve loads session data and never hits this action.
 */
function matrix_rd_block_guest_custom_order_ajax(): void {
    $product_id = isset($_POST['donut_box_product_id']) ? (int) $_POST['donut_box_product_id'] : 0;
    if ($product_id <= 0 || ! matrix_rd_is_custom_order_product($product_id)) {
        return;
    }

    wp_send_json([
        'success' => false,
        'message' => __('Please log in to create a custom order.', 'matrix-starter'),
        'data'    => [
            'message' => __('Please log in to create a custom order.', 'matrix-starter'),
        ],
    ]);
}
add_action('wp_ajax_nopriv_donut_box_add_to_cart', 'matrix_rd_block_guest_custom_order_ajax', 1);

/**
 * Block the plain ?add-to-cart= custom-order URL for guests. Session restore
 * and site-monitor add_to_cart() calls do not set that request key.
 */
function matrix_rd_block_guest_custom_order_add_to_cart(bool $passed, $product_id): bool {
    if (! $passed || is_user_logged_in() || is_admin()) {
        return $passed;
    }
    if (! isset($_REQUEST['add-to-cart'])) {
        return $passed;
    }
    if (! matrix_rd_is_custom_order_product((int) $product_id)) {
        return $passed;
    }

    wc_add_notice(__('Please log in to create a custom order.', 'matrix-starter'), 'error');

    return false;
}
add_filter('woocommerce_add_to_cart_validation', 'matrix_rd_block_guest_custom_order_add_to_cart', 10, 2);

/**
 * Body classes used by woocommerce-header.php (legacy rd-product-type-{slug}).
 */
function matrix_rd_product_type_body_class(array $classes): array {
    if (! is_singular('product')) {
        return $classes;
    }

    $slug = matrix_rd_product_type_slug((int) get_queried_object_id());
    if ($slug) {
        $classes[] = 'rd-product-type-' . sanitize_html_class($slug);
    }

    return $classes;
}
add_filter('body_class', 'matrix_rd_product_type_body_class');

function matrix_rd_breadcrumb_delimiter(array $defaults): array {
    $defaults['delimiter'] = ' &gt; ';
    return $defaults;
}
add_filter('woocommerce_breadcrumb_defaults', 'matrix_rd_breadcrumb_delimiter');

/**
 * Product breadcrumbs: Home > Our Boxes / Our Donuts / Our Merch > product title.
 */
function matrix_rd_customize_product_breadcrumbs(array $crumbs, $breadcrumb): array {
    if (! is_product()) {
        return $crumbs;
    }

    global $post;
    if (! $post) {
        return $crumbs;
    }

    $terms = wp_get_post_terms($post->ID, 'rd_product_type');
    if (is_wp_error($terms) || empty($terms)) {
        return $crumbs;
    }

    $product_type = $terms[0]->name;

    if (strcasecmp($product_type, 'Donut') === 0) {
        array_splice($crumbs, -2, 1);
        array_splice($crumbs, -1, 0, array(array(__('Our Donuts', 'rolling-donut'), home_url('/our-donuts/'))));
    } elseif (strcasecmp($product_type, 'Merch') === 0) {
        array_splice($crumbs, -2, 1);
        array_splice($crumbs, -1, 0, array(array(__('Our Merch', 'rolling-donut'), home_url('/merch/'))));
    } elseif (strcasecmp($product_type, 'Box') === 0) {
        array_splice($crumbs, -2, 1);
        array_splice($crumbs, -1, 0, array(array(__('Our Boxes', 'rolling-donut'), home_url('/donut-box/'))));
    }

    return $crumbs;
}
add_filter('woocommerce_get_breadcrumb', 'matrix_rd_customize_product_breadcrumbs', 20, 2);

/**
 * Main shop query: donut products, excluding vegan tag (vegan block is separate).
 */
function matrix_rd_shop_pre_get_posts(WP_Query $query): void {
    if (is_admin() || ! $query->is_main_query() || ! function_exists('is_shop') || ! is_shop()) {
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
        'relation' => 'AND',
        [
            'taxonomy' => 'rd_product_type',
            'field'    => 'slug',
            'terms'    => 'donut',
            'operator' => 'IN',
        ],
        [
            'taxonomy' => 'product_tag',
            'field'    => 'slug',
            'terms'    => 'vegan',
            'operator' => 'NOT IN',
        ],
    ];

    $query->set('tax_query', $tax_query);
    $query->set('posts_per_page', -1);
}
add_action('pre_get_posts', 'matrix_rd_shop_pre_get_posts');

function matrix_rd_is_rd_page(): bool {
    if (is_front_page()
        || (function_exists('is_shop') && is_shop())
        || (function_exists('is_product') && is_product())
        || is_home()
        || is_category()
        || is_singular('post')
    ) {
        return true;
    }

    if (is_page()) {
        return ! (function_exists('is_cart') && is_cart())
            && ! (function_exists('is_checkout') && is_checkout());
    }

    return false;
}

/**
 * Load flexi blocks on default pages only when legacy meta exists.
 */
function matrix_rd_load_page_flexi_if_present(int $post_id): void {
    if (! function_exists('load_flexible_content_templates')) {
        return;
    }

    foreach (['flexible_content', 'flexible_content_blocks'] as $field) {
        $raw = get_post_meta($post_id, $field, true);
        if ($raw !== '' && $raw !== false && $raw !== '0') {
            load_flexible_content_templates($post_id);
            return;
        }
    }
}

function matrix_rd_pages_enqueue_assets(): void {
    if (is_admin()) {
        return;
    }

    $is_rd_context = matrix_rd_is_rd_page()
        || (function_exists('is_account_page') && is_account_page())
        || (function_exists('is_cart') && is_cart())
        || (function_exists('is_checkout') && is_checkout());

    if (! $is_rd_context) {
        return;
    }

    $theme_version = get_option('theme_css_version', '1.0');

    if (is_readable(get_template_directory() . '/assets/css/rolling-donut-legacy.css')) {
        wp_enqueue_style(
            'matrix-rd-legacy',
            get_template_directory_uri() . '/assets/css/rolling-donut-legacy.css',
            [],
            $theme_version
        );
    }

    if (function_exists('is_account_page') && is_account_page()
        && is_readable(get_template_directory() . '/assets/css/rolling-donut-myaccount.css')) {
        $myaccount_css = get_template_directory() . '/assets/css/rolling-donut-myaccount.css';
        wp_enqueue_style(
            'matrix-rd-myaccount',
            get_template_directory_uri() . '/assets/css/rolling-donut-myaccount.css',
            ['matrix-rd-legacy', 'matrix-starter'],
            (string) filemtime($myaccount_css)
        );
    }

    if (function_exists('is_shop') && is_shop()) {
        wp_enqueue_style('slick-css');
        wp_enqueue_script('slick-js');
    }

    if (function_exists('is_product') && is_product()) {
        $product = wc_get_product(get_queried_object_id());
        if ($product instanceof WC_Product && $product->get_type() === 'donut_box_builder') {
            wp_enqueue_style('slick-css');
            wp_enqueue_script('slick-js');
        }
    }

    if (is_page('our-shops')) {
        wp_enqueue_style('leaflet');
        wp_enqueue_script('leaflet');
    }

    if (is_page('donut-box')
        && is_readable(get_template_directory() . '/assets/css/rolling-donut-box-archive.css')) {
        wp_enqueue_style(
            'matrix-rd-box-archive',
            get_template_directory_uri() . '/assets/css/rolling-donut-box-archive.css',
            ['matrix-rd-legacy', 'matrix-starter'],
            $theme_version
        );
    }
}
add_action('wp_enqueue_scripts', 'matrix_rd_pages_enqueue_assets', 30);

/**
 * Cart page: quantity +/- controls and WooCommerce-native AJAX updates.
 */
function matrix_rd_cart_scripts(): void {
    if (! function_exists('is_cart') || ! is_cart()) {
        return;
    }

    $path = get_template_directory() . '/assets/js/rolling-donut-cart.js';
    if (! is_readable($path)) {
        return;
    }

    wp_enqueue_script(
        'matrix-rd-cart',
        get_template_directory_uri() . '/assets/js/rolling-donut-cart.js',
        ['jquery', 'wc-cart'],
        (string) filemtime($path),
        true
    );
}
add_action('wp_enqueue_scripts', 'matrix_rd_cart_scripts', 35);

/**
 * WP_Query args for catalog listings that should match the shop (published, visible, stable order).
 *
 * @param array<string, mixed> $args Extra args such as tax_query.
 * @return array<string, mixed>
 */
function matrix_rd_catalog_product_query_args(array $args = []): array {
    $defaults = [
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => [
            'date' => 'DESC',
            'ID'   => 'DESC',
        ],
    ];

    $merged = array_merge($defaults, $args);

    $visibility = [
        'taxonomy' => 'product_visibility',
        'field'    => 'name',
        'terms'    => ['exclude-from-catalog', 'exclude-from-search'],
        'operator' => 'NOT IN',
    ];

    if (! isset($merged['tax_query']) || ! is_array($merged['tax_query'])) {
        $merged['tax_query'] = [$visibility];
    } else {
        $merged['tax_query'][] = $visibility;
    }

    return $merged;
}

/**
 * Render products from a custom query using the theme product card.
 */
function matrix_rd_render_product_query(WP_Query $query, bool $bare_list_items = false): void {
    if (! $query->have_posts()) {
        return;
    }

    if (! $bare_list_items) {
        remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
        remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
        do_action('woocommerce_before_shop_loop');
        woocommerce_product_loop_start();
    }

    while ($query->have_posts()) {
        $query->the_post();
        global $product;
        $product = wc_get_product(get_the_ID());
        if (! $product instanceof WC_Product) {
            continue;
        }
        do_action('woocommerce_shop_loop');
        wc_get_template_part('content', 'product');
    }

    if (! $bare_list_items) {
        woocommerce_product_loop_end();
    }
    wp_reset_postdata();
}

/**
 * AJAX: filter merch/box products by product category (legacy filter_products).
 */
function matrix_rd_ajax_filter_products(): void {
    $category    = isset($_POST['category']) ? sanitize_text_field(wp_unslash((string) $_POST['category'])) : '';
    $product_type = isset($_POST['productType']) ? sanitize_text_field(wp_unslash((string) $_POST['productType'])) : '';

    if ($product_type === '' || $category === '') {
        wp_die('');
    }

    $tax_query = [
        'relation' => 'AND',
        [
            'taxonomy' => 'rd_product_type',
            'field'    => 'slug',
            'terms'    => strtolower($product_type),
        ],
    ];

    if ($category !== 'all') {
        $tax_query[] = [
            'taxonomy' => 'product_cat',
            'field'    => 'term_id',
            'terms'    => (int) $category,
        ];
    }

    $products = new WP_Query(matrix_rd_catalog_product_query_args([
        'tax_query' => $tax_query,
    ]));

    ob_start();
    if ($products->have_posts()) {
        while ($products->have_posts()) {
            $products->the_post();
            global $product;
            $product = wc_get_product(get_the_ID());
            if (! $product instanceof WC_Product) {
                continue;
            }
            do_action('woocommerce_shop_loop');
            wc_get_template_part('content', 'product');
        }
        wp_reset_postdata();
    } else {
        echo '<li class="w-full py-8 text-center">' . esc_html__('No products found for this category.', 'matrix-starter') . '</li>';
    }

    echo ob_get_clean();
    wp_die();
}
add_action('wp_ajax_filter_products', 'matrix_rd_ajax_filter_products');
add_action('wp_ajax_nopriv_filter_products', 'matrix_rd_ajax_filter_products');

/**
 * Enqueue merch/box filter script on product listing pages.
 */
function matrix_rd_product_filter_scripts(): void {
    if (! is_page(['merch', 'donut-box'])) {
        return;
    }

    $product_type = is_page('merch') ? 'Merch' : 'Box';
    $version      = get_option('theme_css_version', '1.0');

    $gallery_path = get_template_directory() . '/assets/js/rolling-donut-box-gallery.js';
    if (is_readable($gallery_path)) {
        wp_enqueue_script(
            'matrix-rd-box-gallery',
            get_template_directory_uri() . '/assets/js/rolling-donut-box-gallery.js',
            [],
            (string) filemtime($gallery_path),
            true
        );
    }

    wp_enqueue_script(
        'matrix-rd-product-filter',
        get_template_directory_uri() . '/assets/js/rolling-donut-product-filter.js',
        [],
        $version,
        true
    );

    wp_localize_script(
        'matrix-rd-product-filter',
        'matrixRdProductFilter',
        [
            'ajaxUrl'     => admin_url('admin-ajax.php'),
            'productType' => $product_type,
        ]
    );
}
add_action('wp_enqueue_scripts', 'matrix_rd_product_filter_scripts', 35);

/**
 * Use custom Splide gallery instead of WooCommerce Flexslider/Photoswipe.
 */
function matrix_rd_disable_wc_default_product_gallery(): void {
    remove_theme_support('wc-product-gallery-zoom');
    remove_theme_support('wc-product-gallery-lightbox');
    remove_theme_support('wc-product-gallery-slider');
}
add_action('after_setup_theme', 'matrix_rd_disable_wc_default_product_gallery', 100);

/**
 * Splide + product page CSS/JS (single product gallery + add to cart styling).
 */
function matrix_rd_single_product_assets(): void {
    if (is_admin() || ! function_exists('is_product') || ! is_product()) {
        return;
    }

    $version = get_option('theme_css_version', '1.0');

    wp_enqueue_style(
        'splide',
        'https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/css/splide.min.css',
        [],
        '4.1.4'
    );
    wp_enqueue_script(
        'splide',
        'https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/js/splide.min.js',
        [],
        '4.1.4',
        true
    );

    $product_css = get_template_directory() . '/assets/css/rolling-donut-product.css';
    wp_enqueue_style(
        'matrix-rd-product',
        get_template_directory_uri() . '/assets/css/rolling-donut-product.css',
        ['matrix-rd-legacy', 'splide'],
        file_exists($product_css) ? (string) filemtime($product_css) : $version
    );

    wp_enqueue_script(
        'matrix-rd-product-gallery',
        get_template_directory_uri() . '/assets/js/rolling-donut-product-gallery.js',
        ['splide'],
        $version,
        true
    );

    $bundle_list_js = get_template_directory() . '/assets/js/rolling-donut-woosb-bundle-list.js';
    wp_enqueue_script(
        'matrix-rd-woosb-bundle-list',
        get_template_directory_uri() . '/assets/js/rolling-donut-woosb-bundle-list.js',
        [],
        file_exists($bundle_list_js) ? (string) filemtime($bundle_list_js) : $version,
        true
    );

    wp_enqueue_script(
        'matrix-rd-quantity',
        get_template_directory_uri() . '/assets/js/rolling-donut-quantity.js',
        ['jquery'],
        $version,
        true
    );

    wp_dequeue_script('wc-single-product');
    wp_dequeue_script('photoswipe-ui-default');
    wp_dequeue_script('photoswipe');
    wp_dequeue_style('photoswipe-default-skin');
    wp_dequeue_style('photoswipe');
}
add_action('wp_enqueue_scripts', 'matrix_rd_single_product_assets', 40);

/**
 * Make bundled-box product names render cleanly on the PDF invoice / packing slip.
 *
 * Two characters have no glyph in the PDF font (DejaVu Sans via dompdf) and show
 * up as "?" in the generated document:
 *   - the WPC Product Bundles separator "→" (U+2192, from `woosb_name_separator`),
 *     e.g. "Midi Sourdough Donuts → Blueberry Cheesecake";
 *   - the "🌈" emoji in "Pride Edition 🌈 …" product titles.
 *
 * WPC also prefixes each bundled flavour with the parent box title
 * ("Football Team – Large Sourdough → Mini Nutella and Marshmallow - Large").
 * Legacy invoices printed only the flavour name; packing slips already do that
 * via `$item->get_name()`. Drop the parent prefix here so invoices match.
 *
 * This only runs in the PDF document context (`wpo_wcpdf_order_item_name`), so the
 * website, cart and emails keep the original arrow, emoji and prefix untouched.
 *
 * @param string $name  Item name (may contain HTML such as an anchor tag).
 * @param mixed  $item  WC_Order_Item or array from WCPDF.
 * @param mixed  $order Unused; accepted so the filter can receive 3 arguments.
 * @return string
 */
function matrix_rd_clean_pdf_item_name($name, $item = null, $order = null) {
    unset($order);

    if (! is_string($name) || $name === '') {
        return $name;
    }

    $stored = matrix_rd_pdf_item_stored_name($item);
    if ($stored !== '' && (matrix_rd_pdf_item_is_bundled_child($item) || matrix_rd_pdf_name_has_bundle_prefix($name, $stored))) {
        $name = $stored;
    }

    // Normalise the bundle separator (→) to an en dash the PDF font can render.
    $name = str_replace(['&rarr;', '&#8594;', "\xE2\x86\x92"], '&ndash;', $name);

    // Drop emoji / pictographs / symbols the PDF font can't render (e.g. 🌈).
    $stripped = preg_replace(
        '/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}\x{2B00}-\x{2BFF}\x{FE00}-\x{FE0F}\x{200D}]/u',
        '',
        $name
    );
    if (null !== $stripped) {
        $name = $stripped;
    }

    // Tidy whitespace left behind ("Pride Edition  - Midi" -> "Pride Edition - Midi").
    $collapsed = preg_replace('/\s{2,}/u', ' ', $name);
    if (null !== $collapsed) {
        $name = $collapsed;
    }

    return trim($name);
}
add_filter('wpo_wcpdf_order_item_name', 'matrix_rd_clean_pdf_item_name', 20, 3);

/**
 * Stored order-item name (no WPC parent-box prefix).
 *
 * @param mixed $item WC_Order_Item, array with a nested item, or null.
 * @return string
 */
function matrix_rd_pdf_item_stored_name($item) {
    $item = matrix_rd_pdf_unwrap_item($item);

    if (is_object($item) && method_exists($item, 'get_name')) {
        return (string) $item->get_name();
    }

    return '';
}

/**
 * True when the line is a WPC bundled flavour (child of a box).
 *
 * @param mixed $item
 */
function matrix_rd_pdf_item_is_bundled_child($item): bool {
    $item = matrix_rd_pdf_unwrap_item($item);

    if (is_object($item) && method_exists($item, 'get_meta')) {
        if ($item->get_meta('_woosb_parent_id') || $item->get_meta('woosb_parent_id')) {
            return true;
        }
    }

    if (is_array($item) || (is_object($item) && $item instanceof \ArrayAccess)) {
        if (! empty($item['woosb_parent_id']) || ! empty($item['_woosb_parent_id'])) {
            return true;
        }
    }

    return false;
}

/**
 * True when $name is "Parent → Child" (or en/em dash) and $stored is "Child".
 *
 * ASCII hyphen is ignored so "Mini Nutella - Large" is not treated as a prefix.
 */
function matrix_rd_pdf_name_has_bundle_prefix(string $name, string $stored): bool {
    $stored_plain = trim(wp_strip_all_tags($stored));
    $name_plain   = trim(wp_strip_all_tags(html_entity_decode($name, ENT_QUOTES, 'UTF-8')));
    if ($stored_plain === '' || $name_plain === $stored_plain || ! str_ends_with($name_plain, $stored_plain)) {
        return false;
    }

    $prefix = rtrim(substr($name_plain, 0, -strlen($stored_plain)));
    if ($prefix === '') {
        return false;
    }

    return (bool) preg_match('/(?:→|–|—)$/u', $prefix);
}

/**
 * @param mixed $item
 * @return mixed
 */
function matrix_rd_pdf_unwrap_item($item) {
    if (is_array($item) && isset($item['item'])) {
        return $item['item'];
    }

    return $item;
}

/**
 * Use a single, unambiguous "Collection / Delivery Date" label on the PDF documents.
 *
 * The Iconic Delivery Slots plugin appends a date row to the order totals and tries
 * to auto-pick "Delivery Date" vs "Collection Date" per order. That detection is
 * unreliable for this store's local-pickup/delivery setup (e.g. a "Free Collection"
 * order still rendered "Delivery Date"), so we show one clear combined label.
 *
 * Scoped to PDF generation only (registered on `wpo_wcpdf_before_document`) so the
 * website order pages, emails and account view keep the plugin's default wording.
 */
function matrix_rd_pdf_register_delivery_label($document_type = '', $order = null) {
    add_filter('iconic_wds_labels_by_type', 'matrix_rd_pdf_combined_delivery_label', 20);
    add_filter('woocommerce_order_item_get_formatted_meta_data', 'matrix_rd_pdf_strip_internal_item_meta', 20, 2);
}
add_action('wpo_wcpdf_before_document', 'matrix_rd_pdf_register_delivery_label', 10, 2);

/**
 * Drop internal box-builder / bundle keys from PDF line items.
 *
 * Legacy packing slips only printed customer-facing meta such as "Logo Upload".
 * WPC Bundles stores a "box: Default Box" row that should not appear.
 *
 * @param array<int, object> $formatted_meta
 * @param mixed              $item
 * @return array<int, object>
 */
function matrix_rd_pdf_strip_internal_item_meta($formatted_meta, $item) {
    if (! is_array($formatted_meta) || $formatted_meta === []) {
        return $formatted_meta;
    }

    $hide = [
        'box',
        '_box',
        'pa_size',
        '_pa_size',
        'unique_key',
        '_unique_key',
        'part_of_box',
        '_part_of_box',
        'parent_item_key',
        '_parent_item_key',
        'donut_box_contents',
        '_donut_box_contents',
    ];

    foreach ($formatted_meta as $meta_id => $meta) {
        $key = isset($meta->key) ? (string) $meta->key : '';
        $display_key = isset($meta->display_key) ? (string) $meta->display_key : '';
        if (in_array($key, $hide, true) || in_array(strtolower($display_key), $hide, true)) {
            unset($formatted_meta[$meta_id]);
        }
    }

    return $formatted_meta;
}

/**
 * PDF engine cannot render GIF/WebP logos. Prefer a PNG sibling, then the site stamp.
 *
 * @param int|string $logo_id
 * @return int
 */
function matrix_rd_pdf_header_logo_id($logo_id, $document = null) {
    $id = absint($logo_id);
    $file = $id ? (string) get_attached_file($id) : '';
    $ext = $file !== '' ? strtolower((string) pathinfo($file, PATHINFO_EXTENSION)) : '';

    if ($file !== '' && is_readable($file) && ! in_array($ext, ['gif', 'webp'], true)) {
        return $id;
    }

    $png_id = absint(get_option('matrix_rd_pdf_logo_attachment_id'));
    if ($png_id > 0) {
        $png_file = (string) get_attached_file($png_id);
        if ($png_file !== '' && is_readable($png_file)) {
            return $png_id;
        }
    }

    $site_logo = absint(get_theme_mod('custom_logo'));
    if ($site_logo > 0 && $site_logo !== $id) {
        return (int) matrix_rd_pdf_header_logo_id($site_logo, $document);
    }

    return $id;
}
add_filter('wpo_wcpdf_header_logo_id', 'matrix_rd_pdf_header_logo_id', 20, 2);

/**
 * @param array $labels Iconic labels grouped by 'delivery' / 'collection'.
 * @return array
 */
function matrix_rd_pdf_combined_delivery_label($labels) {
    if (! is_array($labels)) {
        return $labels;
    }

    $combined = __('Collection / Delivery Date', 'matrix-starter');

    if (isset($labels['delivery']['date'])) {
        $labels['delivery']['date'] = $combined;
    }
    if (isset($labels['collection']['date'])) {
        $labels['collection']['date'] = $combined;
    }

    return $labels;
}

/**
 * Pickup location name + address for My Account view-order (legacy order-details-customer).
 *
 * Local Pickup Plus stores these on the shipping line item, not order post meta.
 *
 * @return array{name: string, address: string}
 */
function matrix_rd_get_order_pickup_display($order): array {
    $empty = ['name' => '', 'address' => ''];
    if (! $order instanceof WC_Order) {
        return $empty;
    }

    $name    = '';
    $address = '';

    foreach ($order->get_items('shipping') as $item) {
        $item_name = trim((string) $item->get_meta('_pickup_location_name'));
        $item_addr = $item->get_meta('_pickup_location_address');
        if ($item_name === '' && $item_addr === '' && $item_addr !== '0') {
            continue;
        }
        if ($item_name !== '') {
            $name = $item_name;
        }
        if ($item_addr !== '' && $item_addr !== null && $item_addr !== false) {
            $address = matrix_rd_format_pickup_address($item_addr);
        }
        if ($name !== '' || $address !== '') {
            break;
        }
    }

    if ($name === '') {
        $name = trim((string) $order->get_meta('_pickup_location_name'));
    }
    if ($address === '') {
        $order_addr = $order->get_meta('_pickup_location_address');
        if ($order_addr !== '' && $order_addr !== null && $order_addr !== false) {
            $address = matrix_rd_format_pickup_address($order_addr);
        }
    }

    return [
        'name'    => $name,
        'address' => $address,
    ];
}

/**
 * @param mixed $address Serialized array, WC address array, or plain string.
 */
function matrix_rd_format_pickup_address($address): string {
    if (is_string($address)) {
        $maybe = maybe_unserialize($address);
        if (is_array($maybe)) {
            $address = $maybe;
        } else {
            return nl2br(esc_html($address));
        }
    }

    if (! is_array($address) || $address === []) {
        return '';
    }

    if (function_exists('WC') && WC()->countries) {
        $formatted = WC()->countries->get_formatted_address($address);
        if (is_string($formatted) && $formatted !== '') {
            return $formatted;
        }
    }

    $parts = [];
    foreach ($address as $value) {
        if (is_string($value) && trim($value) !== '') {
            $parts[] = $value;
        }
    }

    return $parts === [] ? '' : esc_html(implode(', ', $parts));
}

/**
 * One Product / Total row on the view-order page (legacy flex layout).
 */
function matrix_rd_render_view_order_item_row($item_id, $item, $order): void {
    if (! $item || ! $order instanceof WC_Order) {
        return;
    }

    if (! apply_filters('woocommerce_order_item_visible', true, $item)) {
        return;
    }

    $product = $item->get_product();
    ?>
    <div class="rd-view-order__row flex justify-between bg-white border-b-2 line-item border-grey-disabled last:border-b-0">
        <div class="rd-view-order__product px-4 mobile:px-10 py-5 text-left item-ordered">
            <?php
            wc_get_template('order/order-details-item.php', [
                'item_id'            => $item_id,
                'item'               => $item,
                'order'              => $order,
                'product'            => $product,
                'show_purchase_note' => $order->has_status(apply_filters('woocommerce_purchase_note_order_statuses', ['completed', 'processing'])),
                'purchase_note'      => $product ? $product->get_purchase_note() : '',
                'show_price'         => false,
            ]);
            ?>
        </div>
        <div class="rd-view-order__total px-4 mobile:px-10 py-5 text-center">
            <span class="woocommerce-Price-amount amount"><?php echo $order->get_formatted_line_subtotal($item); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
        </div>
    </div>
    <?php
}

/**
 * Whether the current request is the Iconic Delivery Slots deliveries screen.
 */
function matrix_rd_is_wds_deliveries_admin_page(): bool {
    if (! is_admin()) {
        return false;
    }

    $page = isset($_GET['page']) ? sanitize_key(wp_unslash((string) $_GET['page'])) : '';

    return $page === 'jckwds-deliveries';
}

/**
 * Drop the redundant "Delivery Details" / "Collection Details" heading from
 * the Ship to column on WooCommerce → Deliveries.
 */
function matrix_rd_blank_wds_details_label_on_deliveries_admin($labels, $order = null) {
    if (! matrix_rd_is_wds_deliveries_admin_page() || ! is_array($labels)) {
        return $labels;
    }

    $labels['details'] = '';

    return $labels;
}
add_filter('iconic_wds_labels', 'matrix_rd_blank_wds_details_label_on_deliveries_admin', 20, 2);
add_filter('woocommerce_order_get_formatted_shipping_address', 'matrix_rd_admin_collection_formatted_shipping_address', 20, 3);
add_filter('woocommerce_shipping_address_map_url', 'matrix_rd_admin_collection_shipping_map_url', 20, 2);
add_filter('woocommerce_admin_shipping_fields', 'matrix_rd_hide_admin_shipping_fields_for_collection', 30, 2);
add_filter('admin_body_class', 'matrix_rd_admin_collection_body_class');
add_action('admin_head', 'matrix_rd_admin_collection_order_edit_assets');

/**
 * Hide the empty method-label wrapper left after blanking the heading.
 */
function matrix_rd_hide_wds_deliveries_method_label_css(): void {
    if (! matrix_rd_is_wds_deliveries_admin_page()) {
        return;
    }
    ?>
    <style>
        .iconic-wds-delivery td > div:first-child:has(> strong:only-child) {
            display: none;
        }
        .iconic-wds-delivery td[data-colname="Ship to"] a:not([href*="http"]) {
            pointer-events: none;
            text-decoration: none;
            color: inherit;
            cursor: default;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('tr.iconic-wds-delivery td[data-colname="Ship to"]').forEach(function (cell) {
                var via = cell.querySelector('.description');
                if (!via || (via.textContent || '').toLowerCase().indexOf('collection') === -1) {
                    return;
                }
                var link = cell.querySelector('a');
                if (link) {
                    link.style.display = 'none';
                }
            });
        });
    </script>
    <?php
}
add_action('admin_head', 'matrix_rd_hide_wds_deliveries_method_label_css');

add_filter('manage_edit-shop_order_columns', 'matrix_rd_add_order_shipping_zone_column', 20);
add_action('manage_shop_order_posts_custom_column', 'matrix_rd_render_order_shipping_zone_column', 10, 2);
add_filter('woocommerce_shop_order_list_table_columns', 'matrix_rd_add_order_shipping_zone_column', 20);
add_action('woocommerce_shop_order_list_table_custom_column', 'matrix_rd_render_order_shipping_zone_column', 10, 2);

add_filter('manage_edit-shop_order_columns', 'matrix_rd_reorder_shop_order_list_columns', 999);
add_filter('woocommerce_shop_order_list_table_columns', 'matrix_rd_reorder_shop_order_list_columns', 999);
add_filter('hidden_columns', 'matrix_rd_shop_order_list_hidden_columns', 20, 2);
add_filter('default_hidden_columns', 'matrix_rd_shop_order_list_hidden_columns', 20, 2);

add_action('load-edit.php', 'matrix_rd_shop_order_list_redirect_to_delivery_sort');
add_action('load-woocommerce_page_wc-orders', 'matrix_rd_shop_order_list_redirect_to_delivery_sort');
add_action('pre_get_posts', 'matrix_rd_shop_order_list_clear_iconic_meta_sort', 20);
add_filter('posts_clauses', 'matrix_rd_shop_order_list_posts_clauses', 20, 2);
add_filter('iconic_wds_reservations_pre_query', 'matrix_rd_wds_reservations_pre_query', 10, 2);

