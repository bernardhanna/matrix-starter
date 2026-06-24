<?php
/**
 * Weddings & Corporate — legacy template-flexi.
 */
get_header();
?>
<main id="main-content" class="site-main w-full overflow-hidden">
  <?php
  while (have_posts()) :
      the_post();
      $featured = get_the_post_thumbnail_url(get_the_ID(), 'full');
      ?>
  <div class="bg-white"<?php echo $featured ? ' style="background-image:url(' . esc_url($featured) . ');background-size:cover;background-position:center"' : ''; ?>>
    <?php get_template_part('template-parts/header/page-header-rd'); ?>
    <?php load_flexible_content_templates(get_the_ID()); ?>
    <?php get_template_part('template-parts/footer/site-links'); ?>
  </div>
      <?php
  endwhile;
  ?>
</main>
<?php
get_footer();
