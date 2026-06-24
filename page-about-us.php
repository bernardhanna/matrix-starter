<?php
/**
 * About us — legacy template-about.
 */
get_header();
?>
<main id="main-content" class="site-main w-full overflow-hidden bg-white">
  <?php
  while (have_posts()) :
      the_post();
      get_template_part('template-parts/header/page-header-rd');
      ?>
  <div class="w-full m-auto bg-transparent pt-0 md:pt-10 laptop:w-90">
    <div class="relative w-full px-4 ml-auto pb-10 mr-auto max-w-max-1038 gutenburg">
      <div class="flex flex-col-reverse gap-4 lg:flex-row">
        <div class="w-full lg:w-1/2">
          <?php the_content(); ?>
        </div>
        <div class="w-full lg:w-1/2">
          <?php get_template_part('template-parts/pages/video-block'); ?>
        </div>
      </div>
    </div>
    <?php get_template_part('template-parts/pages/header-image'); ?>
  </div>
      <?php
      matrix_rd_render_our_story(get_the_ID());
      ?>
  <div class="px-4 mx-auto lg:max-w-max-1549">
    <?php get_template_part('template-parts/pages/faqs-all'); ?>
  </div>
  <?php get_template_part('template-parts/footer/site-links'); ?>
      <?php
  endwhile;
  ?>
  <?php get_template_part('template-parts/footer/instagram-follow'); ?>
</main>
<?php
get_footer();
