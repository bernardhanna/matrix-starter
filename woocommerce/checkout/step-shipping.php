<?php
/**
 * Express checkout — step 1 shipping methods.
 *
 * @package Matrix_Starter
 */

defined('ABSPATH') || exit;

$packages = WC()->shipping()->get_packages();

if (empty($packages)) {
    return;
}
?>
<table class="w-full shop_table rd-checkout-shipping-table rd-express-shipping-table">
    <tbody>
        <?php
        foreach ($packages as $i => $package) {
            $chosen_method = isset(WC()->session->chosen_shipping_methods[$i])
                ? WC()->session->chosen_shipping_methods[$i]
                : '';

            wc_get_template(
                'cart/cart-shipping.php',
                [
                    'package'                  => $package,
                    'available_methods'        => $package['rates'],
                    'show_package_details'     => count($packages) > 1,
                    'show_shipping_calculator' => false,
                    'package_details'          => '',
                    'package_name'             => $package['package_name'],
                    'index'                    => $i,
                    'chosen_method'            => $chosen_method,
                    'formatted_destination'    => WC()->countries->get_formatted_address($package['destination'], ', '),
                    'has_calculated_shipping'  => WC()->customer->has_calculated_shipping(),
                ]
            );
        }
        ?>
    </tbody>
</table>
