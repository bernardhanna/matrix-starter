<?php
// Supports both Flexible Content (get_sub_field) and direct template args.
$args = is_array($args ?? null) ? $args : [];
$sf = static function (string $key, $default = null) use ($args) {
    if (array_key_exists($key, $args)) {
        return $args[$key];
    }
    $val = get_sub_field($key);
    return $val !== null ? $val : $default;
};

// Get fields / overrides
$heading            = $sf('heading', '');
$heading_tag        = $sf('heading_tag', 'h1');
$content            = $sf('content', '');
$primary_cta        = $sf('primary_cta');
$secondary_cta      = $sf('secondary_cta');
$primary_cta_icon   = $sf('primary_cta_icon');
$secondary_cta_icon = $sf('secondary_cta_icon');
$media_type         = $sf('media_type', 'image');
$image              = $sf('image');
$video_file         = $sf('video_file');
$video_url          = trim((string) $sf('video_url', ''));
$image_presentation = $sf('image_presentation', 'default');
$image_alt          = $image ? (get_post_meta($image, '_wp_attachment_image_alt', true) ?: 'Featured image') : '';
$background_color   = $sf('background_color', '#EEF6FC');
$use_white_text     = (bool) $sf('use_white_text', false);
$use_white_breadcrumbs = (bool) $sf('use_white_breadcrumbs', $use_white_text);
$custom_breadcrumbs = (bool) $sf('custom_breadcrumbs', false);
$layout_option      = $sf('layout_option', 'layout_1');
$layout_2_desktop_min_height = max(0, (int) $sf('layout_2_desktop_min_height', 300));
$layout_2_container_padding_class = (string) $sf('layout_2_container_padding_class', 'pt-[0rem] pb-0 md:pb-0');
$disable_mobile_absolute_media = (bool) $sf('disable_mobile_absolute_media', false);
$mobile_image_contain = (bool) $sf('mobile_image_contain', false);
$section_extra_classes = trim((string) $sf('section_extra_classes', ''));
$breadcrumbs_arg    = $sf('breadcrumbs');

// Guard against accidentally saved document-style titles (e.g. "Site - Parent - Page")
// so subhero headings on child pages remain the page title only.
if (is_singular()) {
    $heading_text = trim(wp_strip_all_tags((string) $heading));
    $page_title   = trim(wp_strip_all_tags((string) get_the_title()));
    $site_name    = trim(wp_strip_all_tags((string) get_bloginfo('name')));

    if ($heading_text !== '' && $page_title !== '') {
        $heading_parts = preg_split('/\s*[-–|]\s*/u', $heading_text);
        $looks_like_document_title = is_array($heading_parts)
            && count($heading_parts) >= 2
            && strcasecmp(trim((string) $heading_parts[0]), $site_name) === 0
            && strcasecmp(trim((string) end($heading_parts)), $page_title) === 0;

        if ($looks_like_document_title) {
            $heading = $page_title;
        }
    }
}

// If heading is empty for singular entries, use page/post title fallback.
if (is_singular() && trim(wp_strip_all_tags((string) $heading)) === '') {
    $heading = (string) get_the_title();
}

$is_layout_2         = $layout_option === 'layout_2';
$is_dark_background  = strtolower($background_color) !== '#eef6fc';
$layout_2_min_height_class = 'lg:min-h-[var(--layout-2-min-height)]';
$layout_2_min_height_style = "--layout-2-min-height: {$layout_2_desktop_min_height}px;";

$allowed_image_presentations = ['default', 'contain', 'contain_right', 'full_height_right_svg'];
if (!in_array($image_presentation, $allowed_image_presentations, true)) {
    $image_presentation = 'default';
}

