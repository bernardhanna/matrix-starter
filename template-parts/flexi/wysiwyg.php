<?php
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

<section class="flex overflow-hidden relative wp_editor">
  <div class="w-full mx-auto max-w-[1095px] flex flex-col md:flex-row-reverse items-center justify-between max-lg:px-5  max-xxl:px-5 pb-20">
   
      <div class="relative">
        <?php if ($text_content): ?>
          <?= wp_kses_post($text_content); ?>
        <?php endif; ?>
      </div>

  </div>
</section>

