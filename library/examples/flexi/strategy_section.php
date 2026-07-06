<?php
// Get ACF field values
$heading            = get_sub_field('heading');
$heading_tag        = get_sub_field('heading_tag');
$content            = get_sub_field('content');
$main_image         = get_sub_field('main_image');
$main_image_alt     = get_post_meta($main_image, '_wp_attachment_image_alt', true) ?: 'Strategy main image';
$secondary_image_1  = get_sub_field('secondary_image_1');
$secondary_image_1_alt = get_post_meta($secondary_image_1, '_wp_attachment_image_alt', true) ?: 'Strategy secondary image';
$secondary_image_2  = get_sub_field('secondary_image_2');
$secondary_image_2_alt = get_post_meta($secondary_image_2, '_wp_attachment_image_alt', true) ?: 'Strategy secondary image';
$button             = get_sub_field('button');
$background_color   = get_sub_field('background_color');
$reverse_layout     = (bool) get_sub_field('reverse_layout');

// Generate unique section ID
$section_id = 'strategy-' . uniqid();

// Build padding classes
$padding_classes = [];
if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
        the_row();
        $screen_size    = get_sub_field('screen_size');
        $padding_top    = get_sub_field('padding_top');
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
    <div class="flex flex-col items-center pt-8 pb-8 w-full md:pt-16 md:pb-16">
        <!-- Changed: remove flex-wrap + enforce stacked mobile / 2-col desktop -->
        <div class="flex overflow-hidden flex-col gap-10 items-center w-full md:flex-row <?php echo $reverse_layout ? 'md:flex-row-reverse' : ''; ?> max-w-[1280px] mx-auto">


            <!-- Images Section (50%) -->
            <div class="flex gap-4 my-auto w-full min-w-0 md:w-1/2" role="img" aria-label="Strategy visual content">

                <!-- Main Image (always 50%) -->
                <?php if ($main_image): ?>
                    <div class="object-contain self-start w-1/2 min-w-0">
                        <?php echo wp_get_attachment_image($main_image, 'full', false, [
                            'alt'     => esc_attr($main_image_alt),
                            'class'   => 'object-contain w-full h-auto',
                            'loading' => 'lazy'
                        ]); ?>
                    </div>
                <?php endif; ?>

                <!-- Secondary Images (always 50%) -->
                <div class="flex flex-col justify-center w-1/2 min-w-0">

                    <?php if ($secondary_image_1): ?>
                        <div class="overflow-hidden w-full bg-gray-50 rounded-none <?php echo $reverse_layout ? 'lg:rounded-tl-2xl' : 'lg:rounded-tr-2xl'; ?>">
                            <?php echo wp_get_attachment_image($secondary_image_1, 'full', false, [
                                'alt'     => esc_attr($secondary_image_1_alt),
                                'class'   => 'object-contain w-full h-auto',
                                'loading' => 'lazy'
                            ]); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($secondary_image_2): ?>
                        <div class="overflow-hidden mt-4 w-full bg-gray-50 rounded-none <?php echo $reverse_layout ? 'lg:rounded-bl-2xl' : 'lg:rounded-br-2xl'; ?>">
                            <?php echo wp_get_attachment_image($secondary_image_2, 'full', false, [
                                'alt'     => esc_attr($secondary_image_2_alt),
                                'class'   => 'object-contain w-full h-auto',
                                'loading' => 'lazy'
                            ]); ?>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

            <!-- Content Section (50%) -->
            <article class="my-auto w-full min-w-0 font-bold text-sky-800 max-lg:px-5 md:w-1/2">

                <?php if (!empty($heading)): ?>
                    <header>
                        <<?php echo esc_attr($heading_tag); ?>
                            id="<?php echo esc_attr($section_id); ?>-heading"
                            class="max-md:max-w-full font-sans text-[30px] font-bold not-italic leading-[38px] text-brand-primary-hover"
                        >
                            <?php echo esc_html($heading); ?>
                        </<?php echo esc_attr($heading_tag); ?>>
                    </header>
                <?php endif; ?>

                <?php if (!empty($content)): ?>
                    <div class="wp_editor mt-4 max-w-[488px] font-sans text-[16px] font-normal not-italic leading-[22px] text-content-body max-md:max-w-full [&_p]:font-sans [&_p]:text-[16px] [&_p]:font-normal [&_p]:not-italic [&_p]:leading-[22px] [&_p]:text-content-body">
                        <?php echo wp_kses_post($content); ?>
                    </div>
                <?php endif; ?>

                <?php if ($button && is_array($button) && isset($button['url'], $button['title'])): ?>
                        <div class="flex pt-4 mt-4 w-full">
                            <a
                                href="<?php echo esc_url($button['url']); ?>"
                                class="inline-flex gap-2 justify-center items-center w-full md:w-fit px-6 py-3 text-sm font-bold text-white leading-5 whitespace-nowrap rounded-pill btn-primary"
                            >
                                <?php echo esc_html($button['title']); ?>
                            </a>
                        </div>
                <?php endif; ?>
               
            </article>

        </div>
    </div>
</section>

<style>
    #<?php echo esc_attr($section_id); ?> .wp_editor ul {
        list-style: none;
        margin: 0;
        padding-left: 0;
    }

    #<?php echo esc_attr($section_id); ?> .wp_editor ul li {
        position: relative;
        padding-left: 24px;
        margin-bottom: 8px;
    }

    #<?php echo esc_attr($section_id); ?> .wp_editor ul li::before {
        content: "";
        position: absolute;
        top: 4px;
        left: 0;
        width: 16px;
        height: 16px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 16 16' fill='none'%3E%3Cpath d='M13.3327 4L5.99935 11.3333L2.66602 8' stroke='%236EC4A9' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
        background-size: 16px 16px;
        background-repeat: no-repeat;
    }
</style>