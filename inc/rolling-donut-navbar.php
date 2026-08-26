<?php
/**
 * Rolling Donut header / navigation helpers (legacy Sage → matrix-starter).
 */

use Log1x\Navi\Navi;

/**
 * Whether this request is the WooCommerce thank-you (order received) page.
 */
function matrix_rd_nav_is_thankyou(): bool {
    if (function_exists('is_order_received_page') && is_order_received_page()) {
        return true;
    }

    return function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('order-received');
}

/**
 * Whether the main site navigation should render (hide on cart/checkout).
 * Thank-you keeps a logo-only bar, matching legacy.
 */
function matrix_rd_nav_should_show(): bool {
    if (matrix_rd_nav_is_thankyou()) {
        return true;
    }
    if (function_exists('is_cart') && is_cart()) {
        return false;
    }
    if (function_exists('is_checkout') && is_checkout()) {
        return false;
    }
    return true;
}

/**
 * Build primary menu via Navi (legacy location: primary_navigation).
 *
 * @return array<int, object>
 */
function matrix_rd_nav_items(): array {
    if (! class_exists(Navi::class)) {
        return [];
    }

    $navi = Navi::make()->build('primary_navigation');
    if ($navi->isEmpty()) {
        $navi = Navi::make()->build('primary');
    }

    return $navi->isEmpty() ? [] : $navi->toArray();
}

/**
 * @return array{left: array, right: array, all: array}
 */
function matrix_rd_nav_split(array $items, int $left_count = 4): array {
    $left  = array_slice($items, 0, $left_count);
    $right = array_slice($items, $left_count);

    return [
        'left'  => $left,
        'right' => matrix_rd_nav_lead_with_label($right, 'merch'),
        'all'   => $items,
    ];
}

/**
 * Move a labelled item to the front of a nav column (keeps Order now last).
 *
 * @param  array<int, object> $items
 * @return array<int, object>
 */
function matrix_rd_nav_lead_with_label(array $items, string $label): array {
    $want = strtolower($label);
    $aliases = [$want, 'merchandise'];
    $lead = [];
    $cta  = [];
    $rest = [];

    foreach ($items as $item) {
        $current = strtolower(trim((string) ($item->label ?? '')));
        if (function_exists('matrix_rd_nav_is_order_cta') && matrix_rd_nav_is_order_cta($item)) {
            $cta[] = $item;
            continue;
        }
        if (in_array($current, $aliases, true)) {
            $lead[] = $item;
            continue;
        }
        $rest[] = $item;
    }

    return array_merge($lead, $rest, $cta);
}

/**
 * @return array{
 *   main: string,
 *   main_alt: string,
 *   mobile: string,
 *   mobile_alt: string,
 *   mobile_open: string,
 *   mobile_open_alt: string
 * }
 */
/**
 * Attachment ID from an ACF image field (ID, array, or URL).
 */
function matrix_rd_acf_image_id(mixed $value): int {
    if (is_numeric($value)) {
        return (int) $value;
    }
    if (is_array($value)) {
        if (! empty($value['ID'])) {
            return (int) $value['ID'];
        }
        if (! empty($value['id'])) {
            return (int) $value['id'];
        }
    }
    return 0;
}

/**
 * Normalize ACF image field (ID, array, or URL) for templates.
 *
 * @return array{url: string, alt: string, id: int}
 */
function matrix_rd_acf_image(mixed $value, string $alt_fallback = '', string $size = 'full'): array {
    $id  = matrix_rd_acf_image_id($value);
    $url = matrix_rd_acf_image_url($value, $size);
    if ($url === '') {
        return ['url' => '', 'alt' => $alt_fallback, 'id' => $id];
    }

    return [
        'url' => $url,
        'alt' => matrix_rd_attachment_alt($url, $alt_fallback),
        'id'  => $id,
    ];
}

function matrix_rd_acf_image_url(mixed $value, string $size = 'full'): string {
    if (empty($value)) {
        return '';
    }

    $id = matrix_rd_acf_image_id($value);
    if ($id > 0) {
        $url = matrix_rd_attachment_display_url($id, $size);
        if ($url !== '') {
            return $url;
        }
    }

    if (is_array($value) && ! empty($value['url']) && $size === 'full') {
        return (string) $value['url'];
    }

    return is_string($value) ? $value : '';
}

