<?php
$section_id = 'content-002-' . wp_generate_uuid4();
$heading = get_sub_field('heading');
$heading_tag = get_sub_field('heading_tag');
$body = get_sub_field('body');
$content_button = get_sub_field('content_button');
$image_id = (int) get_sub_field('image');

$allowed_heading_tags = ['h2', 'h3', 'h4'];
if (!in_array($heading_tag, $allowed_heading_tags, true)) {
    $heading_tag = 'h2';
}

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
?>

<section
  id="<?php echo esc_attr($section_id); ?>"
  class="relative flex overflow-hidden bg-white font-montserrat"
  role="region"
  aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
>
  <div class="flex flex-col items-center w-full mx-auto max-w-container max-lg:px-5 <?php echo esc_attr(implode(' ', $padding_classes)); ?>">
    <div class="grid w-full gap-8 lg:grid-cols-2 lg:items-center">
      <div>
        <?php if ($heading) : ?>
          <<?php echo esc_attr($heading_tag); ?> id="<?php echo esc_attr($section_id); ?>-heading" class="font-montserrat text-[#003b65]">
            <?php echo esc_html($heading); ?>
          </<?php echo esc_attr($heading_tag); ?>>
        <?php endif; ?>
        <?php if ($body) : ?>
          <div class="theme-prose wp_editor mt-4"><?php echo wp_kses_post($body); ?></div>
        <?php endif; ?>
        <?php if (is_array($content_button) && !empty($content_button['url']) && !empty($content_button['title'])) : ?>
          <?php $button_class = 'flexi-cta-' . wp_generate_uuid4(); ?>
          <a
            href="<?php echo esc_url($content_button['url']); ?>"
            target="<?php echo esc_attr($content_button['target'] ?? '_self'); ?>"
            <?php if (($content_button['target'] ?? '') === '_blank') : ?>rel="noopener noreferrer"<?php endif; ?>
            class="<?php echo esc_attr(matrix_btn_classes('primary', ['full_mobile' => true])); ?> <?php echo esc_attr($button_class); ?> mt-6"
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
      <?php if ($image_id) : ?>
        <div>
          <?php echo wp_get_attachment_image($image_id, 'large', false, [
            'class' => 'w-full h-auto rounded-[16px]',
            'alt' => get_post_meta($image_id, '_wp_attachment_image_alt', true) ?: esc_attr($heading ?: 'Content image'),
          ]); ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>
