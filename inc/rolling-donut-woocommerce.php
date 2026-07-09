<?php
/**
 * Rolling Donut WooCommerce helpers.
 */

require_once __DIR__ . '/rolling-donut-myaccount-auth.php';
require_once __DIR__ . '/rolling-donut-cart.php';
require_once __DIR__ . '/rolling-donut-side-cart.php';
require_once __DIR__ . '/rolling-donut-checkout.php';

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
        wp_enqueue_style(
            'matrix-rd-myaccount',
            get_template_directory_uri() . '/assets/css/rolling-donut-myaccount.css',
            ['matrix-rd-legacy', 'matrix-starter'],
            $theme_version
        );
    }

    if (function_exists('is_shop') && is_shop()) {
        wp_enqueue_style('slick-css');
        wp_enqueue_script('slick-js');
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

    $products = new WP_Query([
        'post_type'      => 'product',
        'posts_per_page' => -1,
        'tax_query'      => $tax_query,
    ]);

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

    wp_enqueue_style(
        'matrix-rd-product',
        get_template_directory_uri() . '/assets/css/rolling-donut-product.css',
        ['matrix-rd-legacy', 'splide'],
        $version
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
 * This only runs in the PDF document context (`wpo_wcpdf_order_item_name`), so the
 * website, cart and emails keep the original arrow and emoji untouched.
 *
 * @param string $name Item name (may contain HTML such as an anchor tag).
 * @return string
 */
function matrix_rd_clean_pdf_item_name($name) {
    if (! is_string($name) || $name === '') {
        return $name;
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
add_filter('wpo_wcpdf_order_item_name', 'matrix_rd_clean_pdf_item_name', 20);

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
}
add_action('wpo_wcpdf_before_document', 'matrix_rd_pdf_register_delivery_label', 10, 2);

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

