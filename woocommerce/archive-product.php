<?php
/**
 * Shop archive — Our Donuts (legacy archive-product.blade.php).
 *
 * @see https://therollingdonut.ie/our-donuts/
 */
defined('ABSPATH') || exit;

get_header();

do_action('woocommerce_before_main_content');
?>
<div class="mt-0 space-top-sub"></div>
<?php wc_get_template('custom/woocommerce-header.php'); ?>

<div class="py-12 bg-white">
  <div class="mx-auto lg:max-w-max-1549">
    <?php if (woocommerce_product_loop()) : ?>
      <?php
      remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
      remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
      do_action('woocommerce_before_shop_loop');
      woocommerce_product_loop_start();
      ?>

      <?php if (wc_get_loop_prop('total')) : ?>
        <?php while (have_posts()) : ?>
          <?php
          the_post();
          do_action('woocommerce_shop_loop');
          wc_get_template_part('content', 'product');
          ?>
        <?php endwhile; ?>
      <?php endif; ?>

      <?php
      woocommerce_product_loop_end();
      do_action('woocommerce_after_shop_loop');
      ?>
    <?php else : ?>
      <?php do_action('woocommerce_no_products_found'); ?>
    <?php endif; ?>

    <?php
    $vegan_products = new WP_Query([
      'post_type'      => 'product',
      'posts_per_page' => -1,
      'tax_query'      => [
        [
          'taxonomy' => 'product_tag',
          'field'    => 'slug',
          'terms'    => 'vegan',
        ],
      ],
    ]);
    ?>
    <?php if ($vegan_products->have_posts()) : ?>
      <h4 class="pb-12 w-full max-md:pl-8 max-sm:py-12 product-category-title font-edmondsans text-xl-font font-reg420">
        <?php esc_html_e('Vegan', 'matrix-starter'); ?>
      </h4>
      <ul class="flex flex-row flex-wrap gap-4 justify-start px-2 products vegan max-mobile:mt-0 lg:gap-6 desktop:px-0 lg:px-4">
        <?php while ($vegan_products->have_posts()) : ?>
          <?php
          $vegan_products->the_post();
          wc_get_template_part('content', 'product');
          ?>
        <?php endwhile; ?>
      </ul>
      <?php wp_reset_postdata(); ?>
    <?php endif; ?>

    <?php get_template_part('template-parts/footer/site-links'); ?>
  </div>
</div>

<?php
do_action('woocommerce_after_main_content');
do_action('woocommerce_sidebar');
get_footer();
