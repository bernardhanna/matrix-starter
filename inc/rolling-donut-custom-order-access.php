<?php
/**
 * Custom Order builder access (pure, dependency-free).
 *
 * /custom-order is a staff tool. Guests who receive a Save & Share Cart link
 * still need to open the cart and pay without signing in — this helper decides
 * where a request for that product should go.
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Where a visitor hitting the Custom Order product should be sent.
 *
 * Logged-in users stay on the builder. Guests who already have that product in
 * the cart (almost always a shared-cart retrieve) go to the cart. Everyone else
 * is sent to sign in.
 *
 * @return string|null Null means do not redirect.
 */
function matrix_rd_guest_custom_order_redirect_url(
    bool $is_logged_in,
    bool $cart_contains_custom_order,
    string $cart_url,
    string $login_url
): ?string {
    if ($is_logged_in) {
        return null;
    }

    if ($cart_contains_custom_order && $cart_url !== '') {
        return $cart_url;
    }

    return $login_url !== '' ? $login_url : null;
}
