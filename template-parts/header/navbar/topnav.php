<?php
/**
 * Utility row: phone, account, search, cart (desktop).
 */
$telephone = $args['telephone'] ?? '';
$cart      = $args['cart'] ?? ['count' => 0, 'total_plain' => '', 'total_html' => ''];

if (! function_exists('wc_get_page_permalink')) {
    return;
}

$account_url = wc_get_page_permalink('myaccount');
$cart_url    = wc_get_cart_url();
?>
<div
  class="top-nav relative top-0 z-[1000] mx-auto hidden w-full max-w-full min-w-0 py-0 lg:mb-0 lg:mt-4 lg:flex md:justify-end lg:justify-end laptop:my-4"
>
  <div class="flex min-w-0 flex-1 flex-row items-center justify-end gap-3 px-4 md:items-center md:justify-end md:px-4 lg:gap-4">
    <div class="hidden md:block md:pl-4" aria-hidden="true"></div>
    <div class="z-50 flex shrink-0 flex-row items-center justify-end gap-3 md:gap-4 lg:gap-4">
      <?php if ($telephone) : ?>
        <a
          class="reg-font relative z-50 hidden shrink-0 flex-row items-center gap-2 lg:flex"
          href="<?php echo esc_attr(matrix_rd_nav_tel_href($telephone)); ?>"
          aria-label="<?php echo esc_attr(sprintf(__('Call us at %s', 'matrix-starter'), $telephone)); ?>"
        >
          <span class="iconify relative inline-flex h-8 w-8 shrink-0 items-center justify-center" data-icon="icon-park-twotone:phone-telephone" aria-hidden="true"></span>
          <div class="nav-line flex shrink-0 lg:hidden laptop:hidden" aria-hidden="true"></div>
          <span class="text-reg-font font-reg420 relative -t-0-1 hidden laptop:flex"><?php echo esc_html($telephone); ?></span>
        </a>
        <div class="nav-line hidden shrink-0 md:block lg:hidden" aria-hidden="true"></div>
      <?php endif; ?>

      <a class="relative z-50 flex shrink-0 flex-row items-center justify-center" href="<?php echo esc_url($account_url); ?>">
        <span class="iconify inline-flex h-8 w-8 shrink-0 items-center justify-center" data-icon="uil:user" aria-hidden="true"></span>
        <span class="sr-only"><?php esc_html_e('My account', 'matrix-starter'); ?></span>
      </a>

      <div class="nav-line hidden shrink-0 lg:block" aria-hidden="true"></div>

      <button
        type="button"
        class="text-reg-font relative z-50 flex shrink-0 cursor-pointer flex-row items-center justify-center"
        @click="showSearch = !showSearch"
        :aria-expanded="showSearch.toString()"
        aria-controls="rd-search-panel"
      >
        <span class="iconify inline-flex h-8 w-8 shrink-0 items-center justify-center" data-icon="ion:search" aria-hidden="true"></span>
        <span class="sr-only"><?php esc_html_e('Toggle search', 'matrix-starter'); ?></span>
      </button>

      <div class="nav-line hidden shrink-0 lg:block" aria-hidden="true"></div>
    </div>
  </div>

  <div
    class="js-cart-header flex shrink-0 items-center justify-center pl-2 pr-0 md:pl-4"
    data-rd-cart
    data-initial-count="<?php echo (int) $cart['count']; ?>"
    data-initial-total="<?php echo (int) $cart['count'] > 0 ? esc_attr($cart['total_plain']) : ''; ?>"
    x-data="rdCartHeader()"
    x-init="init()"
  >
    <a
      class="z-50 reg-font cart-contents relative hidden flex-row items-center align-center lg:flex"
      href="<?php echo esc_url($cart_url); ?>"
      title="<?php esc_attr_e('View your shopping cart', 'woocommerce'); ?>"
      <?php echo function_exists('matrix_rd_uses_side_cart') && matrix_rd_uses_side_cart() ? 'data-rd-side-cart-trigger' : ''; ?>
    >
      <span class="iconify" data-icon="grommet-icons:basket" data-width="32" data-height="32" aria-hidden="true"></span>
      <span
        class="cart-contents-count text-tiny font-reg420 bg-red-critical w-[14px] h-[14px] flex items-center justify-center rounded-full border-2 border-black-border-solid p-2 basket-detail"
        x-text="cartCount"
        x-show="cartCount > 0"
        x-cloak
      ></span>
      <span
        class="ml-2 cart-total cart-contents-total amount text-reg-font font-reg420"
        x-text="cartTotal"
        x-show="cartCount > 0"
        x-cloak
      ></span>
    </a>
  </div>
</div>
<script>
  (function () {
    function alignTopNav() {
      if (typeof window.matrixRdScheduleDesktopTopNav === 'function') {
        window.matrixRdScheduleDesktopTopNav();
      } else if (typeof window.matrixRdAlignDesktopTopNav === 'function') {
        window.matrixRdAlignDesktopTopNav();
      }
    }

    if (window.requestAnimationFrame) {
      window.requestAnimationFrame(function () {
        window.requestAnimationFrame(alignTopNav);
      });
    } else {
      window.setTimeout(alignTopNav, 0);
    }

    window.addEventListener('load', alignTopNav, { once: true });
  })();
</script>
