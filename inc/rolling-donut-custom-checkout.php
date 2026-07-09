<?php
/**
 * Legacy custom-checkout mu-plugin — billing/shipping field layout, Dublin delivery rules.
 *
 * @package Matrix_Starter
 */

// Force recalculation of shipping rates when a coupon is applied or removed
add_action('woocommerce_cart_calculate_fees', 'force_shipping_recalculation_on_coupon', 20);
function force_shipping_recalculation_on_coupon($cart) {
    if (WC()->session) {
        WC()->session->set('shipping_for_package_0', false);
    }
    WC()->cart->calculate_shipping();
}

// Trigger checkout refresh when a coupon is applied or removed
add_action('wp_footer', 'add_coupon_recalculation_script');
function add_coupon_recalculation_script() {
    if (is_checkout() || is_cart()) {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('body').on('applied_coupon removed_coupon', function() {
                    $('body').trigger('update_checkout');
                });
            });
        </script>
        <?php
    }
}

// Set Dublin as the default county/state in WooCommerce checkout for Ireland
add_filter('default_checkout_state', 'set_default_checkout_state');
function set_default_checkout_state($state) {
    if (WC()->customer->get_billing_country() === 'IE') { // Applies only for Ireland
        $state = 'Dublin';
    }
    return $state;
}

// Make the Eircode / Postcode fields optional for both billing and shipping
add_filter('woocommerce_get_country_locale', 'ireland_country_locale_change', 10, 1);
function ireland_country_locale_change($locale) {
    $locale['IE']['postcode']['required'] = false;
    return $locale;
}

// Set a default valid Eircode for shipping calculations if none is entered
add_filter('woocommerce_checkout_posted_data', 'set_valid_default_eircode_for_checkout');
function set_valid_default_eircode_for_checkout($data) {
    if ($data['shipping_country'] === 'IE' && empty($data['shipping_postcode'])) {
        $data['shipping_postcode'] = 'D01 F5P2'; // Valid Dublin Eircode for calculations
    }
    return $data;
}

// Clear the default Eircode if it wasn't provided by the user before saving the order
add_action('woocommerce_checkout_create_order', 'clear_default_eircode_for_order', 10, 2);
function clear_default_eircode_for_order($order, $data) {
    if ($order->get_shipping_country() === 'IE' && $order->get_shipping_postcode() === 'D01 F5P2') {
        $order->set_shipping_postcode(''); // Clear the default Eircode before saving the order
    }
}

/**
 * Whether the current checkout address is in Dublin (for delivery eligibility).
 */
function matrix_rd_checkout_is_dublin_address($package = null): bool {
    $valid_dublin_areas = array('Dublin', 'DUBLIN', 'D01', 'D02', 'D03', 'D04', 'D05', 'D06', 'D6W', 'D07', 'D08', 'D09', 'D11', 'D12', 'D13', 'D14', 'D15', 'D16', 'D17', 'D18', 'D20', 'D22', 'D24');

    $state = '';
    if (WC()->customer) {
        $state = WC()->customer->get_shipping_state();
        if ('' === $state) {
            $state = WC()->customer->get_billing_state();
        }
    }

    if ('' === $state && is_array($package) && ! empty($package['destination']['state'])) {
        $state = $package['destination']['state'];
    }

    return in_array($state, $valid_dublin_areas, true);
}

// Per-order checkout should show one shipping-method list (avoid duplicate radios).
add_filter('woocommerce_shipping_packages', 'matrix_rd_checkout_collapse_per_order_shipping_packages', 999);
function matrix_rd_checkout_is_checkout_request(): bool {
    if (function_exists('is_checkout') && is_checkout()) {
        return true;
    }

    return wp_doing_ajax() && isset($_GET['wc-ajax']) && 'update_order_review' === $_GET['wc-ajax']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
}

