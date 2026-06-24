<?php
/**
 * Shared checkout / cart fulfilment notices.
 *
 * @package Matrix_Starter
 */

defined('ABSPATH') || exit;

/**
 * Notice copy used across cart and checkout.
 *
 * @return array<string, array{label: string, variant: string}>
 */
function matrix_rd_checkout_notices(): array {
    return [
        'dublin_only' => [
            'label'   => __('Please Note: Delivery in Dublin area only', 'rolling-donut'),
            'variant' => 'warning',
        ],
        'cutoff_5pm' => [
            'label'      => __('Orders must be in by 5pm for next day delivery', 'rolling-donut'),
            'collection' => __('Orders must be in by 5pm for next day collection', 'rolling-donut'),
            'variant'    => 'info',
        ],
        'seven_days' => [
            'label'   => __('Delivery & Collection available 7 days', 'rolling-donut'),
            'variant' => 'info',
        ],
        'no_time_guarantee' => [
            'label'      => __('Unfortunately, we cannot guarantee specific delivery times', 'rolling-donut'),
            'collection' => __('Unfortunately, we cannot guarantee specific collection times', 'rolling-donut'),
            'variant'    => 'warning',
        ],
    ];
}

/**
 * Whether the current checkout session is collection / pickup.
 */
function matrix_rd_checkout_is_collection_selected(): bool {
    if (! function_exists('WC') || ! WC()->session) {
        return false;
    }

    $chosen = WC()->session->get('chosen_shipping_methods', []);
    $method = is_array($chosen) ? (string) ($chosen[0] ?? '') : '';

    return $method && false !== strpos($method, 'local_pickup');
}

/**
 * Build notice CSS classes.
 *
 * @param string $variant Notice variant.
 */
function matrix_rd_checkout_notice_classes(string $variant): string {
    $classes = 'rd-checkout-notice rd-checkout-notice--' . $variant . ' mb-2 font-light text-mob-md-font';

    if ('warning' === $variant) {
        $classes .= ' font-reg420';
    }

    return $classes;
}

/**
 * Render a single notice line.
 *
 * @param string $key Notice key from matrix_rd_checkout_notices().
 */
function matrix_rd_checkout_render_notice(string $key): void {
    $notices = matrix_rd_checkout_notices();

    if (empty($notices[$key])) {
        return;
    }

    $notice = $notices[$key];
    ?>
    <p class="<?php echo esc_attr(matrix_rd_checkout_notice_classes($notice['variant'])); ?>">
        <?php echo esc_html($notice['label']); ?>
    </p>
    <?php
}

/**
 * Render delivery and collection variants for the same notice.
 *
 * @param string $key Notice key from matrix_rd_checkout_notices().
 */
function matrix_rd_checkout_render_fulfilment_notice(string $key): void {
    $notices = matrix_rd_checkout_notices();

    if (empty($notices[$key])) {
        return;
    }

    $notice  = $notices[$key];
    $classes = matrix_rd_checkout_notice_classes($notice['variant']);
    ?>
    <p class="<?php echo esc_attr($classes); ?> rd-checkout-notice--delivery">
        <?php echo esc_html($notice['label']); ?>
    </p>
    <p class="<?php echo esc_attr($classes); ?> rd-checkout-notice--collection">
        <?php echo esc_html($notice['collection'] ?? $notice['label']); ?>
    </p>
    <?php
}

/**
 * Render contextual notices for a checkout wizard step.
 *
 * @param string $step method|schedule|details|cart
 */
function matrix_rd_checkout_render_step_notices(string $step): void {
    $groups = [
        'method' => [
            'always'  => ['seven_days'],
            'delivery' => ['dublin_only'],
        ],
        'schedule' => [
            'fulfilment' => ['cutoff_5pm', 'no_time_guarantee'],
        ],
        'details' => [],
        'cart' => ['dublin_only', 'cutoff_5pm', 'seven_days', 'no_time_guarantee'],
    ];

    if (empty($groups[$step])) {
        return;
    }

    $config = $groups[$step];
    ?>
    <div class="rd-checkout-notices" data-rd-notices-for="<?php echo esc_attr($step); ?>">
        <?php
        foreach ($config['always'] ?? [] as $key) {
            matrix_rd_checkout_render_notice($key);
        }

        if (! empty($config['fulfilment'])) {
            foreach ($config['fulfilment'] as $key) {
                matrix_rd_checkout_render_fulfilment_notice($key);
            }
        }

        if (! empty($config['delivery'])) {
            echo '<div class="rd-checkout-notices__delivery-only">';
            foreach ($config['delivery'] as $key) {
                matrix_rd_checkout_render_notice($key);
            }
            echo '</div>';
        }
        ?>
    </div>
    <?php
}

/**
 * Render the compact notice stack used on the cart page.
 */
function matrix_rd_checkout_render_cart_notices(): void {
    matrix_rd_checkout_render_step_notices('cart');
}
