<?php
/**
 * Turnstile / captcha host allowlist.
 */

if (! defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

require_once __DIR__ . '/../../inc/helpers/theme-forms-display.php';

test('production hostname is in the captcha allowlist', function () {
    expect(matrix_theme_form_captcha_live_hosts())->toContain('therollingdonut.ie');
});

test('localhost is not treated as a live captcha host', function () {
    expect(matrix_theme_form_captcha_live_hosts())->not->toContain('localhost');
});
