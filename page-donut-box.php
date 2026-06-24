<?php
/**
 * Donut Box products — legacy template-box-products.
 *
 * @see https://therollingdonut.ie/donut-box/
 */
defined('ABSPATH') || exit;

get_header();

do_action('woocommerce_before_main_content');

while (have_posts()) {
    the_post();
    $ordered_categories = matrix_rd_acf_terms(
        get_field('ordered_categories') ?: get_post_meta(get_the_ID(), 'ordered_categories', true)
    );
    set_query_var('matrix_rd_filter_categories', $ordered_categories);
    ?>
<div class="woocommerce mt-0 space-top-sub" data-product-type="Box">
  <?php wc_get_template('custom/woocommerce-header.php'); ?>

  <div class="w-full bg-white">
    <?php get_template_part('template-parts/home/services'); ?>

    <div class="px-2 pb-20 mx-auto mobile:px-4 lg:max-w-max-100">
      <ul class="flex flex-row flex-wrap justify-between filter products columns-3">
        <?php
        if ($ordered_categories !== []) {
            foreach ($ordered_categories as $product_category) {
                $box_query = new WP_Query([
                    'post_type'      => 'product',
                    'posts_per_page' => -1,
                    'tax_query'      => [
                        'relation' => 'AND',
                        [
                            'taxonomy' => 'rd_product_type',
                            'field'    => 'slug',
                            'terms'    => 'box',
                        ],
                        [
                            'taxonomy' => 'product_cat',
                            'field'    => 'term_id',
                            'terms'    => (int) $product_category->term_id,
                        ],
                    ],
                ]);

                if (! $box_query->have_posts()) {
                    wp_reset_postdata();
                    continue;
                }

                $category_description = term_description((int) $product_category->term_id, 'product_cat');
                ?>
        <h4 class="w-full product-category-title font-edmondsans text-xl-font font-reg420"><?php echo esc_html($product_category->name); ?></h4>
                <?php if ($category_description) : ?>
        <span class="relative -mt-2 leading-none -top-2 category-description text-reg-font text-black-font w-full block mb-4"><?php echo esc_html(wp_strip_all_tags($category_description)); ?></span>
                <?php endif; ?>
                <?php
                matrix_rd_render_product_query($box_query, true);
            }
        } else {
            echo '<li class="w-full py-8 text-center">' . esc_html__('No ordered categories found.', 'matrix-starter') . '</li>';
        }
        ?>
      </ul>
    </div>
  </div>
</div>
    <?php
}
do_action('woocommerce_after_main_content');
get_footer();
