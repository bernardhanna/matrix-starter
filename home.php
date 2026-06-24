<?php
/**
 * Blog posts index — legacy index.blade.php.
 */
get_header();
?>
<main id="main-content" class="site-main w-full overflow-hidden">
  <?php get_template_part('template-parts/header/page-header-rd'); ?>
  <div class="px-4 mx-auto max-w-max-1596 desktop:px-0 bg-white">
    <?php get_template_part('template-parts/blog/featured'); ?>
    <?php get_template_part('template-parts/blog/archive'); ?>
  </div>
  <?php get_template_part('template-parts/footer/site-links'); ?>
</main>
<?php
get_footer();
