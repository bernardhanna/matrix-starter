<?php
$section_id = 'wysiwyg-' . wp_generate_uuid4();
$text_content = get_sub_field('text_content');

$padding_classes = [];
if (have_rows('padding_settings')) {
  while (have_rows('padding_settings')) {
    the_row();
    $screen = get_sub_field('screen_size');
    $pt = get_sub_field('padding_top');
    $pb = get_sub_field('padding_bottom');
    $padding_classes[] = "{$screen}:pt-[{$pt}rem]";
    $padding_classes[] = "{$screen}:pb-[{$pb}rem]";
  }
}
?>

<section
  id="<?php echo esc_attr($section_id); ?>"
  class="relative flex overflow-hidden bg-white font-montserrat"
  role="region"
  aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
>
  <div class="<?php echo esc_attr(matrix_content_container_classes()); ?> <?php echo esc_attr(implode(' ', $padding_classes)); ?>">
    <h2 id="<?php echo esc_attr($section_id); ?>-heading" class="sr-only"><?php esc_html_e('Content', 'matrix-starter'); ?></h2>
    <div class="theme-prose wp_editor">
      <div class="entry-content">
        <?php if ($text_content) : ?>
          <?php echo wp_kses_post($text_content); ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
