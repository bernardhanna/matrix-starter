<?php
/**
 * Featured donuts slider (home).
 */
$featured_donuts = get_field('donuts');
if (empty($featured_donuts) || ! is_array($featured_donuts)) {
    return;
}

$donut_posts = [];
foreach ($featured_donuts as $donut) {
    $post_id = is_object($donut) ? (int) $donut->ID : (int) $donut;
    if ($post_id > 0) {
        $donut_posts[] = get_post($post_id);
    }
}
$donut_posts = array_filter($donut_posts);
if (empty($donut_posts)) {
    return;
}

$total = count($donut_posts);
?>
<section class="<?php echo esc_attr(matrix_rd_section_shell_classes('featured-donuts relative bg-black')); ?>" id="featured-section">
  <div class="splide relative overflow-visible tablet-sm:[&_.splide__pagination]:hidden" id="featured-slider" role="group" aria-roledescription="carousel" aria-label="<?php esc_attr_e('Featured donuts', 'matrix-starter'); ?>">
    <div class="splide__track" id="featured-slider-track">
      <div class="splide__list">
        <?php foreach ($donut_posts as $donut) : ?>
          <?php
          $bg_color    = get_field('featured_donut_bg_color', $donut->ID) ?: '#ffed56';
          $allergens   = get_field('product_allergens', $donut->ID);
          $description = apply_filters('the_content', $donut->post_content);
          $image_url   = get_the_post_thumbnail_url($donut->ID, 'large') ?: get_the_post_thumbnail_url($donut->ID);
          ?>
          <div class="splide__slide" style="background-color: <?php echo esc_attr($bg_color); ?>">
            <div class="<?php echo esc_attr(matrix_rd_section_inner_classes('featured-slide tablet-sm:flex-row flex h-full flex-col pb-8 tablet-sm:pb-0')); ?>">
              <div class="left-feature w-full tablet-sm:w-45">
                <img
                  class="featured-image mobile:h-full max-tablet-sm:aspect-square xyz-in xyz-n10 w-full object-cover transition duration-150 ease-in-out"
                  xyz="fade up big"
                  src="<?php echo esc_url($image_url); ?>"
                  sizes="(max-width: 640px) 309px, 800px"
                  alt="<?php echo esc_attr($donut->post_title); ?>"
                />
              </div>
              <div class="w-full tablet-sm:w-1/2 h-auto laptop:h-[800px]" style="background-color: <?php echo esc_attr($bg_color); ?>">
                <div class="flex h-full flex-col items-center">
                  <div class="relative mt-12 flex w-full max-tablet-sm:p-8 flex-col items-start pb-12 tablet-sm:px-24 xxl:mt-32">
                    <p class="slide-count text-md-font tablet-sm:text-mob-xxl-font">
                      <span class="start-count font-reg420">1</span>/<?php echo (int) $total; ?>
                    </p>
                    <h3 class="text-lg-font tablet-sm:text-xl-font font-reg420 transition duration-300 ease-in-out delay-700">
                      <?php echo esc_html($donut->post_title); ?>
                    </h3>
                    <div class="text-sm-font tablet-sm:text-base-font max-w-max-573 w-full text-left">
                      <?php echo $description; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    </div>
                    <span class="text-lg-font tablet-sm:text-xl-font font-reg420 pb-4 pt-8"><?php esc_html_e('Allergens', 'matrix-starter'); ?></span>
                    <?php if (! empty($allergens) && is_array($allergens)) : ?>
                      <ul class="allergen-list">
                        <?php foreach ($allergens as $allergen) : ?>
                          <?php
                          $aid = is_object($allergen) ? (int) $allergen->ID : (int) $allergen;
                          if ($aid <= 0) {
                              continue;
                          }
                          ?>
                          <li class="flex items-center pb-[12px]">
                            <img src="<?php echo esc_url(get_the_post_thumbnail_url($aid)); ?>" alt="" class="allergen-img mr-[10px]" />
                            <span class="allergen-title text-base-font font-medium text-black-full"><?php echo esc_html(get_the_title($aid)); ?></span>
                          </li>
                        <?php endforeach; ?>
                      </ul>
                    <?php endif; ?>
                    <a class="btn-width mt-6 flex h-[64px] w-full flex-row items-center justify-center rounded-btn-72 border-3 border-black bg-white text-sm-md-font font-reg420 text-black-full transition delay-150 hover:border-yellow-primary hover:bg-yellow-primary hover:text-black-full tablet-sm:h-[72px] tablet-sm:w-[362px] tablet-sm:text-md-font md:w-[322px]" href="<?php echo esc_url(home_url('/donut-box/')); ?>">
                      <?php esc_html_e('Order Now', 'matrix-starter'); ?>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="splide tablet-sm:w-[100px] tablet-sm:mx-auto laptop:h-[700px] tablet-sm:absolute top-0 max-laptop:left-12 left-0 laptop:right-38 tablet-sm:right-[11.5rem] hidden h-full tablet-sm:flex tablet-sm:flex-col tablet-sm:items-center tablet-sm:justify-center" id="donut-thumb-slider">
      <div class="splide__track flex tablet-sm:h-full tablet-sm:items-center tablet-sm:justify-between">
        <div class="splide__list flex flex-col">
          <?php foreach ($donut_posts as $donut) : ?>
            <?php
            $thumb_src = matrix_rd_acf_image_url(get_field('thumb_image', $donut->ID));
            if ($thumb_src === '') {
                $thumb_src = get_the_post_thumbnail_url($donut->ID, 'thumbnail') ?: '';
            }
            ?>
            <div class="splide__slide donut-indicator rounded-full border-4 border-solid border-black-full bg-white p-2.5">
              <img src="<?php echo esc_url($thumb_src); ?>" alt="<?php echo esc_attr($donut->post_title . ' Thumbnail'); ?>" height="48" width="48" />
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php get_template_part('template-parts/home/partials/featured-slider-arrows'); ?>
  </div>
</section>
<style>
  /* Hide the desktop-only vertical thumbnail nav on phones (it uses absolute
     positioning that only makes sense alongside the desktop layout). Below
     tablet-sm (993px) the main slider is navigated via swipe + pagination dots. */
  @media (max-width: 992px) {
    #featured-slider #donut-thumb-slider {
      display: none !important;
    }
  }

  /* Give the Splide pagination dots breathing room on phones. */
  @media (max-width: 992px) {
    #featured-slider .splide__pagination {
      position: relative;
      margin-top: 0.5rem;
      padding: 0.75rem 0;
    }

    #featured-slider .splide__pagination__page {
      width: 10px;
      height: 10px;
      background: rgba(0, 0, 0, 0.35);
    }

    #featured-slider .splide__pagination__page.is-active {
      background: #000;
      transform: scale(1.2);
    }
  }
</style>
