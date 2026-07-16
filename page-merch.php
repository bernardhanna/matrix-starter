<?php
/**
 * Merch products — legacy template-merch-products.
 *
 * Template Name: Merch Products
 */
defined('ABSPATH') || exit;

get_header();

do_action('woocommerce_before_main_content');

$ordered_categories = matrix_rd_acf_terms(
    get_field('merch_ordered_categories') ?: get_post_meta(get_the_ID(), 'merch_ordered_categories', true)
);
set_query_var('matrix_rd_filter_categories', $ordered_categories);
?>
<div class="woocommerce mt-0 space-top-sub" data-product-type="Merch">
  <?php wc_get_template('custom/woocommerce-header.php'); ?>

  <div class="bg-white">
    <div class="px-2 pb-20 mx-auto mobile:px-4 pt-7 lg:max-w-max-1485">
      <ul class="flex flex-row flex-wrap w-full filter products columns-3">
        <?php
        $merch_query = new WP_Query([
            'post_type'      => 'product',
            'posts_per_page' => -1,
            'tax_query'      => [
                [
                    'taxonomy' => 'rd_product_type',
                    'field'    => 'slug',
                    'terms'    => 'merch',
                ],
            ],
        ]);

        if ($merch_query->have_posts()) {
            matrix_rd_render_product_query($merch_query, true);
        } else {
            echo '<li class="w-full py-8 text-center">' . esc_html__('No Merch products found.', 'matrix-starter') . '</li>';
        }
        ?>
      </ul>
    </div>
  </div>
</div>
<?php
do_action('woocommerce_after_main_content');
get_footer();
