<?php
/**
 * Single blog post — legacy resources/views/single.blade.php.
 *
 * @package Matrix_Starter
 */

get_header();
?>
<main id="main-content" class="site-main single-post w-full overflow-hidden bg-white">
<?php
while (have_posts()) :
    the_post();
    get_template_part('template-parts/blog/single-hero');
    ?>
  <div class="mx-auto max-w-[1296px] bloghead flex flex-col lg:flex-row py-12 px-4 lg:px-8 macbook:px-0">
    <div class="w-full lg:w-3/4">
      <article <?php post_class('h-entry'); ?> id="post-<?php the_ID(); ?>">
        <div class="e-content mb-8 laptop:w-85 entry-content gutenburg">
          <?php the_content(); ?>
        </div>
        <?php
        wp_link_pages([
            'before' => '<nav class="page-nav"><p>' . esc_html__('Pages:', 'matrix-starter'),
            'after'  => '</p></nav>',
            'echo'   => 1,
        ]);
        ?>
      </article>
    </div>
    <div class="w-full lg:w-sidebar">
      <?php get_template_part('template-parts/blog/related-sidebar'); ?>
    </div>
  </div>
    <?php
endwhile;
?>
</main>
<?php
get_footer();
