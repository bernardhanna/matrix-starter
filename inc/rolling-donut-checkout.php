<?php
/**
 * Rolling Donut checkout — legacy Sage / mu-plugin parity.
 */

require_once __DIR__ . '/rolling-donut-custom-checkout.php';
require_once __DIR__ . '/rolling-donut-checkout-notices.php';
require_once __DIR__ . '/rolling-donut-express-checkout.php';

/**
 * Enqueue checkout-specific assets (legacy CSS path fixes + pickup plugin).
 */
function matrix_rd_checkout_enqueue_assets(): void {
    if (! function_exists('is_checkout') || ! is_checkout() || is_wc_endpoint_url('order-received')) {
        return;
    }

    wp_enqueue_style('woocommerce-general');
    wp_enqueue_style('woocommerce-layout');
    wp_enqueue_style('woocommerce-smallscreen');

    if (function_exists('wc_local_pickup_plus')) {
        $plugin  = wc_local_pickup_plus();
        $base    = $plugin->get_plugin_url();
        $version = class_exists('WC_Local_Pickup_Plus') ? WC_Local_Pickup_Plus::VERSION : '1.0';

        wp_enqueue_style(
            'wc-local-pickup-plus-frontend-css',
            $base . '/assets/css/frontend/wc-local-pickup-plus-frontend.min.css',
            [],
            $version
        );

        wp_enqueue_script(
            'wc-local-pickup-plus-frontend',
            $base . '/assets/js/frontend/wc-local-pickup-plus-frontend.min.js',
            ['jquery', 'woocommerce', 'wc-cart-fragments', 'wc-checkout'],
            $version,
            true
        );
    }

    $css_path = get_template_directory() . '/assets/css/rolling-donut-checkout.css';
    if (is_readable($css_path)) {
        wp_enqueue_style(
            'matrix-rd-checkout',
            get_template_directory_uri() . '/assets/css/rolling-donut-checkout.css',
            ['matrix-starter', 'matrix-rd-legacy'],
            (string) filemtime($css_path)
        );
    }
}
add_action('wp_enqueue_scripts', 'matrix_rd_checkout_enqueue_assets', 30);

/**
 * Hydrate pickup-location addresses from legacy post meta.
 *
 * Local Pickup Plus reads a location's address from its custom
 * `woocommerce_pickup_locations_geodata` table (see
 * WC_Local_Pickup_Plus_Pickup_Location::get_address()). Locations imported from
 * the legacy site only have `_pickup_location_address_*` post meta and no
 * geodata row, so get_address() returns empty — which is why the pickup
 * dropdown showed only the name with no address line.
 *
 * This filter backfills the address from that legacy meta whenever the plugin's
 * address comes back empty, so the address renders everywhere (dropdown option
 * second line, order emails, admin) without touching plugin tables.
 */
function matrix_rd_pickup_location_address_from_meta($address, $piece, $location) {
    if (! $address instanceof \WC_Local_Pickup_Plus_Address || ! is_object($location) || ! method_exists($location, 'get_id')) {
        return $address;
    }

    $already_populated = '' !== (string) $address->get_address_line_1()
        || '' !== (string) $address->get_city()
        || '' !== (string) $address->get_postcode();

    if ($already_populated) {
        return $address;
    }

    $location_id = (int) $location->get_id();
    if ($location_id <= 0) {
        return $address;
    }

    $address_1 = (string) get_post_meta($location_id, '_pickup_location_address_address_1', true);
    $city      = (string) get_post_meta($location_id, '_pickup_location_address_city', true);
    $postcode  = (string) get_post_meta($location_id, '_pickup_location_address_postcode', true);

    if ('' === $address_1 && '' === $city && '' === $postcode) {
        return $address;
    }

    return new \WC_Local_Pickup_Plus_Address([
        'name'      => method_exists($location, 'get_name') ? $location->get_name() : '',
        'country'   => (string) get_post_meta($location_id, '_pickup_location_address_country', true),
        'state'     => (string) get_post_meta($location_id, '_pickup_location_address_state', true),
        'city'      => $city,
        'postcode'  => $postcode,
        'address_1' => $address_1,
        'address_2' => (string) get_post_meta($location_id, '_pickup_location_address_address_2', true),
    ], $location_id);
}
add_filter('wc_local_pickup_plus_pickup_location_address', 'matrix_rd_pickup_location_address_from_meta', 10, 3);

