<?php
/**
 * Partner card link — Figma 3:890 (PACE Internal Board).
 *
 * Expects $args['card'] from matrix_partners_card_from_post() / matrix_partners_normalize_card().
 *
 * @package Matrix_Starter
 */

if (!defined('ABSPATH')) {
    exit;
}

$card = isset($args['card']) && is_array($args['card']) ? $args['card'] : [];
if ($card === []) {
    return;
}

$logo_id = (int) ($card['logo_id'] ?? 0);
$title   = (string) ($card['title'] ?? '');
$role    = (string) ($card['role'] ?? '');
$description = trim((string) ($card['description'] ?? ''));

$view_label = trim((string) ($args['view_partner_label'] ?? ''));
if ($view_label === '') {
    $view_label = trim((string) ($card['link_label'] ?? ''));
}
if ($view_label === '') {
    $view_label = __('View partner →', 'matrix-starter');
}

$border_color = trim((string) ($args['card_border_color'] ?? '#ecf0f4'));
$title_id     = trim((string) ($args['title_id'] ?? ''));
if ($title_id === '') {
    $post_id = (int) ($card['id'] ?? 0);
    $title_id = $post_id > 0 ? 'partner-card-' . $post_id : 'partner-card-' . wp_unique_id();
}

$href = function_exists('matrix_partners_card_href')
    ? matrix_partners_card_href($card)
    : null;

if ($href === null) {
    $link = $card['link'] ?? null;
    if (is_array($link) && !empty($link['url'])) {
        $external = ($link['target'] ?? '') === '_blank';
        $href = [
            'url'        => (string) $link['url'],
            'target'     => $external ? '_blank' : '_self',
            'rel'        => $external ? 'noopener noreferrer' : '',
            'aria_label' => $title !== '' ? $title : (string) ($link['title'] ?? ''),
        ];
    }
}

$has_link   = $href !== null;
$logo_alt   = $title !== '' ? $title : __('Partner logo', 'matrix-starter');
$card_style = 'border-color:' . esc_attr($border_color) . ';';
$tag        = $has_link ? 'a' : 'article';
$card_class = 'flex flex-col gap-3.5 rounded-2xl border border-solid bg-white p-[25px] transition hover:border-[#0a60a0] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0a60a0]';
if ($has_link) {
    $card_class .= ' group';
}
?>
<<?php echo esc_attr($tag); ?>
    <?php if ($has_link) : ?>
        href="<?php echo esc_url($href['url']); ?>"
        target="<?php echo esc_attr($href['target']); ?>"
        <?php if ($href['rel'] !== '') : ?>
            rel="<?php echo esc_attr($href['rel']); ?>"
        <?php endif; ?>
        <?php if ($href['aria_label'] !== '') : ?>
            aria-label="<?php echo esc_attr($href['aria_label']); ?>"
        <?php else : ?>
            aria-labelledby="<?php echo esc_attr($title_id); ?>"
        <?php endif; ?>
    <?php else : ?>
        aria-labelledby="<?php echo esc_attr($title_id); ?>"
    <?php endif; ?>
    class="<?php echo esc_attr($card_class); ?>"
    style="<?php echo esc_attr($card_style); ?>"
>
    <div class="flex w-full items-center gap-3.5">
        <?php if ($logo_id > 0) : ?>
            <div class="flex h-[54px] w-[105px] shrink-0 items-center justify-center">
                <?php
                echo wp_get_attachment_image(
                    $logo_id,
                    'medium',
                    false,
                    [
                        'alt'   => esc_attr($logo_alt),
                        'class' => 'max-h-[54px] max-w-[105px] object-contain object-left',
                    ]
                );
                ?>
            </div>
        <?php endif; ?>

        <?php if ($title !== '' || $role !== '') : ?>
            <div class="min-w-0 flex-1">
                <?php if ($title !== '') : ?>
                    <?php
                    $heading_tag = $has_link ? 'h2' : 'h3';
                    ?>
                    <<?php echo esc_attr($heading_tag); ?>
                        id="<?php echo esc_attr($title_id); ?>"
                        class="text-[18px] font-bold leading-[27px] text-[#003b65]"
                    >
                        <?php echo esc_html($title); ?>
                    </<?php echo esc_attr($heading_tag); ?>>
                <?php endif; ?>
                <?php if ($role !== '') : ?>
                    <p class="font-comfortaa text-[13px] font-normal leading-[19.5px] text-[#757575]">
                        <?php echo esc_html($role); ?>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($description !== '') : ?>
        <div class="font-comfortaa text-[14px] font-normal leading-[22.4px] text-[#1d1d1d] [&_p]:mb-0">
            <?php echo wp_kses_post($description); ?>
        </div>
    <?php endif; ?>

    <?php if ($has_link) : ?>
        <span class="text-[13px] font-semibold leading-[19.5px] text-[#0a60a0] group-hover:underline">
            <?php echo esc_html($view_label); ?>
        </span>
    <?php endif; ?>
</<?php echo esc_attr($tag); ?>>
