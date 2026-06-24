<?php
/**
 * Sitemap page — legacy template-sitemap (HTML list from sitemap_navigation menu).
 */
get_header();
?>
<main id="main-content" class="site-main w-full overflow-hidden bg-white">
  <div class="px-8 pb-8 mx-auto overflow-hidden lg:px-4 lg:max-w-max-1549">
    <h1 class="text-lg-font laptop:text-xl-font font-reg420"><?php esc_html_e('Sitemap', 'matrix-starter'); ?></h1>
    <?php get_template_part('template-parts/pages/sitemap-nav'); ?>
  </div>
</main>
<?php
get_footer();
