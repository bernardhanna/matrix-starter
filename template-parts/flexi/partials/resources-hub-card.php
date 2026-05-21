<?php
/**
 * Resources Hub pathway card — Figma 4:22 / 4:34 / 4:46.
 *
 * @package Matrix_Starter
 *
 * @var array<string, mixed> $card
 * @var array<string, string> $style
 */

if (!defined('ABSPATH')) {
    exit;
}

$card  = is_array($args['card'] ?? null) ? $args['card'] : (is_array($card ?? null) ? $card : []);
$style = is_array($args['style'] ?? null) ? $args['style'] : (is_array($style ?? null) ? $style : matrix_pace_resources_hub_card_styles()['blue']);

$link      = is_array($card['link'] ?? null) ? $card['link'] : [];
$url       = !empty($link['url']) ? (string) $link['url'] : '#';
$target    = !empty($link['target']) ? (string) $link['target'] : '_self';
$title     = (string) ($card['title'] ?? '');
$desc      = (string) ($card['description'] ?? '');
$cta       = (string) ($card['cta_label'] ?? 'Enter pathway →');
$icon_id   = (int) ($card['icon'] ?? 0);
$preset    = (string) ($card['icon_preset'] ?? 'youth_workers');
$icon_src  = matrix_pace_resources_hub_icon_src($preset);
$aria      = $title !== '' ? $title : ($link['title'] ?? 'Pathway');
?>
<a
    href="<?php echo esc_url($url); ?>"
    target="<?php echo esc_attr($target); ?>"
    <?php echo $target === '_blank' ? 'rel="noopener noreferrer"' : ''; ?>
    class="pace-resources-card group flex min-h-[261px] flex-col rounded-[20px] px-7 py-8 transition hover:opacity-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0a60a0] focus-visible:ring-offset-2"
    style="background: <?php echo esc_attr($style['gradient']); ?>;"
    aria-label="<?php echo esc_attr($aria); ?>"
>
    <div
        class="mb-2 flex size-14 shrink-0 items-center justify-center rounded-2xl"
        style="background-color: <?php echo esc_attr($style['icon_bg']); ?>;"
    >
        <?php if ($icon_id > 0) : ?>
            <?php
            echo wp_get_attachment_image($icon_id, 'thumbnail', false, [
                'class' => 'size-8 object-contain',
                'alt'   => '',
            ]);
            ?>
        <?php elseif ($icon_src !== '') : ?>
            <img
                src="<?php echo esc_url($icon_src); ?>"
                alt=""
                class="size-8 object-contain"
                width="32"
                height="32"
                loading="lazy"
                decoding="async"
            />
        <?php else : ?>
            <span style="color: <?php echo esc_attr($style['title_color']); ?>;">
                <?php echo matrix_pace_resources_hub_icon_svg($preset); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </span>
        <?php endif; ?>
    </div>

    <div class="flex min-h-0 flex-1 flex-col">
        <?php if ($title !== '') : ?>
            <h2
                class="font-montserrat text-[24px] font-bold leading-[28.8px]"
                style="color: <?php echo esc_attr($style['title_color']); ?>;"
            >
                <?php echo esc_html($title); ?>
            </h2>
        <?php endif; ?>

        <?php if ($desc !== '') : ?>
            <p
                class="mt-2.5 font-comfortaa text-[14px] leading-[21.7px] opacity-90"
                style="color: <?php echo esc_attr($style['text_color']); ?>;"
            >
                <?php echo matrix_pace_flexi_body_text($desc); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </p>
        <?php endif; ?>
    </div>

    <p
        class="mt-3 pt-3 font-montserrat text-[13px] font-semibold leading-[19.5px]"
        style="color: <?php echo esc_attr($style['cta_color']); ?>;"
    >
        <?php echo esc_html($cta); ?>
    </p>
</a>
