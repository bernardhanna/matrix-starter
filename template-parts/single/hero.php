<?php
$background_image = get_field('banner_image');
if (!$background_image) {
  $background_image = get_the_post_thumbnail_url(get_the_ID(), 'full');
}

if ($background_image) { // Only display section if an image exists
  $background_opacity = get_field('background_opacity');
  if ($background_opacity === null) {
    $background_opacity = 0.7;
  }
  $excerpt = has_excerpt() ? get_the_excerpt() : '';
?>

  <section class="relative flex overflow-hidden bg-center bg-cover h-[240px] mt-12 pt-12 pb-12 lg:pt-0 lg:pb-0 items-start text-left"
    style="background-image: url('<?php echo esc_url($background_image); ?>');">
    <div class="absolute inset-0" style="background-color: #083344; opacity: <?php echo esc_attr($background_opacity); ?>;"></div>
    <div class="px-5 xl:px-0 z-40 flex flex-col w-full mx-auto max-w-[1085px] justify-center h-full">
      <nav class="pb-8" aria-label="Breadcrumb">
        <ol class="flex text-xs leading-none text-white">
          <li>
            <a href="<?php echo esc_url(home_url('/resources')); ?>" class="font-semibold hover:underline focus:outline-none focus:ring-2 focus:ring-white">Resources</a>
            <span class="mx-1" aria-hidden="true">&gt;</span>
          </li>
          <li aria-current="page"><?php the_title(); ?></li>
        </ol>
      </nav>
      <h1 class="text-4xl font-bold leading-none tracking-tight" style="color: #ffb703; opacity: 1;">
        <?php the_title(); ?>
      </h1>
      <?php if (!empty($excerpt)) : ?>
        <p class="text-white">
          <?php echo esc_html($excerpt); ?>
        </p>
      <?php endif; ?>
    </div>
  </section>

<?php } // End if $background_image exists 
?>