/**
 * Render the "Check out" page heading at the very top of the checkout, above the
 * returning-customer login and coupon forms (both hooked at priority 10). The
 * heading previously lived inside form-coupon.php, which placed it below the
 * login prompt.
 */
function matrix_rd_checkout_render_heading(): void {
    echo '<div class="flex flex-col justify-start px-0 mx-auto text-left max-w-max-1568">';
    echo '<div class="flex flex-col mb-4">';
    echo '<h1 class="mb-2 text-black-full text-xl-font font-reg420">' . esc_html__('Check out', 'rolling-donut') . '</h1>';
    echo '</div>';
    echo '</div>';
}
add_action('woocommerce_before_checkout_form', 'matrix_rd_checkout_render_heading', 5);

/**
 * Notices: render after coupon intro (legacy position), not at top of form.
 */
function matrix_rd_checkout_register_notice_hooks(): void {
    remove_action('woocommerce_before_checkout_form', 'woocommerce_output_all_notices', 10);
    remove_action('woocommerce_before_checkout_form', 'woocommerce_output_all_notices', 5);
    add_action('woocommerce_before_checkout_form', 'matrix_rd_checkout_output_notices', 40);
}
add_action('wp', 'matrix_rd_checkout_register_notice_hooks');

/**
 * Keep LPP package metadata valid inside the checkout shipping table.
 */
function matrix_rd_checkout_fix_lpp_shipping_markup(): void {
    if (! function_exists('is_checkout') || ! is_checkout() || is_wc_endpoint_url('order-received')) {
        return;
    }

    if (! function_exists('wc_local_pickup_plus')) {
        return;
    }

    $frontend = wc_local_pickup_plus()->get_frontend_instance();
    if (! $frontend) {
        return;
    }

    $lpp_checkout = $frontend->get_checkout_instance();
    if (! $lpp_checkout) {
        return;
    }

    remove_action('woocommerce_review_order_after_cart_contents', [$lpp_checkout, 'packages_count'], 40);
    add_action('woocommerce_review_order_after_cart_contents', 'matrix_rd_checkout_packages_count', 40);

    // LPP wraps cart-shipping in extra <tr> rows meant for WC table layouts; skip on our checkout table.
    remove_action('woocommerce_before_template_part', [$lpp_checkout, 'add_package_group_row_start'], 10);
    remove_action('woocommerce_after_template_part', [$lpp_checkout, 'add_package_group_row_end'], 10);
}
add_action('wp', 'matrix_rd_checkout_fix_lpp_shipping_markup');

/**
 * Keep Stripe UPE mounted — replacing #payment on updated_checkout wipes card inputs.
 */
function matrix_rd_checkout_preserve_payment_fragment(array $fragments): array {
    unset($fragments['#payment'], $fragments['.woocommerce-checkout-payment']);

    return $fragments;
}
add_filter('woocommerce_update_order_review_fragments', 'matrix_rd_checkout_preserve_payment_fragment', 20);

function matrix_rd_checkout_packages_count(): void {
    static $output = false;

    if ($output || ! WC()->cart || ! WC()->cart->needs_shipping()) {
        return;
    }

    $packages = WC()->shipping()->get_packages();
    if (empty($packages) || ! function_exists('wc_local_pickup_plus_shipping_method_id')) {
        return;
    }

    $shipping_method_id = wc_local_pickup_plus_shipping_method_id();
    $packages_to_ship   = 0;
    $packages_to_pickup = 0;

    foreach ($packages as $package) {
        if (isset($package['ship_via']) && in_array($shipping_method_id, $package['ship_via'], true)) {
            $packages_to_pickup++;
        } else {
            $packages_to_ship++;
        }
    }

    $output = true;
    ?>
    <div class="hidden lpp-packages-count">
        <input type="hidden" id="wc-local-pickup-plus-packages-to-ship" value="<?php echo esc_attr((string) $packages_to_ship); ?>" />
        <input type="hidden" id="wc-local-pickup-plus-packages-to-pickup" value="<?php echo esc_attr((string) $packages_to_pickup); ?>" />
    </div>
    <?php
}

