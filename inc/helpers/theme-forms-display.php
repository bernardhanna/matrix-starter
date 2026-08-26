<?php
/**
 * Shared Theme_Forms display helpers (captcha markup from Theme Options).
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Hostnames where Cloudflare Turnstile (and reCAPTCHA) may render or validate.
 * Local / staging stay captcha-free so widgets are not blocked by hostname allowlists.
 *
 * @return array<int, string>
 */
function matrix_theme_form_captcha_live_hosts(): array {
    $hosts = array('therollingdonut.ie');

    if (function_exists('apply_filters')) {
        $hosts = (array) apply_filters('matrix_theme_form_captcha_hosts', $hosts);
    }

    return array_values(array_filter(array_map('strval', $hosts)));
}

/**
 * Whether the current site host is production (Turnstile allowed).
 */
function matrix_theme_form_captcha_is_live_host(): bool {
    $host = strtolower((string) wp_parse_url(home_url('/'), PHP_URL_HOST));
    $host = (string) preg_replace('/^www\./', '', $host);

    return $host !== '' && in_array($host, matrix_theme_form_captcha_live_hosts(), true);
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
 * Whether Theme Options has a captcha provider + site key (ignores hostname).
 */
function matrix_theme_form_captcha_configured(): bool {
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
 * Whether a captcha is configured and should render / validate on this host.
 */
function matrix_theme_form_captcha_enabled(): bool {
    return matrix_theme_form_captcha_configured() && matrix_theme_form_captcha_is_live_host();
}

/**
 * Verify a Cloudflare Turnstile token. Returns true when captcha is not active.
 */
function matrix_theme_form_turnstile_token_valid(?string $token = null): bool {
    if (!matrix_theme_form_captcha_enabled() || matrix_theme_form_captcha_provider() !== 'turnstile') {
        return true;
    }

    $token = $token !== null ? $token : sanitize_text_field(wp_unslash((string) ($_POST['cf-turnstile-response'] ?? '')));
    if ($token === '') {
        return false;
    }

    $secret = (string) (function_exists('get_field') ? get_field('turnstile_secret_key', 'option') : '');
    if ($secret === '') {
        return false;
    }

    $response = wp_remote_post(
        'https://challenges.cloudflare.com/turnstile/v0/siteverify',
        array(
            'timeout' => 10,
            'body'    => array(
                'secret'   => $secret,
                'response' => $token,
            ),
        )
    );
    if (is_wp_error($response)) {
        return false;
    }

    $json = json_decode((string) wp_remote_retrieve_body($response), true);

    return !empty($json['success']);
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
