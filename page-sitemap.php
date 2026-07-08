<?php
/**
 * Sitemap page — legacy template-sitemap (HTML list from sitemap_navigation menu).
 */
get_header();
?>
<main id="main-content" class="site-main w-full overflow-hidden bg-white">
  <?php get_template_part('template-parts/header/page-header-rd'); ?>
  <div class="w-full px-4 pb-8 mx-auto overflow-hidden lg:px-4 lg:max-w-max-1549">
    <?php get_template_part('template-parts/pages/sitemap-nav'); ?>
  </div>
</main>
<?php
get_footer();
