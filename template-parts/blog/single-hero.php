<?php
/**
 * Single post hero — featured image with title card overlay (legacy single.blade.php).
 */
if (! has_post_thumbnail()) {
    return;
}

$thumb_id = (int) get_post_thumbnail_id();
?>
<div class="relative w-full">
  <?php
  echo wp_get_attachment_image(
      $thumb_id,
      'full',
      false,
      [
          'class' => 'w-full object-cover h-[500px] max-w-max-sitewidth margin-auto',
          'alt'   => matrix_rd_attachment_alt(
              (string) wp_get_attachment_url($thumb_id),
              get_the_title()
          ),
      ]
  );
  ?>
  <div class="absolute top-0 left-0 right-0 mx-auto flex h-full max-w-[1296px] items-end px-4 lg:items-center lg:px-8 macbook:px-0">
    <div class="mob-no-b-border h-auto w-full rounded-normal bg-white p-5 max-lg:rounded-bl-none max-lg:rounded-br-none lg:w-[517px]">
      <h1 class="mb-6 font-reg420 text-1lg-font leading-[56px] sm:text-xl-font">
        <?php the_title(); ?>
      </h1>
      <?php if (has_excerpt()) : ?>
      <div class="p-summary mb-6 text-base max-mobile:hidden">
        <?php the_excerpt(); ?>
      </div>
      <?php endif; ?>
      <?php get_template_part('template-parts/blog/entry-meta'); ?>
    </div>
  </div>
</div>
