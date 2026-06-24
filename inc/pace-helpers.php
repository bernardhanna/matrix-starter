<?php
/**
 * PACE hero / subhero helpers (used by template-parts/hero/*).
 */

if (! function_exists('matrix_pace_hex_to_rgb')) {
    /**
     * @return array{r: int, g: int, b: int}|null
     */
    function matrix_pace_hex_to_rgb(string $color): ?array {
        $color = trim($color);
        if ($color === '') {
            return null;
        }
        if (str_starts_with($color, 'rgb')) {
            if (preg_match('/rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)/', $color, $m)) {
                return ['r' => (int) $m[1], 'g' => (int) $m[2], 'b' => (int) $m[3]];
            }
            return null;
        }
        $hex = ltrim($color, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if (! preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
            return null;
        }

        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
    }
}

if (! function_exists('matrix_pace_subhero_is_dark_background')) {
    function matrix_pace_subhero_is_dark_background(string $background_color): bool {
        $rgb = matrix_pace_hex_to_rgb($background_color);
        if ($rgb === null) {
            return true;
        }
        $luminance = (0.2126 * $rgb['r'] + 0.7152 * $rgb['g'] + 0.0722 * $rgb['b']) / 255;

        return $luminance < 0.55;
    }
}

if (! function_exists('matrix_pace_subhero_resolve_decoration_fill')) {
    function matrix_pace_subhero_resolve_decoration_fill(string $decoration_style, string $decoration_color): string {
        if ($decoration_color !== '') {
            return $decoration_color;
        }

        return match ($decoration_style) {
            'default_grey' => '#d9d9d9',
            'default_white' => '#ffffff',
            default => '#d9d9d9',
        };
    }
}

if (! function_exists('matrix_pace_subhero_kicker_class')) {
    function matrix_pace_subhero_kicker_class(string $background_color, bool $use_white_text): string {
        if ($use_white_text) {
            return 'text-[13px] font-semibold uppercase tracking-[1.82px] text-[#f4bd0b]';
        }

        return 'text-[13px] font-semibold uppercase tracking-[1.82px] text-[#003b65]';
    }
}

if (! function_exists('matrix_pace_subhero_render_decoration')) {
    function matrix_pace_subhero_render_decoration(string $decoration_style, string $decoration_fill, int $custom_decoration): void {
        if ($decoration_style === 'none') {
            return;
        }

        if ($decoration_style === 'custom' && $custom_decoration > 0) {
            echo '<div class="pointer-events-none absolute inset-y-0 right-0 z-[1] hidden w-[42%] max-w-[520px] lg:block" aria-hidden="true">';
            echo wp_get_attachment_image(
                $custom_decoration,
                'full',
                false,
                ['class' => 'h-full w-full object-contain object-right']
            );
            echo '</div>';
            return;
        }

        printf(
            '<div class="pointer-events-none absolute inset-y-0 right-0 z-[1] hidden w-[38%] opacity-40 lg:block" aria-hidden="true" style="background: linear-gradient(135deg, transparent 0%%, %s 100%%);"></div>',
            esc_attr($decoration_fill)
        );
    }
}

if (! function_exists('matrix_pace_flexi_padding_classes')) {
    /**
     * @param array<int, array<string, mixed>>|null $rows
     * @param list<string> $defaults
     * @return list<string>
     */
    function matrix_pace_flexi_padding_classes(?array $rows, array $defaults = []): array {
        if (empty($rows)) {
            return $defaults;
        }

        $classes = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $screen_size    = (string) ($row['screen_size'] ?? '');
            $padding_top    = $row['padding_top'] ?? null;
            $padding_bottom = $row['padding_bottom'] ?? null;
            if ($screen_size !== '' && $padding_top !== '' && $padding_top !== null) {
                $classes[] = "{$screen_size}:pt-[{$padding_top}rem]";
            }
            if ($screen_size !== '' && $padding_bottom !== '' && $padding_bottom !== null) {
                $classes[] = "{$screen_size}:pb-[{$padding_bottom}rem]";
            }
        }

        return $classes !== [] ? $classes : $defaults;
    }
}

if (! function_exists('matrix_pace_flexi_heading_html')) {
    function matrix_pace_flexi_heading_html(string $title_raw, string $heading_tag = 'h1'): string {
        unset($heading_tag);

        if ($title_raw === '') {
            return '';
        }

        $title_clean = preg_replace('#</?p[^>]*>#i', '', $title_raw);
        $title_clean = preg_replace('#</?div[^>]*>#i', '', (string) $title_clean);
        $title_clean = preg_replace('#</?h[1-6][^>]*>#i', '', (string) $title_clean);

        return trim((string) wp_kses($title_clean, [
            'br'     => [],
            'strong' => [],
            'em'     => [],
            'span'   => ['class' => true],
        ]));
    }
}
