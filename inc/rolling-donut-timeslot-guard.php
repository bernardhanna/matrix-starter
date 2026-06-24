<?php
/**
 * Rolling Donut — Iconic delivery-slot guard (pure, dependency-free).
 *
 * The store runs date-only collection/delivery. Iconic's timeslot field can be
 * disabled (timesettings_setup_enable = 0) so no slot UI renders, yet the
 * "timeslot mandatory" switch may still be on — an impossible combination that
 * rejects every order with "Please select a time slot." This helper decides
 * when that requirement should be relaxed. It lives in its own side-effect-free
 * file so it can be unit-tested without booting WordPress.
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Whether Iconic's timeslot field is switched on.
 *
 * @param array<string, mixed> $settings Iconic settings array.
 */
function matrix_rd_checkout_timeslots_enabled(array $settings): bool {
    return ! empty($settings['timesettings_timesettings_setup_enable'])
        && '0' !== (string) $settings['timesettings_timesettings_setup_enable'];
}

/**
 * Whether the mandatory-timeslot requirement should be relaxed for this store.
 *
 * Only relax when the slot field is disabled but still flagged mandatory: that
 * is the impossible-to-satisfy state. When slots are enabled we leave the
 * customer's real selection (and the plugin's validation) untouched.
 *
 * @param array<string, mixed> $settings Iconic settings array.
 */
function matrix_rd_checkout_should_relax_timeslot(array $settings): bool {
    if (matrix_rd_checkout_timeslots_enabled($settings)) {
        return false;
    }

    return ! empty($settings['timesettings_timesettings_setup_mandatory'])
        && '0' !== (string) $settings['timesettings_timesettings_setup_mandatory'];
}