function matrix_rd_checkout_collapse_per_order_shipping_packages(array $packages): array {
    if (! matrix_rd_checkout_is_checkout_request() || count($packages) <= 1) {
        return $packages;
    }

    $lpp = function_exists('wc_local_pickup_plus_shipping_method') ? wc_local_pickup_plus_shipping_method() : null;
    if (! $lpp || ! $lpp->is_per_order_selection_enabled()) {
        return $packages;
    }

    return [reset($packages)];
}

// Filter out shipping options based on whether the address is in Dublin or outside
add_filter('woocommerce_package_rates', 'filter_local_pickup_plus_and_delivery_for_dublin', 20, 2);
function filter_local_pickup_plus_and_delivery_for_dublin($rates, $package) {
    $is_dublin_address = matrix_rd_checkout_is_dublin_address($package);

    foreach ($rates as $rate_id => $rate) {
        $is_local_pickup_plus = strpos($rate_id, 'local_pickup_plus') !== false;
        $is_free_shipping = strpos($rate_id, 'free_shipping') !== false;

        if ($is_dublin_address) {
            if (!$is_local_pickup_plus && !$is_free_shipping && strpos($rate_id, 'flat_rate') === false) {
                unset($rates[$rate_id]);
            }
        } else {
            if (!$is_local_pickup_plus && !$is_free_shipping) {
                unset($rates[$rate_id]);
            }
        }
    }
    return $rates;
}

// Customize the "no shipping available" message for customers outside Dublin
add_filter('woocommerce_no_shipping_available_html', 'custom_no_shipping_available_message');
add_filter('woocommerce_cart_no_shipping_available_html', 'custom_no_shipping_available_message');
function custom_no_shipping_available_message($message) {
    return __("We currently only offer delivery to the Dublin area. Please select 'Ship to a different address' to enter a Dublin-based address for delivery.", 'woocommerce');
}

// Seed hidden shipping postcode for rate calculations (server filter handles AJAX).
add_action('wp_footer', 'matrix_rd_checkout_seed_shipping_postcode');
function matrix_rd_checkout_seed_shipping_postcode() {
    if (! is_checkout()) {
        return;
    }
    ?>
    <script type="text/javascript">
        jQuery(function ($) {
            var $postcode = $('#shipping_postcode');
            if ($postcode.length && !$postcode.val()) {
                $postcode.val('D01 F5P2');
            }

            $('#ship-to-different-address-checkbox').on('change.rdCheckout', function () {
                if (this.checked) {
                    $('#shipping_state').val('Dublin').trigger('change');
                }
            });
        });
    </script>
    <?php
}

// Add the custom Eircode field directly to the shipping fields array
add_filter('woocommerce_checkout_fields', 'add_custom_shipping_eircode_field');
function add_custom_shipping_eircode_field($fields) {
    // Define the new custom Eircode field for shipping
    $fields['shipping']['custom_shipping_eircode'] = array(
        'type'        => 'text',
        'class'       => array('form-row-wide'),
        'label'       => __('Eircode ', 'woocommerce'),
        'placeholder' => __('Enter your Eircode if known'),
        'required'    => false,
        'priority'    => 71, // Set priority just after County (typically priority 70)
    );

    // Reorder fields to ensure custom Eircode appears right after the County field
    $ordered_shipping_fields = array();
    foreach ($fields['shipping'] as $key => $field) {
        // Insert the custom Eircode field right after `shipping_state`
        $ordered_shipping_fields[$key] = $field;
        if ($key === 'shipping_state') {
            $ordered_shipping_fields['custom_shipping_eircode'] = $fields['shipping']['custom_shipping_eircode'];
        }
    }
    $fields['shipping'] = $ordered_shipping_fields;

    return $fields;
}

