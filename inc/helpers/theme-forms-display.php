<?php
/**
 * Shared Theme_Forms display helpers (captcha markup from Theme Options).
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Active captcha provider from Theme Options → Contact Forms.
 */
function matrix_theme_form_captcha_provider(): string {
    if (!function_exists('get_field')) {
        return 'none';
    }

    $provider = strtolower(trim((string) (get_field('captcha_provider', 'option') ?: 'none')));

    return in_array($provider, ['none', 'recaptcha_v3', 'turnstile'], true) ? $provider : 'none';
}

/**
 * Whether a captcha is configured and should render / validate.
 */
function matrix_theme_form_captcha_enabled(): bool {
    $provider = matrix_theme_form_captcha_provider();

    if ($provider === 'recaptcha_v3') {
        return (string) (function_exists('get_field') ? get_field('recaptcha_site_key', 'option') : '') !== '';
    }

    if ($provider === 'turnstile') {
        return (string) (function_exists('get_field') ? get_field('turnstile_site_key', 'option') : '') !== '';
    }

    return false;
}

/**
 * Captcha widget markup for theme forms (Turnstile visible widget; reCAPTCHA v3 is injected on submit).
 *
 * @param array{class?: string, size?: string, theme?: string} $args
 */
function matrix_theme_form_captcha_markup(array $args = []): string {
    if (!matrix_theme_form_captcha_enabled()) {
        return '';
    }

    if (matrix_theme_form_captcha_provider() !== 'turnstile') {
        return '';
    }

    $class = isset($args['class']) ? (string) $args['class'] : 'cf-turnstile';
    $theme = isset($args['theme']) ? (string) $args['theme'] : 'light';
    $size  = isset($args['size']) ? (string) $args['size'] : 'normal';

    return sprintf(
        '<div class="%s" data-theme="%s" data-size="%s" aria-hidden="false"></div>',
        esc_attr($class),
        esc_attr($theme),
        esc_attr($size)
    );
}
