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
    $name = matrix_rd_get_order_pickup_location_label($order);
    if ($name !== '') {
        return $name;
    }

    return function_exists('__')
        ? __('N/A: Collection', 'matrix-starter')
        : 'N/A: Collection';
}

/**
 * @param mixed $order
 * @return array{0: int, 1: mixed}
 */
function matrix_rd_order_collection_shipping_item($order): array
{
    if (!is_object($order) || !method_exists($order, 'get_shipping_methods')) {
        return [0, null];
    }

    foreach ($order->get_shipping_methods() as $item_id => $item) {
        $method_id = is_object($item) && method_exists($item, 'get_method_id')
            ? (string) $item->get_method_id()
            : (string) ($item['method_id'] ?? '');

        $is_collection = function_exists('matrix_rd_shipping_method_is_collection')
            ? matrix_rd_shipping_method_is_collection($method_id)
            : ($method_id !== '' && str_contains($method_id, 'local_pickup'));

        if ($is_collection) {
            return [(int) $item_id, $item];
        }
    }

    return [0, null];
}

/**
 * @return list<array{id: string, name: string, address: string}>
 */
function matrix_rd_admin_pickup_location_choices(): array
{
    if (function_exists('matrix_rd_express_checkout_pickup_location_records')) {
        $records = matrix_rd_express_checkout_pickup_location_records();
        if ($records !== []) {
            return $records;
        }
    }

    if (!function_exists('get_posts')) {
        return [];
    }

    $posts = get_posts([
        'post_type'      => 'wc_pickup_location',
        'post_status'    => 'publish',
        'numberposts'    => 80,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'suppress_filters' => true,
    ]);

    $out = [];
    foreach ($posts as $post) {
        if (!is_object($post) || !isset($post->ID, $post->post_title)) {
            continue;
        }
        $out[] = [
            'id'      => (string) $post->ID,
            'name'    => (string) $post->post_title,
            'address' => '',
        ];
    }

    return $out;
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
 * @param mixed $address      Formatted HTML address.
 * @param mixed $raw_address  Raw address array from WooCommerce, or an order in tests.
 * @param mixed $order        WC_Order when WooCommerce calls this filter.
 * @return mixed
 */
function matrix_rd_admin_collection_formatted_shipping_address($address, $raw_address = [], $order = null)
{
    if (!is_object($order) && is_object($raw_address)) {
        $order = $raw_address;
    }

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

    $label = matrix_rd_collection_admin_address_label($order);
    ?>
    <style>
        .order_data_column_shipping .rd-admin-eircode,
        .order_data_column_shipping .edit_address,
        .order_data_column_shipping a.edit_address {
            display: none !important;
        }
        .rd-admin-collection-editor {
            margin-top: 8px;
        }
        .rd-admin-collection-editor select {
            width: 100%;
            max-width: 100%;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var col = document.querySelector('.order_data_column_shipping');
            if (!col) {
                return;
            }
            var heading = col.querySelector('h3');
            if (heading) {
                heading.childNodes.forEach(function (node) {
                    if (node.nodeType === 3 && node.textContent.indexOf('Shipping') !== -1) {
                        node.textContent = node.textContent.replace('Shipping', 'Collection');
                    }
                });
            }
            var address = col.querySelector('.address');
            if (address) {
                address.querySelectorAll('p:not(.order_note)').forEach(function (p) {
                    p.remove();
                });
                var line = document.createElement('p');
                line.className = 'rd-admin-collection-shop';
                line.textContent = <?php echo function_exists('wp_json_encode') ? wp_json_encode($label) : json_encode($label); ?>;
                address.insertBefore(line, address.firstChild);
            }
        });
    </script>
    <?php
}

/**
 * @param mixed $order
 */
function matrix_rd_render_admin_order_collection_editor($order): void
{
    if (!matrix_rd_order_is_collection($order)) {
        return;
    }

    [$item_id, $item] = matrix_rd_order_collection_shipping_item($order);
    $current = 0;
    if (is_object($item) && method_exists($item, 'get_meta')) {
        $current = (int) $item->get_meta('_pickup_location_id');
    }

    $choices = matrix_rd_admin_pickup_location_choices();
    $heading = function_exists('__')
        ? __('Collection location', 'matrix-starter')
        : 'Collection location';
    $help = function_exists('__')
        ? __('Change the shop and click Update to save.', 'matrix-starter')
        : 'Change the shop and click Update to save.';
    ?>
    <div class="rd-admin-collection-editor">
        <p class="form-field form-field-wide">
            <label for="rd_collection_pickup_location_id"><strong><?php echo esc_html($heading); ?></strong></label>
            <input type="hidden" name="rd_collection_shipping_item_id" value="<?php echo (int) $item_id; ?>" />
            <select name="rd_collection_pickup_location_id" id="rd_collection_pickup_location_id">
                <?php foreach ($choices as $choice) : ?>
                    <option value="<?php echo esc_attr((string) $choice['id']); ?>" <?php echo ((int) $choice['id'] === $current) ? 'selected="selected"' : ''; ?>>
                        <?php echo esc_html((string) $choice['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p class="description"><?php echo esc_html($help); ?></p>
    </div>
    <?php
}

/**
 * @param mixed $order_id
 */
function matrix_rd_admin_save_collection_pickup_location($order_id): void
{
    if (!isset($_POST['rd_collection_pickup_location_id'], $_POST['rd_collection_shipping_item_id'])) {
        return;
    }

    $location_id = (int) (function_exists('wp_unslash') ? wp_unslash($_POST['rd_collection_pickup_location_id']) : $_POST['rd_collection_pickup_location_id']);
    $item_id = (int) (function_exists('wp_unslash') ? wp_unslash($_POST['rd_collection_shipping_item_id']) : $_POST['rd_collection_shipping_item_id']);

    if ($location_id <= 0 || $item_id <= 0) {
        return;
    }

    if (!function_exists('wc_local_pickup_plus') || !function_exists('wc_local_pickup_plus_get_pickup_location')) {
        return;
    }

    $location = wc_local_pickup_plus_get_pickup_location($location_id);
    $plugin = wc_local_pickup_plus();
    if (!$location || !$plugin || !method_exists($plugin, 'get_orders_instance')) {
        return;
    }

    $orders = $plugin->get_orders_instance();
    if (!$orders || !method_exists($orders, 'get_order_items_instance')) {
        return;
    }

    $items = $orders->get_order_items_instance();
    if (!$items || !method_exists($items, 'set_order_item_pickup_location')) {
        return;
    }

    $items->set_order_item_pickup_location($item_id, $location);

    if (function_exists('wc_get_order')) {
        $order = wc_get_order((int) $order_id);
        if ($order && method_exists($order, 'get_item')) {
            $item = $order->get_item($item_id);
            if (is_object($item) && method_exists($item, 'save')) {
                $item->save();
            }
        }
    }
}
