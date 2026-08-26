<?php
/**
 * Unit tests for Custom Order page access.
 *
 * The builder is staff-only. Shared-cart recipients are almost always logged
 * out and must still reach the cart, not a login wall.
 */

if (! defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

require_once __DIR__ . '/../../inc/rolling-donut-custom-order-access.php';

test('logged-in users stay on the custom-order builder', function () {
    expect(matrix_rd_guest_custom_order_redirect_url(
        true,
        false,
        'https://example.test/cart/',
        'https://example.test/my-account/'
    ))->toBeNull();
});

test('a guest with a custom-order already in the cart is sent to the cart', function () {
    expect(matrix_rd_guest_custom_order_redirect_url(
        false,
        true,
        'https://example.test/cart/',
        'https://example.test/my-account/'
    ))->toBe('https://example.test/cart/');
});

test('a guest with an empty cart is sent to sign in', function () {
    expect(matrix_rd_guest_custom_order_redirect_url(
        false,
        false,
        'https://example.test/cart/',
        'https://example.test/my-account/'
    ))->toBe('https://example.test/my-account/');
});

test('a logged-in user with a custom-order in the cart still stays on the builder', function () {
    expect(matrix_rd_guest_custom_order_redirect_url(
        true,
        true,
        'https://example.test/cart/',
        'https://example.test/my-account/'
    ))->toBeNull();
});
