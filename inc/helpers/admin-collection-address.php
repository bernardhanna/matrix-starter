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

function matrix_rd_is_pdf_document_request(): bool
{
    if (function_exists('did_action') && did_action('wpo_wcpdf_before_document')) {
        return true;
    }

    if (function_exists('doing_action') && doing_action('wpo_wcpdf_before_document')) {
        return true;
    }

    $page = function_exists('sanitize_key')
        ? sanitize_key(matrix_rd_admin_request_key('page'))
        : strtolower(matrix_rd_admin_request_key('page'));

    return $page === 'wpo_wcpdf' || str_contains($page, 'wcpdf');
}

function matrix_rd_is_admin_shop_order_edit_request(): bool
{
    $action = matrix_rd_admin_request_key('action');
    $page = function_exists('sanitize_key')
        ? sanitize_key(matrix_rd_admin_request_key('page'))
        : strtolower(matrix_rd_admin_request_key('page'));

    if ($page === 'wc-orders') {
        return $action === 'edit' || $action === '';
    }

    $pagenow = isset($GLOBALS['pagenow']) ? (string) $GLOBALS['pagenow'] : '';
    if ($pagenow === 'post.php' || $pagenow === 'post-new.php') {
        return true;
    }

    $script = isset($_SERVER['PHP_SELF']) ? (string) $_SERVER['PHP_SELF'] : '';

    return $script !== '' && (substr($script, -8) === 'post.php' || str_ends_with($script, 'post-new.php'));
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

function matrix_rd_collection_admin_address_label($order): string
{
    if (function_exists('matrix_rd_get_order_shipping_zone_label')) {
        $label = trim((string) matrix_rd_get_order_shipping_zone_label($order));
        if ($label !== '') {
            return $label;
        }
    }

    return function_exists('__')
        ? __('N/A: Collection', 'matrix-starter')
        : 'N/A: Collection';
}

function matrix_rd_should_replace_admin_collection_shipping($order): bool
{
    if (array_key_exists('matrix_rd_collection_address_screen_override', $GLOBALS)) {
        return (bool) $GLOBALS['matrix_rd_collection_address_screen_override']
            && matrix_rd_order_is_collection($order);
    }

    if (function_exists('wp_doing_ajax') && wp_doing_ajax()) {
        return false;
    }

    if (matrix_rd_is_pdf_document_request()) {
        return false;
    }

    if (!function_exists('is_admin') || !is_admin()) {
        return false;
    }

    return matrix_rd_order_is_collection($order);
}

/**
 * @param mixed $address Formatted HTML address.
 * @param mixed $order
 * @return mixed
 */
function matrix_rd_admin_collection_formatted_shipping_address($address, $order)
{
    if (!matrix_rd_should_replace_admin_collection_shipping($order)) {
        return $address;
    }

    return esc_html(matrix_rd_collection_admin_address_label($order));
}

/**
 * @param mixed $url
 * @param mixed $order
 * @return mixed
 */
function matrix_rd_admin_collection_shipping_map_url($url, $order)
{
    if (!matrix_rd_should_replace_admin_collection_shipping($order)) {
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
 * @param mixed $order
 */
function matrix_rd_admin_current_shop_order()
{
    $id = (int) matrix_rd_admin_request_key('post');
    if ($id <= 0) {
        $id = (int) matrix_rd_admin_request_key('id');
    }

    if ($id <= 0 || !function_exists('wc_get_order')) {
        return null;
    }

    $order = wc_get_order($id);

    return is_object($order) ? $order : null;
}

/**
 * @param mixed $classes
 * @return mixed
 */
function matrix_rd_admin_collection_body_class($classes)
{
    $order = matrix_rd_admin_current_shop_order();
    if (!$order || !matrix_rd_order_is_collection($order)) {
        return $classes;
    }

    return trim((string) $classes . ' rd-order-is-collection');
}

function matrix_rd_admin_collection_order_edit_assets(): void
{
    $order = matrix_rd_admin_current_shop_order();
    if (!$order || !matrix_rd_order_is_collection($order)) {
        return;
    }
    ?>
    <style>
        body.rd-order-is-collection .order_data_column_shipping .rd-admin-eircode {
            display: none !important;
        }
        body.rd-order-is-collection .order_data_column_shipping h3 {
            font-size: 0;
        }
        body.rd-order-is-collection .order_data_column_shipping h3::before {
            content: "Collection";
            font-size: 14px;
            font-weight: 600;
        }
        body.rd-order-is-collection .order_data_column_shipping h3 a {
            font-size: 13px;
            font-weight: 400;
            margin-left: 6px;
        }
    </style>
    <?php
}
