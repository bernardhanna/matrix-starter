<?php

$section_id = 'content-editor-' . wp_generate_uuid4();

$heading_text = get_sub_field('heading_text');
$heading_tag = get_sub_field('heading_tag');

$show_subheading = get_sub_field('show_subheading');
$subheading_wysiwyg = get_sub_field('subheading_wysiwyg');

$column_1 = get_sub_field('column_1');
$column_2 = get_sub_field('column_2');
$show_column_3 = get_sub_field('show_column_3');
$column_3 = get_sub_field('column_3');

$background_color = get_sub_field('background_color');
$heading_color = get_sub_field('heading_color');
$subheading_color = get_sub_field('subheading_color');
$body_text_color = get_sub_field('body_text_color');

$padding_classes = [];
if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
        the_row();
        $screen_size = get_sub_field('screen_size');
        $padding_top = get_sub_field('padding_top');
        $padding_bottom = get_sub_field('padding_bottom');

        if ($screen_size !== '' && $padding_top !== '' && $padding_top !== null) {
            $padding_classes[] = $screen_size . ':pt-[' . $padding_top . 'rem]';
        }
        if ($screen_size !== '' && $padding_bottom !== '' && $padding_bottom !== null) {
            $padding_classes[] = $screen_size . ':pb-[' . $padding_bottom . 'rem]';
        }
    }
}
$padding_class_string = implode(' ', $padding_classes);

$allowed_tags = ['h1','h2','h3','h4','h5','h6','span','p'];
if (!in_array($heading_tag, $allowed_tags, true)) {
    $heading_tag = 'h3';
}

$heading_id = $section_id . '-heading';
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="flex overflow-hidden relative max-md:overflow-visible"
    <?php if (!empty(trim((string) $heading_text))): ?>aria-labelledby="<?php echo esc_attr($heading_id); ?>"<?php endif; ?>
    style="background-color: <?php echo esc_attr($background_color); ?>;"
>
    <div class="flex flex-col items-center w-full mx-auto max-w-container py-6 lg:pt-10  lg:pb-12 max-xl:px-5 <?php echo esc_attr($padding_class_string); ?>">
        <div class="w-full max-w-[60rem] flex flex-col gap-6">
            <?php if (!empty(trim((string) $heading_text))): ?>
                <<?php echo esc_html($heading_tag); ?>
                    id="<?php echo esc_attr($heading_id); ?>"
                    class="w-full break-words text-left text-[1.5rem] font-[700] leading-[2rem] font-['Public Sans']"
                    style="color: <?php echo esc_attr($heading_color); ?>;"
                >
                    <?php echo esc_html($heading_text); ?>
                </<?php echo esc_html($heading_tag); ?>>
            <?php endif; ?>

            <?php if (!empty($show_subheading) && !empty($subheading_wysiwyg)) { ?>
                <div
                    class="wp_editor content-editor-rich w-full break-words text-left text-[1.25rem] font-[400] leading-[1.625rem] font-['Public Sans'] mb-0 [&_p]:mb-0"
                    style="color: <?php echo esc_attr($subheading_color); ?>;"
                >
                    <?php echo wp_kses_post($subheading_wysiwyg); ?>
                </div>
            <?php } ?>

            <div class="flex flex-col gap-6 w-full md:gap-14 md:flex-row md:items-start">
                <?php if (!empty($column_1)) { ?>
                    <div
                        class="wp_editor content-editor-rich w-full md:w-1/2 whitespace-normal break-words text-left text-[1rem] font-[400] leading-[1.375rem] font-['Public Sans']"
                        style="color: <?php echo esc_attr($body_text_color); ?>;"
                    >
                        <?php echo wp_kses_post($column_1); ?>
                    </div>
                <?php } ?>

                <?php if (!empty($column_2)) { ?>
                    <div
                        class="wp_editor content-editor-rich w-full md:w-1/2 whitespace-normal break-words text-left text-[1rem] font-[400] leading-[1.375rem] font-['Public Sans']"
                        style="color: <?php echo esc_attr($body_text_color); ?>;"
                    >
                        <?php echo wp_kses_post($column_2); ?>
                    </div>
                <?php } ?>
            </div>

            <?php if (!empty($show_column_3) && !empty($column_3)) { ?>
                <div
                    class="wp_editor content-editor-rich w-full whitespace-normal break-words text-left text-[1rem] font-[400] leading-[1.375rem] font-['Public Sans']"
                    style="color: <?php echo esc_attr($body_text_color); ?>;"
                >
                    <?php echo wp_kses_post($column_3); ?>
                </div>
            <?php } ?>
        </div>
    </div>
</section>

<style>
    /* Scoped typography + bullet styling for this specific content editor block */
    #<?php echo esc_attr($section_id); ?> .content-editor-rich h1,
    #<?php echo esc_attr($section_id); ?> .content-editor-rich h2,
    #<?php echo esc_attr($section_id); ?> .content-editor-rich h3 {
        width: 100%;
        word-break: break-word;
        text-align: left;
        font-family: "Public Sans", sans-serif;
        font-size: 1.5rem;
        font-style: normal;
        font-weight: 700;
        line-height: 2rem;
        letter-spacing: 0;
        color: #00263E;
        margin: 0 0 16px;
    }

    #<?php echo esc_attr($section_id); ?> .content-editor-rich h4 {
        font-family: "Public Sans", sans-serif;
        font-size: 16px;
        font-style: normal;
        font-weight: 700;
        line-height: 22px;
        letter-spacing: 0;
        color: #00263E;
        margin: 0 0 10px;
    }

    #<?php echo esc_attr($section_id); ?> .content-editor-rich ul {
        list-style: none;
        margin: 0 0 16px;
        padding: 0;
    }

    #<?php echo esc_attr($section_id); ?> .content-editor-rich ul li {
        position: relative;
        margin: 0 0 12px;
        padding-left: 28px;
    }

    #<?php echo esc_attr($section_id); ?> .content-editor-rich ul li::before {
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