$is_video_mode = $media_type === 'video';
$video_src = '';
$video_mime = 'video/mp4';
if ($is_video_mode) {
    if (is_array($video_file) && !empty($video_file['url'])) {
        $video_src = (string) $video_file['url'];
        if (!empty($video_file['mime_type'])) {
            $video_mime = (string) $video_file['mime_type'];
        }
    } elseif ($video_url !== '') {
        $video_src = $video_url;
        $filetype = wp_check_filetype((string) $video_src);
        if (!empty($filetype['type'])) {
            $video_mime = (string) $filetype['type'];
        }
    }
}
$has_video = $is_video_mode && $video_src !== '';
$has_image = !$is_video_mode && !empty($image);
$has_media = $has_image || $has_video;
$is_full_height_right_image = $has_image && $image_presentation === 'full_height_right_svg';

$layout_1_grid_class = $is_full_height_right_image
    ? 'relative grid w-full grid-cols-1 items-center gap-12 max-md:gap-10 md:grid-cols-12 lg:flex lg:min-h-[420px] lg:items-center'
    : 'grid w-full grid-cols-1 items-center gap-12 max-md:gap-10 md:grid-cols-12';
$layout_2_grid_class = $is_full_height_right_image
    ? 'relative grid w-full grid-cols-1 items-center gap-12 max-md:gap-10 md:grid-cols-12 lg:flex lg:items-center ' . $layout_2_min_height_class
    : 'grid w-full grid-cols-1 items-center gap-12 max-md:gap-10 md:grid-cols-12';

$section_media_wrap_class = $is_full_height_right_image
    ? 'pointer-events-none absolute inset-y-0 right-0 z-[1] hidden lg:flex lg:w-[60%] lg:items-stretch lg:justify-end'
    : '';
$section_media_figure_class = $is_full_height_right_image
    ? 'h-full w-full overflow-hidden'
    : '';
$section_media_image_class = $is_full_height_right_image
    ? 'h-full w-full object-contain object-right'
    : '';

$layout_1_media_col_class = $is_full_height_right_image
    ? 'order-1 max-md:absolute max-md:inset-y-0 max-md:right-[-1rem] max-md:h-full max-md:w-full md:order-2 md:col-span-7 lg:hidden'
    : 'order-1 max-md:w-full md:order-2 md:col-span-7';
$layout_2_media_col_class = $is_full_height_right_image
    ? 'order-1 max-md:absolute max-md:inset-y-0 max-md:right-[-1rem] max-md:h-full max-md:w-full md:order-2 md:col-span-6 lg:hidden'
    : 'order-1 max-md:w-full md:order-2 md:col-span-6';

$layout_1_figure_class = $is_full_height_right_image
    ? 'flex h-full w-full min-h-[260px] items-stretch justify-end overflow-hidden'
    : 'w-full max-sm:mt-4';
$layout_2_figure_class = $is_full_height_right_image
    ? ($disable_mobile_absolute_media ? 'w-full h-full' : 'w-full h-full max-md:absolute max-md:top-0 max-md:bottom-0 max-md:right-[-1rem] max-md:h-full')
    : ($disable_mobile_absolute_media ? 'w-full' : 'w-full max-md:absolute max-md:inset-0 max-md:h-full');

$layout_1_text_col_class = $is_full_height_right_image
    ? 'order-2 relative z-[2] flex flex-col justify-center self-center max-md:max-w-full max-lg:pt-24 md:order-1 md:col-span-5 lg:w-full lg:max-w-[460px]'
    : 'order-2 flex flex-col max-md:max-w-full md:order-1 md:col-span-5';
$layout_2_text_col_class = $is_full_height_right_image
    ? 'order-2 relative z-[2] flex flex-col justify-center self-center max-md:max-w-full max-lg:pt-24 md:order-1 md:col-span-6 lg:w-full lg:max-w-[460px]'
    : 'order-2 z-[2] flex flex-col max-md:max-w-full md:order-1 md:col-span-6';

$layout_1_image_class = 'w-full max-h-[522px] h-auto rounded-lg object-cover object-center';
$layout_2_image_class = 'w-full max-h-[300px] h-auto rounded-lg object-contain md:object-right max-md:object-center';
$layout_1_video_class = 'w-full h-full max-h-[522px] rounded-lg object-cover object-center';
$layout_2_video_class = 'w-full h-full max-h-[300px] rounded-lg object-cover object-center';

