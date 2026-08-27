<?php
/**
 * WooCommerce → Orders list columns and default Date sort match live.
 */

if (! defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

require_once dirname(__DIR__, 2) . '/inc/helpers/admin-order-list.php';

test('orders list columns match live order and labels', function () {
    $columns = matrix_rd_reorder_shop_order_list_columns([
        'cb'               => '',
        'order_number'     => 'Order',
        'order_date'       => 'Date',
        'order_status'     => 'Status',
        'billing_address'  => 'Billing',
        'order_total'      => 'Total',
        'wc_actions'       => 'Actions',
        'origin'           => 'Origin',
        'jckwds_delivery'  => 'Delivery',
        'order_shipping_zone' => 'Shipping Zone',
    ]);

    expect(array_keys($columns))->toBe([
        'cb',
        'order_number',
        'order_date',
        'order_status',
        'pickup_locations',
        'order_total',
        'jckwds_delivery',
        'origin',
        'order_shipping_zone',
        'billing_address',
        'wc_actions',
    ]);
    expect($columns['pickup_locations'])->toBe('Pickup Locations');
});

test('orders list hides billing and actions, keeps live visible columns', function () {
    $screen = (object) ['id' => 'edit-shop_order'];
    $hidden = matrix_rd_shop_order_list_hidden_columns(['origin'], $screen);

    expect($hidden)->toContain('billing_address');
    expect($hidden)->toContain('wc_actions');
    expect($hidden)->not->toContain('origin');
    expect($hidden)->not->toContain('jckwds_delivery');
    expect($hidden)->not->toContain('order_shipping_zone');
    expect($hidden)->not->toContain('pickup_locations');
});

test('orders list defaults to upcoming delivery date and time', function () {
    expect(matrix_rd_shop_order_list_default_orderby_args([
        'post_type' => 'shop_order',
    ]))->toBe([
        'orderby' => 'jckwds_delivery',
        'order'   => 'asc',
    ]);

    expect(matrix_rd_shop_order_list_default_orderby_args([
        'post_type' => 'shop_order',
        'orderby'   => 'date',
    ]))->toBeNull();

    $sql = matrix_rd_shop_order_list_delivery_orderby_sql('wp_posts', 'rd_wds.ymd', 'rd_wds.ts', 'asc');
    expect($sql)->toContain('rd_wds.ymd ASC');
    expect($sql)->toContain('wp_posts.post_date ASC');
    expect($sql)->toContain('rd_wds.ts ASC');
    expect(matrix_rd_wds_reservations_order_sql('j', 'p'))->toContain('j.date ASC');
    expect(matrix_rd_wds_reservations_order_sql('j', 'p'))->toContain('p.post_date ASC');
});

test('theme registers live orders-list columns and upcoming delivery sort', function () {
    $file = dirname(__DIR__, 2) . '/inc/rolling-donut-woocommerce.php';
    $contents = file_get_contents($file);

    expect($contents)->toContain("add_filter('manage_edit-shop_order_columns', 'matrix_rd_reorder_shop_order_list_columns', 999)");
    expect($contents)->toContain("add_filter('hidden_columns', 'matrix_rd_shop_order_list_hidden_columns', 20, 2)");
    expect($contents)->toContain("add_action('load-edit.php', 'matrix_rd_shop_order_list_redirect_to_delivery_sort')");
    expect($contents)->toContain("add_filter('posts_clauses', 'matrix_rd_shop_order_list_posts_clauses', 20, 2)");
    expect($contents)->toContain("add_filter('iconic_wds_reservations_pre_query', 'matrix_rd_wds_reservations_pre_query', 10, 2)");
});
