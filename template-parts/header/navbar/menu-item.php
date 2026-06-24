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

$is_active       = matrix_rd_nav_item_active($nav_item);
$nav_item_li_class = $args['nav_item_li_class'] ?? 'group relative overflow-visible';
$link_class      = trim(
    ($nav_is_last_cta ? 'btn-menu ' : '') .
    'text-reg-font font-reg420 text-black-full whitespace-nowrap flex items-center hover:underline ' .
    ($is_active ? 'active' : '') . ' ' .
    (string) ($nav_item->classes ?? '')
);
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
