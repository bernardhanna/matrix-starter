<?php
/**
 * WooCommerce → Orders list: same columns as live, sorted by upcoming
 * delivery date and time (soonest due first, completed history after).
 *
 * Column order: Order, Date, Status, Pickup Locations, Total, Delivery,
 * Origin, Shipping Zone.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Visible column keys, in live order (checkbox is always first).
 *
 * @return list<string>
 */
function matrix_rd_shop_order_list_visible_column_keys(): array
{
    return [
        'order_number',
        'order_date',
        'order_status',
        'pickup_locations',
        'order_total',
        'jckwds_delivery',
        'origin',
        'order_shipping_zone',
    ];
}

/**
 * Columns that stay registered but hidden (Screen Options), matching live.
 *
 * @return list<string>
 */
function matrix_rd_shop_order_list_hidden_column_keys(): array
{
    return [
        'billing_address',
        'shipping_address',
        'wc_actions',
        'invoice_number_column',
        'invoice_date_column',
        'iconic_wds_subscription_delivery',
    ];
}

/**
 * Rebuild the orders list columns to match live, keeping plugin renderers.
 *
 * @param array<string, string> $columns
 * @return array<string, string>
 */
function matrix_rd_reorder_shop_order_list_columns(array $columns): array
{
    if ($columns === []) {
        return $columns;
    }

    $labels = [
        'pickup_locations'    => 'Pickup Locations',
        'jckwds_delivery'     => 'Delivery',
        'origin'              => 'Origin',
        'order_shipping_zone' => 'Shipping Zone',
    ];

    if (function_exists('__')) {
        $labels['pickup_locations']    = __('Pickup Locations', 'woocommerce-shipping-local-pickup-plus');
        $labels['jckwds_delivery']     = __('Delivery', 'jckwds');
        $labels['origin']              = __('Origin', 'woocommerce');
        $labels['order_shipping_zone'] = __('Shipping Zone', 'matrix-starter');
    }

    foreach ($labels as $key => $label) {
        if (!isset($columns[$key])) {
            $columns[$key] = $label;
        }
    }

    $out = [];
    if (isset($columns['cb'])) {
        $out['cb'] = $columns['cb'];
    }

    foreach (matrix_rd_shop_order_list_visible_column_keys() as $key) {
        if (isset($columns[$key])) {
            $out[$key] = $columns[$key];
        }
    }

    foreach ($columns as $key => $heading) {
        if (!isset($out[$key])) {
            $out[$key] = $heading;
        }
    }

    return $out;
}

/**
 * Hide billing/actions/invoice extras so the table matches live.
 *
 * @param array<int, string>|false $hidden
 * @param mixed                    $screen
 * @return array<int, string>|false
 */
function matrix_rd_shop_order_list_hidden_columns($hidden, $screen)
{
    if (!is_object($screen) || !isset($screen->id)) {
        return $hidden;
    }

    $ids = ['edit-shop_order', 'woocommerce_page_wc-orders'];
    if (!in_array((string) $screen->id, $ids, true)) {
        return $hidden;
    }

    $hidden = is_array($hidden) ? $hidden : [];
    $hidden = array_values(array_unique(array_merge(
        $hidden,
        matrix_rd_shop_order_list_hidden_column_keys()
    )));

    return array_values(array_diff($hidden, matrix_rd_shop_order_list_visible_column_keys()));
}

/**
 * Default the orders list to Iconic's Delivery column (soonest upcoming first).
 *
 * @param array<string, mixed> $get
 * @return array{orderby: string, order: string}|null
 */
function matrix_rd_shop_order_list_default_orderby_args(array $get): ?array
{
    $post_type = isset($get['post_type']) ? (string) $get['post_type'] : '';
    $page      = isset($get['page']) ? (string) $get['page'] : '';
    if (function_exists('sanitize_key')) {
        $post_type = sanitize_key($post_type);
        $page      = sanitize_key($page);
    }

    if ($post_type !== 'shop_order' && $page !== 'wc-orders') {
        return null;
    }

    if (isset($get['orderby']) && (string) $get['orderby'] !== '') {
        return null;
    }

    $action = isset($get['action']) ? (string) $get['action'] : '';
    if ($action !== '' && $action !== '-1') {
        return null;
    }

    return [
        'orderby' => 'jckwds_delivery',
        'order'   => 'asc',
    ];
}

function matrix_rd_shop_order_list_redirect_to_delivery_sort(): void
{
    if (!is_admin()) {
        return;
    }

    $args = matrix_rd_shop_order_list_default_orderby_args(wp_unslash($_GET));
    if ($args === null) {
        return;
    }

    wp_safe_redirect(add_query_arg($args));
    exit;
}

