<?php
/**
 * Packing slip prints driver / staff notes and hides Woo email-log noise.
 */

if (! defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

require_once __DIR__ . '/../../inc/helpers/packing-slip-notes.php';

test('customer notes including driver notes are visible on the packing slip', function () {
    $note = (object) [
        'content'       => 'Leave with reception',
        'customer_note' => 1,
    ];

    expect(matrix_rd_packing_slip_note_is_visible($note))->toBeTrue();
});

test('private staff notes are visible on the packing slip', function () {
    $note = (object) [
        'content'       => 'PRIVATE-STAFF-ABC123',
        'customer_note' => 0,
    ];

    expect(matrix_rd_packing_slip_note_is_visible($note))->toBeTrue();
});

test('Woo email-log notes stay off the packing slip', function () {
    $note = (object) [
        'content'       => 'Email "Processing order" sent.',
        'customer_note' => 0,
    ];

    expect(matrix_rd_packing_slip_note_is_visible($note))->toBeFalse();
});

test('status-change and Stripe charge notes stay off the packing slip', function () {
    expect(matrix_rd_packing_slip_note_is_visible((object) [
        'content'       => 'Order status changed from Pending to Processing.',
        'customer_note' => 0,
    ]))->toBeFalse();

    expect(matrix_rd_packing_slip_note_is_visible((object) [
        'content'       => 'Stripe charge abc123 succeeded. Charge ID ch_123.',
        'customer_note' => 0,
    ]))->toBeFalse();
});
