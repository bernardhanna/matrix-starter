<?php
/**
 * Bestsellers slider (home) — legacy markup with Alpine hover states.
 */
$products = get_field('product');
if (empty($products) || ! is_array($products)) {
    return;
}

$product_posts = [];
foreach ($products as $product) {
    $post_id = is_object($product) ? (int) $product->ID : (int) $product;
    if ($post_id > 0) {
        $product_posts[] = get_post($post_id);
    }
}
$product_posts = array_filter($product_posts);
if (empty($product_posts)) {
    return;
}

$bg_image   = matrix_rd_acf_image(get_field('bg_image'), '', 'large');
$text_image = matrix_rd_acf_image(get_field('text_image'), '', 'large');
$heading    = get_field('heading');
?>
<section
  class="<?php echo esc_attr(matrix_rd_section_shell_classes('bestsellers-slider relative bg-repeat')); ?>"
  <?php if ($bg_image['url']) : ?>
    style="background-image: url('<?php echo esc_url($bg_image['url']); ?>');"
  <?php endif; ?>
>
  <div class="overlay">
    <?php if ($text_image['url']) : ?>
      <img class="text-image mx-auto h-[86px] w-full max-w-max-1000 object-contain lg:h-full" src="<?php echo esc_url($text_image['url']); ?>" alt="<?php echo esc_attr($text_image['alt']); ?>" loading="lazy" decoding="async" />
    <?php endif; ?>
    <?php if ($heading) : ?>
      <h1 id="main" class="text-mob-xxl-font lg:text-lg-font font-regular relative text-center text-white">
        <?php echo esc_html($heading); ?>
      </h1>
    <?php endif; ?>
    <div class="<?php echo esc_attr(matrix_rd_section_inner_classes('relative top-0 flex flex-col content align-center pt-4 pb-20 max-desktop:px-6')); ?>">
      <div class="bestseller-splide splide relative w-full lg:pt-8 lg:px-14 xl:px-16" role="group" aria-roledescription="carousel" aria-label="<?php esc_attr_e('Bestselling donuts', 'matrix-starter'); ?>">
        <div class="splide__track">
          <div class="splide__list">
            <?php foreach ($product_posts as $product) : ?>
              <?php
              $product_allergens = get_field('product_allergens', $product->ID);
              $box_number        = get_field('box_number', $product->ID);
              $wc_product        = function_exists('wc_get_product') ? wc_get_product($product->ID) : null;
              $price             = $wc_product ? $wc_product->get_price() : get_post_meta($product->ID, '_price', true);
              $short_description = $wc_product ? $wc_product->get_short_description() : '';
              $permalink         = get_permalink($product->ID);
              $image_url         = get_the_post_thumbnail_url($product->ID, 'large') ?: get_the_post_thumbnail_url($product->ID);
              ?>
              <div class="splide__slide item flex lg:p-0" x-data="{ showAllergens: false }">
                <?php if (! empty($product_allergens) && is_array($product_allergens)) : ?>
                  <div
                    class="absolute right-20 top-6 z-50 max-sm:right-10 lg:right-6 lg:top-6 cursor-pointer"
                    @click="showAllergens = !showAllergens"
                  >
                    <div class="z-50" x-show="!showAllergens">
                      <span class="sr-only"><?php esc_html_e('info icon', 'matrix-starter'); ?></span>
                      <svg xmlns="http://www.w3.org/2000/svg" width="31" height="30" viewBox="0 0 31 30" fill="none" aria-hidden="true">
                        <circle cx="15.678" cy="14.8499" r="14.721" fill="black" />
                        <path d="M15.6767 26.0037C21.8359 26.0037 26.8289 21.0107 26.8289 14.8515C26.8289 8.69226 21.8359 3.69922 15.6767 3.69922C9.51745 3.69922 4.52441 8.69226 4.52441 14.8515C4.52441 21.0107 9.51745 26.0037 15.6767 26.0037Z" stroke="white" stroke-width="2.67654" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M15.6777 19.3125V14.8516" stroke="white" stroke-width="2.67654" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M15.6777 10.3906H15.6889" stroke="white" stroke-width="2.67654" stroke-linecap="round" stroke-linejoin="round" />
                      </svg>
                    </div>
                    <div x-show="showAllergens" class="relative right-1.5 top-1.5 z-50 rounded-t-lg">
                      <span class="sr-only"><?php esc_html_e('close', 'matrix-starter'); ?></span>
                      <svg xmlns="http://www.w3.org/2000/svg" width="23" height="22" viewBox="0 0 23 22" fill="none" aria-hidden="true">
                        <rect x="1.5" y="1" width="20" height="20" rx="10" fill="black" />
                        <circle cx="11.5" cy="11" r="11" fill="#FFED56" />
                        <path d="M11.4993 19.3346C16.1017 19.3346 19.8327 15.6037 19.8327 11.0013C19.8327 6.39893 16.1017 2.66797 11.4993 2.66797C6.89698 2.66797 3.16602 6.39893 3.16602 11.0013C3.16602 15.6037 6.89698 19.3346 11.4993 19.3346Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M14.5 14L8.5 8" stroke="#0E1217" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M8.5 14L14.5 8" stroke="#0E1217" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                      </svg>
                    </div>
                  </div>
                  <div
                    x-show="showAllergens"
                    class="allergen-info absolute -m-[10px] right-4 top-4 z-40 w-[220px] rounded-tl-lg bg-white p-4 text-black"
                  >
                    <span class="text-sm-font font-reg420 text-black-full"><?php esc_html_e('Allergens', 'matrix-starter'); ?></span>
                    <div class="mt-4 w-full">
                      <div class="flex w-full flex-row flex-wrap">
                        <?php foreach ($product_allergens as $allergen) : ?>
                          <?php
                          $allergen_id = is_object($allergen) ? (int) $allergen->ID : (int) $allergen;
                          if ($allergen_id <= 0 || ! has_post_thumbnail($allergen_id)) {
                              continue;
                          }
                          ?>
                          <div class="row flex w-1/2 items-center pb-4">
                            <img src="<?php echo esc_url(get_the_post_thumbnail_url($allergen_id, 'thumbnail')); ?>" alt="<?php echo esc_attr(get_the_title($allergen_id)); ?>" class="mr-1 h-6 w-6" loading="lazy" decoding="async" />
                            <span class="text-mob-xs-font font-regular font-laca"><?php echo esc_html(get_the_title($allergen_id)); ?></span>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    </div>
                  </div>
                <?php endif; ?>

                <a
                  href="<?php echo esc_url($permalink); ?>"
                  class="relative w-full bg-transparent px-12 max-sm:px-6 lg:rounded-sm-10 lg:px-0"
                  x-data="{ isHovered: false, isLargeScreen: window.innerWidth > 1084 }"
                  x-init="$nextTick(() => { isLargeScreen = window.innerWidth > 1084 }); window.addEventListener('resize', () => { isLargeScreen = window.innerWidth > 1084 })"
                  :style="(isLargeScreen && isHovered) ? 'background-color: transparent;' : (isLargeScreen ? 'background-color: white;' : 'background-color: transparent;')"
                  style="background-color: white;"
                >
                  <div class="light-black-gradient absolute inset-0 z-10 h-[386px] rounded-sm-10 opacity-50"></div>
                  <img
                    class="bestseller_image relative h-[386px] w-full rounded-sm-8 border-3 border-solid border-black-full object-cover"
                    src="<?php echo esc_url($image_url); ?>"
                    alt="<?php echo esc_attr($product->post_title); ?>"
                    loading="lazy"
                    decoding="async"
                  />
                  <div
                    id="product-content-one-<?php echo (int) $product->ID; ?>"
                    class="product-content-one absolute inset-0 z-40 flex h-[386px] flex-col items-center justify-end p-4 lg:items-start"
                    @mouseenter="isLargeScreen && (isHovered = true)"
                    @mouseleave="isLargeScreen && (isHovered = false)"
                    x-transition:enter.duration.500ms.delay.50ms
                    x-transition:leave.duration.400ms
                  >
                    <?php if (! empty($box_number)) : ?>
                      <div class="absolute left-4 top-4 z-10 hidden rounded-normal border-2 border-black-full border-normal bg-white p-2 text-center font-laca text-black-full lg:flex">
                        <?php
                        /* translators: %s: box quantity */
                        printf(esc_html__('Box of %s', 'matrix-starter'), esc_html($box_number));
                        ?>
                      </div>
                    <?php endif; ?>
                    <h4
                      class="text-sm-md-font lg:text-md-font font-regular z-10 pb-0 text-center leading-8 text-white lg:pb-6 lg:text-left lg:font-reg420 lg:md-font"
                      x-transition.delay.150ms
                    >
                      <?php echo esc_html($product->post_title); ?>
                    </h4>
                    <span class="animate-button btn btn-primary text-mob-md-font lg:text-reg-font font-medium mb-4 flex h-[56px] w-[257px] items-center justify-center rounded-lg-x bg-white text-center text-black-full hover:bg-yellow-primary lg:mb-10 lg:hidden">
                      <?php esc_html_e('Select and Customise', 'matrix-starter'); ?>
                    </span>
                    <div
                      id="product-info-<?php echo (int) $product->ID; ?>"
                      class="product-info flex w-full items-center justify-between"
                      x-show.transition="isHovered"
                      x-transition:enter.duration.500ms
                      x-transition:leave.duration.400ms
                    >
                      <?php if ($short_description) : ?>
                        <p class="text-sm-md-font font-light text-left text-white font-laca">
                          <?php echo esc_html(wp_strip_all_tags($short_description)); ?>
                        </p>
                      <?php endif; ?>
                      <?php if ($price !== '' && $price !== null) : ?>
                        <span class="text-sm-md-font font-light text-right text-white font-laca">€<?php echo esc_html(number_format((float) $price, 2, '.', ',')); ?></span>
                      <?php endif; ?>
                    </div>
                  </div>
                  <div
                    id="product-content-two-<?php echo (int) $product->ID; ?>"
                    class="product-content-two animate-fade flex flex-col"
                    @mouseenter="isLargeScreen && (isHovered = true)"
                    @mouseleave="isLargeScreen && (isHovered = false)"
                  >
                    <div
                      class="relative hidden w-full flex-row justify-between rounded-bl-sm-8 rounded-br-sm-8 p-4 py-4 lg:flex"
                      x-bind:style="isHovered && isLargeScreen ? 'background-color: transparent;' : 'background-color: white;'"
                      x-show="!isHovered"
                      style="background-color: white;"
                    >
                      <?php if ($short_description) : ?>
                        <p class="w-90 text-sm-md-font font-light text-left text-black-full font-laca">
                          <?php echo esc_html(wp_strip_all_tags($short_description)); ?>
                        </p>
                      <?php endif; ?>
                      <?php if ($price !== '' && $price !== null) : ?>
                        <span class="w-1/3 text-right text-sm-md-font font-reg420 text-black-full font-edmondsans">€<?php echo esc_html(number_format((float) $price, 2, '.', ',')); ?></span>
                      <?php endif; ?>
                    </div>
                    <div class="relative mt-2">
                      <span
                        x-show.transition="isHovered"
                        role="link"
                        tabindex="0"
                        @click.stop="window.location.href = '<?php echo esc_js($permalink); ?>'"
                        @keydown.enter.stop="window.location.href = '<?php echo esc_js($permalink); ?>'"
                        class="button flex h-[58px] w-full cursor-pointer items-center justify-center rounded-large border-2 border-solid border-black-full bg-white font-reg420 hover:bg-yellow-primary sm-md-font"
                      >
                        <?php esc_html_e('View Box', 'matrix-starter'); ?>
                      </span>
                    </div>
                  </div>
                </a>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<style>
  /* Desktop: arrows in side gutters, not over slides */
  @media (width >= 1085px) {
    .bestsellers-slider .bestseller-splide .splide__arrow {
      top: 50%;
      transform: translateY(-50%);
      width: 3rem;
      height: 3rem;
      border-radius: 9999px;
      background: rgb(0 0 0 / 65%);
      opacity: 1;
      z-index: 20;
    }

    .bestsellers-slider .bestseller-splide .splide__arrow--prev {
      left: 0;
    }

    .bestsellers-slider .bestseller-splide .splide__arrow--next {
      right: 0;
    }

    .bestsellers-slider .bestseller-splide .splide__arrow svg {
      fill: #fff;
    }

    .bestsellers-slider .bestseller-splide .splide__arrow:disabled {
      opacity: 0.35;
    }
  }

  @media (width <= 1084px) {
    .bestsellers-slider .bestseller-splide .splide__arrow {
      background: #ffed56;
      width: 3rem;
      height: 3rem;
    }

    .bestsellers-slider .bestseller-splide .splide__arrow svg {
      fill: #000;
    }

    .bestsellers-slider .bestseller-splide .splide__arrow--prev {
      left: -0.5rem;
    }

    .bestsellers-slider .bestseller-splide .splide__arrow--next {
      right: -0.5rem;
    }
  }

  @media (width >= 1550px) {
    .bestseller-splide .product-content-two {
      height: auto;
      min-height: 150px;
    }
  }
</style>
