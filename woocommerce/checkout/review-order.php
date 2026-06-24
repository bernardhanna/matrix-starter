<?php

/**
 * Review order table
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/review-order.php.
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

defined('ABSPATH') || exit;
?>
<style>
    .leading-zero {
        line-height: 0;
    }

    .leading-2 {
        line-height: 2;
    }

    .woocommerce-checkout-review-order-table .checkout-product-thumbnail img {
        display: block;
        height: 64px;
        width: 84px;
        border-radius: 12px;
        object-fit: cover;
    }
</style>
<details class="rd-order-summary w-full border-2 border-solid shop_table woocommerce-checkout-review-order-table border-black-full rounded-20px boxshadow">
    <summary class="rd-order-summary__bar" aria-label="<?php esc_attr_e('Order details', 'matrix-starter'); ?>">
        <span class="rd-order-summary__title text-base-font font-reg420"><?php esc_html_e('Order Details', 'matrix-starter'); ?></span>
        <span class="rd-order-summary__meta">
            <span class="rd-order-summary__total"><?php wc_cart_totals_order_total_html(); ?></span>
            <span class="rd-order-summary__chevron" aria-hidden="true"></span>
        </span>
    </summary>

    <div class="rd-order-summary__body">
    <div class="bg-gray-disabled bg-grey-disabled relative flex justify-between  px-2 mobile:px-8 pt-4">
        <div class="w-1/2 text-left product-name bg-grey-disabled text-base-font font-reg420"><?php esc_html_e('Product', 'woocommerce'); ?></div>
        <div class="product-total text-right bg-grey-disabled text-base-font font-reg420 py-[8px] px-2 w-1/2"><?php esc_html_e('Subtotal', 'woocommerce'); ?></div>
    </div>

    <div class="p-0 m-0 leading-zero">
        <?php
        do_action('woocommerce_review_order_before_cart_contents');

        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            if (function_exists('matrix_rd_side_cart_is_child_line') && matrix_rd_side_cart_is_child_line($cart_item)) {
                continue;
            }

            $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);

            if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key)) {
                $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
                $thumbnail         = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image('h-[64px] w-[84px] rounded-normal object-cover'), $cart_item, $cart_item_key);
        ?>
                <div class="<?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item flex justify-between items-center gap-4 px-2 mobile:px-8 py-8 w-full border-b border-grey-border', $cart_item, $cart_item_key)); ?>">
                    <div class="flex items-center flex-1 min-w-0 gap-4">
                        <div class="checkout-product-thumbnail shrink-0">
                            <?php
                            if ($product_permalink) {
                                printf('<a href="%s">%s</a>', esc_url($product_permalink), $thumbnail); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                            } else {
                                echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                            }
                            ?>
                        </div>
                        <div class="product-name font-laca font-regular text-sm-font leading-2">
                        <?php echo wp_kses_post(apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key)) . '&nbsp;'; ?>
                        <?php echo apply_filters('woocommerce_checkout_cart_item_quantity', ' <strong class="product-quantity">' . sprintf('&times;&nbsp;%s', $cart_item['quantity']) . '</strong>', $cart_item, $cart_item_key); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        ?>
                        <?php
                        // Box-builder parents get the collapsible accordion (box contents +
                        // editable customer note) instead of the flat item-data list. This
                        // mirrors the side cart and avoids the duplicate add-on rows that
                        // wc_get_formatted_cart_item_data() would otherwise render here.
                        if (class_exists('RD_Box_Builder_Cart_Edit') && RD_Box_Builder_Cart_Edit::is_box_parent($cart_item)) {
                            echo RD_Box_Builder_Cart_Edit::render($cart_item_key, $cart_item, 'checkout'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        } else {
                            echo wc_get_formatted_cart_item_data($cart_item); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        }
                        ?>
                        </div>
                    </div>
                    <div class="product-total font-laca font-regular text-sm-font shrink-0">
                        <?php echo apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        ?>
                    </div>
                </div>
        <?php
            }
        }

        do_action('woocommerce_review_order_after_cart_contents');
        ?>
    </div>

    <div class="flex justify-between px-2 py-4 border-b mobile:px-8 cart-subtotal border-grey-border">
        <div class="font-laca font-regular text-sm-font"><?php esc_html_e('Subtotal', 'woocommerce'); ?></div>
        <div class="font-laca font-regular text-sm-font"><?php wc_cart_totals_subtotal_html(); ?></div>
    </div>

    <?php foreach (WC()->cart->get_coupons() as $code => $coupon) : ?>
        <div class="flex justify-between  px-2 mobile:px-8 py-4  border-grey-border cart-discount coupon-<?php echo esc_attr(sanitize_title($code)); ?>">
            <div class="font-laca font-regular text-sm-font"><?php wc_cart_totals_coupon_label($coupon); ?></div>
            <div class="font-laca font-regular text-sm-font"><?php wc_cart_totals_coupon_html($coupon); ?></div>
        </div>
    <?php endforeach; ?>


    <?php foreach (WC()->cart->get_fees() as $fee) : ?>
        <div class="flex justify-between px-2 py-4 mobile:px-8 fee">
            <div class="font-laca font-regular text-sm-font"><?php echo esc_html($fee->name); ?></div>
            <div class="font-laca font-regular text-sm-font"><?php wc_cart_totals_fee_html($fee); ?></div>
        </div>
    <?php endforeach; ?>

    <?php if (wc_tax_enabled() && !WC()->cart->display_prices_including_tax()) : ?>
        <?php if ('itemized' === get_option('woocommerce_tax_total_display')) : ?>
            <?php foreach (WC()->cart->get_tax_totals() as $code => $tax) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
            ?>
                <div class="flex justify-between tax-rate tax-rate-<?php echo esc_attr(sanitize_title($code)); ?>">
                    <div class="font-laca font-regular text-sm-font"><?php echo esc_html($tax->label); ?></div>
                    <div class="font-laca font-regular text-sm-font"><?php echo wp_kses_post($tax->formatted_amount); ?></div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="flex justify-between px-8 py-4 tax-total">
                <div class="font-laca font-regular text-sm-font"><?php echo esc_html(WC()->countries->tax_or_vat()); ?></div>

                <div class="font-laca font-regular text-sm-font"><?php wc_cart_totals_taxes_total_html(); ?></div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php do_action('woocommerce_review_order_before_order_total'); ?>

    <div class="flex justify-between px-8 py-8 order-total">
        <div class="font-laca font-regular text-sm-font"><?php esc_html_e('Total', 'woocommerce'); ?></div>
        <div class="font-laca font-regular text-sm-font"><?php wc_cart_totals_order_total_html(); ?></div>
    </div>

    <?php do_action('woocommerce_review_order_after_order_total'); ?>

    </div>
</details>