/**
 * URL for an attachment at $size, falling back to a JPEG derivative when the
 * file is still over 500KB (typical for uncompressed PNG photos).
 */
function matrix_rd_attachment_display_url(int $attachment_id, string $size = 'full'): string {
    $url = wp_get_attachment_image_url($attachment_id, $size);
    if (! is_string($url) || $url === '') {
        $url = wp_get_attachment_image_url($attachment_id, 'full');
    }
    if (! is_string($url) || $url === '') {
        return '';
    }

    $path = matrix_rd_local_path_from_url($url);
    if ($path === '' || ! is_readable($path)) {
        return $url;
    }

    if (filesize($path) <= 500 * 1024) {
        return $url;
    }

    $jpeg = matrix_rd_ensure_jpeg_derivative($attachment_id, $path, 1600);
    return $jpeg !== '' ? $jpeg : $url;
}

function matrix_rd_local_path_from_url(string $url): string {
    $uploads = wp_get_upload_dir();
    $baseurl = (string) ($uploads['baseurl'] ?? '');
    $basedir = (string) ($uploads['basedir'] ?? '');
    if ($baseurl === '' || $basedir === '' || ! str_starts_with($url, $baseurl)) {
        return '';
    }

    $path = $basedir . substr($url, strlen($baseurl));
    return is_readable($path) ? $path : '';
}

/**
 * Write a resized JPEG next to an oversized photo and cache the path on the attachment.
 */
function matrix_rd_ensure_jpeg_derivative(int $attachment_id, string $source_path, int $max_width = 1600): string {
    $meta_key = '_rd_jpeg_' . $max_width;
    $cached   = get_post_meta($attachment_id, $meta_key, true);
    if (is_array($cached)
        && ! empty($cached['path'])
        && is_readable((string) $cached['path'])
        && filesize((string) $cached['path']) > 0
        && ! empty($cached['url'])
    ) {
        return (string) $cached['url'];
    }

    if (! function_exists('wp_get_image_editor')) {
        return '';
    }

    $editor = wp_get_image_editor($source_path);
    if (is_wp_error($editor)) {
        return '';
    }

    $dims = $editor->get_size();
    if (! empty($dims['width']) && (int) $dims['width'] > $max_width) {
        $editor->resize($max_width, $max_width, false);
    }
    $editor->set_quality(82);

    $dest = (string) preg_replace('/\.[^.]+$/', '-rdw' . $max_width . '.jpg', $source_path);
    if ($dest === '' || $dest === $source_path) {
        $dest = $source_path . '-rdw' . $max_width . '.jpg';
    }

    $saved = $editor->save($dest, 'image/jpeg');
    if (is_wp_error($saved) || empty($saved['path'])) {
        return '';
    }

    $uploads = wp_get_upload_dir();
    $url     = str_replace((string) $uploads['basedir'], (string) $uploads['baseurl'], (string) $saved['path']);
    update_post_meta($attachment_id, $meta_key, [
        'path' => (string) $saved['path'],
        'url'  => $url,
    ]);

    return $url;
}

function matrix_rd_nav_logos(): array {
    $empty = [
        'main'            => '',
        'main_alt'        => '',
        'mobile'          => '',
        'mobile_alt'      => '',
        'mobile_open'     => '',
        'mobile_open_alt' => '',
    ];

    if (! function_exists('get_field')) {
        return $empty;
    }

    $main        = matrix_rd_acf_image_url(get_field('main_logo', 'option'));
    $mobile      = matrix_rd_acf_image_url(get_field('mobile_logo', 'option'));
    $mobile_open = matrix_rd_acf_image_url(get_field('mobile_logo_open', 'option'));

    return [
        'main'            => $main,
        'main_alt'        => matrix_rd_attachment_alt($main, __('The Rolling Donut logo', 'matrix-starter')),
        'mobile'          => $mobile ?: $main,
        'mobile_alt'      => matrix_rd_attachment_alt($mobile ?: $main, __('Mobile logo', 'matrix-starter')),
        'mobile_open'     => $mobile_open ?: $mobile ?: $main,
        'mobile_open_alt' => matrix_rd_attachment_alt($mobile_open ?: $mobile ?: $main, __('Mobile logo', 'matrix-starter')),
    ];
}

