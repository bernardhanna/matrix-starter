<?php
/**
 * Side cart panel contents (Rolling Donut).
 *
 * @package WooCommerce\Templates
 */

defined('ABSPATH') || exit;

$cart       = WC()->cart;
$cart_url   = wc_get_cart_url();
$checkout   = wc_get_checkout_url();
$is_empty   = ! $cart || $cart->is_empty();
$item_count = $is_empty ? 0 : $cart->get_cart_contents_count();
?>
<div class="rd-side-cart__header">
  <h2 id="rd-side-cart-title" class="rd-side-cart__title font-laca text-lg-font font-light text-black-full">
    <?php
    printf(
        /* translators: %d: number of items in cart */
        esc_html(_n('Your Cart (%d item)', 'Your Cart (%d items)', $item_count, 'matrix-starter')),
        (int) $item_count
    );
    ?>
  </h2>
  <button
    type="button"
    class="rd-side-cart__close flex h-10 w-10 items-center justify-center rounded-full border-2 border-black-full bg-red-critical text-black-full hover:opacity-70"
    data-rd-side-cart-close
    aria-label="<?php esc_attr_e('Close cart', 'matrix-starter'); ?>"
  >
    <span aria-hidden="true">&times;</span>
  </button>
</div>

<?php if ($is_empty) : ?>
  <div class="rd-side-cart__empty flex flex-1 flex-col items-center justify-center px-6 py-12 text-center">
    <span class="iconify mb-4 text-black-full" data-icon="grommet-icons:basket" data-width="48" data-height="48" aria-hidden="true"></span>
    <p class="font-laca text-base-font text-black-full"><?php esc_html_e('Your cart is empty.', 'matrix-starter'); ?></p>
    <button
      type="button"
      class="rd-side-cart__btn rd-side-cart__btn--secondary mt-6"
      data-rd-side-cart-close
    >
      <?php esc_html_e('Continue Shopping', 'matrix-starter'); ?>
    </button>
  </div>
<?php else : ?>
  <ul class="rd-side-cart__items" role="list">
    <?php
    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        $_product   = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
        $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);

        if (! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0) {
            continue;
        }

        // Box/bundle child lines are hidden — only the parent box row is shown.
        if (function_exists('matrix_rd_side_cart_is_child_line') && matrix_rd_side_cart_is_child_line($cart_item)) {
            continue;
        }

        if (! apply_filters('woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key)) {
            continue;
        }

        $product_name = matrix_rd_side_cart_item_name($_product->get_name(), $cart_item, $cart_item_key);
        $thumbnail         = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image('woocommerce_thumbnail'), $cart_item, $cart_item_key);
        $product_price     = apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key);
        $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
        ?>
        <li class="rd-side-cart__item" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">
          <div class="rd-side-cart__item-media">
            <?php if ($product_permalink) : ?>
              <a href="<?php echo esc_url($product_permalink); ?>" class="rd-side-cart__item-image">
                <?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
              </a>
            <?php else : ?>
              <span class="rd-side-cart__item-image"><?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
            <?php endif; ?>
          </div>
          <div class="rd-side-cart__item-body">
            <div class="rd-side-cart__item-top">
              <?php if ($product_permalink) : ?>
                <a href="<?php echo esc_url($product_permalink); ?>" class="rd-side-cart__item-name font-laca text-sm-md-font font-light text-black-full hover:underline">
                  <?php echo wp_kses_post($product_name); ?>
                </a>
              <?php else : ?>
                <span class="rd-side-cart__item-name font-laca text-sm-md-font font-light text-black-full">
                  <?php echo wp_kses_post($product_name); ?>
                </span>
              <?php endif; ?>
              <button
                type="button"
                class="rd-side-cart__remove"
                data-rd-side-cart-remove
                data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>"
                aria-label="<?php echo esc_attr(sprintf(__('Remove %s from cart', 'matrix-starter'), wp_strip_all_tags($product_name))); ?>"
              >
                &times;
              </button>
            </div>
            <?php
            if (! function_exists('matrix_rd_side_cart_is_box_parent') || ! matrix_rd_side_cart_is_box_parent($cart_item)) {
                echo wc_get_formatted_cart_item_data($cart_item); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            } elseif (class_exists('RD_Box_Builder_Cart_Edit') && RD_Box_Builder_Cart_Edit::is_box_parent($cart_item)) {
                echo RD_Box_Builder_Cart_Edit::render($cart_item_key, $cart_item, 'side'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }
            ?>
            <div class="rd-side-cart__item-meta font-reg420 text-sm-md-font text-black-full">
              <span class="rd-side-cart__qty"><?php echo esc_html((string) $cart_item['quantity']); ?> &times;</span>
              <span class="rd-side-cart__price"><?php echo wp_kses_post($product_price); ?></span>
            </div>
          </div>
        </li>
        <?php
    }
    ?>
  </ul>

  <div class="rd-side-cart__footer">
    <div class="rd-side-cart__subtotal flex items-center justify-between">
      <span class="font-reg420 text-base-font text-black-full"><?php esc_html_e('Subtotal', 'matrix-starter'); ?></span>
      <span class="rd-side-cart__subtotal-amount font-laca text-lg-font font-light text-black-full">
        <?php echo wp_kses_post($cart->get_cart_subtotal()); ?>
      </span>
    </div>
    <p class="rd-side-cart__note mt-2 text-xs text-black-full/70">
      <?php esc_html_e('Shipping and taxes calculated at checkout.', 'matrix-starter'); ?>
    </p>
    <div class="rd-side-cart__actions mt-6 flex flex-col gap-3">
      <a href="<?php echo esc_url($checkout); ?>" class="rd-side-cart__btn rd-side-cart__btn--primary">
        <?php esc_html_e('Checkout', 'matrix-starter'); ?>
      </a>
      <a href="<?php echo esc_url($cart_url); ?>" class="rd-side-cart__btn rd-side-cart__btn--secondary">
        <?php esc_html_e('View Cart', 'matrix-starter'); ?>
      </a>
      <button
        type="button"
        class="rd-side-cart__btn rd-side-cart__btn--clear"
        data-rd-side-cart-clear
      >
        <?php esc_html_e('Clear cart', 'matrix-starter'); ?>
      </button>
      <button type="button" class="rd-side-cart__btn rd-side-cart__btn--ghost" data-rd-side-cart-close>
        <?php esc_html_e('Continue Shopping', 'matrix-starter'); ?>
      </button>
    </div>
  </div>
<?php endif; ?>
