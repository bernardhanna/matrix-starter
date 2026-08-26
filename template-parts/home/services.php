<?php
/**
 * Home services (Delivery / Collection) — legacy services.blade.php.
 */
$post_id = (int) get_queried_object_id();
if (! $post_id && is_front_page()) {
    $post_id = (int) get_option('page_on_front');
}

$services = matrix_rd_acf_repeater_rows(
    'services_list',
    ['image', 'video', 'title', 'description'],
    $post_id ?: null
);

if ($services === []) {
    return;
}

$is_box_page = is_page('donut-box');
$pb_class    = $is_box_page ? 'pb-8 max-md:pb-4' : 'pb-20 max-md:pb-12';

// On the donut-box page show delivery/collection as a responsive grid (2-up, 1-up
// on very narrow screens). Desktop keeps the legacy side-by-side row.
$inner_class = $is_box_page
    ? 'rd-box-services-grid items-start w-full'
    : 'items-center w-full services-slick lg:flex lg:justify-center lg:flex-row';
?>
<style>
  .slick-initialized .slick-slide { display: flex; }
  .slick-track { display: flex; }
</style>
<section class="<?php echo esc_attr(matrix_rd_section_shell_classes($is_box_page ? 'services rd-box-services-section' : 'services')); ?>">
  <div class="<?php echo esc_attr(matrix_rd_section_inner_classes('px-8')); ?>">
  <div class="relative w-full pt-16 <?php echo esc_attr($pb_class); ?>">
    <div class="lg:flex lg:justify-center lg:w-full">
      <div class="<?php echo esc_attr($inner_class); ?>">
        <?php foreach ($services as $service) :
            $image = matrix_rd_acf_image($service['image'] ?? null, '', 'medium_large');
            $video = matrix_rd_acf_file_url($service['video'] ?? null);
            $title = isset($service['title']) ? (string) $service['title'] : '';
            $desc  = isset($service['description']) ? (string) $service['description'] : '';
            if ($image['url'] === '') {
                continue;
            }
            ?>
        <div
          class="relative flex flex-col items-center justify-center w-auto h-auto item lg:flex-row lg:justify-start max-w-750 lg:w-1/2"
          x-data="{ isHovered: false }"
        >
          <div
            class="relative w-[150px] h-[150px] xxl:w-[200px] xxl:h-[200px] cursor-pointer"
            @mouseenter="isHovered = true; $refs.videoElement && $refs.videoElement.play().catch(() => {})"
            @mouseleave="isHovered = false; $refs.videoElement && $refs.videoElement.pause()"
          >
            <img
              class="absolute top-0 left-0 z-10 w-full h-full object-contain transition-opacity duration-300 opacity-100 pointer-events-none"
              :class="{ 'opacity-0': isHovered, 'opacity-100': !isHovered }"
              src="<?php echo esc_url($image['url']); ?>"
              alt="<?php echo esc_attr($image['alt']); ?>"
              loading="lazy"
              decoding="async"
            />
            <?php if ($video !== '') : ?>
            <video
              class="absolute top-0 left-0 z-0 w-full h-full object-contain transition-opacity duration-300 opacity-0 pointer-events-none"
              :class="{ 'opacity-0': !isHovered, 'opacity-100': isHovered }"
              preload="metadata"
              x-ref="videoElement"
              muted
              loop
              playsinline
              aria-hidden="true"
            >
              <source src="<?php echo esc_url($video); ?>" type="video/mp4" />
              <track
                kind="captions"
                srclang="en"
                label="<?php esc_attr_e('Captions', 'matrix-starter'); ?>"
                src="<?php echo esc_url(get_template_directory_uri() . '/assets/captions/decorative-muted.vtt'); ?>"
              />
            </video>
            <?php endif; ?>
          </div>
          <div class="flex flex-col items-center justify-center px-4 text-center lg:items-start lg:flex-start lg:text-right lg:h-full">
            <span
              class="relative text-center top-2 text-font-28 font-reg420 text-black-full lg:text-start"
              :class="{ 'text-yellow-primary': isHovered, 'shadow-hover': isHovered }"
            ><?php echo esc_html($title); ?></span>
            <div
              class="w-[8.1875rem] h-[0.1875rem] transition-opacity duration-300 opacity-0"
              :class="{ 'bg-black-full': isHovered, 'opacity-0': !isHovered }"
            ></div>
            <p class="w-full mt-4 text-center lg:mt-6 reg-font font-lighter font-laca md:text-start max-w-max-478">
              <?php echo nl2br(esc_html($desc)); ?>
            </p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  </div>
</section>