function custom_woocommerce_form_field_args($args, $key, $value)
{
    // Styling Form Fields Using Filters
    $args['label_class'][] = 'ml-2 text-mob-xs-font font-reg420 block pb-2';
    $args['input_class'][] = 'woocommerce-Input woocommerce-Input--text input-text rounded-lg-x h-input text-black-secondary text-mob-xs-font font-laca font-light pl-11 flex w-full';

    // Check if the key matches any of our conditions
    switch ($key) {
        case 'billing_first_name':
            $args['placeholder'] = 'First Name';
            $args['input_class'][] = 'lg:w-99';
            $args['wrapper_class'] = 'first-name-wrapper';
            $args['before_field'] = '<div class="flex flex-col flex-wrap items-center w-full billing-name-wrapper md:flex-row md:justify-between">';
            break;

        case 'billing_last_name':
            $args['placeholder'] = 'Last Name';
            $args['input_class'][] = 'lg:w-99';
            $args['after_field'] = '</div>';  // Close the wrapping div
            break;

        case 'billing_company':
            $args['placeholder'] = 'Company';
            break;

        case 'billing_country':
            $args['placeholder'] = 'Country';
            $args['default'] = 'IE';
            break;

        case 'shipping_country':
            $args['default'] = 'IE';
            break;

        case 'billing_address_1':
            $args['placeholder'] = 'House Number and street name';
            break;

        case 'billing_address_2':
            $args['placeholder'] = 'Apartment, suite, unit etc (Optional)';
            break;

        case 'billing_city':
            $args['placeholder'] = 'Town/ City';
            break;

        case 'billing_state':
            $args['placeholder'] = 'County';
            $args['type'] = 'text';
            $args['default'] = 'Dublin';
            break;

        case 'billing_postcode':
        case 'shipping_postcode':
            // Dynamically set placeholder for Eircode/Postcode
            $country = WC()->customer->get_billing_country();
            $args['placeholder'] = ($country === 'IE') ? 'Eircode' : 'Postcode / Zip';
            break;

        case 'billing_phone':
            $args['placeholder'] = 'Telephone number';
            break;

        case 'billing_email':
            $args['placeholder'] = 'Email';
            break;

        case 'shipping_company':
            $args['placeholder'] = 'Company';
            break;

        case 'shipping_address_1':
            $args['placeholder'] = 'House Number and street name';
            break;

        case 'shipping_address_2':
            $args['placeholder'] = 'Apartment, suite, unit etc (Optional)';
            break;

        case 'shipping_city':
            $args['placeholder'] = 'Town/ City';
            break;

        case 'shipping_state':
            $args['placeholder'] = 'County';
            break;

        case 'custom_shipping_eircode':
            $args['placeholder'] = 'Eircode';
            break;

        case 'jckwds-delivery-date':
            $args['label_class'][] = 'required_field';
            break;

        default:
            break;
    }

    return $args;
}

add_filter('woocommerce_form_field_args', 'custom_woocommerce_form_field_args', 10, 3);

// JavaScript for Dynamic Update of Postcode Placeholder Based on Country Selection
add_action('wp_footer', 'dynamic_postcode_placeholder_script');

function dynamic_postcode_placeholder_script() {
    if (is_checkout()) {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                function updatePostcodePlaceholder() {
                    var billingCountry = $('#billing_country').val();
                    var shippingCountry = $('#shipping_country').val();

                    // Update billing postcode placeholder
                    var billingPlaceholder = (billingCountry === 'IE') ? 'Eircode' : 'Postcode / Zip';
                    $('#billing_postcode').attr('placeholder', billingPlaceholder);

                    // Update shipping postcode placeholder
                    var shippingPlaceholder = (shippingCountry === 'IE') ? 'Eircode' : 'Postcode / Zip';
                    $('#shipping_postcode').attr('placeholder', shippingPlaceholder);
                }

                // Initial update on page load
                updatePostcodePlaceholder();

                // Update placeholders when the country fields change
                $('#billing_country, #shipping_country').change(function() {
                    updatePostcodePlaceholder();
                });
            });
        </script>
        <?php
    }
}
function change_woocommerce_field_markup($field, $key, $args, $value)
{
    // Remove 'form-row' class from the field
    $field = str_replace('form-row', '', $field);

    // Wrap each field with a div
    $field = '<div class="w-full single-field-wrapper" data-priority="' . $args['priority'] . '">' . $field . '</div>';

    // Wrap first and last name fields together for both billing and shipping
    if ($key === 'billing_first_name' || $key === 'shipping_first_name') {
        $field = '<div class="flex flex-col w-full name-field xl:flex-row xl:justify-between">' . $field;
    } else if ($key === 'billing_last_name' || $key === 'shipping_last_name') {
        $field = $field . '</div>';
    }

    return $field;
}

