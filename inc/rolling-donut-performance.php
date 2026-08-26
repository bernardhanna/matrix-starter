<?php
/**
 * Front-end performance helpers: resource hints + LCP hero preloading.
 *
 * These target the issues surfaced by Lighthouse on the home page:
 *  - long render-blocking cross-origin CSS/font chains (preconnect)
 *  - a slow LCP because the hero background image only loaded after Alpine ran
 *    (preload + a server-rendered inline background in template-parts/home/hero.php).
 */

defined('ABSPATH') || exit;

/**
 * Warm up the cross-origin connections used for fonts, CDN libraries and the
 * Facebook pixel so their TLS handshakes don't sit on the critical path.
 *
 * @param array<int,mixed> $hints    Existing hints for $relation.
 * @param string           $relation Relation type being filtered.
 * @return array<int,mixed>
 */
add_filter('wp_resource_hints', static function (array $hints, string $relation): array {
    if ($relation === 'preconnect') {
        $hints[] = array('href' => 'https://fonts.googleapis.com', 'crossorigin' => 'anonymous');
        $hints[] = array('href' => 'https://fonts.gstatic.com', 'crossorigin' => 'anonymous');
        $hints[] = array('href' => 'https://fonts.cdnfonts.com', 'crossorigin' => 'anonymous');
        $hints[] = array('href' => 'https://cdn.jsdelivr.net', 'crossorigin' => 'anonymous');
        $hints[] = array('href' => 'https://cdnjs.cloudflare.com', 'crossorigin' => 'anonymous');
    }

    if ($relation === 'dns-prefetch') {
        $hints[] = 'https://connect.facebook.net';
        $hints[] = 'https://www.google.com';
    }

    return $hints;
}, 10, 2);

/**
 * Preload the home hero image (the LCP element) so the browser fetches it at
 * high priority instead of waiting for the render-blocking CSS to be parsed.
 * Uses the first hero slide's right-hand photo (desktop + mobile).
 */
add_action('wp_head', static function (): void {
    if (! is_front_page()) {
        return;
    }

    $desktop = '';
    $mobile  = '';
    if (function_exists('matrix_rd_get_home_hero_slides')) {
        $slides = matrix_rd_get_home_hero_slides();
        if (isset($slides[0]) && is_array($slides[0])) {
            $desktop = (string) ($slides[0]['right_image']['url'] ?? '');
            $mobile  = (string) ($slides[0]['right_image_mobile']['url'] ?? '');
        }
    }
    if ($desktop === '' && function_exists('get_field')) {
        $desktop = matrix_rd_perf_image_url(get_field('banner_right'));
        $mobile  = matrix_rd_perf_image_url(get_field('banner_bottom_mobile'));
    }

    if ($desktop !== '') {
        printf(
            '<link rel="preload" as="image" href="%s" media="(min-width: 1084px)" fetchpriority="high">' . "\n",
            esc_url($desktop)
        );
    }

    if ($mobile !== '') {
        printf(
            '<link rel="preload" as="image" href="%s" media="(max-width: 1083px)" fetchpriority="high">' . "\n",
            esc_url($mobile)
        );
    } elseif ($desktop !== '') {
        printf(
            '<link rel="preload" as="image" href="%s" media="(max-width: 1083px)" fetchpriority="high">' . "\n",
            esc_url($desktop)
        );
    }
}, 2);

/**
 * Load non-critical stylesheets asynchronously (media-swap trick) so they
 * don't block first paint. On the homepage everything except the hero/nav
 * stack is deferred; elsewhere only Font Awesome and Slick are.
 */
add_filter('style_loader_tag', static function (string $tag, string $handle): string {
    if (is_admin()) {
        return $tag;
    }
    if (function_exists('is_checkout') && (is_checkout() || is_cart())) {
        return $tag;
    }

    $always_async = array('fontawesome-6', 'font-awesome', 'slick-css');
    $home_critical = array(
        'matrix-rd-font-edmondsans',
        'matrix-rd-font-laca',
        'matrix-starter',
        'matrix-rd-navbar',
        'splide',
        'matrix-rd-legacy',
        'matrix-rd-fonts',
        'matrix-rd-home-hero-slider',
    );

    $async = in_array($handle, $always_async, true);
    if (is_front_page() && ! in_array($handle, $home_critical, true)) {
        $async = true;
    }
    if (! $async) {
        return $tag;
    }

    $tag = preg_replace(
        '/(rel=([\'"])stylesheet\2)/',
        "$1 media=\"print\" onload=\"this.media='all'\"",
        $tag,
        1
    );

    return $tag;
}, 20, 2);

/**
 * Resolve an ACF image field (ID, numeric-string ID, URL, or array) to a URL.
 * Prefers the theme's own resolver so it matches what the hero template renders.
 */
function matrix_rd_perf_image_url($image): string {
    if (function_exists('matrix_rd_acf_image')) {
        $resolved = matrix_rd_acf_image($image, '', '1536x1536');
        if (is_array($resolved) && ! empty($resolved['url'])) {
            return (string) $resolved['url'];
        }
    }

    if (is_array($image)) {
        if (! empty($image['url'])) {
            return (string) $image['url'];
        }
        if (! empty($image['ID'])) {
            $url = wp_get_attachment_image_url((int) $image['ID'], '1536x1536');
            return $url ?: '';
        }
    }

    if (is_numeric($image)) {
        $url = wp_get_attachment_image_url((int) $image, '1536x1536');
        return $url ?: '';
    }

    if (is_string($image) && filter_var($image, FILTER_VALIDATE_URL)) {
        return $image;
    }

    return '';
}

