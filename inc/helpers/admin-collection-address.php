<?php
/**
 * Admin: collection orders should show the pickup shop, not the customer
 * home address. Iconic's Deliveries "Ship to" column and the order edit
 * Shipping box both read WooCommerce's formatted shipping address, which
 * is still the billing/home address for local pickup.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param mixed $order WC_Order when WooCommerce is loaded.
 */
function matrix_rd_order_is_collection($order): bool
{
    if (!is_object($order) || !method_exists($order, 'get_shipping_methods')) {
        return false;
    }

    $methods = $order->get_shipping_methods();
    if (!is_array($methods) || $methods === []) {
        return false;
    }

    foreach ($methods as $method) {
        $method_id = is_object($method) && method_exists($method, 'get_method_id')
            ? (string) $method->get_method_id()
            : (string) ($method['method_id'] ?? '');

        if (function_exists('matrix_rd_shipping_method_is_collection')) {
            if (matrix_rd_shipping_method_is_collection($method_id)) {
                return true;
            }
            continue;
        }

        if ($method_id !== '' && str_contains($method_id, 'local_pickup')) {
            return true;
        }
    }

    return false;
}

/**
 * Pickup shop name stored on the local-pickup shipping item (e.g. PAV).
 *
 * @param mixed $order WC_Order when WooCommerce is loaded.
 */
function matrix_rd_get_order_pickup_location_label($order): string
{
    if (!is_object($order) || !method_exists($order, 'get_shipping_methods')) {
        return '';
    }

    foreach ($order->get_shipping_methods() as $item) {
        $method_id = is_object($item) && method_exists($item, 'get_method_id')
            ? (string) $item->get_method_id()
            : (string) ($item['method_id'] ?? '');

        $is_collection = function_exists('matrix_rd_shipping_method_is_collection')
            ? matrix_rd_shipping_method_is_collection($method_id)
            : ($method_id !== '' && str_contains($method_id, 'local_pickup'));

        if (!$is_collection) {
            continue;
        }

        $name = '';
        $location_id = 0;

        if (is_object($item) && method_exists($item, 'get_meta')) {
            $name = trim((string) $item->get_meta('_pickup_location_name'));
            $location_id = (int) $item->get_meta('_pickup_location_id');
        } elseif (is_array($item)) {
            $name = trim((string) ($item['_pickup_location_name'] ?? $item['pickup_location_name'] ?? ''));
            $location_id = (int) ($item['_pickup_location_id'] ?? $item['pickup_location_id'] ?? 0);
        }

        if ($name === '' && $location_id > 0 && function_exists('get_post')) {
            $post = get_post($location_id);
            if (is_object($post) && isset($post->post_title)) {
                $name = trim((string) $post->post_title);
            }
        }

        if ($name !== '') {
            return $name;
        }
    }

    return '';
}

function matrix_rd_admin_request_key(string $key): string
{
    if (!isset($_GET[$key])) {
        return '';
    }

    $value = (string) $_GET[$key];

    return function_exists('wp_unslash') ? (string) wp_unslash($value) : $value;
}

function matrix_rd_is_admin_shop_order_edit_request(): bool
{
    $action = matrix_rd_admin_request_key('action');
    $page = function_exists('sanitize_key')
        ? sanitize_key(matrix_rd_admin_request_key('page'))
        : strtolower(matrix_rd_admin_request_key('page'));

    if ($page === 'wc-orders' && $action === 'edit') {
        return true;
    }

    if ($action !== 'edit') {
        return false;
    }

    $pagenow = isset($GLOBALS['pagenow']) ? (string) $GLOBALS['pagenow'] : '';
    if ($pagenow === 'post.php') {
        return true;
    }

    $script = isset($_SERVER['PHP_SELF']) ? (string) $_SERVER['PHP_SELF'] : '';

    return $script !== '' && substr($script, -8) === 'post.php';
}

