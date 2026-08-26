<?php
/**
 * Rolling Donut checkout — legacy Sage / mu-plugin parity.
 */

require_once __DIR__ . '/rolling-donut-custom-checkout.php';
require_once __DIR__ . '/rolling-donut-checkout-notices.php';
require_once __DIR__ . '/rolling-donut-express-checkout.php';
require_once __DIR__ . '/rolling-donut-timeslot-guard.php';

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

    if (wp_script_is('wc-checkout', 'registered') || wp_script_is('wc-checkout', 'enqueued')) {
        wp_enqueue_script('wc-checkout');
        wp_add_inline_script(
            'wc-checkout',
            <<<'JS'
(function ($) {
  function couponNoticeTarget($input) {
    return $input.closest('.rd-checkout-payment-coupon__row');
  }

  function clearPaymentCouponError($input) {
    var $wrap = $input.closest('.rd-checkout-payment-coupon');
    $wrap.find('.rd-checkout-payment-coupon__error').remove();
    $input
      .removeClass('has-error')
      .removeAttr('aria-invalid')
      .removeAttr('aria-describedby');
  }

  function showPaymentCouponError($input, htmlOrText) {
    clearPaymentCouponError($input);
    var msg = $('<div>').html(htmlOrText || '').text().trim();
    if (!msg) {
      msg = 'Unable to apply that coupon. Please try again.';
    }
    $input
      .addClass('has-error')
      .attr('aria-invalid', 'true')
      .attr('aria-describedby', 'rd-payment-coupon-error')
      .trigger('focus');
    $('<p>', {
      id: 'rd-payment-coupon-error',
      class: 'rd-checkout-payment-coupon__error',
      role: 'alert',
      text: msg,
    }).insertAfter(couponNoticeTarget($input));
  }

  function flagPaymentRefresh() {
    var $checkoutForm = $('form.checkout');
    if (!$checkoutForm.length) {
      return;
    }
    if (!$checkoutForm.find('input[name="rd_refresh_payment"]').length) {
      $checkoutForm.append('<input type="hidden" name="rd_refresh_payment" id="rd_refresh_payment" value="1" />');
    } else {
      $checkoutForm.find('input[name="rd_refresh_payment"]').val('1');
    }
  }

  function applyPaymentCoupon() {
    var $input = $('#rd_payment_coupon_code');
    if (!$input.length) {
      return;
    }

    var code = String($input.val() || '').trim();
    if (!code) {
      showPaymentCouponError($input, 'Please enter a coupon or gift voucher code.');
      return;
    }

    clearPaymentCouponError($input);

    // Prefer WooCommerce AJAX directly — the visible field lives in #payment and
    // must not depend on the legacy hidden form.checkout_coupon (often removed /
    // display:none / not wired after fragment refresh).
    if (typeof wc_checkout_params === 'undefined' || !wc_checkout_params.wc_ajax_url) {
      var $legacy = $('form.checkout_coupon');
      if ($legacy.length) {
        $legacy.find('#coupon_code').val(code);
        $legacy.trigger('submit');
      } else {
        showPaymentCouponError($input, 'Coupon applying is unavailable. Please refresh and try again.');
      }
      return;
    }

    var $box = $input.closest('.rd-checkout-payment-coupon');
    if ($box.hasClass('processing')) {
      return;
    }

    $box.addClass('processing');
    if ($.fn.block) {
      $box.block({
        message: null,
        overlayCSS: { background: '#fff', opacity: 0.6 },
      });
    }

    $.ajax({
      type: 'POST',
      url: wc_checkout_params.wc_ajax_url
        .toString()
        .replace('%%endpoint%%', 'apply_coupon'),
      data: {
        security: wc_checkout_params.apply_coupon_nonce,
        coupon_code: code,
        billing_email: $('form.checkout input[name="billing_email"]').val() || '',
      },
      dataType: 'html',
      success: function (response) {
        $box.removeClass('processing');
        if ($.fn.unblock) {
          $box.unblock();
        }

        $('.woocommerce-NoticeGroup-checkout .woocommerce-error, .woocommerce-NoticeGroup-checkout .woocommerce-message, .rd-checkout-payment-coupon .woocommerce-error, .rd-checkout-payment-coupon .woocommerce-message, .rd-checkout-payment-coupon .woocommerce-info').remove();

        var isError =
          !response ||
          response.indexOf('woocommerce-error') !== -1 ||
          response.indexOf('is-error') !== -1;

        if (isError) {
          showPaymentCouponError($input, response);
        } else {
          clearPaymentCouponError($input);
          $input.val('');
          if (response) {
            // Surface success near the payment coupon UI (not the legacy form).
            $box.prepend(response);
          }
          // Flag before applied_coupon_in_checkout so the follow-up
          // update_order_review serializes with a payment-fragment rebuild.
          flagPaymentRefresh();

          // Only fire "applied" on success — the listener clears inline errors.
          $(document.body).trigger('applied_coupon_in_checkout', [code]);
        }

        $(document.body).trigger('update_checkout', {
          update_shipping_method: false,
        });
      },
      error: function () {
        $box.removeClass('processing');
        if ($.fn.unblock) {
          $box.unblock();
        }
        showPaymentCouponError($input, 'Unable to apply that coupon. Please try again.');
      },
    });
  }

  function removePaymentCoupon(code) {
    code = String(code || '').trim();
    if (!code) {
      return;
    }

    if (typeof wc_checkout_params === 'undefined' || !wc_checkout_params.wc_ajax_url) {
      return;
    }

    var $box = $('.rd-checkout-payment-coupon').first();
    if ($box.hasClass('processing')) {
      return;
    }

    $box.addClass('processing');
    if ($.fn.block) {
      $box.block({
        message: null,
        overlayCSS: { background: '#fff', opacity: 0.6 },
      });
    }

    $.ajax({
      type: 'POST',
      url: wc_checkout_params.wc_ajax_url
        .toString()
        .replace('%%endpoint%%', 'remove_coupon'),
      data: {
        security: wc_checkout_params.remove_coupon_nonce,
        coupon: code,
      },
      dataType: 'html',
      success: function () {
        $box.removeClass('processing');
        if ($.fn.unblock) {
          $box.unblock();
        }

        flagPaymentRefresh();
        $(document.body).trigger('removed_coupon_in_checkout', [code]);
        $(document.body).trigger('update_checkout', {
          update_shipping_method: false,
        });
      },
      error: function () {
        $box.removeClass('processing');
        if ($.fn.unblock) {
          $box.unblock();
        }
        $(document.body).trigger('update_checkout', {
          update_shipping_method: false,
        });
      },
    });
  }

  $(document.body).on('click', '.rd-checkout-payment-coupon__apply', function (e) {
    e.preventDefault();
    applyPaymentCoupon();
  });

  $(document.body).on('click', '.rd-checkout-payment-coupon__remove', function (e) {
    e.preventDefault();
    e.stopPropagation();
    removePaymentCoupon($(this).data('coupon'));
  });

  $(document.body).on('keydown', '#rd_payment_coupon_code', function (e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      applyPaymentCoupon();
    }
  });

  $(document.body).on('input change', '#rd_payment_coupon_code', function () {
    clearPaymentCouponError($(this));
  });

  $(document.body).on('applied_coupon applied_coupon_in_checkout', function () {
    var $input = $('#rd_payment_coupon_code');
    if ($input.length) {
      $input.val('');
      clearPaymentCouponError($input);
    }
  });
})(jQuery);
JS
            ,
            'after'
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
    $logos    = function_exists('matrix_rd_nav_logos') ? matrix_rd_nav_logos() : [];
    $logo_url = is_array($logos) ? (string) ($logos['main'] ?? '') : '';
    $logo_alt = is_array($logos) && ! empty($logos['main_alt'])
        ? (string) $logos['main_alt']
        : __('The Rolling Donut logo', 'matrix-starter');

    echo '<div class="flex flex-col justify-start px-0 mx-auto text-left max-w-max-1568">';
    // Heading row: the "Check out" title sits on the left while the logo is
    // absolutely centred, so it stays in the middle of the row in line with the
    // title regardless of the heading width.
    echo '<div class="rd-checkout-heading-row">';
    echo '<h1 class="rd-checkout-heading-title text-black-full text-xl-font font-reg420">' . esc_html__('Checkout', 'rolling-donut') . '</h1>';
    if ($logo_url !== '') {
        echo '<a href="' . esc_url(home_url('/')) . '" class="rd-checkout-heading-logo" aria-label="' . esc_attr__('The Rolling Donut — home', 'matrix-starter') . '">';
        echo '<img src="' . esc_url($logo_url) . '" alt="' . esc_attr($logo_alt) . '" />';
        echo '</a>';
    }
    echo '</div>';
    echo '</div>';
}

