<?php
/**
 * Unit tests for the bakery prep-sheet aggregation (MT Future Orders).
 *
 * The bakery uses these numbers to decide how many donuts to make for a given
 * date, so the counts must be exact. A box-builder order is a parent "box" line
 * plus one child line per flavour; the donuts to bake are the flavour/individual
 * lines, never the box container. These tests lock that behaviour down.
 */

if (! defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

require_once __DIR__ . '/../../../../plugins/mt-future-orders/includes/mtfo-summary.php';

/** Build a grouped row as the SQL query would return it. */
function mtfo_row(string $name, int $qty, int $product_id, int $variation_id = 0): array
{
    return array(
        'order_item_name' => $name,
        '_qty'            => (string) $qty,
        '_product_id'     => (string) $product_id,
        '_variation_id'   => (string) $variation_id,
    );
}

/** A box-container detector for a fixed set of box product ids. */
function mtfo_box_detector(array $box_ids): callable
{
    return static function (int $product_id) use ($box_ids): bool {
        return in_array($product_id, $box_ids, true);
    };
}

test('box containers are excluded from the donut count', function () {
    // One midi box (container qty 1) holding 10 flavours x 2 = 20 donuts.
    $items = array(
        mtfo_row('Midi Sourdough Donuts', 1, 1959),
        mtfo_row('Apple Crumble - midi', 2, 2070, 2072),
        mtfo_row('Blueberry Cheesecake - midi', 2, 2111, 2113),
        mtfo_row('Caramelised Biscuit - midi', 2, 2028, 2030),
        mtfo_row('Cookies & Cream - midi', 2, 1922, 1924),
        mtfo_row('Ferrero Rocher - midi', 2, 1910, 1912),
        mtfo_row('Kinder Bueno - midi', 2, 1902, 1904),
        mtfo_row('Old Fashioned Raspberry Jam - midi', 2, 1894, 1896),
        mtfo_row('Red Velvet - midi', 2, 1914, 1916),
        mtfo_row('The Dub - midi', 2, 1898, 1900),
        mtfo_row('The Milkybar Kid - midi', 2, 2055, 2057),
    );

    $summary = mtfo_summarize_items($items, mtfo_box_detector(array(1959)));

    expect($summary['donuts'])->toBe(20); // the actual donuts to bake
    expect($summary['boxes'])->toBe(1);   // one box container
    expect($summary['total'])->toBe(21);  // legacy "all line items" figure
});

test('regression: the live 23/06/2026 sheet totals 92 donuts, 7 boxes', function () {
    // Both 1959 (Midi Sourdough) and 46907 (Football Team Large Sourdough) are
    // woosb box bundles; their contents are the "- midi"/"- large" flavour lines.
    $items = array(
        // midi box contents (10 x 2 = 20)
        mtfo_row('Apple Crumble - midi', 2, 2070),
        mtfo_row('Blueberry Cheesecake - midi', 2, 2111),
        mtfo_row('Caramelised Biscuit - midi', 2, 2028),
        mtfo_row('Cookies & Cream - midi', 2, 1922),
        mtfo_row('Ferrero Rocher - midi', 2, 1910),
        mtfo_row('Kinder Bueno - midi', 2, 1902),
        mtfo_row('Old Fashioned Raspberry Jam - midi', 2, 1894),
        mtfo_row('Red Velvet - midi', 2, 1914),
        mtfo_row('The Dub - midi', 2, 1898),
        mtfo_row('The Milkybar Kid - midi', 2, 2055),
        // large donuts (9 x 6 + 24 = 78)
        mtfo_row('Caramelised Biscuit - large', 6, 2028),
        mtfo_row('Cookies & Cream - large', 6, 1922),
        mtfo_row('Football Team - Large Sourdough', 6, 46907),
        mtfo_row('Kinder Bueno - large', 6, 1902),
        mtfo_row('Mini Nutella and Marshmallow - large', 6, 1995),
        mtfo_row('Old Fashioned Raspberry Jam - large', 6, 1894),
        mtfo_row('The Milkybar Kid - large', 6, 2055),
        mtfo_row('Vanilla Glaze - large', 24, 1962),
        mtfo_row('Vanilla Sprinkles - large', 6, 1990),
        mtfo_row('White Kinder Bueno - large', 6, 2051),
        // midi box container (the "Football Team" line at 46907 above is the
        // large-box container; its contents are the other "- large" lines)
        mtfo_row('Midi Sourdough Donuts', 1, 1959),
    );

    $summary = mtfo_summarize_items($items, mtfo_box_detector(array(1959, 46907)));

    expect($summary['donuts'])->toBe(92); // 20 midi + 72 large flavours
    expect($summary['boxes'])->toBe(7);   // 1 midi box + 6 large boxes
    expect($summary['total'])->toBe(99);
});

test('multiple boxes never inflate the donut count', function () {
    // 3 boxes ordered (container qty 3) holding 60 donuts in total.
    $items = array(
        mtfo_row('Midi Sourdough Donuts', 3, 1959),
        mtfo_row('Apple Crumble - midi', 30, 2070),
        mtfo_row('Red Velvet - midi', 30, 1914),
    );

    $summary = mtfo_summarize_items($items, mtfo_box_detector(array(1959)));

    expect($summary['donuts'])->toBe(60);
    expect($summary['boxes'])->toBe(3);
    expect($summary['total'])->toBe(63);
});

test('individual donuts (no boxes) count fully as donuts', function () {
    $items = array(
        mtfo_row('Vanilla Glaze - large', 12, 1962),
        mtfo_row('Red Velvet - large', 6, 1914),
    );

    $summary = mtfo_summarize_items($items, mtfo_box_detector(array(1959)));

    expect($summary['donuts'])->toBe(18);
    expect($summary['boxes'])->toBe(0);
    expect($summary['total'])->toBe(18);
});

test('per-row quantities are preserved and flagged correctly', function () {
    $items = array(
        mtfo_row('Midi Sourdough Donuts', 1, 1959, 0),
        mtfo_row('Apple Crumble - midi', 2, 2070, 2072),
    );

    $summary = mtfo_summarize_items($items, mtfo_box_detector(array(1959)));

    expect($summary['rows'][0]['is_box'])->toBeTrue();
    expect($summary['rows'][0]['variation_id'])->toBe(0);
    expect($summary['rows'][1]['is_box'])->toBeFalse();
    expect($summary['rows'][1]['qty'])->toBe(2);
    expect($summary['rows'][1]['variation_id'])->toBe(2072);
});

test('an empty sheet totals zero', function () {
    $summary = mtfo_summarize_items(array(), mtfo_box_detector(array(1959)));

    expect($summary['donuts'])->toBe(0);
    expect($summary['boxes'])->toBe(0);
    expect($summary['total'])->toBe(0);
    expect($summary['rows'])->toBe(array());
});

test('missing or negative quantities are floored at zero', function () {
    $items = array(
        mtfo_row('Weird line', -5, 1962),
        array('order_item_name' => 'No qty', '_product_id' => '1962'),
    );

    $summary = mtfo_summarize_items($items, mtfo_box_detector(array(1959)));

    expect($summary['donuts'])->toBe(0);
    expect($summary['total'])->toBe(0);
});