add_filter('woocommerce_form_field', 'change_woocommerce_field_markup', 10, 4);

// Only allow Dublin as an option
function custom_woocommerce_states($states)
{
    $states['IE'] = array(
        //'' => 'Select Area Code',  // Placeholder
        'Dublin' => __('Dublin', 'woocommerce'),
        'D01' => 'Dublin 01', 'D02' => 'Dublin 02', 'D03' => 'Dublin 03', 'D04' => 'Dublin 04',
        'D05' => 'Dublin 05', 'D06' => 'Dublin 06', 'D6W' => 'Dublin 6W', 'D07' => 'Dublin 07',
        'D08' => 'Dublin 08', 'D09' => 'Dublin 09', 'D11' => 'Dublin 11', 'D12' => 'Dublin 12',
        'D13' => 'Dublin 13', 'D14' => 'Dublin 14', 'D15' => 'Dublin 15', 'D16' => 'Dublin 16',
        'D17' => 'Dublin 17', 'D18' => 'Dublin 18', 'D20' => 'Dublin 20', 'D22' => 'Dublin 22', 'D24' => 'Dublin 24',
        'break' => '************* Note: We Do Not Delivery to the Any of the below ****************',
        'CE' => 'Clare', 'CN' => 'Cavan', 'CW' => 'Carlow', 'C' => 'Cork',
        'DL' => 'Donegal', 'G' => 'Galway', 'KE' => 'Kildare', 'KY' => 'Kerry',
        'KK' => 'Kilkenny', 'LS' => 'Laois', 'LM' => 'Leitrim', 'LH' => 'Louth',
        'LD' => 'Longford', 'L' => 'Limerick', 'MH' => 'Meath', 'MN' => 'Monaghan',
        'MO' => 'Mayo', 'OY' => 'Offaly', 'RN' => 'Roscommon', 'SO' => 'Sligo',
        'TA' => 'Tipperary', 'WD' => 'Waterford', 'WH' => 'Westmeath', 'WX' => 'Wexford',
        'WW' => 'Wicklow'
    );
    return $states;
}
add_filter('woocommerce_states', 'custom_woocommerce_states');

// Validate billing and shipping addresses during checkout
add_action('woocommerce_checkout_process', 'validate_addresses_for_supported_areas');

