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

// On the donut-box page show the services as a 2-column grid at every width
// (the Slick carousel JS that 1-ups this below 1084px only loads on the home /
// about pages, so there's nothing to conflict with here). The `lg:flex` rules
// keep the existing desktop row layout — `grid` only governs below `lg`.
$inner_class = $is_box_page
    ? 'grid grid-cols-2 gap-x-4 gap-y-6 items-start w-full mx-auto lg:flex lg:items-center lg:justify-center lg:flex-row lg:max-w-max-1514'
    : 'items-center w-full mx-auto services-slick lg:flex lg:justify-center lg:flex-row lg:max-w-max-1514';
?>
<style>
  .slick-initialized .slick-slide { display: flex; }
  .slick-track { display: flex; }
</style>
<section class="px-8 services">
  <div class="relative w-full pt-16 <?php echo esc_attr($pb_class); ?>">
    <div class="lg:flex lg:justify-center lg:w-full">
      <div class="<?php echo esc_attr($inner_class); ?>">
        <?php foreach ($services as $service) :
            $image = matrix_rd_acf_image($service['image'] ?? null);
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
            @mouseover="isHovered = true; $refs.videoElement && $refs.videoElement.play()"
            @mouseout="isHovered = false; $refs.videoElement && $refs.videoElement.pause()"
          >
            <img
              class="absolute top-0 left-0 z-10 w-full h-full transition-opacity duration-300 opacity-100"
              :class="{ 'opacity-0': isHovered, 'opacity-100': !isHovered }"
              src="<?php echo esc_url($image['url']); ?>"
              alt="<?php echo esc_attr($image['alt']); ?>"
            />
            <?php if ($video !== '') : ?>
            <video
              class="absolute top-0 left-0 z-50 w-full h-full transition-opacity duration-300 opacity-0"
              :class="{ 'opacity-0': !isHovered, 'opacity-100': isHovered }"
              preload="none"
              x-ref="videoElement"
              muted
              playsinline
            >
              <source src="<?php echo esc_url($video); ?>" type="video/mp4" />
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
</section>
