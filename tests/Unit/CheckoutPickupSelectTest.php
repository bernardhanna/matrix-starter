<?php
/**
 * Mobile Free Collection picker: clearing a default location must not fire a
 * native change (Local Pickup Plus would block checkout), and the open Select2
 * control must stay in document flow so the options remain tappable.
 */

$express_js  = file_get_contents(__DIR__ . '/../../assets/js/rolling-donut-express-checkout.js');
$express_css = file_get_contents(__DIR__ . '/../../assets/css/rolling-donut-express-checkout.css');
$checkout_css = file_get_contents(__DIR__ . '/../../assets/css/rolling-donut-checkout.css');

test('clearing a pickup default does not trigger a native change event', function () use ($express_js) {
    expect(preg_match('/function clearPickupSelectValue\(\$select\) \{.*?\n    \}/s', $express_js, $matches))->toBe(1);
    expect($matches[0])->toContain("trigger('change.select2')");
    expect($matches[0])->not->toContain("\$select.trigger('change')");
    expect($matches[0])->toContain('Do not trigger a native `change`');
});

test('pickup loading poll gives up within two seconds', function () use ($express_js) {
    expect($express_js)->toContain('attempts > 20');
    expect($express_js)->not->toContain('attempts > 100');
});

test('mobile tap selection is not blocked by the open-tap guard', function () use ($express_js) {
    expect($express_js)->toContain("original.type === 'touchend'");
    expect($express_js)->toContain("original.type === 'pointerup'");
});

test('open pickup Select2 control stays in flow instead of covering the menu', function () use ($express_css) {
    expect($express_css)->toContain('#rd-checkout-step-method .pickup-location-field > .select2-container');
    expect($express_css)->toContain('position: relative !important');
    expect($express_css)->not->toContain('#rd-checkout-step-method .pickup-location-field > .select2-container--open');
    expect($express_css)->toContain('#rd-checkout-step-method:has(.select2-container--open)');
});

test('checkout blockUI overlays do not swallow taps', function () use ($checkout_css) {
    expect($checkout_css)->toContain('.rd-express-checkout-form .blockUI');
    expect($checkout_css)->toContain('pointer-events: none !important');
});
