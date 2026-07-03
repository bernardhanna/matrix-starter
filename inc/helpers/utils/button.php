<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Utility classes for theme buttons (.btn-theme-* in assets/css/app.css).
 *
 * @param string               $variant primary|secondary|secondary-dark|ghost|soft|icon|share|submit|custom
 * @param array<string, mixed> $options   full_mobile (bool) — full width on mobile
 */
function matrix_btn_classes(string $variant = 'primary', array $options = []): string
{
    $map = [
        'primary'        => 'btn btn-theme-primary',
        'secondary'      => 'btn btn-theme-secondary',
        'secondary-dark' => 'btn btn-theme-secondary-dark',
        'ghost'          => 'btn btn-theme-ghost',
        'soft'           => 'btn btn-theme-soft',
        'icon'           => 'btn btn-theme-icon',
        'share'          => 'btn btn-theme-share',
        'submit'         => 'btn btn-theme-primary btn-theme-submit',
        'custom'         => 'btn btn-theme-custom',
    ];

    $classes = $map[$variant] ?? $map['primary'];

    if (!empty($options['full_mobile'])) {
        $classes .= ' btn-theme-full-mobile';
    }

    return $classes;
}