/**
 * Layout for the checkout heading row (title left, logo centred in line).
 */
function matrix_rd_checkout_heading_styles(): void {
    if (! function_exists('is_checkout') || ! is_checkout() || is_wc_endpoint_url('order-received')) {
        return;
    }
    ?>
    <style>
        .rd-checkout-heading-row {
            position: relative;
            display: flex;
            align-items: center;
            min-height: 6rem;
            margin-bottom: 1rem;
        }
        .rd-checkout-heading-title {
            margin: 0;
        }
        .rd-checkout-heading-logo {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            display: inline-flex;
            line-height: 0;
        }
        .rd-checkout-heading-logo img {
            height: 6rem;
            width: auto;
            display: block;
        }
        @media (max-width: 575px) {
            .rd-checkout-heading-logo img {
                height: 4.5rem;
            }
            .rd-checkout-heading-row {
                min-height: 4.5rem;
            }
        }
    </style>
    <?php
}
add_action('wp_head', 'matrix_rd_checkout_heading_styles');
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
 * Whether this order-review refresh must rebuild #payment (coupon apply/remove).
 * Stripe UPE has to remount against the new total; preserving the old fragment
 * leaves an empty payment box and checkout then fails with "Invalid payment method."
 */
function matrix_rd_checkout_should_refresh_payment_fragment(): bool {
    $post_data = isset($_POST['post_data']) ? wp_unslash($_POST['post_data']) : '';

    if (! is_string($post_data) || $post_data === '') {
        return false;
    }

    parse_str($post_data, $parsed);

    if (! is_array($parsed)) {
        return false;
    }

    return ! empty($parsed['rd_refresh_payment']);
}

