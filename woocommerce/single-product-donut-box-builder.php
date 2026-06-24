<?php
defined('ABSPATH') || exit;

global $product;

if (!is_a($product, 'WC_Product')) {
    $product = wc_get_product(get_the_ID());
}

if (!$product || $product->get_type() !== 'donut_box_builder') {
    wc_get_template_part('single-product');
    return;
}

get_header('shop');

do_action('woocommerce_before_main_content');

while (have_posts()) :
    the_post();
    wc_get_template_part('content', 'single-product-donut-box-builder');
endwhile;

do_action('woocommerce_after_main_content');

get_footer('shop');
?>
