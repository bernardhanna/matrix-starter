<?php
/**
 * Post byline — legacy partials/entry-meta.blade.php.
 */
$author_id = (int) get_the_author_meta('ID');
?>
<div class="flex flex-row flex-wrap justify-between">
  <p class="font-laca text-xs-font">
    <span><?php esc_html_e('By', 'matrix-starter'); ?></span>
    <a href="<?php echo esc_url(get_author_posts_url($author_id)); ?>" class="p-author h-card">
      <?php the_author(); ?>
    </a>
  </p>
  <time class="dt-published font-laca text-xs-font" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
    <?php echo esc_html(get_the_date()); ?>
  </time>
</div>
