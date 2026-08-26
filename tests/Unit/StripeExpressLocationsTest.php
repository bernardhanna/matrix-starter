<?php
/**
 * Stripe Link / express wallets must only appear at checkout.
 */

test('theme forces Stripe express wallets off product and cart pages', function () {
    $file = dirname(__DIR__, 2) . '/inc/rolling-donut-express-checkout.php';

    expect(is_readable($file))->toBeTrue();

    $contents = file_get_contents($file);

    expect($contents)->toContain("add_filter('wc_stripe_hide_payment_request_on_product_page', '__return_true')");
    expect($contents)->toContain("add_filter('wc_stripe_show_payment_request_on_cart', '__return_false')");
    expect($contents)->toContain("add_filter('option_woocommerce_stripe_settings', 'matrix_rd_stripe_express_checkout_locations_checkout_only')");
    expect($contents)->toContain("remove_action('woocommerce_after_add_to_cart_form'");
    expect($contents)->toContain("remove_action('woocommerce_proceed_to_checkout'");
    expect($contents)->toContain("'link_button_locations'");
    expect($contents)->toContain("return ['checkout']");
});
