<?php
/**
 * Single product layout hooks (legacy Rolling Donut behaviour).
 */
defined('ABSPATH') || exit;

/**
 * Whether a product uses one of the box-builder layouts (the WPC bundle with the
 * "Enable Box Builder" flag, or the legacy donut_box_builder type). Box-builder
 * pages keep the full hero header and own their own title/price presentation;
 * everything else gets the slimmer "image first, price beside title" layout.
 */
function matrix_rd_is_box_builder_product($product = null): bool
{
    if ($product === null) {
        $product = $GLOBALS['product'] ?? null;
    }
    if (is_numeric($product)) {
        $product = wc_get_product($product);
    }
    if (! $product instanceof WC_Product) {
        return false;
    }
    if (function_exists('rd_box_builder_is_enabled') && rd_box_builder_is_enabled($product)) {
        return true;
    }

    return $product->is_type('donut_box_builder');
}

/**
 * Number of items in a box product (ACF box_number, then WOOSB whole limit, then legacy meta).
 */
function matrix_rd_get_product_box_count($product = null): int
{
    if ($product === null) {
        $product = $GLOBALS['product'] ?? null;
    }
    if (is_numeric($product)) {
        $product = wc_get_product($product);
    }
    if (! $product instanceof WC_Product) {
        return 0;
    }

    if (function_exists('get_field')) {
        $box_number = get_field('box_number', $product->get_id());
        if ($box_number !== '' && $box_number !== null) {
            return max(0, (int) $box_number);
        }
    }

    if ($product->is_type('woosb')) {
        $whole_max = (int) $product->get_meta('woosb_limit_whole_max');
        if ($whole_max > 0) {
            return $whole_max;
        }
    }

    $legacy_qty = (int) get_post_meta($product->get_id(), '_donut_box_builder_box_quantity', true);
    if ($legacy_qty > 0) {
        return $legacy_qty;
    }

    return 0;
}

/**
 * @return string Empty when the product has no configured box size.
 */
function matrix_rd_get_box_count_label(int $count): string
{
    if ($count <= 0) {
        return '';
    }

    return sprintf(
        /* translators: %d: number of items in the box */
        __('Box of %d', 'matrix-starter'),
        $count
    );
}

/**
 * "Box of N" badge — matches the donut-box archive cards.
 *
 * @param WC_Product|int|null $product
 */
function matrix_rd_render_box_count_badge($product = null, string $modifier = ''): void
{
    $count = matrix_rd_get_product_box_count($product);
    $label = matrix_rd_get_box_count_label($count);
    if ($label === '') {
        return;
    }

    $classes = 'rd-box-count-badge';
    if ($modifier !== '') {
        $classes .= ' rd-box-count-badge--'.sanitize_html_class($modifier);
    }

    printf('<span class="%s">%s</span>', esc_attr($classes), esc_html($label));
}

/**
 * Bundle child products whose allergens should appear on the gallery overlay.
 *
 * @return WC_Product[]
 */
function matrix_rd_get_bundle_products_for_allergens(WC_Product $product): array
{
    if (! $product->is_type('woosb') || ! method_exists($product, 'get_items')) {
        return array();
    }

    $rd_is_box_builder = function_exists('rd_box_builder_is_enabled') && rd_box_builder_is_enabled($product);
    $bundle_items      = (array) $product->get_items();
    $selected          = array();

    foreach ($bundle_items as $bundle_item) {
        $bundle_item_id  = isset($bundle_item['id']) ? (int) $bundle_item['id'] : 0;
        $bundle_item_qty = isset($bundle_item['qty']) ? (int) $bundle_item['qty'] : 0;
        if ($bundle_item_id <= 0 || $bundle_item_qty <= 0) {
            continue;
        }
        $bundle_product = wc_get_product($bundle_item_id);
        if ($bundle_product instanceof WC_Product) {
            $selected[] = $bundle_product;
        }
    }

    if ($selected === array() && ! $rd_is_box_builder) {
        foreach ($bundle_items as $bundle_item) {
            $bundle_item_id = isset($bundle_item['id']) ? (int) $bundle_item['id'] : 0;
            if ($bundle_item_id <= 0) {
                continue;
            }
            $bundle_product = wc_get_product($bundle_item_id);
            if ($bundle_product instanceof WC_Product) {
                $selected[] = $bundle_product;
            }
        }
    }

    return $selected;
}

