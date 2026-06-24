<?php
/**
 * Template for Our Shops (/our-shops/) — legacy template-locations.
 *
 * @see https://therollingdonut.ie/our-shops/
 */
get_header();
?>
<main id="main-content" class="site-main w-full overflow-hidden">
  <div class="mt-0 space-top-sub"></div>
  <?php get_template_part('template-parts/header/page-header-rd'); ?>
  <section class="relative -top-36 right-0 left-0 w-full">
    <div id="map" class="h-[500px] mobile:h-[640px] w-full z-10"></div>
  </section>
  <div class="w-full">
    <div class="px-4 mx-auto max-w-max-1571">
      <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
          <section class="relative text-center -top-20 location-content">
            <div class="entry-content">
              <?php the_content(); ?>
            </div>
          </section>
        <?php endwhile; ?>
      <?php endif; ?>
      <?php get_template_part('template-parts/locations/list'); ?>
      <?php get_template_part('template-parts/footer/site-links'); ?>
    </div>
  </div>
</main>
<?php
get_footer();
