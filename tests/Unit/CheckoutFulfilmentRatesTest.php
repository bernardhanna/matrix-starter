<?php
/**
 * Checkout method step must always offer Delivery and Collection.
 *
 * Local Pickup Plus marks the cart as pickup-only after Collection is chosen,
 * which strips delivery rates. The theme then re-renders step 1 from those
 * packages — so Change opens a list with only Collection. These helpers keep
 * both fulfilment rates available for the method radios.
 */

if (! defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

require_once __DIR__ . '/../../inc/helpers/checkout-fulfilment-rates.php';

beforeEach(function () {
    matrix_rd_checkout_fulfilment_rate_store([], true);
});

test('pickup rate ids are detected from local_pickup tokens', function () {
    expect(matrix_rd_checkout_rate_id_is_pickup('local_pickup_plus'))->toBeTrue();
    expect(matrix_rd_checkout_rate_id_is_pickup('local_pickup:2'))->toBeTrue();
    expect(matrix_rd_checkout_rate_id_is_pickup('flat_rate:1'))->toBeFalse();
    expect(matrix_rd_checkout_rate_id_is_pickup('free_shipping:3'))->toBeFalse();
});

test('kinds detect when a rate list has both fulfilment options', function () {
    $both = [
        'flat_rate:1'        => 'delivery',
        'local_pickup_plus'  => 'pickup',
    ];
    $pickup_only = ['local_pickup_plus' => 'pickup'];
    $delivery_only = ['flat_rate:1' => 'delivery'];

    expect(matrix_rd_checkout_fulfilment_kinds($both))->toBe(['pickup' => true, 'delivery' => true]);
    expect(matrix_rd_checkout_has_both_fulfilment_kinds($both))->toBeTrue();
    expect(matrix_rd_checkout_has_both_fulfilment_kinds($pickup_only))->toBeFalse();
    expect(matrix_rd_checkout_has_both_fulfilment_kinds($delivery_only))->toBeFalse();
});

test('restore puts delivery back when collection-only rates replace a full snapshot', function () {
    $snapshot = [
        'flat_rate:1'       => 'Delivery (Dublin only)',
        'local_pickup_plus' => 'Free Collection',
    ];
    $pickup_only = [
        'local_pickup_plus' => 'Free Collection',
    ];

    $restored = matrix_rd_checkout_restore_fulfilment_rates($pickup_only, $snapshot);

    expect($restored)->toHaveKey('flat_rate:1');
    expect($restored)->toHaveKey('local_pickup_plus');
    expect(matrix_rd_checkout_has_both_fulfilment_kinds($restored))->toBeTrue();
});

test('restore puts collection back when delivery-only rates replace a full snapshot', function () {
    $snapshot = [
        'flat_rate:1'       => 'Delivery (Dublin only)',
        'local_pickup_plus' => 'Free Collection',
    ];
    $delivery_only = [
        'flat_rate:1' => 'Delivery (Dublin only)',
    ];

    $restored = matrix_rd_checkout_restore_fulfilment_rates($delivery_only, $snapshot);

    expect($restored)->toHaveKey('local_pickup_plus');
    expect(matrix_rd_checkout_has_both_fulfilment_kinds($restored))->toBeTrue();
});

test('restore is a no-op when both options are already present', function () {
    $rates = [
        'flat_rate:1'       => 'Delivery',
        'local_pickup_plus' => 'Collection',
    ];

    expect(matrix_rd_checkout_restore_fulfilment_rates($rates, $rates))->toBe($rates);
});

test('remember_and_restore snapshots a full list and repairs a later pickup-only list', function () {
    $full = [
        'flat_rate:1'       => 'Delivery (Dublin only)',
        'local_pickup_plus' => 'Free Collection',
    ];

    expect(matrix_rd_checkout_remember_and_restore_fulfilment_rates($full))->toBe($full);

    $pickup_only = ['local_pickup_plus' => 'Free Collection'];
    $restored = matrix_rd_checkout_remember_and_restore_fulfilment_rates($pickup_only);

    expect(matrix_rd_checkout_has_both_fulfilment_kinds($restored))->toBeTrue();
    expect($restored)->toHaveKey('flat_rate:1');
});

test('merging per-order packages keeps delivery rates from the shipping sibling', function () {
    $packages = [
        [
            'rates' => ['local_pickup_plus' => 'Free Collection'],
            'ship_via' => ['local_pickup_plus'],
        ],
        [
            'rates' => ['flat_rate:1' => 'Delivery (Dublin only)'],
        ],
    ];

    $merged = matrix_rd_checkout_merge_packages_for_method_choice($packages);

    expect($merged)->toHaveCount(1);
    expect($merged[0]['rates'])->toHaveKey('local_pickup_plus');
    expect($merged[0]['rates'])->toHaveKey('flat_rate:1');
});

test('merging a single package leaves it unchanged', function () {
    $packages = [
        ['rates' => ['flat_rate:1' => 'Delivery', 'local_pickup_plus' => 'Collection']],
    ];

    expect(matrix_rd_checkout_merge_packages_for_method_choice($packages))->toBe($packages);
});

test('checkout keeps delivery rates even before a Dublin address is known', function () {
    expect(matrix_rd_checkout_should_keep_package_rate('flat_rate:1', false, true))->toBeTrue();
    expect(matrix_rd_checkout_should_keep_package_rate('local_pickup_plus', false, true))->toBeTrue();
    expect(matrix_rd_checkout_should_keep_package_rate('flat_rate:1', false, false))->toBeFalse();
    expect(matrix_rd_checkout_should_keep_package_rate('flat_rate:1', true, false))->toBeTrue();
});