function validate_addresses_for_supported_areas() {
    // Define the valid Dublin areas for shipping
    $valid_dublin_areas = array(
        'Dublin', 'D01', 'D02', 'D03', 'D04', 'D05', 'D06', 'D6W', 'D07',
        'D08', 'D09', 'D11', 'D12', 'D13', 'D14', 'D15', 'D16', 'D17',
        'D18', 'D20', 'D22', 'D24'
    );

    // Check the selected shipping method
    $chosen_shipping_methods = WC()->session->get('chosen_shipping_methods');
    $chosen_shipping_method = isset($chosen_shipping_methods[0]) ? $chosen_shipping_methods[0] : '';

    // Determine if the chosen shipping method is for collection (bypass validation)
    $is_collection = strpos($chosen_shipping_method, 'local_pickup') !== false || strpos($chosen_shipping_method, 'collection') !== false;

    if ($is_collection) {
        // Skip validation if the chosen method is collection
        return;
    }

    // Check if "Ship to a different address" is selected
    $ship_to_different_address = isset($_POST['ship_to_different_address']) && $_POST['ship_to_different_address'];

    if ($ship_to_different_address) {
        // Validate the shipping address if "Ship to a different address" is selected
        $shipping_state = isset($_POST['shipping_state']) ? trim($_POST['shipping_state']) : '';

        if (!in_array($shipping_state, $valid_dublin_areas)) {
            wc_add_notice(__('We do not deliver to this area. Please enter a valid Dublin area for shipping.', 'woocommerce'), 'error');
        }
    } else {
        // Billing address may be any country/region — it is never restricted.
        // The Dublin-only rule applies to the delivery destination. When no
        // separate shipping address is given, WooCommerce uses the billing
        // address as the delivery address, so for delivery that address must be
        // in Dublin (Ireland). Otherwise we ask them to ship to a Dublin address.
        $billing_country = isset($_POST['billing_country']) ? wc_clean(wp_unslash($_POST['billing_country'])) : '';
        $billing_state   = isset($_POST['billing_state']) ? trim(wp_unslash($_POST['billing_state'])) : '';

        $delivers_to_dublin = ('IE' === $billing_country) && in_array($billing_state, $valid_dublin_areas, true);

        if (!$delivers_to_dublin) {
            wc_add_notice(__('We only deliver within Dublin. Please choose “Ship to a different address” and enter a Dublin address, or select collection instead.', 'woocommerce'), 'error');
        }
    }
}

//SHIPPING FORM
add_filter('woocommerce_shipping_fields', 'customize_checkout_shipping_fields', 20, 1);
function customize_checkout_shipping_fields($shipping_fields)
{
    // Define custom placeholders for specific fields
    $custom_placeholders = array(
        'shipping_first_name' => 'First name',
        'shipping_last_name'  => 'Surname',
        'shipping_company'    => 'Company Name',
        'shipping_address_1'  => 'Enter your address',
        'shipping_postcode'   => 'Eircode',
        'custom_shipping_eircode'   => 'Eircode',
        'shipping_phone'      => 'Phone Number',
        'shipping_email'      => 'Email Address',
    );

    // Iterate over the shipping fields and apply changes
    foreach ($shipping_fields as $key => $field) {
        // Add custom class to all input fields
        $shipping_fields[$key]['input_class'][] = 'rounded-lg-x h-input text-black-secondary text-mob-xs-font font-laca font-light pl-11 w-full flex';

        // Add custom class to all labels
        $shipping_fields[$key]['label_class'][] = 'mt-4 ml-2 text-mob-xs-font font-reg420';

        // If there's a custom placeholder for this field, use it
        if (isset($custom_placeholders[$key])) {
            $shipping_fields[$key]['placeholder'] = $custom_placeholders[$key];
        }
    }

    return $shipping_fields;
}

// Save the custom Eircode field to order meta
add_action('woocommerce_checkout_update_order_meta', 'save_custom_eircode_field');
function save_custom_eircode_field($order_id) {
    if (!empty($_POST['custom_shipping_eircode'])) {
        update_post_meta($order_id, '_custom_shipping_eircode', sanitize_text_field($_POST['custom_shipping_eircode']));
    }
}

// Remove the default Postcode field from the admin Shipping edit form so staff
// only ever edit the single custom Eircode input below (matches legacy: the raw
// postcode field was confusing because the Eircode lives in its own meta key).
add_filter('woocommerce_admin_shipping_fields', 'rd_remove_admin_shipping_postcode_field', 20, 1);
function rd_remove_admin_shipping_postcode_field($fields) {
    unset($fields['postcode']);
    return $fields;
}

