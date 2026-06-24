<?php
/**
 * Sitemap page navigation list (legacy template-sitemap).
 */
$navigation = matrix_rd_sitemap_nav_tree();

if ($navigation === []) {
    return;
}
?>
<ul class="relative overflow-hidden sitemap-menu lg:left-8">
  <?php foreach ($navigation as $item) : ?>
  <li class="sitemap-menu-item list-disc <?php echo esc_attr(trim($item->classes . ($item->active ? ' active' : ''))); ?>">
    <a class="font-light text-sm-md-font font-laca" href="<?php echo esc_url($item->url); ?>">
      <?php echo wp_kses_post($item->label); ?>
    </a>
    <?php if ($item->children !== []) : ?>
    <ul class="sitemap-sub-menu">
      <?php foreach ($item->children as $child) : ?>
      <li class="sitemap-sub-menu-item <?php echo esc_attr(trim($child->classes . ($child->active ? ' active' : ''))); ?>">
        <a class="font-light text-base-font font-laca" href="<?php echo esc_url($child->url); ?>">
          <?php echo wp_kses_post($child->label); ?>
        </a>
      </li>
      <?php endforeach; ?>
    </ul>
    <?php endif; ?>
  </li>
  <?php endforeach; ?>
</ul>
