<?php
/**
 * Featured donuts Splide controls — match homepage hero arrows + dots.
 */
$featured_arrow_prev_svg = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20.4375 11.1094H6.72422L14.932 3.98438C15.0633 3.86953 14.9836 3.65625 14.8102 3.65625H12.7359C12.6445 3.65625 12.5578 3.68906 12.4898 3.74766L3.63281 11.4328C3.46868 11.5751 3.37438 11.7816 3.37438 11.9988C3.37438 12.216 3.46868 12.4226 3.63281 12.5648L12.5414 20.2969C12.5766 20.3273 12.6188 20.3438 12.6633 20.3438H14.8078C14.9813 20.3438 15.0609 20.1281 14.9297 20.0156L6.72422 12.8906H20.4375C20.5406 12.8906 20.625 12.8062 20.625 12.7031V11.2969C20.625 11.1938 20.5406 11.1094 20.4375 11.1094Z" fill="currentColor"/></svg>';
$featured_arrow_next_svg = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20.3672 11.4328L11.5125 3.74766C11.4445 3.68906 11.3578 3.65625 11.2664 3.65625H9.19219C9.01875 3.65625 8.93906 3.87188 9.07031 3.98438L17.2781 11.1094H3.5625C3.45938 11.1094 3.375 11.1938 3.375 11.2969V12.7031C3.375 12.8062 3.45938 12.8906 3.5625 12.8906H17.2758L9.06797 20.0156C8.93672 20.1305 9.01641 20.3438 9.18984 20.3438H11.3344C11.3789 20.3438 11.4234 20.3273 11.4563 20.2969L20.3672 12.5672C20.5314 12.4244 20.6256 12.2176 20.6256 12C20.6256 11.7824 20.5314 11.5756 20.3672 11.4328Z" fill="currentColor"/></svg>';
?>
<div class="featured-slider__controls">
  <div class="splide__arrows featured-slider__arrows">
    <button
      class="splide__arrow splide__arrow--prev"
      type="button"
      aria-label="<?php esc_attr_e('Previous slide', 'matrix-starter'); ?>"
      aria-controls="featured-slider-track"
    >
      <?php echo $featured_arrow_prev_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
      <span class="sr-only"><?php esc_html_e('Previous slide', 'matrix-starter'); ?></span>
    </button>
    <button
      class="splide__arrow splide__arrow--next"
      type="button"
      aria-label="<?php esc_attr_e('Next slide', 'matrix-starter'); ?>"
      aria-controls="featured-slider-track"
    >
      <?php echo $featured_arrow_next_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
      <span class="sr-only"><?php esc_html_e('Next slide', 'matrix-starter'); ?></span>
    </button>
  </div>
  <ul
    class="splide__pagination featured-slider__pagination"
    id="featured-slider-pagination"
    role="tablist"
    aria-label="<?php esc_attr_e('Featured donut slide pagination', 'matrix-starter'); ?>"
  ></ul>
</div>
