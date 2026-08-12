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
 * Featured slider styles (layout + hero-matching controls).
 */
function matrix_rd_home_featured_slider_overrides(): void {
    if (is_admin() || (! is_front_page() && ! is_page('about-us'))) {
        return;
    }

    $deps = ['matrix-rd-legacy'];
    if (wp_style_is('matrix-rd-home-hero-slider', 'enqueued')) {
        $deps[] = 'matrix-rd-home-hero-slider';
    } elseif (wp_style_is('matrix-starter', 'enqueued')) {
        $deps = ['matrix-starter'];
    }

    $css = get_template_directory() . '/assets/css/rolling-donut-featured.css';
    if (! is_readable($css)) {
        return;
    }

    wp_enqueue_style(
        'matrix-rd-featured-slider',
        get_template_directory_uri() . '/assets/css/rolling-donut-featured.css',
        $deps,
        (string) filemtime($css)
    );
}
add_action('wp_enqueue_scripts', 'matrix_rd_home_featured_slider_overrides', 110);

/**
 * Normalize one featured slide row.
 *
 * @param array<string, mixed> $row
 * @return array<string, mixed>|null
 */
function matrix_rd_normalize_featured_slide(array $row): ?array {
    $pick = static function (array $row, array $keys) {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row) && $row[$key] !== null && $row[$key] !== '') {
                return $row[$key];
            }
        }
        return null;
    };

    $image        = matrix_rd_acf_image($pick($row, ['slide_image', 'image']) ?? null);
    $image_mobile = matrix_rd_acf_image($pick($row, ['slide_image_mobile', 'image_mobile']) ?? null);
    $heading      = trim((string) ($pick($row, ['slide_heading', 'heading']) ?? ''));
    $text         = trim((string) ($pick($row, ['slide_text', 'text', 'description']) ?? ''));
    $bg_color     = trim((string) ($pick($row, ['slide_bg_color', 'bg_color']) ?? ''));
    $text_color   = (string) ($pick($row, ['slide_text_color', 'text_color']) ?? 'black');
    $button       = $pick($row, ['slide_button', 'button']) ?? [];

    if (! is_array($button)) {
        $button = [];
    }

    $button_url   = trim((string) ($button['url'] ?? ''));
    $button_title = trim((string) ($button['title'] ?? ''));

    if ($bg_color === '') {
        $bg_color = '#ffed56';
    }
    if (! in_array($text_color, ['black', 'white'], true)) {
        $text_color = 'black';
    }

    $has_content = $image['url'] !== ''
        || $image_mobile['url'] !== ''
        || $heading !== ''
        || $text !== '';

    if (! $has_content) {
        return null;
    }

    if ($button_url === '') {
        $button_url   = home_url('/donut-box/');
        $button_title = $button_title !== '' ? $button_title : __('Order Now', 'matrix-starter');
    }

    return [
        'image'        => $image,
        'image_mobile' => $image_mobile,
        'heading'      => $heading,
        'text'         => $text,
        'bg_color'     => $bg_color,
        'text_color'   => $text_color,
        'button'       => [
            'url'    => $button_url,
            'title'  => $button_title !== '' ? $button_title : __('Order Now', 'matrix-starter'),
            'target' => (string) ($button['target'] ?? ''),
        ],
    ];
}

/**
 * Build featured slides from the legacy product relationship.
 *
 * @return array<int, array<string, mixed>>
 */
function matrix_rd_featured_slides_from_products(?int $post_id = null): array {
    $context  = $post_id ?? (int) get_option('page_on_front');
    $featured = $context > 0 ? get_field('donuts', $context) : get_field('donuts');
    if (empty($featured) || ! is_array($featured)) {
        return [];
    }

    $slides = [];
    foreach ($featured as $donut) {
        $post_id = is_object($donut) ? (int) $donut->ID : (int) $donut;
        if ($post_id <= 0) {
            continue;
        }

        $post = get_post($post_id);
        if (! $post instanceof WP_Post) {
            continue;
        }

        $image_id = (int) get_post_thumbnail_id($post_id);
        $bg       = (string) (get_field('featured_donut_bg_color', $post_id) ?: '#ffed56');
        $raw      = (string) $post->post_content;
        $text     = trim(preg_replace('/\s+/', ' ', wp_strip_all_tags($raw)) ?? '');

        $slide = matrix_rd_normalize_featured_slide([
            'slide_image'      => $image_id > 0 ? $image_id : '',
            'slide_heading'    => $post->post_title,
            'slide_text'       => $text,
            'slide_bg_color'   => $bg,
            'slide_text_color' => 'black',
            'slide_button'     => [
                'title'  => __('Order Now', 'matrix-starter'),
                'url'    => home_url('/donut-box/'),
                'target' => '',
            ],
            '_image_id'        => $image_id,
        ]);

        if ($slide !== null) {
            $slide['_image_id'] = $image_id;
            $slides[]           = $slide;
        }
    }

    return $slides;
}