if ($image_presentation === 'contain') {
    $layout_1_image_class = 'w-full max-h-[522px] h-auto rounded-lg object-contain object-center';
    $layout_2_image_class = 'w-full max-h-[300px] h-auto rounded-lg object-contain object-center max-md:object-center';
} elseif ($image_presentation === 'contain_right') {
    $layout_1_image_class = 'w-full max-h-[522px] h-auto rounded-lg object-contain object-right';
    $layout_2_image_class = 'w-full max-h-[300px] h-auto rounded-lg object-contain md:object-right max-md:object-center';
} elseif ($is_full_height_right_image) {
    $layout_1_image_class = 'h-full w-full object-contain object-right';
    $layout_2_image_class = 'h-full w-full object-contain object-right max-md:object-right';
}

if ($mobile_image_contain) {
    $layout_1_image_class .= ' max-md:object-contain';
    $layout_2_image_class .= ' max-md:object-contain';
}

// CTA icon alts
$primary_cta_icon_alt   = $primary_cta_icon ? (get_post_meta($primary_cta_icon, '_wp_attachment_image_alt', true) ?: '') : '';
$secondary_cta_icon_alt = $secondary_cta_icon ? (get_post_meta($secondary_cta_icon, '_wp_attachment_image_alt', true) ?: '') : '';

// CTA checks
$has_primary_cta = $primary_cta && is_array($primary_cta) && !empty($primary_cta['url']) && !empty($primary_cta['title']);
$has_secondary_cta = $secondary_cta && is_array($secondary_cta) && !empty($secondary_cta['url']) && !empty($secondary_cta['title']);
$has_ctas = $has_primary_cta || $has_secondary_cta;

// Generate breadcrumbs automatically if not custom
$breadcrumbs = [];
if (is_array($breadcrumbs_arg) && !empty($breadcrumbs_arg)) {
    $breadcrumbs = $breadcrumbs_arg;
} elseif ($custom_breadcrumbs && have_rows('breadcrumb_items')) {
    while (have_rows('breadcrumb_items')) {
        the_row();
        $breadcrumbs[] = [
            'title'      => get_sub_field('breadcrumb_title'),
            'url'        => get_sub_field('breadcrumb_url'),
            'is_current' => get_sub_field('is_current_page'),
        ];
    }
} else {
    $breadcrumbs[] = [
        'title'      => 'Home',
        'url'        => home_url('/'),
        'is_current' => false,
    ];

    global $post;
    if ($post && $post->post_parent) {
        $ancestors = get_post_ancestors($post);
        $ancestors = array_reverse($ancestors);

        foreach ($ancestors as $ancestor) {
            $breadcrumbs[] = [
                'title'      => get_the_title($ancestor),
                'url'        => get_permalink($ancestor),
                'is_current' => false,
            ];
        }
    }

    $current_title = is_home() ? 'Home' : get_the_title();
    $breadcrumbs[] = [
        'title'      => $current_title,
        'url'        => '',
        'is_current' => true,
    ];
}

// Text color classes
$text_color_class         = $use_white_text ? 'text-white' : 'text-content-body';
$breadcrumb_text_class    = $use_white_breadcrumbs ? 'text-white' : 'text-gray-500';
$breadcrumb_current_class = $use_white_breadcrumbs ? 'text-white' : 'text-content-body';
$breadcrumb_link_class    = $use_white_breadcrumbs ? 'text-white' : 'text-slate-900';
$breadcrumb_focus_class   = $use_white_breadcrumbs ? 'focus:ring-white' : 'focus:ring-sky-700';
$heading_color_class      = $use_white_text ? 'text-white' : 'text-sky-800';
$content_color_class      = $is_dark_background ? 'text-white [&_p]:!text-white' : 'text-content-body';

// Padding classes
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

// Generate unique section ID
$section_id = 'subhero-' . uniqid();
?>

