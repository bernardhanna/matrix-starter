<?php
/**
 * Full-screen mobile menu.
 */
$nav_items      = $args['nav_items'] ?? [];
$mobile_menu_bg = $args['mobile_menu_bg'] ?? matrix_rd_nav_mobile_bg();
$bg_style       = $mobile_menu_bg !== ''
    ? '--mobile-bg-image: url(' . esc_url($mobile_menu_bg) . '); background-image: var(--mobile-bg-image);'
    : '';
$menu_bg_class  = $mobile_menu_bg !== '' ? 'rd-mobile-menu--has-bg' : 'bg-black-full';
?>
<div
  id="rd-mobile-menu"
  @resize.window="if (window.innerWidth > 1210) open = false"
  x-cloak
  x-show="open"
  x-transition:enter="transition ease-out duration-500"
  x-transition:enter-start="opacity-0 -translate-y-full"
  x-transition:enter-end="opacity-100 translate-y-0"
  x-transition:leave="transition ease-in duration-200"
  x-transition:leave-start="opacity-100 translate-y-0"
  x-transition:leave-end="opacity-0 -translate-y-full"
  class="rd-mobile-menu <?php echo esc_attr($menu_bg_class); ?> fixed inset-0 flex h-screen w-full flex-col text-white"
  style="<?php echo esc_attr($bg_style); ?> background-repeat: no-repeat; background-size: cover; background-position: center; z-index: 99;"
  role="dialog"
  aria-modal="true"
  aria-label="<?php esc_attr_e('Mobile menu', 'matrix-starter'); ?>"