/**
 * Unique allergen posts for a product — unions every flavour in a bundle box.
 *
 * @param WC_Product|int|null $product
 * @return WP_Post[]
 */
function matrix_rd_get_product_allergen_posts($product = null): array
{
    if ($product === null) {
        $product = $GLOBALS['product'] ?? null;
    }
    if (is_numeric($product)) {
        $product = wc_get_product($product);
    }
    if (! $product instanceof WC_Product || ! function_exists('get_field')) {
        return array();
    }

    $source_ids = array();
    if ($product->is_type('woosb') && method_exists($product, 'get_items')) {
        foreach (matrix_rd_get_bundle_products_for_allergens($product) as $bundle_product) {
            if (function_exists('dbb_resolve_allergen_product_id')) {
                $source_ids[] = dbb_resolve_allergen_product_id($bundle_product->get_id());
            } else {
                $source_ids[] = $bundle_product->get_parent_id() > 0
                    ? $bundle_product->get_parent_id()
                    : $bundle_product->get_id();
            }
        }
    } else {
        $source_ids[] = $product->get_id();
    }

    $seen  = array();
    $posts = array();
    foreach (array_unique(array_filter($source_ids)) as $source_id) {
        $raw = get_field('product_allergens', $source_id);
        if ($raw === null || $raw === '' || $raw === false) {
            continue;
        }

        $acf_posts = function_exists('matrix_rd_acf_posts')
            ? matrix_rd_acf_posts($raw)
            : (is_array($raw) ? $raw : array($raw));

        foreach ($acf_posts as $post) {
            if (! $post instanceof WP_Post || $post->post_title === '' || isset($seen[$post->ID])) {
                continue;
            }
            $seen[$post->ID] = true;
            $posts[]         = $post;
        }
    }

    usort($posts, static function (WP_Post $a, WP_Post $b): int {
        return strcasecmp($a->post_title, $b->post_title);
    });

    return $posts;
}

/**
 * Gallery chrome overlays: box count badge + allergen toggle (archive card style).
 *
 * @param WC_Product|int|null $product
 */
function matrix_rd_render_product_gallery_chrome($product = null): void
{
    if ($product === null) {
        $product = $GLOBALS['product'] ?? null;
    }
    if (is_numeric($product)) {
        $product = wc_get_product($product);
    }
    if (! $product instanceof WC_Product) {
        return;
    }

    $has_box_badge = matrix_rd_get_product_box_count($product) > 0;
    $allergens     = matrix_rd_get_product_allergen_posts($product);
    if (! $has_box_badge && $allergens === array()) {
        return;
    }
    ?>
    <div class="rd-product-gallery-chrome">
        <?php if ($has_box_badge) {
            matrix_rd_render_box_count_badge($product, 'gallery');
        } ?>
        <?php if ($allergens !== array()) {
            matrix_rd_render_gallery_allergen_toggle($allergens);
        } ?>
    </div>
    <?php
}

/**
 * @param WP_Post[] $allergens
 */