function matrix_rd_is_admin_collection_address_screen(): bool
{
    if (array_key_exists('matrix_rd_collection_address_screen_override', $GLOBALS)) {
        return (bool) $GLOBALS['matrix_rd_collection_address_screen_override'];
    }

    if (function_exists('wp_doing_ajax') && wp_doing_ajax()) {
        return false;
    }

    if (!function_exists('is_admin') || !is_admin()) {
        return false;
    }

    $page = function_exists('sanitize_key')
        ? sanitize_key(matrix_rd_admin_request_key('page'))
        : strtolower(matrix_rd_admin_request_key('page'));

    if ($page === 'jckwds-deliveries') {
        return true;
    }

    if (matrix_rd_is_admin_shop_order_edit_request()) {
        return true;
    }

    if (function_exists('get_current_screen')) {
        $screen = get_current_screen();
        if (is_object($screen) && isset($screen->id)) {
            if ($screen->id === 'woocommerce_page_jckwds-deliveries' || $screen->id === 'shop_order') {
                return true;
            }
            $action = matrix_rd_admin_request_key('action');
            if ($screen->id === 'woocommerce_page_wc-orders') {
                return $action === 'edit';
            }
        }
    }

    return false;
}

/**
 * @param mixed $address Formatted HTML address.
 * @param mixed $order
 * @return mixed
 */
function matrix_rd_admin_collection_formatted_shipping_address($address, $order)
{
    if (!matrix_rd_is_admin_collection_address_screen() || !matrix_rd_order_is_collection($order)) {
        return $address;
    }

    $label = matrix_rd_get_order_pickup_location_label($order);

    return $label !== '' ? esc_html($label) : '';
}

/**
 * @param mixed $url
 * @param mixed $order
 * @return mixed
 */
function matrix_rd_admin_collection_shipping_map_url($url, $order)
{
    if (!matrix_rd_is_admin_collection_address_screen() || !matrix_rd_order_is_collection($order)) {
        return $url;
    }

    return '';
}

/**
 * Hide extra shipping fields (phone, etc.) so collection orders do not look
 * like a delivery on the order edit screen.
 *
 * @param mixed $fields
 * @param mixed $order
 * @return mixed
 */
function matrix_rd_hide_admin_shipping_fields_for_collection($fields, $order = null)
{
    if (!is_array($fields) || !matrix_rd_order_is_collection($order)) {
        return $fields;
    }

    foreach ($fields as $key => $field) {
        if (!is_array($field)) {
            continue;
        }
        $fields[$key]['show'] = false;
    }

    return $fields;
}

/**
 * Order edit: rename the Shipping box and show the pickup shop.
 *
 * @param mixed $order
 */
function matrix_rd_render_admin_order_collection_box($order): void
{
    if (!matrix_rd_order_is_collection($order)) {
        return;
    }

    $label = matrix_rd_get_order_pickup_location_label($order);
    if ($label === '') {
        $label = function_exists('__')
            ? __('Free Collection', 'matrix-starter')
            : 'Free Collection';
    }

    $heading = function_exists('__')
        ? __('Collection from', 'matrix-starter')
        : 'Collection from';
    ?>
    <p class="rd-admin-collection-location">
        <strong><?php echo esc_html($heading); ?>:</strong>
        <?php echo esc_html($label); ?>
    </p>
    <style>
        .order_data_column_shipping .address > p:not(.order_note):not(.none_set) {
            display: none;
        }
        .order_data_column_shipping .rd-admin-eircode {
            display: none;
        }
        .order_data_column_shipping .rd-admin-collection-location {
            margin: 0.5em 0 0;
        }
    </style>
    <script>
        (function () {
            var col = document.querySelector('.order_data_column_shipping');
            if (!col) {
                return;
            }
            var heading = col.querySelector('h3');
            if (!heading) {
                return;
            }
            heading.childNodes.forEach(function (node) {
                if (node.nodeType === 3 && node.textContent.indexOf('Shipping') !== -1) {
                    node.textContent = node.textContent.replace('Shipping', 'Collection');
                }
            });
        })();
    </script>
    <?php
}