/**
 * Featured slides for the homepage carousel.
 *
 * @return array<int, array<string, mixed>>
 */
function matrix_rd_get_featured_slides(): array {
    $post_id = (int) get_option('page_on_front');
    $context = $post_id > 0 ? $post_id : false;

    $rows = function_exists('get_field') ? get_field('featured_slides', $context) : null;
    if (is_array($rows) && $rows !== []) {
        $slides = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $slide = matrix_rd_normalize_featured_slide($row);
            if ($slide !== null) {
                $slides[] = $slide;
            }
        }
        if ($slides !== []) {
            return $slides;
        }
    }

    return matrix_rd_featured_slides_from_products($post_id > 0 ? $post_id : null);
}

/**
 * Seed featured_slides from legacy product picks when the repeater is empty.
 */
function matrix_rd_seed_featured_slides(int $post_id, bool $force = false): bool {
    if (! function_exists('get_field') || ! function_exists('update_field')) {
        return false;
    }

    $front_page_id = (int) get_option('page_on_front');
    if ($front_page_id <= 0 || $post_id !== $front_page_id) {
        return false;
    }

    $existing = get_field('featured_slides', $post_id);
    if (! $force && is_array($existing) && $existing !== []) {
        foreach ($existing as $row) {
            if (is_array($row) && matrix_rd_normalize_featured_slide($row) !== null) {
                return false;
            }
        }
    }

    $from_products = matrix_rd_featured_slides_from_products($post_id);
    if ($from_products === []) {
        return false;
    }

    $rows = [];
    foreach ($from_products as $slide) {
        $image_id = (int) ($slide['_image_id'] ?? 0);
        if ($image_id <= 0 && ! empty($slide['image']['url'])) {
            $image_id = (int) attachment_url_to_postid($slide['image']['url']);
        }

        $rows[] = [
            'slide_image'      => $image_id > 0 ? $image_id : '',
            'slide_heading'    => $slide['heading'],
            'slide_text'       => $slide['text'],
            'slide_bg_color'   => $slide['bg_color'],
            'slide_text_color' => $slide['text_color'],
            'slide_button'     => $slide['button'],
        ];
    }

    return (bool) update_field('featured_slides', $rows, $post_id);
}

/**
 * Theme asset base for bundled home hero slide images.
 */
function matrix_rd_home_hero_asset_url(string $filename): string {
    return get_template_directory_uri() . '/assets/images/home-hero/' . ltrim($filename, '/');
}

/**
 * Import a bundled theme hero image into the media library (once per file).
 */
function matrix_rd_theme_hero_attachment_id(string $filename): int {
    static $cache = [];

    $filename = ltrim($filename, '/');
    if (isset($cache[$filename])) {
        return $cache[$filename];
    }

    $source = get_template_directory() . '/assets/images/home-hero/' . $filename;
    if (! is_readable($source)) {
        $cache[$filename] = 0;
        return 0;
    }

    $meta_key = '_matrix_rd_theme_hero_source';
    $hash     = (string) md5_file($source);

    $existing = get_posts([
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_query'     => [
            [
                'key'   => $meta_key,
                'value' => $filename,
            ],
        ],
    ]);

    if ($existing !== []) {
        $id = (int) $existing[0];
        if (get_post_meta($id, '_matrix_rd_theme_hero_hash', true) === $hash) {
            $cache[$filename] = $id;
            return $id;
        }
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $tmp = wp_tempnam($filename);
    if (! $tmp || ! copy($source, $tmp)) {
        if ($tmp) {
            @unlink($tmp);
        }
        $cache[$filename] = 0;
        return 0;
    }

    $file_array = [
        'name'     => basename($filename),
        'tmp_name' => $tmp,
    ];
    $attach_id = media_handle_sideload(
        $file_array,
        0,
        'Hero: ' . pathinfo($filename, PATHINFO_FILENAME)
    );
    @unlink($tmp);

    if (is_wp_error($attach_id)) {
        $cache[$filename] = 0;
        return 0;
    }

    update_post_meta($attach_id, $meta_key, $filename);
    update_post_meta($attach_id, '_matrix_rd_theme_hero_hash', $hash);

    $cache[$filename] = (int) $attach_id;
    return $cache[$filename];
}

/**
 * Homepage hero layout: default (full-bleed card) or layout_2 (split panels).
 */
