<?php
/**
 * Header layout on cart / checkout / thank-you.
 *
 * Legacy kept a centered logo (no menus) on cart — including the empty cart —
 * and on thank-you. Checkout hides the site nav because the page heading
 * already carries the logo.
 */

if (! defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

if (! function_exists('add_action')) {
    function add_action($hook = '', $callback = '', $priority = 10, $accepted_args = 1): void {}
}

if (! function_exists('add_filter')) {
    function add_filter($hook = '', $callback = '', $priority = 10, $accepted_args = 1): void {}
}

require_once __DIR__ . '/../../inc/rolling-donut-navbar.php';

test('storefront pages use the full navigation', function () {
    expect(matrix_rd_nav_header_mode(false, false, false))->toBe('full');
});

test('a cart with items uses the centered logo-only bar', function () {
    expect(matrix_rd_nav_header_mode(false, true, false))->toBe('logo_only');
});

test('an empty cart is still the cart page and uses the logo-only bar', function () {
    expect(matrix_rd_nav_header_mode(false, true, false))->toBe('logo_only');
});

test('checkout hides the site nav in favour of the heading logo', function () {
    expect(matrix_rd_nav_header_mode(false, false, true))->toBe('hidden');
});

test('thank-you keeps the logo-only bar even though it is a checkout endpoint', function () {
    expect(matrix_rd_nav_header_mode(true, false, true))->toBe('logo_only');
});
