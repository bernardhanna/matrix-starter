<?php
/**
 * Collection orders show the pickup shop, not the customer home address.
 */

if (! defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

if (! function_exists('esc_html')) {
    function esc_html($text) {
        return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
    }
}

require_once dirname(__DIR__, 2) . '/inc/helpers/admin-order-shipping-zone.php';
require_once dirname(__DIR__, 2) . '/inc/helpers/admin-collection-address.php';

function matrix_rd_test_fake_collection_order(string $method_id, string $location_name): object
{
    $item = new class($method_id, $location_name) {
        public function __construct(private string $method_id, private string $location_name)
        {
        }

        public function get_method_id(): string
        {
            return $this->method_id;
        }

        public function get_meta(string $key): string
        {
            return $key === '_pickup_location_name' ? $this->location_name : '';
        }
    };

    return new class($item) {
        public function __construct(private object $item)
        {
        }

        public function get_shipping_methods(): array
        {
            return [$this->item];
        }
    };
}

test('local pickup orders are collection and expose the shop name', function () {
    $order = matrix_rd_test_fake_collection_order('local_pickup_plus', 'PAV');

    expect(matrix_rd_order_is_collection($order))->toBeTrue();
    expect(matrix_rd_get_order_pickup_location_label($order))->toBe('PAV');
    expect(matrix_rd_collection_admin_address_label($order))->toBe('PAV');
});

test('delivery orders keep their shipping address on admin screens', function () {
    $order = matrix_rd_test_fake_collection_order('flat_rate', '');

    expect(matrix_rd_order_is_collection($order))->toBeFalse();
    expect(matrix_rd_get_order_pickup_location_label($order))->toBe('');
});

test('woocommerce three-arg formatted address filter still receives the order', function () {
    $GLOBALS['matrix_rd_collection_address_screen_override'] = true;

    $order = matrix_rd_test_fake_collection_order('local_pickup_plus', 'PAV');
    $home = 'Elaine Dunne, 17 Greenwood Way';

    expect(matrix_rd_admin_collection_formatted_shipping_address($home, ['address_1' => '17 Greenwood Way'], $order))
        ->toBe('PAV');

    unset($GLOBALS['matrix_rd_collection_address_screen_override']);
});

test('deliveries tab replaces collection shipping with N/A: Collection', function () {
    $GLOBALS['matrix_rd_collection_address_screen_override'] = true;

    $order = matrix_rd_test_fake_collection_order('local_pickup_plus', 'PAV');
    $home = 'Mary Flanagan, 19 Orchard Drive, Stamullen';

    expect(matrix_rd_should_replace_admin_collection_shipping($order))->toBeTrue();
    expect(matrix_rd_admin_collection_formatted_shipping_address($home, $order))->toBe('PAV');
    expect(matrix_rd_admin_collection_shipping_map_url('https://maps.google.com/?q=Stamullen', $order))->toBe('');

    unset($GLOBALS['matrix_rd_collection_address_screen_override']);
});

test('storefront and packing slip keep the stored shipping address', function () {
    $GLOBALS['matrix_rd_collection_address_screen_override'] = false;

    $order = matrix_rd_test_fake_collection_order('local_pickup_plus', 'PAV');
    $home = 'Mary Flanagan, 19 Orchard Drive, Stamullen';

    expect(matrix_rd_is_admin_collection_address_screen())->toBeFalse();
    expect(matrix_rd_should_replace_admin_collection_shipping($order))->toBeFalse();
    expect(matrix_rd_admin_collection_formatted_shipping_address($home, $order))->toBe($home);

    unset($GLOBALS['matrix_rd_collection_address_screen_override']);
});

test('classic post.php order edit is treated as an admin collection address screen', function () {
    $GLOBALS['pagenow'] = 'post.php';
    $_GET['post'] = '55998';
    $_GET['action'] = 'edit';

    expect(matrix_rd_is_admin_shop_order_edit_request())->toBeTrue();

    unset($GLOBALS['pagenow'], $_GET['post'], $_GET['action']);
});

test('order edit hides extra shipping fields for collection', function () {
    $order = matrix_rd_test_fake_collection_order('local_pickup_plus', 'Rolling Donut Pavilions');
    $fields = matrix_rd_hide_admin_shipping_fields_for_collection([
        'phone' => ['label' => 'Phone', 'show' => true],
        'city' => ['label' => 'City', 'show' => false],
    ], $order);

    expect($fields['phone']['show'])->toBeFalse();
    expect($fields['city']['show'])->toBeFalse();
});

test('theme registers collection address filters for admin screens', function () {
    $woo = dirname(__DIR__, 2) . '/inc/rolling-donut-woocommerce.php';
    $checkout = dirname(__DIR__, 2) . '/inc/rolling-donut-custom-checkout.php';
    $contents = file_get_contents($woo);

    expect($contents)->toContain('helpers/admin-collection-address.php');
    expect($contents)->toContain("add_filter('woocommerce_order_get_formatted_shipping_address', 'matrix_rd_admin_collection_formatted_shipping_address', 20, 3)");
    expect($contents)->toContain("add_action('woocommerce_admin_order_data_after_shipping_address', 'matrix_rd_render_admin_order_collection_editor'");
    expect($contents)->toContain("add_action('woocommerce_process_shop_order_meta', 'matrix_rd_admin_save_collection_pickup_location'");
    expect(file_get_contents($checkout))->toContain('matrix_rd_order_is_collection');
});