function matrix_rd_checkout_output_notices(): void {
    if (function_exists('wc_print_notices')) {
        wc_print_notices();
    }
}

/**
 * Pay now button (legacy).
 */
function matrix_rd_checkout_order_button_text(): string {
    return __('Pay now', 'matrix-starter');
}
add_filter('woocommerce_order_button_text', 'matrix_rd_checkout_order_button_text');

function matrix_rd_checkout_order_button_html(string $button_html): string {
    $order_button_text = apply_filters('woocommerce_order_button_text', __('Place order', 'woocommerce'));
    $custom_classes    = 'btn text-black-full hover:text-yellow-primary text-mob-lg-font lg:text-sm-md-font font-medium h-[66px] bg-yellow-primary rounded-lg-x w-full rd-border hover:bg-black-primary woocommerce-button button woocommerce-form-login__submit ml-auto mr-auto max-w-max-704 mt-6';
    $wc_button_class   = wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : '';

    return '<button type="submit" class="button alt' . esc_attr($wc_button_class) . ' ' . esc_attr($custom_classes) . '" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr($order_button_text) . '" data-value="' . esc_attr($order_button_text) . '">' . esc_html($order_button_text) . '</button>';
}
add_filter('woocommerce_order_button_html', 'matrix_rd_checkout_order_button_html');

/**
 * Field icon classes (legacy checkout inputs).
 */
function matrix_rd_checkout_address_field_icons(array $address_fields): array {
    $icons = [
        'first_name' => 'icon-first-name',
        'last_name'  => 'icon-last-name',
        'company'    => 'icon-company',
        'address_1'  => 'icon-address-1',
        'address_2'  => 'icon-address-2',
        'city'       => 'icon-city',
        'state'      => 'icon-state',
        'postcode'   => 'icon-postcode',
        'country'    => 'icon-country',
        'phone'      => 'icon-phone',
        'email'      => 'icon-email',
    ];

    foreach ($icons as $key => $class) {
        if (isset($address_fields[$key])) {
            $address_fields[$key]['class'][] = $class;
        }
    }

    return $address_fields;
}
add_filter('woocommerce_default_address_fields', 'matrix_rd_checkout_address_field_icons', 20);

/**
 * Order notes label/placeholder (legacy).
 */
function matrix_rd_checkout_order_notes_placeholder(array $fields): array {
    if (isset($fields['order']['order_comments'])) {
        $fields['order']['order_comments']['placeholder'] = __('Note to delivery driver', 'matrix-starter');
        $fields['order']['order_comments']['label']       = __('Note to delivery driver', 'matrix-starter');
    }

    return $fields;
}
add_filter('woocommerce_checkout_fields', 'matrix_rd_checkout_order_notes_placeholder');

/**
 * Seed shipping address from billing when empty (legacy).
 */
function matrix_rd_checkout_set_default_shipping_address(): void {
    if (! is_checkout() || ! WC()->customer) {
        return;
    }

    if (! empty(WC()->customer->get_shipping_country())) {
        return;
    }

    $customer = WC()->customer;
    $customer->set_shipping_country($customer->get_billing_country());
    $customer->set_shipping_state($customer->get_billing_state());
    $customer->set_shipping_postcode($customer->get_billing_postcode());
    $customer->set_shipping_city($customer->get_billing_city());
    $customer->set_shipping_address_1($customer->get_billing_address_1());
    $customer->set_shipping_address_2($customer->get_billing_address_2());
    $customer->set_calculated_shipping(true);
}
add_action('woocommerce_before_checkout_form', 'matrix_rd_checkout_set_default_shipping_address');

