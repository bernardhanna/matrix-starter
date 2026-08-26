<?php
/**
 * Home hero slider (Splide) — Figma hero slider component.
 */

$slides = function_exists('matrix_rd_get_home_hero_slides') ? matrix_rd_get_home_hero_slides() : [];

if ($slides === []) {
    return;
}

$slide_count = count($slides);
$hero_layout = function_exists('matrix_rd_get_home_hero_layout') ? matrix_rd_get_home_hero_layout() : 'layout_1';
$hero_layout_class = $hero_layout === 'layout_2' ? 'home-hero--layout-2' : 'home-hero--layout-1';

$hero_arrow_prev_svg = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20.4375 11.1094H6.72422L14.932 3.98438C15.0633 3.86953 14.9836 3.65625 14.8102 3.65625H12.7359C12.6445 3.65625 12.5578 3.68906 12.4898 3.74766L3.63281 11.4328C3.46868 11.5751 3.37438 11.7816 3.37438 11.9988C3.37438 12.216 3.46868 12.4226 3.63281 12.5648L12.5414 20.2969C12.5766 20.3273 12.6188 20.3438 12.6633 20.3438H14.8078C14.9813 20.3438 15.0609 20.1281 14.9297 20.0156L6.72422 12.8906H20.4375C20.5406 12.8906 20.625 12.8062 20.625 12.7031V11.2969C20.625 11.1938 20.5406 11.1094 20.4375 11.1094Z" fill="currentColor"/></svg>';

$hero_arrow_next_svg = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20.3672 11.4328L11.5125 3.74766C11.4445 3.68906 11.3578 3.65625 11.2664 3.65625H9.19219C9.01875 3.65625 8.93906 3.87188 9.07031 3.98438L17.2781 11.1094H3.5625C3.45938 11.1094 3.375 11.1938 3.375 11.2969V12.7031C3.375 12.8062 3.45938 12.8906 3.5625 12.8906H17.2758L9.06797 20.0156C8.93672 20.1305 9.01641 20.3438 9.18984 20.3438H11.3344C11.3789 20.3438 11.4234 20.3273 11.4563 20.2969L20.3672 12.5672C20.5314 12.4244 20.6256 12.2176 20.6256 12C20.6256 11.7824 20.5314 11.5756 20.3672 11.4328Z" fill="currentColor"/></svg>';

$hero_cta_icon_svg = static function (string $fill): string {
    return '<svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" viewBox="0 0 34 34" fill="none" aria-hidden="true">'
        . '<path d="M32 17C32 25.2843 25.2843 32 17 32V34C26.3888 34 34 26.3888 34 17H32ZM17 2C25.2843 2 32 8.71573 32 17H34C34 7.61116 26.3888 0 17 0V2ZM2 17C2 8.71573 8.71573 2 17 2V0C7.61116 0 0 7.61116 0 17H2ZM17 32C8.71573 32 2 25.2843 2 17H0C0 26.3888 7.61116 34 17 34V32ZM21.3333 17C21.3333 19.3932 19.3932 21.3333 17 21.3333V23.3333C20.4978 23.3333 23.3333 20.4978 23.3333 17H21.3333ZM17 12.6667C19.3932 12.6667 21.3333 14.6068 21.3333 17H23.3333C23.3333 13.5022 20.4978 10.6667 17 10.6667V12.6667ZM12.6667 17C12.6667 14.6068 14.6068 12.6667 17 12.6667V10.6667C13.5022 10.6667 10.6667 13.5022 10.6667 17H12.6667ZM17 21.3333C14.6068 21.3333 12.6667 19.3932 12.6667 17H10.6667C10.6667 20.4978 13.5022 23.3333 17 23.3333V21.3333Z" fill="' . esc_attr($fill) . '"/>'
        . '<path d="M27.5096 14.7855C27.4734 15.629 27.6744 16.3322 28.0488 16.8697C28.4217 17.405 28.9875 17.8065 29.7373 18.0132C29.9445 18.0683 30.1528 18.1231 30.3582 18.1752L30.3585 18.1753C31.046 18.3493 31.7807 18.5354 32.482 18.873C32.7356 12.6734 30.4025 7.7431 25.539 4.16582C23.6525 2.77875 21.4246 1.88478 18.9082 1.51652C18.9057 1.71265 18.8897 1.9094 18.8756 2.08348C18.8678 2.18013 18.865 2.21395 18.8623 2.24692C18.85 2.39525 18.8392 2.52667 18.8356 2.66115C18.7931 4.15317 19.6097 5.29799 20.9579 5.70402C21.1831 5.77239 21.4143 5.8279 21.6552 5.88574C21.9485 5.95733 22.1362 6.00355 22.3221 6.05251C25.0424 6.7799 26.7838 8.64063 27.4741 11.5077C27.6817 12.3607 27.6164 13.2362 27.5598 13.9953C27.5375 14.2911 27.5189 14.5391 27.5096 14.7855Z" fill="' . esc_attr($fill) . '"/>'
        . '</svg>';
};

