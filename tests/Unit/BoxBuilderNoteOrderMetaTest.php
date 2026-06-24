<?php
/**
 * Unit tests for the box-builder customer note flowing onto the order line item.
 *
 * The checkout "Order Details" accordion lets a customer edit the box's
 * "Note to customer". That note is stored on the cart line as `special_requests`
 * and written to the order line item by RD_Box_Builder_Addons::add_order_meta()
 * under the "Note to customer" key. The packing slip renders item meta via
 * wc_display_item_meta(), so once the note is written under that key it appears
 * on the slip. These tests guard that write — the load-bearing step — without a
 * full WordPress/WooCommerce bootstrap, matching the dependency-free style of
 * BoxBuilderIntegrityTest.
 */

if (! defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

// Minimal WP shims used by add_order_meta(). The real functions exist at runtime;
// here we only need predictable, side-effect-free stand-ins.
if (! function_exists('__')) {
    function __($text, $domain = 'default')
    {
        return $text;
    }
}
if (! function_exists('sanitize_text_field')) {
    function sanitize_text_field($str)
    {
        return is_string($str) ? trim(preg_replace('/[\r\n\t ]+/', ' ', $str)) : $str;
    }
}
if (! function_exists('esc_url_raw')) {
    function esc_url_raw($url)
    {
        return $url;
    }
}

require_once __DIR__ . '/../../../../plugins/rd-box-builder/includes/class-addons.php';

/**
 * A tiny stand-in for a WC_Order_Item that records add_meta_data() calls the same
 * way WooCommerce does, including the "unique" flag rd-box-builder relies on to
 * avoid a second plugin double-writing the same key.
 */
class RD_BB_Fake_Order_Item
{
    /** @var array<int, array{key: string, value: mixed}> */
    public array $meta = array();

    public function add_meta_data($key, $value, $unique = false): void
    {
        if ($unique) {
            foreach ($this->meta as $existing) {
                if ($existing['key'] === $key) {
                    return;
                }
            }
        }
        $this->meta[] = array('key' => $key, 'value' => $value);
    }

    public function get_meta($key)
    {
        foreach ($this->meta as $existing) {
            if ($existing['key'] === $key) {
                return $existing['value'];
            }
        }

        return '';
    }

    public function count_meta($key): int
    {
        $count = 0;
        foreach ($this->meta as $existing) {
            if ($existing['key'] === $key) {
                $count++;
            }
        }

        return $count;
    }
}

test('a box note is written to the order line item as "Note to customer"', function () {
    $item = new RD_BB_Fake_Order_Item();

    RD_Box_Builder_Addons::add_order_meta($item, 'cart-key', array(
        'special_requests' => 'Happy birthday Sam!',
    ), null);

    expect($item->get_meta('Note to customer'))->toBe('Happy birthday Sam!');
});

test('an edited note is the value that reaches the order (and packing slip)', function () {
    // Mirrors the customer editing the note in the checkout accordion: the new
    // value — not the original — is what must land on the order line item, which
    // is what wc_display_item_meta() prints on the packing slip.
    $item = new RD_BB_Fake_Order_Item();

    RD_Box_Builder_Addons::add_order_meta($item, 'cart-key', array(
        'special_requests' => 'Updated: please leave at the back door',
    ), null);

    expect($item->get_meta('Note to customer'))->toBe('Updated: please leave at the back door');
});

test('the note is written exactly once (no duplicate meta on the slip)', function () {
    $item = new RD_BB_Fake_Order_Item();

    RD_Box_Builder_Addons::add_order_meta($item, 'cart-key', array(
        'special_requests' => 'One note only',
    ), null);

    expect($item->count_meta('Note to customer'))->toBe(1);
});

test('a box with no note writes no note meta', function () {
    $item = new RD_BB_Fake_Order_Item();

    RD_Box_Builder_Addons::add_order_meta($item, 'cart-key', array(), null);

    expect($item->get_meta('Note to customer'))->toBe('');
});
