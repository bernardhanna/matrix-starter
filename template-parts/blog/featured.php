<?php
/**
 * Featured posts row on blog index (ACF on posts page).
 */
$blog_page_id = (int) get_option('page_for_posts');
$rows         = matrix_rd_acf_repeater_rows('featured_posts', ['post'], $blog_page_id);

if ($rows === []) {
    return;
}
?>
<div class="flex flex-flow flex-row justify-between items-center my-12 lg:px-4 bg-white">
  <div class="w-full pb-4">
    <ul class="flex flex-flow flex-nowrap overflow-x-auto flex-row justify-between items-center w-full bg-white lg:gap-0 gap-4">
      <?php foreach ($rows as $row) :
          $post = matrix_rd_acf_post($row['post'] ?? null);
          if (! $post) {
              continue;
          }
          $thumb = get_the_post_thumbnail_url($post->ID, 'medium_large');
          $cats  = get_the_category($post->ID);
          $cat_names = implode(', ', array_map(static fn ($c) => $c->name, $cats));
          $content = get_post_field('post_content', $post->ID);
          $mins    = max(1, (int) ceil(str_word_count(wp_strip_all_tags($content)) / 250));
          ?>
      <li class="featured-post border-solid border-black-full rounded-sm-10 p-4 min-w-[280px] boxshadow-three">
        <a href="<?php echo esc_url(get_permalink($post)); ?>" class="w-full h-full block">
          <?php if ($thumb) : ?>
          <img class="rounded-sm-10 w-full h-[300px] object-cover" src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr($post->post_title); ?>" />
          <?php endif; ?>
          <div class="flex flex-row flex-wrap justify-between items-center pt-4">
            <?php if ($cat_names) : ?>
            <span class="text-sm-font text-black-secondary font-laca"><?php echo esc_html($cat_names); ?></span>
            <?php endif; ?>
            <span class="text-sm-font text-black-secondary font-laca"><?php echo esc_html(get_the_date('j, F Y', $post)); ?></span>
          </div>
          <h4 class="text-black-full text-mob-md-font font-reg420 leading-[1.625rem] py-2"><?php echo esc_html($post->post_title); ?></h4>
          <span class="text-sm-font font-laca text-black-full"><?php echo (int) $mins; ?> <?php esc_html_e('min read', 'matrix-starter'); ?></span>
        </a>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>