<?php if (!$is_layout_2): ?>
    <!-- Layout 1 -->
    <section
        id="<?php echo esc_attr($section_id); ?>"
        class="relative flex overflow-hidden <?php echo esc_attr(implode(' ', $padding_classes)); ?> <?php echo esc_attr($section_extra_classes); ?>"
        style="background-color: <?php echo esc_attr($background_color); ?>;"
    >
        <?php if ($is_full_height_right_image && $image): ?>
            <div class="<?php echo esc_attr($section_media_wrap_class); ?>">
                <figure class="<?php echo esc_attr($section_media_figure_class); ?>">
                    <?php
                    echo wp_get_attachment_image($image, 'full', false, [
                        'alt'      => esc_attr($image_alt),
                        'class'    => $section_media_image_class,
                        'loading'  => 'lazy',
                        'decoding' => 'async',
                    ]);
                    ?>
                </figure>
            </div>
        <?php endif; ?>

        <div class="relative pb-6 mx-auto max-md:mt-[3rem] mt-5 w-full max-xl:px-5 max-w-container md:pb-8">
            <div class="<?php echo esc_attr($layout_1_grid_class); ?>">

                <?php if ($has_media): ?>
                    <div class="<?php echo esc_attr($layout_1_media_col_class); ?>">
                        <figure class="<?php echo esc_attr($layout_1_figure_class); ?>">
                            <?php if ($has_video): ?>
                                <video class="<?php echo esc_attr($layout_1_video_class); ?>" muted loop playsinline preload="metadata" controls>
                                    <source src="<?php echo esc_url($video_src); ?>" type="<?php echo esc_attr($video_mime); ?>">
                                </video>
                            <?php else: ?>
                                <?php
                                echo wp_get_attachment_image($image, 'full', false, [
                                    'alt'      => esc_attr($image_alt),
                                    'class'    => $layout_1_image_class,
                                    'loading'  => 'lazy',
                                    'decoding' => 'async',
                                ]);
                                ?>
                            <?php endif; ?>

                            <?php if ($image_alt): ?>
                                <figcaption class="sr-only">
                                    <?php echo esc_html($image_alt); ?>
                                </figcaption>
                            <?php endif; ?>
                        </figure>
                    </div>
                <?php endif; ?>

                <div class="<?php echo esc_attr($layout_1_text_col_class); ?> <?php echo esc_attr($text_color_class); ?>">

                    <?php if (!empty($breadcrumbs)): ?>
                        <nav class="mb-4 max-sm:mb-6" aria-label="Breadcrumb navigation" role="navigation">
                            <ol class="flex min-w-0 flex-nowrap items-center gap-2 overflow-hidden text-sm leading-5 <?php echo esc_attr($breadcrumb_text_class); ?>">
                                <?php foreach ($breadcrumbs as $index => $breadcrumb): ?>
                                    <li class="flex items-center gap-2 <?php echo $breadcrumb['is_current'] ? 'min-w-0 flex-1' : 'shrink-0'; ?>">
                                        <?php if ($breadcrumb['is_current']): ?>
                                            <span
                                                class="<?php echo esc_attr($breadcrumb_current_class); ?> block min-w-0 max-w-full truncate font-['Public_Sans'] text-[12px] leading-[18px] font-normal"
                                                aria-current="page"
                                                title="<?php echo esc_attr($breadcrumb['title']); ?>"
                                            >
                                                <?php echo esc_html($breadcrumb['title']); ?>
                                            </span>
                                        <?php else: ?>
                                            <a
                                                href="<?php echo esc_url($breadcrumb['url']); ?>"
                                                class="<?php echo esc_attr($breadcrumb_link_class); ?> whitespace-nowrap font-['Public_Sans'] text-[12px] leading-[18px] font-bold rounded hover:underline focus:outline-none focus:ring-2 focus:ring-offset-2 <?php echo esc_attr($breadcrumb_focus_class); ?>"
                                            >
                                                <?php echo esc_html($breadcrumb['title']); ?>
                                            </a>
                                        <?php endif; ?>

                                        <?php if ($index < count($breadcrumbs) - 1): ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="6" height="10" viewBox="0 0 6 10" fill="none" aria-hidden="true" class="shrink-0">
                                                <path d="M1 9L5 5L1 1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ol>
                        </nav>
                    <?php endif; ?>

                    <?php if (!empty($heading)): ?>
                        <<?php echo esc_attr($heading_tag); ?>
                            id="<?php echo esc_attr($section_id); ?>-heading"
                            class="mb-6 text-[30px] leading-[38px] font-bold not-italic <?php echo esc_attr($heading_color_class); ?> max-sm:text-4xl max-sm:leading-10"
                        >
                            <?php echo esc_html($heading); ?>
                        </<?php echo esc_attr($heading_tag); ?>>
                    <?php endif; ?>

                    <?php if (!empty($content)): ?>
                        <div
                            class="wp_editor <?php echo esc_attr($content_color_class); ?> font-['Public_Sans'] text-[18px] leading-[24px] font-normal"
                            role="region"
                            aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
                        >
                            <?php echo wp_kses_post($content); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($has_ctas): ?>
                        <div
                            class="flex flex-col flex-wrap gap-4 items-start pt-6 sm:flex-row sm:flex-nowrap"
                            role="group"
                            aria-label="<?php echo esc_attr__('Subhero calls to action', 'matrix-starter'); ?>"
                        >
                            <?php if ($has_primary_cta): ?>
                                <a
                                    href="<?php echo esc_url($primary_cta['url']); ?>"
                                    target="<?php echo esc_attr($primary_cta['target'] ?? '_self'); ?>"
                                    class="hero-cta inline-flex w-fit items-center justify-center gap-2 rounded-pill bg-brand-secondary px-6 py-4 text-center text-[0.875rem] leading-5 font-bold text-white hover:bg-brand-primary-hover hover:text-white focus-visible:ring-offset-2 focus-visible:ring-sky-700 whitespace-nowrap focus:outline-none focus-visible:ring-0 focus-visible:border-[3px] focus-visible:border-brand-accent focus-visible:bg-brand-primary-hover focus-visible:text-white transition-colors duration-200" 
                                    aria-label="<?php echo esc_attr($primary_cta['title']); ?>"
                                >
                                    <?php if ($primary_cta_icon): ?>
                                        <?php
                                        echo wp_get_attachment_image($primary_cta_icon, 'full', false, [
                                            'alt'      => esc_attr($primary_cta_icon_alt),
                                            'class'    => 'w-6 h-6 object-contain shrink-0',
                                            'loading'  => 'lazy',
                                            'decoding' => 'async',
                                        ]);
                                        ?>
                                    <?php endif; ?>

                                    <span><?php echo esc_html($primary_cta['title']); ?></span>
                                </a>
                            <?php endif; ?>

                            <?php if ($has_secondary_cta): ?>
                                <a
                                    href="<?php echo esc_url($secondary_cta['url']); ?>"
                                    target="<?php echo esc_attr($secondary_cta['target'] ?? '_self'); ?>"
                                    class="hero-cta inline-flex w-fit items-center justify-center gap-2 rounded-pill border border-brand-secondary px-6 py-4 text-[0.875rem] leading-5 font-bold text-brand-secondary transition-opacity duration-200 hover:opacity-90 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-sky-700 whitespace-nowrap"
                                    aria-label="<?php echo esc_attr($secondary_cta['title']); ?>"
                                >
                                    <?php if ($secondary_cta_icon): ?>
                                        <?php
                                        echo wp_get_attachment_image($secondary_cta_icon, 'full', false, [
                                            'alt'      => esc_attr($secondary_cta_icon_alt),
                                            'class'    => 'w-6 h-6 object-contain shrink-0',
                                            'loading'  => 'lazy',
                                            'decoding' => 'async',
                                        ]);
                                        ?>
                                    <?php endif; ?>

                                    <span><?php echo esc_html($secondary_cta['title']); ?></span>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </section>

