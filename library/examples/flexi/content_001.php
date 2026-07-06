<?php
$heading         = get_sub_field('heading');
$heading_tag     = get_sub_field('heading_tag') ?: 'h2';
$content         = get_sub_field('content');
$media_type      = get_sub_field('media_type') ?: 'image';
$video_source    = get_sub_field('video_source') ?: 'local';
$video_file      = get_sub_field('video_file');
$video_poster    = get_sub_field('video_poster');
$video_poster_url = $video_poster ? wp_get_attachment_image_url((int) $video_poster, 'full') : '';
$video_youtube_url = trim((string) get_sub_field('video_youtube_url'));
$video_vimeo_url   = trim((string) get_sub_field('video_vimeo_url'));
$image           = get_sub_field('image');
$image_alt       = $image ? (get_post_meta($image, '_wp_attachment_image_alt', true) ?: 'About us image') : '';
$bullet_points   = get_sub_field('bullet_points');
$button          = get_sub_field('button');
$show_button_icon= get_sub_field('show_button_icon');
$reverse_layout  = get_sub_field('reverse_layout');
$background_color= get_sub_field('background_color');

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

$section_id = 'about-us-' . wp_rand(1000, 9999);

$video_embed_url = '';
if ($media_type === 'video') {
    $embed_opts = [
        'autoplay' => false,
        'mute' => false,
        'loop' => false,
        'controls' => true,
        'playsinline' => true,
    ];
    if ($video_source === 'youtube' && $video_youtube_url !== '') {
        $yt_id = function_exists('matrix_hero_parse_youtube_id') ? matrix_hero_parse_youtube_id($video_youtube_url) : '';
        if ($yt_id !== '' && function_exists('matrix_hero_youtube_embed_url')) {
            $video_embed_url = matrix_hero_youtube_embed_url($yt_id, $embed_opts);
        }
    } elseif ($video_source === 'vimeo' && $video_vimeo_url !== '') {
        $vm_id = function_exists('matrix_hero_parse_vimeo_id') ? matrix_hero_parse_vimeo_id($video_vimeo_url) : '';
        if ($vm_id !== '' && function_exists('matrix_hero_vimeo_embed_url')) {
            $video_embed_url = matrix_hero_vimeo_embed_url($vm_id, $embed_opts);
        }
    }
}
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="relative overflow-hidden <?php echo esc_attr(implode(' ', $padding_classes)); ?>"
    style="background-color: <?php echo esc_attr($background_color); ?>;"
    aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
