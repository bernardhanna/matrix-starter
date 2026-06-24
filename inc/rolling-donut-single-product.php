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
    // Non-box-builder pages drop the hero band, so the price moves up beside the
    // title (priority 6 = right after the title at 5, inside the header wrapper).
    add_action('woocommerce_single_product_summary', 'matrix_rd_single_visible_price', 6);
    add_action('woocommerce_single_product_summary', 'matrix_rd_woosb_sync_price_markup', 9);
    add_action('woocommerce_single_product_summary', 'matrix_rd_product_summary_header_close', 11);
    add_action('woocommerce_single_product_summary', 'matrix_rd_single_product_description_below_header', 12);
}
add_action('wp', 'matrix_rd_single_product_setup', 20);

/**
 * Visible price shown beside the title in the summary on every single product
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

    echo '<p class="price rd-summary-price">'.wp_kses_post($price_html).'</p>';
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
    <div class="mt-10 rd-mobile-gallery-head" aria-hidden="true">
        <span class="rd-mobile-gallery-title"><?php echo esc_html(get_the_title($product->get_id())); ?></span>
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
