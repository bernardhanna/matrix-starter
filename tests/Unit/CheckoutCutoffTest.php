<?php
/**
 * 5pm cutoff copy used on cart and checkout.
 */

if (! defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

if (! function_exists('__')) {
    function __($text, $domain = 'default')
    {
        return $text;
    }
}

require_once __DIR__ . '/../../inc/rolling-donut-checkout-notices.php';

test('checkout notices include a 5pm cutoff for delivery and collection', function () {
    $notices = matrix_rd_checkout_notices();

    expect($notices['cutoff_5pm']['label'])->toContain('5pm');
    expect($notices['cutoff_5pm']['collection'])->toContain('5pm');
});
