<?php
$section_id = 'partners-' . wp_rand(1000, 999999);

$heading_text = (string) get_sub_field('heading_text');
$heading_tag  = (string) get_sub_field('heading_tag');

$subheading_text = (string) get_sub_field('subheading_text');
$subheading_tag  = (string) get_sub_field('subheading_tag');

$logos = get_sub_field('logos');
$enable_logo_slider = (bool) get_sub_field('enable_logo_slider');
$logo_visual_style = (string) get_sub_field('logo_visual_style');
if ($logo_visual_style === '') {
  $logo_visual_style = 'original';
}
$show_slider_arrows = (bool) get_sub_field('show_slider_arrows');
$show_mobile_dots = (bool) get_sub_field('show_mobile_dots');
$slider_autoplay = (bool) get_sub_field('slider_autoplay');
$slider_autoplay_speed = (int) (get_sub_field('slider_autoplay_speed') ?: 3000);
if ($slider_autoplay_speed < 1000) {
  $slider_autoplay_speed = 1000;
}
$slider_start_delay = (int) (get_sub_field('slider_start_delay') ?: 500);
if ($slider_start_delay < 0) {
  $slider_start_delay = 0;
}
$slider_transition_speed = (int) (get_sub_field('slider_transition_speed') ?: 450);
if ($slider_transition_speed < 100) {
  $slider_transition_speed = 100;
}
$slider_slides_desktop = (int) (get_sub_field('slider_slides_desktop') ?: 5);
if ($slider_slides_desktop < 1) {
  $slider_slides_desktop = 1;
}

$background_color = (string) get_sub_field('background_color');

$allowed_heading_tags = ['h1','h2','h3','h4','h5','h6','span','p'];
if (!in_array($heading_tag, $allowed_heading_tags, true)) {
  $heading_tag = 'h3';
}
if (!in_array($subheading_tag, $allowed_heading_tags, true)) {
  $subheading_tag = 'h4';
}

$padding_classes = [];
if (have_rows('padding_settings')) {
  while (have_rows('padding_settings')) {
    the_row();
    $screen_size    = (string) get_sub_field('screen_size');
    $padding_top    = get_sub_field('padding_top');
    $padding_bottom = get_sub_field('padding_bottom');

    if ($screen_size !== '' && $padding_top !== null && $padding_top !== '') {
      $padding_classes[] = "{$screen_size}:pt-[{$padding_top}rem]";
    }
    if ($screen_size !== '' && $padding_bottom !== null && $padding_bottom !== '') {
      $padding_classes[] = "{$screen_size}:pb-[{$padding_bottom}rem]";
    }
  }
}
$padding_class_string = implode(' ', $padding_classes);

$heading_id = $section_id . '-heading';

$logo_image_class = 'object-contain';
if ($logo_visual_style === 'muted') {
  $logo_image_class .= ' mix-blend-luminosity';
} elseif ($logo_visual_style === 'white') {
  // Best-effort white conversion: invert to white and screen-blend to suppress boxed backgrounds.
  $logo_image_class .= ' brightness-0 invert mix-blend-screen';
}
?>

<section
  id="<?php echo esc_attr($section_id); ?>"
  class="flex relative overflow-hidden"
  role="region"
  aria-labelledby="<?php echo esc_attr($heading_id); ?>"
  style="background-color: <?php echo esc_attr($background_color ?: '#009DE6'); ?>;"
