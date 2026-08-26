<?php
/**
 * Unit tests for the box-builder integrity safeguard.
 *
 * These cover the pure reconciliation maths that guarantees a box always contains
 * exactly its configured number of donuts (e.g. a "box of 12" can never end up as
 * 11 on a packing slip), and the parent/child grouping that keeps two separately
 * configured copies of the same box from being pooled into one false mismatch.
 * The functions are dependency-free, so we load just the plugin class and
 * exercise them directly.
 */

if (! defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

require_once __DIR__ . '/../../../../plugins/rd-box-builder/includes/class-integrity.php';

/** Convenience: total donuts after reconciling. */
function rd_bb_reconciled_total(array $quantities, int $required): int
{
    return array_sum(RD_Box_Builder_Integrity::reconcile($quantities, $required));
}

test('a correct box is left untouched', function () {
    $box = ['a' => 4, 'b' => 4, 'c' => 4]; // 12

    expect(RD_Box_Builder_Integrity::reconcile($box, 12))->toBe($box);
});

test('a box of 12 showing only 11 donuts is topped back up to 12', function () {
    // The reported bug: customer chose 12 but a donut went missing somewhere.
    $box = ['a' => 5, 'b' => 3, 'c' => 3]; // 11

    $fixed = RD_Box_Builder_Integrity::reconcile($box, 12);

    expect(array_sum($fixed))->toBe(12);
    // The missing donut is added back to the most-chosen flavour.
    expect($fixed['a'])->toBe(6);
    expect($fixed['b'])->toBe(3);
    expect($fixed['c'])->toBe(3);
});

test('a box short by several donuts is restored to the exact size', function () {
    $box = ['a' => 2, 'b' => 2, 'c' => 2]; // 6, needs 20

    expect(rd_bb_reconciled_total($box, 20))->toBe(20);
});

test('an over-filled box is trimmed back to the exact size', function () {
    $box = ['a' => 8, 'b' => 6, 'c' => 6]; // 20, needs 12

    $fixed = RD_Box_Builder_Integrity::reconcile($box, 12);

    expect(array_sum($fixed))->toBe(12);
    // Nothing goes negative.
    foreach ($fixed as $qty) {
        expect($qty)->toBeGreaterThanOrEqual(0);
    }
});

test('trimming pulls from the largest flavour first, staying balanced', function () {
    $box = ['a' => 10, 'b' => 1, 'c' => 1]; // 12, needs 6

    $fixed = RD_Box_Builder_Integrity::reconcile($box, 6);

    expect(array_sum($fixed))->toBe(6);
    expect($fixed['b'])->toBe(1);
    expect($fixed['c'])->toBe(1);
    expect($fixed['a'])->toBe(4);
});

test('a single-flavour box is topped up on that flavour', function () {
    $box = ['only' => 11];

    expect(RD_Box_Builder_Integrity::reconcile($box, 12))->toBe(['only' => 12]);
});

test('negative quantities are floored at zero before reconciling', function () {
    $box = ['a' => -3, 'b' => 5];

    $fixed = RD_Box_Builder_Integrity::reconcile($box, 12);

    expect(array_sum($fixed))->toBe(12);
    expect($fixed['a'])->toBeGreaterThanOrEqual(0);
});

test('an empty box cannot be reconstructed and stays empty', function () {
    expect(RD_Box_Builder_Integrity::reconcile([], 12))->toBe([]);
});

test('integer keys (order item ids) are preserved', function () {
    $box = [58 => 1, 59 => 1, 60 => 1]; // 3, needs 6

    $fixed = RD_Box_Builder_Integrity::reconcile($box, 6);

    expect(array_keys($fixed))->toBe([58, 59, 60]);
    expect(array_sum($fixed))->toBe(6);
});

/**
 * Build an order-line descriptor for group_box_lines().
 *
 * @param array<string, mixed> $overrides
 * @return array<string, mixed>
 */
function rd_bb_line(array $overrides): array
{
    return array_merge(array(
        'id'                => 0,
        'product_id'        => 0,
        'quantity'          => 1,
        'name'              => '',
        'parent_product_id' => 0,
        'box_size'          => 0,
    ), $overrides);
}

test('two separately configured copies of the same box are each checked on their own', function () {
    // Real order: Midi (box of 20) × 3 mixed flavours, plus Midi (box of 20) × 3
    // all Cookies & Cream. Children follow their parent, as WPC writes them.
    // Combined they look like 120 donuts, but each box is a complete 60.
    $midi = 1001;
    $lines = array(
        rd_bb_line(array('id' => 1, 'product_id' => 50, 'name' => 'Branded Mug', 'quantity' => 1)),
        rd_bb_line(array('id' => 2, 'product_id' => $midi, 'name' => 'Midi Sourdough Donuts', 'quantity' => 3, 'box_size' => 20)),
        rd_bb_line(array('id' => 3, 'product_id' => 201, 'parent_product_id' => $midi, 'quantity' => 6, 'name' => 'Blueberry Cheesecake')),
        rd_bb_line(array('id' => 4, 'product_id' => 202, 'parent_product_id' => $midi, 'quantity' => 6, 'name' => 'Apple Crumble')),
        rd_bb_line(array('id' => 5, 'product_id' => 203, 'parent_product_id' => $midi, 'quantity' => 6, 'name' => 'The Milkybar Kid')),
        rd_bb_line(array('id' => 6, 'product_id' => 204, 'parent_product_id' => $midi, 'quantity' => 6, 'name' => 'Caramelised Biscuit')),
        rd_bb_line(array('id' => 7, 'product_id' => 205, 'parent_product_id' => $midi, 'quantity' => 6, 'name' => 'Cookies & Cream')),
        rd_bb_line(array('id' => 8, 'product_id' => 206, 'parent_product_id' => $midi, 'quantity' => 6, 'name' => 'Red Velvet')),
        rd_bb_line(array('id' => 9, 'product_id' => 207, 'parent_product_id' => $midi, 'quantity' => 6, 'name' => 'Ferrero Rocher')),
        rd_bb_line(array('id' => 10, 'product_id' => 208, 'parent_product_id' => $midi, 'quantity' => 6, 'name' => 'Kinder Bueno')),
        rd_bb_line(array('id' => 11, 'product_id' => 209, 'parent_product_id' => $midi, 'quantity' => 6, 'name' => 'The Dub')),
        rd_bb_line(array('id' => 12, 'product_id' => 210, 'parent_product_id' => $midi, 'quantity' => 6, 'name' => 'Old Fashioned Raspberry Jam')),
        rd_bb_line(array('id' => 13, 'product_id' => $midi, 'name' => 'Midi Sourdough Donuts', 'quantity' => 3, 'box_size' => 20)),
        rd_bb_line(array('id' => 14, 'product_id' => 205, 'parent_product_id' => $midi, 'quantity' => 60, 'name' => 'Cookies & Cream')),
    );

    $groups = RD_Box_Builder_Integrity::group_box_lines($lines);

    expect($groups)->toHaveCount(2);
    expect($groups[0]['actual'])->toBe(60);
    expect($groups[0]['expected'])->toBe(60);
    expect($groups[1]['actual'])->toBe(60);
    expect($groups[1]['expected'])->toBe(60);
    expect($groups[0]['ambiguous'])->toBeFalse();
    expect($groups[1]['ambiguous'])->toBeFalse();
});

test('an under-filled box is still flagged when another copy of the same product is complete', function () {
    $midi = 1001;
    $lines = array(
        rd_bb_line(array('id' => 2, 'product_id' => $midi, 'name' => 'Midi Sourdough Donuts', 'quantity' => 1, 'box_size' => 20)),
        rd_bb_line(array('id' => 3, 'product_id' => 201, 'parent_product_id' => $midi, 'quantity' => 6, 'name' => 'Blueberry')),
        rd_bb_line(array('id' => 13, 'product_id' => $midi, 'name' => 'Midi Sourdough Donuts', 'quantity' => 1, 'box_size' => 20)),
        rd_bb_line(array('id' => 14, 'product_id' => 205, 'parent_product_id' => $midi, 'quantity' => 20, 'name' => 'Cookies & Cream')),
    );

    $groups = RD_Box_Builder_Integrity::group_box_lines($lines);

    expect($groups)->toHaveCount(2);
    expect($groups[0]['actual'])->toBe(6);
    expect($groups[0]['expected'])->toBe(20);
    expect($groups[0]['ambiguous'])->toBeTrue();
    expect($groups[1]['actual'])->toBe(20);
    expect($groups[1]['expected'])->toBe(20);
    expect($groups[1]['ambiguous'])->toBeFalse();
});

test('a single box still groups all of its following children', function () {
    $lines = array(
        rd_bb_line(array('id' => 1, 'product_id' => 100, 'name' => 'Box of 12', 'quantity' => 1, 'box_size' => 12)),
        rd_bb_line(array('id' => 2, 'product_id' => 201, 'parent_product_id' => 100, 'quantity' => 4)),
        rd_bb_line(array('id' => 3, 'product_id' => 202, 'parent_product_id' => 100, 'quantity' => 8)),
    );

    $groups = RD_Box_Builder_Integrity::group_box_lines($lines);

    expect($groups)->toHaveCount(1);
    expect($groups[0]['actual'])->toBe(12);
    expect($groups[0]['expected'])->toBe(12);
    expect($groups[0]['child_ids'])->toBe(array(2, 3));
    expect($groups[0]['ambiguous'])->toBeFalse();
});
