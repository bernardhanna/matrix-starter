<?php
/**
 * Packing-slip note visibility (driver / customer / staff vs Woo system logs).
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Whether an order note should print on the packing slip.
 *
 * Customer notes (including the checkout "note to driver") always show.
 * Private staff notes show unless they are Woo system noise (email logs,
 * status changes, Stripe charges, etc.).
 *
 * @param object|array $note Woo order note from wc_get_order_notes().
 */
function matrix_rd_packing_slip_note_is_visible($note): bool
{
    $content = is_object($note) ? (string) ($note->content ?? '') : (string) ($note['content'] ?? $note);
    $is_customer = is_object($note)
        ? (int) ($note->customer_note ?? 0) === 1
        : ! empty($note['customer_note']);

    if ($is_customer) {
        return true;
    }

    $content_lower = strtolower($content);
    $is_email_log  = (strpos($content_lower, 'email sent') !== false)
        || (bool) preg_match('/^email\s+".+"\s+(not\s+)?sent/', $content_lower);

    if ($is_email_log) {
        return false;
    }

    $blocked = array(
        'order status changed',
        'pdf',
        'payment',
        'stripe charge',
        'charge id',
        'error',
        'refunded',
        'manually created',
        'stock',
        'tax',
        'delivery date updated',
    );
    foreach ($blocked as $needle) {
        if (strpos($content_lower, $needle) !== false) {
            return false;
        }
    }

    return $content !== '';
}