// Editable custom Eircode in the admin order details (matches legacy behaviour
// where staff can enter/amend the Eircode directly on the order).
add_action('woocommerce_admin_order_data_after_shipping_address', 'display_custom_eircode_in_admin', 10, 1);
function display_custom_eircode_in_admin($order) {
    $custom_eircode = $order->get_meta('_custom_shipping_eircode');
    ?>
    <p class="form-field form-field-wide rd-admin-eircode">
        <label for="rd_custom_shipping_eircode"><strong><?php esc_html_e('Eircode', 'woocommerce'); ?></strong></label>
        <input type="text"
               id="rd_custom_shipping_eircode"
               name="rd_custom_shipping_eircode"
               value="<?php echo esc_attr($custom_eircode); ?>"
               placeholder="<?php esc_attr_e('Enter Eircode', 'woocommerce'); ?>"
               style="width:100%;" />
    </p>
    <?php
}

// Persist the Eircode when the order is saved from the admin screen.
add_action('woocommerce_process_shop_order_meta', 'save_custom_eircode_field_admin', 60, 1);
function save_custom_eircode_field_admin($order_id) {
    if (!isset($_POST['rd_custom_shipping_eircode'])) {
        return;
    }

    $order = wc_get_order($order_id);
    if (!$order) {
        return;
    }

    $eircode = sanitize_text_field(wp_unslash($_POST['rd_custom_shipping_eircode']));
    $order->update_meta_data('_custom_shipping_eircode', $eircode);
    // Keep the real shipping postcode in sync so the shipping address, emails and
    // PDFs all show the Eircode consistently (legacy synced these two together).
    $order->set_shipping_postcode($eircode);
    $order->save();
}

// Display custom Eircode in order details on the "My Account" page
add_action('woocommerce_order_details_after_customer_details', 'display_custom_eircode_in_order_details');
function display_custom_eircode_in_order_details($order) {
    $custom_eircode = get_post_meta($order->get_id(), '_custom_shipping_eircode', true);
    if ($custom_eircode) {
        echo '<p><strong>' . __('Eircode', 'woocommerce') . ':</strong> ' . $custom_eircode . '</p>';
    }
}

// Hide the default WooCommerce shipping postcode field
add_filter('woocommerce_checkout_fields', 'remove_default_shipping_postcode_field');
function remove_default_shipping_postcode_field($fields) {
    if (isset($fields['shipping']['shipping_postcode'])) {
        $fields['shipping']['shipping_postcode']['class'][] = 'shippinghidden'; // Adds a hidden class to hide the field
    }
    return $fields;
}

// CSS to hide the default postcode field if JavaScript is disabled
add_action('wp_head', 'add_hidden_class_style');
function add_hidden_class_style() {
    echo '<style>.shippinghidden { display: none !important; }</style>';
}

// Hook into the function that formats the shipping address in the order list
add_filter('woocommerce_order_formatted_shipping_address', 'add_custom_shipping_eircode_to_address', 10, 2);

function add_custom_shipping_eircode_to_address($address, $order) {
    // Get the custom shipping Eircode from the order meta
    $custom_shipping_eircode = $order->get_meta('_custom_shipping_eircode');
    
    // Check if there is a custom shipping Eircode to append to the shipping address
    if (!empty($custom_shipping_eircode)) {
        // Append the custom Eircode to the address (adjust position if needed)
        $address['postcode'] = $custom_shipping_eircode; // Replacing the postcode with custom Eircode
    }
    
    return $address;
}

// Also ensure the custom Eircode appears in emails and order details on the front end
add_filter('woocommerce_order_shipping_to_display_shipped_via', 'add_custom_shipping_eircode_to_address_display', 10, 2);

function add_custom_shipping_eircode_to_address_display($formatted_address, $order) {
    // Retrieve the custom shipping Eircode
    $custom_shipping_eircode = $order->get_meta('_custom_shipping_eircode');

    // If the custom shipping Eircode exists, append it to the formatted address
    if (!empty($custom_shipping_eircode) && !str_contains($formatted_address, $custom_shipping_eircode)) {
        $formatted_address .= ', ' . esc_html($custom_shipping_eircode);
    }
    
    return $formatted_address;
}
?>
