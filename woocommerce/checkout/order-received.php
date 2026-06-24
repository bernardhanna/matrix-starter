<?php

/**
 * "Order received" message — Rolling Donut legacy parity.
 *
 * Personalised, white-on-black confirmation heading rendered above the order
 * overview on the thank-you page.
 *
 * @see https://woo.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.3.0
 *
 * @var WC_Order|false $order
 */

defined('ABSPATH') || exit;

$first_name = $order ? $order->get_billing_first_name() : '';
?>

<?php if ($order && $first_name) : ?>
    <h1 class="text-white thankyou-title lg:text-xxl-font font-reg420 mb-6 text-sm-md-font">
        <?php
        /* translators: %s: customer first name. */
        echo esc_html(sprintf(__('Thank you, %s, for your order!', 'matrix-starter'), $first_name));
        ?>
    </h1>

    <p class="hidden lg:block text-white font-lighter text-sm-md-font woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received">
        <?php echo esc_html(apply_filters('woocommerce_thankyou_order_received_text', __('Congratulations! Your order has been placed. Thank you for shopping with us.', 'matrix-starter'), $order)); ?>
    </p>
<?php elseif ($order) : ?>
    <h1 class="text-white thankyou-title lg:text-xxl-font font-reg420 mb-6 text-sm-md-font">
        <?php esc_html_e('Thank you for your order!', 'matrix-starter'); ?>
    </h1>

    <p class="hidden lg:block text-white font-lighter text-sm-md-font woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received">
        <?php echo esc_html(apply_filters('woocommerce_thankyou_order_received_text', __('Congratulations! Your order has been placed. Thank you for shopping with us.', 'matrix-starter'), $order)); ?>
    </p>
<?php else : ?>
    <p class="text-white font-lighter text-sm-md-font woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received">
        <?php echo esc_html(apply_filters('woocommerce_thankyou_order_received_text', __('Congratulations! Your order has been placed. Thank you for shopping with us.', 'matrix-starter'), null)); ?>
    </p>
<?php endif; ?>
