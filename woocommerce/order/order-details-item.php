<?php
/**
 * Order item details — product name, qty, and meta for the view-order flex layout.
 *
 * The parent template prints the Total column separately, so this file outputs
 * product content only (no table rows).
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 5.2.0
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! apply_filters('woocommerce_order_item_visible', true, $item)) {
    return;
}

if (empty($product) && $item) {
    $product = $item->get_product();
}

$item_total = $order->get_line_total($item, true, true);
$displayed_meta_keys = [];

$show_purchase_note = isset($show_purchase_note)
    ? $show_purchase_note
    : $order->has_status(apply_filters('woocommerce_purchase_note_order_statuses', ['completed', 'processing']));
$purchase_note = isset($purchase_note) ? $purchase_note : ($product ? $product->get_purchase_note() : '');
?>
<div class="<?php echo esc_attr(apply_filters('woocommerce_order_item_class', 'woocommerce-table__line-item order_item', $item, $order)); ?>">
    <div class="woocommerce-table__product-name product-name">
        <?php
        $is_visible        = $product && $product->is_visible();
        $product_permalink = apply_filters('woocommerce_order_item_permalink', $is_visible ? $product->get_permalink($item) : '', $item, $order);

        echo wp_kses_post(apply_filters('woocommerce_order_item_name', $product_permalink ? sprintf('<a href="%s">%s</a>', $product_permalink, $item->get_name()) : $item->get_name(), $item, $is_visible));

        $qty          = $item->get_quantity();
        $refunded_qty = $order->get_qty_refunded_for_item($item_id);

        if ($refunded_qty) {
            $qty_display = '<del>' . esc_html($qty) . '</del> <ins>' . esc_html($qty - ($refunded_qty * -1)) . '</ins>';
        } else {
            $qty_display = esc_html($qty);
        }

        echo apply_filters('woocommerce_order_item_quantity_html', ' <strong class="product-quantity">' . sprintf('&times;&nbsp;%s', $qty_display) . '</strong>', $item); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        if ($item_total > 0) {
            do_action('woocommerce_order_item_meta_start', $item_id, $item, $order, false);

            $meta_data = $item->get_formatted_meta_data();
            foreach ($meta_data as $meta) {
                if (in_array($meta->key, $displayed_meta_keys, true)) {
                    continue;
                }

                $raw_value = is_string($meta->value) ? $meta->value : (string) $meta->display_value;
                $is_logo   = in_array($meta->key, ['Logo Upload', 'Additional Logos', 'logo_upload', 'additional_logos'], true)
                    || in_array($meta->display_key, ['Logo Upload', 'Additional Logos'], true);

                if ($is_logo && filter_var($raw_value, FILTER_VALIDATE_URL)) {
                    echo '<p><strong>' . esc_html($meta->display_key) . ':</strong> <img src="' . esc_url($raw_value) . '" alt="" style="max-width:50px;"></p>';
                } else {
                    echo '<p><strong>' . esc_html($meta->display_key) . ':</strong> ' . wp_kses_post(make_clickable($raw_value)) . '</p>';
                }

                $displayed_meta_keys[] = $meta->key;
            }

            do_action('woocommerce_order_item_meta_end', $item_id, $item, $order, false);
        }
        ?>
    </div>
    <?php if ($show_purchase_note && $purchase_note) : ?>
        <div class="woocommerce-table__product-purchase-note product-purchase-note">
            <?php echo wpautop(do_shortcode(wp_kses_post($purchase_note))); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>
    <?php endif; ?>
</div>
