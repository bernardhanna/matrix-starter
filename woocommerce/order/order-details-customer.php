<?php
/**
 * Order customer details — Rolling Donut view-order addresses.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.7.0
 */

defined('ABSPATH') || exit;

$pickup = function_exists('matrix_rd_get_order_pickup_display')
    ? matrix_rd_get_order_pickup_display($order)
    : ['name' => '', 'address' => ''];

$has_pickup   = ($pickup['name'] !== '' || $pickup['address'] !== '');
$show_shipping = ! $has_pickup
    && ! wc_ship_to_billing_address_only()
    && $order->has_shipping_address();
?>
<section class="woocommerce-customer-details rd-view-order-customer">

    <section class="woocommerce-columns woocommerce-columns--2 woocommerce-columns--addresses col2-set addresses flex flex-col sm:flex-row justify-between pb-8 px-4 mobile:px-12">
        <div class="woocommerce-column woocommerce-column--1 woocommerce-column--billing-address col-1">
            <h2 class="woocommerce-column__title font-reg420"><?php esc_html_e('Billing address', 'woocommerce'); ?></h2>

            <address>
                <?php echo wp_kses_post($order->get_formatted_billing_address(esc_html__('N/A', 'woocommerce'))); ?>

                <?php if ($order->get_billing_phone()) : ?>
                    <p class="woocommerce-customer-details--phone"><?php echo esc_html($order->get_billing_phone()); ?></p>
                <?php endif; ?>

                <?php if ($order->get_billing_email()) : ?>
                    <p class="woocommerce-customer-details--email"><?php echo esc_html($order->get_billing_email()); ?></p>
                <?php endif; ?>

                <?php
                /**
                 * Action hook fired after an address in the order customer details.
                 *
                 * @since 8.7.0
                 * @param string   $address_type Type of address (billing or shipping).
                 * @param WC_Order $order        Order object.
                 */
                do_action('woocommerce_order_details_after_customer_address', 'billing', $order);
                ?>
            </address>
        </div>

        <?php if ($has_pickup) : ?>
            <div class="woocommerce-column woocommerce-column--2 woocommerce-column--pickup-address col-2 mt-6 sm:mt-0">
                <h2 class="woocommerce-column__title font-reg420"><?php esc_html_e('Pickup Location', 'woocommerce'); ?></h2>
                <address>
                    <?php
                    echo wp_kses_post($pickup['name']);
                    if ($pickup['name'] !== '' && $pickup['address'] !== '') {
                        echo '<br>';
                    }
                    echo wp_kses_post($pickup['address']);
                    ?>

                    <?php
                    do_action('woocommerce_order_details_after_customer_address', 'pickup', $order);
                    ?>
                </address>
            </div>
        <?php elseif ($show_shipping) : ?>
            <div class="woocommerce-column woocommerce-column--2 woocommerce-column--shipping-address col-2 mt-6 sm:mt-0">
                <h2 class="woocommerce-column__title font-reg420"><?php esc_html_e('Shipping address', 'woocommerce'); ?></h2>
                <address>
                    <?php echo wp_kses_post($order->get_formatted_shipping_address(esc_html__('N/A', 'woocommerce'))); ?>

                    <?php if ($order->get_shipping_phone()) : ?>
                        <p class="woocommerce-customer-details--phone"><?php echo esc_html($order->get_shipping_phone()); ?></p>
                    <?php endif; ?>

                    <?php
                    do_action('woocommerce_order_details_after_customer_address', 'shipping', $order);
                    ?>
                </address>
            </div>
        <?php endif; ?>
    </section>

    <?php do_action('woocommerce_order_details_after_customer_details', $order); ?>

</section>
