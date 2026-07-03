<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param array<int, array<string, mixed>>|null $rows
 * @param array<int, string>                    $defaults
 */
function matrix_flexi_padding_classes(?array $rows, array $defaults): array
{
    if (empty($rows)) {
        return $defaults;
    }

    $classes = [];
    foreach ($rows as $row) {
        $screen_size    = $row['screen_size'] ?? '';
        $padding_top    = $row['padding_top'] ?? null;
        $padding_bottom = $row['padding_bottom'] ?? null;
        if ($screen_size && $padding_top !== '' && $padding_top !== null) {
            $classes[] = "{$screen_size}:pt-[{$padding_top}rem]";
        }
        if ($screen_size && $padding_bottom !== '' && $padding_bottom !== null) {
            $classes[] = "{$screen_size}:pb-[{$padding_bottom}rem]";
        }
    }

    return $classes !== [] ? $classes : $defaults;
}

function matrix_flexi_heading_html(string $raw, string $heading_tag = 'h1'): string
{
    $allowed = [
        'br'     => [],
        'strong' => [],
        'em'     => [],
        'span'   => ['class' => true],
    ];

    $clean = preg_replace('#</?p[^>]*>#i', '', $raw);
    $clean = preg_replace('#</?div[^>]*>#i', '', (string) $clean);
    $clean = preg_replace('#</?h[1-6][^>]*>#i', '', (string) $clean);

    return trim((string) wp_kses($clean, $allowed));
}

function matrix_subhero_is_dark_background(string $hex): bool
{
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    if (strlen($hex) !== 6 || !ctype_xdigit($hex)) {
        return true;
    }

    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;

    return $luminance < 0.55;
}

function matrix_subhero_resolve_decoration_fill(string $style, string $custom_color = ''): string
{
    if ($custom_color !== '') {
        return $custom_color;
    }

    return match ($style) {
        'yellow_stacked', 'yellow_wave' => '#f4bd0b',
        'blue_stacked' => '#0098d8',
        default => '#d1d5db',
    };
}

function matrix_subhero_kicker_class(string $background_color, bool $use_white_text): string
{
    if ($use_white_text) {
        return 'text-[13px] font-semibold uppercase tracking-[0.14em] text-[#f4bd0b]';
    }

    return matrix_subhero_is_dark_background($background_color)
        ? 'text-[13px] font-semibold uppercase tracking-[0.14em] text-[#f4bd0b]'
        : 'text-[13px] font-semibold uppercase tracking-[0.14em] text-[#003b65]';
}

function matrix_subhero_render_decoration(string $style, string $fill, int $custom_attachment_id = 0): void
{
    if ($style === 'custom' && $custom_attachment_id > 0) {
        echo wp_get_attachment_image($custom_attachment_id, 'full', false, [
            'class' => 'pointer-events-none absolute right-0 top-0 z-[1] h-full w-auto max-w-[45%] object-contain object-right',
            'alt'   => '',
        ]);
        return;
    }

    if ($style === 'none') {
        return;
    }

    $map = [
        'yellow_stacked' => 'decoration-yellow-stacked.svg',
        'yellow_wave'    => 'decoration-yellow-wave.svg',
        'blue_stacked'   => 'decoration-blue-stacked.svg',
        'default_grey'   => 'decoration-default-grey.svg',
    ];

    $file = $map[$style] ?? $map['default_grey'];
    $path = get_template_directory() . '/assets/images/subhero/' . $file;
    if (!is_readable($path)) {
        return;
    }

    $url = get_template_directory_uri() . '/assets/images/subhero/' . $file;
    printf(
        '<img src="%s" alt="" class="pointer-events-none absolute right-0 top-0 z-[1] h-full w-auto max-w-[45%] object-contain object-right" style="color:%s" aria-hidden="true" />',
        esc_url($url),
        esc_attr($fill)
    );
}

/**
 * Blog index / category archive subhero args from Theme Options.
 */
function matrix_blog_subhero_args(?WP_Term $term = null): array
{
    $settings = get_field('blog_settings', 'option');
    if (!is_array($settings)) {
        // Legacy option key from older installs; prefer blog_settings.
        $settings = get_field('pace_blog_settings', 'option');
    }
    $settings = is_array($settings) ? $settings : [];

    $kicker = (string) ($settings['hero_kicker'] ?? "WHAT'S NEW");
    $title  = (string) ($settings['hero_title'] ?? 'News & updates');
    $intro  = (string) ($settings['hero_intro'] ?? 'Latest news, stories, and announcements.');
    $bg     = (string) ($settings['hero_background'] ?? '#003b65');
    $deco   = (string) ($settings['decoration_style'] ?? 'yellow_stacked');

    if ($term instanceof WP_Term) {
        $title = $term->name;
        $intro = trim((string) term_description($term)) ?: $intro;
    }

    return [
        'kicker'            => $kicker,
        'heading'           => $title,
        'heading_tag'       => 'h1',
        'content'           => $intro,
        'background_color'  => $bg,
        'decoration_style'  => $deco,
        'use_white_text'    => true,
        'padding_settings'  => [],
    ];
}

/**
 * Resolve an ACF image field ID for page templates (no bundled fallbacks).
 */
function matrix_page_image_id(string $field_name, int $post_id = 0): int
{
    $post_id = $post_id > 0 ? $post_id : (int) get_the_ID();

    return (int) get_field($field_name, $post_id);
}
