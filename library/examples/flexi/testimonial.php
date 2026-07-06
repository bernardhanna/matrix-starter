<?php
// Get ACF fields
$main_image = get_sub_field('main_image');
$main_image_alt = get_post_meta($main_image, '_wp_attachment_image_alt', true) ?: 'Testimonial image';
$quotation_mark_color = get_sub_field('quotation_mark_color') ?: '#ec4899';
$main_quote = get_sub_field('main_quote');
$main_quote_tag = get_sub_field('main_quote_tag') ?: 'h2';
$highlighted_quote = get_sub_field('highlighted_quote');
$author_name = get_sub_field('author_name');
$author_title = get_sub_field('author_title');
$signature_image = get_sub_field('signature_image');
$signature_image_alt = get_post_meta($signature_image, '_wp_attachment_image_alt', true) ?: 'Author signature';
$background_color = get_sub_field('background_color') ?: '#ffffff';

// Generate unique section ID
$section_id = 'testimonial_' . uniqid();

// Padding classes
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
    role="region"
    aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
>
    <div class="flex flex-col items-center  mx-auto w-full max-w-[1084px] max-lg:px-5">
        <div class="flex flex-col justify-center items-center px-5 pt-8 pb-6 w-full md:pt-16 md:flex-row md:px-0">
            <div class="flex flex-col gap-4 items-start w-full md:flex-row md:gap-20">

                <?php if ($main_image): ?>
                <div class="flex-1 max-md:w-full" role="img" aria-labelledby="<?php echo esc_attr($section_id); ?>-image-desc">
                    <?php echo wp_get_attachment_image($main_image, 'full', false, [
                        'alt' => esc_attr($main_image_alt),
                        'class' => 'w-full h-auto rounded-lg object-cover max-sm:object-contain  max-w-[450px] max-h-[450px]',
                        'loading' => 'lazy'
                    ]); ?>
                    <span id="<?php echo esc_attr($section_id); ?>-image-desc" class="sr-only">
                        <?php echo esc_html($main_image_alt); ?>
                    </span>
                </div>
                <?php endif; ?>

                <div class="flex flex-col flex-1 pt-6 pl-12 max-md:w-full md:pl-0">

                   <?php if (!empty($main_quote)): ?>

                    <div class="relative w-full max-w-[400px]">

                        <!-- Quotation mark -->
                        <div
                            class="absolute -top-4 -left-4 text-6xl font-bold leading-none -translate-x-1/2 md:text-7xl"
                            style="color: <?php echo esc_attr($quotation_mark_color); ?>;"
                            aria-hidden="true"
                        >
                            "
                        </div>

                        <<?php echo esc_attr($main_quote_tag); ?>
                            id="<?php echo esc_attr($section_id); ?>-heading"
                            class="pt-0 mb-6 text-left font-sans text-[36px] font-bold not-italic leading-[44px] tracking-[-0.72px] text-sky-800"
                            role="heading"
                            aria-level="<?php echo esc_attr(str_replace('h', '', $main_quote_tag)); ?>"
                        >
                            <?php echo esc_html($main_quote); ?>
                        </<?php echo esc_attr($main_quote_tag); ?>>

                    </div>

                    <?php endif; ?>

                    <?php if (!empty($highlighted_quote)): ?>
                    <blockquote
                        class="max-w-[400px] pl-6 mb-8 border-l-2 border-[#008BCC] max-sm:mb-6"
                        role="complementary"
                        aria-labelledby="<?php echo esc_attr($section_id); ?>-quote"
                    >
                        <p
                            id="<?php echo esc_attr($section_id); ?>-quote"
                            class="wp_editor font-sans !text-[18px] !font-normal !not-italic !leading-[24px] text-gray-700 [&_p]:!font-sans [&_p]:!text-[18px] [&_p]:!font-normal [&_p]:!not-italic [&_p]:!leading-[24px] [&_p]:!text-gray-700"
                        >
                            <?php echo wp_kses_post($highlighted_quote); ?>
                        </p>
                    </blockquote>
                    <?php endif; ?>

                    <?php if (!empty($author_name) || !empty($author_title)): ?>
                    <cite class="mb-8 block font-sans text-[14px] font-bold not-italic leading-[20px] text-gray-900 max-sm:mb-6">
                        <?php if (!empty($author_name)): ?>
                            <span class="author-name"><?php echo esc_html($author_name); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($author_title)): ?>
                            <?php if (!empty($author_name)): ?>, <?php endif; ?>
                            <span class="author-title"><?php echo esc_html($author_title); ?></span>
                        <?php endif; ?>
                    </cite>
                    <?php endif; ?>

                    <?php if ($signature_image): ?>
                    <div class="signature-container" role="img" aria-labelledby="<?php echo esc_attr($section_id); ?>-signature-desc">
                        <?php echo wp_get_attachment_image($signature_image, 'full', false, [
                            'alt' => esc_attr($signature_image_alt),
                            'class' => 'h-[60px] max-sm:h-[50px] w-auto object-contain',
                            'loading' => 'lazy'
                        ]); ?>
                        <span id="<?php echo esc_attr($section_id); ?>-signature-desc" class="sr-only">
                            <?php echo esc_html($signature_image_alt); ?>
                        </span>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</section>