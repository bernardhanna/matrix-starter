<?php
$section_id = 'cta-row-' . wp_generate_uuid4();
$heading = get_sub_field('heading');
$intro = get_sub_field('intro');
$content_button = get_sub_field('content_button');
$background_color = get_sub_field('background_color');

$padding_classes = [];
if (have_rows('padding_settings')) {
  while (have_rows('padding_settings')) {
    the_row();
    $screen = get_sub_field('screen_size');
    $pt = get_sub_field('padding_top');
    $pb = get_sub_field('padding_bottom');
    if ($screen && $pt !== null && $pt !== '') {
      $padding_classes[] = "{$screen}:pt-[{$pt}rem]";
    }
    if ($screen && $pb !== null && $pb !== '') {
      $padding_classes[] = "{$screen}:pb-[{$pb}rem]";
    }
  }
}

$section_style = $background_color ? 'background-color:' . esc_attr($background_color) . ';' : '';
?>

<section
  id="<?php echo esc_attr($section_id); ?>"
  class="relative flex overflow-hidden font-montserrat"
  role="region"
  aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
  <?php if ($section_style) : ?>style="<?php echo esc_attr($section_style); ?>"<?php endif; ?>
>
  <div class="flex flex-col items-center w-full mx-auto max-w-container max-lg:px-5 text-center <?php echo esc_attr(implode(' ', $padding_classes)); ?>">
    <?php if ($heading) : ?>
      <h2 id="<?php echo esc_attr($section_id); ?>-heading" class="font-montserrat text-[#003b65]"><?php echo esc_html($heading); ?></h2>
    <?php endif; ?>
    <?php if ($intro) : ?>
      <p class="mt-4 font-comfortaa text-[18px] leading-[27px] text-[#00263E]"><?php echo esc_html($intro); ?></p>
    <?php endif; ?>
    <?php if (is_array($content_button) && !empty($content_button['url']) && !empty($content_button['title'])) : ?>
      <?php $button_class = 'flexi-cta-' . wp_generate_uuid4(); ?>
      <a
        href="<?php echo esc_url($content_button['url']); ?>"
        target="<?php echo esc_attr($content_button['target'] ?? '_self'); ?>"
        <?php if (($content_button['target'] ?? '') === '_blank') : ?>rel="noopener noreferrer"<?php endif; ?>
        class="<?php echo esc_attr(matrix_btn_classes('primary')); ?> <?php echo esc_attr($button_class); ?> mt-6"
        aria-label="<?php echo esc_attr($content_button['title']); ?>"
      >
        <?php echo esc_html($content_button['title']); ?>
      </a>
      <style>
      #<?php echo esc_attr($section_id); ?> a.<?php echo esc_attr($button_class); ?>:focus-visible {
        outline: none;
        box-shadow: 0 0 0 3px var(--Turquoise-500, #1C959B);
      }
      </style>
    <?php endif; ?>
  </div>
</section>