/**
 * Keep Stripe UPE mounted on ordinary address/shipping refreshes — replacing
 * #payment wipes card inputs. After a coupon, allow the fragment through.
 */
function matrix_rd_checkout_preserve_payment_fragment(array $fragments): array {
    if (matrix_rd_checkout_should_refresh_payment_fragment()) {
        return $fragments;
    }

    unset($fragments['#payment'], $fragments['.woocommerce-checkout-payment']);

    return $fragments;
}
add_filter('woocommerce_update_order_review_fragments', 'matrix_rd_checkout_preserve_payment_fragment', 99);

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
    if (WC()->cart && ! WC()->cart->needs_payment()) {
        return __('Get it for Free!', 'matrix-starter');
    }

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
 * Phone fields: tel input + pattern hint (letters stripped via checkout JS).
 */
function matrix_rd_checkout_phone_field_attributes(array $address_fields): array {
    if (! isset($address_fields['phone'])) {
        return $address_fields;
    }

    $address_fields['phone']['type'] = 'tel';
    $address_fields['phone']['custom_attributes'] = array_merge(
        (array) ($address_fields['phone']['custom_attributes'] ?? []),
        [
            'inputmode' => 'tel',
            'pattern'   => '[0-9+\\s()-]*',
            'title'     => __('Enter numbers only', 'matrix-starter'),
        ]
    );

    return $address_fields;
}
add_filter('woocommerce_default_address_fields', 'matrix_rd_checkout_phone_field_attributes', 25);

/**
 * Strip non-phone characters before WooCommerce validates checkout.
 */
function matrix_rd_checkout_sanitize_phone_post_data(): void {
    foreach (array('billing_phone', 'shipping_phone') as $field) {
        if (empty($_POST[ $field ])) {
            continue;
        }

        $_POST[ $field ] = preg_replace('/[^\d+\s()-]/', '', wp_unslash((string) $_POST[ $field ]));
    }
}
add_action('woocommerce_checkout_process', 'matrix_rd_checkout_sanitize_phone_post_data', 5);

/**
 * Order notes label/placeholder (legacy).
 */
