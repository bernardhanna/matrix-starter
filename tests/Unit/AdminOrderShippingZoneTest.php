<?php
/**
 * Orders list shows Shipping Zone instead of WooCommerce's default Ship to.
 */

if (! defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

require_once dirname(__DIR__, 2) . '/inc/helpers/admin-order-shipping-zone.php';

test('orders list replaces Ship to with Shipping Zone', function () {
    $columns = matrix_rd_add_order_shipping_zone_column([
        'cb'               => '',
        'order_number'     => 'Order',
        'shipping_address' => 'Ship to',
        'order_total'      => 'Total',
    ]);

    expect($columns)->toHaveKey('order_shipping_zone');
    expect($columns['order_shipping_zone'])->toBe('Shipping Zone');
    expect($columns)->not->toHaveKey('shipping_address');
    expect(array_keys($columns))->toBe([
        'cb',
        'order_number',
        'order_shipping_zone',
        'order_total',
    ]);
});

test('orders list still adds Shipping Zone when Ship to is missing', function () {
    $columns = matrix_rd_add_order_shipping_zone_column([
        'order_number' => 'Order',
        'order_total'  => 'Total',
    ]);

    expect($columns['order_shipping_zone'])->toBe('Shipping Zone');
});

test('local pickup methods are treated as collection', function () {
    expect(matrix_rd_shipping_method_is_collection('local_pickup'))->toBeTrue();
    expect(matrix_rd_shipping_method_is_collection('local_pickup_plus'))->toBeTrue();
    expect(matrix_rd_shipping_method_is_collection('flat_rate'))->toBeFalse();
    expect(matrix_rd_shipping_method_is_collection(''))->toBeFalse();
});

test('zone lookup uses WooCommerce instance_id key', function () {
    $file = dirname(__DIR__, 2) . '/inc/helpers/admin-order-shipping-zone.php';
    $contents = file_get_contents($file);

    expect($contents)->toContain("WC_Shipping_Zones::get_zone_by('instance_id'");
    expect($contents)->not->toContain("get_zone_by('instance'");
});

test('theme registers shipping zone column on both CPT and HPOS order lists', function () {
    $file = dirname(__DIR__, 2) . '/inc/rolling-donut-woocommerce.php';

    expect(is_readable($file))->toBeTrue();

    $contents = file_get_contents($file);

    expect($contents)->toContain("add_filter('manage_edit-shop_order_columns', 'matrix_rd_add_order_shipping_zone_column', 20)");
    expect($contents)->toContain("add_action('manage_shop_order_posts_custom_column', 'matrix_rd_render_order_shipping_zone_column', 10, 2)");
    expect($contents)->toContain("add_filter('woocommerce_shop_order_list_table_columns', 'matrix_rd_add_order_shipping_zone_column', 20)");
    expect($contents)->toContain("add_action('woocommerce_shop_order_list_table_custom_column', 'matrix_rd_render_order_shipping_zone_column', 10, 2)");
});