function matrix_rd_shop_order_list_is_delivery_sort(WP_Query $query): bool
{
    if (!is_admin() || !$query->is_main_query()) {
        return false;
    }

    $post_type = $query->get('post_type');
    if ($post_type !== 'shop_order') {
        return false;
    }

    $orderby = isset($_GET['orderby']) ? sanitize_key(wp_unslash((string) $_GET['orderby'])) : (string) $query->get('orderby');

    return $orderby === 'jckwds_delivery';
}

/**
 * Stop Iconic's INNER JOIN on timestamp so we can filter to upcoming slots
 * and sub-sort by order placed date.
 */
function matrix_rd_shop_order_list_clear_iconic_meta_sort(WP_Query $query): void
{
    if (!matrix_rd_shop_order_list_is_delivery_sort($query)) {
        return;
    }

    $query->set('meta_key', '');
    $query->set('meta_compare_key', '');
    $query->set('orderby', 'none');
}

function matrix_rd_shop_order_list_today_timestamp(): int
{
    if (function_exists('wp_timezone')) {
        return (new DateTimeImmutable('today', wp_timezone()))->getTimestamp();
    }

    return (int) strtotime('today');
}

/**
 * Upcoming only: delivery calendar date, then order placed date, then slot time.
 */
function matrix_rd_shop_order_list_delivery_orderby_sql(string $posts_table, string $ymd_expr, string $ts_expr, string $direction): string
{
    $dir = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

    return "{$ymd_expr} {$dir}, {$posts_table}.post_date ASC, {$ts_expr} {$dir}, {$posts_table}.ID ASC";
}

function matrix_rd_shop_order_list_upcoming_where_sql(string $ts_expr, int $today_ts): string
{
    return ' AND ' . $ts_expr . ' >= ' . (int) $today_ts;
}

/**
 * @param array<string, string> $clauses
 * @return array<string, string>
 */
function matrix_rd_shop_order_list_posts_clauses(array $clauses, WP_Query $query): array
{
    if (!matrix_rd_shop_order_list_is_delivery_sort($query)) {
        return $clauses;
    }

    global $wpdb;

    if (strpos((string) $clauses['join'], 'rd_wds') === false) {
        $clauses['join'] .= " INNER JOIN (
            SELECT t.post_id,
                   MAX(CAST(t.meta_value AS UNSIGNED)) AS ts,
                   MAX(d.meta_value) AS ymd
            FROM {$wpdb->postmeta} t
            LEFT JOIN {$wpdb->postmeta} d
              ON d.post_id = t.post_id
             AND d.meta_key IN ('jckwds_date_ymd','_jckwds_date_ymd')
            WHERE t.meta_key IN ('jckwds_timestamp','_jckwds_timestamp')
            GROUP BY t.post_id
        ) rd_wds ON rd_wds.post_id = {$wpdb->posts}.ID ";
    }

    $clauses['where'] .= matrix_rd_shop_order_list_upcoming_where_sql(
        'rd_wds.ts',
        matrix_rd_shop_order_list_today_timestamp()
    );

    $direction = isset($_GET['order']) ? strtolower((string) wp_unslash($_GET['order'])) : 'asc';
    $clauses['orderby'] = matrix_rd_shop_order_list_delivery_orderby_sql(
        $wpdb->posts,
        'rd_wds.ymd',
        'rd_wds.ts',
        $direction
    );

    return $clauses;
}

/**
 * Same upcoming sort for WooCommerce → Deliveries (Iconic):
 * delivery date, then order placed date, then slot time.
 *
 * @return string
 */
function matrix_rd_wds_reservations_order_sql(string $jckwds_alias, string $posts_alias): string
{
    return "{$jckwds_alias}.date ASC, {$posts_alias}.post_date ASC, COALESCE({$jckwds_alias}.starttime, 0) ASC, {$jckwds_alias}.order_id ASC";
}

/**
 * Replace Iconic's date/starttime query so Deliveries matches the orders list.
 *
 * @param mixed $reservations
 * @param mixed $processed
 * @return mixed
 */
function matrix_rd_wds_reservations_pre_query($reservations, $processed)
{
    if ($reservations !== null || !isset($GLOBALS['wpdb'])) {
        return $reservations;
    }

    global $wpdb;

    $today = function_exists('current_time') ? current_time('Y-m-d') : gmdate('Y-m-d');
    $order = matrix_rd_wds_reservations_order_sql('j', 'p');

    return $wpdb->get_results(
        $wpdb->prepare(
            "SELECT j.*
             FROM {$wpdb->prefix}jckwds j
             LEFT JOIN {$wpdb->posts} p ON p.ID = j.order_id
             WHERE j.date >= %s AND j.processed = %d
             ORDER BY {$order}",
            $today,
            (int) $processed
        )
    );
}
