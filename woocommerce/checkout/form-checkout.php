<?php

/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

if (! defined('ABSPATH')) {
    exit;
}

do_action('woocommerce_before_checkout_form', $checkout);

// If checkout registration is disabled and not logged in, the user cannot checkout.
if (! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in()) {
    echo esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('You must be logged in to checkout.', 'woocommerce')));
    return;
}

?>
<style>
     .kl_newsletter_checkbox_field .woocommerce-input-wrapper {
        border: none!important;
     }

      .kl_newsletter_checkbox_field .woocommerce-input-wrapper label {
            display: flex
            ;
                align-items: center;
      }

     .kl_newsletter_checkbox_field .woocommerce-input-wrapper  .input-checkbox {
    height: 20px;
    width: 20px;
        margin-right: .5rem;
            border-radius: 0;

    padding-left: 0px;
      }
    #billing_state {
        width: 100%;
        height: 3.5rem;
        background: transparent;
    }

    /* Delivery/collection is local only, so the shipping country stays hidden
       (forced to the store base). Billing is global — customers can bill from
       any country — so the billing country selector is shown. */
    #shipping_country_field {
        display: none !important;
    }

    .child-product {
        display: none;
    }

    .iconic-wds-fields__fields .single-field-wrapper {
        padding-bottom: 0rem !important;
    }

    #rd-checkout-step-method ul.woocommerce-shipping-methods,
    .woocommerce .shipping-costs-row ul.woocommerce-shipping-methods {
        list-style: none outside;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        width: 100%;
        border: none;
    }

    .woocommerce-input-wrapper select {
        width: 100%;
        border: none !important;
        padding-left: 3rem;
        height: 100%;
    }

    .woocommerce-shipping-total.shipping th,
    .rd-checkout-shipping-table .woocommerce-shipping-totals.shipping > th {
        display: none !important;
    }

    .rd-checkout-shipping-table {
        border: 0;
    }

    .rd-checkout-shipping-table td,
    .rd-checkout-shipping-table th {
        border: 0;
        padding: 0;
    }

    /* Keep the shipping/collection options fluid. The plugin renders them in
       auto-layout tables that size to their content (~545px) and ignore the
       column, so on narrower columns the option boxes overflow the card.
       Forcing fixed layout + 100% width makes them track the container. */
    .rd-checkout-shipping-table,
    .rd-checkout-shipping-table .lpp-shipping-package-wrapper,
    .rd-checkout-shipping-table ul.woocommerce-shipping-methods {
        width: 100%;
        max-width: 100%;
        table-layout: fixed;
    }

    .rd-checkout-shipping-table td {
        min-width: 0;
        max-width: 100%;
    }

    .rd-checkout-shipping-table .rd-shipping-option,
    .rd-checkout-shipping-table .pickup-location-address {
        overflow-wrap: anywhere;
    }

    .woocommerce-error {
        height: max-content !important;
        max-width: 1728px;
        margin: auto;
        background-color: black;
        width: 100%;
    }

    .hide-on-checkout {
        display: none !important;
    }

    #jckwds-delivery-date-description {
        display: none !important;
    }

    .woocommerce-info::before {
        display: none;
    }

    .woocommerce-info {
        border-top-color: #000;
        padding: 0px;
        margin: 0px;
        margin-bottom: 1rem;
    }

    .woocommerce-checkout form.checkout_coupon {
        border: none;
        padding: 0px;
        border: none;
        padding-top: 1rem;
    }

    .woocommerce-checkout #payment {
        background: transparent;
        border-radius: unset;
    }

    .woocommerce-checkout #payment div.payment_box {
        position: relative;
        box-sizing: border-box;
        width: 100%;
        padding: 1em;
        margin: 1em 0;
        font-size: .92em;
        border-radius: 2px;
        line-height: 1.5;
        background-color: transparent;
        color: #515151;
    }

    .pickup-location-address {
        display: none;
    }

    .woocommerce-checkout #payment div.payment_box {
        position: relative;
        box-sizing: unset;
        width: 100%;
        padding: unset;
        margin: unset;
        font-size: unset;
        border-radius: unset;
        line-height: 1.5;
        background-color: transparent;
        color: unset;
    }

    .woocommerce-checkout #payment div.form-row {
        padding: 0px;
    }

    .woocommerce-checkout #payment div.payment_box::before {
        display: none;
    }

    .woocommerce-checkout #payment div.payment_box .form-row {
        margin: 0 0 0em;
    }

    .woocommerce-checkout #payment ul.payment_methods li input {
        margin: 0px;
        width: 20px !important;
        height: 20px !important;
        margin-right: 0.5rem;
    }

    .woocommerce-checkout #payment ul.payment_methods {
        text-align: left;
        padding: 1.5rem;
        margin: 0;
        list-style: none outside;
        margin-bottom: 1rem;
    }

    [type='text'],
    [type='email'],
    [type='url'],
    [type='password'],
    [type='number'],
    [type='date'],
    [type='datetime-local'],
    [type='month'],
    [type='tel'],
    [type='time'],
    [type='week'],
    [multiple],
    textarea,
    select {
        border: 0px solid #D8D7CE;
    }

    .woocommerce-shipping-totals .select2-container .select2-selection--single {
        height: 3.5rem;
        border-radius: 12.5rem;
        margin-top: 1rem;
    }

    .woocommerce-shipping-totals .select2-container--default .select2-selection--single .select2-selection__rendered {
        padding-left: 1.75rem;
    }

    .woocommerce-shipping-totals .select2-container--default.select2-container--open.select2-container--below .select2-selection--single {
        border-bottom-left-radius: unset;
        border-bottom-right-radius: unset;
        border-radius: 12.5rem;
    }

    .woocommerce-shipping-totals .select2-container--open .select2-dropdown {
        left: 0 !important;
        right: auto !important;
    }

    .woocommerce #payment #place_order,
    .woocommerce-page #payment #place_order {
        margin-top: 1rem;
        float: right;
        border-radius: 4.5rem;
        border: 3px solid var(--black-key, #000);
        background: var(--yellow-main, #FFED56);
        color: black;
        font-size: 1.5rem;
        font-style: normal;
        font-weight: 420;
        line-height: 1.625rem;
    }

    .woocommerce #payment #place_order:hover,
    .woocommerce-page #payment #place_order:hover {
        background: var(--black-key, #000);
        color: var(--yellow-main, #FFED56);
    }

    .coupon-btn {
        border-radius: 4.5rem !important;
        border: 3px solid #FFED56 !important;
        background: #000 !important;
        color: #FFED56 !important;
        font-family: Edmondsans !important;
        font-size: 1.25rem !important;
        font-style: normal !important;
        font-weight: 420 !important;
        line-height: 1.5rem !important;
    }

    .coupon-btn:hover {
        background: #FFED56 !important;
        color: #000 !important;
    }

    .checkout_coupon [type='text'] {
        border: 1px solid #D8D7CE;
    }

    .woocommerce-shipping-contents {
        display: none;
    }

    #rd-checkout-step-method ul.woocommerce-shipping-methods li label,
    .woocommerce .shipping-costs-row ul.woocommerce-shipping-methods li label {
        font-size: 1.125rem;
        font-style: normal;
        font-weight: 400;
        line-height: 1.5;
        font-family: 'laca';
    }

    #rd-checkout-step-method ul.woocommerce-shipping-methods li .shipping-method-wrapper label {
        display: block;
    }

    .pickup-location-field .pickup-location-address {
        font-size: .8em;
        margin: 15px 0;
        font-size: 16px;
        font-style: normal;
        font-weight: 350;
        line-height: 1.625rem;
        margin-bottom: 0px;
    }

    .pickup-location-lookup {
        padding-left: 2rem;
        margin-top: 1rem;
        height: 3.5rem;
        font-weight: 350;
    }

    @media (max-width: 768px) {
        .woocommerce form .form-row {
            width: 100% !important;
        }

        .woocommerce form.checkout_coupon {
            margin: 0px !important;
        }
    }

    .woosb-cart-child {
        display: none;
    }

    .border-grey-bordermixmatch-child,
    .mixmatch-child {
        display: none !important;
    }

    #rd-checkout-step-method ul.woocommerce-shipping-methods li input,
    .woocommerce .shipping-costs-row ul.woocommerce-shipping-methods li input {
        margin: 0px .5rem 0 0;
        vertical-align: middle;
    }

    #rd-checkout-step-method ul.woocommerce-shipping-methods li,
    .woocommerce .shipping-costs-row ul.woocommerce-shipping-methods li {
        margin: 0 0 .5em;
        line-height: 1.5;
    }

    .pickup-location-field em {
        display: none;
    }

    .woocommerce-error {
        color: white;
    }

    .woocommerce-form__input.woocommerce-form__input-checkbox {
        height: 20px;
        width: 20px;
    }

    .woocommerce-button button.woocommerce-form-login__submit:hover {
        background-color: #FFED56;
        color: black;
    }

    .custom-woocommerce-notice {
        bottom: 0px !important;
    }

    @media (max-width: 1024px) {
        .custom-woocommerce-notice {
            top: 0px !important;
        }
    }

    .woocommerce form .form-row label.checkbox,
    .woocommerce-page form .form-row label.checkbox {
        display: flex;
    }

    .woocommerce-form__label.woocommerce-form__label-for-checkbox.checkbox {
        line-height: unset;
    }

    .woocommerce form .password-input,
    .woocommerce-page form .password-input {
        width: 100%;
    }

    .woocommerce form .show-password-input,
    .woocommerce-page form .show-password-input {
        top: 1rem;
    }

    .woocommerce form .form-row .input-checkbox {
        color: black;
    }

    .shipping-option-0 {
        padding: 16px 24px;
        border-radius: 8px;
        background: #EAEAEA;
    }

    .shipping-option-1 {
        padding: 16px 24px;
        border-radius: 8px;
        background: #EAEAEA;
    }

    .shipping-option-2 {
        padding: 16px 24px;
        border-radius: 8px;
        background: #EAEAEA;
    }

    .shipping-option-0:hover,
    .shipping-option-1:hover,
    .shipping-option-2:hover,
    .shipping-option-3:hover {
        background: #FFF6A7;
    }

    @media (max-width: 380px) {
        #rd-checkout-step-method ul.woocommerce-shipping-methods li label,
    .woocommerce .shipping-costs-row ul.woocommerce-shipping-methods li label {
            font-size: 16px;
        }

        .shipping-option-0 {
            padding: .5rem;
        }

        .shipping-option-1 {
            padding: .5rem;
        }

        .shipping-option-2 {
            padding: .5rem;
        }
    }

    #shipping_postcode_field .optional {
        display: none !important;
    }

    #billing_statebilling_state {
        padding-left: 2.75rem;
    }
