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
 * Desktop and mobile art are served from separate ACF fields, so each gets a
 * media-scoped preload.
 */
add_action('wp_head', static function (): void {
    if (! is_front_page() || ! function_exists('get_field')) {
        return;
    }

    $desktop = matrix_rd_perf_image_url(get_field('banner_left'));
    $mobile  = matrix_rd_perf_image_url(get_field('banner_top_mobile'));

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
        // Fall back to the desktop art on mobile if no dedicated mobile image.
        printf(
            '<link rel="preload" as="image" href="%s" media="(max-width: 1083px)" fetchpriority="high">' . "\n",
            esc_url($desktop)
        );
    }
}, 2);

/**
 * Load a small set of clearly non-critical, render-blocking stylesheets
 * asynchronously (media-swap trick) so they don't block first paint. These only
 * style below-the-fold UI: Font Awesome (footer/social icons) and the Slick
 * carousel (bestsellers strip below the hero).
 */
add_filter('style_loader_tag', static function (string $tag, string $handle): string {
    if (is_admin()) {
        return $tag;
    }
    // Never defer styles on the checkout/cart flow.
    if (function_exists('is_checkout') && (is_checkout() || is_cart())) {
        return $tag;
    }

    $async_handles = array('fontawesome-6', 'font-awesome', 'slick-css');
    if (! in_array($handle, $async_handles, true)) {
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
        $resolved = matrix_rd_acf_image($image);
        if (is_array($resolved) && ! empty($resolved['url'])) {
            return (string) $resolved['url'];
        }
    }

    if (is_array($image)) {
        if (! empty($image['url'])) {
            return (string) $image['url'];
        }
        if (! empty($image['ID'])) {
            $url = wp_get_attachment_image_url((int) $image['ID'], 'full');
            return $url ?: '';
        }
    }

    if (is_numeric($image)) {
        $url = wp_get_attachment_image_url((int) $image, 'full');
        return $url ?: '';
    }

    if (is_string($image) && filter_var($image, FILTER_VALIDATE_URL)) {
        return $image;
    }

    return '';
}
