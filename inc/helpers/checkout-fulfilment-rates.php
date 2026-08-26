<?php
/**
 * Keep Delivery and Collection available on the checkout method step.
 *
 * Local Pickup Plus can strip non-pickup rates once Collection is chosen (and
 * per-order packaging can leave only the pickup package). The method radios
 * are re-rendered from those packages, so Change would otherwise show
 * Collection alone. Snapshot the last full rate list and merge it back.
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Whether a WooCommerce shipping rate ID is collection / local pickup.
 */
function matrix_rd_checkout_rate_id_is_pickup(string $rate_id): bool
{
    return $rate_id !== '' && false !== strpos($rate_id, 'local_pickup');
}

/**
 * Which fulfilment kinds are present in a rates map keyed by rate ID.
 *
 * @param array<string, mixed> $rates Package rates.
 *
 * @return array{pickup: bool, delivery: bool}
 */
function matrix_rd_checkout_fulfilment_kinds(array $rates): array
{
    $kinds = ['pickup' => false, 'delivery' => false];

    foreach (array_keys($rates) as $rate_id) {
        if (matrix_rd_checkout_rate_id_is_pickup((string) $rate_id)) {
            $kinds['pickup'] = true;
        } else {
            $kinds['delivery'] = true;
        }
    }

    return $kinds;
}

/**
 * @param array<string, mixed> $rates Package rates.
 */
function matrix_rd_checkout_has_both_fulfilment_kinds(array $rates): bool
{
    $kinds = matrix_rd_checkout_fulfilment_kinds($rates);

    return $kinds['pickup'] && $kinds['delivery'];
}

/**
 * Session/memory store for the last rate list that included both options.
 *
 * @param array<string, mixed>|null $rates Rates to store, or null to read.
 * @param bool                      $reset Clear the store.
 *
 * @return array<string, mixed>
 */
function matrix_rd_checkout_fulfilment_rate_store(?array $rates = null, bool $reset = false): array
{
    static $memory = [];

    if ($reset) {
        $memory = [];

        if (function_exists('WC') && WC() && isset(WC()->session) && WC()->session) {
            WC()->session->set('rd_fulfilment_choice_rates', []);
        }

        return [];
    }

    if (function_exists('WC') && WC() && isset(WC()->session) && WC()->session) {
        if ($rates !== null) {
            WC()->session->set('rd_fulfilment_choice_rates', $rates);
        }

        $stored = WC()->session->get('rd_fulfilment_choice_rates', []);

        return is_array($stored) ? $stored : [];
    }

    if ($rates !== null) {
        $memory = $rates;
    }

    return $memory;
}

/**
 * Re-attach missing delivery or collection rates from a previously complete list.
 *
 * @param array<string, mixed> $current_rates  Rates after LPP / zone filtering.
 * @param array<string, mixed> $snapshot_rates Last list that had both options.
 *
 * @return array<string, mixed>
 */
function matrix_rd_checkout_restore_fulfilment_rates(array $current_rates, array $snapshot_rates): array
{
    if (matrix_rd_checkout_has_both_fulfilment_kinds($current_rates) || $snapshot_rates === []) {
        return $current_rates;
    }

    $kinds = matrix_rd_checkout_fulfilment_kinds($current_rates);

    foreach ($snapshot_rates as $rate_id => $rate) {
        $id = (string) $rate_id;

        if (isset($current_rates[$id])) {
            continue;
        }

        $is_pickup = matrix_rd_checkout_rate_id_is_pickup($id);

        if ($is_pickup && empty($kinds['pickup'])) {
            $current_rates[$id] = $rate;
            $kinds['pickup'] = true;
        } elseif (! $is_pickup && empty($kinds['delivery'])) {
            $current_rates[$id] = $rate;
            $kinds['delivery'] = true;
        }
    }

    return $current_rates;
}

/**
 * Snapshot a complete method list; restore from it when a later calc drops one.
 *
 * @param array<string, mixed> $rates Package rates.
 *
 * @return array<string, mixed>
 */
function matrix_rd_checkout_remember_and_restore_fulfilment_rates(array $rates): array
{
    if (matrix_rd_checkout_has_both_fulfilment_kinds($rates)) {
        matrix_rd_checkout_fulfilment_rate_store($rates);

        return $rates;
    }

    return matrix_rd_checkout_restore_fulfilment_rates(
        $rates,
        matrix_rd_checkout_fulfilment_rate_store()
    );
}

/**
 * Collapse per-order packages to one list without dropping sibling rates.
 *
 * LPP may emit a pickup package first and a shipping package second. Taking
 * only the first package (reset) is what left Change showing Collection alone.
 *
 * @param array<int|string, array<string, mixed>> $packages Shipping packages.
 *
 * @return array<int, array<string, mixed>>
 */
function matrix_rd_checkout_merge_packages_for_method_choice(array $packages): array
{
    if (count($packages) <= 1) {
        return array_values($packages);
    }

    $merged = reset($packages);

    if (! is_array($merged)) {
        return array_values($packages);
    }

    if (! isset($merged['rates']) || ! is_array($merged['rates'])) {
        $merged['rates'] = [];
    }

    foreach ($packages as $package) {
        if (empty($package['rates']) || ! is_array($package['rates'])) {
            continue;
        }

        foreach ($package['rates'] as $rate_id => $rate) {
            if (! isset($merged['rates'][$rate_id])) {
                $merged['rates'][$rate_id] = $rate;
            }
        }
    }

    return [$merged];
}

/**
 * Whether a rate should remain after the Dublin destination filter.
 *
 * Checkout always keeps Delivery (flat_rate) so the method step can offer both
 * options before an address is known. Cart still hides delivery outside Dublin.
 */
function matrix_rd_checkout_should_keep_package_rate(string $rate_id, bool $is_dublin_address, bool $is_checkout): bool
{
    if (false !== strpos($rate_id, 'local_pickup') || false !== strpos($rate_id, 'free_shipping')) {
        return true;
    }

    if (false !== strpos($rate_id, 'flat_rate')) {
        return $is_dublin_address || $is_checkout;
    }

    return false;
}
