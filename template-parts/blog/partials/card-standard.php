<?php
/**
 * Standard blog card — PACE listing grid.
 *
 * @package Matrix_Starter
 *
 * @var int    $post_id
 * @var string $read_more_label
 */

if (!defined('ABSPATH')) {
    exit;
}

$post_id         = isset($post_id) ? (int) $post_id : get_the_ID();
$read_more_label = isset($read_more_label) ? (string) $read_more_label : 'Read more →';
$permalink       = get_permalink($post_id);
$title           = get_the_title($post_id);
$excerpt         = get_the_excerpt($post_id);
$date            = get_the_date('j F Y', $post_id);
$source          = matrix_pace_post_source_label($post_id);
?>
<article class="pace-blog-card group flex flex-col gap-3 rounded-2xl border border-[#ecf0f4] bg-white p-[25px]">
    <div class="flex flex-wrap items-center gap-2.5 text-[12px] leading-[18px] text-[#757575]">
        <time datetime="<?php echo esc_attr(get_the_date('c', $post_id)); ?>"><?php echo esc_html($date); ?></time>
        <span aria-hidden="true">·</span>
        <span><?php echo esc_html($source); ?></span>
    </div>
    <h2 class="font-montserrat text-[22px] font-bold leading-[27.5px] text-[#003b65]">
        <a href="<?php echo esc_url($permalink); ?>" class="hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0a60a0]">
            <?php echo esc_html($title); ?>
        </a>
    </h2>
    <?php if ($excerpt !== '') : ?>
        <p class="font-comfortaa text-[15px] leading-[23.25px] text-[#5c6b78]"><?php echo esc_html($excerpt); ?></p>
    <?php endif; ?>
    <p class="font-comfortaa text-[16px] leading-6 text-[#1d1d1d]">
        <a href="<?php echo esc_url($permalink); ?>" class="underline decoration-solid underline-offset-2 group-hover:text-[#0a60a0]">
            <?php echo esc_html($read_more_label); ?>
        </a>
    </p>
</article>
