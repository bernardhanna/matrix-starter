<?php
/**
 * Featured blog card — PACE listing (index only).
 *
 * @package Matrix_Starter
 *
 * @var int    $post_id
 * @var string $read_more_label
 * @var string $featured_label
 */

if (!defined('ABSPATH')) {
    exit;
}

$post_id         = isset($post_id) ? (int) $post_id : get_the_ID();
$read_more_label = isset($read_more_label) ? (string) $read_more_label : 'Read more →';
$featured_label  = isset($featured_label) ? (string) $featured_label : 'FEATURED';
$permalink       = get_permalink($post_id);
$title           = get_the_title($post_id);
$excerpt         = get_the_excerpt($post_id);
$date            = get_the_date('j F Y', $post_id);
$source          = matrix_pace_post_source_label($post_id);
$thumb_id        = get_post_thumbnail_id($post_id);
?>
<article class="pace-blog-featured group col-span-1 flex flex-col overflow-hidden rounded-2xl border border-[#ecf0f4] bg-white md:col-span-2 md:flex-row">
    <?php if ($thumb_id) : ?>
        <div class="relative aspect-[320/220] w-full shrink-0 md:w-[320px]">
            <?php
            echo wp_get_attachment_image($thumb_id, 'large', false, [
                'class' => 'absolute inset-0 size-full object-cover',
                'alt'   => get_post_meta($thumb_id, '_wp_attachment_image_alt', true) ?: $title,
            ]);
            ?>
        </div>
    <?php endif; ?>
    <div class="flex flex-1 flex-col gap-3 p-6 md:p-[25px]">
        <div class="flex flex-wrap items-center gap-2.5">
            <span class="rounded bg-[#fffae8] px-2 py-0.5 font-montserrat text-[11px] font-semibold uppercase tracking-wide text-[#8a6608]">
                <?php echo esc_html($featured_label); ?>
            </span>
            <span class="text-[12px] leading-[18px] text-[#757575]">
                <time datetime="<?php echo esc_attr(get_the_date('c', $post_id)); ?>"><?php echo esc_html($date); ?></time>
                <span aria-hidden="true"> · </span>
                <?php echo esc_html($source); ?>
            </span>
        </div>
        <h2 class="font-montserrat text-[22px] font-bold leading-[27.5px] text-[#003b65] md:text-[24px] md:leading-[30px]">
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
    </div>
</article>