function matrix_rd_checkout_use_billing_for_shipping(array $address, $customer): array {
    if ($customer && empty($customer->get_shipping_country())) {
        $address['country']    = $customer->get_billing_country();
        $address['state']      = $customer->get_billing_state();
        $address['postcode']   = $customer->get_billing_postcode();
        $address['city']       = $customer->get_billing_city();
        $address['address_1']  = $customer->get_billing_address_1();
        $address['address_2']  = $customer->get_billing_address_2();
    }

    return $address;
}
add_filter('woocommerce_shipping_address', 'matrix_rd_checkout_use_billing_for_shipping', 10, 2);

/**
 * Local Pickup Plus field markup tweaks (legacy).
 */
function matrix_rd_checkout_pickup_field_strip_br(string $field_html, $package_id, $package): string {
    return str_replace('<br />', '', $field_html);
}
add_filter('wc_local_pickup_plus_get_pickup_location_package_field_html', 'matrix_rd_checkout_pickup_field_strip_br', 10, 3);

function matrix_rd_checkout_pickup_field_collect_at(string $field_html, $package_id, $package): string {
    $field_html = str_replace(
        '<div class="pickup-location-address">',
        '<div class="pickup-location-address"><strong>Collect at:</strong> ',
        $field_html
    );

    $field_html = str_replace(
        ['Search locations&hellip;', 'Search locations…'],
        'Select a Pickup location',
        $field_html
    );

    return $field_html;
}
add_filter('wc_local_pickup_plus_get_pickup_location_package_field_html', 'matrix_rd_checkout_pickup_field_collect_at', 10, 3);

/**
 * Iconic delivery slots: preserve selected time across county/AJAX refresh.
 */
function matrix_rd_checkout_save_delivery_slots_to_session(): void {
    if (! WC()->session) {
        return;
    }

    if (isset($_POST['jckwds-delivery-time'])) {
        WC()->session->set('iconic_delivery_time', sanitize_text_field(wp_unslash($_POST['jckwds-delivery-time'])));
    }

    if (isset($_POST['jckwds-delivery-date'])) {
        WC()->session->set('iconic_delivery_date', sanitize_text_field(wp_unslash($_POST['jckwds-delivery-date'])));
    }

    if (isset($_POST['jckwds-delivery-date-ymd'])) {
        WC()->session->set('iconic_delivery_date_ymd', sanitize_text_field(wp_unslash($_POST['jckwds-delivery-date-ymd'])));
    }
}
add_action('woocommerce_checkout_update_order_review', 'matrix_rd_checkout_save_delivery_slots_to_session');

function matrix_rd_checkout_restore_delivery_slots_from_session(): void {
    if (! WC()->session) {
        return;
    }

    $delivery_time     = WC()->session->get('iconic_delivery_time');
    $delivery_date     = WC()->session->get('iconic_delivery_date');
    $delivery_date_ymd = WC()->session->get('iconic_delivery_date_ymd');

    if (empty($delivery_time) && empty($delivery_date) && empty($delivery_date_ymd)) {
        return;
    }
    ?>
    <script>
      jQuery(function ($) {
        var saved = <?php echo wp_json_encode([
            'time' => $delivery_time,
            'date' => $delivery_date,
            'dateYmd' => $delivery_date_ymd,
        ]); ?>;

        function restoreDeliverySlots() {
          if (saved.dateYmd && !$('#jckwds-delivery-date-ymd').val()) {
            $('#jckwds-delivery-date-ymd').val(saved.dateYmd);
          }
          if (saved.date && !$('#jckwds-delivery-date').val()) {
            if (window.jckwds && typeof window.jckwds.set_date === 'function') {
              window.jckwds.vars = window.jckwds.vars || {};
              window.jckwds.vars.set_date_flag = true;
              window.jckwds.set_date(saved.date);
            } else {
              $('#jckwds-delivery-date').val(saved.date);
            }
          }
          if (saved.time && !$('#jckwds-delivery-time').val()) {
            $('#jckwds-delivery-time').val(saved.time);
          }
        }

        restoreDeliverySlots();
        $(document.body).on('updated_checkout', restoreDeliverySlots);
      });
    </script>
    <?php
}
add_action('woocommerce_after_checkout_form', 'matrix_rd_checkout_restore_delivery_slots_from_session');

