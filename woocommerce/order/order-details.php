<?php
/**
 * Order details — Rolling Donut My Account view-order layout.
 *
 * Ports the legacy Sage flex table (grey Product/Total header, white rows)
 * so view-order matches the orders list instead of WooCommerce's default shop_table.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.9.0
 *
 * @var int $order_id
 */

defined('ABSPATH') || exit;

$order = wc_get_order($order_id); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

if (is_wc_endpoint_url('view-order')) {
    echo '<div class="w-full px-4 mx-auto max-w-max-1000 wc-backward-container"><a href="' . esc_url(wc_get_account_endpoint_url('orders')) . '" class="flex items-center font-bold leading-none woocommerce-button button wc-backward font-laca text-mob-md-font text-yellow-primary hover:underline flex-container"><svg class="mr-2" width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
<path d="M27 14C27 6.8203 21.1797 1 14 1C6.8203 1 0.999998 6.8203 0.999999 14C0.999999 21.1797 6.8203 27 14 27C21.1797 27 27 21.1797 27 14Z" fill="#FFED56" stroke="black" stroke-width="2"/>
<path fill-rule="evenodd" clip-rule="evenodd" d="M15.0406 18.3071C15.3047 18.5711 15.7327 18.5711 15.9967 18.3071L16.1533 18.1506C16.4171 17.8868 16.4173 17.4591 16.1538 17.195L13.0184 14.0528L16.1538 10.9105C16.4173 10.6464 16.4171 10.2188 16.1533 9.95496L15.9967 9.79841C15.7327 9.53439 15.3047 9.53439 15.0406 9.79841L10.7863 14.0528L15.0406 18.3071Z" fill="black"/>
</svg>
' . esc_html__('Back to orders', 'woocommerce') . '</a></div>';
}

if (! $order) {
    return;
}

$order_items           = $order->get_items(apply_filters('woocommerce_purchase_order_item_types', 'line_item'));
$show_customer_details = is_user_logged_in() && $order->get_user_id() === get_current_user_id();
?>
<style>
    .item-ordered .woocommerce-Price-amount.amount {
        display: none;
    }
</style>
<div class="rd-view-order flex flex-col w-full">
    <div class="rd-view-order__head flex justify-between px-4 mobile:px-10 py-2 bg-grey-disabled">
        <div class="font-bold text-left"><?php esc_html_e('Product', 'woocommerce'); ?></div>
        <div class="font-bold text-center"><?php esc_html_e('Total', 'woocommerce'); ?></div>
    </div>
    <div class="rd-view-order__items flex flex-col">
        <?php
        $main_product   = null;
        $selected_items = [];

        foreach ($order_items as $item_id => $item) {
            $product = $item->get_product();
            if ($product && $product->get_type() === 'donut_box_builder') {
                $main_product = ['id' => $item_id, 'item' => $item];
            } else {
                $selected_items[] = ['id' => $item_id, 'item' => $item];
            }
        }

        if ($main_product && function_exists('matrix_rd_render_view_order_item_row')) {
            matrix_rd_render_view_order_item_row($main_product['id'], $main_product['item'], $order);
            foreach ($selected_items as $selected) {
                matrix_rd_render_view_order_item_row($selected['id'], $selected['item'], $order);
            }
        } elseif (function_exists('matrix_rd_render_view_order_item_row')) {
            foreach ($order_items as $item_id => $item) {
                matrix_rd_render_view_order_item_row($item_id, $item, $order);
            }
        }
        ?>
    </div>
    <div class="rd-view-order__totals flex flex-col border-t-2 border-grey-disabled">
        <?php foreach ($order->get_order_item_totals() as $total) : ?>
            <div class="flex justify-between bg-white border-b-2 border-grey-disabled last:border-b-0">
                <div class="px-4 mobile:px-10 py-5 text-left"><?php echo esc_html($total['label']); ?></div>
                <div class="px-4 mobile:px-10 py-5 text-center"><span class="woocommerce-Price-amount amount"><?php echo wp_kses_post($total['value']); ?></span></div>
            </div>
        <?php endforeach; ?>
        <?php if ($order->get_customer_note()) : ?>
            <div class="flex justify-between bg-white border-b-2 border-grey-disabled last:border-b-0">
                <div class="px-4 mobile:px-10 py-5 text-left"><?php esc_html_e('Note:', 'woocommerce'); ?></div>
                <div class="px-4 mobile:px-10 py-5 text-center"><?php echo wp_kses_post(nl2br(wptexturize($order->get_customer_note()))); ?></div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php do_action('woocommerce_order_details_after_order_table', $order); ?>

<?php
/**
 * Action hook fired after the order details.
 *
 * @since 4.4.0
 * @param WC_Order $order Order data.
 */
do_action('woocommerce_after_order_details', $order);

if ($show_customer_details) {
    wc_get_template('order/order-details-customer.php', ['order' => $order]);
}
?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var content = document.querySelector('.woocommerce-MyAccount-content');
        if (!content) {
            return;
        }

        var backButton = content.querySelector('.wc-backward-container');
        if (backButton && content.parentNode) {
            content.parentNode.insertBefore(backButton, content);
        }

        var reorderButton = content.querySelector('.wc-reorder-button');
        if (reorderButton && content.parentNode) {
            var wrap = reorderButton.closest('p') || reorderButton;
            var container = document.createElement('div');
            container.className = 'px-4 w-full max-w-max-1000 mx-auto wc-reorder-button-container';
            wrap.parentNode && wrap.parentNode.removeChild(wrap);
            container.appendChild(wrap);
            content.parentNode.insertBefore(container, content.nextSibling);
        }
    });
</script>
