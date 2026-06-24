<?php
/**
 * Rolling Donut homepage (legacy template-home fields + assets).
 */

/**
 * Load legacy home sections for the static front page.
 */
function matrix_rd_load_home_sections(): void {
    if (! is_front_page()) {
        return;
    }

    $sections = [
        'hero',
        'services',
        'featuredslider',
        'bestsellers',
        'info',
        'our-story',
        'faqs',
        'site-links',
    ];

    foreach ($sections as $section) {
        $part = 'template-parts/home/' . $section;
        $file = get_template_directory() . '/' . $part . '.php';
        if (is_readable($file)) {
            get_template_part($part);
        }
    }
}

/**
 * Enqueue Splide, legacy CSS, and home JS on the front page.
 */
function matrix_rd_home_enqueue_assets(): void {
    if (is_admin() || (! is_front_page() && ! is_page('about-us'))) {
        return;
    }

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
    wp_enqueue_script(
        'splide-autoplay',
        'https://cdn.jsdelivr.net/npm/@splidejs/splide-extension-auto-play@0.5.3/dist/js/splide-extension-auto-play.min.js',
        ['splide'],
        '0.5.3',
        true
    );

    $theme_version = get_option('theme_css_version', '1.0');
    $legacy_css    = get_template_directory() . '/assets/css/rolling-donut-legacy.css';
    if (is_readable($legacy_css)) {
        wp_enqueue_style(
            'matrix-rd-legacy',
            get_template_directory_uri() . '/assets/css/rolling-donut-legacy.css',
            [],
            $theme_version
        );
    }

    wp_enqueue_script(
        'matrix-rd-home',
        get_template_directory_uri() . '/assets/js/rolling-donut-home.js',
        ['splide', 'splide-autoplay', 'jquery', 'alpine'],
        $theme_version,
        true
    );

    wp_enqueue_script(
        'matrix-rd-our-story',
        get_template_directory_uri() . '/assets/js/rolling-donut-our-story.js',
        ['splide'],
        $theme_version,
        true
    );

    if (wp_script_is('slick-js', 'registered')) {
        wp_enqueue_style('slick-css');
        wp_enqueue_script('slick-js');
    }
}
add_action('wp_enqueue_scripts', 'matrix_rd_home_enqueue_assets', 30);

/**
 * Load the legacy utility stylesheet on the 404 page. The Rolling Donut 404
 * layout relies on classes (bg-black-full, bg-yellow-primary, rounded-btn-72,
 * text-sm-md-font) that live only in rolling-donut-legacy.css, which is
 * otherwise enqueued just on the home/about pages.
 */
function matrix_rd_404_enqueue_assets(): void {
    if (is_admin() || ! is_404()) {
        return;
    }

    $legacy_css = get_template_directory() . '/assets/css/rolling-donut-legacy.css';
    if (is_readable($legacy_css)) {
        wp_enqueue_style(
            'matrix-rd-legacy',
            get_template_directory_uri() . '/assets/css/rolling-donut-legacy.css',
            [],
            get_option('theme_css_version', '1.0')
        );
    }
}
add_action('wp_enqueue_scripts', 'matrix_rd_404_enqueue_assets', 30);

/**
 * Re-queue Tailwind bundle after legacy CSS so featured-slider utilities override legacy rules.
 */
function matrix_rd_home_enqueue_tailwind_after_legacy(): void {
    if (is_admin() || (! is_front_page() && ! is_page('about-us'))) {
        return;
    }

    if (! wp_style_is('matrix-starter', 'enqueued') || ! wp_style_is('matrix-rd-legacy', 'enqueued')) {
        return;
    }

    global $wp_styles;
    $handle = 'matrix-starter';
    $style  = $wp_styles->registered[ $handle ] ?? null;
    if (! $style) {
        return;
    }

    wp_dequeue_style($handle);
    wp_enqueue_style(
        $handle,
        $style->src,
        ['matrix-rd-legacy', 'splide'],
        $style->ver
    );
}
add_action('wp_enqueue_scripts', 'matrix_rd_home_enqueue_tailwind_after_legacy', 100);

/**
 * Featured slider arrow visibility (loads after legacy CSS).
 */
function matrix_rd_home_featured_slider_overrides(): void {
    if (is_admin() || (! is_front_page() && ! is_page('about-us'))) {
        return;
    }

    $deps = ['matrix-rd-legacy'];
    if (wp_style_is('matrix-rd-fonts', 'enqueued')) {
        $deps = ['matrix-rd-fonts'];
    } elseif (wp_style_is('matrix-starter', 'enqueued')) {
        $deps = ['matrix-starter'];
    }

    wp_register_style('matrix-rd-featured-slider', false, $deps, '1');
    wp_enqueue_style('matrix-rd-featured-slider');
    wp_add_inline_style(
        'matrix-rd-featured-slider',
        '.featured-donuts .splide__arrow{position:relative!important;top:auto!important;left:auto!important;right:auto!important;transform:none!important;background:transparent!important;opacity:1!important}.featured-donuts .splide__arrow svg{display:block!important}@media (min-width:993px){.featured-donuts .splide__arrows{display:flex!important;z-index:200!important;right:6rem!important;left:auto!important;top:auto!important;bottom:2rem!important;transform:none!important;width:auto!important;flex-direction:column-reverse!important;align-items:flex-end!important;justify-content:flex-end!important;gap:.75rem!important}.featured-donuts .splide__arrow svg{height:61.5px!important;width:34px!important}}@media (max-width:992px){.featured-donuts .splide__arrows{display:none!important}}'
    );
}
add_action('wp_enqueue_scripts', 'matrix_rd_home_featured_slider_overrides', 110);