function matrix_rd_get_home_hero_layout(): string {
    $layout = function_exists('get_field') ? get_field('hero_layout') : null;
    if (! is_string($layout) || $layout === '') {
        $layout = (string) get_post_meta(get_queried_object_id(), 'hero_layout', true);
    }

    return in_array($layout, ['default', 'layout_2'], true) ? $layout : 'default';
}

/**
 * Whether a hero repeater row has enough content to render.
 *
 * @param array<string, mixed> $row
 */
function matrix_rd_hero_slides_row_is_populated(array $row): bool {
    return matrix_rd_normalize_home_hero_slide($row) !== null;
}

/**
 * True when the repeater is empty or every row is blank.
 *
 * @param mixed $rows
 */
function matrix_rd_hero_slides_need_seeding(mixed $rows): bool {
    if (! is_array($rows) || $rows === []) {
        return true;
    }

    foreach ($rows as $row) {
        if (is_array($row) && matrix_rd_hero_slides_row_is_populated($row)) {
            return false;
        }
    }

    return true;
}

/**
 * Permalink for homepage hero slide 1 CTA (personalised midi sourdough box).
 */
function matrix_rd_home_hero_personalised_midi_url(): string {
    $product = get_page_by_path('personalised-midi-sourdough-donuts-box-of-20', OBJECT, 'product');
    if ($product instanceof WP_Post) {
        return (string) get_permalink($product);
    }

    return home_url('/product/personalised-midi-sourdough-donuts-box-of-20/');
}

/**
 * Default hero slides as ACF repeater rows (attachment IDs for image fields).
 *
 * @return array<int, array<string, mixed>>
 */
function matrix_rd_get_default_home_hero_slides_acf_rows(): array {
    $img               = static fn (string $file): int => matrix_rd_theme_hero_attachment_id($file);
    $shop              = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/');
    $personalised_midi = matrix_rd_home_hero_personalised_midi_url();

    return [
        [
            'slide_left_pattern'        => $img('slide-1-left-pattern.png'),
            'slide_left_image'          => $img('slide-1-neon.png'),
            'slide_left_image_mobile'   => $img('slide-1-neon.png'),
            'slide_heading'             => __('The Original Donuts In Dublin', 'matrix-starter'),
            'slide_subtext'             => __("Made in Ireland. Made Fresh Daily. The Original Donuts In Dublin Since\n1978.", 'matrix-starter'),
            'slide_right_image'         => $img('slide-1-right.png'),
            'slide_left_overlay'        => 'black',
            'slide_text_color'          => 'white',
            'slide_button_style'        => 'white',
            'slide_button_icon'         => 1,
            'slide_hero_link'           => [
                'title'  => __('Order fresh box now', 'matrix-starter'),
                'url'    => $personalised_midi,
                'target' => '',
            ],
        ],
        [
            'slide_left_pattern'   => $img('slide-2-left-pattern.png'),
            'slide_right_image'    => $img('slide-2-right-layer-3.png'),
            'slide_heading'        => __('Curate Your Box of 6 or 12', 'matrix-starter'),
            'slide_subtext'        => __('Mix and match your favorite sourdough glazes to create the ultimate custom box for friends, office, or events.', 'matrix-starter'),
            'slide_left_overlay'   => 'dark_brown',
            'slide_text_color'     => 'white',
            'slide_button_style'   => 'white',
            'slide_button_icon'    => 1,
            'slide_hero_link'      => [
                'title'  => __('Build your custom box', 'matrix-starter'),
                'url'    => home_url('/donut-box/'),
                'target' => '',
            ],
        ],
        [
            'slide_left_pattern' => $img('slide-3-left-pattern.png'),
            'slide_right_image'  => $img('slide-3-right-layer-4.png'),
            'slide_heading'      => __('Treat Yourself: 15% Off Your First Box!', 'matrix-starter'),
            'slide_subtext'      => __('Discover our signature handcrafted sourdough and vegan donuts Rolling Donut. Made fresh daily. Order online and get them delivered straight to your home or office.', 'matrix-starter'),
            'slide_left_overlay' => 'yellow',
            'slide_text_color'   => 'black',
            'slide_button_style' => 'black',
            'slide_button_icon'  => 1,
            'slide_hero_link'    => [
                'title'  => __('Buy Now', 'matrix-starter'),
                'url'    => $shop,
                'target' => '',
            ],
        ],
    ];
}

/**
 * Seed the front-page hero repeater with the three built-in default slides.
 */
