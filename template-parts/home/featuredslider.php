<?php
/**
 * Featured donuts / featured slides slider (home).
 *
 * Prefer ACF repeater `featured_slides` (editable like the hero). Falls back to
 * the legacy `donuts` product relationship when the repeater is empty.
 */
$slides = function_exists('matrix_rd_get_featured_slides') ? matrix_rd_get_featured_slides() : [];
if ($slides === []) {
    return;
}

$slide_count = count($slides);
?>
<section class="<?php echo esc_attr(matrix_rd_section_shell_classes('featured-donuts relative bg-black')); ?>" id="featured-section">
  <div
    class="splide featured-donuts-slider relative overflow-hidden"
    id="featured-slider"
    role="group"
    aria-roledescription="carousel"
    aria-label="<?php esc_attr_e('Featured donuts', 'matrix-starter'); ?>"
  >
    <div class="splide__track" id="featured-slider-track">
      <div class="splide__list">
        <?php foreach ($slides as $index => $slide) : ?>
          <?php
          $image         = $slide['image'];
          $image_mobile  = $slide['image_mobile'];
          $heading       = (string) ($slide['heading'] ?? '');
          $text          = (string) ($slide['text'] ?? '');
          $bg_color      = (string) ($slide['bg_color'] ?? '#ffed56');
          $text_color    = (string) ($slide['text_color'] ?? 'black');
          $button        = is_array($slide['button'] ?? null) ? $slide['button'] : [];
          $button_url    = (string) ($button['url'] ?? '');
          $button_title  = (string) ($button['title'] ?? '');
          $button_target = (string) ($button['target'] ?? '');
          $text_class    = $text_color === 'white' ? 'featured-slide__text--white' : 'featured-slide__text--black';
          $img_alt       = $image['alt'] !== '' ? $image['alt'] : $heading;
          $desktop_url   = $image['url'] !== '' ? $image['url'] : $image_mobile['url'];
          $mobile_url    = $image_mobile['url'] !== '' ? $image_mobile['url'] : $image['url'];
          ?>
          <div
            class="splide__slide"
            style="background-color: <?php echo esc_attr($bg_color); ?>"
            aria-label="<?php echo esc_attr(sprintf(/* translators: %1$d slide number, %2$d total */ __('Slide %1$d of %2$d', 'matrix-starter'), $index + 1, $slide_count)); ?>"
          >
            <div class="featured-slide" style="background-color: <?php echo esc_attr($bg_color); ?>">
              <div class="featured-slide__media">
                <?php if ($desktop_url !== '') : ?>
                  <?php if ($mobile_url !== '' && $mobile_url !== $desktop_url) : ?>
                    <picture>
                      <source media="(max-width: 992px)" srcset="<?php echo esc_url($mobile_url); ?>" />
                      <img
                        class="featured-slide__image featured-image"
                        src="<?php echo esc_url($desktop_url); ?>"
                        alt="<?php echo esc_attr($img_alt); ?>"
                        loading="lazy"
                        decoding="async"
                      />
                    </picture>
                  <?php else : ?>
                    <img
                      class="featured-slide__image featured-image"
                      src="<?php echo esc_url($desktop_url); ?>"
                      alt="<?php echo esc_attr($img_alt); ?>"
                      loading="lazy"
                      decoding="async"
                    />
                  <?php endif; ?>
                <?php endif; ?>
              </div>
              <div class="featured-slide__panel" style="background-color: <?php echo esc_attr($bg_color); ?>">
                <div class="featured-slide__content <?php echo esc_attr($text_class); ?>">
                  <?php if ($heading !== '') : ?>
                    <h3 class="featured-slide__heading"><?php echo esc_html($heading); ?></h3>
                  <?php endif; ?>
                  <?php if ($text !== '') : ?>
                    <div class="featured-slide__body">
                      <?php
                      $text_html = strpos($text, '<') === false ? wpautop($text) : $text;
                      echo wp_kses_post($text_html);
                      ?>
                    </div>
                  <?php endif; ?>
                  <?php if ($button_url !== '') : ?>
                    <a
                      class="featured-slide__cta"
                      href="<?php echo esc_url($button_url); ?>"
                      <?php echo $button_target !== '' ? 'target="' . esc_attr($button_target) . '" rel="noopener noreferrer"' : ''; ?>
                    >
                      <?php echo esc_html($button_title !== '' ? $button_title : __('Order Now', 'matrix-starter')); ?>
                    </a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php if ($slide_count > 1) : ?>
      <?php get_template_part('template-parts/home/partials/featured-slider-arrows'); ?>
    <?php endif; ?>
  </div>
</section>