</style>
<div id="moveNotice"></div>
<div class="pt-8 pb-24 mb-10 rd-express-checkout-page">
    <?php
    $rd_is_collection    = function_exists('matrix_rd_checkout_is_collection_selected') && matrix_rd_checkout_is_collection_selected();
    $rd_fulfilment_class = $rd_is_collection ? 'rd-fulfilment-collection' : 'rd-fulfilment-delivery';

    $rd_continue_date_label = $rd_is_collection
        ? __('Continue to collection date', 'matrix-starter')
        : __('Continue to delivery date', 'matrix-starter');
    $rd_schedule_heading = $rd_is_collection
        ? __('Choose your collection date', 'matrix-starter')
        : __('Choose your delivery date', 'matrix-starter');
    ?>
    <form name="checkout" method="post" class="mx-auto checkout woocommerce-checkout woo-move-notice max-w-max-1568 rd-express-checkout-form <?php echo esc_attr($rd_fulfilment_class); ?>" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">

        <?php if ($checkout->get_checkout_fields()) : ?>
            <nav class="rd-checkout-progress" aria-label="<?php esc_attr_e('Checkout progress', 'matrix-starter'); ?>">
                <ol class="rd-checkout-progress__list">
                    <li class="rd-checkout-progress__item rd-checkout-progress__item--active" data-rd-progress="method">
                        <button type="button" class="rd-checkout-progress__trigger" data-rd-progress="method">
                            <span class="rd-checkout-progress__number" aria-hidden="true">1</span>
                            <span class="rd-checkout-progress__label"><?php esc_html_e('Method', 'matrix-starter'); ?></span>
                        </button>
                    </li>
                    <li class="rd-checkout-progress__item" data-rd-progress="schedule">
                        <button type="button" class="rd-checkout-progress__trigger" data-rd-progress="schedule" disabled>
                            <span class="rd-checkout-progress__number" aria-hidden="true">2</span>
                            <span class="rd-checkout-progress__label"><?php esc_html_e('Date', 'matrix-starter'); ?></span>
                        </button>
                    </li>
                    <li class="rd-checkout-progress__item" data-rd-progress="details">
                        <button type="button" class="rd-checkout-progress__trigger" data-rd-progress="details" disabled>
                            <span class="rd-checkout-progress__number" aria-hidden="true">3</span>
                            <span class="rd-checkout-progress__label"><?php esc_html_e('Details', 'matrix-starter'); ?></span>
                        </button>
                    </li>
                    <li class="rd-checkout-progress__item rd-checkout-progress__item--pay" data-rd-progress="pay">
                        <button type="button" class="rd-checkout-progress__trigger" data-rd-progress="pay" disabled>
                            <span class="rd-checkout-progress__number" aria-hidden="true">4</span>
                            <span class="rd-checkout-progress__label"><?php esc_html_e('Payment', 'matrix-starter'); ?></span>
                        </button>
                    </li>
                </ol>
            </nav>

            <div class="rd-express-checkout">
                <div class="rd-express-checkout__main">
                    <?php do_action('woocommerce_checkout_before_customer_details'); ?>

                    <section id="rd-checkout-step-method" class="rd-checkout-step rd-checkout-step--active" data-rd-step="method" aria-labelledby="rd-checkout-step-method-heading">
                        <div class="rd-checkout-step__shell">
                            <div class="rd-checkout-step__header">
                                <span class="rd-checkout-step__number" aria-hidden="true">1</span>
                                <div class="rd-checkout-step__heading">
                                    <h2 id="rd-checkout-step-method-heading" class="rd-checkout-step__title text-base-font font-reg420">
                                        <?php esc_html_e('Delivery or collection', 'matrix-starter'); ?>
                                    </h2>
                                    <p class="rd-checkout-step__summary" data-rd-summary="method" aria-live="polite"></p>
                                </div>
                                <button type="button" class="rd-checkout-step__change" data-rd-goto="method" hidden>
                                    <?php esc_html_e('Change', 'matrix-starter'); ?>
                                </button>
                            </div>
                            <div class="rd-checkout-step__body">
                                <div class="rd-checkout-step__errors" role="alert" aria-live="assertive" hidden></div>
                                <?php matrix_rd_checkout_render_step_notices('method'); ?>
                                <div class="rd-express-shipping-body">
                                    <?php matrix_rd_express_checkout_render_shipping_methods(); ?>
                                </div>
                                <div class="rd-checkout-step__actions">
                                    <button type="button" class="rd-checkout-step__continue button alt" data-rd-label-delivery="<?php esc_attr_e('Continue to delivery date', 'matrix-starter'); ?>" data-rd-label-collection="<?php esc_attr_e('Continue to collection date', 'matrix-starter'); ?>">
                                        <?php echo esc_html($rd_continue_date_label); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section id="rd-checkout-step-schedule" class="rd-checkout-step rd-checkout-step--schedule rd-checkout-step--upcoming" data-rd-step="schedule" aria-labelledby="rd-checkout-step-schedule-heading">
                        <div class="rd-checkout-step__shell">
                            <div class="rd-checkout-step__header">
                                <span class="rd-checkout-step__number" aria-hidden="true">2</span>
                                <div class="rd-checkout-step__heading">
                                    <h2 id="rd-checkout-step-schedule-heading" class="rd-checkout-step__title text-base-font font-reg420" data-rd-label-delivery="<?php esc_attr_e('Choose your delivery date', 'matrix-starter'); ?>" data-rd-label-collection="<?php esc_attr_e('Choose your collection date', 'matrix-starter'); ?>">
                                        <?php echo esc_html($rd_schedule_heading); ?>
                                    </h2>
                                    <p class="rd-checkout-step__summary" data-rd-summary="schedule" aria-live="polite"></p>
                                </div>
                                <button type="button" class="rd-checkout-step__change" data-rd-goto="schedule" hidden>
                                    <?php esc_html_e('Change', 'matrix-starter'); ?>
                                </button>
                            </div>
                            <div class="rd-checkout-step__body" hidden>
                                <div class="rd-checkout-step__errors" role="alert" aria-live="assertive" hidden></div>
                                <?php matrix_rd_checkout_render_step_notices('schedule'); ?>
                                <?php do_action('rd_checkout_step_schedule'); ?>
                                <div class="rd-checkout-step__actions">
                                    <button type="button" class="rd-checkout-step__back" data-rd-goto="method">
                                        <?php esc_html_e('Back', 'matrix-starter'); ?>
                                    </button>
                                    <button type="button" class="rd-checkout-step__continue button alt">
                                        <?php esc_html_e('Continue to details', 'matrix-starter'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section id="rd-checkout-step-details" class="rd-checkout-step rd-checkout-step--upcoming" data-rd-step="details" aria-labelledby="rd-checkout-step-details-heading">
                        <div class="rd-checkout-step__shell">
                            <div class="rd-checkout-step__header">
                                <span class="rd-checkout-step__number" aria-hidden="true">3</span>
                                <div class="rd-checkout-step__heading">
                                    <h2 id="rd-checkout-step-details-heading" class="rd-checkout-step__title text-base-font font-reg420">
                                        <?php esc_html_e('Your details', 'matrix-starter'); ?>
                                    </h2>
                                    <p class="rd-checkout-step__summary" data-rd-summary="details" aria-live="polite"></p>
                                </div>
                                <button type="button" class="rd-checkout-step__change" data-rd-goto="details" hidden>
                                    <?php esc_html_e('Change', 'matrix-starter'); ?>
                                </button>
                            </div>
                            <div class="rd-checkout-step__body" hidden>
                                <div class="rd-checkout-step__errors" role="alert" aria-live="assertive" hidden></div>
                                <p class="rd-checkout-details-hint rd-delivery-hint font-laca font-regular text-sm-font">
                                    <?php esc_html_e('Enter the address where we should deliver your order.', 'matrix-starter'); ?>
                                </p>
                                <p class="rd-checkout-details-hint rd-collection-hint font-laca font-regular text-sm-font">
                                    <?php esc_html_e('Enter your contact and billing details for collection.', 'matrix-starter'); ?>
                                </p>
                                <div id="customer_details" class="rd-checkout-details-fields">
                                    <div class="col-2 rd-checkout-shipping-block">
                                        <?php do_action('woocommerce_checkout_shipping'); ?>
                                    </div>
                                    <div class="col-1 rd-checkout-billing-block hideText">
                                        <?php do_action('woocommerce_checkout_billing'); ?>
                                    </div>
                                </div>
                                <div class="rd-checkout-step__actions">
                                    <button type="button" class="rd-checkout-step__back" data-rd-goto="schedule">
                                        <?php esc_html_e('Back', 'matrix-starter'); ?>
                                    </button>
                                    <button type="button" class="rd-checkout-step__continue rd-checkout-step__continue--pay button alt">
                                        <?php esc_html_e('Continue to payment', 'matrix-starter'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section id="rd-checkout-step-pay" class="rd-checkout-step rd-checkout-step--upcoming" data-rd-step="pay" aria-labelledby="rd-checkout-step-pay-heading">
                        <div class="rd-checkout-step__shell">
                            <div class="rd-checkout-step__header">
                                <span class="rd-checkout-step__number" aria-hidden="true">4</span>
                                <div class="rd-checkout-step__heading">
                                    <h2 id="rd-checkout-step-pay-heading" class="rd-checkout-step__title text-base-font font-reg420">
                                        <?php esc_html_e('Payment method', 'matrix-starter'); ?>
                                    </h2>
                                    <p class="rd-checkout-step__summary" data-rd-summary="pay" aria-live="polite"></p>
                                </div>
                                <button type="button" class="rd-checkout-step__change" data-rd-goto="pay" hidden>
                                    <?php esc_html_e('Change', 'matrix-starter'); ?>
                                </button>
                            </div>
                            <div class="rd-checkout-step__body" hidden>
                                <div class="rd-checkout-step__errors" role="alert" aria-live="assertive" hidden></div>
                                <?php do_action('rd_checkout_step_pay_before_payment'); ?>
                                <?php woocommerce_checkout_payment(); ?>
                                <div class="rd-checkout-step__actions">
                                    <button type="button" class="rd-checkout-step__back" data-rd-goto="details">
                                        <?php esc_html_e('Back', 'matrix-starter'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>

                    <?php do_action('woocommerce_checkout_after_customer_details'); ?>
                </div>

                <aside class="rd-express-checkout__summary">
                    <?php do_action('woocommerce_checkout_before_order_review_heading'); ?>

                    <?php do_action('woocommerce_checkout_before_order_review'); ?>

                    <div id="order_review" class="woocommerce-checkout-review-order">
                        <?php do_action('woocommerce_checkout_order_review'); ?>
                    </div>

                    <?php do_action('woocommerce_checkout_after_order_review'); ?>
                </aside>
            </div>
        <?php endif; ?>
    </form>

    <div class="rd-mobile-pay-bar" id="rd-mobile-pay-bar" hidden>
        <div class="rd-mobile-pay-bar__inner">
            <div class="rd-mobile-pay-bar__total">
                <span class="rd-mobile-pay-bar__label"><?php esc_html_e('Total', 'matrix-starter'); ?></span>
                <strong class="rd-mobile-pay-bar__amount" aria-live="polite"></strong>
            </div>
            <button type="button" class="rd-mobile-pay-bar__button button alt">
                <?php esc_html_e('Place Order', 'matrix-starter'); ?>
            </button>
        </div>
    </div>
</div>
<?php do_action('woocommerce_after_checkout_form', $checkout); ?>
<script>
    jQuery(document).ready(function($) {
        $('#order_comments').on('input', function() {
            var maxLength = 200;
            var currentLength = $(this).val().length;

            if (currentLength > maxLength) {
                $(this).val($(this).val().substring(0, maxLength));
                alert('Customer note cannot exceed 200 characters.');
            }
        });
    });

    jQuery(document).ready(function($) {
        // Close the notice on button click
        $('body').on('click', '.close-notice-button', function() {
            $(this).closest('#custom-woocommerce-notice').fadeOut('fast');
        });

        // Auto-fade after 5 seconds
        setTimeout(function() {
            $('#custom-woocommerce-notice').fadeOut('slow');
        }, 5000);
    });
</script>