function matrix_rd_seed_home_hero_slides(int $post_id, bool $force = false): bool {
    if (! function_exists('get_field') || ! function_exists('update_field')) {
        return false;
    }

    $front_page_id = (int) get_option('page_on_front');
    if ($front_page_id <= 0 || $post_id !== $front_page_id) {
        return false;
    }

    $existing = get_field('hero_slides', $post_id);
    if (! $force && ! matrix_rd_hero_slides_need_seeding($existing)) {
        return false;
    }

    $rows = matrix_rd_get_default_home_hero_slides_acf_rows();
    update_field('hero_slides', $rows, $post_id);
    update_option('matrix_rd_hero_slides_seeded', (string) $post_id);

    return true;
}

/**
 * Map ACF hero slide row keys (new fields + legacy names).
 *
 * @param array<string, mixed> $row
 * @return array<string, mixed>
 */
function matrix_rd_map_hero_slide_acf_row(array $row): array {
    $pick = static function (array $source, array $keys) {
        foreach ($keys as $key) {
            if (array_key_exists($key, $source) && $source[$key] !== null && $source[$key] !== '') {
                return $source[$key];
            }
        }

        return null;
    };

    $subtext = trim((string) ($pick($row, ['slide_subtext', 'slide_hero_text', 'hero_text']) ?? ''));
    if ($subtext === '') {
        $lines = [];
        foreach (['slide_hero_text_line_1', 'slide_hero_text_line_2'] as $line_key) {
            $line = trim((string) ($row[$line_key] ?? ''));
            if ($line !== '') {
                $lines[] = $line;
            }
        }
        if ($lines !== []) {
            $subtext = implode("\n", $lines);
        }
    }

    $legacy_layout = (string) ($pick($row, ['slide_layout']) ?? '');
    $left_overlay  = (string) ($pick($row, ['slide_left_overlay']) ?? '');
    if ($left_overlay === '') {
        $left_overlay = match ($legacy_layout) {
            'promo_dark'   => 'dark_brown',
            'promo_yellow' => 'yellow',
            default        => 'black',
        };
    }

    $text_color = (string) ($pick($row, ['slide_text_color']) ?? '');
    if ($text_color === '') {
        $text_color = $legacy_layout === 'promo_yellow' ? 'black' : 'white';
    }

    $button_style = (string) ($pick($row, ['slide_button_style']) ?? '');
    if ($button_style === '') {
        $button_style = $legacy_layout === 'promo_yellow' ? 'black' : 'white';
    }

    $button_hover_style = (string) ($pick($row, ['slide_button_hover_style']) ?? 'default');

    $button_icon = $row['slide_button_icon'] ?? null;
    if ($button_icon === null || $button_icon === '') {
        $button_icon = true;
    }

    $truthy = static function ($value): bool {
        return $value === true || $value === 1 || $value === '1';
    };

    return [
        'left_pattern'            => $pick($row, ['slide_left_pattern', 'slide_banner_left', 'banner_left']),
        'left_pattern_mobile'     => $pick($row, ['slide_left_pattern_mobile', 'slide_banner_top_mobile', 'banner_top_mobile']),
        'left_image'              => $pick($row, ['slide_left_image', 'slide_neon', 'neon']),
        'left_image_mobile'       => $pick($row, ['slide_left_image_mobile', 'slide_neon_mobile', 'neon_mobile']),
        'heading'                 => (string) ($pick($row, ['slide_heading', 'heading']) ?? ''),
        'heading_mobile'          => (string) ($pick($row, ['slide_heading_mobile']) ?? ''),
        'subtext'                 => $subtext,
        'subtext_mobile'          => trim((string) ($pick($row, ['slide_subtext_mobile']) ?? '')),
        'hero_link'               => $pick($row, ['slide_hero_link', 'hero_link']) ?? [],
        'button_note'             => trim((string) ($pick($row, ['slide_button_note', 'button_note']) ?? '')),
        'right_image'             => $pick($row, ['slide_right_image', 'slide_banner_right', 'banner_right']),
        'right_image_mobile'      => $pick($row, ['slide_right_image_mobile', 'slide_banner_bottom_mobile', 'banner_bottom_mobile']),
        'left_overlay'            => $left_overlay,
        'left_overlay_custom'     => (string) ($pick($row, ['slide_left_overlay_custom']) ?? ''),
        'text_color'              => $text_color,
        'button_style'            => $button_style,
        'button_hover_style'      => $button_hover_style,
        'button_icon'             => (bool) $button_icon,
        'compact_overlay'         => $truthy($row['slide_compact_overlay'] ?? $row['compact_overlay'] ?? false),
        'full_width_content'      => $truthy($row['slide_full_width_content'] ?? $row['full_width_content'] ?? false),
        'body_highlight'          => $truthy($row['slide_body_highlight'] ?? $row['body_highlight'] ?? false),
        'body_emphasis'           => $truthy($row['slide_body_emphasis'] ?? $row['body_emphasis'] ?? false),
        'title_size'              => (string) ($pick($row, ['slide_title_size', 'title_size']) ?? 'default'),
        'mobile_text_size'        => (string) ($pick($row, ['slide_mobile_text_size', 'mobile_text_size']) ?? 'default'),
    ];
}