function matrix_rd_checkout_order_notes_placeholder(array $fields): array {
    if (isset($fields['order']['order_comments'])) {
        $fields['order']['order_comments']['placeholder'] = __('Note to delivery driver', 'matrix-starter');
        $fields['order']['order_comments']['label']       = __('Note to delivery driver', 'matrix-starter');
        $fields['order']['order_comments']['class'][]     = 'icon-order-note';
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

/**
 * Whether a shipping rate ID is collection / local pickup.
 */
function matrix_rd_checkout_is_pickup_rate_id(string $rate_id): bool {
    if ($rate_id === '') {
        return false;
    }

    if (false !== strpos($rate_id, 'local_pickup')) {
        return true;
    }

    $pickup_id = function_exists('wc_local_pickup_plus_shipping_method_id')
        ? wc_local_pickup_plus_shipping_method_id()
        : 'local_pickup_plus';

    return current(explode(':', $rate_id)) === $pickup_id;
}

/**
 * First non-pickup rate ID from a package rates array.
 *
 * @param array<string, WC_Shipping_Rate> $rates Package rates.
 */
function matrix_rd_checkout_first_delivery_rate_id(array $rates): string {
    foreach (array_keys($rates) as $rate_id) {
        if (! matrix_rd_checkout_is_pickup_rate_id((string) $rate_id)) {
            return (string) $rate_id;
        }
    }

    return '';
}

/**
 * Prefer delivery when WooCommerce picks the default shipping method.
 *
 * @param string                           $default        Proposed default rate ID.
 * @param array<string, WC_Shipping_Rate>  $rates          Available rates.
 * @param string                           $chosen_method  Session chosen method.
 */
function matrix_rd_checkout_prefer_delivery_shipping_method(string $default, array $rates, string $chosen_method): string {
    if (! is_checkout() || is_wc_endpoint_url('order-received') || empty($rates)) {
        return $default;
    }

    if ($chosen_method && matrix_rd_checkout_is_pickup_rate_id($chosen_method) && isset($rates[$chosen_method])) {
        return $chosen_method;
    }

    if ($default && ! matrix_rd_checkout_is_pickup_rate_id($default)) {
        return $default;
    }

    $delivery = matrix_rd_checkout_first_delivery_rate_id($rates);

    return $delivery !== '' ? $delivery : $default;
}
add_filter('woocommerce_shipping_chosen_method', 'matrix_rd_checkout_prefer_delivery_shipping_method', 10, 3);

/**
 * On checkout load, default to delivery instead of collection.
 */
function matrix_rd_checkout_ensure_delivery_default_shipping(): void {
    if (! is_checkout() || is_wc_endpoint_url('order-received') || ! WC()->session || ! WC()->cart) {
        return;
    }

    if (! WC()->cart->needs_shipping()) {
        return;
    }

    static $done = false;

    if ($done) {
        return;
    }

    $done = true;

    WC()->cart->calculate_shipping();

    $packages = WC()->shipping()->get_packages();

    if (empty($packages[0]['rates'])) {
        return;
    }

    $delivery = matrix_rd_checkout_first_delivery_rate_id($packages[0]['rates']);

    if ($delivery === '') {
        return;
    }

    $chosen = WC()->session->get('chosen_shipping_methods', []);

    if (! is_array($chosen)) {
        $chosen = [];
    }

    $current = (string) ($chosen[0] ?? '');

    if ($current === '' || matrix_rd_checkout_is_pickup_rate_id($current)) {
        $chosen[0] = $delivery;
        WC()->session->set('chosen_shipping_methods', $chosen);
    }
}
add_action('woocommerce_before_checkout_form', 'matrix_rd_checkout_ensure_delivery_default_shipping', 15);

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
 * One labelled placeholder option for pickup location selects (mobile + Select2).
 */
function matrix_rd_checkout_pickup_select_normalize_options(string $field_html, $package_id, $package): string {
    if (strpos($field_html, 'pickup-location-lookup') === false) {
        return $field_html;
    }

    $placeholder = esc_html__('Select a collection location', 'matrix-starter');

    $field_html = preg_replace('/<option[^>]*data-placeholder=["\']true["\'][^>]*>.*?<\/option>/is', '', $field_html);
    $field_html = preg_replace('/<option(?![^>]*\bvalue=)[^>]*>\s*<\/option>/i', '', $field_html);
    $field_html = preg_replace('/<option[^>]*value=["\']?["\'][^>]*>\s*<\/option>/i', '', $field_html);

    if (strpos($field_html, 'data-placeholder="true"') === false) {
        $field_html = preg_replace(
            '/(<select[^>]*class="[^"]*pickup-location-lookup[^"]*"[^>]*>)/i',
            '$1<option value="" data-placeholder="true">' . $placeholder . '</option>',
            $field_html,
            1
        );
    }

    $field_html = str_replace(
        'data-placeholder="' . esc_attr__('Search locations&hellip;', 'woocommerce-shipping-local-pickup-plus') . '"',
        'data-placeholder="' . esc_attr__('Select a collection location', 'matrix-starter') . '"',
        $field_html
    );

    return $field_html;
}
add_filter('wc_local_pickup_plus_get_pickup_location_package_field_html', 'matrix_rd_checkout_pickup_select_normalize_options', 20, 3);

/**
 * Resolve a human-readable timeslot label from Iconic WDS value/id.
 */
function matrix_rd_checkout_resolve_timeslot_label(string $time_value): string {
    $time_value = trim($time_value);

    if ($time_value === '') {
        return '';
    }

    if (preg_match('/\d{1,2}:\d{2}/', $time_value)) {
        return $time_value;
    }

    if (function_exists('iconic_wds')) {
        $wds = iconic_wds();

        if ($wds && method_exists($wds, 'get_timeslot_data')) {
            $data = $wds->get_timeslot_data($time_value);

            if (! empty($data['formatted'])) {
                return (string) $data['formatted'];
            }
        }
    }

    return $time_value;
}

/**
 * Selected delivery/collection date and time for checkout summaries.
 *
 * @return array{date: string, time: string}
 */
function matrix_rd_checkout_get_schedule_summary_parts(): array {
    $date = '';
    $time = '';

    if (WC()->session) {
        $date = (string) WC()->session->get('iconic_delivery_date', '');
        $time = (string) WC()->session->get('iconic_delivery_time_label', '');

        if ($time === '') {
            $time = matrix_rd_checkout_resolve_timeslot_label((string) WC()->session->get('iconic_delivery_time', ''));
        }
    }

    return [
        'date' => trim($date),
        'time' => trim($time),
    ];
}

/**
 * Resolve a pickup location display name from its ID.
 */
function matrix_rd_checkout_resolve_pickup_location_name(int $location_id): string {
    if ($location_id <= 0 || ! function_exists('wc_local_pickup_plus_get_pickup_location')) {
        return '';
    }

    $location = wc_local_pickup_plus_get_pickup_location($location_id);

    if (! $location || ! is_object($location) || ! method_exists($location, 'get_name')) {
        return '';
    }

    $name = trim((string) $location->get_name());

    if ($name !== '') {
        return $name;
    }

    if (method_exists($location, 'get_formatted_name')) {
        return trim((string) $location->get_formatted_name());
    }

    return '';
}

/**
 * Selected pickup store name for checkout summaries.
 */
function matrix_rd_checkout_get_selected_pickup_location_name(): string {
    if (! WC()->session) {
        return '';
    }

    $location_id = 0;
    $post_data   = isset($_POST['post_data']) && is_string($_POST['post_data'])
        ? wp_unslash($_POST['post_data'])
        : '';
    $parsed      = matrix_rd_checkout_parse_order_review_post_data($post_data);

    if (! empty($parsed['_shipping_method_pickup_location_id'])) {
        $ids         = $parsed['_shipping_method_pickup_location_id'];
        $location_id = is_array($ids) ? (int) reset($ids) : (int) $ids;
    }

    if ($location_id <= 0 && function_exists('wc_local_pickup_plus')) {
        $plugin = wc_local_pickup_plus();

        if ($plugin && method_exists($plugin, 'get_session_instance')) {
            $pickup_data = $plugin->get_session_instance()->get_package_pickup_data(0);

            if (is_array($pickup_data) && ! empty($pickup_data['pickup_location_id'])) {
                $location_id = (int) $pickup_data['pickup_location_id'];
            }
        }
    }

    if ($location_id <= 0) {
        $location_id = (int) WC()->session->get('matrix_rd_pickup_location_id', 0);
    }

    $name = $location_id > 0 ? matrix_rd_checkout_resolve_pickup_location_name($location_id) : '';

    if ($name === '') {
        $name = trim((string) WC()->session->get('matrix_rd_pickup_location_name', ''));
    }

    return $name;
}

/**
 * Default seed Eircode used before the customer enters their own.
 */
function matrix_rd_checkout_get_default_seed_eircode(): string {
    return 'D01 F5P2';
}

/**
 * Strip zone suffixes like "(Dublin only)" from shipping rate labels.
 */
function matrix_rd_checkout_strip_rate_label_suffix(string $label): string {
    $clean = trim(preg_replace('/\s*\([^)]*\)\s*/', '', $label));

    return $clean !== '' ? $clean : trim($label);
}

/**
 * Format delivery method label with the customer's Eircode when available.
 */
function matrix_rd_checkout_format_delivery_method_label(string $rate_label): string {
    $base_label = matrix_rd_checkout_strip_rate_label_suffix($rate_label);
    $eircode    = matrix_rd_checkout_get_delivery_eircode();

    if ($eircode !== '') {
        return $base_label . ' (' . $eircode . ')';
    }

    return $base_label;
}

/**
 * Selected delivery Eircode for checkout summaries.
 */
function matrix_rd_checkout_get_delivery_eircode(): string {
    $default_seed = matrix_rd_checkout_get_default_seed_eircode();
    $eircode      = '';

    $post_data = isset($_POST['post_data']) && is_string($_POST['post_data'])
        ? wp_unslash($_POST['post_data'])
        : '';
    $parsed    = matrix_rd_checkout_parse_order_review_post_data($post_data);

    if (! empty($parsed['custom_shipping_eircode'])) {
        $eircode = trim(sanitize_text_field((string) $parsed['custom_shipping_eircode']));
    } elseif (! empty($parsed['shipping_postcode'])) {
        $eircode = trim(sanitize_text_field((string) $parsed['shipping_postcode']));
    } elseif (! empty($parsed['billing_postcode'])) {
        $eircode = trim(sanitize_text_field((string) $parsed['billing_postcode']));
    }

    if ($eircode === '' && WC()->session) {
        $eircode = trim((string) WC()->session->get('matrix_rd_delivery_eircode', ''));
    }

    if ($eircode === '' && WC()->customer) {
        $eircode = trim((string) WC()->customer->get_shipping_postcode());

        if ($eircode === '') {
            $eircode = trim((string) WC()->customer->get_billing_postcode());
        }
    }

    if ($eircode === $default_seed) {
        return '';
    }

    return $eircode;
}

/**
 * Parse checkout post_data from AJAX order-review updates.
 *
 * @return array<string, mixed>
 */
function matrix_rd_checkout_parse_order_review_post_data($post_data = ''): array {
    $parsed = [];

    if (is_string($post_data) && $post_data !== '') {
        parse_str($post_data, $parsed);
    }

    return is_array($parsed) ? $parsed : [];
}

/**
 * Iconic delivery slots: preserve selected time across county/AJAX refresh.
 *
 * @param string $post_data Serialized checkout field data from AJAX.
 */
function matrix_rd_checkout_save_delivery_slots_to_session($post_data = ''): void {
    if (! WC()->session) {
        return;
    }

    $data = matrix_rd_checkout_parse_order_review_post_data($post_data);

    if (isset($data['jckwds-delivery-time'])) {
        $time = sanitize_text_field((string) $data['jckwds-delivery-time']);
        WC()->session->set('iconic_delivery_time', $time);
        WC()->session->set('iconic_delivery_time_label', matrix_rd_checkout_resolve_timeslot_label($time));
    }

    if (isset($data['jckwds-delivery-date'])) {
        WC()->session->set('iconic_delivery_date', sanitize_text_field((string) $data['jckwds-delivery-date']));
    }

    if (isset($data['jckwds-delivery-date-ymd'])) {
        WC()->session->set('iconic_delivery_date_ymd', sanitize_text_field((string) $data['jckwds-delivery-date-ymd']));
    }
}
add_action('woocommerce_checkout_update_order_review', 'matrix_rd_checkout_save_delivery_slots_to_session');

/**
 * Preserve selected pickup location across checkout AJAX refreshes.
 *
 * @param string $post_data Serialized checkout field data from AJAX.
 */
function matrix_rd_checkout_save_pickup_location_to_session($post_data = ''): void {
    if (! WC()->session) {
        return;
    }

    $data = matrix_rd_checkout_parse_order_review_post_data($post_data);

    if (empty($data['_shipping_method_pickup_location_id'])) {
        return;
    }

    $ids         = $data['_shipping_method_pickup_location_id'];
    $location_id = is_array($ids) ? (int) reset($ids) : (int) $ids;

    if ($location_id <= 0) {
        return;
    }

    WC()->session->set('matrix_rd_pickup_location_id', $location_id);
    WC()->session->set(
        'matrix_rd_pickup_location_name',
        matrix_rd_checkout_resolve_pickup_location_name($location_id)
    );
}
add_action('woocommerce_checkout_update_order_review', 'matrix_rd_checkout_save_pickup_location_to_session');

/**
 * Preserve entered delivery Eircode across checkout AJAX refreshes.
 *
 * @param string $post_data Serialized checkout field data from AJAX.
 */
function matrix_rd_checkout_save_delivery_eircode_to_session($post_data = ''): void {
    if (! WC()->session) {
        return;
    }

    $data         = matrix_rd_checkout_parse_order_review_post_data($post_data);
    $default_seed = matrix_rd_checkout_get_default_seed_eircode();
    $eircode      = '';

    if (! empty($data['custom_shipping_eircode'])) {
        $eircode = trim(sanitize_text_field((string) $data['custom_shipping_eircode']));
    } elseif (! empty($data['shipping_postcode'])) {
        $eircode = trim(sanitize_text_field((string) $data['shipping_postcode']));
    } elseif (! empty($data['billing_postcode'])) {
        $eircode = trim(sanitize_text_field((string) $data['billing_postcode']));
    }

    if ($eircode === '' || $eircode === $default_seed) {
        return;
    }

    WC()->session->set('matrix_rd_delivery_eircode', $eircode);
}
add_action('woocommerce_checkout_update_order_review', 'matrix_rd_checkout_save_delivery_eircode_to_session');

/**
 * Preserve phone and email across checkout AJAX refreshes.
 *
 * @param string $post_data Serialized checkout field data from AJAX.
 */
function matrix_rd_checkout_save_contact_to_session($post_data = ''): void {
    if (! WC()->session) {
        return;
    }

    $data = matrix_rd_checkout_parse_order_review_post_data($post_data);

    foreach (array('billing_phone', 'billing_email') as $field) {
        if (! empty($data[ $field ])) {
            WC()->session->set('matrix_rd_' . $field, sanitize_text_field((string) $data[ $field ]));
        }
    }
}
add_action('woocommerce_checkout_update_order_review', 'matrix_rd_checkout_save_contact_to_session');

/**
 * Restore saved phone and email after checkout fragment updates.
 */
function matrix_rd_checkout_restore_contact_from_session(): void {
    if (! WC()->session || ! is_checkout()) {
        return;
    }

    $phone = (string) WC()->session->get('matrix_rd_billing_phone', '');
    $email = (string) WC()->session->get('matrix_rd_billing_email', '');

    if ($phone === '' && $email === '') {
        return;
    }
    ?>
    <script>
      jQuery(function ($) {
        var saved = <?php echo wp_json_encode([
            'phone' => $phone,
            'email' => $email,
        ]); ?>;

        function restoreContactFields() {
          if (saved.phone && !$('#billing_phone').val()) {
            $('#billing_phone').val(saved.phone).trigger('change');
          }
          if (saved.email && !$('#billing_email').val()) {
            $('#billing_email').val(saved.email).trigger('change');
          }
        }

        restoreContactFields();
        $(document.body).on('updated_checkout', restoreContactFields);
      });
    </script>
    <?php
}
add_action('woocommerce_after_checkout_form', 'matrix_rd_checkout_restore_contact_from_session');

/**
 * Default checkout countries to Ireland.
 *
 * @return string
 */
function matrix_rd_checkout_default_country(): string {
    return 'IE';
}
add_filter('default_checkout_billing_country', 'matrix_rd_checkout_default_country');
add_filter('default_checkout_shipping_country', 'matrix_rd_checkout_default_country');

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
 * Iconic delivery slots: stop the checkout demanding a time slot that can never
 * be chosen.
 *
 * The store runs date-only collection/delivery: the time-slot field is disabled
 * (timesettings_setup_enable = 0) so no slot UI ever renders. The plugin,
 * however, still has "time slot mandatory" switched on, so every order was
 * rejected at submit with "Please select a time slot." — there is no field for
 * the customer to satisfy it with.
 *
 * Iconic\Checkout::classic_checkout_process() reads $iconic_wds->settings
 * directly (no per-value filter) on woocommerce_checkout_process @ 10, so we
 * relax the mandatory flag at priority 9 — but only while slots are disabled.
 * A chosen date is then enough, which matches the live UI.
 */
function matrix_rd_checkout_relax_timeslot_requirement(): void {
    global $iconic_wds;

    if (! isset($iconic_wds) || ! is_object($iconic_wds) || empty($iconic_wds->settings) || ! is_array($iconic_wds->settings)) {
        return;
    }

    if (! matrix_rd_checkout_should_relax_timeslot($iconic_wds->settings)) {
        return;
    }

    $iconic_wds->settings['timesettings_timesettings_setup_mandatory'] = '0';
}
add_action('woocommerce_checkout_process', 'matrix_rd_checkout_relax_timeslot_requirement', 9);

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

        function sanitizePhoneInputValue(value) {
          return String(value || '').replace(/[^\d+\s()-]/g, '');
        }

        function bindCheckoutPhoneInputs() {
          $('#billing_phone, #shipping_phone').each(function () {
            var cleaned = sanitizePhoneInputValue(this.value);
            if (this.value !== cleaned) {
              this.value = cleaned;
            }
          });
        }

        bindCheckoutPhoneInputs();

        $(document).on('input', '#billing_phone, #shipping_phone', function () {
          var cleaned = sanitizePhoneInputValue(this.value);
          if (this.value !== cleaned) {
            this.value = cleaned;
          }
        });

        $(document).on('paste', '#billing_phone, #shipping_phone', function () {
          var input = this;
          window.setTimeout(function () {
            var cleaned = sanitizePhoneInputValue(input.value);
            if (input.value !== cleaned) {
              input.value = cleaned;
              $(input).trigger('change');
            }
          }, 0);
        });

        $(document.body).on('updated_checkout', bindCheckoutPhoneInputs);

        function setPickupLocationPlaceholder() {
          if ($('.rd-express-checkout-form').length) {
            return;
          }

          $('#rd-checkout-step-method select.pickup-location-lookup').each(function () {
            var $pickupSelect = $(this);
            var placeholder = 'Select a collection location';

            if ($pickupSelect.hasClass('select2-hidden-accessible')) {
              $pickupSelect.select2('destroy');
            }

            $pickupSelect.find('option[data-placeholder="true"]').remove();

            if (!$pickupSelect.find('option[value=""]').length) {
              $pickupSelect.prepend('<option value="" data-placeholder="true">' + placeholder + '</option>');
            }

            $pickupSelect.find('option[value=""]').first().attr('data-placeholder', 'true').text(placeholder);
            $pickupSelect.find('option[value=""]').slice(1).remove();
            $pickupSelect.find('option').filter(function () {
              var value = String($(this).attr('value') || '');
              var text = $.trim($(this).text() || '');
              return (!value || value === '0') && !text;
            }).remove();

            if ($pickupSelect.hasClass('wc-enhanced-select') || $pickupSelect.hasClass('pickup-location-lookup')) {
              $pickupSelect.select2({
                placeholder: placeholder,
                allowClear: true,
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

          var $reviewTable = $('.woocommerce-checkout-review-order-table');
          var $orderReview = $('#order_review');

          if ($reviewTable.length) {
            $reviewTable.unblock();
            $reviewTable.find('.blockUI.blockOverlay').remove();
          }

          if ($orderReview.length) {
            $orderReview.unblock();
            $orderReview.find('.blockUI.blockOverlay').remove();
          }
        });
      });
    </script>
    <?php
}
add_action('wp_footer', 'matrix_rd_checkout_footer_scripts', 100);

/**
 * Strip All-in-One Security Google reCAPTCHA from WooCommerce login / checkout.
 * Live login uses Cloudflare Turnstile from Theme Options instead.
 */
function matrix_rd_checkout_disable_aios_captcha(): void {
    global $aio_wp_security, $wp_filter;

    if (isset($aio_wp_security->captcha_obj)) {
        foreach ([
            'woocommerce_after_checkout_billing_form',
            'woocommerce_login_form',
            'woocommerce_register_form',
            'woocommerce_lostpassword_form',
        ] as $hook) {
            remove_action($hook, [$aio_wp_security->captcha_obj, 'insert_captcha_question_form']);
        }
    }

    $validation_hooks = [
        'woocommerce_after_checkout_validation' => 'aiowps_validate_woo_checkout_captcha',
        'woocommerce_process_login_errors'      => 'aiowps_validate_woo_login_or_reg_captcha',
        'woocommerce_process_registration_errors' => 'aiowps_validate_woo_login_or_reg_captcha',
    ];

    foreach ($validation_hooks as $hook => $method) {
        if (empty($wp_filter[$hook]) || ! is_object($wp_filter[$hook])) {
            continue;
        }

        foreach ($wp_filter[$hook]->callbacks as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                $fn = $callback['function'] ?? null;
                if (is_array($fn) && isset($fn[1]) && $fn[1] === $method) {
                    remove_filter($hook, $fn, (int) $priority);
                }
            }
        }
    }
}
add_action('init', 'matrix_rd_checkout_disable_aios_captcha', 20);

/**
 * Stop All-in-One Security from printing Google reCAPTCHA's API on checkout
 * and My Account. Those screens use live-only Turnstile instead.
 */
function matrix_rd_disable_aios_captcha_footer(): void {
    global $aio_wp_security;

    if (! isset($aio_wp_security) || ! is_object($aio_wp_security)) {
        return;
    }

    $on_checkout = function_exists('is_checkout') && is_checkout();
    $on_account  = function_exists('is_account_page') && is_account_page();
    if (! $on_checkout && ! $on_account) {
        return;
    }

    remove_action('wp_footer', [$aio_wp_security, 'aiowps_footer_content']);
}
add_action('wp', 'matrix_rd_disable_aios_captcha_footer');

add_filter('body_class', function (array $classes): array {
    if (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('order-received')) {
        $classes[] = 'tw-thankyou';
    }

    return $classes;
});
