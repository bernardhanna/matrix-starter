  <?php

    /**
     * @Author: Bernard Hanna
     * @Date:   2023-08-10 12:18:40
     * @Last Modified by:   Bernard Hanna
     * @Last Modified time: 2023-10-10 12:23:09
     */
    global $product;

    $rd_product_type = get_rd_product_type($product->get_id());

    ?>
  <li id="<?php echo esc_attr($product->get_slug()); ?>" <?php wc_product_class('w-48 lg:w-31-5 product-small-device flex flex-col relative md:pb-12 lg:pb-0', $product); ?> x-data="{ showAllergens: false, windowWidth: window.innerWidth }" @resize.window="windowWidth = window.innerWidth">
      <?php
        $product_allergens = matrix_rd_acf_posts(get_field('product_allergens', $product->get_id()));
        $box_number = get_field('box_number', $product->get_id());
        $allergen_text = '';
        if ($product_allergens !== []) {
            foreach ($product_allergens as $allergen) {
                $allergen_text .= $allergen->post_title . ', ';
            }
            $allergen_text = rtrim($allergen_text, ' ');
        }
        $rd_product_type = get_rd_product_type($product->get_id());
        $button_url = ($rd_product_type == 'Donut') ? '/donut-box/' : get_permalink();
        ?>
        <?php if ($product_allergens !== []) : ?>
                    <div class="absolute top-4 right-4 z-50 cursor-pointer" @click="showAllergens = !showAllergens">
            <div class="z-50" x-show="!showAllergens">
                <span class="sr-only"><?php _e('info icon', 'rolling-donut'); ?></span>
                <div class="allergen_svg"></div>
            </div>
            <div x-cloak x-show="showAllergens" class="relative top-1.5 right-1.5 z-50 rounded-t-lg">
                <span class="sr-only"><?php _e('close', 'rolling-donut'); ?></span>
                <svg xmlns="http://www.w3.org/2000/svg" width="23" height="22" viewBox="0 0 23 22" fill="none">
                    <rect x="1.5" y="1" width="20" height="20" rx="10" fill="black" />
                    <circle cx="11.5" cy="11" r="11" fill="#FFED56" />
                    <path d="M11.4993 19.3346C16.1017 19.3346 19.8327 15.6037 19.8327 11.0013C19.8327 6.39893 16.1017 2.66797 11.4993 2.66797C6.89698 2.66797 3.16602 6.39893 3.16602 11.0013C3.16602 15.6037 6.89698 19.3346 11.4993 19.3346Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M14.5 14L8.5 8" stroke="#0E1217" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M8.5 14L14.5 8" stroke="#0E1217" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
        </div>
        <div x-cloak x-show="showAllergens" class="p-4 rounded-tl-lg z-40 allergen-info absolute top-4 right-4 bg-white text-black w-[220px] -m-[10px]">
            <span class="text-black-full text-sm-font font-reg420"><?php _e('Allergens', 'rolling-donut'); ?></span>
            <div class="mt-4 w-full">
                <div class="flex flex-row flex-wrap w-full">
                    <?php
                    if ($product_allergens !== []) {
                        foreach ($product_allergens as $allergen) {
                            $allergen_id = $allergen->ID;
                            if (has_post_thumbnail($allergen_id)) {
                                echo '<div class="flex items-center pb-4 w-1/2 row"><img src="' . get_the_post_thumbnail_url($allergen_id, 'thumbnail') . '" alt="' . $allergen->post_title . '" class="mr-1 w-6 h-6">';
                            }
                            echo '<span class="font-laca text-mob-xs-font font-regular">';
                            echo esc_html($allergen->post_title);
                            echo '</span>';
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
      <div class="relative w-full max-md:rounded-t-lg">
          <?php $bg_color = get_field('featured_donut_bg_color'); ?>
            <?php
            // Get WooCommerce gallery image IDs for the product.
            $gallery_ids = $product->get_gallery_image_ids();

            if (! empty($gallery_ids)) {
                // Build an array starting with the featured image.
                $featured_image_url = get_the_post_thumbnail_url($product->get_id(), 'full');
                $images = array();
                if ($featured_image_url) {
                    $images[] = $featured_image_url;
                }
                foreach ($gallery_ids as $gallery_id) {
                    $img_url = wp_get_attachment_url($gallery_id);
                    if ($img_url && ! in_array($img_url, $images, true)) {
                        $images[] = $img_url;
                    }
                }
                $gallery_container_id = 'product-gallery-' . $product->get_id();
            ?>
                <div
                    id="<?php echo esc_attr($gallery_container_id); ?>"
                    class="rd-box-product-gallery relative border-t-8 border-black w-full h-[300px] md:h-[386px] rounded-md overflow-hidden"
                    style="background-color: <?php echo esc_attr($bg_color); ?>;"
                    data-rd-box-gallery
                >
                    <?php foreach ($images as $index => $image_url) : ?>
                        <img
                            src="<?php echo esc_url($image_url); ?>"
                            class="gallery-image absolute inset-0 w-full h-full object-cover transition-opacity duration-700 ease-in-out <?php echo $index === 0 ? 'opacity-100' : 'opacity-0'; ?>"
                            alt="<?php the_title_attribute(); ?>" />
                    <?php endforeach; ?>

                    <a href="<?php echo esc_url($button_url); ?>" aria-label="<?php echo esc_attr(sprintf(__('View %s', 'matrix-starter'), wp_strip_all_tags(get_the_title()))); ?>" class="absolute inset-0 z-10 rounded-sm-10"></a>

                    <?php if (count($images) > 1) : ?>
                    <button
                        type="button"
                        class="rd-box-gallery-arrow rd-box-gallery-arrow--prev absolute left-0 top-1/2 z-30 px-4 pt-1 m-1 text-white rounded-full transform -translate-y-1/2 gallery-arrow bg-black-full hover:bg-white hover:text-black-full text-mob-lg-font hover:bg-black/70"
                        aria-label="<?php esc_attr_e('Previous image', 'matrix-starter'); ?>"
                    >
                      <
                    </button>

                    <button
                        type="button"
                        class="rd-box-gallery-arrow rd-box-gallery-arrow--next absolute right-0 top-1/2 z-30 px-4 pt-1 m-1 text-white rounded-full transform -translate-y-1/2 gallery-arrow bg-black-full hover:bg-white hover:text-black-full text-mob-lg-font hover:bg-black/70"
                        aria-label="<?php esc_attr_e('Next image', 'matrix-starter'); ?>"
                    >
                      >
                    </button>
                    <?php endif; ?>
                </div>

            <?php
            } else {
                echo '<a href="' . esc_url($button_url) . '" aria-label="' . esc_attr(sprintf(__('View %s', 'matrix-starter'), wp_strip_all_tags(get_the_title()))) . '" class="block">';
                echo woocommerce_get_product_thumbnail('full', array(
                    'class' => 'border-top-eight max-md:rounded-t-lg w-full h-max-max-125 md:max-h-full object-cover h-auto md:h-[386px] md:border-2 md:border-solid md:border-black md:rounded-sm-8 m-0 max-lg:aspect-5/4',
                    'style' => "background-color: {$bg_color};"
                ));
                echo '</a>';
            }
            ?>


          <?php if (!empty($box_number)) : ?>
              <div class="flex absolute top-4 left-4 z-20 p-2 text-center bg-white border-2 text-black-full font-laca border-black-full border-normal rounded-normal pointer-events-none">Box of <?php echo $box_number; ?></div>
          <?php endif; ?>

          <?php if ($rd_product_type !== 'Donut') : ?>

              <div class="flex absolute top-4 right-4 z-20 p-2 text-center bg-white border-2 text-black-full font-laca border-black-full border-normal rounded-normal pointer-events-none"><?php woocommerce_template_loop_price(); ?></div>

          <?php endif; ?>
          <div class="flex flex-col max-md md:pt-4">
              <div class="flex flex-col justify-center md:mt-2 max-md:flex-col max-md:p-4">
                  <h4 class="block text-center text-black-full text-mob-md-font lg:text-sm-md-font font-reg420 font-edmondsans"><?php the_title(); ?></h4>
                 <?php if ($product->get_short_description()) : ?>
                    <p style="line-height: 1.5rem;" class="text-center text-mob-xs-font lg:text-sm-font text-black-full !leading-tight">
                        <?php echo wp_kses_post($product->get_short_description()); ?>
                    </p>
                <?php endif; ?>
              </div>
              <div class="flex p-4">
                  <a href="<?php echo esc_url($button_url); ?>"
                      class="button w-full text-mob-xs-font md:text-sm-font font-reg420 h-[56px] flex justify-center items-center rounded-large border-black-full border-solid border-2 bg-white hover:bg-yellow-primary">

                      <?php
                        if ($rd_product_type == 'Donut') {
                            echo __('Order now', 'rolling-donut');
                        } elseif ($rd_product_type == 'Merch') {
                            echo __('View Product', 'rolling-donut');
                        } else {
                            echo __('View Box', 'rolling-donut');
                        }
                        ?>
                  </a>
              </div>
          </div>
                    </div>
  </li>