/**
 * Inline overlay background style for one hero slide.
 *
 * @param array<string, mixed> $slide
 */
function matrix_rd_home_hero_overlay_style(array $slide): string {
    $preset = (string) ($slide['left_overlay'] ?? 'black');
    $custom = trim((string) ($slide['left_overlay_custom'] ?? ''));

    $background = match ($preset) {
        'transparent' => 'transparent',
        'dark_brown'  => 'rgba(51, 41, 35, 0.71)',
        'yellow'      => 'rgba(241, 218, 26, 0.9)',
        'custom'      => $custom !== '' ? $custom : 'rgba(0, 0, 0, 0.71)',
        default       => 'rgba(0, 0, 0, 0.71)',
    };

    return 'background:' . esc_attr($background) . ';';
}

/**
 * Donut icon fill colour for a hero CTA button style.
 */
function matrix_rd_home_hero_cta_icon_fill(string $button_style): string {
    return $button_style === 'black' ? '#ffffff' : '#000000';
}

/**
 * Modifier classes for optional per-slide design options.
 *
 * @param array<string, mixed> $slide
 */
function matrix_rd_home_hero_slide_modifier_classes(array $slide): string {
    $classes = [];

    if (($slide['mobile_text_size'] ?? 'default') === 'compact') {
        $classes[] = 'home-hero-slide--text-compact';
    }
    if (! empty($slide['compact_overlay'])) {
        $classes[] = 'home-hero-slide--compact-overlay';
    }
    if (! empty($slide['full_width_content'])) {
        $classes[] = 'home-hero-slide--full-width';
    }
    if (! empty($slide['body_highlight'])) {
        $classes[] = 'home-hero-slide--body-highlight';
    }
    if (! empty($slide['body_emphasis'])) {
        $classes[] = 'home-hero-slide--body-emphasis';
    }
    if (($slide['title_size'] ?? 'default') === 'small') {
        $classes[] = 'home-hero-slide--title-small';
    }

    return $classes === [] ? '' : ' ' . implode(' ', $classes);
}

/**
 * Render hero subtext, optionally as newsletter-style white highlight lines.
 */
function matrix_rd_home_hero_render_body_html(string $html, bool $highlight = false): string {
    $html = trim($html);
    if ($html === '') {
        return '';
    }

    if (! $highlight) {
        return wp_kses_post($html);
    }

    $normalized = preg_replace('/<br\s*\/?>/i', "\n", $html);
    $normalized = html_entity_decode((string) $normalized, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $normalized = wp_strip_all_tags((string) $normalized);
    $lines      = preg_split('/\R+/', $normalized) ?: [];

    $out = [];
    foreach ($lines as $index => $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }
        $mod = $index === 0 ? ' home-hero-slide__highlight-line--first' : '';
        $out[] = '<span class="home-hero-slide__highlight-line' . $mod . '">' . esc_html($line) . '</span>';
    }

    return $out === [] ? '' : implode('', $out);
}

/**
 * Normalize one hero slide row from ACF or a built-in default array.
 *
 * @param array<string, mixed> $row
 * @return array<string, mixed>|null
 */
