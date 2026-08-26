<?php
/**
 * Admin order screen has a dedicated Eircode field staff can save.
 */

test('admin eircode field uses rd_custom_shipping_eircode', function () {
    $file = dirname(__DIR__, 2) . '/inc/rolling-donut-custom-checkout.php';

    expect(is_readable($file))->toBeTrue();

    $contents = file_get_contents($file);

    expect($contents)->toContain('id="rd_custom_shipping_eircode"');
    expect($contents)->toContain('rd-admin-eircode');
    expect($contents)->toContain('function save_custom_eircode_field_admin');
    expect($contents)->toContain('_custom_shipping_eircode');
});
