<?php
if (!defined('ABSPATH')) {
    exit;
}

global $product;
$product_id = $product->get_id();
$type_slug = function_exists('matrix_rd_product_type_slug')
    ? matrix_rd_product_type_slug($product_id)
    : null;

$term_ids = wp_get_post_terms($product_id, 'rd_product_type', array('fields' => 'ids'));
if (empty($term_ids) || is_wp_error($term_ids)) {
    return;
}

$args = array(
    'post_type'      => 'product',
    'posts_per_page' => 4,
    'post__not_in'   => array($product_id),
    'tax_query'      => array(
        array(
            'taxonomy' => 'rd_product_type',
            'field'    => 'term_id',
            'terms'    => $term_ids,
            'operator' => 'IN',
        ),
    ),
);

$related_products = new WP_Query($args);

if (! $related_products->have_posts()) {
    return;
}
?>

<div class="w-full overflow-hidden related rd-related-product pb-5-5rem product <?php echo ! is_product() ? 'notebook:px-4' : ''; ?>">
    <h4 class="px-4 pt-12 pb-4 text-black-full text-sm-md-font lg:text-lg-font font-reg420">
        <?php
        if ($type_slug === 'merch') {
            esc_html_e('Other designs you might like', 'rolling-donut');
        } elseif ($type_slug === 'box') {
            esc_html_e('Other boxes you might like', 'rolling-donut');
        } else {
            esc_html_e('Other donuts you might like', 'rolling-donut');
        }
        ?>
    </h4>

    <?php woocommerce_product_loop_start(); ?>

    <?php
    while ($related_products->have_posts()) :
        $related_products->the_post();
        global $product;
        $rd_product_type = get_rd_product_type($product->get_id());
        $button_url = (strcasecmp($rd_product_type, 'Donut') === 0) ? home_url('/donut-box/') : get_permalink();
        $product_allergens = function_exists('matrix_rd_acf_posts')
            ? matrix_rd_acf_posts(get_field('product_allergens', $product->get_id()))
            : array();
        ?>
        <li <?php wc_product_class('flex flex-col w-full relative lg:w-23 max-xs:w-full sm-mob:w-48 h-auto rd-related-product__item', $product); ?> x-data="{ showAllergens: false }">
            <?php if ($product_allergens !== []) : ?>
                <div class="absolute z-50 cursor-pointer top-4 right-4" @click.prevent="showAllergens = !showAllergens">
                    <div class="z-50" x-show="!showAllergens">
                        <span class="sr-only"><?php esc_html_e('info icon', 'rolling-donut'); ?></span>
                        <div class="allergen_svg"></div>
                    </div>
                    <div x-cloak x-show="showAllergens" class="z-50 relative rounded-t-lg top-1.5 right-1.5">
                        <span class="sr-only"><?php esc_html_e('close', 'rolling-donut'); ?></span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="23" height="22" viewBox="0 0 23 22" fill="none" aria-hidden="true">
                            <rect x="1.5" y="1" width="20" height="20" rx="10" fill="black" />
                            <circle cx="11.5" cy="11" r="11" fill="#FFED56" />
                            <path d="M11.4993 19.3346C16.1017 19.3346 19.8327 15.6037 19.8327 11.0013C19.8327 6.39893 16.1017 2.66797 11.4993 2.66797C6.89698 2.66797 3.16602 6.39893 3.16602 11.0013C3.16602 15.6037 6.89698 19.3346 11.4993 19.3346Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M14.5 14L8.5 8" stroke="#0E1217" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M8.5 14L14.5 8" stroke="#0E1217" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                </div>
            <?php endif; ?>

            <div class="relative w-full h-full bg-white border-2 border-black border-solid related-post hover:border-yellow-primary rounded-sm-8">
                <a class="block w-full" href="<?php echo esc_url($button_url); ?>">
                    <?php
                    echo woocommerce_get_product_thumbnail(
                        'woocommerce_thumbnail',
                        array(
                            'class' => 'w-full object-cover related-post-img h-[157px] lg:h-[200px] m-0',
                        )
                    );
                    ?>
                </a>
                <div class="relative top-0 left-0 z-10 w-full p-4 bg-white min-h-[200px] flex flex-col">
                    <h4 class="lg:pb-8 text-black-full text-mob-md-font font-reg420">
                        <a class="text-inherit no-underline" href="<?php echo esc_url($button_url); ?>"><?php the_title(); ?></a>
                    </h4>
                    <?php if (strcasecmp($rd_product_type, 'Donut') !== 0) : ?>
                        <span class="pb-4 text-black-full font-laca font-reg420 text-sm-md-font md:text-md-font"><?php woocommerce_template_loop_price(); ?></span>
                    <?php endif; ?>
                    <a
                        class="button w-full text-mob-xs-font md:text-sm-font font-reg420 h-[56px] flex justify-center items-center rounded-large border-black-full border-2 bg-white hover:bg-yellow-primary mt-auto"
                        href="<?php echo esc_url($button_url); ?>">
                        <?php
                        if (strcasecmp($rd_product_type, 'Donut') === 0) {
                            esc_html_e('Order now', 'rolling-donut');
                        } elseif (strcasecmp($rd_product_type, 'Merch') === 0) {
                            esc_html_e('View Product', 'rolling-donut');
                        } else {
                            esc_html_e('View Box', 'rolling-donut');
                        }
                        ?>
                    </a>
                </div>
            </div>
        </li>
    <?php endwhile; ?>

    <?php woocommerce_product_loop_end(); ?>
</div>

<?php
wp_reset_postdata();
