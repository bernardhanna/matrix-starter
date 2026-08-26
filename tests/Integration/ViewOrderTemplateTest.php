<?php

test('view-order template no longer contains the TEST leftover', function () {
    $template = dirname(__DIR__, 2) . '/woocommerce/myaccount/view-order.php';

    expect(is_readable($template))->toBeTrue();

    $contents = file_get_contents($template);

    expect($contents)->toContain("esc_html_e('Order updates', 'woocommerce')");
    expect($contents)->not->toContain('TEST');
});

test('view-order details templates use the legacy flex layout', function () {
    $details = dirname(__DIR__, 2) . '/woocommerce/order/order-details.php';
    $item    = dirname(__DIR__, 2) . '/woocommerce/order/order-details-item.php';
    $customer = dirname(__DIR__, 2) . '/woocommerce/order/order-details-customer.php';

    expect(is_readable($details))->toBeTrue();
    expect(is_readable($item))->toBeTrue();
    expect(is_readable($customer))->toBeTrue();

    $details_contents = file_get_contents($details);
    $item_contents    = file_get_contents($item);
    $customer_contents = file_get_contents($customer);

    expect($details_contents)->toContain('wc-backward-container');
    expect($details_contents)->toContain('Back to orders');
    expect($details_contents)->toContain('rd-view-order');
    expect($details_contents)->toContain('bg-grey-disabled');
    expect($details_contents)->toContain("esc_html_e('Product', 'woocommerce')");
    expect($details_contents)->not->toContain('woocommerce-order-details__title');
    expect($details_contents)->not->toContain('shop_table order_details');

    expect($item_contents)->not->toContain('<tr');
    expect($item_contents)->toContain('product-quantity');
    expect($item_contents)->toContain('Logo Upload');

    expect($customer_contents)->toContain('Billing address');
    expect($customer_contents)->toContain('Pickup Location');
    expect($customer_contents)->toContain('font-reg420');
    expect($customer_contents)->toContain('matrix_rd_get_order_pickup_display');
});

test('view-order helpers are defined in the theme WooCommerce bootstrap', function () {
    $file = dirname(__DIR__, 2) . '/inc/rolling-donut-woocommerce.php';

    expect(is_readable($file))->toBeTrue();

    $contents = file_get_contents($file);

    expect($contents)->toContain('function matrix_rd_get_order_pickup_display');
    expect($contents)->toContain('function matrix_rd_render_view_order_item_row');
    expect($contents)->toContain('function matrix_rd_format_pickup_address');
});
