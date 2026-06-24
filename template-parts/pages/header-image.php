<?php
/**
 * Full-width header image below page content (ACF header_image).
 */
$image = get_field('header_image');
$url   = matrix_rd_acf_image_url($image);
if ($url === '' && is_string($image) && str_starts_with($image, '/')) {
    $url = home_url($image);
}
if ($url === '') {
    return;
}
$alt = matrix_rd_attachment_alt($url, '');
?>
<section class="relative flex flex-col items-center justify-center w-full px-4 md:py-8 m-auto flexi-image">
  <div class="relative w-full px-4 pb-10 ml-auto mr-auto max-w-max-1038">
    <img class="oldimg w-full object-contain rounded-[10px] laptop:border-12 laptop:border-white" src="<?php echo esc_url($url); ?>" alt="<?php echo esc_attr($alt); ?>" />
  </div>
</section>