>
  <nav class="rd-mobile-menu__nav mx-auto flex w-full max-w-lg flex-1 flex-col overflow-y-auto px-4 pb-10 text-center" aria-label="<?php esc_attr_e('Mobile navigation', 'matrix-starter'); ?>">
    <ul class="rd-mobile-menu__list flex w-full flex-1 list-none flex-col items-center justify-center p-0">
      <?php foreach ($nav_items as $nav_item) : ?>
        <?php
        $is_order_cta = matrix_rd_nav_is_order_cta($nav_item);
        $item_classes = trim((string) ($nav_item->classes ?? ''));
        if ($is_order_cta) {
            $item_classes = matrix_rd_nav_sanitize_order_cta_classes($item_classes);
            $link_class   = trim('mob-menu-order-btn text-sm-md-font font-medium hover:no-underline ' . $item_classes);
        } else {
            $link_class = trim('text-sm-md-font font-medium text-white ' . $item_classes);
        }
        $item_li_class = 'rd-mobile-menu__item my-5 sm:my-6' . ($is_order_cta ? ' rd-mobile-menu__item--cta' : '');
        $row_class     = $is_order_cta ? 'flex w-full items-center justify-center' : 'flex items-center justify-center';
        ?>
        <li x-data="{ isOpen: false }" class="<?php echo esc_attr($item_li_class); ?>">
          <div class="<?php echo esc_attr($row_class); ?>">
            <a
              href="<?php echo esc_url($nav_item->url); ?>"
              class="<?php echo esc_attr($link_class); ?>"
              role="menuitem"
            >
              <?php if ($is_order_cta) : ?>
                <svg class="mob-menu-order-btn__icon shrink-0 fill-black-full" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 34 34" fill="none" aria-hidden="true">
                  <path d="M32 17C32 25.2843 25.2843 32 17 32V34C26.3888 34 34 26.3888 34 17H32ZM17 2C25.2843 2 32 8.71573 32 17H34C34 7.61116 26.3888 0 17 0V2ZM2 17C2 8.71573 8.71573 2 17 2V0C7.61116 0 0 7.61116 0 17H2ZM17 32C8.71573 32 2 25.2843 2 17H0C0 26.3888 7.61116 34 17 34V32ZM21.3333 17C21.3333 19.3932 19.3932 21.3333 17 21.3333V23.3333C20.4978 23.3333 23.3333 20.4978 23.3333 17H21.3333ZM17 12.6667C19.3932 12.6667 21.3333 14.6068 21.3333 17H23.3333C23.3333 13.5022 20.4978 10.6667 17 10.6667V12.6667ZM12.6667 17C12.6667 14.6068 14.6068 12.6667 17 12.6667V10.6667C13.5022 10.6667 10.6667 13.5022 10.6667 17H12.6667ZM17 21.3333C14.6068 21.3333 12.6667 19.3932 12.6667 17H10.6667C10.6667 20.4978 13.5022 23.3333 17 23.3333V21.3333Z" />
                  <path d="M27.5096 14.7855L27.5095 14.7881C27.4734 15.629 27.6744 16.3322 28.0488 16.8697C28.4217 17.405 28.9875 17.8065 29.7373 18.0132C29.9445 18.0683 30.1528 18.1231 30.3582 18.1752L27.5096 14.7855ZM27.5096 14.7855C27.5189 14.5391 27.5375 14.2911 27.557 14.0322L27.5571 14.0317L27.5598 13.9953C27.6164 13.2362 27.6817 12.3607 27.4741 11.5077C26.7838 8.64063 25.0424 6.7799 22.3239 6.05299L22.3221 6.05251C22.1362 6.00355 21.9485 5.95733 21.7653 5.91224L21.7633 5.91175L21.7633 5.91174L21.7611 5.9112C21.7256 5.90264 21.6903 5.89416 21.6552 5.88574C21.4143 5.8279 21.1831 5.77239 20.959 5.70434L20.9579 5.70402C19.6097 5.29799 18.7931 4.15317 18.8356 2.66202L18.8356 2.66115C18.8392 2.52667 18.85 2.39525 18.8623 2.24692C18.865 2.21395 18.8678 2.18013 18.8706 2.14527C18.8722 2.12502 18.8739 2.10441 18.8756 2.08348C18.8897 1.9094 18.9057 1.71265 18.9082 1.51652C21.4246 1.88478 23.6525 2.77875 25.539 4.16582C30.4025 7.7431 32.7356 12.6734 32.482 18.873M27.5096 14.7855L32.482 18.873M32.482 18.873C31.7807 18.5354 31.046 18.3493 30.3819 18.1812L30.3585 18.1753L32.482 18.873Z" stroke="currentColor" />
                </svg>
              <?php endif; ?>
              <?php echo esc_html($nav_item->label); ?>
            </a>
            <?php if (! empty($nav_item->children)) : ?>
              <button
                type="button"
                class="ml-2 shrink-0"
                @click="isOpen = !isOpen"
                :aria-expanded="isOpen.toString()"
                aria-label="<?php echo esc_attr(sprintf(__('Toggle %s submenu', 'matrix-starter'), $nav_item->label)); ?>"
              >
                <span class="sr-only"><?php echo esc_html(sprintf(__('Toggle %s submenu', 'matrix-starter'), $nav_item->label)); ?></span>
                <span x-show="!isOpen" class="iconify text-white" data-icon="mdi:chevron-down" data-width="32" data-height="32" aria-hidden="true"></span>
                <span x-show="isOpen" class="iconify text-white" data-icon="mdi:chevron-up" data-width="32" data-height="32" aria-hidden="true"></span>
              </button>
            <?php endif; ?>
          </div>
          <?php if (! empty($nav_item->children)) : ?>
            <ul
              x-show="isOpen"
              x-transition
              class="submenu list-none p-0"
              role="menu"
            >
              <?php foreach ($nav_item->children as $child) : ?>
                <li class="my-3 sm:my-4">
                  <a
                    class="text-sm-md-font font-medium text-white flex items-center justify-center <?php echo esc_attr((string) ($child->classes ?? '')); ?>"
                    href="<?php echo esc_url($child->url); ?>"
                    role="menuitem"
                  >
                    <?php echo esc_html($child->label); ?>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </nav>
</div>
