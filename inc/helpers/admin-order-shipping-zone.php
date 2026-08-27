<?php
/**
 * Orders list: show Shipping Zone instead of WooCommerce's default "Ship to".
 *
 * Ported from the live mu-plugin (`woocommerce-custom-functions.php`).
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Swap the "Ship to" column for "Shipping Zone" (append if Ship to is absent).
 *
 * @param array<string, string> $columns
 * @return array<string, string>
 */
function matrix_rd_add_order_shipping_zone_column(array $columns): array
{
    $label = function_exists('__')
        ? __('Shipping Zone', 'matrix-starter')
        : 'Shipping Zone';

    $out = [];
    $inserted = false;

    foreach ($columns as $key => $heading) {
        if ($key === 'shipping_address') {
            $out['order_shipping_zone'] = $label;
            $inserted = true;
            continue;
        }
        $out[$key] = $heading;
    }

    if (!$inserted) {
        $out['order_shipping_zone'] = $label;
    }

    return $out;
}

function matrix_rd_shipping_method_is_collection(string $method_id): bool
{
    return $method_id !== '' && str_contains($method_id, 'local_pickup');
}

/**
 * @param object $order WC_Order when WooCommerce is loaded.
 */
function matrix_rd_get_order_shipping_zone_label($order): string
{
    $fallback = function_exists('__')
        ? __('N/A: Collection', 'matrix-starter')
        : 'N/A: Collection';

    if (!is_object($order) || !method_exists($order, 'get_shipping_methods')) {
        return $fallback;
    }

    $methods = $order->get_shipping_methods();
    if (!is_array($methods) || $methods === []) {
        return $fallback;
    }

    foreach ($methods as $method) {
        $method_id = is_object($method) && method_exists($method, 'get_method_id')
            ? (string) $method->get_method_id()
            : (string) ($method['method_id'] ?? '');
        if (matrix_rd_shipping_method_is_collection($method_id)) {
            return $fallback;
        }
    }

    if (!class_exists('WC_Shipping_Zones')) {
        return $fallback;
    }

    foreach ($methods as $method) {
        $instance_id = is_object($method) && method_exists($method, 'get_instance_id')
            ? (int) $method->get_instance_id()
            : (int) ($method['instance_id'] ?? 0);

        if ($instance_id <= 0) {
            continue;
        }

        $zone = WC_Shipping_Zones::get_zone_by('instance_id', $instance_id);
        if (is_object($zone) && method_exists($zone, 'get_zone_name')) {
            $name = trim((string) $zone->get_zone_name());
            if ($name !== '') {
                return $name;
            }
        }
    }

    if (!method_exists('WC_Shipping_Zones', 'get_zones')) {
        return $fallback;
    }

    $zones = WC_Shipping_Zones::get_zones();
    if (!is_array($zones)) {
        return $fallback;
    }

    foreach ($methods as $method) {
        $method_id = is_object($method) && method_exists($method, 'get_method_id')
            ? (string) $method->get_method_id()
            : (string) ($method['method_id'] ?? '');

        foreach ($zones as $zone) {
            if (!is_array($zone) || empty($zone['shipping_methods']) || empty($zone['zone_name'])) {
                continue;
            }
            foreach ($zone['shipping_methods'] as $zone_method) {
                $zone_method_id = is_object($zone_method) ? (string) ($zone_method->id ?? '') : '';
                if ($method_id !== '' && $zone_method_id === $method_id) {
                    return (string) $zone['zone_name'];
                }
            }
        }
    }

    return $fallback;
}

/**
 * @param string     $column Column id.
 * @param mixed      $order  WC_Order, order id, or unused (legacy uses global $post).
 */
function matrix_rd_render_order_shipping_zone_column($column, $order = null): void
{
    if ($column !== 'order_shipping_zone') {
        return;
    }

    if (!is_object($order) || !method_exists($order, 'get_shipping_methods')) {
        $order_id = is_numeric($order) ? (int) $order : 0;
        if ($order_id <= 0 && function_exists('get_the_ID')) {
            $order_id = (int) get_the_ID();
        }
        $order = ($order_id > 0 && function_exists('wc_get_order')) ? wc_get_order($order_id) : null;
    }

    $label = matrix_rd_get_order_shipping_zone_label($order);
    echo esc_html($label);
}
