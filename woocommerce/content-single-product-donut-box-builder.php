<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css" />
<style>
    /* Slider specific styles */
    #box-container .in-box img {
        border-radius: 100%;
        border: 2px solid #ffed56;
        max-height: 100px;
        max-width: 100px;
        min-height: 100px;
        min-width: 100px;
    }

    #available-products {
        height: fit-content;
    }

    @media (max-width: 1084px) {
        #available-products .slick-track {
            text-align: center;
        }

        #available-products .slick-slide img {
            display: block;
            margin: auto;
        }
    }

    .max-w-160px {
        max-width: 160px;
    }

    .quantity {
        margin: auto;
        height: 44px;
        border: 1px solid #d8d7ce !important;
        padding: 1rem;
        border-radius: 2rem;
        width: 167px;
    }

    @media (min-width: 1200px) {
        .quantity {
            width: 200px;
        }
    }

    @media (min-width: 1200px) {
        .postid-3947 .quantity {
            width: auto;
            max-width: 150px;
        }
    }

    .input-text.qty.text.hasQtyButtons {
        border: none !important;
    }

    .decrement-btn,
    .increment-btn {
        padding: 2px;
        width: 50px;
    }

    .decrement-btn svg {
        width: 10px;
    }

    .grid-cols-4 {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    @media (max-width: 1450px) {
        .grid-cols-4 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 420px) {
        .container-box {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    /* While the flavour search is active the carousel is un-slicked into a
       plain grid; keep it readable on small screens. */
    @media (max-width: 1084px) {
        #available-products.rd-searching {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }
    }

    .grid-cols-3 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .grid-cols-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    @media(max-width: 1085px) {
        .max-w-480px {
            max-width: 480px;
        }
    }

    .summary {
        height: fit-content;
    }

    .border-top-right-radius {
        border-top-right-radius: 1.5rem;
    }

    .bg-red-btn {
        background-color: #C70000;
    }

    .toast-message {
        border-radius: 8px;
        background: #E3645F;
        width: 272px;
        padding: 16px;
        justify-content: center;
        align-items: center;
        gap: 8px;
        color: var(--black-full, #000);
        text-align: center;
        font-family: 'Edmondsans', sans-serif;
        font-size: 16px;
        font-style: normal;
        font-weight: 420;
        line-height: 22px;
        bottom: 0px;
        position: absolute;
    }

    .woocommerce-notice.error .woocommerce-error {
        background-color: #f55959;
        color: #000;
        font-size: 1.2em;
        font-weight: bold;
        text-align: center;
        padding: 1em;
    }

    .box-item {
        position: relative;
        cursor: pointer;
    }

    .tooltip {
        display: none;
        position: absolute;
        top: 0px;
        left: 50%;
        transform: translateX(-50%);
        background-color: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 0px;
        border-radius: 5px;
        font-size: 14px;
        text-align: center;
        z-index: 10;
        width: 50%;
        border-radius: 100%;
        max-height: 100px;
        max-width: 100px;
        min-height: 100px;
        min-width: 100px;
    }

    .box-item:hover .tooltip {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .slick-prev:before,
    .slick-next:before {
        color: black;
    }

    .slick-prev {
        left: 1rem;
        z-index: 50;
    }

    .slick-arrow {
        background: #ffed56;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 100%;
        border: 2px solid black;
        height: 40px;
        width: 40px;
    }


    @media (max-width: 1084px) {

        .slick-arrow {

            margin-top: -5rem;

        }

    }

    .slick-arrow span {
        color: black;
        font-size: 24px;
        line-height: 0;
        font-weight: bold;
    }

    .slick-prev:before {
        content: none;
    }

    .slick-next {
        right: 1rem;
    }

    .slick-next:before {
        content: none;
    }

    @media (max-width: 1084px) {
        .product-sum h2 {
            display: none;
        }

        .product-sum p {
            text-align: center;
        }
    }

    .postid-3947 .pd_box_list .decrement-btn,
    .postid-3947 .pd_box_list .increment-btn {
        padding: 0px;
        width: auto;
        box-shadow: none;
    }


    .woocommerce-notices-wrapper {
        position: fixed;
        bottom: 50%;
        left: 50%;
        right: 50%;
        width: 100%;
        z-index: 1000;
    }

    @media (max-width: 1084px) {
        #available-products-container {
            order: 2;
            border-radius: 0 0 1.5rem 1.5rem;
            background: white;
        }

        #box_items {
            order: 1;
        }

        #info-open {
            margin-bottom: 0px;
            padding-bottom: 0px;
        }

        .product-sum {
            padding-bottom: 1rem;
        }
    }
</style>
<style id="rd-custom-order-ui">
    /* ---------- Flavour search bar (all box-builder pages) ---------- */
    .rd-donut-search .relative {
        max-width: 520px;
        margin-left: auto;
        margin-right: auto;
    }

    #donut-search {
        height: 52px;
        padding-left: 46px;
        padding-right: 46px;
        border-radius: 132px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, .06);
        transition: box-shadow .15s ease, border-color .15s ease;
    }

    #donut-search::placeholder {
        color: #8a857c;
    }

    #donut-search:focus {
        border-color: #000 !important;
        box-shadow: 0 0 0 3px rgba(255, 237, 86, .55);
    }

    .rd-donut-search .relative>span:first-child {
        left: 16px;
    }

    #donut-search-clear {
        right: 8px;
    }

    /* Keep the search reachable while scrolling the long custom-order list. */
    .postid-3947 .rd-donut-search {
        position: sticky;
        top: 0;
        z-index: 30;
        background: #fff;
        padding-top: 14px;
        padding-bottom: 14px;
        margin-bottom: 1rem;
        box-shadow: 0 8px 16px -14px rgba(0, 0, 0, .5);
    }

    /* ---------- Flavour cards (custom-order page only) ---------- */
    .postid-3947 #available-products {
        display: grid;
        gap: 14px;
        grid-template-columns: 1fr;
    }

    @media (min-width: 1084px) {
        .postid-3947 #available-products {
            grid-template-columns: 1fr 1fr;
        }
    }

    .postid-3947 #available-products .product-item {
        border: 1px solid #e7e5dd;
        border-radius: 16px;
        background: #fff;
        padding: 10px 14px;
        transition: box-shadow .2s ease, transform .2s ease, border-color .2s ease;
    }

    .postid-3947 #available-products .product-item:hover {
        border-color: #000;
        box-shadow: 0 10px 22px -10px rgba(0, 0, 0, .18);
        transform: translateY(-2px);
    }

    .postid-3947 #available-products .product-item .flex.flex-row {
        gap: 4px;
    }

    .postid-3947 #available-products .pd_title {
        flex: 1 1 auto;
        margin: 0;
        padding: 0 10px;
        font-size: 16px;
        line-height: 1.2;
    }

    .postid-3947 #available-products .product-item img {
        flex: 0 0 auto;
        height: 64px !important;
        width: 64px !important;
        box-shadow: 0 2px 6px rgba(0, 0, 0, .08);
    }

    /* Quantity stepper as a tidy pill */
    .postid-3947 #available-products .quantity {
        margin-top: 0 !important;
        flex: 0 0 auto;
        width: auto;
        max-width: none;
        gap: 2px;
        padding: 3px;
        background: #fbfaf6;
        border: 1px solid #e1dfd6;
        border-radius: 999px;
    }

    .postid-3947 #available-products .quantity input {
        width: 48px;
        min-width: 48px;
        padding: 0;
        text-align: center;
        background: transparent;
        font-weight: 500;
        font-size: 16px;
        line-height: 30px;
        overflow: visible;
        /* Slick sets user-select:none on the slider; keep these typeable. */
        user-select: text;
        -webkit-user-select: text;
        pointer-events: auto;
        -moz-appearance: textfield;
        appearance: textfield;
    }

    .postid-3947 #available-products .quantity input::-webkit-outer-spin-button,
    .postid-3947 #available-products .quantity input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .postid-3947 #available-products .decrement-btn,
    .postid-3947 #available-products .increment-btn {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid #e1dfd6;
        border-radius: 999px;
        transition: background .15s ease, border-color .15s ease;
    }

    .postid-3947 #available-products .decrement-btn:hover,
    .postid-3947 #available-products .increment-btn:hover {
        background: #ffed56;
        border-color: #000;
    }

    /* ---------- Price + summary (custom-order page only) ---------- */
    .postid-3947 #custom-price {
        height: 50px;
        max-width: 240px;
        font-size: 18px;
        border: 1px solid #d8d7ce;
        border-radius: 12px;
    }

    .postid-3947 #custom-price:focus {
        outline: none;
        border-color: #000;
        box-shadow: 0 0 0 3px rgba(255, 237, 86, .5);
    }

    .postid-3947 #box_items {
        border-radius: 16px;
        overflow: hidden;
    }

    /* The config column ships as a left-aligned 40% block, leaving the right
       side empty on the stacked custom-order layout. Centre it with a sensible
       width so the price / logo / add-to-cart controls feel intentional. */
    .postid-3947 .summary {
        width: 100% !important;
        max-width: 560px;
        margin-left: auto;
        margin-right: auto;
    }
