<?php
$content_001_heading_tag = get_sub_field('content_001_heading_tag') ?: 'h2';
$content_001_heading_text = get_sub_field('content_001_heading_text');
$content_001_paragraph_text = get_sub_field('content_001_paragraph_text');
$content_001_image_id = get_sub_field('content_001_image');
$content_001_image_url = wp_get_attachment_url($content_001_image_id);
$content_001_image_alt = get_post_meta($content_001_image_id, '_wp_attachment_image_alt', true);
?>

<section class="flex flex-col items-center self-stretch justify-center py-8 bg-white text-text-primary lg:py-20">
  <div class="flex flex-col items-center max-w-full p-10 md:flex-row bg-primary w-container max-md:px-5 rounded-custom">
    <article class="flex flex-col flex-1 shrink justify-center py-20 pr-20 pl-14 basis-0 w-full md:w-50%] lg:w-[35%] max-md:px-5 max-md:max-w-full">
      <?php if ($content_001_heading_text): ?>
        <<?php echo esc_html($content_001_heading_tag); ?> class="text-3xl font-semibold leading-10">
          <?php echo esc_html($content_001_heading_text); ?>
        </<?php echo esc_html($content_001_heading_tag); ?>>
      <?php endif; ?>

      <?php if ($content_001_paragraph_text): ?>
        <div class="mt-6 text-lg leading-7">
          <?php echo $content_001_paragraph_text; // WYSIWYG content 
          ?>
        </div>
      <?php endif; ?>
    </article>
    <div class="min-w-[240px] w-full md:w-[50%] lg:w-[65%] flex items-center">
      <?php
      // Check if an image URL is provided, fallback to placeholder if not
      if ($content_001_image_url): ?>
        <img src="<?php echo esc_url($content_001_image_url); ?>"
          alt="<?php echo esc_attr($content_001_image_alt); ?>"
          class="object-cover max-sm:min-h-[300px] rounded-custom" />
      <?php else: ?>
        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/placeholder.png'); ?>"
          alt="Placeholder Image"
          class="object-cover max-sm:min-h-[300px] rounded-custom" />
      <?php endif; ?>
    </div>
  </div>
</section>