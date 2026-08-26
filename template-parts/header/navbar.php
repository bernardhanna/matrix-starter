<?php
/**
 * Rolling Donut site header + navigation (legacy sections/header.blade.php).
 * Headroom: one bar, fixed after scroll; visible on scroll up, hidden on scroll down.
 */

if (! matrix_rd_nav_should_show()) {
    return;
}

$nav_all     = matrix_rd_nav_items();
$nav_split   = matrix_rd_nav_split($nav_all, 4);
$logos       = matrix_rd_nav_logos();
$telephone   = matrix_rd_nav_telephone();
$cart        = matrix_rd_nav_cart();
$mobile_bg   = matrix_rd_nav_mobile_bg();
$is_thankyou = matrix_rd_nav_is_thankyou();
$logo_only   = function_exists('matrix_rd_nav_is_logo_only') && matrix_rd_nav_is_logo_only();

$show_topbar = true;
if ($logo_only || (function_exists('is_cart') && (is_cart() || is_checkout()) && is_user_logged_in())) {
    $show_topbar = false;
}

$inner_args = [
    'nav_split'    => $nav_split,
    'nav_all'      => $nav_all,
    'logos'        => $logos,
    'telephone'    => $telephone,
    'cart'         => $cart,
    'mobile_bg'    => $mobile_bg,
    'is_thankyou'  => $is_thankyou,
    'logo_only'    => $logo_only,
    'show_topnav'  => ! $logo_only,
];

$is_cart_or_checkout = (function_exists('is_cart') && is_cart())
    || (function_exists('is_checkout') && is_checkout());
?>
<header
  class="rd-header relative z-[200] w-full"
  :class="{ 'z-[1200]': open }"
  x-data="matrixRdHeadroom({ cartOrCheckout: <?php echo $is_cart_or_checkout ? 'true' : 'false'; ?> })"
  x-effect="open ? (document.body.style.overflow = 'hidden') : (document.body.style.overflow = ''); isPinned; isVisible; $nextTick(() => { measure(); if (typeof window.matrixRdScheduleDesktopTopNav === 'function') window.matrixRdScheduleDesktopTopNav(); })"
  @keydown.escape.window="open = false; showSearch = false"
>
  <div
    x-ref="headerBar"
    class="rd-header-bar w-full bg-white"
    :class="{
      'rd-header-bar--pinned': isPinned,
      'rd-header-bar--hidden': isPinned && !isVisible && !open,
      'rd-header-bar--menu-open': open
    }"
    :style="pinnedStyle"
  >
    <?php if ($show_topbar) : ?>
      <div x-show="!isPinned" x-cloak>
        <?php get_template_part('template-parts/header/navbar/topbar'); ?>
      </div>
    <?php endif; ?>

    <?php
    get_template_part('template-parts/header/navbar-inner', null, array_merge($inner_args, [
        'section_id' => 'site-nav',
    ]));
    ?>
  </div>

  <div class="rd-header-spacer" :style="{ height: spacerHeight }" aria-hidden="true"></div>

  <?php
  if (! $logo_only) {
      get_template_part('template-parts/header/navbar/search-panel', null, [
          'mobile_menu_bg' => $mobile_bg,
      ]);
  }
  ?>

</header>
