<?php
// Get all dynamic content
$heading = get_sub_field('heading');
$heading_tag = get_sub_field('heading_tag');
$description = get_sub_field('description');
$content = get_sub_field('content');
$image = get_sub_field('image');
$image_alt = get_post_meta($image, '_wp_attachment_image_alt', true) ?: 'Start a new group image';
$primary_button = get_sub_field('primary_button');
$secondary_button = get_sub_field('secondary_button');
$reverse_layout = get_sub_field('reverse_layout');
$background_color = get_sub_field('background_color');

// Generate unique section ID
$section_id = 'start-group-' . uniqid();

// Build padding classes
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
    class="relative flex overflow-hidden <?php echo esc_attr(implode(' ', $padding_classes)); ?>"
    style="background-color: <?php echo esc_attr($background_color); ?>;"
>
    <div class="flex flex-col items-center py-5 lg:pt-8 lg:pb-16 mx-auto w-full max-w-[1084px] max-lg:px-5">
        <div class="grid gap-4 items-center w-full lg:gap-10 md:grid-cols-2">

            <!-- Text Content Section -->
            <article class="w-full min-w-0 max-md:max-w-full max-md:order-2 <?php echo $reverse_layout ? 'md:order-2' : 'md:order-1'; ?>">
                <div class="w-full max-md:max-w-full">

                    <?php if (!empty($heading)): ?>
                        <<?php echo esc_attr($heading_tag); ?> class="font-sans text-[24px] font-bold not-italic leading-[32px] text-[var(--Blue-SR-500,#00628F)] max-md:max-w-full">
                            <?php echo esc_html($heading); ?>
                        </<?php echo esc_attr($heading_tag); ?>>
                    <?php endif; ?>

                    <?php if (!empty($description)): ?>
                        <p class="mt-2 font-sans text-[20px] font-light not-italic leading-[26px] text-[var(--Gray-600,#475467)] max-md:max-w-full">
                            <?php echo esc_html($description); ?>
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($content)): ?>
                        <div class="mt-2 max-md:max-w-full wp_editor font-sans !text-[16px] !font-normal !not-italic !leading-[22px] !text-[var(--Gray-700,#00263E)] [&_p]:!font-sans [&_p]:!text-[16px] [&_p]:!font-normal [&_p]:!not-italic [&_p]:!leading-[22px] [&_p]:!text-[var(--Gray-700,#00263E)]">
                            <?php echo wp_kses_post($content); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Key Points Section -->
                <?php if (have_rows('key_points')): ?>
                    <div class="flex flex-wrap gap-4 items-start pt-2 mt-4 max-md:max-w-full" role="list" aria-label="Key benefits">
                        <div class="flex flex-col" role="group" aria-label="First set of benefits">
                            <?php
                            $point_count = 0;
                            while (have_rows('key_points')):
                                the_row();
                                $point_text = get_sub_field('point_text');
                                if (!empty($point_text) && $point_count < 2):
                                    $point_count++;
                            ?>
                                <div class="flex overflow-hidden gap-1 items-center px-3 py-1 <?php echo $point_count > 1 ? 'mt-2' : ''; ?> bg-[#CBF3F6] rounded-full w-fit" role="listitem">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="10" viewBox="0 0 13 10" fill="none">
                                        <path d="M11.6667 1L4.33333 8.33333L1 5" stroke="#6EC4A9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    <span class="font-sans text-[14px] font-semibold not-italic leading-[20px] text-[var(--Gray-800,#001929)]">
                                        <?php echo esc_html($point_text); ?>
                                    </span>
                                </div>
                            <?php
                                endif;
                            endwhile;
                            ?>
                        </div>

                        <div class="flex flex-col min-w-60" role="group" aria-label="Second set of benefits">
                            <?php
                            $point_count = 0;
                            while (have_rows('key_points')):
                                the_row();
                                $point_text = get_sub_field('point_text');
                                if (!empty($point_text)):
                                    $point_count++;
                                    if ($point_count > 2):
                            ?>
                                <div class="flex overflow-hidden w-fit gap-1 items-center px-3 py-1 <?php echo $point_count > 3 ? 'mt-2' : ''; ?> bg-[#CBF3F6] rounded-full" role="listitem">
                                  <svg xmlns="http://www.w3.org/2000/svg" width="13" height="10" viewBox="0 0 13 10" fill="none">
                                        <path d="M11.6667 1L4.33333 8.33333L1 5" stroke="#6EC4A9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    <span class="font-sans text-[14px] font-semibold not-italic leading-[20px] text-[var(--Gray-800,#001929)]">
                                        <?php echo esc_html($point_text); ?>
                                    </span>
                                </div>
                            <?php
                                    endif;
                                endif;
                            endwhile;
                            ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Buttons Section -->
                <div class="flex gap-2 items-start pt-4 mt-4 max-w-full text-sm font-bold leading-none w-[289px]">
                    <?php if ($primary_button && is_array($primary_button) && isset($primary_button['url'], $primary_button['title'])): ?>
                        <a
                            href="<?php echo esc_url($primary_button['url']); ?>"
                            class="btn-primary flex gap-2 justify-center items-center px-6 py-4 min-h-[52px] rounded-full w-fit whitespace-nowrap"
                            target="<?php echo esc_attr($primary_button['target'] ?? '_self'); ?>"
                            aria-label="<?php echo esc_attr($primary_button['title']); ?>"
                        >
                            <span class="text-sm font-bold leading-5">
                                <?php echo esc_html($primary_button['title']); ?>
                            </span>
                        </a>
                    <?php endif; ?>

                    <?php if ($secondary_button && is_array($secondary_button) && isset($secondary_button['url'], $secondary_button['title'])): ?>
                        <a
                            href="<?php echo esc_url($secondary_button['url']); ?>"
                            class="btn flex gap-2 justify-center items-center px-6 py-4 text-sky-800 border border-sky-800 border-solid min-h-[52px] rounded-full w-fit whitespace-nowrap hover:bg-sky-50 transition-colors duration-200"
                            target="<?php echo esc_attr($secondary_button['target'] ?? '_self'); ?>"
                            aria-label="<?php echo esc_attr($secondary_button['title']); ?>"
                        >
                            <span class="text-sm font-bold leading-5">
                                <?php echo esc_html($secondary_button['title']); ?>
                            </span>
                        </a>
                    <?php endif; ?>
                </div>
            </article>

            <!-- Image Section -->
            <?php if ($image): ?>
                <div class="w-full min-w-0 overflow-hidden rounded-lg  max-md:order-1 <?php echo $reverse_layout ? 'md:order-1' : 'md:order-2'; ?>">
                    <?php echo wp_get_attachment_image($image, 'full', false, [
                        'alt' => esc_attr($image_alt),
                        'class' => 'h-auto w-full object-contain  max-md:max-w-full',
                    ]); ?>
                </div>
            <?php endif; ?>

        </div>
    </div>
</section>