function matrix_rd_render_gallery_allergen_toggle(array $allergens): void
{
    if ($allergens === array()) {
        return;
    }
    ?>
    <div class="rd-gallery-allergen" x-data="{ showAllergens: false }">
        <div
            class="rd-gallery-allergen-toggle cursor-pointer"
            role="button"
            tabindex="0"
            @click="showAllergens = !showAllergens"
            @keydown.enter.prevent="showAllergens = !showAllergens"
            @keydown.space.prevent="showAllergens = !showAllergens"
            :aria-expanded="showAllergens"
        >
            <div class="z-50" x-show="!showAllergens">
                <span class="sr-only"><?php esc_html_e('Allergen info', 'matrix-starter'); ?></span>
                <div class="allergen_svg" aria-hidden="true"></div>
            </div>
            <div x-show="showAllergens" x-cloak class="relative top-1.5 right-1.5 z-50 rounded-t-lg">
                <span class="sr-only"><?php esc_html_e('Close allergen info', 'matrix-starter'); ?></span>
                <svg xmlns="http://www.w3.org/2000/svg" width="23" height="22" viewBox="0 0 23 22" fill="none" aria-hidden="true">
                    <rect x="1.5" y="1" width="20" height="20" rx="10" fill="black"></rect>
                    <circle cx="11.5" cy="11" r="11" fill="#FFED56"></circle>
                    <path d="M11.4993 19.3346C16.1017 19.3346 19.8327 15.6037 19.8327 11.0013C19.8327 6.39893 16.1017 2.66797 11.4993 2.66797C6.89698 2.66797 3.16602 6.39893 3.16602 11.0013C3.16602 15.6037 6.89698 19.3346 11.4993 19.3346Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M14.5 14L8.5 8" stroke="#0E1217" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M8.5 14L14.5 8" stroke="#0E1217" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </div>
        </div>
        <div
            x-cloak
            x-show="showAllergens"
            class="rd-gallery-allergen-panel allergen-info p-4 rounded-tl-lg z-40 bg-white text-black w-[220px] -m-[10px]"
        >
            <span class="text-black-full text-sm-font font-reg420"><?php esc_html_e('Allergens', 'rolling-donut'); ?></span>
            <div class="mt-4 w-full">
                <div class="flex flex-row flex-wrap w-full">
                    <?php foreach ($allergens as $allergen) : ?>
                        <?php
                        $allergen_id = (int) $allergen->ID;
                        if ($allergen_id <= 0) {
                            continue;
                        }
                        ?>
                        <div class="flex items-center pb-4 w-1/2 row">
                            <?php if (has_post_thumbnail($allergen_id)) : ?>
                                <img
                                    src="<?php echo esc_url(get_the_post_thumbnail_url($allergen_id, 'thumbnail')); ?>"
                                    alt="<?php echo esc_attr($allergen->post_title); ?>"
                                    class="mr-1 w-6 h-6"
                                />
                            <?php endif; ?>
                            <span class="font-laca text-mob-xs-font font-regular"><?php echo esc_html($allergen->post_title); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}

function matrix_rd_single_product_setup(): void
{
    if (! function_exists('is_product') || ! is_product()) {
        return;
    }

    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10);
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50);
    remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10);

    add_action('woocommerce_single_product_summary', 'matrix_rd_product_summary_header_open', 4);
    add_action('woocommerce_single_product_summary', 'matrix_rd_single_summary_box_badge', 6);
    add_action('woocommerce_single_product_summary', 'matrix_rd_single_visible_price', 7);
    add_action('woocommerce_single_product_summary', 'matrix_rd_woosb_sync_price_markup', 9);
    add_action('woocommerce_single_product_summary', 'matrix_rd_product_summary_header_close', 11);
    add_action('woocommerce_after_add_to_cart_button', 'matrix_rd_single_product_description_after_allergens', 15);
}
add_action('wp', 'matrix_rd_single_product_setup', 20);

/**
 * Intro above the bundled-flavours list on WOOSB product pages.
 */
function matrix_rd_set_box_notice_before_bundle(): void
{
    global $product;

    if (! $product instanceof WC_Product || ! $product->is_type('woosb')) {
        return;
    }

    if (matrix_rd_is_box_builder_product($product)) {
        return;
    }

    echo '<p class="rd-set-box-notice">';
    esc_html_e('This is a set box and so flavours cannot be altered. Box contains the following flavours:', 'matrix-starter');
    echo '</p>';
}
add_action('woosb_before_table', 'matrix_rd_set_box_notice_before_bundle', 10);

