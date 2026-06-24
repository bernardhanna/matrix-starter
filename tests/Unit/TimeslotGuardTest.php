<?php
/**
 * Unit tests for the Iconic delivery-slot guard.
 *
 * The store runs date-only collection/delivery with the timeslot field disabled.
 * The plugin still had "timeslot mandatory" on, which rejected every order with
 * "Please select a time slot." matrix_rd_checkout_should_relax_timeslot()
 * decides when to drop that impossible requirement; these tests guard that
 * decision without booting WordPress (matching the dependency-free Unit style).
 */

if (! defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

require_once __DIR__ . '/../../inc/rolling-donut-timeslot-guard.php';

test('relaxes the requirement when slots are disabled but still mandatory', function () {
    // The exact live-store combination that blocked checkout.
    $settings = array(
        'timesettings_timesettings_setup_enable'    => '0',
        'timesettings_timesettings_setup_mandatory' => '1',
        'timesettings_timesettings_asap_enable'     => '1',
    );

    expect(matrix_rd_checkout_should_relax_timeslot($settings))->toBeTrue();
});

test('leaves the requirement intact when slots are enabled', function () {
    // Slots are shown, so a real selection exists — never override it.
    $settings = array(
        'timesettings_timesettings_setup_enable'    => '1',
        'timesettings_timesettings_setup_mandatory' => '1',
    );

    expect(matrix_rd_checkout_should_relax_timeslot($settings))->toBeFalse();
    expect(matrix_rd_checkout_timeslots_enabled($settings))->toBeTrue();
});

test('does nothing when slots are disabled and already optional', function () {
    // Nothing to relax: the requirement is already off.
    $settings = array(
        'timesettings_timesettings_setup_enable'    => '0',
        'timesettings_timesettings_setup_mandatory' => '0',
    );

    expect(matrix_rd_checkout_should_relax_timeslot($settings))->toBeFalse();
});

test('treats missing settings as disabled/optional (no relax needed)', function () {
    expect(matrix_rd_checkout_should_relax_timeslot(array()))->toBeFalse();
    expect(matrix_rd_checkout_timeslots_enabled(array()))->toBeFalse();
});
