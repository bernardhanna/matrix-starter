<?php
/**
 * Rolling Donut cart UX (notices, redirect, header fragments) — ported from legacy MU plugin.
 */

/**
 * Move default WC notices to footer overlay on catalog/product pages.
 */
function matrix_rd_unregister_default_wc_notices(): void {
    remove_action('woocommerce_before_single_product', 'woocommerce_output_all_notices', 10);
    remove_action('woocommerce_before_shop_loop', 'woocommerce_output_all_notices', 10);
}
add_action('woocommerce_init', 'matrix_rd_unregister_default_wc_notices', 20);

/**
 * Cart subtotal excluding shipping (legacy header total).
 */
function matrix_rd_get_cart_total_excluding_shipping(): float {
    if (! function_exists('WC') || ! WC()->cart) {
        return 0.0;
    }

    $total = 0.0;

    foreach (WC()->cart->get_cart() as $cart_item) {
        if (isset($cart_item['line_total'])) {
            $total += (float) $cart_item['line_total'];
        }
    }

    $total += (float) WC()->cart->get_cart_contents_tax();

    return $total;
}

/**
 * Plain-text cart total for the header badge (e.g. "€36.00").
 */
function matrix_rd_get_cart_total_plain_excluding_shipping(): string {
    if (! function_exists('WC') || ! WC()->cart) {
        return '';
    }

    if (is_null(WC()->cart)) {
        wc_load_cart();
    }

    $amount = matrix_rd_get_cart_total_excluding_shipping();

    return html_entity_decode(wp_strip_all_tags(wc_price($amount)), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect back to product after add-to-cart (prevents double-add on refresh).
 */
function matrix_rd_add_to_cart_redirect(string $url, $product = null): string {
    if (! isset($_REQUEST['add-to-cart'])) {
        return $url;
    }

    $product_id = (int) wp_unslash($_REQUEST['add-to-cart']);
    if ($product_id <= 0) {
        return $url;
    }

    $permalink = get_permalink($product_id);

    return is_string($permalink) && $permalink !== '' ? $permalink : $url;
}
add_filter('woocommerce_add_to_cart_redirect', 'matrix_rd_add_to_cart_redirect', 10, 2);

/**
 * Flag redirect so the client can fetch any stashed notice after reload.
 */
function matrix_rd_add_to_cart_redirect_notice_flag(string $url): string {
    if (! isset($_REQUEST['add-to-cart'])) {
        return $url;
    }

    if (function_exists('matrix_rd_uses_side_cart') && matrix_rd_uses_side_cart()) {
        return add_query_arg('rd_side_cart', '1', $url);
    }

    return add_query_arg('rd_atc', '1', $url);
}
add_filter('woocommerce_add_to_cart_redirect', 'matrix_rd_add_to_cart_redirect_notice_flag', 20);

/**
 * Render a "Buy Now" express-checkout button next to the add-to-cart button.
 *
 * Submits the normal add-to-cart form with an extra `rd_buy_now` flag so the
 * redirect filter below can send the customer straight to checkout, skipping
 * both the cart page and the slide-out cart. Works for simple, variable and
 * bundle (woosb) products since all of them fire this hook inside the form.
 *
 * For box-builder products the rd-box-builder script intercepts this button and
 * adds the configured box over AJAX before navigating to checkout; if that
 * script is absent the native submit below still works as a graceful fallback.
 */
function matrix_rd_render_buy_now_button(): void {
    global $product;

    if (! $product instanceof WC_Product) {
        return;
    }
    if (! $product->is_purchasable() || ! $product->is_in_stock()) {
        return;
    }

    // Legacy box-builder products (e.g. "custom-order") render a bespoke AJAX
    // "Add Box to Cart" flow with no surrounding form.cart. A native submit
    // Buy Now button there can't submit (no form) and would bypass the box
    // contents/custom price anyway, and the form.cart CSS never reaches it so
    // it shows up unstyled. Skip it for that product type.
    if ($product->is_type('donut_box_builder')) {
        return;
    }

    // Carry `add-to-cart` on the button itself (simple products keep that value on
    // the Add-to-Basket button, not a hidden input) and pass the buy-now flag via
    // the form action query so $_REQUEST sees both on submit.
    $buy_now_action = add_query_arg('rd_buy_now', '1', $product->get_permalink());

    printf(
        '<button type="submit" name="add-to-cart" value="%1$d" formaction="%2$s" class="rd-buy-now-button button">%3$s</button>',
        (int) $product->get_id(),
        esc_url($buy_now_action),
        esc_html__('Buy Now', 'matrix-starter')
    );
}
// Priority 9 so Buy Now sits directly under Add to Basket and *above* the
// allergen-info accordion (box-builder-woo hooks that at the default 10).
add_action('woocommerce_after_add_to_cart_button', 'matrix_rd_render_buy_now_button', 9);

/**
 * Send "Buy Now" adds straight to checkout (skips cart + slide-out cart).
 *
 * Runs late (priority 99) so it overrides the product-permalink redirect and the
 * side-cart/notice query flags added above, returning a clean checkout URL.
 */
function matrix_rd_buy_now_redirect(string $url): string {
    if (empty($_REQUEST['rd_buy_now']) || ! isset($_REQUEST['add-to-cart'])) {
        return $url;
    }

    if (! function_exists('wc_get_checkout_url')) {
        return $url;
    }

    // Buy Now sends the customer straight to checkout, so the "… has been added to
    // your cart. View cart" success notice is just noise. This filter only runs
    // after a successful add with no error notices, so dropping just the success
    // notices here is safe (error/info notices are preserved).
    if (function_exists('wc_get_notices') && function_exists('wc_set_notices')) {
        $notices = wc_get_notices();
        unset($notices['success']);
        wc_set_notices($notices);
    }

    $checkout_url = wc_get_checkout_url();

    return is_string($checkout_url) && $checkout_url !== '' ? $checkout_url : $url;
}
add_filter('woocommerce_add_to_cart_redirect', 'matrix_rd_buy_now_redirect', 99);

/**
 * Floating mobile action bar (Add to Basket + Buy Now) for non-box-builder
 * single products. Box-builder pages ship their own `.rd-bb-mobilebar`, so they
 * are excluded here. The bar is a proxy — its buttons forward to the real form
 * buttons via rolling-donut-mobile-actionbar.js — keeping all native cart logic.
 */
/**
 * Resolve the current product for the action bar.
 *
 * On a single product page the global `$product` is only populated once the main
 * loop runs (e.g. at `wp_footer`), but the script enqueue runs earlier on
 * `wp_enqueue_scripts` — before the loop — when the global is still empty. Fall
 * back to the queried object so both the enqueue and the render agree, otherwise
 * the bar renders without its proxy script and its buttons do nothing.
 */
function matrix_rd_actionbar_product(): ?WC_Product {
    global $product;
    if ($product instanceof WC_Product) {
        return $product;
    }

    if (function_exists('is_product') && is_product()) {
        $resolved = wc_get_product(get_queried_object_id());
        if ($resolved instanceof WC_Product) {
            return $resolved;
        }
    }

    return null;
}

function matrix_rd_should_show_mobile_actionbar(): bool {
    if (! function_exists('is_product') || ! is_product()) {
        return false;
    }

    $product = matrix_rd_actionbar_product();
    if (! $product instanceof WC_Product) {
        return false;
    }
    if (function_exists('matrix_rd_is_box_builder_product') && matrix_rd_is_box_builder_product($product)) {
        return false;
    }

    return $product->is_purchasable() && $product->is_in_stock();
}

function matrix_rd_render_mobile_action_bar(): void {
    if (! matrix_rd_should_show_mobile_actionbar()) {
        return;
    }

    $product = matrix_rd_actionbar_product();
    if (! $product instanceof WC_Product) {
        return;
    }
    $add_text = $product->single_add_to_cart_text();
    $show_buy = $product->is_purchasable() && $product->is_in_stock();
    ?>
    <div class="rd-mobile-actionbar" role="group" aria-label="<?php esc_attr_e('Product actions', 'matrix-starter'); ?>">
        <button type="button" class="rd-mobile-actionbar-add"><?php echo esc_html($add_text); ?></button>
        <?php if ($show_buy) : ?>
        <button type="button" class="rd-mobile-actionbar-buy"><?php esc_html_e('Buy Now', 'matrix-starter'); ?></button>
        <?php endif; ?>
    </div>
    <?php
}
add_action('wp_footer', 'matrix_rd_render_mobile_action_bar', 30);

/**
 * Enqueue the proxy script that wires the floating bar to the real form buttons.
 */
function matrix_rd_enqueue_mobile_actionbar_script(): void {
    if (! matrix_rd_should_show_mobile_actionbar()) {
        return;
    }

    $rel  = '/assets/js/rolling-donut-mobile-actionbar.js';
    $path = get_template_directory() . $rel;

    wp_enqueue_script(
        'matrix-rd-mobile-actionbar',
        get_template_directory_uri() . $rel,
        [],
        file_exists($path) ? (string) filemtime($path) : (string) get_option('theme_css_version', '1.0'),
        true
    );
}
add_action('wp_enqueue_scripts', 'matrix_rd_enqueue_mobile_actionbar_script', 30);

/**
 * Enqueue variation validation for variable products (merch). Ensures Add to
 * Basket / Buy Now (inline and the floating bar) show a clear inline error when
 * no variation is selected, instead of a silent reload or browser alert.
 */
function matrix_rd_enqueue_variation_validation_script(): void {
    if (! function_exists('is_product') || ! is_product()) {
        return;
    }

    $product = function_exists('matrix_rd_actionbar_product') ? matrix_rd_actionbar_product() : null;
    if (! $product instanceof WC_Product || ! $product->is_type('variable')) {
        return;
    }

    $rel  = '/assets/js/rolling-donut-variation-validation.js';
    $path = get_template_directory() . $rel;
    if (! file_exists($path)) {
        return;
    }

    wp_enqueue_script(
        'matrix-rd-variation-validation',
        get_template_directory_uri() . $rel,
        [],
        (string) filemtime($path),
        true
    );

    wp_localize_script('matrix-rd-variation-validation', 'matrixRdVariation', [
        'selectPrefix'   => __('Please select', 'rolling-donut'),
        'selectSuffix'   => __('before adding to your basket.', 'rolling-donut'),
        'genericMessage' => __('Please choose your product options before adding to your basket.', 'rolling-donut'),
    ]);
}
add_action('wp_enqueue_scripts', 'matrix_rd_enqueue_variation_validation_script', 31);

/**
 * Suppress noisy "Cart updated" success notices.
 */
function matrix_rd_filter_cart_updated_notice(string $message): string {
    if (strpos($message, 'Cart updated') !== false) {
        return '';
    }

    return $message;
}
add_filter('woocommerce_add_success', 'matrix_rd_filter_cart_updated_notice', 10, 1);

/**
 * Session/customer key used for short-lived notice transients.
 */
function matrix_rd_notice_transient_key(): string {
    if (function_exists('WC') && WC()->session && WC()->session->get_customer_id()) {
        return 'matrix_rd_notice_' . WC()->session->get_customer_id();
    }

    $cookie_name = 'wp_woocommerce_session_' . (defined('COOKIEHASH') ? COOKIEHASH : '');
    if ($cookie_name === 'wp_woocommerce_session_' || empty($_COOKIE[$cookie_name])) {
        return '';
    }

    $parts = explode('|', (string) wp_unslash($_COOKIE[$cookie_name]));

    return ! empty($parts[0]) ? 'matrix_rd_notice_' . $parts[0] : '';
}

/**
 * Persist success notice messages so they survive redirect/AJAX timing issues.
 */
function matrix_rd_stash_success_notice(string $message): string {
    if ($message === '') {
        return $message;
    }

    $transient_key = matrix_rd_notice_transient_key();
    if ($transient_key === '') {
        return $message;
    }

    $pending   = get_transient($transient_key);
    $pending   = is_array($pending) ? $pending : [];
    $pending[] = $message;
    set_transient($transient_key, $pending, 5 * MINUTE_IN_SECONDS);

    return $message;
}
add_filter('woocommerce_add_success', 'matrix_rd_stash_success_notice', 99, 1);

/**
 * Render success notices via the legacy overlay template.
 *
 * @param array<int, array{notice: string, data?: array}> $notices
 */
function matrix_rd_render_success_notices_html(array $notices): string {
    if ($notices === []) {
        return '';
    }

    ob_start();
    wc_get_template('notices/success.php', ['notices' => $notices]);
    return (string) ob_get_clean();
}

/**
 * Collect pending success notices from session and short-lived transients.
 *
 * @return array<int, array{notice: string, data: array}>
 */
function matrix_rd_collect_pending_success_notices(): array {
    $collected = [];

    if (function_exists('WC') && WC()->session) {
        $session_notices = WC()->session->get('wc_notices', []);
        if (! empty($session_notices['success']) && is_array($session_notices['success'])) {
            foreach ($session_notices['success'] as $notice) {
                $text = isset($notice['notice']) ? (string) $notice['notice'] : '';
                if ($text !== '') {
                    $collected[] = [
                        'notice' => $text,
                        'data'   => $notice['data'] ?? [],
                    ];
                }
            }
        }
    }

    if ($collected !== []) {
        $transient_key = matrix_rd_notice_transient_key();
        if ($transient_key !== '') {
            delete_transient($transient_key);
        }

        return $collected;
    }

    $transient_key = matrix_rd_notice_transient_key();
    if ($transient_key !== '') {
        $pending = get_transient($transient_key);
        if (is_array($pending)) {
            foreach ($pending as $message) {
                $message = (string) $message;
                if ($message !== '') {
                    $collected[] = [
                        'notice' => $message,
                        'data'   => [],
                    ];
                }
            }
        }
        delete_transient($transient_key);
    }

    return $collected;
}

/**
 * Display WooCommerce notices as footer overlay (legacy success.php template).
 */
function matrix_rd_output_woocommerce_notices(): void {
    if (! function_exists('WC')) {
        return;
    }

    if (function_exists('is_cart') && is_cart()) {
        return;
    }
    if (function_exists('is_checkout') && is_checkout()) {
        return;
    }

    if (! function_exists('matrix_rd_uses_side_cart') || ! matrix_rd_uses_side_cart()) {
        $success_notices = matrix_rd_collect_pending_success_notices();
        if ($success_notices !== []) {
            echo matrix_rd_render_success_notices_html($success_notices); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
    } else {
        matrix_rd_collect_pending_success_notices();
    }

    if (! WC()->session) {
        return;
    }

    $all_notices  = WC()->session->get('wc_notices', []);
    $notice_types = apply_filters('woocommerce_notice_types', ['error', 'notice']);

    foreach ($notice_types as $notice_type) {
        if (wc_notice_count($notice_type) > 0) {
            wc_get_template("notices/{$notice_type}.php", [
                'notices' => $all_notices[$notice_type] ?? [],
            ]);
        }
    }

    wc_clear_notices();
}
add_action('wp_footer', 'matrix_rd_output_woocommerce_notices', 10);

/**
 * AJAX: return rendered notice HTML for post-add-to-cart flows (donut box builder, etc.).
 */
function matrix_rd_ajax_fetch_woocommerce_notices(): void {
    if (function_exists('matrix_rd_uses_side_cart') && matrix_rd_uses_side_cart()) {
        wp_send_json_success('');
        return;
    }

    ob_start();
    matrix_rd_output_woocommerce_notices();
    wp_send_json_success(ob_get_clean());
}
add_action('wp_ajax_fetch_wc_notices', 'matrix_rd_ajax_fetch_woocommerce_notices');
add_action('wp_ajax_nopriv_fetch_wc_notices', 'matrix_rd_ajax_fetch_woocommerce_notices');

/**
 * Style add-to-cart success messages (legacy Tailwind classes).
 */
function matrix_rd_customize_add_to_cart_message(string $message, array $products): string {
    $product_names = array_map(static function ($product_id) {
        return get_the_title($product_id);
    }, array_keys($products));

    if (empty($product_names)) {
        return $message;
    }

    $product_name = $product_names[0];

    $view_cart_classes  = 'text-yellow-primary text-sm-md-font font-reg42 hover:underline';
    $product_name_class = 'font-laca text-white text-sm-md-font font-light';

    $styled_product_name = sprintf('<span class="%s">%s</span>', esc_attr($product_name_class), esc_html($product_name));
    $message             = str_replace('"' . $product_name . '"', $styled_product_name, $message);
    $message             = str_replace('“' . $product_name . '”', $styled_product_name, $message);

    return str_replace('class="button wc-forward"', 'class="' . esc_attr($view_cart_classes) . '"', $message);
}
add_filter('wc_add_to_cart_message_html', 'matrix_rd_customize_add_to_cart_message', 10, 2);

/**
 * Disable add-to-cart notification for single donut products (legacy behaviour).
 */
function matrix_rd_disable_add_to_cart_notification_for_donuts(string $message, array $products, bool $show_qty): string {
    foreach ($products as $product_id => $qty) {
        if (! function_exists('get_rd_product_type')) {
            continue;
        }
        if (strcasecmp(get_rd_product_type((int) $product_id), 'Donut') === 0) {
            return '';
        }
    }

    return $message;
}
add_filter('wc_add_to_cart_message_html', 'matrix_rd_disable_add_to_cart_notification_for_donuts', 10, 3);

/**
 * Enqueue add-to-cart notice overlay helpers (legacy my_ajax_script.js + app.ts behaviour).
 */
function matrix_rd_enqueue_cart_notice_scripts(): void {
    if (is_admin()) {
        return;
    }
    if (function_exists('is_cart') && is_cart()) {
        return;
    }
    if (function_exists('is_checkout') && is_checkout()) {
        return;
    }

    $version = get_option('theme_css_version', '1.0');
    $path    = get_template_directory() . '/assets/js/rolling-donut-cart-notices.js';

    $deps = ['jquery'];
    if (function_exists('matrix_rd_uses_side_cart') && matrix_rd_uses_side_cart()) {
        $deps[] = 'matrix-rd-side-cart';
    }

    wp_enqueue_script(
        'matrix-rd-cart-notices',
        get_template_directory_uri() . '/assets/js/rolling-donut-cart-notices.js',
        $deps,
        file_exists($path) ? (string) filemtime($path) : $version,
        true
    );

    wp_localize_script('matrix-rd-cart-notices', 'matrixRdCartNotices', [
        'ajaxUrl'      => admin_url('admin-ajax.php'),
        'feedbackMode' => function_exists('matrix_rd_cart_feedback_mode') ? matrix_rd_cart_feedback_mode() : 'popup',
    ]);
}
add_action('wp_enqueue_scripts', 'matrix_rd_enqueue_cart_notice_scripts', 20);