function matrix_rd_normalize_home_hero_slide(array $row): ?array {
    $row = matrix_rd_map_hero_slide_acf_row($row);

    $left_pattern         = matrix_rd_acf_image($row['left_pattern'] ?? null);
    $left_pattern_mobile  = matrix_rd_acf_image($row['left_pattern_mobile'] ?? null);
    $left_image           = matrix_rd_acf_image($row['left_image'] ?? null);
    $left_image_mobile    = matrix_rd_acf_image($row['left_image_mobile'] ?? null);
    $right_image          = matrix_rd_acf_image($row['right_image'] ?? null);
    $right_image_mobile   = matrix_rd_acf_image($row['right_image_mobile'] ?? null);

    $heading         = trim((string) ($row['heading'] ?? ''));
    $heading_mobile  = trim((string) ($row['heading_mobile'] ?? ''));
    $subtext         = trim((string) ($row['subtext'] ?? ''));
    $subtext_mobile  = trim((string) ($row['subtext_mobile'] ?? ''));
    $button_note     = trim((string) ($row['button_note'] ?? ''));
    $hero_link  = is_array($row['hero_link'] ?? null) ? $row['hero_link'] : [];
    $text_color = in_array($row['text_color'] ?? '', ['white', 'black'], true) ? $row['text_color'] : 'white';
    $button_style = in_array($row['button_style'] ?? '', ['white', 'black', 'yellow'], true) ? $row['button_style'] : 'white';
    $button_hover_style = in_array($row['button_hover_style'] ?? '', ['default', 'white', 'black', 'yellow'], true)
        ? $row['button_hover_style']
        : 'default';
    $left_overlay = in_array($row['left_overlay'] ?? '', ['black', 'dark_brown', 'yellow', 'transparent', 'custom'], true)
        ? $row['left_overlay']
        : 'black';
    $title_size = in_array($row['title_size'] ?? '', ['default', 'small'], true)
        ? $row['title_size']
        : 'default';
    $mobile_text_size = in_array($row['mobile_text_size'] ?? '', ['default', 'compact'], true)
        ? $row['mobile_text_size']
        : 'default';

    $has_content = $left_pattern['url'] !== ''
        || $left_image['url'] !== ''
        || $heading !== ''
        || $heading_mobile !== ''
        || $subtext !== ''
        || $subtext_mobile !== ''
        || $right_image['url'] !== '';

    if (! $has_content) {
        return null;
    }

    return [
        'left_pattern'        => $left_pattern,
        'left_pattern_mobile' => $left_pattern_mobile,
        'left_image'          => $left_image,
        'left_image_mobile'   => $left_image_mobile,
        'heading'             => $heading,
        'heading_mobile'      => $heading_mobile,
        'subtext'             => $subtext,
        'subtext_mobile'      => $subtext_mobile,
        'hero_link'           => $hero_link,
        'button_note'         => $button_note,
        'right_image'         => $right_image,
        'right_image_mobile'  => $right_image_mobile,
        'left_overlay'        => $left_overlay,
        'left_overlay_custom' => (string) ($row['left_overlay_custom'] ?? ''),
        'text_color'          => $text_color,
        'button_style'        => $button_style,
        'button_hover_style'  => $button_hover_style,
        'button_icon'         => (bool) ($row['button_icon'] ?? true),
        'compact_overlay'     => ! empty($row['compact_overlay']),
        'full_width_content'  => ! empty($row['full_width_content']),
        'body_highlight'      => ! empty($row['body_highlight']),
        'body_emphasis'       => ! empty($row['body_emphasis']),
        'title_size'          => $title_size,
        'mobile_text_size'    => $mobile_text_size,
    ];
}

/**
 * Built-in slides used when the hero_slides repeater is empty.
 *
 * @return array<int, array<string, mixed>>
 */
function matrix_rd_get_default_home_hero_slides(): array {
    $asset             = static fn (string $file): string => matrix_rd_home_hero_asset_url($file);
    $shop              = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/');
    $personalised_midi = matrix_rd_home_hero_personalised_midi_url();

    $slide_one = matrix_rd_normalize_home_hero_slide([
        'slide_left_pattern'        => $asset('slide-1-left-pattern.png'),
        'slide_left_image'          => $asset('slide-1-neon.png'),
        'slide_left_image_mobile'   => $asset('slide-1-neon.png'),
        'slide_heading'             => __('The Original Donuts In Dublin', 'matrix-starter'),
        'slide_subtext'             => __("Made in Ireland. Made Fresh Daily. The Original Donuts In Dublin Since\n1978.", 'matrix-starter'),
        'slide_right_image'         => $asset('slide-1-right.png'),
        'slide_left_overlay'        => 'black',
        'slide_text_color'          => 'white',
        'slide_button_style'        => 'white',
        'slide_button_icon'         => 1,
        'slide_hero_link'           => [
            'title'  => __('Order fresh box now', 'matrix-starter'),
            'url'    => $personalised_midi,
            'target' => '',
        ],
    ]);

    $slide_two = matrix_rd_normalize_home_hero_slide([
        'slide_left_pattern'   => $asset('slide-2-left-pattern.png'),
        'slide_right_image'    => $asset('slide-2-right-layer-3.png'),
        'slide_heading'        => __('Curate Your Box of 6 or 12', 'matrix-starter'),
        'slide_subtext'        => __('Mix and match your favorite sourdough glazes to create the ultimate custom box for friends, office, or events.', 'matrix-starter'),
        'slide_left_overlay'   => 'dark_brown',
        'slide_text_color'     => 'white',
        'slide_button_style'   => 'white',
        'slide_button_icon'    => 1,
        'slide_hero_link'      => [
            'title'  => __('Build your custom box', 'matrix-starter'),
            'url'    => home_url('/donut-box/'),
            'target' => '',
        ],
    ]);

    $slide_three = matrix_rd_normalize_home_hero_slide([
        'slide_left_pattern' => $asset('slide-3-left-pattern.png'),
        'slide_right_image'  => $asset('slide-3-right-layer-4.png'),
        'slide_heading'      => __('Treat Yourself: 15% Off Your First Box!', 'matrix-starter'),
        'slide_subtext'      => __('Discover our signature handcrafted sourdough and vegan donuts Rolling Donut. Made fresh daily. Order online and get them delivered straight to your home or office.', 'matrix-starter'),
        'slide_left_overlay' => 'yellow',
        'slide_text_color'   => 'black',
        'slide_button_style' => 'black',
        'slide_button_icon'  => 1,
        'slide_hero_link'    => [
            'title'  => __('Buy Now', 'matrix-starter'),
            'url'    => $shop,
            'target' => '',
        ],
    ]);

    return array_values(array_filter([$slide_one, $slide_two, $slide_three]));
}

