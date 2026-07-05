<?php
$section_id = 'faq-' . wp_generate_uuid4();
$heading = get_sub_field('heading');

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
    <?php if ($heading) : ?>
      <h2 id="<?php echo esc_attr($section_id); ?>-heading" class="w-full font-montserrat text-[#003b65]"><?php echo esc_html($heading); ?></h2>
    <?php endif; ?>
    <?php if (have_rows('faq_items')) : ?>
      <dl class="mt-8 w-full space-y-6">
        <?php while (have_rows('faq_items')) : the_row();
          $question = get_sub_field('question');
          $answer = get_sub_field('answer');
          if (!$question) {
            continue;
          }
          $item_id = $section_id . '-item-' . get_row_index();
        ?>
          <div>
            <dt class="font-montserrat font-semibold text-[#003b65]" id="<?php echo esc_attr($item_id); ?>-q"><?php echo esc_html($question); ?></dt>
            <?php if ($answer) : ?>
              <dd class="theme-prose wp_editor mt-2" aria-labelledby="<?php echo esc_attr($item_id); ?>-q"><?php echo wp_kses_post($answer); ?></dd>
            <?php endif; ?>
          </div>
        <?php endwhile; ?>
      </dl>
    <?php endif; ?>
  </div>
</section>