function matrix_rd_attachment_alt(string $url, string $fallback): string {
    if ($url === '') {
        return $fallback;
    }
    $id = (int) attachment_url_to_postid($url);
    if ($id <= 0 && is_numeric($url)) {
        $id = (int) $url;
    }
    if (! $id) {
        return $fallback;
    }
    $alt = get_post_meta($id, '_wp_attachment_image_alt', true);
    return is_string($alt) && $alt !== '' ? $alt : $fallback;
}

function matrix_rd_nav_telephone(): string {
    if (! function_exists('get_field')) {
        return '';
    }
    $tel = get_field('office_telephone', 'option');
    if (is_array($tel) && isset($tel['value'])) {
        $tel = $tel['value'];
    }
    return is_string($tel) ? trim($tel) : '';
}

function matrix_rd_nav_mobile_bg(): string {
    if (! function_exists('get_field')) {
        return '';
    }
    return matrix_rd_acf_image_url(get_field('mobile_menu_bg', 'option'));
}

/**
 * Whether a nav item is the mobile/desktop “Order now” CTA.
 */
function matrix_rd_nav_is_order_cta(object $item): bool {
    $classes = (string) ($item->classes ?? '');
    if (str_contains($classes, 'btn-menu') || str_contains($classes, 'mob-menu-order-btn')) {
        return true;
    }
    $label = isset($item->label) ? (string) $item->label : '';
    return stripos($label, 'order now') !== false;
}

/**
 * Strip layout/CTA classes from WP menus so the mobile Order now pill stays centered.
 */
function matrix_rd_nav_sanitize_order_cta_classes(string $classes): string {
    $classes = preg_replace(
        '/\b(btn-menu|mob-menu-order-btn|btn-top|lgbtn-menu|hoverno-underline|text-white|relative|absolute|fixed|static|sticky)\b/',
        '',
        $classes
    ) ?? '';
    $classes = preg_replace('/\b(left|right|top|bottom)-[\w-]+\b/', '', $classes) ?? '';

    return trim(preg_replace('/\s+/', ' ', $classes) ?? '');
}

/**
 * @return array{count: int, total_plain: string, total_html: string}
 */
function matrix_rd_nav_cart(): array {
    $count = 0;
    $total_plain = '';
    $total_html  = '';

    if (function_exists('WC')) {
        if (is_null(WC()->cart)) {
            wc_load_cart();
        }
        if (WC()->cart) {
            $count       = (int) WC()->cart->get_cart_contents_count();
            $total_plain = matrix_rd_get_cart_total_plain_excluding_shipping();
            $total_html  = WC()->cart->get_cart_total();
        }
    }

    return [
        'count'       => $count,
        'total_plain' => $total_plain,
        'total_html'  => $total_html,
    ];
}

function matrix_rd_nav_item_active(object $item): bool {
    if (! empty($item->active)) {
        return true;
    }
    $request = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '';
    return untrailingslashit(home_url($request)) === untrailingslashit($item->url ?? '');
}

function matrix_rd_nav_tel_href(string $telephone): string {
    return 'tel:' . preg_replace('/[^+\d]/', '', $telephone);
}

/**
 * AJAX: cart count + total for header (ported from legacy MU plugin).
 */
function matrix_rd_ajax_get_cart_info(): void {
    if (! class_exists('WooCommerce')) {
        wp_send_json_error(__('WooCommerce not available.', 'matrix-starter'));
    }

    if (null === WC()->session) {
        WC()->initialize_session();
    }

    if (null === WC()->customer) {
        WC()->initialize_customer();
    }

    if (is_null(WC()->cart)) {
        wc_load_cart();
    }

    if (! WC()->cart) {
        wp_send_json_error(__('Cart not available.', 'matrix-starter'));
    }

    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');

    wp_send_json_success([
        'cart_count' => WC()->cart->get_cart_contents_count(),
        'cart_total' => matrix_rd_get_cart_total_plain_excluding_shipping(),
    ]);
}
add_action('wp_ajax_get_cart_info', 'matrix_rd_ajax_get_cart_info');
add_action('wp_ajax_nopriv_get_cart_info', 'matrix_rd_ajax_get_cart_info');

/**
 * Headroom helper must load before Alpine parses the header.
 */
