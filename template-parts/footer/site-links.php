<?php
/**
 * Site links row (Theme Options repeater).
 */
// Pages where the quick-links grid stays visible on desktop (matches legacy site).
$hide = 'lg:hidden';
if (
    is_page(['our-shops', 'contact-us', 'frequently-asked-questions'])
    || (function_exists('is_shop') && is_shop())
    || (function_exists('is_woocommerce') && is_woocommerce())
) {
    $hide = '';
}

$padding = is_page('our-shops') ? 'px-0' : 'px-2';
$links   = matrix_rd_acf_repeater_rows('site_links', ['site_links'], 'option');

if ($links === []) {
    return;
}
?>
<section class="my-8 lg:max-w-max-1341 lg:mx-auto sitelinks flex flex-col <?php echo esc_attr($padding); ?> md:px-6 lg:p-0 <?php echo esc_attr($hide); ?> w-full lg:grid lg:grid-rows-3 grid-flow-col gap-3">
  <?php foreach ($links as $row) :
      $link = matrix_rd_acf_link($row['site_links'] ?? null);
      if ($link['url'] === '') {
          continue;
      }
      ?>
  <a target="<?php echo esc_attr($link['target']); ?>" href="<?php echo esc_url($link['url']); ?>" class="flex items-center justify-between p-4 pl-4 rounded-[8px] border border-black bg-white hover:bg-yellow-primary shadow-lg text-sm-md-font font-medium boxshadow h-[75px]">
    <?php echo esc_html(html_entity_decode($link['title'])); ?>
    <img src="https://api.iconify.design/ph:caret-right-bold.svg?width=18&height=18" alt="" width="18" height="18" />
  </a>
  <?php endforeach; ?>
</section>
