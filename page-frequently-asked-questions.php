<?php
/**
 * FAQs page — legacy template-faqs (all FAQ CPT posts).
 */
get_header();
?>
<main id="main-content" class="site-main w-full overflow-hidden bg-white">
  <?php
  while (have_posts()) :
      the_post();
      get_template_part('template-parts/header/page-header-rd');
      ?>
  <div class="bg-white">
    <div class="px-4 mx-auto lg:max-w-max-1549">
      <section class="relative pt-4 lg:pb-20">
        <div class="gutenburg entry-content max-w-none">
          <?php the_content(); ?>
        </div>
      </section>
      <?php
      get_template_part('template-parts/pages/faqs-all', null, [
          'show_view_all' => false,
      ]);
      ?>
    </div>
    <?php get_template_part('template-parts/footer/site-links'); ?>
  </div>
      <?php
  endwhile;
  ?>
</main>
<?php
get_footer();
