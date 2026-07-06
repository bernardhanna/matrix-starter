<?php

$section_id = 'cta-large-button-' . wp_generate_uuid4();
$heading = get_sub_field('heading');
$heading_tag = get_sub_field('heading_tag') ?: 'h3';
$description = get_sub_field('description');
$button = get_sub_field('button');
$show_button_icon = (bool) get_sub_field('show_button_icon');
$alignment = get_sub_field('alignment') ?: 'center';
$button_size = get_sub_field('button_size') ?: 'large';
$background_color = get_sub_field('background_color') ?: '#ffffff';

$allowed_heading_tags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'span'];
if (!in_array($heading_tag, $allowed_heading_tags, true)) {
    $heading_tag = 'h3';
}

$padding_classes = [];
if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
        the_row();
        $screen_size = get_sub_field('screen_size');
        $padding_top = get_sub_field('padding_top');
        $padding_bottom = get_sub_field('padding_bottom');
        $padding_classes[] = "{$screen_size}:pt-[{$padding_top}rem]";
        $padding_classes[] = "{$screen_size}:pb-[{$padding_bottom}rem]";
    }
}

$container_align_class = 'items-center text-center';
$button_wrap_class = 'justify-center';
if ($alignment === 'left') {
    $container_align_class = 'items-start text-left';
    $button_wrap_class = 'justify-start';
} elseif ($alignment === 'right') {
    $container_align_class = 'items-end text-right';
    $button_wrap_class = 'justify-end';
}

$button_size_class = 'px-8 py-4 text-base w-full md:w-fit';
if ($button_size === 'xsmall') {
    $button_size_class = 'px-4 py-2 text-xs w-full md:w-fit';
} elseif ($button_size === 'medium') {
    $button_size_class = 'px-6 py-3 text-sm w-full md:w-fit';
} elseif ($button_size === 'full_width') {
    $button_size_class = 'px-6 py-3 text-sm w-full';
}
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="relative flex overflow-hidden <?php echo esc_attr(implode(' ', $padding_classes)); ?>"
    style="background-color: <?php echo esc_attr($background_color); ?>;"
    aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
>
    <div class="mx-auto flex w-full max-w-[1095px] flex-col <?php echo esc_attr($container_align_class); ?> gap-4 px-5">
        <?php if (!empty($heading)) : ?>
            <<?php echo esc_attr($heading_tag); ?>
                id="<?php echo esc_attr($section_id); ?>-heading"
                class="font-['Public_Sans'] text-[30px] font-bold leading-[38px] text-brand-primary-hover"
            >
                <?php echo esc_html($heading); ?>
            </<?php echo esc_attr($heading_tag); ?>>
        <?php endif; ?>

        <?php if (!empty($description)) : ?>
            <div class="wp_editor text-content-body">
                <?php echo wp_kses_post($description); ?>
            </div>
        <?php endif; ?>

        <?php if ($button && is_array($button) && !empty($button['url']) && !empty($button['title'])) : ?>
            <div class="flex w-full <?php echo esc_attr($button_wrap_class); ?>">
                <a
                    href="<?php echo esc_url($button['url']); ?>"
                    target="<?php echo esc_attr($button['target'] ?? '_self'); ?>"
                    class="inline-flex gap-2 justify-center items-center <?php echo esc_attr($button_size_class); ?> font-bold text-white rounded-pill border-[3px] border-[var(--Turquoise-500,#1C959B)] bg-[var(--Blue-SR-400,#008BCC)] hover:bg-[var(--Blue-SR-500,#00628F)] hover:text-white focus:outline-none focus-visible:ring-0 focus-visible:border-[3px] focus-visible:border-[var(--Turquoise-500,#1C959B)] focus-visible:bg-[var(--Blue-SR-500,#00628F)] focus-visible:text-white transition-colors duration-200 btn-primary"
                    aria-label="<?php echo esc_attr($button['title']); ?>"
                >
                    <?php echo esc_html($button['title']); ?>
                    <?php if ($show_button_icon) : ?>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    <?php endif; ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>