function matrix_rd_nav_enqueue_headroom(): void {
    if (is_admin() || ! matrix_rd_nav_should_show()) {
        return;
    }

    $theme_version = get_option('theme_css_version', '1.0');
    $headroom_path = get_template_directory() . '/assets/js/rolling-donut-headroom.js';
    $headroom_ver  = is_readable($headroom_path) ? (string) filemtime($headroom_path) : $theme_version;
    wp_enqueue_script(
        'matrix-rd-headroom',
        get_template_directory_uri() . '/assets/js/rolling-donut-headroom.js',
        [],
        $headroom_ver,
        true
    );
}
add_action('wp_enqueue_scripts', 'matrix_rd_nav_enqueue_headroom', 19);

/**
 * Ensure Alpine loads after headroom on pages with the RD nav.
 */
function matrix_rd_nav_alpine_depends_on_headroom(array $deps, string $handle): array {
    if ($handle === 'alpine' && matrix_rd_nav_should_show() && ! is_admin()) {
        $deps[] = 'matrix-rd-headroom';
    }
    return $deps;
}
add_filter('wp_script_dependencies', 'matrix_rd_nav_alpine_depends_on_headroom', 10, 2);

/**
 * Enqueue Iconify + navbar styles/scripts.
 */
function matrix_rd_nav_enqueue_assets(): void {
    if (is_admin() || ! matrix_rd_nav_should_show()) {
        return;
    }

    wp_enqueue_script(
        'iconify',
        'https://cdn.jsdelivr.net/npm/@iconify/iconify@3.1.1/dist/iconify.min.js',
        [],
        '3.1.1',
        true
    );

    $theme_version = get_option('theme_css_version', '1.0');
    $navbar_css_path = get_template_directory() . '/assets/css/rolling-donut-navbar.css';
    $navbar_css_ver  = is_readable($navbar_css_path) ? (string) filemtime($navbar_css_path) : $theme_version;
    wp_enqueue_style(
        'matrix-rd-navbar',
        get_template_directory_uri() . '/assets/css/rolling-donut-navbar.css',
        ['matrix-starter'],
        $navbar_css_ver
    );

    $navbar_deps = ['jquery', 'iconify', 'alpine', 'matrix-rd-headroom'];
    if (function_exists('matrix_rd_uses_side_cart') && matrix_rd_uses_side_cart()) {
        $navbar_deps[] = 'matrix-rd-side-cart';
    }
    if (wp_script_is('matrix-rd-cart-notices', 'registered')) {
        $navbar_deps[] = 'matrix-rd-cart-notices';
    }

    $navbar_js_path = get_template_directory() . '/assets/js/rolling-donut-navbar.js';
    $navbar_js_ver  = is_readable($navbar_js_path) ? (string) filemtime($navbar_js_path) : $theme_version;
    wp_enqueue_script(
        'matrix-rd-navbar',
        get_template_directory_uri() . '/assets/js/rolling-donut-navbar.js',
        $navbar_deps,
        $navbar_js_ver,
        true
    );

    wp_localize_script('matrix-rd-navbar', 'matrixRdNav', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
    ]);

    wp_add_inline_script('alpine', <<<'JS'
document.addEventListener('alpine:init', function () {
  Alpine.data('rdCartHeader', function () {
    return {
      cartCount: 0,
      cartTotal: '',
      init: function () {
        this.cartCount = parseInt(this.$el.dataset.initialCount || '0', 10) || 0;
        if (this.cartCount > 0) {
          this.cartTotal = this.$el.dataset.initialTotal || '';
        }
        this.refreshCart();
      },
      refreshCart: function () {
        var self = this;
        window.fetchCartData().then(function (data) {
          var count = parseInt(data.cart_count, 10) || 0;
          self.cartCount = count;
          self.cartTotal = count > 0 ? (data.cart_total ?? '') : '';
        });
      },
    };
  });

  Alpine.data('rdCartHeaderMobile', function () {
    return {
      cartCount: 0,
      init: function () {
        this.cartCount = parseInt(this.$el.dataset.initialCount || '0', 10) || 0;
        this.refreshCart();
      },
      refreshCart: function () {
        var self = this;
        window.fetchCartData().then(function (data) {
          self.cartCount = parseInt(data.cart_count, 10) || 0;
        });
      },
    };
  });
});
JS
    , 'before');
}
add_action('wp_enqueue_scripts', 'matrix_rd_nav_enqueue_assets', 25);
