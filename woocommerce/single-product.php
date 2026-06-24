<?php
/**
 * Single product — ported from legacy single-product.blade.php.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 1.6.4
 */
defined('ABSPATH') || exit;

get_header();

do_action('woocommerce_before_main_content');

global $product;
$product = wc_get_product(get_queried_object_id());

// All single products lead with the product image — the hero header band (banner
// image + duplicate title + price) is dropped everywhere for a consistent layout.
// The title and price are shown together in the summary instead (see
// matrix_rd_single_visible_price / matrix_rd_product_summary_header_open).
?>
<div class="relative bg-no-repeat bg-cover removebg max-lg:z-10 max-lg:-top-6">
  <div class="w-full px-0 mx-auto lg:px-4">
    <?php
    if ($product instanceof WC_Product && (! $product->is_type('wooextmm') || $product->is_type('woosb'))) {
        while (have_posts()) {
            the_post();
            $product = wc_get_product(get_the_ID());

            if (! $product instanceof WC_Product) {
                continue;
            }

            if ($product->is_type('woosb')) {
                wc_get_template_part('content', 'single-product');
            } elseif ($product->get_type() === 'donut_box_builder') {
                wc_get_template_part('content', 'single-product-donut-box-builder');
            } else {
                wc_get_template_part('content', 'single-product');
            }
        }
    }

    $product = wc_get_product(get_queried_object_id());
    if ($product instanceof WC_Product && $product->is_type('wooextmm') && ! $product->is_type('woosb')) {
        do_action('mixmatch_after_single_product_summary');
    }

    do_action('woocommerce_after_main_content');
    ?>
  </div>
</div>
<?php
get_footer();
