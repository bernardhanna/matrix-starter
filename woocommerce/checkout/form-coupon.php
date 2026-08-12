<?php

/**
 * @Author: Bernard Hanna
 * @Date:   2023-10-24 14:18:19
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-10-24 14:21:23
 */

/**
 * Checkout coupon form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-coupon.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

defined('ABSPATH') || exit;

if (!wc_coupons_enabled()) { // @codingStandardsIgnoreLine.
    return;
}

?>
<style>
    .checkout_coupon button {
        border-radius: 4.5rem;
        border: 3px solid black;
        background: #FFED56;
        color: black;
        font-style: normal;
        font-weight: 420;
        line-height: 1.625rem;
    }
</style>

<div class="flex flex-col justify-start px-0 mx-auto text-left max-w-max-1568 rd-checkout-coupon-legacy">
    <div class="woocommerce-form-coupon-toggle"><?php
                                                wc_print_notice(
                                                    apply_filters(
                                                        'woocommerce_checkout_coupon_message',
                                                        esc_html__('Have a coupon / gift voucher?', 'matrix-starter') . ' <a href="#" class="font-bold text-black underline showcoupon">' . esc_html__('Click here', 'woocommerce') . '</a>' . esc_html__(' to enter your code', 'woocommerce')
                                                    ),
                                                    'notice'
                                                );
                                                ?></div>
    <form class="checkout_coupon woocommerce-form-coupon" method="post" style="display:none" aria-hidden="true">
        <p><?php esc_html_e('If you have a coupon or gift voucher code, please apply it below.', 'matrix-starter'); ?></p>
        <div class="flex flex-col md:flex-row w-full items-center py-4 md:w-3/5 xxl:w-[772px]">
            <div class="w-full form-row form-row-first"><input type="text" name="coupon_code" class="flex w-full mr-4 font-light input-text woocommerce-Input woocommerce-Input--text rounded-lg-x h-input text-black-secondary text-mob-xs-font font-laca pl-11 lg:w-99" placeholder="<?php esc_attr_e('Coupon / Gift voucher', 'matrix-starter'); ?>" id="coupon_code" value="" /></div>
            <div class="w-full mt-4 form-row md:mt-0 form-row-last md:w-2/5"><button type="submit" class="coupon-btn h-input button text-yellow-primary bg-black-full border-3 border-solid border-yellow-primary rounded-btn-72 text-base-font font-reg420 hover:border-black-full hover:bg-yellow-primary hover:text-black-full  min-w-min-208 w-full md:w-[208px] flex items-center justify-center <?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>" name="apply_coupon" value="<?php esc_attr_e('Apply coupon', 'woocommerce'); ?>"><?php esc_html_e('Apply', 'matrix-starter'); ?></button></div>
        </div>
        <div class="clear"></div>
    </form>
</div>