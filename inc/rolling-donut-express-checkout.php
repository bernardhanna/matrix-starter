<?php
/**
 * Express checkout — hybrid step layout.
 *
 * @package Matrix_Starter
 */

defined('ABSPATH') || exit;

/**
 * Bootstrap express checkout hooks.
 */
function matrix_rd_express_checkout_bootstrap(): void {
    if (! function_exists('is_checkout') || ! is_checkout() || is_wc_endpoint_url('order-received')) {
        return;
    }

    matrix_rd_express_checkout_reposition_iconic_fields();
    matrix_rd_express_checkout_terms_collapse();
    remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
    add_action('woocommerce_after_shipping_rate', 'matrix_rd_express_checkout_prerender_pickup_location_field', 998, 2);
}
add_action('wp', 'matrix_rd_express_checkout_bootstrap');

/**
 * Collapse long terms & conditions content in the payment area.
 */
function matrix_rd_express_checkout_terms_collapse(): void {
    remove_action('woocommerce_checkout_terms_and_conditions', 'wc_terms_and_conditions_page_content', 30);
    add_action('woocommerce_checkout_terms_and_conditions', 'matrix_rd_express_checkout_collapsed_terms', 30);
}

/**
 * Render terms page content inside a collapsible disclosure.
 */
function matrix_rd_express_checkout_collapsed_terms(): void {
    if (! wc_terms_and_conditions_page_id()) {
        return;
    }

    ?>
    <details class="rd-terms-collapse">
        <summary class="rd-terms-collapse__summary">
            <?php esc_html_e('Read terms & conditions', 'matrix-starter'); ?>
        </summary>
        <div class="rd-terms-collapse__content woocommerce-terms-and-conditions">
            <?php wc_terms_and_conditions_page_content(); ?>
        </div>
    </details>
    <?php
}

/**
 * Remove visible shipping postcode — custom Eircode field is used instead.
 *
 * @param array<string, mixed> $fields Checkout fields.
 *
 * @return array<string, mixed>
 */
function matrix_rd_express_checkout_remove_visible_postcode(array $fields): array {
    if (! function_exists('is_checkout') || ! is_checkout() || is_wc_endpoint_url('order-received')) {
        return $fields;
    }

    unset($fields['shipping']['shipping_postcode']);

    return $fields;
}
add_filter('woocommerce_checkout_fields', 'matrix_rd_express_checkout_remove_visible_postcode', 100);

/**
 * Phone and email live in the delivery contact block (billing fields). WooCommerce
 * also outputs shipping_phone on the address via default address fields — hide the
 * duplicates so customers only see one working phone field.
 *
 * @param array<string, mixed> $fields Checkout fields.
 *
 * @return array<string, mixed>
 */
function matrix_rd_express_checkout_hide_shipping_contact_fields(array $fields): array {
    if (! function_exists('is_checkout') || ! is_checkout() || is_wc_endpoint_url('order-received')) {
        return $fields;
    }

    unset($fields['shipping']['shipping_phone'], $fields['shipping']['shipping_email']);

    return $fields;
}
add_filter('woocommerce_checkout_fields', 'matrix_rd_express_checkout_hide_shipping_contact_fields', 101);

/**
 * Hidden shipping postcode synced from custom Eircode for WooCommerce validation.
 */
function matrix_rd_express_checkout_hidden_shipping_postcode(): void {
    $value = WC()->customer ? WC()->customer->get_shipping_postcode() : '';
    ?>
    <input type="hidden" name="shipping_postcode" id="shipping_postcode" value="<?php echo esc_attr($value); ?>" />
    <?php
}
add_action('woocommerce_after_checkout_shipping_form', 'matrix_rd_express_checkout_hidden_shipping_postcode', 5);

/**
 * Copy custom Eircode into shipping_postcode before validation.
 *
 * @param array<string, mixed> $data Posted checkout data.
 *
 * @return array<string, mixed>
 */
function matrix_rd_express_checkout_sync_shipping_postcode(array $data): array {
    if (! empty($data['custom_shipping_eircode'])) {
        $data['shipping_postcode'] = $data['custom_shipping_eircode'];
    }

    return $data;
}
add_filter('woocommerce_checkout_posted_data', 'matrix_rd_express_checkout_sync_shipping_postcode', 15);