/**
 * Slides for the home hero carousel.
 *
 * @return array<int, array<string, mixed>>
 */
function matrix_rd_get_home_hero_slides(): array {
    if (! function_exists('get_field')) {
        return [];
    }

    $rows = get_field('hero_slides');
    if (is_array($rows) && $rows !== []) {
        $slides = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $slide = matrix_rd_normalize_home_hero_slide($row);
            if ($slide !== null) {
                $slides[] = $slide;
            }
        }
        if ($slides !== []) {
            return $slides;
        }
    }

    return matrix_rd_get_default_home_hero_slides();
}

/**
 * Copy legacy single-hero post meta into the hero_slides repeater once (admin only).
 */
function matrix_rd_migrate_legacy_home_hero_slides(int $post_id): void {
    if (! function_exists('get_field') || ! function_exists('update_field')) {
        return;
    }

    $front_page_id = (int) get_option('page_on_front');
    if ($front_page_id <= 0 || $post_id !== $front_page_id) {
        return;
    }

    if (get_option('matrix_rd_hero_slides_migrated', '') === (string) $post_id) {
        return;
    }

    $existing = get_field('hero_slides', $post_id);
    if (is_array($existing) && $existing !== [] && ! matrix_rd_hero_slides_need_seeding($existing)) {
        update_option('matrix_rd_hero_slides_migrated', (string) $post_id);
        return;
    }

    $banner_left  = get_post_meta($post_id, 'banner_left', true);
    $banner_right = get_post_meta($post_id, 'banner_right', true);
    if ($banner_left === '' && $banner_right === '') {
        return;
    }

    $hero_text = trim((string) get_post_meta($post_id, 'hero_text', true));
    $lines     = $hero_text !== '' ? preg_split('/\r\n|\r|\n/', $hero_text) : [];
    $lines     = is_array($lines) ? array_values(array_filter(array_map('trim', $lines))) : [];

    if (count($lines) === 1 && preg_match('/^(.*?)(\d{4}\.?)$/', $lines[0], $matches)) {
        $lines = [rtrim($matches[1]), $matches[2]];
    } elseif ($lines === [] && $hero_text !== '') {
        $lines = [$hero_text];
    }

    $hero_link = get_post_meta($post_id, 'hero_link', true);
    if (is_string($hero_link) && $hero_link !== '') {
        $hero_link = maybe_unserialize($hero_link);
    }
    if (! is_array($hero_link)) {
        $hero_link = [];
    }

    $slide = [
        'slide_left_pattern'         => get_post_meta($post_id, 'banner_left', true),
        'slide_left_pattern_mobile'  => get_post_meta($post_id, 'banner_top_mobile', true),
        'slide_left_image'           => get_post_meta($post_id, 'neon', true),
        'slide_left_image_mobile'    => get_post_meta($post_id, 'neon_mobile', true),
        'slide_right_image'          => get_post_meta($post_id, 'banner_right', true),
        'slide_right_image_mobile'   => get_post_meta($post_id, 'banner_bottom_mobile', true),
        'slide_subtext'              => $hero_text,
        'slide_hero_link'            => $hero_link,
        'slide_left_overlay'         => 'black',
        'slide_text_color'           => 'white',
        'slide_button_style'         => 'white',
        'slide_button_icon'          => 1,
    ];

    update_field('hero_slides', [$slide], $post_id);
    update_option('matrix_rd_hero_slides_migrated', (string) $post_id);
}
add_action('acf/save_post', 'matrix_rd_migrate_legacy_home_hero_slides', 5);

