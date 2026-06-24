<?php

/**
 * Thankyou page — Rolling Donut legacy parity.
 *
 * Ports the legacy Sage thank-you design: black background (set on the page
 * wrapper in page.php), optional illustration beside a white order overview and
 * a "Keep Shopping" CTA. The page background pattern + image come from the
 * "Thank You Page" theme options (ty_bg / ty_image).
 *
 * @see https://woo.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.1.0
 *
 * @var WC_Order $order
 */

defined('ABSPATH') || exit;

$ty_image = (function_exists('get_field') && function_exists('matrix_rd_acf_image_url'))
    ? matrix_rd_acf_image_url(get_field('ty_image', 'option'))
    : '';

$is_local_pickup_plus = false;
$location_id          = '';
$is_delivery          = false;

if ($order instanceof WC_Order) {
    foreach ($order->get_items('shipping') as $item) {
        if ($item->get_method_id() === 'local_pickup_plus') {
            $is_local_pickup_plus = true;
            $location_id          = $item->get_meta('_pickup_location_id');
        }

        if ($item->get_method_id() === 'flat_rate' || $item->get_method_id() === 'delivery_method_id') {
            $is_delivery = true;
        }
    }

    $delivery_date = $order->get_meta('jckwds_date');
}
?>
<style>
    .rd-thankyou-image {
        height: 346px;
    }

    @media (min-width: 1024px) {
        .rd-thankyou-image {
            height: auto;
        }
    }

    .rd-thankyou-keep-shopping {
        min-height: 58px;
    }