/**
 * Body class for unified product-page cart CTAs (Figma 6003:35549).
 *
 * @param array<int, string> $classes
 * @return array<int, string>
 */
function matrix_rd_standard_product_body_class(array $classes): array
{
    if (function_exists('is_product') && is_product()) {
        $classes[] = 'rd-standard-product-cart';
    }

    return $classes;
}
add_filter('body_class', 'matrix_rd_standard_product_body_class');

/**
 * Box count badge in the summary header (right column).
 */
function matrix_rd_single_summary_box_badge(): void
{
    global $product;

    if (! $product instanceof WC_Product) {
        return;
    }

    matrix_rd_render_box_count_badge($product, 'summary');
}

/**
 * Visible price shown in the summary header row (below the title).
 * page (the hero band that used to carry the price has been removed site-wide).
 * In box-builder mode the builder UI shows the live price, so this static summary
 * price is hidden by the box-builder CSS (body.rd-bb-active).
 */
function matrix_rd_single_visible_price(): void
{
    global $product;

    if (! $product instanceof WC_Product) {
        return;
    }

    $price_html = $product->get_price_html();
    if ($price_html === '') {
        return;
    }

    echo '<div class="rd-summary-price-wrap">';
    echo '<p class="price rd-summary-price">'.wp_kses_post($price_html).'</p>';
    echo '</div>';
}

/**
 * Mobile-only title + price shown on a white band above the product gallery
 * slider on every single product page. The canonical title/price still render in
 * the summary below the gallery, so this duplicate is marked aria-hidden. Hooked
 * at priority 15 — after the sale flash (10), before the gallery (20) — so it
 * lands as the first row of the stacked mobile layout. In box-builder mode the
 * builder shows its own mobile head, so this band is hidden by the box-builder
 * CSS (body.rd-bb-active).
 */
function matrix_rd_mobile_title_above_gallery(): void
{
    global $product;

    if (! $product instanceof WC_Product) {
        return;
    }

    $price_html = $product->get_price_html();
    ?>
    <div class="mt-10 rd-mobile-gallery-head">
        <h1 class="rd-mobile-gallery-title"><?php echo esc_html(get_the_title($product->get_id())); ?></h1>
        <?php if ($price_html !== '') { ?>
            <span class="price rd-mobile-gallery-price"><?php echo wp_kses_post($price_html); ?></span>
        <?php } ?>
    </div>
    <?php
}
add_action('woocommerce_before_single_product_summary', 'matrix_rd_mobile_title_above_gallery', 15);

/**
 * Hidden summary price target for WOOSB bundle price updates (hero shows visible price).
 */
function matrix_rd_woosb_sync_price_markup(): void
{
    global $product;

    if (! $product instanceof WC_Product || ! $product->is_type('woosb')) {
        return;
    }

    echo '<p class="price woosb-sync-price" aria-hidden="true" style="position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0;">';
    echo wp_kses_post($product->get_price_html());
    echo '</p>';
}

function matrix_rd_woosb_price_selector(array $vars): array
{
    $vars['price_selector'] = '.summary > .price.woosb-sync-price, .product-price .amount';

    return $vars;
}
add_filter('woosb_vars', 'matrix_rd_woosb_price_selector');

function matrix_rd_single_add_to_cart_button_text(string $text): string
{
    if (! function_exists('is_product') || ! is_product()) {
        return $text;
    }

    global $product;
    if (! $product instanceof WC_Product) {
        return $text;
    }

    return __('Add to Basket', 'rolling-donut');
}
add_filter('woocommerce_product_single_add_to_cart_text', 'matrix_rd_single_add_to_cart_button_text');

function matrix_rd_product_summary_header_open(): void
{
    // Every product now uses the same header: the title and price share a single
    // wrapping row (.rd-title-price-row drives the styling). The hero band has
    // been removed site-wide, so there is no longer a stacked box-builder variant.
    echo '<div class="mt-5 w-full product-summary-header rd-title-price-row">';
}