/**
 * Homepage does not need product-builder, share-cart, captcha, or Google Fonts
 * stacks. Dequeue them late so plugin enqueue callbacks have already run.
 */
function matrix_rd_front_page_trim_assets(): void {
    if (is_admin() || ! is_front_page()) {
        return;
    }

    $script_handles = array(
        'woo-variation-swatches',
        'woosb-frontend',
        'woosb-blocks',
        'cxecrt-tip-tip',
        'cxecrt-frontend-js',
        'stat-counters',
        'theme-forms',
        'turnstile',
        'recaptcha',
        'alpine-intersect',
        'jquery-migrate',
    );
    foreach ($script_handles as $handle) {
        wp_dequeue_script($handle);
    }

    $style_handles = array(
        'woo-variation-swatches',
        'woosb-frontend',
        'woosb-blocks',
        'cxecrt-tip-tip',
        'cxecrt-icon-font',
        'cxecrt-css',
        'wc-blocks-style',
        'wc-blocks',
        'matrix-google-fonts',
        'iconic-wds-style',
        'jckwds-style',
    );
    foreach ($style_handles as $handle) {
        wp_dequeue_style($handle);
    }

    $scripts = wp_scripts();
    if (isset($scripts->registered['jquery'])) {
        $scripts->registered['jquery']->deps = array_values(array_diff(
            (array) $scripts->registered['jquery']->deps,
            array('jquery-migrate')
        ));
    }
    if (isset($scripts->registered['alpine'])) {
        $scripts->registered['alpine']->deps = array_values(array_diff(
            (array) $scripts->registered['alpine']->deps,
            array('alpine-intersect')
        ));
    }

    $style_needles = array(
        'woo-variation-swatches',
        'woo-product-bundle-premium',
        'woocommerce-email-cart',
        'iconic-woo-delivery-slots',
        '/woocommerce/assets/client/blocks/wc-blocks.css',
        'fonts.googleapis.com',
    );
    $styles = wp_styles();
    if ($styles && ! empty($styles->queue)) {
        foreach ($styles->queue as $handle) {
            $src = (string) ($styles->registered[ $handle ]->src ?? '');
            foreach ($style_needles as $needle) {
                if ($src !== '' && str_contains($src, $needle)) {
                    wp_dequeue_style($handle);
                    break;
                }
            }
        }
    }

    $script_needles = array(
        'woo-variation-swatches',
        'woo-product-bundle-premium/assets/js/frontend',
        'woocommerce-email-cart',
        'stat-counters.js',
        'challenges.cloudflare.com/turnstile',
        'jquery-migrate',
        '@alpinejs/intersect',
    );
    if ($scripts && ! empty($scripts->queue)) {
        foreach ($scripts->queue as $handle) {
            $src = (string) ($scripts->registered[ $handle ]->src ?? '');
            foreach ($script_needles as $needle) {
                if ($src !== '' && str_contains($src, $needle)) {
                    wp_dequeue_script($handle);
                    break;
                }
            }
        }
    }

    // The plugin still dumps its modal HTML in wp_footer even after the CSS/JS
    // that hide it are dequeued. Drop that markup on the homepage too.
    matrix_rd_remove_share_cart_footer_markup();
}
add_action('wp_enqueue_scripts', 'matrix_rd_front_page_trim_assets', 999);
add_action('wp_print_scripts', 'matrix_rd_front_page_trim_assets', 1);
add_action('wp_print_styles', 'matrix_rd_front_page_trim_assets', 1);

/**
 * Stop WooCommerce Save & Share Cart from printing its modal in wp_footer.
 */
function matrix_rd_remove_share_cart_footer_markup(): void {
    global $cxecrt;

    if (! is_object($cxecrt) || ! method_exists($cxecrt, 'cart_page_load_form')) {
        return;
    }

    remove_action('wp_footer', array($cxecrt, 'cart_page_load_form'));
}

/**
 * Dashboard RSS widgets store multi-megabyte site transients in wp_options.
 */
function matrix_rd_disable_dashboard_rss_widgets(): void {
    remove_meta_box('dashboard_primary', 'dashboard', 'side');
    remove_meta_box('dashboard_primary', 'dashboard', 'normal');
    remove_meta_box('dashboard_secondary', 'dashboard', 'side');
    remove_meta_box('dashboard_secondary', 'dashboard', 'normal');
}
add_action('wp_dashboard_setup', 'matrix_rd_disable_dashboard_rss_widgets', 99);

/**
 * Drop expired / leftover options bloat that inflates the performance sample.
 */
function matrix_rd_trim_options_bloat(): void {
    if (function_exists('delete_expired_transients')) {
        delete_expired_transients(true);
    }

    delete_option('_wpallimport_session_new_');
    delete_option('ptk_patterns');
    delete_site_transient('t15s-registry-gforms');
    delete_transient('woocommerce_admin_remote_inbox_notifications_specs');

    global $wpdb;
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $feed_names = $wpdb->get_col(
        "SELECT option_name FROM {$wpdb->options}
         WHERE option_name LIKE '_site_transient_feed_%'
            OR option_name LIKE '_site_transient_timeout_feed_%'
            OR option_name LIKE '_transient_feed_%'
            OR option_name LIKE '_transient_timeout_feed_%'"
    );
    foreach ((array) $feed_names as $name) {
        delete_option((string) $name);
    }
}
add_action('wp_scheduled_delete', 'matrix_rd_trim_options_bloat');