/**
 * Friendly help when Iconic has no bookable dates for the chosen method.
 */
function matrix_rd_express_checkout_schedule_unavailable_help(): void {
    ?>
    <div id="rd-schedule-unavailable" class="rd-schedule-unavailable" hidden>
        <p class="rd-schedule-unavailable__title">
            <?php esc_html_e('No dates available right now', 'matrix-starter'); ?>
        </p>
        <p class="rd-schedule-unavailable__text">
            <?php esc_html_e('We may be fully booked for delivery on your chosen day. Try free collection or pick a different delivery method.', 'matrix-starter'); ?>
        </p>
        <div class="rd-schedule-unavailable__actions">
            <button type="button" class="rd-schedule-unavailable__try-collection button">
                <?php esc_html_e('Try free collection', 'matrix-starter'); ?>
            </button>
            <button type="button" class="rd-schedule-unavailable__change-method button alt">
                <?php esc_html_e('Change method', 'matrix-starter'); ?>
            </button>
        </div>
        <p class="rd-schedule-unavailable__contact">
            <?php
            echo wp_kses_post(
                sprintf(
                    /* translators: %s: contact page link */
                    __('Still stuck? %s and we\'ll help.', 'matrix-starter'),
                    '<a href="' . esc_url(home_url('/contact/')) . '">' . esc_html__('Contact us', 'matrix-starter') . '</a>'
                )
            );
            ?>
        </p>
    </div>
    <?php
}
add_action('rd_checkout_step_schedule', 'matrix_rd_express_checkout_schedule_unavailable_help', 20);

/**
 * Use collection labels for Local Pickup Plus in Iconic date fields.
 *
 * @param string        $type  Label type (delivery|collection).
 * @param WC_Order|null $order Order when editing.
 */
function matrix_rd_express_checkout_iconic_label_type(string $type, $order = null): string {
    if ($order instanceof WC_Order) {
        return $type;
    }

    $chosen = WC()->session ? WC()->session->get('chosen_shipping_methods', []) : [];
    $method = is_array($chosen) ? (string) ($chosen[0] ?? '') : '';

    if ($method && false !== strpos($method, 'local_pickup')) {
        return 'collection';
    }

    return $type;
}
add_filter('iconic_wds_get_label_type', 'matrix_rd_express_checkout_iconic_label_type', 10, 2);

/**
 * Move Iconic delivery slot fields into step 2.
 */
function matrix_rd_express_checkout_reposition_iconic_fields(): void {
    global $iconic_wds, $iconic_wds_dates;

    if (! isset($iconic_wds, $iconic_wds_dates) || ! is_object($iconic_wds_dates)) {
        return;
    }

    if (! empty($iconic_wds->has_subscription_product_in_cart()) && $iconic_wds->has_subscription_product_in_cart()) {
        return;
    }

    $position = $iconic_wds->settings['general_setup_position'] ?? 'woocommerce_checkout_order_review';
    $priority = (int) ($iconic_wds->settings['general_setup_position_priority'] ?? 10);

    if ('add_manually' !== $position) {
        remove_action($position, [$iconic_wds_dates, 'display_checkout_fields'], $priority);
    }

    add_action('rd_checkout_step_schedule', [$iconic_wds_dates, 'display_checkout_fields'], 10);
}

/**
 * Enqueue express checkout assets.
 */