function matrix_rd_product_summary_header_close(): void
{
    echo '</div>';
}

/**
 * Product description below the allergen accordion (all single product pages).
 */
function matrix_rd_single_product_description_after_allergens(): void
{
    matrix_rd_single_product_description_below_header();
}

/**
 * Product description in a collapsible accordion (legacy Rolling Donut layout).
 */
function matrix_rd_single_product_description_below_header(): void
{
    global $product;

    if (! $product instanceof WC_Product) {
        return;
    }

    $description = $product->get_description();
    if ($description === '') {
        return;
    }

    matrix_rd_render_product_description_accordion(
        apply_filters('the_content', $description),
        $product->is_type('donut_box_builder')
    );
}

/**
 * @param  string  $description_html  Filtered product description HTML.
 * @param  bool  $open_on_desktop  Box-builder pages open the accordion on large screens.
 */
function matrix_rd_render_product_description_accordion(string $description_html, bool $open_on_desktop = false): void
{
    $x_data = $open_on_desktop
        ? '{ open: window.innerWidth > 1084 }'
        : '{ open: false }';
    $x_init = $open_on_desktop
        ? 'window.addEventListener(\'resize\', () => { open = window.innerWidth > 1084 })'
        : '';
    ?>
    <div class="mt-8 mb-8 border border-black border-solid max-lg:mb-0 rounded-sm-8" id="info-open" x-data="<?php echo esc_attr($x_data); ?>"<?php echo $x_init ? ' x-init="'.esc_attr($x_init).'"' : ''; ?>>
        <div>
            <div id="info-open-heading">
                <button
                    type="button"
                    class="flex justify-between items-center px-4 py-3 w-full font-medium text-left"
                    @click="open = !open"
                    :aria-expanded="open"
                    aria-controls="info-open-body">
                    <div class="flex items-center">
                        <span class="flex items-start ml-2 font-medium text-black-full text-sm-font lg:text-reg-font"><?php esc_html_e('Product Description', 'rolling-donut'); ?></span>
                    </div>
                    <div class="flex justify-center items-center w-12 h-12">
                        <template x-if="!open">
                            <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" viewBox="0 0 48 48" class="h-full text-black iconify text-md-font iconify--icon-park-outline">
                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="m24.06 10l-.036 28M10 24h28"></path>
                            </svg>
                        </template>
                        <template x-if="open">
                            <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" viewBox="0 0 48 48" class="h-full text-black iconify text-md-font iconify--icon-park-outline">
                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M10.5 24h28"></path>
                            </svg>
                        </template>
                    </div>
                </button>
            </div>
            <div id="info-open-body" class="overflow-hidden px-4 py-4 border-t border-solid transition-all duration-500 border-grey-disabled product_description reg-font font-laca font-regular" x-show="open" x-cloak style="display: none;" aria-labelledby="info-open-heading">
                <?php echo $description_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- filtered through the_content.?>
            </div>
        </div>
    </div>
    <?php
}

/**
 * WOOSB list layout: qty + name markup matches rd-bb-summary rows.
 *
 * @param string|int $qty
 */
function matrix_rd_woosb_item_qty_markup(string $qty_html, $qty, $product): string
{
    if (! function_exists('is_product') || ! is_product()) {
        return $qty_html;
    }

    return '<span class="rd-bb-summary-qty">'.esc_html((string) $qty).' &times;</span> ';
}
add_filter('woosb_item_qty', 'matrix_rd_woosb_item_qty_markup', 10, 3);

/**
 * @param WC_Product $product
 * @param WC_Product $global_product
 */
function matrix_rd_woosb_item_name_markup(string $item_name, $product, $global_product = null, $order = 0): string
{
    if (! function_exists('is_product') || ! is_product() || $item_name === '') {
        return $item_name;
    }

    return '<span class="rd-bb-summary-label">'.$item_name.'</span>';
}
add_filter('woosb_item_name', 'matrix_rd_woosb_item_name_markup', 10, 4);