<?php else: ?>
    <!-- Layout 2 -->
    <section
        id="<?php echo esc_attr($section_id); ?>"
        class="relative flex overflow-hidden <?php echo esc_attr(implode(' ', $padding_classes)); ?> <?php echo esc_attr($section_extra_classes); ?>"
        style="background-color: <?php echo esc_attr($background_color); ?>;"
    >
        <?php if ($is_full_height_right_image && $image): ?>
            <div class="<?php echo esc_attr($section_media_wrap_class); ?>">
                <figure class="<?php echo esc_attr($section_media_figure_class); ?>">
                    <?php
                    echo wp_get_attachment_image($image, 'full', false, [
                        'alt'      => esc_attr($image_alt),
                        'class'    => $section_media_image_class,
                        'loading'  => 'lazy',
                        'decoding' => 'async',
                    ]);
                    ?>
                </figure>
            </div>
        <?php endif; ?>

        <div
            class="relative max-xl:px-5 mx-auto flex w-full pb-4 max-w-container <?php echo esc_attr($layout_2_container_padding_class); ?> <?php echo esc_attr($layout_2_min_height_class); ?> lg:items-center"
            style="<?php echo esc_attr($layout_2_min_height_style); ?>"
        >
            <div class="<?php echo esc_attr($layout_2_grid_class); ?>">

                <?php if ($has_media): ?>
                    <div class="<?php echo esc_attr($layout_2_media_col_class); ?>">
                        <figure class="<?php echo esc_attr($layout_2_figure_class); ?>">
                            <?php if ($has_video): ?>
                                <video class="<?php echo esc_attr($layout_2_video_class); ?>" muted loop playsinline preload="metadata" controls>
                                    <source src="<?php echo esc_url($video_src); ?>" type="<?php echo esc_attr($video_mime); ?>">
                                </video>
                            <?php else: ?>
                                <?php
                                echo wp_get_attachment_image($image, 'full', false, [
                                    'alt'      => esc_attr($image_alt),
                                    'class'    => $layout_2_image_class,
                                    'loading'  => 'lazy',
                                    'decoding' => 'async',
                                ]);
                                ?>
                            <?php endif; ?>

                            <?php if ($image_alt): ?>
                                <figcaption class="sr-only">
                                    <?php echo esc_html($image_alt); ?>
                                </figcaption>
                            <?php endif; ?>
                        </figure>
                    </div>
                <?php endif; ?>

                <div class="<?php echo esc_attr($layout_2_text_col_class); ?> <?php echo esc_attr($text_color_class); ?>">

                    <?php if (!empty($breadcrumbs)): ?>
                        <nav class="mt-[3rem] sm:mt-0 mb-4 max-sm:mb-6" aria-label="Breadcrumb navigation" role="navigation">
                            <ol class="flex min-w-0 flex-nowrap items-center gap-2 overflow-hidden text-sm leading-5 <?php echo esc_attr($breadcrumb_text_class); ?>">
                                <?php foreach ($breadcrumbs as $index => $breadcrumb): ?>
                                    <li class="flex items-center gap-2 <?php echo $breadcrumb['is_current'] ? 'min-w-0 flex-1' : 'shrink-0'; ?>">
                                        <?php if ($breadcrumb['is_current']): ?>
                                            <span
                                                class="<?php echo esc_attr($breadcrumb_current_class); ?> block min-w-0 max-w-full truncate font-['Public_Sans'] text-[12px] leading-[18px] font-normal"
                                                aria-current="page"
                                                title="<?php echo esc_attr($breadcrumb['title']); ?>"
                                            >
                                                <?php echo esc_html($breadcrumb['title']); ?>
                                            </span>
                                        <?php else: ?>
                                            <a
                                                href="<?php echo esc_url($breadcrumb['url']); ?>"
                                                class="<?php echo esc_attr($breadcrumb_link_class); ?> whitespace-nowrap font-['Public_Sans'] text-[12px] leading-[18px] font-bold rounded hover:underline focus:outline-none focus:ring-2 focus:ring-offset-2 <?php echo esc_attr($breadcrumb_focus_class); ?>"
                                            >
                                                <?php echo esc_html($breadcrumb['title']); ?>
                                            </a>
                                        <?php endif; ?>

                                        <?php if ($index < count($breadcrumbs) - 1): ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="6" height="10" viewBox="0 0 6 10" fill="none" aria-hidden="true" class="shrink-0">
                                                <path d="M1 9L5 5L1 1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ol>
                        </nav>
                    <?php endif; ?>

                    <?php if (!empty($heading)): ?>
                        <<?php echo esc_attr($heading_tag); ?>
                            id="<?php echo esc_attr($section_id); ?>-heading"
                            class="mb-6 text-[30px] leading-[38px] font-bold not-italic <?php echo esc_attr($heading_color_class); ?> max-sm:text-4xl max-sm:leading-10"
                        >
                            <?php echo esc_html($heading); ?>
                        </<?php echo esc_attr($heading_tag); ?>>
                    <?php endif; ?>

                    <?php if (!empty($content)): ?>
                        <div
                            class="wp_editor <?php echo esc_attr($content_color_class); ?> font-['Public_Sans'] text-[18px] leading-[24px] font-normal"
                            role="region"
                            aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
                        >
                            <?php echo wp_kses_post($content); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($has_ctas): ?>
                        <div
                            class="flex flex-col flex-wrap gap-4 items-start pt-6 sm:flex-row sm:flex-nowrap"
                            role="group"
                            aria-label="<?php echo esc_attr__('Subhero calls to action', 'matrix-starter'); ?>"
                        >
                            <?php if ($has_primary_cta): ?>
                                <a
                                    href="<?php echo esc_url($primary_cta['url']); ?>"
                                    target="<?php echo esc_attr($primary_cta['target'] ?? '_self'); ?>"
                                    class="hero-cta inline-flex w-fit items-center justify-center gap-2 rounded-pill bg-brand-secondary px-6 py-4 text-center text-[0.875rem] leading-5 font-bold text-white transition-opacity duration-200 hover:opacity-90 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-sky-700 whitespace-nowrap"
                                    aria-label="<?php echo esc_attr($primary_cta['title']); ?>"
                                >
                                    <?php if ($primary_cta_icon): ?>
                                        <?php
                                        echo wp_get_attachment_image($primary_cta_icon, 'full', false, [
                                            'alt'      => esc_attr($primary_cta_icon_alt),
                                            'class'    => 'w-6 h-6 object-contain shrink-0',
                                            'loading'  => 'lazy',
                                            'decoding' => 'async',
                                        ]);
                                        ?>
                                    <?php endif; ?>

                                    <span><?php echo esc_html($primary_cta['title']); ?></span>
                                </a>
                            <?php endif; ?>

                            <?php if ($has_secondary_cta): ?>
                                <a
                                    href="<?php echo esc_url($secondary_cta['url']); ?>"
                                    target="<?php echo esc_attr($secondary_cta['target'] ?? '_self'); ?>"
                                    class="hero-cta inline-flex w-fit items-center justify-center gap-2 rounded-pill border border-brand-secondary px-6 py-4 text-[0.875rem] leading-5 font-bold text-brand-secondary transition-opacity duration-200 hover:opacity-90 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-sky-700 whitespace-nowrap"
                                    aria-label="<?php echo esc_attr($secondary_cta['title']); ?>"
                                >
                                    <?php if ($secondary_cta_icon): ?>
                                        <?php
                                        echo wp_get_attachment_image($secondary_cta_icon, 'full', false, [
                                            'alt'      => esc_attr($secondary_cta_icon_alt),
                                            'class'    => 'w-6 h-6 object-contain shrink-0',
                                            'loading'  => 'lazy',
                                            'decoding' => 'async',
                                        ]);
                                        ?>
                                    <?php endif; ?>

                                    <span><?php echo esc_html($secondary_cta['title']); ?></span>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </section>
<?php endif; ?>