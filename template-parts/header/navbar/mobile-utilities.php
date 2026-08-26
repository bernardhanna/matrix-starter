<?php
/**
 * Mobile account + cart icons (visible below lg).
 */

if (! function_exists('wc_get_page_permalink')) {
    return;
}

$account_url = wc_get_page_permalink('myaccount');
$cart_url    = wc_get_cart_url();
$cart        = matrix_rd_nav_cart();
$cart_count  = (int) $cart['count'];
?>
<div
  class="js-cart-header-mobile rd-mobile-utilities flex w-full max-w-max-128 items-center justify-end gap-3 pr-1 lg:hidden"
  data-rd-cart
  data-initial-count="<?php echo $cart_count; ?>"
  x-data="rdCartHeaderMobile()"
  x-init="init()"
>
  <a class="flex shrink-0 flex-row items-center justify-center" href="<?php echo esc_url($account_url); ?>">
    <span
      class="iconify inline-flex h-8 w-8 shrink-0 items-center justify-center text-black-full"
      :class="{ 'text-white': open }"
      data-icon="uil:user"
      aria-hidden="true"
    ></span>
    <span class="sr-only"><?php esc_html_e('My account', 'matrix-starter'); ?></span>
  </a>
  <a
    href="<?php echo esc_url($cart_url); ?>"
    class="cart-contents relative ml-2 flex shrink-0 flex-row items-center justify-center text-reg-font"
    title="<?php esc_attr_e('View your shopping cart', 'woocommerce'); ?>"
    <?php echo function_exists('matrix_rd_uses_side_cart') && matrix_rd_uses_side_cart() ? 'data-rd-side-cart-trigger' : ''; ?>
  >
    <span
      class="iconify inline-flex h-8 w-8 shrink-0 items-center justify-center text-black-full"
      :class="{ 'text-white': open }"
      data-icon="grommet-icons:cart"
      data-width="30"
      data-height="30"
      aria-hidden="true"
    ></span>
    <span
      class="cart-contents-count text-tiny font-reg420 bg-red-critical w-[14px] h-[14px] flex items-center justify-center rounded-full border-2 border-black-border-solid p-2 basket-detail"
      x-text="cartCount"
      x-show="cartCount > 0"
      x-cloak
    ></span>
  </a>
</div>
