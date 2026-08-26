<?php
/**
 * Unit tests for hiding / dropping zero-qty bundled flavours.
 *
 * Box-builder catalogues store every midi/large flavour on the parent bundle,
 * with unused ones at qty 0 so the picker has the full list. WPC copies that
 * full string onto the order as `_woosb_ids` and then prints every entry on
 * order details — including "0 × Chocolate Praline". These tests lock down the
 * two pure transforms: strip zeros from a woosb_ids payload, and rebuild the
 * "Bundled products" HTML without those rows.
 */

if (! defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

if (! function_exists('esc_html')) {
    function esc_html($text)
    {
        return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
    }
}

require_once __DIR__ . '/../../../../plugins/rd-box-builder/includes/class-zero-qty.php';

/** The `_woosb_ids` value stored on order 48163 (Special Occasions midi). */
function rd_bb_zero_qty_order_ids(): string
{
    return '2072/8ab1/2,2113/af38/2,2030/58e5/2,2207/5beb/0,1908/b6d6/0,1924/379a/2,41272/f24a/0,1912/9432/2,1904/1af1/2,1896/d4fe/2,1916/54eb/2,1900/a84e/2,2057/0753/2,2130/5ba4/0,1992/fba3/0,2117/971c/0,2033/240e/0,2010/99b8/0,1940/53af/0,1956/e648/0,1936/2925/0,1932/6686/0,1928/7888/0,2053/6859/0';
}

test('order 48163 woosb_ids drops unused flavours and keeps the mix of 10', function () {
    $stripped = RD_Box_Builder_Zero_Qty::strip_ids(rd_bb_zero_qty_order_ids());
    $segments = explode(',', $stripped);

    expect($segments)->toHaveCount(10);
    expect($stripped)->toContain('2072/8ab1/2');
    expect($stripped)->toContain('2057/0753/2');
    expect($stripped)->not->toContain('2207/5beb/0');
    foreach ($segments as $segment) {
        $parts = explode('/', $segment);
        expect((float) ($parts[2] ?? 0))->toBeGreaterThan(0);
    }
});

test('JS-format ids with empty attrs drop qty 0 and keep the rest intact', function () {
    $ids = '2072/8ab1/2/,2207/5beb/0/,1924/379a/2/%7B%7D';

    expect(RD_Box_Builder_Zero_Qty::strip_ids($ids))->toBe('2072/8ab1/2/,1924/379a/2/%7B%7D');
});

test('legacy id/qty strings drop zeros', function () {
    expect(RD_Box_Builder_Zero_Qty::strip_ids('2072/2,2207/0,1924/2'))->toBe('2072/2,1924/2');
});

test('array-format woosb_ids drop qty 0 entries and keep keys', function () {
    $ids = array(
        '8ab1' => array('id' => '2072', 'qty' => '2'),
        '5beb' => array('id' => '2207', 'qty' => '0'),
        '379a' => array('id' => '1924', 'qty' => 2),
    );

    expect(RD_Box_Builder_Zero_Qty::strip_ids($ids))->toBe(array(
        '8ab1' => array('id' => '2072', 'qty' => '2'),
        '379a' => array('id' => '1924', 'qty' => 2),
    ));
});

test('empty and already-clean payloads are unchanged', function () {
    expect(RD_Box_Builder_Zero_Qty::strip_ids(''))->toBe('');
    expect(RD_Box_Builder_Zero_Qty::strip_ids('2072/8ab1/2'))->toBe('2072/8ab1/2');
    expect(RD_Box_Builder_Zero_Qty::strip_ids(array()))->toBe(array());
});

test('bundled-products list HTML omits 0 × rows', function () {
    $items = array(
        array('id' => 2072, 'qty' => 2),
        array('id' => 2207, 'qty' => 0),
        array('id' => 1924, 'qty' => 2),
    );
    $html = '<ul><li>2 × Apple Crumble – midi</li><li>0 × Chocolate Praline – midi</li><li>2 × Cookies & Cream – midi</li></ul>';

    $out = RD_Box_Builder_Zero_Qty::filter_bundled_names($html, $items, static function (int $id): string {
        return array(
            2072 => 'Apple Crumble – midi',
            2207 => 'Chocolate Praline – midi',
            1924 => 'Cookies & Cream – midi',
        )[$id] ?? '';
    });

    expect($out)->not->toContain('0 ×');
    expect($out)->not->toContain('Chocolate Praline');
    expect($out)->toContain('2 × Apple Crumble – midi');
    expect($out)->toContain('2 × Cookies &amp; Cream – midi');
});

test('bundled-products one-line HTML does not leave empty semicolon slots', function () {
    $items = array(
        array('id' => 2072, 'qty' => 2),
        array('id' => 2207, 'qty' => 0),
        array('id' => 1924, 'qty' => 2),
    );
    $html = '2 × Apple; 0 × Chocolate Praline; 2 × Cookies';

    $out = RD_Box_Builder_Zero_Qty::filter_bundled_names($html, $items, static function (int $id): string {
        return array(2072 => 'Apple', 2207 => 'Chocolate Praline', 1924 => 'Cookies')[$id] ?? '';
    });

    expect($out)->toBe('2 × Apple; 2 × Cookies');
});