</style>
<?php defined('ABSPATH') || exit;

global $product;
$product_id = $product->get_id();

// Hook: woocommerce_before_single_product.
do_action('woocommerce_before_single_product');

if (post_password_required()) {
    echo get_the_password_form();
    return;
}

// Ensure the global product is available
if (!is_a($product, 'WC_Product')) {
    $product = wc_get_product(get_the_ID());
}

if (!$product || !is_a($product, 'WC_Product')) {
    return; // Exit if $product is not a valid product
}

// Fetch box quantity
$box_quantity = get_post_meta($product->get_id(), '_donut_box_builder_box_quantity', true);
if ($box_quantity === '') {
    $box_quantity = 0; // 0 means unlimited
}

// Fetch prefilled products
$pre_filled = get_post_meta($product->get_id(), '_donut_box_builder_pre_filled', true);
$prefilled_products = get_post_meta($product->get_id(), '_prefilled_box_products', true);

// Placeholder URL (legacy used /content/uploads/ — map to WordPress uploads).
$placeholder_url = content_url('uploads/2024/04/Donuts.svg');
if (!file_exists(WP_CONTENT_DIR . '/uploads/2024/04/Donuts.svg')) {
    $placeholder_url = wc_placeholder_img_src('woocommerce_thumbnail');
}

// Fetch manually selected variations
$selected_variations = get_post_meta($product->get_id(), '_custom_box_products', true);
$selected_products = array();

if ($selected_variations) {
    foreach ($selected_variations as $variation_id) {
        $variation_product = wc_get_product($variation_id);
        if ($variation_product) {
            $selected_products[] = $variation_product;
        }
    }
}

// Fetch disabled products
$disabled_variations = get_post_meta($product->get_id(), '_disabled_box_products', true);
if (!$disabled_variations) {
    $disabled_variations = [];
}

// Get selected filter type (e.g., all_donuts, all_large_donuts, all_midi_donuts, selected_flavours_only, category)
$group_selection = get_post_meta($product->get_id(), '_donut_group_selection', true);
$selected_size = get_post_meta($product->get_id(), '_donut_size_selection', true); // Fetch the size selection

// Initialize array for all products to display
$all_products = array();

// Determine products to fetch based on group selection
if ($group_selection === 'all_donuts') {
    // Fetch all donuts without size restriction
    $args = array(
        'status' => 'publish',
        'limit' => -1,
        'type' => 'variable',
        'tax_query' => array(
            array(
                'taxonomy' => 'rd_product_type',
                'field' => 'slug',
                'terms' => 'donut',
            ),
        ),
    );

    $all_donuts = wc_get_products($args);
    foreach ($all_donuts as $donut_product) {
        if ($donut_product->is_type('variable')) {
            $variations = $donut_product->get_children();
            foreach ($variations as $variation_id) {
                $variation_product = wc_get_product($variation_id);
                if ($variation_product && !in_array($variation_id, $disabled_variations)) {
                    $all_products[] = $variation_product;
                }
            }
        }
    }
} elseif ($group_selection === 'all_large_donuts' || $group_selection === 'all_midi_donuts') {
    // Fetch donuts of specific size based on selection
    $selected_size = ($group_selection === 'all_large_donuts') ? 'large' : 'midi';
    $args = array(
        'status' => 'publish',
        'limit' => -1,
        'type' => 'variable',
        'tax_query' => array(
            array(
                'taxonomy' => 'rd_product_type',
                'field' => 'slug',
                'terms' => 'donut',
            ),
        ),
    );

    $all_donuts = wc_get_products($args);
    foreach ($all_donuts as $donut_product) {
        if ($donut_product->is_type('variable')) {
            $variations = $donut_product->get_children();
            foreach ($variations as $variation_id) {
                $variation_product = wc_get_product($variation_id);
                if ($variation_product && !in_array($variation_id, $disabled_variations)) {
                    $variation_attributes = $variation_product->get_attributes();
                    if (isset($variation_attributes['pa_size']) && strtolower($variation_attributes['pa_size']) === strtolower($selected_size)) {
                        $all_products[] = $variation_product;
                    }
                }
            }
        }
    }
} elseif ($group_selection === 'category') {
    // Fetch donuts by selected category
    $selected_category = get_post_meta($product->get_id(), '_donut_category_selection', true);
    if ($selected_category) {
        $args = array(
            'status' => 'publish',
            'limit' => -1,
            'type' => 'variable',
            'tax_query' => array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'slug',
                    'terms' => $selected_category,
                ),
            ),
        );

        $category_donuts = wc_get_products($args);

        foreach ($category_donuts as $donut_product) {
            if ($donut_product->is_type('variable')) {
                $variations = $donut_product->get_children();
                foreach ($variations as $variation_id) {
                    $variation_product = wc_get_product($variation_id);
                    if ($variation_product && !in_array($variation_id, $disabled_variations)) {
                        // Apply the size filter if a size is selected
                        if ($selected_size && $selected_size !== 'all') {
                            $variation_attributes = $variation_product->get_attributes();
                            if (isset($variation_attributes['pa_size']) && strtolower($variation_attributes['pa_size']) === strtolower($selected_size)) {
                                $all_products[] = $variation_product;
                            }
                        } else {
                            // If no size filter, include all variations
                            $all_products[] = $variation_product;
                        }
                    }
                }
            }
        }
    }
} elseif ($group_selection === 'selected_flavours_only') {
    // Only use manually selected variations
    $all_products = array_filter($selected_products, function ($product) use ($disabled_variations) {
        return !in_array($product->get_id(), $disabled_variations);
    });
} else {
    // Default to manually selected variations if no specific selection is made
    $all_products = array_filter($selected_products, function ($product) use ($disabled_variations) {
        return !in_array($product->get_id(), $disabled_variations);
    });
}

// Remove duplicate products (if any)
$all_products = array_unique($all_products, SORT_REGULAR);

// Sort products by name
usort($all_products, function ($a, $b) {
    return strcasecmp($a->get_name(), $b->get_name());
});

