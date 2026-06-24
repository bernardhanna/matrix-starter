<?php
/**
 * Rolling Donut side cart slide-out (xootix Side Cart inspired).
 */

/**
 * @return 'popup'|'side_cart'
 */
function matrix_rd_cart_feedback_mode(): string {
    if (! function_exists('get_field')) {
        return 'popup';
    }

    $mode = get_field('rd_cart_feedback_mode', 'option');

    return $mode === 'side_cart' ? 'side_cart' : 'popup';
}

function matrix_rd_uses_side_cart(): bool {
    return matrix_rd_cart_feedback_mode() === 'side_cart';
}

/**
 * Whether a cart line is a child of a donut box or WPC product bundle.
 */
function matrix_rd_side_cart_is_child_line(array $cart_item): bool {
    if (isset($cart_item['part_of_box']) && $cart_item['part_of_box'] === true) {
        return true;
    }

    if (! empty($cart_item['woosb_parent_id'])) {
        return true;
    }

    return false;
}

/**
 * Whether a cart line is a box/bundle parent that should show as a single title in the side cart.
 */
function matrix_rd_side_cart_is_box_parent(array $cart_item): bool {
    $product = $cart_item['data'] ?? null;
    if (! $product instanceof WC_Product) {
        return false;
    }

    if ($product->get_type() === 'donut_box_builder') {
        return isset($cart_item['part_of_box']) && $cart_item['part_of_box'] === false;
    }

    if ($product->is_type('woosb') || ! empty($cart_item['woosb_ids'])) {
        return true;
    }

    return false;
}

/**
 * Hide bundled/box child lines from the side cart.
 */
function matrix_rd_side_cart_item_visible(bool $visible, array $cart_item, string $cart_item_key): bool {
    if (matrix_rd_side_cart_is_child_line($cart_item)) {
        return false;
    }

    return $visible;
}
add_filter('woocommerce_widget_cart_item_visible', 'matrix_rd_side_cart_item_visible', 10, 3);

/**
 * Plain product title for side cart rows (no nested flavour/bundle breakdown).
 */
function matrix_rd_side_cart_item_name(string $product_name, array $cart_item, string $cart_item_key): string {
    if (matrix_rd_side_cart_is_box_parent($cart_item)) {
        return esc_html($product_name);
    }

    return apply_filters('woocommerce_cart_item_name', $product_name, $cart_item, $cart_item_key);
}

/**
 * Render side cart line items + totals (inner panel content).
 */
function matrix_rd_render_side_cart_contents(): string {
    if (! function_exists('WC') || ! WC()->cart) {
        return '';
    }

    if (is_null(WC()->cart)) {
        wc_load_cart();
    }

    ob_start();
    wc_get_template('cart/side-cart-contents.php');
    return (string) ob_get_clean();
}

/**
 * Footer shell for the slide-out panel.
 */
function matrix_rd_render_side_cart_shell(): void {
    if (! matrix_rd_uses_side_cart()) {
        return;
    }
    if (is_admin()) {
        return;
    }
    if (function_exists('is_cart') && is_cart()) {
        return;
    }
    if (function_exists('is_checkout') && is_checkout()) {
        return;
    }
    ?>
    <div
      id="rd-side-cart"
      class="rd-side-cart"
      aria-hidden="true"
      data-rd-side-cart
    >
      <button
        type="button"
        class="rd-side-cart__overlay"
        data-rd-side-cart-close
        aria-label="<?php esc_attr_e('Close cart', 'matrix-starter'); ?>"
        tabindex="-1"
      ></button>
      <aside
        class="rd-side-cart__panel"
        role="dialog"
        aria-modal="true"
        aria-labelledby="rd-side-cart-title"
        tabindex="-1"
      >
        <div class="rd-side-cart__inner" data-rd-side-cart-inner>
          <?php echo matrix_rd_render_side_cart_contents(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>
      </aside>
    </div>
    <?php
}
add_action('wp_footer', 'matrix_rd_render_side_cart_shell', 5);

/**
 * AJAX: return refreshed side cart HTML.
 */
function matrix_rd_ajax_fetch_side_cart(): void {
    if (! class_exists('WooCommerce')) {
        wp_send_json_error(__('WooCommerce not available.', 'matrix-starter'));
    }

    if (null === WC()->session) {
        WC()->initialize_session();
    }

    if (is_null(WC()->cart)) {
        wc_load_cart();
    }

    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');

    wp_send_json_success([
        'html'       => matrix_rd_render_side_cart_contents(),
        'cart_count' => WC()->cart ? WC()->cart->get_cart_contents_count() : 0,
        'cart_total' => matrix_rd_get_cart_total_plain_excluding_shipping(),
    ]);
}
add_action('wp_ajax_fetch_side_cart', 'matrix_rd_ajax_fetch_side_cart');
add_action('wp_ajax_nopriv_fetch_side_cart', 'matrix_rd_ajax_fetch_side_cart');

/**
 * Enqueue side cart assets.
 */
function matrix_rd_enqueue_side_cart_assets(): void {
    if (! matrix_rd_uses_side_cart()) {
        return;
    }
    if (is_admin()) {
        return;
    }
    if (function_exists('is_cart') && is_cart()) {
        return;
    }
    if (function_exists('is_checkout') && is_checkout()) {
        return;
    }

    $theme_version = get_option('theme_css_version', '1.0');
    $css_path      = get_template_directory() . '/assets/css/rolling-donut-side-cart.css';
    $js_path       = get_template_directory() . '/assets/js/rolling-donut-side-cart.js';

    wp_enqueue_style(
        'matrix-rd-side-cart',
        get_template_directory_uri() . '/assets/css/rolling-donut-side-cart.css',
        [],
        file_exists($css_path) ? (string) filemtime($css_path) : $theme_version
    );

    $deps = ['jquery'];
    if (wp_script_is('wc-cart-fragments', 'registered')) {
        $deps[] = 'wc-cart-fragments';
    }

    wp_enqueue_script(
        'matrix-rd-side-cart',
        get_template_directory_uri() . '/assets/js/rolling-donut-side-cart.js',
        $deps,
        file_exists($js_path) ? (string) filemtime($js_path) : $theme_version,
        true
    );

    wp_localize_script('matrix-rd-side-cart', 'matrixRdSideCart', [
        'ajaxUrl'    => admin_url('admin-ajax.php'),
        'cartUrl'    => function_exists('wc_get_cart_url') ? wc_get_cart_url() : '',
        'checkoutUrl' => function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : '',
        'mode'       => 'side_cart',
        'i18n'       => [
            'title'             => __('Your Cart', 'matrix-starter'),
            'close'             => __('Close cart', 'matrix-starter'),
            'continueShopping'  => __('Continue Shopping', 'matrix-starter'),
            'viewCart'          => __('View Cart', 'matrix-starter'),
            'checkout'          => __('Checkout', 'matrix-starter'),
            'empty'             => __('Your cart is empty.', 'matrix-starter'),
            'remove'            => __('Remove item', 'matrix-starter'),
        ],
    ]);
}
add_action('wp_enqueue_scripts', 'matrix_rd_enqueue_side_cart_assets', 19);

/**
 * Body class for side cart mode (styling hooks).
 */
function matrix_rd_side_cart_body_class(array $classes): array {
    if (matrix_rd_uses_side_cart()) {
        $classes[] = 'rd-cart-feedback-side';
    } else {
        $classes[] = 'rd-cart-feedback-popup';
    }

    return $classes;
}
add_filter('body_class', 'matrix_rd_side_cart_body_class');
