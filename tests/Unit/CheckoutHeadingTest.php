<?php
/**
 * Checkout page heading and address-section heading classes.
 */

$checkout_php = file_get_contents(__DIR__ . '/../../inc/rolling-donut-checkout.php');
$checkout_css = file_get_contents(__DIR__ . '/../../assets/css/rolling-donut-checkout.css');
$billing_php  = file_get_contents(__DIR__ . '/../../woocommerce/checkout/form-billing.php');
$express_js   = file_get_contents(__DIR__ . '/../../assets/js/rolling-donut-express-checkout.js');
$express_css  = file_get_contents(__DIR__ . '/../../assets/css/rolling-donut-express-checkout.css');
$form_checkout = file_get_contents(__DIR__ . '/../../woocommerce/checkout/form-checkout.php');
$heading_css  = $checkout_php . "\n" . $checkout_css;

test('checkout heading uses the Checkout title classes', function () use ($checkout_php) {
    expect($checkout_php)->toContain(
        '<h1 class="rd-checkout-heading-title text-black-full text-xl-font font-reg420">'
    );
    expect($checkout_php)->toContain("esc_html__('Checkout', 'rolling-donut')");
});

test('checkout heading stacks below the logo on small screens', function () use ($heading_css) {
    expect($heading_css)->toMatch('/\.rd-checkout-heading-row\s*\{[^}]*flex-direction:\s*column/s');
    expect($heading_css)->toMatch('/\.rd-checkout-heading-logo\s*\{[^}]*position:\s*static/s');
});

test('delivery and billing address headings share the address heading classes', function () use ($billing_php, $express_js) {
    $classes = 'rd-delivery-address-heading text-black-full text-md-font font-reg420';

    expect($billing_php)->toContain($classes);
    expect($express_js)->toContain($classes);
    expect($express_js)->toContain('addClass(ADDRESS_HEADING_CLASSES)');
});

test('checkout does not render a floating mobile pay bar', function () use ($form_checkout, $express_css, $express_js) {
    expect($form_checkout)->not->toContain('rd-mobile-pay-bar');
    expect($express_css)->not->toContain('.rd-mobile-pay-bar {');
    expect($express_js)->not->toContain('handleMobilePayBarClick');
});