// Fetch the disable add/remove value to pass to JavaScript
$disable_add_remove = get_post_meta($product->get_id(), '_donut_box_builder_disable_add_remove', true);
?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class('', $product); ?>>
    <h1 class="sr-only"><?php echo esc_html(get_the_title($product->get_id())); ?></h1>
    <div class="flex flex-col w-full <?php if ($product_id != 3947) : // Check if it's a custom-order product 
                                        ?>lg:flex-row<?php endif; ?>">
        <div id="available-products-container" class="w-full px-0 <?php if ($product_id != 3947) : ?>lg:w-3/5 pt-8 lg:px-8<?php endif; ?> max-lg:order-2">
            <div class="rd-donut-search w-full px-4 lg:px-0 mb-6 <?php echo ($product_id == 3947) ? 'pt-6 lg:pt-8' : ''; ?>">
                <label for="donut-search" class="sr-only"><?php esc_html_e('Search flavours', 'donut-box-builder'); ?></label>
                <div class="relative w-full max-w-[480px] mx-auto">
                    <span class="absolute -translate-y-1/2 pointer-events-none left-4 top-1/2 text-black-full" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="7"></circle>
                            <path d="m21 21-4.3-4.3"></path>
                        </svg>
                    </span>
                    <input type="search" id="donut-search" name="donut-search" autocomplete="off"
                        placeholder="<?php esc_attr_e('Search flavours…', 'donut-box-builder'); ?>"
                        aria-controls="available-products"
                        class="w-full h-[48px] pl-12 pr-12 rounded-[132px] border-2 border-solid border-black-full bg-white text-black-full font-reg420 text-base-font focus:outline-none focus:border-yellow-primary">
                    <button type="button" id="donut-search-clear" aria-label="<?php esc_attr_e('Clear search', 'donut-box-builder'); ?>"
                        class="absolute hidden items-center justify-center -translate-y-1/2 rounded-full right-3 top-1/2 h-7 w-7 bg-black-full text-white hover:bg-yellow-primary hover:text-black-full">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18"></path>
                            <path d="m6 6 12 12"></path>
                        </svg>
                    </button>
                </div>
                <p id="donut-search-empty" class="hidden mt-4 text-center text-black-full font-reg420 text-sm-font"><?php esc_html_e('No flavours match your search.', 'donut-box-builder'); ?></p>
            </div>
            <div id="available-products" class="relative grid items-center w-full grid-cols-4 gap-3 pd_box_list h-fit">
                <?php
                if (!empty($all_products)) {
                    $non_vegan_products = array();
                    $vegan_products = array();

                    foreach ($all_products as $product_item) {
                        // Check if the product is vegan
                        $parent_product_id = $product_item->get_parent_id();
                        $parent_product = wc_get_product($parent_product_id);

                        if ($parent_product) {
                            $vegan_category_id = 168;

                            if (has_term($vegan_category_id, 'product_cat', $parent_product->get_id())) {
                                $vegan_products[] = $product_item;
                            } else {
                                $non_vegan_products[] = $product_item;
                            }
                        }
                    }

                    function display_product_with_quantity($product_item, $current_product_id, $product)
                    {
                        $product_name = $product_item->get_name();
                        $product_categories = wc_get_product_category_list($product_item->get_id(), ' ', '', '');

                        if ($current_product_id != 3947 && $product->get_slug() != 'custom-order') {
                            $product_name = str_replace(array('- Large', '- Midi', '- Standard'), '', $product_item->get_name());
                            $product_name = trim($product_name);
                        } else {
                            $product_name = $product_item->get_name();
                        }


                        $product_data = array(
                            'id'        => $product_item->get_id(),
                            'name'      => $product_item->get_name(),
                            'thumbnail' => wp_get_attachment_image_url($product_item->get_image_id(), 'woocommerce_thumbnail'),
                            'price'     => $product_item->get_price()
                        );

                        $category_class = implode(' ', wp_get_post_terms($product_item->get_id(), 'product_cat', array('fields' => 'slugs')));
                ?>
                        <?php
                        // Use the global $product object to retrieve the correct product ID
                        $product_id = $product->get_id();
                        ?>
                        <div id="product-<?php echo $product_item->get_id(); ?>" class="relative flex flex-col justify-center items-center w-full product-item <?php echo esc_attr($category_class); ?>" data-item='<?php echo esc_attr(json_encode($product_data)); ?>'>
                            <div id="toast-<?php echo $product_item->get_id(); ?>"
                                class="absolute hidden p-4 text-white transform -translate-x-1/2 -translate-y-full bg-red-500 rounded-lg shadow-lg z-90 toast-message left-1/2 w-72 b-0"
                                role="alert"
                                aria-live="polite">
                                <div class="z-0 flex-1 my-auto shrink basis-0">
                                    In order to add additional donuts, you need to remove items from the current box as it exceeds the box limit.
                                </div>
                                <svg class="object-contain absolute z-0 shrink-0 self-start w-9 aspect-[1.56] bottom-[-15px] h-[23px] right-[118px] mt-2 w-full text-center" xmlns="http://www.w3.org/2000/svg" width="32" height="18" viewBox="0 0 32 18" fill="none">
                                    <path d="M16 18L31.5885 0.75H0.411543L16 18Z" fill="#E3645F" />
                                </svg>
                            </div>
                            <?php if ($product_id == 3947) : ?>
                                <div class="flex flex-col w-full">
                                    <div class="flex flex-row items-center justify-start w-full">
                                        <?php echo $product_item->get_image('woocommerce_thumbnail', array('class' => 'rounded-full h-[80px] w-[80px] object-contain')); ?>
                                        <p class="py-4 leading-none text-left pd_title text-black-full font-reg420 text-base-font"><?php echo esc_html($product_name); ?></p>
                                        <div class="flex items-center mt-4 quantity margin-auto">
                                            <button type="button" aria-label="<?php echo esc_attr(sprintf(__('Decrease quantity of %s', 'donut-box-builder'), $product_name)); ?>" class="decrement-btn" onclick="decrementProductQuantity(<?php echo $product_item->get_id(); ?>)">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="19" height="4" viewBox="0 0 19 4" fill="none" aria-hidden="true">
                                                    <path d="M18.0553 2.26316C18.0553 3.2175 17.2816 3.99116 16.3273 3.99116H2.0073C1.05295 3.99116 0.279297 3.2175 0.279297 2.26316C0.279297 1.30881 1.05295 0.535156 2.0073 0.535156H16.3273C17.2816 0.535156 18.0553 1.30881 18.0553 2.26316Z" fill="#291F19"></path>
                                                </svg>
                                            </button>
                                            <input type="number"
                                                id="product-quantity-<?php echo $product_item->get_id(); ?>"
                                                class="border-none product-quantity-input input-text qty text hasQtyButtons"
                                                data-product-id="<?php echo $product_item->get_id(); ?>"
                                                step="1"
                                                min="0"
                                                value="0"
                                                title="Qty"
                                                aria-label="<?php echo esc_attr(sprintf(__('Quantity of %s', 'donut-box-builder'), $product_name)); ?>"
                                                size="4"
                                                inputmode="numeric"
                                                autocomplete="off">
                                            <button type="button" aria-label="<?php echo esc_attr(sprintf(__('Increase quantity of %s', 'donut-box-builder'), $product_name)); ?>" class="increment-btn" onclick="incrementProductQuantity(<?php echo $product_item->get_id(); ?>)">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="13" viewBox="0 0 12 13" fill="none">
                                                    <path d="M2.08737 8.00708C1.13303 8.00708 0.359375 7.23343 0.359375 6.27908C0.359375 5.32473 1.13303 4.55108 2.08738 4.55108H4.42338V2.18308C4.42338 1.22873 5.19703 0.455078 6.15137 0.455078C7.10572 0.455078 7.87937 1.22873 7.87937 2.18308V4.55108H10.2474C11.2017 4.55108 11.9754 5.32473 11.9754 6.27908C11.9754 7.23343 11.2017 8.00708 10.2474 8.00708H7.87937V10.3431C7.87937 11.2974 7.10572 12.0711 6.15137 12.0711C5.19703 12.0711 4.42338 11.2974 4.42338 10.3431V8.00708H2.08737Z" fill="#291F19"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php else : ?>
                                <?php echo $product_item->get_image('woocommerce_thumbnail', array('class' => 'rounded-full h-[150px] w-[150px] object-contain')); ?>
                                <p class="py-4 leading-none text-center pd_title text-black-full font-reg420 text-mob-md-font"><?php echo esc_html($product_name); ?></p>
                                <div class="flex items-center mx-4 mt-4 quantity margin-auto">
                                    <button type="button" aria-label="<?php echo esc_attr(sprintf(__('Decrease quantity of %s', 'donut-box-builder'), $product_name)); ?>" class="border border-solid border-black rounded bg-white h-[29px] w-[29px] flex items-center justify-center decrement-btn" onclick="decrementProductQuantity(<?php echo $product_item->get_id(); ?>)">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="19" height="4" viewBox="0 0 19 4" fill="none" aria-hidden="true">
                                            <path d="M18.0553 2.26316C18.0553 3.2175 17.2816 3.99116 16.3273 3.99116H2.0073C1.05295 3.99116 0.279297 3.2175 0.279297 2.26316C0.279297 1.30881 1.05295 0.535156 2.0073 0.535156H16.3273C17.2816 0.535156 18.0553 1.30881 18.0553 2.26316Z" fill="#291F19"></path>
                                        </svg>
                                    </button>
                                    <input type="number" id="product-quantity-<?php echo $product_item->get_id(); ?>" class="border-none input-text qty text hasQtyButtons" step="1" min="1" value="1" title="Qty" aria-label="<?php echo esc_attr(sprintf(__('Quantity of %s', 'donut-box-builder'), $product_name)); ?>" size="4" inputmode="numeric" autocomplete="off">
                                    <button type="button" aria-label="<?php echo esc_attr(sprintf(__('Increase quantity of %s', 'donut-box-builder'), $product_name)); ?>" class="border border-solid border-black rounded bg-white h-[29px] w-[29px] flex items-center justify-center increment-btn" onclick="incrementProductQuantity(<?php echo $product_item->get_id(); ?>)">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="13" viewBox="0 0 12 13" fill="none">
                                            <path d="M2.08737 8.00708C1.13303 8.00708 0.359375 7.23343 0.359375 6.27908C0.359375 5.32473 1.13303 4.55108 2.08738 4.55108H4.42338V2.18308C4.42338 1.22873 5.19703 0.455078 6.15137 0.455078C7.10572 0.455078 7.87937 1.22873 7.87937 2.18308V4.55108H10.2474C11.2017 4.55108 11.9754 5.32473 11.9754 6.27908C11.9754 7.23343 11.2017 8.00708 10.2474 8.00708H7.87937V10.3431C7.87937 11.2974 7.10572 12.0711 6.15137 12.0711C5.19703 12.0711 4.42338 11.2974 4.42338 10.3431V8.00708H2.08737Z" fill="#291F19"></path>
                                        </svg>
                                    </button>
                                </div>
                                <button class="px-8 h-[40px] max-w-[110px] mobile:max-w-160px py-3 my-4 btn text-black-full text-sm-font lg:text-sm-font font-normal bg-yellow-primary rounded-lg-x w-auto rd-border hover:text-black-full hover:bg-white add-to-box-button" data-item='<?php echo esc_attr(json_encode($product_data)); ?>'><?php _e('Add to Box', 'donut-box-builder'); ?></button>
                            <?php endif; ?>
                        </div>
                <?php
                    }

                    $current_product_id = $product->get_id();

                    foreach ($non_vegan_products as $non_vegan_product) {
                        display_product_with_quantity($non_vegan_product, $current_product_id, $product);
                    }

                    foreach ($vegan_products as $vegan_product) {
                        display_product_with_quantity($vegan_product, $current_product_id, $product);
                    }
                } else {
                    echo '<p>' . __('No matching products found.', 'donut-box-builder') . '</p>';
                }
                ?>
            </div>
        </div>

        <div class="flex flex-col w-full px-4 border-black <?php if ($product_id !== 3947) {
                                                                echo 'lg:border-l-3 mx-auto lg:px-8';
                                                            } ?>  lg:w-2/5 summary entry-summary w-full  pb-8 <?php if ($product_id == 3947) : ?>mt-8  justify-center  mx-auto<?php endif; ?>">
            <div class="product-sum max-lg:pb-8">
                <?php
                /**
                 * Hook: woocommerce_single_product_summary.
                 *
                 * @hooked woocommerce_template_single_title - 5
                 * @hooked woocommerce_template_single_rating - 10
                 * @hooked woocommerce_template_single_price - 10
                 * @hooked woocommerce_template_single_excerpt - 20
                 * @hooked woocommerce_template_single_add_to_cart - 30
                 * @hooked woocommerce_template_single_meta - 40
                 * @hooked woocommerce_template_single_sharing - 50
                 * @hooked WC_Structured_Data::generate_product_data() - 60
                 */
                if ($product_id != 3947) :
                    do_action('woocommerce_single_product_summary');
                endif;
                ?>
            </div>

            <!-- Custom Fields Display -->
            <?php

            // Define number of columns based on box quantity
            $columns = 3; // Default columns
            if ($box_quantity) {
                if ($box_quantity <= 6) {
                    $columns = 3;
                } elseif ($box_quantity <= 12) {
                    $columns = 4;
                } else {
                    $columns = 5;
                }
            }
            ?>
            <?php if ($product_id != 3947): ?>
                <div class="w-full m-auto mb-4 border rounded-normal bg-black-full border-black-full max-w-480px max-lg:order-1">
                    <div class="flex flex-row items-center justify-between">
                        <div id="box-quantity-display" class="text-left flex items-center text-sm-font laptop:text-mob-md-font pl-4 text-white h-[50px]"><strong class="max-small:hidden"><?php _e('Box Quantity: ', 'donut-box-builder'); ?></strong> <span class="pl-2" id="current-box-quantity">0</span>/<?php echo esc_html($box_quantity); ?></div>
                        <div class="flex items-center justify-center">
                            <?php
                            $disable_add_remove = get_post_meta($product->get_id(), '_donut_box_builder_disable_add_remove', true);

                            if ($disable_add_remove !== 'yes') {
                                // Render the Clear Box button
                            ?>
                                <button id="clear-box" onclick="clearBoxItems()" class="flex items-center bg-red-btn hover:text-black-full justify-center rounded-sm text-white border-top-right-radius h-[50px] px-[10px] gap-[21.435px] font-medium text-sm-font laptop:text-mob-md-font">
                                    <?php _e('Clear Box', 'donut-box-builder'); ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="33" height="33" viewBox="0 0 33 33" fill="none">
                                        <g clip-path="url(#clip0_3017_6817)">
                                            <path d="M1.47607 5.77344V13.8114H9.51405" stroke="black" stroke-width="2.67932" stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path d="M4.83863 20.5088C5.70726 22.9742 7.35362 25.0907 9.52966 26.5391C11.7057 27.9875 14.2935 28.6895 16.9032 28.5393C19.513 28.3891 22.0032 27.3949 23.9987 25.7063C25.9941 24.0178 27.3868 21.7265 27.9669 19.1776C28.5469 16.6288 28.2828 13.9604 27.2145 11.5747C26.1462 9.18894 24.3314 7.21502 22.0437 5.95034C19.756 4.68566 17.1192 4.19873 14.5307 4.56292C11.9421 4.92711 9.54206 6.12269 7.69211 7.96952L1.47607 13.8105" stroke="black" stroke-width="2.67932" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </g>
                                        <defs>
                                            <clipPath id="clip0_3017_6817)">
                                                <rect width="32.1519" height="32.1519" fill="white" transform="translate(0.13623 0.414062)"></rect>
                                            </clipPath>
                                        </defs>
                                    </svg>
                                </button>
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                    <div id="box-container"
                        class="grid grid-cols-4 gap-8 p-4 container-box laptop:p-6"
                        data-max-quantity="<?php echo esc_attr($box_quantity); ?>"
                        data-placeholder-url="<?php echo esc_url($placeholder_url); ?>"
                        data-product-id="<?php echo esc_attr($product->get_id()); ?>">
                        <div id="placeholder" class="relative flex flex-col items-center justify-center box-item p2">
                            <img src="<?php echo esc_url($placeholder_url); ?>" alt="Donut Placeholder" class="box-img">
                        </div>
                        <!-- Box Items will be inserted here dynamically -->
                    </div>
                </div>
            <?php else: ?>
                <div id="box_items" class="w-full m-auto mb-4 border bg-black-full border-black-full max-w-480px">
                    <div class="flex flex-row items-center justify-between">
                        <div id="box-quantity-display" class="text-left flex items-center text-sm-font laptop:text-mob-md-font pl-4 text-white h-[50px]"><strong class="max-small:hidden"><?php _e('Items in Box', 'donut-box-builder'); ?></strong> <span class="pl-2" class="hidden" id="current-box-quantity">0</span>/<?php echo esc_html($box_quantity); ?></div>
                    </div>
                    <div id="item-list-container" class="w-full p-4 m-auto text-black bg-white border border-black-full max-w-480px">
                        <span class="font-bold box-item-count">Total Items: <span id="box-item-count-display">0</span></span>
                        <ul id="item-list">
                            <!-- Items will be inserted here dynamically -->
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
            <?php do_action('woocommerce_donut_box_builder_after_summary'); ?>

            <div class="py-8 text-center border-b border-gray-300 border-solid extenonheadingparent">
                <?php
                global $product;
                $product = wc_get_product(get_the_ID());
                if (is_a($product, 'WC_Product')) {
                    $price = $product->get_price();
                    $currency = get_woocommerce_currency_symbol();
                ?>
                    <div class="text-black-full font-reg420 text-sm-md-font
                            <?php
                            $product_id = $product->get_id();
                            echo (in_array('rd-product-type-donut', get_body_class()) || $product_id == 3947) ? 'hidden' : '';
                            ?>">
                        Box total: <bdi id="box-total" class="relative z-50"><?php echo $currency . $price; ?></bdi>
                    </div>
                <?php
                } else {
                    echo 'Price not available';
                }
                ?>
                <?php
                global $product;

                $product_id = $product->get_id();

                if ($product_id == 3947) {
                ?>
                    <div id="custom-price-container" class="flex flex-col justify-center my-4">
                        <label class="text-black-full font-reg420 text-sm-md-font" for="custom-price">Enter your price (€):</label>
                        <input class="w-full m-auto my-4 text-center" type="number" placeholder="0" id="custom-price" name="custom_price" min="0" step="any" inputmode="decimal" required>
                        <p class="text-xs text-gray-600 -mt-2 mb-2">0 is allowed for complimentary orders.</p>
                    </div>
                <?php
                }
                ?>
            </div>

            <!-- Quantity and Total -->
            <div class="flex items-center justify-between py-4">
                <div class="flex flex-col justify-center w-full">
                    <span class="text-center text-black-full font-reg420 text-sm-md-font" for="custom-price">Number of Boxes</span>
                    <div class="flex items-center quantity margin-auto">
                        <button type="button" id="box-quantity-decrement" aria-label="<?php esc_attr_e('Decrease number of boxes', 'donut-box-builder'); ?>" class="border border-solid border-black rounded bg-white h-[29px] w-[29px] flex items-center justify-center decrement-btn hover:bg-yellow-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="19" height="4" viewBox="0 0 19 4" fill="none">
                                <path d="M18.0553 2.26316C18.0553 3.2175 17.2816 3.99116 16.3273 3.99116H2.0073C1.05295 3.99116 0.279297 3.2175 0.279297 2.26316C0.279297 1.30881 1.05295 0.535156 2.0073 0.535156H16.3273C17.2816 0.535156 18.0553 1.30881 18.0553 2.26316Z" fill="#291F19"></path>
                            </svg>
                        </button>
                        <input type="number" id="box-quantity" class="border-none input-text qty text hasQtyButtons" step="1" min="1" value="1" title="Qty" aria-label="<?php esc_attr_e('Number of boxes', 'donut-box-builder'); ?>" size="4" inputmode="numeric" autocomplete="off">
                        <button type="button" id="box-quantity-increment" aria-label="<?php esc_attr_e('Increase number of boxes', 'donut-box-builder'); ?>" class="border border-solid border-black rounded bg-white h-[29px] w-[29px] flex items-center justify-center increment-btn hover:bg-yellow-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="13" viewBox="0 0 12 13" fill="none">
                                <path d="M2.08737 8.00708C1.13303 8.00708 0.359375 7.23343 0.359375 6.27908C0.359375 5.32473 1.13303 4.55108 2.08738 4.55108H4.42338V2.18308C4.42338 1.22873 5.19703 0.455078 6.15137 0.455078C7.10572 0.455078 7.87937 1.22873 7.87937 2.18308V4.55108H10.2474C11.2017 4.55108 11.9754 5.32473 11.9754 6.27908C11.9754 7.23343 11.2017 8.00708 10.2474 8.00708H7.87937V10.3431C7.87937 11.2974 7.10572 12.0711 6.15137 12.0711C5.19703 12.0711 4.42338 11.2974 4.42338 10.3431V8.00708H2.08737Z" fill="#291F19"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <button id="add-box-to-cart" onclick="addBoxToCart()" class="ml-auto mr-auto mt-8 flex items-center text-center justify-center bg-black-full border-3 border-black-full rounded-[132px] text-white hover:text-black-full whitespace-nowrap mob-md-font mobile:text-sm-md-font font-reg420 gap-4 h-[54px] max-w-[416px] w-full py-4 px-[80px] disabled:bg-yellow-disabled disabled:cursor-not-allowed disabled:border-grey-disabled hover:bg-yellow-primary hover:border-none" <?php global $product;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            $product_id = $product->get_id();
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            if ($product_id != 3947) echo 'disabled'; ?>>
                <?php _e('Add Box to Cart', 'donut-box-builder'); ?>
            </button>
            <?php do_action('woocommerce_after_add_to_cart_button'); ?>
        </div>

    </div>
    <?php
    /**
     * Hook: woocommerce_after_single_product_summary.
     *
     * @hooked woocommerce_output_product_data_tabs - 10
     * @hooked woocommerce_upsell_display - 15
     * @hooked woocommerce_output_related_products - 20
     */
    do_action('woocommerce_after_single_product_summary');
    ?>
    <div class="fixed bottom-0 left-0 right-0 px-4 overflow-hidden transition-opacity duration-500 z-99 bg-black-full border-top-40 lg:hidden">
        <div class="flex justify-center items-center w-full max-w-[450px] mobile:w-8/12 lg:w-1/2 m-auto">
            <button id="floating-add-to-cart-btn"
                class="m-4 border-2 border-black-full border-solid flex items-center justify-center bg-white text-black-full hover:bg-yellow-primary w-[270px] py-2 rounded-lg-x relative disabled:bg-yellow-disabled disabled:border-grey-disabled disabled:cursor-not-allowed"
                onclick="addBoxToCart()"
                disabled>
                <?php _e('Add Box to Cart', 'donut-box-builder'); ?>
            </button>
        </div>
    </div>

</div>

<?php do_action('woocommerce_after_single_product'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const productId = parseInt('<?php echo $product_id; ?>');
        const disableAddRemove = '<?php echo $disable_add_remove; ?>'; // "yes" or "no"
        let boxItems = [];

        const addBoxToCartButton = document.getElementById('add-box-to-cart');
        const boxQuantityInput = document.getElementById('box-quantity');
        const currentBoxQuantityElement = document.getElementById('current-box-quantity');
        let currentAllergens = new Set();

        const prefilledProducts = (typeof my_script_object !== 'undefined' && my_script_object.prefilled_products_data)
            ? my_script_object.prefilled_products_data
            : [];
        if (prefilledProducts && prefilledProducts.length > 0) {
            prefilledProducts.forEach((product) => {
                product.quantity = 1;
                boxItems.push(product);
                fetchAllergens(product.id, allergens => {
                    allergens.forEach(allergen => currentAllergens.add(allergen));
                    updateAllergenDisplay();
                });
            });
        }

        if (productId === 3947) {
            const quantityInputs = document.querySelectorAll('.product-quantity-input');

            quantityInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const newQuantity = parseInt(this.value) || 0;
                    const productId = parseInt(this.getAttribute('data-product-id'));
                    const productData = getProductDataById(productId);
                    updateItemInBox(productData, newQuantity);
                });
            });
        }

        function getProductDataById(productId) {
            const productElement = document.getElementById('product-' + productId);
            const dataItem = productElement.getAttribute('data-item');
            if (dataItem) {
                return JSON.parse(dataItem);
            } else {
                console.error('Data item not found for product ID:', productId);
                return null;
            }
        }

        function updateItemInBox(item, newQuantity) {
            if (!item) {
                console.error('Invalid item data');
                return;
            }
            const currentItem = boxItems.find(boxItem => boxItem.id === item.id);
            const previousQuantity = currentItem ? currentItem.quantity : 0;
            let totalItemsInBox = boxItems.reduce((total, boxItem) => total + (boxItem ? boxItem.quantity : 0), 0);
            totalItemsInBox = totalItemsInBox - previousQuantity + newQuantity;

            boxItems = boxItems.filter(boxItem => boxItem.id !== item.id);

            if (newQuantity > 0) {
                item.quantity = newQuantity;
                boxItems.push(item);
            }

            updateBoxDisplay();
            updateAddToCartButtonState();
        }

        function updateBoxDisplay() {
            if (productId === 3947) {
                const itemListContainer = document.getElementById('item-list');
                if (itemListContainer) {
                    itemListContainer.innerHTML = '';

                    boxItems.forEach(item => {
                        if (item) {
                            const listItem = document.createElement('li');
                            listItem.textContent = `${item.name} x ${item.quantity}`;
                            itemListContainer.appendChild(listItem);
                        }
                    });
                }

                updateCurrentBoxQuantity();

            } else {
                const boxContainer = document.getElementById('box-container');
                const maxQuantity = parseInt(boxContainer.getAttribute('data-max-quantity')) || 0;
                const placeholderUrl = boxContainer.getAttribute('data-placeholder-url');

                boxContainer.innerHTML = '';

                const totalItemsInBox = boxItems.reduce((total, item) => total + (item ? item.quantity : 0), 0);

                boxItems.forEach((item) => {
                    if (item) {
                        for (let i = 0; i < item.quantity; i++) {
                            const div = document.createElement('div');
                            div.className = 'flex flex-col justify-center items-center box-item p2 relative in-box';
                            const img = document.createElement('img');
                            img.src = item.thumbnail || placeholderUrl;
                            img.alt = item.name;
                            img.className = 'box-img';
                            div.appendChild(img);

                            const tooltip = document.createElement('div');
                            tooltip.className = 'tooltip';
                            tooltip.textContent = item.name.replace('- Large', '').replace('- Midi', '').trim();
                            div.appendChild(tooltip);

                            if (disableAddRemove !== 'yes') {
                                const removeButton = document.createElement('button');
                                removeButton.innerHTML = `<svg width="24px" height="24px" viewBox="0 0 21 21" xmlns="http://www.w3.org/2000/svg">
                                <g fill="#f55959" fill-rule="evenodd" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" transform="translate(2 2)">
                                    <circle cx="8.5" cy="8.5" r="8"></circle>
                                    <g transform="matrix(0 1 -1 0 17 0)">
                                        <path d="m5.5 11.5 6-6"></path>
                                        <path d="m5.5 5.5 6 6"></path>
                                    </g>
                                </g>
                            </svg>`;
                                removeButton.className = 'absolute top-0 right-0 bg-red-500 text-white px-2 py-1 z-50';
                                removeButton.onclick = function() {
                                    removeOneItemFromBox(item.id);
                                };
                                div.appendChild(removeButton);
                            }

                            boxContainer.appendChild(div);
                        }
                    }
                });

                if (maxQuantity > 0) {
                    const emptySlots = maxQuantity - totalItemsInBox;

                    for (let i = 0; i < emptySlots; i++) {
                        const placeholderDiv = document.createElement('div');
                        placeholderDiv.className = 'flex flex-col justify-center items-center box-item p2 relative placeholder';
                        const img = document.createElement('img');
                        img.src = placeholderUrl;
                        img.alt = 'Donut Placeholder';
                        img.className = 'box-img';
                        placeholderDiv.appendChild(img);
                        boxContainer.appendChild(placeholderDiv);
                    }
                }

                updateAddToCartButtonState();
                updateBoxTotal();
                updateCurrentBoxQuantity();
            }
        }

        function updateCurrentBoxQuantity() {
            const totalItemsInBox = boxItems.reduce((total, item) => total + (item ? item.quantity : 0), 0);

            const boxItemCountDisplay = document.getElementById('box-item-count-display');
            if (boxItemCountDisplay) {
                boxItemCountDisplay.textContent = totalItemsInBox;
            }

            if (currentBoxQuantityElement) {
                currentBoxQuantityElement.textContent = totalItemsInBox;
            }
        }

     function updateAddToCartButtonState() {
            if (productId === 3947) {                     // custom-order product never disabled
                addBoxToCartButton.disabled = false;
                return;
            }

            const totalItemsInBox = boxItems.reduce(
                (total, item) => total + (item ? item.quantity : 0), 0
            );
            const maxQuantity = parseInt(
                document.getElementById('box-container').dataset.maxQuantity
            ) || 0;

            const mustDisable = maxQuantity > 0 && totalItemsInBox !== maxQuantity;

            // ① main button
            addBoxToCartButton.disabled = mustDisable;

            // ② floating button
            const floatingBtn = document.getElementById('floating-add-to-cart-btn');
            if (floatingBtn) floatingBtn.disabled = mustDisable;
        }

        function updateBoxTotal() {
            let basePrice = parseFloat('<?php echo $product->get_price(); ?>') || 0;
            let total = basePrice;

            const selectedStandCheckboxes = document.querySelectorAll('.stand-type-checkbox:checked');
            selectedStandCheckboxes.forEach(checkbox => {
                total += parseFloat(checkbox.dataset.price) || 0;
            });

            const selectedAdditionalProducts = document.querySelectorAll('.additional-product-checkbox:checked');
            selectedAdditionalProducts.forEach(checkbox => {
                const productId = checkbox.value;
                const quantityInput = document.getElementById('additional_product_quantity_' + productId) || document.getElementById('additional-product-quantity-' + productId);
                const quantity = parseInt(quantityInput.value) || 1;
                const price = parseFloat(checkbox.dataset.price) || 0;
                total += price * quantity;
            });

            const boxTotalElement = document.getElementById('box-total');
            if (boxTotalElement) {
                if (isNaN(total)) {
                    boxTotalElement.textContent = '€0.00';
                } else {
                    boxTotalElement.textContent = '€' + total.toFixed(2);
                }
            }
        }

        function addItemToBox(item) {
            const quantityInput = document.getElementById('product-quantity-' + item.id);
            let quantity = parseInt(quantityInput.value) || 1;

            let totalItemsInBox = boxItems.reduce((total, boxItem) => total + (boxItem ? boxItem.quantity : 0), 0);

            const maxQuantity = parseInt(document.getElementById('box-container').getAttribute('data-max-quantity')) || 0;

            if (maxQuantity === 0 || totalItemsInBox + quantity <= maxQuantity) {
                let existingItem = boxItems.find(boxItem => boxItem && boxItem.id === item.id);
                if (existingItem) {
                    existingItem.quantity += quantity;
                } else {
                    item.quantity = quantity;
                    boxItems.push(item);
                }
                updateBoxDisplay();
                updateProductClasses();
                updateCurrentBoxQuantity();
                updateAddToCartButtonState();
            } else {
                // Show the toast notification for the specific product
                showToast(item.id);
            }
        }

        // Function to display the toast notification
        function showToast(productId) {
            const toast = document.getElementById(`toast-${productId}`);
            if (toast) {
                toast.classList.remove('hidden');
                setTimeout(() => {
                    toast.classList.add('hidden');
                }, 5000); // Hide the toast after 3 seconds
            }
        }

        if (productId !== 3947) {
            document.querySelectorAll('.add-to-box-button').forEach(button => {
                button.addEventListener('click', function() {
                    const itemData = JSON.parse(this.getAttribute('data-item'));
                    addItemToBox(itemData);
                });
            });
        }

        function quantityInputsForProduct(productId) {
            return document.querySelectorAll(
                '#product-quantity-' + productId + ', .product-quantity-input[data-product-id="' + productId + '"]'
            );
        }

        function setProductQuantity(productId, nextValue) {
            const inputs = quantityInputsForProduct(productId);
            if (!inputs.length) {
                return;
            }
            inputs.forEach(function(input) {
                input.value = nextValue;
            });
            inputs[0].dispatchEvent(new Event('change', { bubbles: true }));
        }

        window.incrementProductQuantity = function(productId) {
            const inputs = quantityInputsForProduct(productId);
            if (!inputs.length) {
                return;
            }
            setProductQuantity(productId, (parseInt(inputs[0].value, 10) || 0) + 1);
        };

        window.decrementProductQuantity = function(productId) {
            const inputs = quantityInputsForProduct(productId);
            if (!inputs.length) {
                return;
            }
            const current = parseInt(inputs[0].value, 10) || 0;
            if (current > 0) {
                setProductQuantity(productId, current - 1);
            }
        };

        function removeOneItemFromBox(productId) {
            // Find and remove the item
            const itemIndex = boxItems.findIndex(item => item && item.id === productId);

            if (itemIndex > -1) {
                // Remove the item completely
               boxItems[itemIndex].quantity > 1 ? boxItems[itemIndex].quantity-- : boxItems.splice(itemIndex, 1);

                // Force a new array reference
                boxItems = [...boxItems];
                updateBoxDisplay();            // ← add this
                return;
                // Update displays
                const boxContainer = document.getElementById('box-container');
                if (boxContainer) {
                    const placeholderUrl = boxContainer.getAttribute('data-placeholder-url');
                    const maxQuantity = parseInt(boxContainer.getAttribute('data-max-quantity')) || 0;

                    // Clear container
                    boxContainer.innerHTML = '';

                    // Add remaining items (if any)
                    boxItems.forEach((item) => {
                        if (item) {
                            const div = document.createElement('div');
                            div.className = 'flex flex-col justify-center items-center box-item p2 relative in-box';

                            const img = document.createElement('img');
                            img.src = item.thumbnail;
                            img.alt = item.name;
                            img.className = 'box-img';
                            div.appendChild(img);

                            // Always add remove button
                            const removeButton = document.createElement('button');
                            removeButton.innerHTML = `<svg width="24px" height="24px" viewBox="0 0 21 21" xmlns="http://www.w3.org/2000/svg">
                        <g fill="#f55959" fill-rule="evenodd" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" transform="translate(2 2)">
                            <circle cx="8.5" cy="8.5" r="8"></circle>
                            <g transform="matrix(0 1 -1 0 17 0)">
                                <path d="m5.5 11.5 6-6"></path>
                                <path d="m5.5 5.5 6 6"></path>
                            </g>
                        </g>
                    </svg>`;
                            removeButton.className = 'absolute top-0 right-0 bg-red-500 text-white px-2 py-1 z-50';
                            removeButton.onclick = function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                removeOneItemFromBox(item.id);
                            };
                            div.appendChild(removeButton);

                            boxContainer.appendChild(div);
                        }
                    });

                    // Fill remaining slots with placeholders
                    const remainingSlots = maxQuantity - boxItems.length;
                    for (let i = 0; i < remainingSlots; i++) {
                        const div = document.createElement('div');
                        div.className = 'flex flex-col justify-center items-center box-item p2 relative placeholder';
                        const img = document.createElement('img');
                        img.src = placeholderUrl;
                        img.alt = 'Donut Placeholder';
                        img.className = 'box-img';
                        div.appendChild(img);
                        boxContainer.appendChild(div);
                    }
                }

                // Update quantity displays
                const currentBoxQuantityElement = document.getElementById('current-box-quantity');
                if (currentBoxQuantityElement) {
                    currentBoxQuantityElement.textContent = boxItems.length;
                }

                const boxItemCountDisplay = document.getElementById('box-item-count-display');
                if (boxItemCountDisplay) {
                    boxItemCountDisplay.textContent = boxItems.length;
                }

                // Clear and update allergens
                currentAllergens.clear();
                if (boxItems.length > 0) {
                    boxItems.forEach(item => {
                        fetchAllergens(item.id, allergens => {
                            allergens.forEach(allergen => currentAllergens.add(allergen));
                            updateAllergenDisplay();
                        });
                    });
                } else {
                    updateAllergenDisplay();
                }

                // Update button state
                const addToCartButton = document.getElementById('add-box-to-cart');
                if (addToCartButton) {
                    // Enable the button only if we have items
                    addToCartButton.disabled = (boxItems.length === 0);
                }

                // Update floating button if it exists
                const floatingAddToCartBtn = document.getElementById('floating-add-to-cart-btn');
                if (floatingAddToCartBtn) {
                    floatingAddToCartBtn.disabled = (boxItems.length === 0);
                }
            }
        }

        function fetchAllergens(productId, callback) {
            const data = new FormData();
            data.append('action', 'get_product_allergens');
            data.append('product_id', productId);

            fetch(my_script_object.ajax_url, {
                    method: 'POST',
                    body: data,
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        callback(data.data);
                    } else {
                        console.error('Failed to fetch allergens:', data.data);
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        window.addBoxToCart = function() {
            try {
                const addToCartButton = document.getElementById('add-box-to-cart');
                addToCartButton.disabled = true;
                addToCartButton.textContent = 'Processing...';

                const productsWithQuantities = boxItems.filter(item => item !== null).map(item => ({
                    id: item.id,
                    quantity: item.quantity
                }));

                const productsData = JSON.stringify(productsWithQuantities);

                const customProductOption = document.getElementById('custom_product_option') ? document.getElementById('custom_product_option').value : '';
                const specialRequests = document.getElementById('special_requests') ? document.getElementById('special_requests').value : '';
                const logoUpload = document.getElementById('logo_upload') ? document.getElementById('logo_upload').files[0] : null;
                const additionalLogosInput = document.getElementById('additional_logos');
                const additionalLogos = additionalLogosInput ? additionalLogosInput.files : [];
                const quantity = document.getElementById('box-quantity').value;
                const customDropdownSelections = {};

                const requireLogoUpload = !!(window.my_script_object && my_script_object.require_logo_upload);
                if (requireLogoUpload && !logoUpload) {
                    const msg = (window.my_script_object && my_script_object.i18n && my_script_object.i18n.logo_required) ?
                        my_script_object.i18n.logo_required :
                        'Please upload your logo before adding this box to the cart.';

                    const requiredEl = document.getElementById('logo_upload_required');
                    if (requiredEl) {
                        requiredEl.textContent = msg;
                        requiredEl.classList.remove('hidden');
                    } else {
                        alert(msg);
                    }

                    addToCartButton.disabled = false;
                    addToCartButton.textContent = 'Add Box to Cart';
                    return;
                }

                const requireSpecialOccasion = !!(window.my_script_object && my_script_object.require_special_occasion);
                if (requireSpecialOccasion && !customProductOption) {
                    const msg = (window.my_script_object && my_script_object.i18n && my_script_object.i18n.special_occasion_required) ?
                        my_script_object.i18n.special_occasion_required :
                        'Please select a special occasion before adding this box to the cart.';

                    const requiredEl = document.getElementById('custom_product_option_required');
                    if (requiredEl) {
                        requiredEl.textContent = msg;
                        requiredEl.classList.remove('hidden');
                    } else {
                        alert(msg);
                    }

                    addToCartButton.disabled = false;
                    addToCartButton.textContent = 'Add Box to Cart';
                    return;
                }

                const customDropdownErrorEls = document.querySelectorAll('.custom-dropdown-group-required');
                customDropdownErrorEls.forEach(el => {
                    el.textContent = '';
                    el.classList.add('hidden');
                });

                const customDropdownSelects = document.querySelectorAll('.custom-dropdown-group-select');
                for (const selectEl of customDropdownSelects) {
                    const groupKey = selectEl.dataset.groupKey || '';
                    const groupLabel = selectEl.dataset.groupLabel || groupKey || 'custom option';
                    const isRequired = selectEl.dataset.required === '1';
                    const selectedValue = selectEl.value || '';

                    if (isRequired && !selectedValue) {
                        const fallback = (window.my_script_object && my_script_object.i18n && my_script_object.i18n.custom_dropdown_required) ?
                            my_script_object.i18n.custom_dropdown_required :
                            'Please select an option before adding this box to the cart.';
                        const msg = `Please select an option for ${groupLabel} before adding this box to the cart.`;
                        const errorEl = document.getElementById(`${selectEl.id}_required`);
                        if (errorEl) {
                            errorEl.textContent = msg;
                            errorEl.classList.remove('hidden');
                        } else {
                            alert(fallback);
                        }

                        addToCartButton.disabled = false;
                        addToCartButton.textContent = 'Add Box to Cart';
                        return;
                    }

                    if (groupKey && selectedValue) {
                        customDropdownSelections[groupKey] = {
                            label: groupLabel,
                            value: selectedValue,
                        };
                    }
                }

                let customPrice = null;
                if (parseInt(productId) === 3947) {
                    const customPriceInput = document.getElementById('custom-price');
                    customPrice = customPriceInput ? customPriceInput.value.trim() : '';
                    const parsedPrice = parseFloat(customPrice);

                    if (customPrice === '' || Number.isNaN(parsedPrice) || parsedPrice < 0) {
                        alert('Please enter a valid price. 0 is allowed.');
                        addToCartButton.disabled = false;
                        addToCartButton.textContent = 'Add Box to Cart';
                        return;
                    }
                    customPrice = parsedPrice;
                }

                const selectedStandCheckboxes = document.querySelectorAll('.stand-type-checkbox:checked');
                const selectedStandIds = Array.from(selectedStandCheckboxes).map(checkbox => checkbox.value).join(',');
                const selectedStandNames = Array.from(selectedStandCheckboxes).map(checkbox => checkbox.dataset.name).join(', ');
                const selectedStandPrices = Array.from(selectedStandCheckboxes).map(checkbox => checkbox.dataset.price).join(',');

                const selectedAdditionalProducts = document.querySelectorAll('.additional-product-checkbox:checked');
                let additionalProductsWithQuantities = [];
                selectedAdditionalProducts.forEach(checkbox => {
                    const productId = checkbox.value;
                    const quantityInput = document.getElementById('additional_product_quantity_' + productId);
                    if (quantityInput) {
                        const quantity = parseInt(quantityInput.value) || 1;
                        additionalProductsWithQuantities.push({
                            id: productId,
                            quantity: quantity
                        });
                    } else {
                        additionalProductsWithQuantities.push({
                            id: productId,
                            quantity: 1
                        });
                    }
                });
                const additionalProductsData = JSON.stringify(additionalProductsWithQuantities);

                let formData = new FormData();
                formData.append('action', 'donut_box_add_to_cart');
                formData.append('nonce', my_script_object.nonce);
                formData.append('donut_box_product_id', productId);
                formData.append('products_data', productsData);
                formData.append('quantity', quantity);

                if (customPrice !== null) {
                    formData.append('custom_price', customPrice);
                }

                formData.append('custom_product_option', customProductOption);
                formData.append('custom_dropdowns', JSON.stringify(customDropdownSelections));
                formData.append('special_requests', specialRequests);
                formData.append('stand_ids', selectedStandIds);
                formData.append('stand_names', selectedStandNames);
                formData.append('stand_prices', selectedStandPrices);
                formData.append('additional_products_data', additionalProductsData);

                if (logoUpload) {
                    formData.append('logo_upload', logoUpload);
                }

                for (let i = 0; i < additionalLogos.length; i++) {
                    formData.append('additional_logos[]', additionalLogos[i]);
                }

                fetch(my_script_object.ajax_url, {
                        method: 'POST',
                        body: formData
                    })
                    .then(async (response) => {
                        const rawText = await response.text();
                        let data = null;

                        try {
                            data = JSON.parse(rawText);
                        } catch (parseError) {
                            throw new Error('Non-JSON add-to-cart response: ' + rawText.substring(0, 300));
                        }

                        if (data.success) {
                            const refreshNotices = async () => {
                                const sideCartMode = window.matrixRdCartNotices && matrixRdCartNotices.feedbackMode === 'side_cart';
                                const payload = data.data || {};
                                if (sideCartMode && typeof window.matrixRdOpenSideCart === 'function') {
                                    window.matrixRdOpenSideCart({
                                        side_cart_html: payload.side_cart_html || '',
                                        html: payload.side_cart_html || payload.html || '',
                                        cart_count: payload.cart_count || payload.cart_contents_count,
                                        cart_total: payload.cart_total || ''
                                    });
                                } else if (payload.notice_html && typeof window.matrixRdAppendCartNoticeHtml === 'function') {
                                    window.matrixRdAppendCartNoticeHtml(data.data.notice_html);
                                } else if (typeof window.matrixRdRefreshCartAndNotices === 'function') {
                                    await window.matrixRdRefreshCartAndNotices();
                                } else if (typeof window.matrixRdShowCartNotices === 'function') {
                                    if (typeof window.refreshAllCartHeaders === 'function') {
                                        window.refreshAllCartHeaders();
                                    }
                                    await window.matrixRdShowCartNotices();
                                } else if (typeof window.refreshAllCartHeaders === 'function') {
                                    window.refreshAllCartHeaders();
                                }
                            };

                            refreshNotices().finally(() => {
                                addToCartButton.disabled = false;
                                addToCartButton.textContent = 'Add Box to Cart';
                            });
                            return;
                        }

                        const message = data.message || (data.data && data.data.message) || 'There was an error adding the box to the cart.';
                        alert(message);
                        addToCartButton.disabled = false;
                        addToCartButton.textContent = 'Add Box to Cart';
                    })
                    .catch(error => {
                        console.error('Error occurred during the request:', error);
                        alert((error && error.message) ? error.message : 'There was an error adding the box to the cart.');
                        addToCartButton.disabled = false;
                        addToCartButton.textContent = 'Add Box to Cart';
                    });
            } catch (error) {
                console.error('Unexpected error in addBoxToCart:', error);
                const addToCartButton = document.getElementById('add-box-to-cart');
                addToCartButton.disabled = false;
                addToCartButton.textContent = 'Add Box to Cart';
            }
        };


        function displayWooCommerceNotice(message, type) {
            const noticeContainer = document.querySelector('.woocommerce-notices-wrapper');
            if (!noticeContainer) {
                const newNoticeContainer = document.createElement('div');
                newNoticeContainer.className = 'woocommerce-notices-wrapper';
                document.body.prepend(newNoticeContainer);
            }

            const noticeElement = document.createElement('div');
            noticeElement.className = `woocommerce-notice ${type}`;
            noticeElement.innerHTML = `<div class="woocommerce-${type}" role="alert">${message}</div>`;

            document.querySelector('.woocommerce-notices-wrapper').appendChild(noticeElement);

            setTimeout(() => {
                noticeElement.remove();
            }, 5000);
        }

        function updateQuantityButtons() {
            document.getElementById('box-quantity-increment').addEventListener('click', function() {
                boxQuantityInput.value = parseInt(boxQuantityInput.value) + 1;
                updateBoxTotal();
            });

            document.getElementById('box-quantity-decrement').addEventListener('click', function() {
                if (parseInt(boxQuantityInput.value) > 1) {
                    boxQuantityInput.value = parseInt(boxQuantityInput.value) - 1;
                    updateBoxTotal();
                }
            });
        }

        boxQuantityInput.addEventListener('change', updateBoxTotal);

        window.clearBoxItems = function() {
            boxItems = [];
            currentAllergens.clear();
            updateBoxDisplay();
            updateAddToCartButtonState();
        };

        updateBoxDisplay();
        updateAddToCartButtonState();
        updateQuantityButtons();
    });


    document.addEventListener("DOMContentLoaded", function() {
        const customPriceInput = document.getElementById('custom-price');
        if (customPriceInput) {
            const formatEnteredPrice = function () {
                if (customPriceInput.value === '') {
                    return;
                }
                const parsed = parseFloat(customPriceInput.value);
                if (Number.isNaN(parsed) || parsed < 0) {
                    return;
                }
                customPriceInput.value = String(Math.round(parsed * 100) / 100);
            };
            customPriceInput.addEventListener('blur', formatEnteredPrice);
        }

        const availableProducts = jQuery("#available-products");

        function initializeSlick() {
            // Custom Order is a quantity list, not a carousel. Slick clones slides,
            // strips input ids, and sets user-select:none — which makes the +/-
            // steppers and number fields unusable.
            const isCustomOrder = document.body.classList.contains('postid-3947');
            // While the flavour search is active, keep a plain (un-slick) list so
            // every match stays in the DOM and can be filtered by display.
            if (isCustomOrder || (window.isDonutSearchActive && window.isDonutSearchActive())) {
                if (availableProducts.hasClass("slick-initialized")) {
                    availableProducts.slick('unslick');
                }
                return;
            }
            if (window.innerWidth < 1084 && !availableProducts.hasClass("slick-initialized")) {
                availableProducts.slick({
                    slidesToShow: 3,
                    slidesToScroll: 1,
                    centerMode: true,
                    centerPadding: '40px',
                    arrows: true,
                    // Disable touch dragging on mobile so vertical page scrolling
                    // isn't hijacked by the carousel; flavours are navigated via arrows.
                    swipe: false,
                    touchMove: false,
                    draggable: false,
                    prevArrow: '<button id="slider-prev" type="button" class="slick-prev slick-arrow"><span class="text-black-full"><</span></button>',
                    nextArrow: '<button id="slider-next" type="button" class="slick-next slick-arrow"><span class="text-black-full">></span></button>',
                    responsive: [{
                            breakpoint: 768,
                            settings: {
                                slidesToShow: 1,
                                slidesToScroll: 1,
                                centerMode: true,
                                centerPadding: '40px',
                            }
                        },
                        {
                            breakpoint: 481,
                            settings: {
                                slidesToShow: 1,
                                slidesToScroll: 1,
                                centerMode: false,
                                centerPadding: '0px'
                            }
                        }
                    ]
                });
            } else if (window.innerWidth >= 1084 && availableProducts.hasClass("slick-initialized")) {
                availableProducts.slick('unslick');
            }
        }

        function debounce(func, wait) {
            let timeout;
            return function() {
                const context = this,
                    args = arguments;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }

        const debouncedResize = debounce(initializeSlick, 250);

        // Expose so the flavour search can rebuild the carousel when cleared.
        window.rdInitDonutSlick = initializeSlick;

        initializeSlick();
        jQuery(window).on('resize', debouncedResize);
    });

    function debounce(func, delay) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), delay);
        };
    }

    document.querySelectorAll('.product-quantity-input').forEach(input => {
        input.addEventListener('input', debounce(event => {
            const quantity = parseInt(event.target.value, 10) || 0;
            const id = parseInt(event.target.getAttribute('data-product-id'), 10);
            const change = new Event('change', { bubbles: true });
            event.target.value = quantity;
            if (id) {
                document.querySelectorAll('.product-quantity-input[data-product-id="' + id + '"]').forEach(function(el) {
                    el.value = quantity;
                });
            }
            event.target.dispatchEvent(change);
        }, 300));
    });

    document.addEventListener('DOMContentLoaded', function() {
        const floatingAddToCartBtn = document.getElementById('floating-add-to-cart-btn');
        const mainAddToCartBtn = document.getElementById('add-box-to-cart');

        // Synchronize button states
        function syncButtonState() {
            const isDisabled = mainAddToCartBtn.disabled;
            floatingAddToCartBtn.disabled = isDisabled;
        }

        // Update floating button state when the main button changes
        mainAddToCartBtn.addEventListener('click', function() {
            floatingAddToCartBtn.textContent = 'Processing...';
            floatingAddToCartBtn.disabled = true;
        });

        // Handle "Add to Cart" click for the floating button
        floatingAddToCartBtn.addEventListener('click', function() {
            floatingAddToCartBtn.textContent = 'Processing...';
            floatingAddToCartBtn.disabled = true;

            // Simulate a click on the main "Add to Cart" button
            mainAddToCartBtn.click();

            // Reset floating button state (optional: replace with actual cart response logic)
            setTimeout(() => {
                floatingAddToCartBtn.textContent = '<?php _e("Add Box to Cart", "donut-box-builder"); ?>';
                syncButtonState();
            }, 2000); // Adjust this delay to match cart processing time
        });

        // Sync the floating button's state on page load
        syncButtonState();
    });
    document.addEventListener('DOMContentLoaded', function() {
        const boxItemsContainer = document.getElementById('box-container');
        const availableProductsContainer = document.getElementById('available-products-container');
        const summaryParent = document.querySelector('.summary');

        function moveAvailableProducts() {
            if (window.innerWidth < 1084) {
                // Move `#available-products-container` below `#box_items`
                if (boxItemsContainer && availableProductsContainer) {
                    boxItemsContainer.after(availableProductsContainer);
                }
            } else {
                // Restore `#available-products-container` to its original location
                if (summaryParent && availableProductsContainer) {
                    summaryParent.parentNode.insertBefore(availableProductsContainer, summaryParent);
                }
            }
        }

        // Initial positioning
        moveAvailableProducts();

        // Listen for resize events
        window.addEventListener('resize', moveAvailableProducts);
    });

    document.addEventListener('DOMContentLoaded', () => {
    const mainBtn     = document.getElementById('add-box-to-cart');
    const floatingBtn = document.getElementById('floating-add-to-cart-btn');

    if (!mainBtn || !floatingBtn) return;   // safety check

    /* 1 – initial sync (covers refresh / Ajax-reloaded pages) */
    floatingBtn.disabled = mainBtn.disabled;

    /* 2 – always stay in lock-step afterwards */
    new MutationObserver(() => {
        floatingBtn.disabled = mainBtn.disabled;
    }).observe(mainBtn, { attributes: true, attributeFilter: ['disabled'] });
});