$render_responsive_image = static function (
    array $desktop,
    array $mobile,
    string $class,
    string $alt = '',
    bool $eager = false,
    bool $decorative = false
): void {
    if ($desktop['url'] === '' && $mobile['url'] === '') {
        return;
    }

    $desktop_url = $desktop['url'] !== '' ? $desktop['url'] : $mobile['url'];
    $mobile_url  = $mobile['url'] !== '' ? $mobile['url'] : $desktop['url'];
    $alt_text    = $decorative
        ? ''
        : ($alt !== '' ? $alt : ($desktop['alt'] !== '' ? $desktop['alt'] : $mobile['alt']));

    if ($mobile_url !== '' && $mobile_url !== $desktop_url) : ?>
      <picture class="<?php echo esc_attr($class); ?>">
        <source media="(max-width: 1083px)" srcset="<?php echo esc_url($mobile_url); ?>" />
        <img
          src="<?php echo esc_url($desktop_url); ?>"
          alt="<?php echo esc_attr($alt_text); ?>"
          <?php echo $eager ? 'loading="eager" fetchpriority="high"' : 'loading="lazy"'; ?>
        />
      </picture>
    <?php else : ?>
      <img
        class="<?php echo esc_attr($class); ?>"
        src="<?php echo esc_url($desktop_url); ?>"
        alt="<?php echo esc_attr($alt_text); ?>"
        <?php echo $eager ? 'loading="eager" fetchpriority="high"' : 'loading="lazy"'; ?>
      />
    <?php endif;
};
?>
<section class="home-hero home-hero--slider <?php echo esc_attr($hero_layout_class); ?> relative z-[1] w-full overflow-hidden" aria-label="<?php esc_attr_e('Homepage hero', 'matrix-starter'); ?>" data-hero-layout="<?php echo esc_attr($hero_layout); ?>">
  <div
    id="home-hero-slider"
    class="home-hero-slider splide relative w-full"
    role="region"
    aria-roledescription="<?php esc_attr_e('carousel', 'matrix-starter'); ?>"
    aria-label="<?php esc_attr_e('Homepage hero slides', 'matrix-starter'); ?>"
  >
    <div class="splide__track" id="home-hero-slider-track">
      <div class="splide__list">
        <?php foreach ($slides as $index => $slide) : ?>
          <?php
          $left_pattern        = $slide['left_pattern'];
          $left_pattern_mobile = $slide['left_pattern_mobile'];
          $left_image          = $slide['left_image'];
          $left_image_mobile   = $slide['left_image_mobile'];
          $right_image         = $slide['right_image'];
          $right_image_mobile  = $slide['right_image_mobile'];
          $heading             = (string) ($slide['heading'] ?? '');
          $heading_mobile      = (string) ($slide['heading_mobile'] ?? '');
          $subtext             = (string) ($slide['subtext'] ?? '');
          $subtext_mobile      = (string) ($slide['subtext_mobile'] ?? '');
          $button_note         = (string) ($slide['button_note'] ?? '');
          $hero_link           = is_array($slide['hero_link'] ?? null) ? $slide['hero_link'] : [];
          $text_color          = (string) ($slide['text_color'] ?? 'white');
          $button_style        = (string) ($slide['button_style'] ?? 'white');
          $button_hover_style  = (string) ($slide['button_hover_style'] ?? 'default');
          $button_icon         = ! empty($slide['button_icon']);
          $body_highlight      = ! empty($slide['body_highlight']);
          $text_class          = $text_color === 'black' ? 'home-hero-slide__text--black' : 'home-hero-slide__text--white';
          $slide_modifier      = function_exists('matrix_rd_home_hero_slide_modifier_classes')
              ? matrix_rd_home_hero_slide_modifier_classes($slide)
              : '';
          $cta_hover_class     = in_array($button_hover_style, ['white', 'black', 'yellow'], true)
              ? ' home-hero-slide__cta--hover-' . $button_hover_style
              : '';
          $hide_title_on_mobile = $heading_mobile !== '';
          $desktop_subtext_only = $subtext !== '' && $subtext_mobile !== '';
          $show_shared_subtext  = $subtext !== '' && $subtext_mobile === '';
          $overlay_style       = function_exists('matrix_rd_home_hero_overlay_style')
              ? matrix_rd_home_hero_overlay_style($slide)
              : '';
          $title_image_alt     = $left_image['alt'] !== '' ? $left_image['alt'] : $heading;
          $cta_icon_fill       = function_exists('matrix_rd_home_hero_cta_icon_fill')
              ? matrix_rd_home_hero_cta_icon_fill($button_style)
              : ($button_style === 'black' ? '#ffffff' : '#000000');
          $render_body         = static function (string $content) use ($body_highlight): string {
              return function_exists('matrix_rd_home_hero_render_body_html')
                  ? matrix_rd_home_hero_render_body_html($content, $body_highlight)
                  : wp_kses_post($content);
          };
          $body_class          = 'home-hero-slide__body ' . $text_class . ($body_highlight ? ' home-hero-slide__body--highlight' : '');
          ?>
          <div class="splide__slide" aria-label="<?php echo esc_attr(sprintf(/* translators: %1$d slide number, %2$d total slides */ __('Slide %1$d of %2$d', 'matrix-starter'), $index + 1, $slide_count)); ?>">
            <div class="home-hero-slide<?php echo esc_attr($slide_modifier); ?>">
              <div class="home-hero-slide__left">
                <?php if ($left_pattern['url'] !== '' || $left_pattern_mobile['url'] !== '') : ?>
                  <div class="home-hero-slide__pattern" aria-hidden="true">
                    <?php
                    $render_responsive_image(
                        $left_pattern,
                        $left_pattern_mobile,
                        'home-hero-slide__pattern-img',
                        '',
                        false,
                        true
                    );
                    ?>
                  </div>
                <?php endif; ?>

                <div class="home-hero-slide__overlay"<?php echo $overlay_style !== '' ? ' style="' . esc_attr($overlay_style) . '"' : ''; ?>>
                  <div class="home-hero-slide__content">
                    <?php if ($hero_layout === 'layout_2' && ($left_image['url'] !== '' || $left_image_mobile['url'] !== '')) : ?>
                      <div class="home-hero-slide__title-image<?php echo $hide_title_on_mobile ? ' home-hero-slide__title-image--desktop-only' : ''; ?>">
                        <?php
                        $render_responsive_image(
                            $left_image,
                            $left_image_mobile,
                            'home-hero-slide__title-image-img',
                            $title_image_alt
                        );
                        ?>
                      </div>
                    <?php endif; ?>

                    <?php if ($heading !== '') : ?>
                      <h2 class="home-hero-slide__heading <?php echo esc_attr($text_class); ?><?php echo $heading_mobile !== '' ? ' home-hero-slide__heading--desktop-only' : ''; ?>">
                        <?php echo esc_html($heading); ?>
                      </h2>
                    <?php endif; ?>

                    <?php if ($heading_mobile !== '') : ?>
                      <h2 class="home-hero-slide__heading home-hero-slide__heading--mobile-only <?php echo esc_attr($text_class); ?>">
                        <?php echo esc_html($heading_mobile); ?>
                      </h2>
                    <?php endif; ?>

                    <?php if ($show_shared_subtext) : ?>
                      <div class="<?php echo esc_attr($body_class); ?>">
                        <?php echo $render_body($subtext); ?>
                      </div>
                    <?php endif; ?>

                    <?php if ($desktop_subtext_only) : ?>
                      <div class="<?php echo esc_attr($body_class . ' home-hero-slide__body--desktop-only'); ?>">
                        <?php echo $render_body($subtext); ?>
                      </div>
                    <?php endif; ?>

                    <?php if ($subtext_mobile !== '') : ?>
                      <div class="<?php echo esc_attr($body_class . ' home-hero-slide__body--mobile-only'); ?>">
                        <?php echo $render_body($subtext_mobile); ?>
                      </div>
                    <?php endif; ?>

                    <?php if (! empty($hero_link['url'])) : ?>
                      <div class="home-hero-slide__cta-wrap">
                        <a
                          class="home-hero-slide__cta home-hero-slide__cta--<?php echo esc_attr($button_style); ?><?php echo esc_attr($cta_hover_class); ?>"
                          href="<?php echo esc_url($hero_link['url']); ?>"
                          <?php echo ! empty($hero_link['target']) ? 'target="' . esc_attr($hero_link['target']) . '"' : ''; ?>
                        >
                          <?php if ($button_icon) : ?>
                            <?php echo $hero_cta_icon_svg($cta_icon_fill); ?>
                          <?php endif; ?>
                          <?php echo esc_html($hero_link['title'] ?? __('Order fresh box now', 'matrix-starter')); ?>
                        </a>
                      </div>
                    <?php endif; ?>

                    <?php if ($button_note !== '') : ?>
                      <div class="home-hero-slide__body home-hero-slide__button-note <?php echo esc_attr($text_class); ?>">
                        <?php echo wp_kses_post($button_note); ?>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>

              <div class="home-hero-slide__right">
                <div class="home-hero-slide__right-media" aria-hidden="true">
                  <?php if ($right_image['url'] !== '' || $right_image_mobile['url'] !== '') : ?>
                    <?php
                    $render_responsive_image(
                        $right_image,
                        $right_image_mobile,
                        'home-hero-slide__right-layer',
                        '',
                        $index === 0,
                        true
                    );
                    ?>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <?php if ($slide_count > 1) : ?>
      <div class="home-hero-slider__controls">
        <div class="splide__arrows home-hero-slider__arrows">
          <button
            class="splide__arrow splide__arrow--prev"
            type="button"
            aria-label="<?php esc_attr_e('Previous slide', 'matrix-starter'); ?>"
            aria-controls="home-hero-slider-track"
          >
            <?php echo $hero_arrow_prev_svg; ?>
            <span class="sr-only"><?php esc_html_e('Previous slide', 'matrix-starter'); ?></span>
          </button>
          <button
            class="splide__arrow splide__arrow--next"
            type="button"
            aria-label="<?php esc_attr_e('Next slide', 'matrix-starter'); ?>"
            aria-controls="home-hero-slider-track"
          >
            <?php echo $hero_arrow_next_svg; ?>
            <span class="sr-only"><?php esc_html_e('Next slide', 'matrix-starter'); ?></span>
          </button>
        </div>
        <ul
          class="splide__pagination home-hero-slider__pagination"
          id="home-hero-slider-pagination"
          role="tablist"
          aria-label="<?php esc_attr_e('Hero slide pagination', 'matrix-starter'); ?>"
        ></ul>
      </div>
    <?php endif; ?>
  </div>
</section>