>
  <div class="flex flex-col items-center w-full mx-auto max-w-container pt-5 pb-5 max-lg:px-5 <?php echo esc_attr($padding_class_string); ?>">
    <div class="w-full max-w-[64rem] mx-auto">
      <div class="flex flex-col gap-8 self-center pt-0 pr-5 pb-6 pl-5 mx-auto w-full h-auto md:justify-start md:items-center max-xl:px-5">

        <div class="flex flex-col md:justify-start md:items-start gap-4 pr-4 pl-4 rounded-[0.5rem] w-full self-start mx-auto max-xl:px-5">
          <<?php echo esc_html($heading_tag); ?>
            id="<?php echo esc_attr($heading_id); ?>"
            class="break-words text-center text-[1.875rem] font-[700] leading-[2.375rem] text-white font-['Public Sans'] w-full"
          >
            <?php echo esc_html($heading_text ?: 'A growing global movement'); ?>
          </<?php echo esc_html($heading_tag); ?>>

          <?php if ($subheading_text !== '') : ?>
            <<?php echo esc_html($subheading_tag); ?>
              class="break-words text-center text-[1.25rem] font-[400] leading-[1.625rem] text-[#fcfcfd] font-['Public Sans'] w-full"
            >
              <?php echo esc_html($subheading_text); ?>
            </<?php echo esc_html($subheading_tag); ?>>
          <?php endif; ?>
        </div>

        <?php if (!empty($logos) && is_array($logos)) : ?>
          <?php
          $logo_items = [];
          foreach ($logos as $logo_item) {
            $logo_image = $logo_item['logo_image'] ?? null;
            $logo_link  = $logo_item['logo_link'] ?? null;

            $logo_url = '';
            $logo_id  = 0;

            if (is_array($logo_image)) {
              $logo_url = (string) ($logo_image['url'] ?? '');
              $logo_id  = (int) ($logo_image['ID'] ?? 0);
            }
            if ($logo_url === '') continue;

            $logo_alt = '';
            $logo_title = '';
            if ($logo_id > 0) {
              $logo_alt = (string) get_post_meta($logo_id, '_wp_attachment_image_alt', true);
              $logo_title = (string) get_the_title($logo_id);
            }
            if ($logo_alt === '') $logo_alt = $logo_title !== '' ? $logo_title : 'Partner logo';
            if ($logo_title === '') $logo_title = $logo_alt;

            $logo_items[] = [
              'url' => $logo_url,
              'alt' => $logo_alt,
              'title' => $logo_title,
              'link' => $logo_link,
              'card_style' => (string) ($logo_item['logo_card_style'] ?? 'white'),
            ];
          }
          ?>

          <?php if (!$enable_logo_slider) : ?>
            <div class="flex flex-col gap-6 self-center pr-4 pl-4 w-full h-auto md:flex-row md:justify-between md:items-center max-xl:px-5" role="list" aria-label="Partner logos">
              <?php foreach ($logo_items as $logo_data) : ?>
                <?php
                $card_style = $logo_data['card_style'] ?? 'white';
                $card_class = 'bg-white';
                if ($card_style === 'translucent') {
                  $card_class = 'bg-white/5';
                } elseif ($card_style === 'transparent') {
                  $card_class = 'bg-transparent';
                }
                ?>
                <div role="listitem" class="flex justify-center md:justify-start">
                  <?php
                  $is_white_logo_mode = $logo_visual_style === 'white';
                  $non_slider_card_class = $is_white_logo_mode ? 'bg-transparent px-0' : $card_class;
                  ?>
                  <?php if (is_array($logo_data['link']) && !empty($logo_data['link']['url'])) : ?>
                    <a href="<?php echo esc_url($logo_data['link']['url']); ?>" target="<?php echo esc_attr($logo_data['link']['target'] ?? '_self'); ?>" class="inline-flex btn rounded-[10px] <?php echo esc_attr($non_slider_card_class); ?>" aria-label="<?php echo esc_attr($logo_data['link']['title'] ?: $logo_data['alt']); ?>">
                      <img src="<?php echo esc_url($logo_data['url']); ?>" alt="<?php echo esc_attr($logo_data['alt']); ?>" title="<?php echo esc_attr($logo_data['title']); ?>" loading="lazy" decoding="async" class="object-cover max-w-full shrink-0 max-md:h-auto max-md:object-contain <?php echo esc_attr($logo_image_class); ?>" />
                    </a>
                  <?php else : ?>
                    <div class="inline-flex rounded-[10px] <?php echo esc_attr($non_slider_card_class); ?>">
                      <img src="<?php echo esc_url($logo_data['url']); ?>" alt="<?php echo esc_attr($logo_data['alt']); ?>" title="<?php echo esc_attr($logo_data['title']); ?>" loading="lazy" decoding="async" class="object-cover max-w-full shrink-0 max-md:h-auto max-md:object-contain <?php echo esc_attr($logo_image_class); ?>" />
                    </div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else : ?>
            <div class="partners-slider-wrap relative w-full px-4 max-xl:px-5">
              <?php if ($show_slider_arrows) : ?>
                <button type="button" class="group partners-prev absolute left-2 top-1/2 z-20 hidden h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-white shadow-[0_8px_24px_rgba(0,0,0,0.12)] transition hover:opacity-100 hover:bg-[#009DE6] xl:-left-4 md:flex" aria-label="Previous partners">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M15 8H1M1 8L8 15M1 8L8 1" class="transition-colors duration-200 group-hover:stroke-white" stroke="#1D2939" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                </button>
                <button type="button" class="group partners-next absolute right-2 top-1/2 z-20 hidden h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-white shadow-[0_8px_24px_rgba(0,0,0,0.12)] transition hover:opacity-100 hover:bg-[#009DE6] xl:-right-4 md:flex" aria-label="Next partners">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M5 12H19M19 12L12 5M19 12L12 19" class="transition-colors duration-200 group-hover:stroke-white" stroke="#1D2939" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                </button>
              <?php endif; ?>

              <div>
                <div
                  class="js-partners-slider"
                  data-autoplay="<?php echo $slider_autoplay ? '1' : '0'; ?>"
                  data-autoplay-speed="<?php echo esc_attr((string) $slider_autoplay_speed); ?>"
                  data-start-delay="<?php echo esc_attr((string) $slider_start_delay); ?>"
                  data-transition-speed="<?php echo esc_attr((string) $slider_transition_speed); ?>"
                  data-slides="<?php echo esc_attr((string) $slider_slides_desktop); ?>"
                  role="list"
                  aria-label="Partner logos slider"
                >
                  <?php foreach ($logo_items as $logo_data) : ?>
                    <?php
                    $card_style = $logo_data['card_style'] ?? 'white';
                    $card_class = 'bg-white';
                    if ($card_style === 'translucent') {
                      $card_class = 'bg-white/5';
                    } elseif ($card_style === 'transparent') {
                      $card_class = 'bg-transparent';
                    }
                    ?>
                    <div class="px-2 partners-slide">
                      <?php
                      $is_white_logo_mode = $logo_visual_style === 'white';
                      $slider_card_class = $is_white_logo_mode ? 'bg-transparent px-0' : $card_class;
                      ?>
                      <div role="listitem" class="flex h-[100px] items-center justify-center rounded-[10px] <?php echo esc_attr($slider_card_class); ?>">
                        <?php if (is_array($logo_data['link']) && !empty($logo_data['link']['url'])) : ?>
                          <a href="<?php echo esc_url($logo_data['link']['url']); ?>" target="<?php echo esc_attr($logo_data['link']['target'] ?? '_self'); ?>" class="inline-flex btn h-[82px] w-full items-center justify-center" aria-label="<?php echo esc_attr($logo_data['link']['title'] ?: $logo_data['alt']); ?>">
                            <img src="<?php echo esc_url($logo_data['url']); ?>" alt="<?php echo esc_attr($logo_data['alt']); ?>" title="<?php echo esc_attr($logo_data['title']); ?>" loading="lazy" decoding="async" class="h-full w-full <?php echo esc_attr($logo_image_class); ?>" />
                          </a>
                        <?php else : ?>
                          <img src="<?php echo esc_url($logo_data['url']); ?>" alt="<?php echo esc_attr($logo_data['alt']); ?>" title="<?php echo esc_attr($logo_data['title']); ?>" loading="lazy" decoding="async" class="h-[82px] w-full <?php echo esc_attr($logo_image_class); ?>" />
                        <?php endif; ?>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>

            <style>
              #<?php echo esc_attr($section_id); ?> .js-partners-slider .slick-list {
                overflow: hidden !important;
              }
              #<?php echo esc_attr($section_id); ?> .js-partners-slider .slick-track {
                overflow: hidden !important;
              }
            </style>

            <script>
            document.addEventListener('DOMContentLoaded', function () {
              var section = document.getElementById('<?php echo esc_js($section_id); ?>');
              if (!section) return;
              if (typeof jQuery === 'undefined' || typeof jQuery.fn.slick === 'undefined') return;

              var $section = jQuery(section);
              var $slider = $section.find('.js-partners-slider');
              if (!$slider.length || $slider.hasClass('slick-initialized')) return;

              var autoplay = $slider.attr('data-autoplay') === '1';
              var autoplaySpeed = parseInt($slider.attr('data-autoplay-speed') || '3000', 10);
              var startDelay = parseInt($slider.attr('data-start-delay') || '500', 10);
              var transitionSpeed = parseInt($slider.attr('data-transition-speed') || '450', 10);
              var slidesDesktop = parseInt($slider.attr('data-slides') || '5', 10);
              var shouldDelayStart = autoplay && !isNaN(startDelay) && startDelay > 0;

              $slider.slick({
                slidesToShow: Math.max(1, Math.min(4, slidesDesktop)),
                slidesToScroll: 1,
                autoplay: shouldDelayStart ? false : autoplay,
                autoplaySpeed: isNaN(autoplaySpeed) ? 3000 : autoplaySpeed,
                speed: isNaN(transitionSpeed) ? 450 : transitionSpeed,
                infinite: true,
                arrows: <?php echo $show_slider_arrows ? 'true' : 'false'; ?>,
                prevArrow: $section.find('.partners-prev'),
                nextArrow: $section.find('.partners-next'),
                dots: <?php echo $show_mobile_dots ? 'true' : 'false'; ?>,
                adaptiveHeight: false,
                swipe: true,
                draggable: true,
                touchMove: true,
                responsive: [
                  { breakpoint: 1280, settings: { slidesToShow: 3 } },
                  { breakpoint: 1024, settings: { slidesToShow: 2 } },
                  { breakpoint: 768, settings: { slidesToShow: 1 } }
                ]
              });

              if (shouldDelayStart) {
                window.setTimeout(function () {
                  if (!$slider.hasClass('slick-initialized')) return;
                  $slider.slick('slickSetOption', 'autoplay', true, true);
                  $slider.slick('slickPlay');
                }, startDelay);
              }
            });
            </script>
          <?php endif; ?>
        <?php endif; ?>

      </div>
    </div>
  </div>
</section>