/* Live flavour search ----------------------------------------------------- */
document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('donut-search');
    var grid  = document.getElementById('available-products');
    if (!input || !grid) return;

    var clearBtn = document.getElementById('donut-search-clear');
    var emptyMsg = document.getElementById('donut-search-empty');

    // Lets initializeSlick() know to keep the list un-slicked while searching.
    window.isDonutSearchActive = function () {
        return (input.value || '').trim() !== '';
    };

    function itemMatches(el, q) {
        if (!q) return true;
        var name = '';
        var di = el.getAttribute('data-item');
        if (di) {
            try { name = (JSON.parse(di).name || '').toLowerCase(); } catch (e) {}
        }
        if (!name) {
            var title = el.querySelector('.pd_title');
            name = title ? title.textContent.toLowerCase() : '';
        }
        // Allow matching category slugs too (e.g. "vegan", "ring donuts").
        var cls = (el.className || '').toLowerCase().replace(/-/g, ' ');
        return name.indexOf(q) !== -1 || cls.indexOf(q) !== -1;
    }

    function applyFilter() {
        var q = (input.value || '').trim().toLowerCase();

        // Drop out of the carousel while searching (and rebuild it when cleared)
        // so all matches stay in the DOM and we never lose detached slides.
        if (typeof window.rdInitDonutSlick === 'function') {
            window.rdInitDonutSlick();
        }
        grid.classList.toggle('rd-searching', q !== '');

        var items = grid.querySelectorAll('.product-item:not(.slick-cloned)');
        var visible = 0;
        items.forEach(function (el) {
            var show = itemMatches(el, q);
            el.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        if (clearBtn) clearBtn.classList.toggle('hidden', q === '');
        if (emptyMsg) emptyMsg.classList.toggle('hidden', visible !== 0);
    }

    var t;
    input.addEventListener('input', function () {
        clearTimeout(t);
        t = setTimeout(applyFilter, 150);
    });
    input.addEventListener('search', applyFilter); // native clear (×) in some browsers

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            input.value = '';
            applyFilter();
            input.focus();
        });
    }
});

</script>