function matrix_rd_express_checkout_enqueue_assets(): void {
    if (! function_exists('is_checkout') || ! is_checkout() || is_wc_endpoint_url('order-received')) {
        return;
    }

    $theme_dir = get_template_directory();
    $theme_uri = get_template_directory_uri();

    $css_path = $theme_dir . '/assets/css/rolling-donut-express-checkout.css';
    if (is_readable($css_path)) {
        wp_enqueue_style(
            'matrix-rd-express-checkout',
            $theme_uri . '/assets/css/rolling-donut-express-checkout.css',
            ['matrix-rd-checkout'],
            (string) filemtime($css_path)
        );
    }

    $js_path = $theme_dir . '/assets/js/rolling-donut-express-checkout.js';
    if (is_readable($js_path)) {
        wp_enqueue_script(
            'matrix-rd-express-checkout',
            $theme_uri . '/assets/js/rolling-donut-express-checkout.js',
            ['jquery', 'wc-checkout'],
            (string) filemtime($js_path),
            true
        );

        wp_localize_script('matrix-rd-express-checkout', 'matrixRdExpressCheckout', [
            'pickupMethodId' => function_exists('wc_local_pickup_plus_shipping_method_id')
                ? wc_local_pickup_plus_shipping_method_id()
                : 'local_pickup_plus',
            'wizardMessages' => [
                'selectMethod'    => __('Choose delivery or collection.', 'matrix-starter'),
                'selectPickup'    => __('Choose a pickup location.', 'matrix-starter'),
                'selectPickupPlaceholder' => __('Select a collection location', 'matrix-starter'),
                'pickupLoading'   => __('Please wait… loading collection locations.', 'matrix-starter'),
                'selectDate'      => __('Choose a delivery or collection date.', 'matrix-starter'),
                'noScheduleDates' => __('No dates are available for this method. Try collection or change your delivery method.', 'matrix-starter'),
                'fieldRequired'   => __('%s is required.', 'matrix-starter'),
                'invalidEmail'    => __('Enter a valid email address.', 'matrix-starter'),
                'acceptTerms'     => __('Tick the box to accept the terms and conditions.', 'matrix-starter'),
                'errorsTitle'     => __('What\'s missing', 'matrix-starter'),
            ],
            'mobilePayLabel'   => __('Place Order', 'matrix-starter'),
            'pickupAddresses'  => matrix_rd_express_checkout_pickup_addresses(),
        ]);
    }
}
add_action('wp_enqueue_scripts', 'matrix_rd_express_checkout_enqueue_assets', 35);

/**
 * Build a map of pickup-location ID => short address line ("street, city") for
 * the checkout JS. Used to show the address beneath each location name in the
 * pickup dropdown (Local Pickup Plus renders a second <small> line from each
 * option's data-address). Addresses are resolved via get_address(), which the
 * matrix_rd_pickup_location_address_from_meta() filter hydrates from legacy meta.
 *
 * @return array<int,string>
 */
function matrix_rd_express_checkout_pickup_addresses(): array {
    if (! function_exists('wc_local_pickup_plus')) {
        return [];
    }

    $plugin = wc_local_pickup_plus();
    if (! $plugin || ! method_exists($plugin, 'get_pickup_locations_instance')) {
        return [];
    }

    $locations = $plugin->get_pickup_locations_instance()->get_sorted_pickup_locations();
    $map       = [];

    foreach ($locations as $location) {
        if (! is_object($location) || ! method_exists($location, 'get_address')) {
            continue;
        }

        $address = $location->get_address();
        if (! $address instanceof \WC_Local_Pickup_Plus_Address) {
            continue;
        }

        $parts = array_filter([
            trim((string) $address->get_street_address('string', ' ')),
            trim((string) $address->get_city()),
            trim((string) $address->get_postcode()),
        ], static fn($part) => '' !== $part);

        if (! empty($parts)) {
            $map[(int) $location->get_id()] = implode(', ', $parts);
        }
    }

    return $map;
}

/**
 * Render the pickup location field while delivery is selected so the customer
 * sees the store picker instantly when switching to Free Collection (LPP only
 * outputs it once collection is already the chosen method).
 *
 * @param \WC_Shipping_Rate|string $method      Shipping rate instance or ID.
 * @param int|string               $package_index Package index.
 */
function matrix_rd_express_checkout_prerender_pickup_location_field($method, $package_index): void {
    if (! function_exists('wc_local_pickup_plus_shipping_method_id')) {
        return;
    }

    $pickup_id = wc_local_pickup_plus_shipping_method_id();
    $method_id = $method instanceof WC_Shipping_Rate ? $method->get_id() : (string) $method;

    if ($method_id !== $pickup_id && false === strpos($method_id, 'local_pickup')) {
        return;
    }

    $chosen = WC()->session ? WC()->session->get('chosen_shipping_methods', []) : [];
    if (isset($chosen[$package_index]) && $chosen[$package_index] === $pickup_id) {
        return;
    }

    if (! class_exists('SkyVerge\WooCommerce\Local_Pickup_Plus\Fields\Package_Pickup_Location_Field')) {
        return;
    }

    $field = new \SkyVerge\WooCommerce\Local_Pickup_Plus\Fields\Package_Pickup_Location_Field($package_index);
    $field->output_html();
}

