<?php
/**
 * Resource archive card — Figma 4:563.
 *
 * @package Matrix_Starter
 *
 * @var int $post_id
 */

if (!defined('ABSPATH')) {
    exit;
}

$post_id    = isset($post_id) ? (int) $post_id : get_the_ID();
$url        = matrix_pace_resource_card_url($post_id);
$is_external = trim((string) get_field('resource_external_url', $post_id)) !== '';
$status     = trim((string) get_field('resource_status_label', $post_id));
$title      = get_the_title($post_id);
$excerpt    = get_the_excerpt($post_id);
$meta       = matrix_pace_resource_meta_line($post_id);
$cta        = trim((string) get_field('resource_cta_label', $post_id));
if ($cta === '') {
    $cta = 'View module →';
}
?>
<article class="pace-resource-card group flex h-full flex-col gap-2 rounded-[14px] border border-[#e1e7ec] bg-white p-[23px] transition hover:border-[#0a60a0]">
    <?php if ($status !== '') : ?>
        <p class="font-comfortaa text-[16px] leading-6 text-[#1d1d1d]"><?php echo esc_html($status); ?></p>
    <?php endif; ?>

    <h3 class="font-montserrat text-[18px] font-bold leading-[21.6px] text-[#003b65]">
        <a
            href="<?php echo esc_url($url); ?>"
            class="hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0a60a0]"
            <?php echo $is_external ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>
        >
            <?php echo esc_html($title); ?>
        </a>
    </h3>

    <?php if ($excerpt !== '') : ?>
        <p class="font-comfortaa text-[14px] leading-[21.7px] text-[#5c6b78]"><?php echo esc_html($excerpt); ?></p>
    <?php endif; ?>

    <?php if ($meta !== '') : ?>
        <p class="font-mono text-[12px] leading-[18px] text-[#757575]"><?php echo esc_html($meta); ?></p>
    <?php endif; ?>

    <p class="mt-auto pt-1 font-montserrat text-[14px] font-semibold leading-[21px] text-[#0a60a0]">
        <a
            href="<?php echo esc_url($url); ?>"
            class="underline-offset-2 hover:underline"
            <?php echo $is_external ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>
        >
            <?php echo esc_html($cta); ?>
        </a>
    </p>
</article>
