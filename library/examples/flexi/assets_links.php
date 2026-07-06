<?php
$section_id = 'assets-links-' . uniqid();
$heading = get_sub_field('heading');
$heading_tag = get_sub_field('heading_tag') ?: 'h2';
$center_heading = (bool) get_sub_field('center_heading');
$background_color = get_sub_field('background_color');
$background_gradient = get_sub_field('background_gradient');
$item_text_color = get_sub_field('item_text_color') ?: '#FFFFFF';
$item_text_size = get_sub_field('item_text_size') ?: 'lg';
$asset_items = get_sub_field('asset_items');

$text_size_class_map = [
    'base' => 'text-base',
    'lg' => 'text-lg',
    'xl' => 'text-xl',
];
$item_text_size_class = $text_size_class_map[$item_text_size] ?? 'text-lg';

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
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="relative overflow-hidden <?php echo esc_attr(implode(' ', $padding_classes)); ?>"
    style="<?php echo !empty($background_gradient)
        ? 'background: ' . esc_attr($background_gradient) . ';'
        : 'background-color: ' . esc_attr($background_color ?: '#1e40af') . ';'; ?>"
    aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
>
    <div class="mx-auto w-full max-w-[1024px] px-5 py-[5rem] max-lg:py-12">
        <?php if (!empty($heading)): ?>
            <<?php echo esc_attr($heading_tag); ?>
                id="<?php echo esc_attr($section_id); ?>-heading"
                class="mb-8 text-3xl font-semibold leading-none text-white <?php echo $center_heading ? 'text-center' : 'text-left'; ?>"
            >
                <?php echo esc_html($heading); ?>
            </<?php echo esc_attr($heading_tag); ?>>
        <?php endif; ?>

        <?php if (!empty($asset_items) && is_array($asset_items)): ?>
            <ul class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3 md:gap-10 lg:gap-12" role="list">
                <?php foreach ($asset_items as $item): ?>
                    <?php
                    $item_icon = $item['icon'] ?? null;
                    $item_title = $item['title'] ?? '';
                    $item_link = $item['link'] ?? [];
                    $item_url = !empty($item_link['url']) ? $item_link['url'] : '#';
                    $item_target = !empty($item_link['target']) ? $item_link['target'] : '_self';
                    $item_label = !empty($item_title) ? $item_title : ($item_link['title'] ?? '');
                    ?>
                    <li class="flex w-full items-center gap-4">
                        <?php if (!empty($item_icon)): ?>
                            <div aria-hidden="true">
                                <?php echo wp_get_attachment_image($item_icon, 'thumbnail', false, [
                                    'alt' => '',
                                    'class' => 'h-auto w-full object-contain',
                                ]); ?>
                            </div>
                        <?php endif; ?>

                        <a
                            href="<?php echo esc_url($item_url); ?>"
                            target="<?php echo esc_attr($item_target); ?>"
                            class="inline-flex max-w-full items-center gap-2 font-semibold leading-tight transition-opacity duration-300 hover:opacity-80 <?php echo esc_attr($item_text_size_class); ?>"
                            style="color: <?php echo esc_attr($item_text_color); ?>;"
                            aria-label="<?php echo esc_attr($item_label); ?>"
                        >
                            <span class="leading-tight"><?php echo esc_html($item_label); ?></span>
                            <svg class="h-[1em] w-[1em] shrink-0" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</section>
