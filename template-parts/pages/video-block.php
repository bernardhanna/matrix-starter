<?php
/**
 * Page-level YouTube video block (ACF on current page).
 */
$post_id  = get_the_ID();
$thumb    = matrix_rd_acf_image(get_field('video_thumbnail', $post_id));
$video_id = (string) get_field('youtube_video_id', $post_id);

if ($thumb['url'] === '' && $video_id === '') {
    return;
}

$thumb_alt = $thumb['alt'] !== '' ? $thumb['alt'] : __('Video Thumbnail', 'matrix-starter');
$play_logo = get_template_directory_uri() . '/assets/images/play-logo.svg';
?>
<section class="relative flex flex-col items-center w-full gap-10 px-4 pt-8 max-sm:pb-8 pb-0 m-auto flexi-video rounded-video">
  <div x-data="{ videoPlaying: false, videoSrc: '' }" class="relative w-full max-w-max-1359">
    <div class="aspect-ratio-16/9 relative w-full h-0 pb-[56.25%] rounded-video overflow-hidden drop-shadow_one">
      <?php if ($thumb['url']) : ?>
        <img
          x-show="!videoPlaying"
          class="video-thumbnail absolute top-0 left-0 object-cover w-full h-full rounded-video"
          src="<?php echo esc_url($thumb['url']); ?>"
          alt="<?php echo esc_attr($thumb_alt); ?>"
        />
      <?php endif; ?>
      <?php if ($video_id) : ?>
        <div
          @click="videoPlaying = true; videoSrc = 'https://www.youtube-nocookie.com/embed/<?php echo esc_attr($video_id); ?>?autoplay=1&controls=0&mute=0';"
          x-show="!videoPlaying"
          class="play-button absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[120px] h-[120px] cursor-pointer bg-no-repeat bg-center"
          style="background-image: url('<?php echo esc_url($play_logo); ?>');"
          role="button"
          tabindex="0"
          aria-label="<?php esc_attr_e('Play video', 'matrix-starter'); ?>"
          @keydown.enter.prevent="videoPlaying = true; videoSrc = 'https://www.youtube-nocookie.com/embed/<?php echo esc_attr($video_id); ?>?autoplay=1&controls=0&mute=0';"
        ></div>
        <iframe
          x-show="videoPlaying"
          :src="videoSrc"
          class="absolute top-0 left-0 w-full h-full rounded-video"
          title="<?php esc_attr_e('YouTube video player', 'matrix-starter'); ?>"
          frameborder="0"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
          allowfullscreen
        ></iframe>
      <?php endif; ?>
    </div>
  </div>
</section>
