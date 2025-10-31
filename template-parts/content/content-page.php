<?php
$content     = get_the_content();
$is_checkout = function_exists('is_checkout') && is_checkout();

$extra_class = (!empty(trim($content)) && !$is_checkout) ? ' py-12' : '';
?>
<article class="relative wp_editor" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="entry-content<?php echo esc_attr($extra_class); ?>">
        <?php the_content(); ?>
    </div>
</article>