/**
 * Render shipping method selection for step 1.
 */
function matrix_rd_express_checkout_render_shipping_methods(): void {
    if (! WC()->cart || ! WC()->cart->needs_shipping() || ! WC()->cart->show_shipping()) {
        return;
    }

    wc_get_template('checkout/step-shipping.php');
}

/**
 * Summary line for chosen fulfilment method in order review.
 */
function matrix_rd_express_checkout_summary_fulfilment_line(): void {
    if (! WC()->cart || ! WC()->cart->needs_shipping()) {
        return;
    }

    $packages = WC()->shipping()->get_packages();
    if (empty($packages)) {
        return;
    }

    $chosen  = WC()->session->get('chosen_shipping_methods', []);
    $method  = $chosen[0] ?? '';
    $pickup  = function_exists('wc_local_pickup_plus_shipping_method_id')
        ? wc_local_pickup_plus_shipping_method_id()
        : 'local_pickup_plus';
    $is_pickup = $method && (false !== strpos($method, 'local_pickup') || $method === $pickup);

    if ($is_pickup) {
        $label = __('Collection', 'matrix-starter');
    } elseif ($method && ! empty($packages[0]['rates'][$method])) {
        $label = function_exists('matrix_rd_checkout_format_delivery_method_label')
            ? matrix_rd_checkout_format_delivery_method_label((string) $packages[0]['rates'][$method]->get_label())
            : wp_strip_all_tags($packages[0]['rates'][$method]->get_label());
    } else {
        return;
    }

    $schedule       = function_exists('matrix_rd_checkout_get_schedule_summary_parts')
        ? matrix_rd_checkout_get_schedule_summary_parts()
        : ['date' => '', 'time' => ''];
    $schedule_parts = array_filter([$schedule['date'] ?? '', $schedule['time'] ?? '']);
    $schedule_text  = implode(' · ', $schedule_parts);
    $location_detail = $is_pickup && function_exists('matrix_rd_checkout_get_selected_pickup_location_name')
        ? matrix_rd_checkout_get_selected_pickup_location_name()
        : '';

    ?>
    <div class="flex justify-between px-2 py-3 border-b mobile:px-8 rd-fulfilment-summary border-grey-border">
        <div class="font-laca font-regular text-sm-font"><?php esc_html_e('Fulfilment', 'matrix-starter'); ?></div>
        <div class="font-laca font-regular text-sm-font text-right">
            <div class="rd-fulfilment-summary__method"><?php echo esc_html(wp_strip_all_tags($label)); ?></div>
            <?php if ($is_pickup) : ?>
                <?php if ($location_detail !== '') : ?>
                    <div class="rd-fulfilment-summary__location mt-1 text-xs-font opacity-80"><?php echo esc_html($location_detail); ?></div>
                <?php else : ?>
                    <div class="rd-fulfilment-summary__location mt-1 text-xs-font opacity-80" hidden></div>
                <?php endif; ?>
            <?php endif; ?>
            <?php if ($schedule_text !== '') : ?>
                <div class="rd-fulfilment-summary__schedule mt-1 text-xs-font opacity-80"><?php echo esc_html($schedule_text); ?></div>
            <?php else : ?>
                <div class="rd-fulfilment-summary__schedule mt-1 text-xs-font opacity-80" hidden></div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
add_action('woocommerce_review_order_before_order_total', 'matrix_rd_express_checkout_summary_fulfilment_line', 5);

/**
 * Show delivery fee in summary when applicable.
 */
function matrix_rd_express_checkout_summary_shipping_cost(): void {
    if (! WC()->cart || ! WC()->cart->needs_shipping()) {
        return;
    }

    $chosen = WC()->session->get('chosen_shipping_methods', []);
    $method = $chosen[0] ?? '';

    if (! $method || false !== strpos($method, 'local_pickup')) {
        return;
    }

    $shipping_total = (float) WC()->cart->get_shipping_total();
    if ($shipping_total <= 0) {
        return;
    }

    ?>
    <div class="flex justify-between px-2 py-3 border-b mobile:px-8 rd-shipping-cost-summary border-grey-border">
        <div class="font-laca font-regular text-sm-font"><?php esc_html_e('Delivery', 'matrix-starter'); ?></div>
        <div class="font-laca font-regular text-sm-font"><?php echo wp_kses_post(wc_price($shipping_total + (float) WC()->cart->get_shipping_tax())); ?></div>
    </div>
    <?php
}
add_action('woocommerce_review_order_before_order_total', 'matrix_rd_express_checkout_summary_shipping_cost', 6);

/**
 * Keep step 1 shipping methods in sync with AJAX checkout updates.
 *
 * @param array<string, string> $fragments Checkout fragments.
 *
 * @return array<string, string>
 */
function matrix_rd_express_checkout_shipping_fragment(array $fragments): array {
    // The fragment must reproduce the `.rd-express-shipping-body` wrapper, not
    // just its inner table. WooCommerce applies fragments with
    // jQuery(key).replaceWith(value); if we returned only the table, the very
    // first AJAX update would replace the wrapper div with a bare <table>, the
    // `.rd-express-shipping-body` selector would no longer match, and every
    // later update would silently no-op. That left a stale shipping table after
    // switching methods — so toggling Delivery -> Free Collection never brought
    // in the pickup-location picker (it is only rendered while collection is the
    // chosen method). Keeping the wrapper makes the selector match every time.
    ob_start();
    matrix_rd_express_checkout_render_shipping_methods();
    $shipping_html = ob_get_clean();

    $fragments['#rd-checkout-step-method .rd-express-shipping-body'] =
        '<div class="rd-express-shipping-body">' . $shipping_html . '</div>';

    return $fragments;
}
add_filter('woocommerce_update_order_review_fragments', 'matrix_rd_express_checkout_shipping_fragment');

/**
 * When delivery uses shipping as the primary address, copy shipping into billing
 * unless the customer opted into a separate billing address.
 *
 * @param array<string, mixed> $data Posted checkout data.
 *
 * @return array<string, mixed>
 */
function matrix_rd_express_checkout_sync_billing_from_shipping(array $data): array {
    $chosen = WC()->session ? WC()->session->get('chosen_shipping_methods', []) : [];
    $method = $chosen[0] ?? '';

    if (! $method || false !== strpos($method, 'local_pickup')) {
        return $data;
    }

    if (! empty($data['rd_bill_different_address'])) {
        return $data;
    }

    $map = [
        'first_name' => 'first_name',
        'last_name'  => 'last_name',
        'company'    => 'company',
        'address_1'  => 'address_1',
        'address_2'  => 'address_2',
        'city'       => 'city',
        'state'      => 'state',
        'postcode'   => 'postcode',
        'country'    => 'country',
        'phone'      => 'phone',
        'email'      => 'email',
    ];

    foreach ($map as $shipping_key => $billing_key) {
        $shipping_field = 'shipping_' . $shipping_key;
        $billing_field  = 'billing_' . $billing_key;

        if (! empty($data[$shipping_field])) {
            $data[$billing_field] = $data[$shipping_field];
        }
    }

    if (! empty($data['custom_shipping_eircode'])) {
        $data['billing_postcode']  = $data['custom_shipping_eircode'];
        $data['shipping_postcode'] = $data['custom_shipping_eircode'];
    }

    foreach (array('phone', 'email') as $contact_key) {
        $billing_field  = 'billing_' . $contact_key;
        $shipping_field = 'shipping_' . $contact_key;

        if (! empty($data[ $billing_field ])) {
            $data[ $shipping_field ] = $data[ $billing_field ];
        }
    }

    $data['ship_to_different_address'] = 1;

    return $data;
}
add_filter('woocommerce_checkout_posted_data', 'matrix_rd_express_checkout_sync_billing_from_shipping', 20);
