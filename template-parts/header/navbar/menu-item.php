<?php
/**
 * Single top-level desktop menu item.
 */
$nav_item        = $args['nav_item'] ?? null;
$nav_is_last_cta = ! empty($args['nav_is_last_cta']);
$nav_tabindex    = (int) ($args['nav_tabindex'] ?? 0);

if (empty($nav_item)) {
    return;
}

$is_active         = matrix_rd_nav_item_active($nav_item);
$is_order_cta      = matrix_rd_nav_is_order_cta($nav_item);
$nav_item_li_class = trim(
    ($args['nav_item_li_class'] ?? 'group relative overflow-visible') .
    ($is_order_cta ? ' rd-nav-cta-item' : '')
);
$link_class        = trim(
    (($nav_is_last_cta || $is_order_cta) ? 'btn-menu ' : '') .
    'text-reg-font font-reg420 text-black-full whitespace-nowrap flex items-center hover:underline ' .
    ($is_active ? 'active' : '') . ' ' .
    (string) ($nav_item->classes ?? '')
);
$show_order_icon = $nav_is_last_cta || $is_order_cta;
?>
<li x-data="{ open: false }" class="<?php echo esc_attr($nav_item_li_class); ?>" role="none">
  <div @mouseenter="open = true" @mouseleave="open = false">
    <a
      <?php if (! empty($nav_item->target)) : ?>
        target="<?php echo esc_attr($nav_item->target); ?>"
      <?php endif; ?>
      class="<?php echo esc_attr($link_class); ?>"
      href="<?php echo esc_url($nav_item->url); ?>"
      role="menuitem"
      <?php if (! empty($nav_item->children)) : ?>
        aria-haspopup="true"
        aria-expanded="false"
        x-bind:aria-expanded="open.toString()"
      <?php endif; ?>
    >
      <?php if ($show_order_icon) : ?>
        <svg class="btn-menu__icon" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 34 34" fill="none" aria-hidden="true">
          <path d="M32 17C32 25.2843 25.2843 32 17 32V34C26.3888 34 34 26.3888 34 17H32ZM17 2C25.2843 2 32 8.71573 32 17H34C34 7.61116 26.3888 0 17 0V2ZM2 17C2 8.71573 8.71573 2 17 2V0C7.61116 0 0 7.61116 0 17H2ZM17 32C8.71573 32 2 25.2843 2 17H0C0 26.3888 7.61116 34 17 34V32ZM21.3333 17C21.3333 19.3932 19.3932 21.3333 17 21.3333V23.3333C20.4978 23.3333 23.3333 20.4978 23.3333 17H21.3333ZM17 12.6667C19.3932 12.6667 21.3333 14.6068 21.3333 17H23.3333C23.3333 13.5022 20.4978 10.6667 17 10.6667V12.6667ZM12.6667 17C12.6667 14.6068 14.6068 12.6667 17 12.6667V10.6667C13.5022 10.6667 10.6667 13.5022 10.6667 17H12.6667ZM17 21.3333C14.6068 21.3333 12.6667 19.3932 12.6667 17H10.6667C10.6667 20.4978 13.5022 23.3333 17 23.3333V21.3333Z" fill="currentColor"/>
          <path d="M27.5096 14.7855C27.4734 15.629 27.6744 16.3322 28.0488 16.8697C28.4217 17.405 28.9875 17.8065 29.7373 18.0132C29.9445 18.0683 30.1528 18.1231 30.3582 18.1752L30.3585 18.1753C31.046 18.3493 31.7807 18.5354 32.482 18.873C32.7356 12.6734 30.4025 7.7431 25.539 4.16582C23.6525 2.77875 21.4246 1.88478 18.9082 1.51652C18.9057 1.71265 18.8897 1.9094 18.8756 2.08348C18.8678 2.18013 18.865 2.21395 18.8623 2.24692C18.85 2.39525 18.8392 2.52667 18.8356 2.66115C18.7931 4.15317 19.6097 5.29799 20.9579 5.70402C21.1831 5.77239 21.4143 5.8279 21.6552 5.88574C21.9485 5.95733 22.1362 6.00355 22.3221 6.05251C25.0424 6.7799 26.7838 8.64063 27.4741 11.5077C27.6817 12.3607 27.6164 13.2362 27.5598 13.9953C27.5375 14.2911 27.5189 14.5391 27.5096 14.7855Z" fill="currentColor"/>
        </svg>
      <?php endif; ?>
      <?php echo esc_html($nav_item->label); ?>
      <?php if (! empty($nav_item->children)) : ?>
        <span class="iconify laptop:ml-2 group-hover:hidden" data-icon="basil:caret-down-outline" aria-hidden="true"></span>
        <span class="iconify laptop:ml-2 hidden group-hover:block" data-icon="basil:caret-up-solid" aria-hidden="true"></span>
      <?php endif; ?>
    </a>
    <?php if (! empty($nav_item->children)) : ?>
      <?php get_template_part('template-parts/header/navbar/submenu', null, ['nav_item' => $nav_item]); ?>
    <?php endif; ?>
  </div>
</li>
