<?php
/**
 * Related posts sidebar — legacy blog/related.blade.php.
 */

$current_id = (int) get_the_ID();
if ($current_id <= 0) {
    return;
}

$category_ids = [];
$categories   = get_the_category($current_id);
if (is_array($categories)) {
    foreach ($categories as $category) {
        $category_ids[] = (int) $category->term_id;
    }
}

$related_args = [
    'post_type'      => 'post',
    'posts_per_page' => 3,
    'post__not_in'   => [$current_id],
    'orderby'        => 'rand',
];

if ($category_ids !== []) {
    $related_args['category__in'] = $category_ids;
}

$related_posts = new WP_Query($related_args);
?>
<aside>
  <h3 class="black-primary-full pb-6 font-reg420 text-font-28 notebook:text-lg-font lg:pb-10">
    <?php esc_html_e('Related Articles', 'matrix-starter'); ?>
  </h3>
  <?php if ($related_posts->have_posts()) : ?>
  <ul class="flex list-none flex-col gap-8 p-0">
    <?php
    while ($related_posts->have_posts()) :
        $related_posts->the_post();
        $thumb_id  = (int) get_post_thumbnail_id();
        $thumb_url = $thumb_id ? get_the_post_thumbnail_url(get_the_ID(), 'full') : '';
        $srcset    = $thumb_id ? wp_get_attachment_image_srcset($thumb_id, 'full') : '';
        $minutes   = matrix_rd_reading_time((string) get_post_field('post_content', get_the_ID()));
        ?>
    <li class="boxshadow-three rounded-sm-10 border-2 border-solid border-black-full p-4">
      <a href="<?php the_permalink(); ?>" class="related-post group">
        <?php if ($thumb_url) : ?>
        <img
          class="rounded-6xs h-[310px] w-full object-cover"
          src="<?php echo esc_url($thumb_url); ?>"
          <?php if ($srcset) : ?>
          srcset="<?php echo esc_attr($srcset); ?>"
          sizes="(max-width: 768px) 100vw, (max-width: 1024px) 50vw, 267px"
          <?php endif; ?>
          alt="<?php the_title_attribute(); ?>"
        />
        <?php endif; ?>
        <div class="flex flex-col justify-around">
          <h2 class="post-title relative pb-3 pt-5 font-reg420 text-sm-md-font leading-[1.625rem] text-black-full">
            <?php the_title(); ?>
          </h2>
          <span class="reading-time flex items-center font-regular text-sm-font text-black-full">
            <?php echo (int) $minutes; ?> <?php esc_html_e('min read', 'matrix-starter'); ?>
            <span class="iconify ml-2 group-hover:hidden" data-icon="bi--arrow-right" aria-hidden="true"></span>
          </span>
          <span class="post-date light-grey pt-3 font-regular text-sm-font">
            <?php echo esc_html(get_the_date('j, F Y')); ?>
          </span>
        </div>
      </a>
    </li>
        <?php
    endwhile;
    wp_reset_postdata();
    ?>
  </ul>
  <?php else : ?>
  <p><?php esc_html_e('No related articles found.', 'matrix-starter'); ?></p>
  <?php endif; ?>
</aside>
