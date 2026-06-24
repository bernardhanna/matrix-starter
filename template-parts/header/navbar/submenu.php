<?php
/**
 * Desktop submenu dropdown.
 */
$nav_item = $args['nav_item'] ?? null;

if (empty($nav_item) || empty($nav_item->children)) {
    return;
}

$children = $nav_item->children;
$last_key = array_key_last($children);
?>
<div
  x-cloak
  x-show="open"
  x-transition.opacity
  class="submenu z-999999 absolute left-0 top-full mt-0 flex w-[200px] flex-col bg-white pt-2 shadow-[0_8px_0_0_rgba(0,0,0,0.2)]"
  role="menu"
>
  <?php foreach ($children as $key => $child) : ?>
    <?php
    $child_classes = trim((string) ($child->classes ?? ''));
    $border_top    = $key !== array_key_first($children) ? 'border-t border-black' : '';
    $border_bottom = $key !== $last_key ? 'border-b border-black' : '';
    ?>
    <a
      <?php if (! empty($child->target)) : ?>
        target="<?php echo esc_attr($child->target); ?>"
      <?php endif; ?>
      class="<?php echo esc_attr(trim("py-4 px-4 w-full text-reg-font font-reg420 text-black-full hover:bg-yellow-primary hover:text-black-full {$border_top} {$border_bottom} {$child_classes}")); ?>"
      href="<?php echo esc_url($child->url); ?>"
      role="menuitem"
    >
      <?php echo esc_html($child->label); ?>
    </a>
  <?php endforeach; ?>
</div>