</style>
<div class="px-4 m-auto woocommerce-order lg:max-w-max-1568">
    <div class="flex flex-col items-center h-full lg:flex-row lg:justify-between">
        <?php if ($ty_image) : ?>
            <div class="flex items-center justify-center w-full lg:w-1/2">
                <img class="rd-thankyou-image w-full object-contain" src="<?php echo esc_url($ty_image); ?>" alt="<?php esc_attr_e('Order Success', 'matrix-starter'); ?>">
            </div>
        <?php endif; ?>
        <div class="w-full <?php echo $ty_image ? 'lg:w-1/2' : ''; ?>">
            <?php if ($order) :

                do_action('woocommerce_before_thankyou', $order->get_id());
            ?>

                <?php if ($order->has_status('failed')) : ?>

                    <p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed text-white"><?php esc_html_e('Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'woocommerce'); ?></p>

                    <p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed-actions">
                        <a href="<?php echo esc_url($order->get_checkout_payment_url()); ?>" class="button pay"><?php esc_html_e('Pay', 'woocommerce'); ?></a>
                        <?php if (is_user_logged_in()) : ?>
                            <a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>" class="button pay"><?php esc_html_e('My account', 'woocommerce'); ?></a>
                        <?php endif; ?>
                    </p>

                <?php else : ?>

                    <?php wc_get_template('checkout/order-received.php', array('order' => $order)); ?>

                    <ul class="woocommerce-order-overview woocommerce-thankyou-order-details order_details">

                        <li class="flex items-center justify-center py-4 my-4 text-white border-t border-b border-white order-numb woocommerce-order-overview__order order text-sm-md-font lg:text-md-font">
                            <?php esc_html_e('Order number:', 'woocommerce'); ?>
                            <strong class="font-reg420">&nbsp;<?php echo esc_html($order->get_order_number()); ?></strong>
                        </li>

                        <li class="text-white woocommerce-order-overview__date date text-sm-md-font lg:text-md-font">
                            <strong><?php esc_html_e('Order Date:', 'woocommerce'); ?></strong>
                            <?php echo esc_html(wc_format_datetime($order->get_date_created())); ?>
                        </li>

                        <?php if ($is_delivery && ! empty($delivery_date)) : ?>
                            <li class="text-white woocommerce-order-overview__date date text-sm-md-font lg:text-md-font">
                                <strong><?php esc_html_e('Delivery Date:', 'matrix-starter'); ?></strong> <?php echo esc_html($delivery_date); ?>
                            </li>
                        <?php endif; ?>

                        <?php if (is_user_logged_in() && $order->get_user_id() === get_current_user_id() && $order->get_billing_email()) : ?>
                            <li class="text-white woocommerce-order-overview__email email text-sm-md-font lg:text-mob-md-font">
                                <?php esc_html_e('Email:', 'woocommerce'); ?>
                                <?php echo esc_html($order->get_billing_email()); ?>
                            </li>
                        <?php endif; ?>

                        <li class="text-white woocommerce-order-overview__total total text-mob-md-font">
                            <?php esc_html_e('Total:', 'woocommerce'); ?>
                            <?php echo wp_kses_post($order->get_formatted_order_total()); ?>
                        </li>

                        <?php
                        $is_local_pickup_plus = false;
                        foreach ($order->get_items('shipping') as $shipping_item) {
                            if (strpos($shipping_item->get_method_title(), 'Collection') !== false) {
                                $is_local_pickup_plus = true;
                                break;
                            }
                        }

                        if ($is_local_pickup_plus && $location_id) :
                            $location_post = get_post($location_id);
                            $location_name = $location_post ? $location_post->post_title : '';
                            $pickup_date   = $order->get_meta('jckwds_date');
                        ?>
                            <li class="mt-4 text-white text-sm-font lg:text-mob-md-font">
                                <?php if ($pickup_date) : ?>
                                    <p><strong><?php esc_html_e('Collection Date:', 'matrix-starter'); ?></strong> <?php echo esc_html($pickup_date); ?></p>
                                <?php endif; ?>
                                <?php if ($location_name) : ?>
                                    <p><strong><?php esc_html_e('Collection Location:', 'matrix-starter'); ?></strong> <?php echo esc_html($location_name); ?></p>
                                <?php endif; ?>
                                <p><strong><?php esc_html_e('Your Donuts Will Be Available When the Store Opens!', 'matrix-starter'); ?></strong> <a class="underline hover:no-underline" target="_blank" rel="noopener" href="<?php echo esc_url(home_url('/our-shops/')); ?>"><?php esc_html_e('View Opening Hours', 'matrix-starter'); ?></a></p>
                            </li>
                        <?php else : ?>
                            <li class="mt-4 text-white text-sm-font lg:text-mob-md-font">
                                <?php echo esc_html($order->get_formatted_billing_full_name()); ?>
                            </li>
                            <?php if ($order->get_billing_address_1()) : ?>
                                <li class="text-white text-sm-font lg:text-mob-md-font">
                                    <?php echo esc_html($order->get_billing_address_1()); ?>
                                </li>
                            <?php endif; ?>
                            <?php if ($order->get_billing_address_2()) : ?>
                                <li class="text-white text-sm-font lg:text-mob-md-font">
                                    <?php echo esc_html($order->get_billing_address_2()); ?>
                                </li>
                            <?php endif; ?>
                            <?php if ($order->get_billing_city()) : ?>
                                <li class="text-white text-sm-font lg:text-mob-md-font">
                                    <?php echo esc_html($order->get_billing_city() . ', ' . $order->get_billing_state() . ' ' . $order->get_billing_postcode()); ?>
                                </li>
                            <?php endif; ?>
                            <?php if ($order->get_billing_country() && isset(WC()->countries->countries[$order->get_billing_country()])) : ?>
                                <li class="text-white text-sm-font lg:text-mob-md-font">
                                    <?php echo esc_html(WC()->countries->countries[$order->get_billing_country()]); ?>
                                </li>
                            <?php endif; ?>
                            <?php if ($order->get_shipping_address_1()) : ?>
                                <li class="mt-4 text-white text-sm-font lg:text-mob-md-font">
                                    <strong><?php esc_html_e('Shipping Address:', 'woocommerce'); ?></strong><br>
                                    <?php echo wp_kses_post($order->get_formatted_shipping_address()); ?>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>

                    </ul>

                    <div class="mt-4">
                        <a href="<?php echo esc_url(home_url('/donut-box/')); ?>" class="rd-thankyou-keep-shopping w-full border-2 text-white border-white border-solid woocommerce-button button wc-forward text-sm-font font-reg420 text-center flex justify-center items-center rounded-btn-72 hover:bg-white hover:text-black-full transition-all">
                            <?php esc_html_e('Keep Shopping', 'woocommerce'); ?>
                        </a>
                    </div>

                <?php endif; ?>

            <?php else : ?>

                <?php wc_get_template('checkout/order-received.php', array('order' => false)); ?>

            <?php endif; ?>
        </div>
    </div>
</div>
