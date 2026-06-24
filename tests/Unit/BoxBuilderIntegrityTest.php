<?php
/**
 * Unit tests for the box-builder integrity safeguard.
 *
 * These cover the pure reconciliation maths that guarantees a box always contains
 * exactly its configured number of donuts (e.g. a "box of 12" can never end up as
 * 11 on a packing slip). The function is dependency-free, so we load just the
 * plugin class and exercise it directly.
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