/**
 * Stripe flat appearance (legacy).
 */
function matrix_rd_checkout_stripe_upe_params(array $stripe_params): array {
    $stripe_params['appearance'] = (object) [
        'theme' => 'flat',
    ];

    return $stripe_params;
}
add_filter('wc_stripe_upe_params', 'matrix_rd_checkout_stripe_upe_params');

/**
 * Checkout footer scripts (legacy).
 */
function matrix_rd_checkout_footer_scripts(): void {
    if (! is_checkout()) {
        return;
    }
    ?>
    <script>
      jQuery(function ($) {
        $('#shipping_phone_field label, #shipping_email_field label').removeClass('screen-reader-text');

        function setPickupLocationPlaceholder() {
          $('#rd-checkout-step-method select.pickup-location-lookup').each(function () {
            var $pickupSelect = $(this);

            if ($pickupSelect.hasClass('select2-hidden-accessible')) {
              $pickupSelect.select2('destroy');
            }

            $pickupSelect.find('option').filter(function () {
              return !$(this).attr('data-placeholder') && !$(this).val();
            }).remove();

            if ($pickupSelect.find('option[data-placeholder="true"]').length === 0) {
              $pickupSelect.prepend('<option value="" disabled selected data-placeholder="true">Select a Pickup location</option>');
            }

            if ($pickupSelect.hasClass('wc-enhanced-select')) {
              $pickupSelect.select2({
                placeholder: 'Select a Pickup location',
                allowClear: false,
                width: '100%'
              });
            }
          });
        }

        function bindPickupLocationAddress() {
          $('select.pickup-location-lookup').off('change.rdPickup').on('change.rdPickup', function () {
            var $pickupSelect = $(this);
            var selectedOption = $pickupSelect.find('option:selected');
            var formattedAddress = selectedOption.data('address-formatted') || '';
            var $addressDiv = $pickupSelect.closest('.pickup-location-field').find('.pickup-location-address');

            if (selectedOption.val() === '' || selectedOption.data('placeholder')) {
              $addressDiv.hide();
            } else {
              $addressDiv.html('<strong>Collect at:</strong> ' + formattedAddress);
              $addressDiv.show();
            }
          });
        }

        setPickupLocationPlaceholder();
        bindPickupLocationAddress();
        $('select.pickup-location-lookup').trigger('change.rdPickup');

        $(document.body).on('updated_checkout wc_local_pickup_plus_ready wc_local_pickup_plus_after_locations_html', function () {
          setPickupLocationPlaceholder();
          bindPickupLocationAddress();
          $('select.pickup-location-lookup').trigger('change.rdPickup');
        });

        function primeStripeCheckoutFields() {
          if (!$('#billing_country').val()) {
            $('#billing_country').val($('#shipping_country').val() || 'IE');
          }

          $('#wc-stripe-payment-token-new').prop('checked', true);
        }

        primeStripeCheckoutFields();

        $(document.body).on('updated_checkout', function () {
          var $payment = $('#payment.woocommerce-checkout-payment');
          if (!$payment.length) {
            return;
          }

          primeStripeCheckoutFields();
          $payment.show();
          $payment.find('.payment_box.payment_method_stripe, #wc-stripe-upe-form, .wc-stripe-upe-element').show();

          // WooCommerce blocks .woocommerce-checkout-payment at the start of
          // update_order_review and only unblocks fragment keys returned in the
          // AJAX response. Because matrix_rd_checkout_preserve_payment_fragment()
          // strips the #payment fragment (to keep the Stripe element mounted),
          // that unblock never runs and a stuck blockUI overlay sits over the
          // Stripe card iframe — invisible due to our 0.12 opacity rule — and
          // swallows every click. Clear it here once the update has settled.
          $payment.unblock();
          $payment.find('.blockUI.blockOverlay').remove();
        });
      });
    </script>
    <?php
}
add_action('wp_footer', 'matrix_rd_checkout_footer_scripts', 100);

add_filter('body_class', function (array $classes): array {
    if (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('order-received')) {
        $classes[] = 'tw-thankyou';
    }

    return $classes;
});
