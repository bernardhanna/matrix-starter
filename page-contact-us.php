<?php
/**
 * Contact Us — legacy template-contact.
 */
get_header();
?>
<main id="main-content" class="site-main w-full overflow-hidden bg-white">
  <?php
  while (have_posts()) :
      the_post();
      get_template_part('template-parts/header/page-header-rd');
      ?>
  <div class="px-4 mx-auto lg:max-w-max-1336">
    <section class="relative pt-6 pb-0 md:pt-10 md:pb-20">
      <div class="gutenburg entry-content max-w-none">
        <?php the_content(); ?>
      </div>
      <?php load_flexible_content_templates(get_the_ID()); ?>
    </section>
    <?php get_template_part('template-parts/pages/faqs-selected'); ?>
    <?php get_template_part('template-parts/footer/site-links'); ?>
  </div>
      <?php
  endwhile;
  ?>
</main>
<?php
get_footer();