>
    <div class="px-5 mx-auto w-full max-w-container2 md:px-10">

        <div class="grid grid-cols-1 gap-12 items-center py-8 md:py-16 md:grid-cols-2">

            <!-- MEDIA -->
            <?php if (($media_type === 'image' && $image) || ($media_type === 'video' && (($video_source === 'local' && is_array($video_file) && !empty($video_file['url'])) || (($video_source === 'youtube' || $video_source === 'vimeo') && $video_embed_url !== '')))) : ?>
                <div class="<?php echo $reverse_layout ? 'md:order-2' : ''; ?>">
                    <div class="relative overflow-hidden bg-gray-100 rounded-lg">
                        <?php if ($media_type === 'video'): ?>
                            <?php if ($video_source === 'local' && is_array($video_file) && !empty($video_file['url'])): ?>
                                <video
                                    class="w-full h-full object-cover rounded-lg"
                                    controls
                                    playsinline
                                    preload="metadata"
                                    <?php echo $video_poster_url ? 'poster="' . esc_url($video_poster_url) . '"' : ''; ?>
                                >
                                    <source
                                        src="<?php echo esc_url($video_file['url']); ?>"
                                        type="<?php echo esc_attr($video_file['mime_type'] ?? 'video/mp4'); ?>"
                                    >
                                </video>
                            <?php elseif (($video_source === 'youtube' || $video_source === 'vimeo') && $video_embed_url !== ''): ?>
                                <div class="absolute inset-0 overflow-hidden rounded-lg bg-black">
                                    <iframe
                                        class="absolute left-1/2 top-1/2 h-[120%] w-[120%] -translate-x-1/2 -translate-y-1/2"
                                        src="<?php echo esc_url($video_embed_url); ?>"
                                        title="<?php echo esc_attr($heading ?: __('Embedded video', 'matrix-starter')); ?>"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                        allowfullscreen
                                        loading="lazy"
                                    ></iframe>
                                </div>
                                <div class="pt-[56.25%]"></div>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php echo wp_get_attachment_image($image, 'full', false, [
                                'alt'   => esc_attr($image_alt),
                                'class' => 'w-full h-auto object-cover max-md:object-contain',
                            ]); ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- CONTENT -->
            <div class="<?php echo $reverse_layout ? 'md:order-1' : ''; ?> w-full lg:max-w-[460px]">

                <?php if (!empty($heading)): ?>
                    <<?php echo esc_attr($heading_tag); ?>
                        id="<?php echo esc_attr($section_id); ?>-heading"
                        class="text-[36px] font-bold leading-[44px] tracking-[-0.72px] text-[var(--Blue-SR-500,#00628F)]"
                    >
                        <?php echo esc_html($heading); ?>
                    </<?php echo esc_attr($heading_tag); ?>>
                <?php endif; ?>

                <?php if (!empty($content)): ?>
                    <div class="content-001-text mt-6 font-['Public_Sans'] text-[16px] font-normal leading-[22px] text-[var(--Gray-600,#475467)] wp_editor">
                        <?php echo wp_kses_post($content); ?>
                    </div>
                <?php endif; ?>

                <?php if ($bullet_points && have_rows('bullet_points')): ?>
                    <div class="mt-6 space-y-3 font-semibold text-slate-900">
                        <?php while (have_rows('bullet_points')): the_row();
                            $bullet_text = get_sub_field('bullet_text');
                            $bullet_icon = get_sub_field('bullet_icon');
                            if (!$bullet_text) continue;
                        ?>
                            <div class="flex gap-2 items-center px-4 py-2 bg-sky-50 rounded-full w-fit">
                                <?php if ($bullet_icon): ?>
                                    <?php echo wp_get_attachment_image($bullet_icon, 'thumbnail', false, [
                                        'alt' => '',
                                        'class' => 'w-4 h-4',
                                        'aria-hidden' => 'true',
                                    ]); ?>
                                <?php else: ?>
                                    <svg width="13" height="10" viewBox="0 0 13 10" fill="none" aria-hidden="true">
                                        <path d="M11.6667 1L4.33333 8.33333L1 5"
                                            stroke="#009DE6"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"/>
                                    </svg>
                                <?php endif; ?>
                                <span class="font-['Public_Sans'] text-[14px] font-semibold leading-5 text-[var(--Gray-800,#001929)]"><?php echo esc_html($bullet_text); ?></span>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>

                <?php if ($button && is_array($button) && isset($button['url'], $button['title'])): ?>
                    <div class="mt-8">
                        <a
                            href="<?php echo esc_url($button['url']); ?>"
                            class="inline-flex gap-2 justify-center items-center px-6 py-3 mx-auto text-sm font-bold text-white rounded-[100px] w-full md:w-fit border-[3px] border-[var(--Turquoise-500,#1C959B)] bg-[var(--Blue-SR-400,#008BCC)] hover:bg-[var(--Blue-SR-500,#00628F)] hover:text-white focus:outline-none focus-visible:ring-0 focus-visible:border-[3px] focus-visible:border-[var(--Turquoise-500,#1C959B)] focus-visible:bg-[var(--Blue-SR-500,#00628F)] focus-visible:text-white transition-colors duration-200 btn-primary"
                            target="<?php echo esc_attr($button['target'] ?? '_self'); ?>"
                            aria-label="<?php echo esc_attr($button['title']); ?>"
                        >
                            <?php echo esc_html($button['title']); ?>

                            <?php if ($show_button_icon): ?>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path
                                        d="M5 12H19M19 12L12 5M19 12L12 19"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            <?php endif; ?>
                        </a>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</section>

<style>
#<?php echo esc_attr($section_id); ?> .content-001-text p:last-child{
    margin-bottom: 0;
}

#<?php echo esc_attr($section_id); ?> .content-001-text ul {
    list-style: none;
    margin: 0 0 1rem;
    padding: 0;
}

#<?php echo esc_attr($section_id); ?> .content-001-text ul li {
    position: relative;
    padding-left: 1.75rem;
    margin-bottom: 0.75rem;
    color: inherit;
    font: inherit;
}

#<?php echo esc_attr($section_id); ?> .content-001-text ul li::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0.25rem;
    width: 16px;
    height: 16px;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 16 16' fill='none'%3E%3Cpath d='M13.3327 4L5.99935 11.3333L2.66602 8' stroke='%236EC4A9' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-size: 16px 16px;
}
</style>