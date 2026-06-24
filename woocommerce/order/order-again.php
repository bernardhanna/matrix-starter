<?php
/**
 * Order Again Button
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/order/order-again.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 5.2.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!isset($order_again_url) && isset($order) && is_a($order, 'WC_Order')) {
    // Generate the order again URL
    $order_again_url = wp_nonce_url(add_query_arg('order_again', $order->get_id(), wc_get_cart_url()), 'woocommerce-order_again');
}
?>
<a href="<?php echo esc_url($order_again_url); ?>" class="button wc-reorder-button">
    <?php esc_html_e('Order Again', 'woocommerce'); ?>
</a>