/**
 * Attachment ID for the default left-panel background pattern (slide-3 yellow).
 */
function matrix_rd_home_hero_default_left_pattern_id(): int {
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }

    $from_theme = matrix_rd_theme_hero_attachment_id('slide-3-left-pattern.png');
    if ($from_theme > 0) {
        $cached = $from_theme;
        return $cached;
    }

    $by_url = (int) attachment_url_to_postid(
        home_url('/wp-content/uploads/2026/07/slide-3-left-pattern.png')
    );
    if ($by_url > 0) {
        $cached = $by_url;
        return $cached;
    }

    $existing = get_posts([
        'post_type'              => 'attachment',
        'post_status'            => 'inherit',
        'posts_per_page'         => 1,
        'fields'                 => 'ids',
        'no_found_rows'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'meta_query'             => [
            [
                'key'     => '_wp_attached_file',
                'value'   => 'slide-3-left-pattern.png',
                'compare' => 'LIKE',
            ],
        ],
    ]);

    $cached = $existing !== [] ? (int) $existing[0] : 0;
    return $cached;
}

/**
 * Pre-select the yellow slide-3 pattern when adding a hero repeater row.
 *
 * @param array<string, mixed> $field
 * @return array<string, mixed>
 */
function matrix_rd_home_hero_left_pattern_default_field(array $field): array {
    $id = matrix_rd_home_hero_default_left_pattern_id();
    if ($id > 0) {
        $field['default_value'] = $id;
    }

    return $field;
}
add_filter(
    'acf/load_field/key=field_homepage_hero_slides_left_pattern',
    'matrix_rd_home_hero_left_pattern_default_field'
);

/**
 * Seed hero_slides from legacy meta or built-in defaults when the editor opens.
 */
function matrix_rd_migrate_legacy_home_hero_on_edit(): void {
    if (! is_admin() || ! function_exists('get_current_screen')) {
        return;
    }

    $screen = get_current_screen();
    if (! $screen || $screen->base !== 'post' || $screen->post_type !== 'page') {
        return;
    }

    $post_id = isset($_GET['post']) ? (int) $_GET['post'] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if ($post_id <= 0) {
        return;
    }

    matrix_rd_migrate_legacy_home_hero_slides($post_id);
    matrix_rd_seed_home_hero_slides($post_id);
    matrix_rd_seed_featured_slides($post_id);
}
add_action('current_screen', 'matrix_rd_migrate_legacy_home_hero_on_edit');

/**
 * Home hero slider styles (loads after legacy CSS).
 */
function matrix_rd_home_hero_slider_overrides(): void {
    if (is_admin() || ! is_front_page()) {
        return;
    }

    $deps = ['matrix-rd-legacy'];
    if (wp_style_is('matrix-starter', 'enqueued')) {
        $deps = ['matrix-starter'];
    }

    $hero_css = get_template_directory() . '/assets/css/rolling-donut-home-hero.css';
    if (is_readable($hero_css)) {
        wp_enqueue_style(
            'matrix-rd-home-hero-slider',
            get_template_directory_uri() . '/assets/css/rolling-donut-home-hero.css',
            $deps,
            (string) filemtime($hero_css)
        );
    }
}
add_action('wp_enqueue_scripts', 'matrix_rd_home_hero_slider_overrides', 120);

/**
 * QC overrides for the homepage hero slider.
 */
function matrix_rd_home_hero_slider_qc_overrides(): void {
    if (is_admin() || (! is_front_page() && ! is_page('about-us'))) {
        return;
    }

    $deps = [];
    if (wp_style_is('matrix-rd-legacy', 'enqueued')) {
        $deps[] = 'matrix-rd-legacy';
    }
    if (wp_style_is('matrix-starter', 'enqueued')) {
        $deps[] = 'matrix-starter';
    }

    wp_register_style('matrix-rd-home-hero-slider-qc', false, $deps, '1');
    wp_enqueue_style('matrix-rd-home-hero-slider-qc');
    wp_add_inline_style(
        'matrix-rd-home-hero-slider-qc',
        '.home-hero-slider .home-hero-slide__cta:hover svg,'
        . '.home-hero-slider .home-hero-slide__cta:hover svg path{fill:#000!important;color:#000!important}'
        . '.home-hero-slider .home-hero-slide__cta--hover-black:hover svg,'
        . '.home-hero-slider .home-hero-slide__cta--hover-black:hover svg path{fill:#fff!important;color:#fff!important}'
    );
}
add_action('wp_enqueue_scripts', 'matrix_rd_home_hero_slider_qc_overrides', 120);
