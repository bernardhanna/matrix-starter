<?php
/**
 * Hero block (ACF flexible layout)
 * template-parts/hero/hero.php
 */

if (!defined('ABSPATH')) exit;

// -------------------------------
// IDs
// -------------------------------
$section_id = 'hero-' . wp_generate_uuid4();
$title_id   = $section_id . '-title';
$desc_id    = $section_id . '-desc';

// -------------------------------
// Content (ACF)
// -------------------------------
$kicker        = get_sub_field('kicker');
$title         = get_sub_field('title');
$heading_tag   = get_sub_field('heading_tag') ?: 'h1';
$description   = get_sub_field('description');
$primary_cta   = get_sub_field('primary_cta');
$secondary_cta = get_sub_field('secondary_cta');

// -------------------------------
// Media
// -------------------------------
$media_type        = get_sub_field('media_type') ?: 'image';
$media_image       = get_sub_field('media_image');
$media_ratio_class = get_sub_field('media_ratio') ?: 'aspect-[16/9]';
$media_presentation = get_sub_field('media_presentation') ?: 'default';

$video_source      = get_sub_field('video_source') ?: 'local';
$video_file        = get_sub_field('video_file');
$video_url         = trim((string) get_sub_field('video_url'));
$video_youtube_url = get_sub_field('video_youtube_url');
$video_vimeo_url   = get_sub_field('video_vimeo_url');

$video_autoplay    = false;
$video_muted       = matrix_hero_acf_bool(get_sub_field('video_muted'), true);
$video_loop        = matrix_hero_acf_bool(get_sub_field('video_loop'), true);
$video_playsinline = matrix_hero_acf_bool(get_sub_field('video_playsinline'), true);
$video_controls    = matrix_hero_acf_bool(get_sub_field('video_controls'), false);

$video_embed_url = '';
if ($media_type === 'video') {
    $embed_opts = [
        'autoplay'    => $video_autoplay,
        'mute'        => $video_muted,
        'loop'        => $video_loop,
        'controls'    => $video_controls,
        'playsinline' => $video_playsinline,
    ];
    if ($video_source === 'youtube' && !empty($video_youtube_url)) {
        $yt_id = matrix_hero_parse_youtube_id($video_youtube_url);
        if ($yt_id !== '') {
            $video_embed_url = matrix_hero_youtube_embed_url($yt_id, $embed_opts);
        }
    } elseif ($video_source === 'vimeo' && !empty($video_vimeo_url)) {
        $vm_id = matrix_hero_parse_vimeo_id($video_vimeo_url);
        if ($vm_id !== '') {
            $video_embed_url = matrix_hero_vimeo_embed_url($vm_id, $embed_opts);
        }
    }
}

$allowed_media_presentations = ['default', 'contain', 'contain_right', 'full_height_right_svg'];
if (!in_array($media_presentation, $allowed_media_presentations, true)) {
    $media_presentation = 'default';
}

$is_full_height_right_media = $media_type === 'image' && !empty($media_image) && $media_presentation === 'full_height_right_svg';

$hero_grid_classes = $is_full_height_right_media
    ? 'relative grid grid-cols-1 gap-4 w-full max-[1200px]:px-5 py-[2rem] min-[1201px]:block min-[1201px]:min-h-[500px] min-[1201px]:py-0'
    : 'grid grid-cols-1 gap-6 w-full max-xl:px-5 py-[2rem] min-[1201px]:grid-cols-[36%_64%] min-[1201px]:items-start min-[1201px]:py-0';

$header_classes = $is_full_height_right_media
    ? 'relative z-[2] flex flex-col order-2 gap-4 self-start pr-5 pl-0 min-w-0 min-[1201px]:order-1 min-[1201px]:max-w-[420px]'
    : 'flex flex-col order-2 gap-4 self-start pr-5 pl-0 min-w-0 min-[1201px]:order-1';

$section_media_wrap_classes = $is_full_height_right_media
    ? 'pointer-events-none absolute inset-y-0 right-0 z-[1] hidden min-[1201px]:flex min-[1201px]:w-[68%] min-[1201px]:items-stretch min-[1201px]:justify-end'
    : '';
$section_media_figure_classes = $is_full_height_right_media
    ? 'h-full w-full overflow-hidden'
    : '';

$media_wrap_classes = $is_full_height_right_media
    ? 'flex order-1 justify-end min-w-0 min-[1201px]:hidden'
    : 'flex order-1 justify-center min-w-0 min-[1201px]:order-2';

$media_figure_classes = $is_full_height_right_media
    ? 'relative flex w-full min-h-[260px] items-stretch justify-end overflow-hidden'
    : 'relative overflow-hidden w-full max-w-full h-auto rounded-lg ' . $media_ratio_class;

$image_classes = 'w-full h-full object-cover';
if ($media_presentation === 'contain') {
    $image_classes = 'w-full h-full object-contain';
} elseif ($media_presentation === 'contain_right') {
    $image_classes = 'w-full h-full object-contain object-right';
} elseif ($is_full_height_right_media) {
    $image_classes = 'w-full h-full object-contain object-right';
}

// -------------------------------
// Background
// -------------------------------
$bg_color = get_sub_field('background_color') ?: 'rgba(0,157,230,1)';

// -------------------------------
// Title cleanup
// -------------------------------
$allowed_title_tags = [
    'br'     => [],
    'strong' => [],
    'em'     => [],
    'span'   => ['class' => true],
];

$title_inline = '';
if (!empty($title)) {
    $title_clean = preg_replace('#</?p[^>]*>#i', '', (string) $title);
    $title_clean = preg_replace('#</?div[^>]*>#i', '', (string) $title_clean);
    $title_clean = preg_replace('#</?h[1-6][^>]*>#i', '', (string) $title_clean);
    $title_inline = trim((string) wp_kses($title_clean, $allowed_title_tags));
}

$hero_iframe_title = $title_inline !== ''
    ? wp_strip_all_tags($title_inline)
    : __('Hero video', 'matrix-starter');
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="overflow-hidden relative min-h-[500px] w-full flex items-center min-[1201px]:pb-[50px]"
    style="background-color: <?php echo esc_attr($bg_color); ?>;"
    aria-labelledby="<?php echo esc_attr($title_id); ?>"
>

    <?php if ($is_full_height_right_media && !empty($media_image)): ?>
        <div class="<?php echo esc_attr($section_media_wrap_classes); ?>">
            <figure class="<?php echo esc_attr($section_media_figure_classes); ?>">
                <?php echo wp_get_attachment_image($media_image, 'full', false, [
                    'class' => $image_classes,
                ]); ?>
            </figure>
        </div>
    <?php endif; ?>

    <div class="relative mx-auto mt-5 w-full max-w-container">

        <div class="<?php echo esc_attr($hero_grid_classes); ?>">

            <!-- Text -->
            <header class="<?php echo esc_attr($header_classes); ?>">

                <div class="w-full">

                    <?php if (!empty($kicker)): ?>
                        <p class="text-left text-[1.8125rem] md:text-[2.25rem] font-bold leading-[2.75rem] tracking-[-0.045rem] text-white">
                            <?php echo esc_html($kicker); ?>
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($title_inline)): ?>
                        <<?php echo esc_attr($heading_tag); ?>
                            id="<?php echo esc_attr($title_id); ?>"
                            class="text-left text-[2.25rem] font-light leading-[2.75rem] tracking-[-0.125rem] text-white"
                        >
                            <?php echo $title_inline; ?>
                        </<?php echo esc_attr($heading_tag); ?>>
                    <?php endif; ?>

                </div>

                <?php if (!empty($description)): ?>
                    <div id="<?php echo esc_attr($desc_id); ?>" class="flex flex-col gap-[10px] text-left min-[1201px]:max-w-[303px] text-[18px] leading-6 text-white">
                        <?php echo wp_kses_post($description); ?>
                    </div>
                <?php endif; ?>

                <?php if ($primary_cta || $secondary_cta): ?>
                    <div class="flex gap-6 pt-0 md:pt-4 w-full min-[1201px]:max-w-[400px] md:justify-start">

                        <?php if ($primary_cta): ?>
                            <a
                                href="<?php echo esc_url($primary_cta['url']); ?>"
                                target="<?php echo esc_attr($primary_cta['target'] ?? '_self'); ?>"
                                class="hero-cta inline-flex items-center justify-center gap-2 bg-white rounded-full px-6 py-4 text-[14px] font-bold text-[#00628F]
                                       hover:bg-[var(--Turquoise-50,#CBF3F6)]
                                       active:bg-[var(--Turquoise-100,#75E0E6)]
                                       focus:outline-none focus-visible:ring-0 focus-visible:border-[3px]
                                       focus-visible:border-[var(--Turquoise-500,#1C959B)]
                                       transition-colors duration-200 whitespace-nowrap"
                            >
                                <?php echo esc_html($primary_cta['title']); ?>
                            </a>
                        <?php endif; ?>

                        <?php if ($secondary_cta): ?>
                            <a
                                href="<?php echo esc_url($secondary_cta['url']); ?>"
                                target="<?php echo esc_attr($secondary_cta['target'] ?? '_self'); ?>"
                                class="hero-cta inline-flex items-center justify-center gap-2 rounded-full border border-solid border-white px-6 py-4 text-[14px] font-bold text-white
                                       hover:border-[var(--Yellow-100,#FCF4C5)]
                                       active:border-[var(--Base-White,#FFF)] active:bg-[var(--Blue-SR-400,#008BCC)]
                                       focus:outline-none focus-visible:ring-0 focus-visible:border-[3px] focus-visible:border-solid
                                       focus-visible:border-[var(--Turquoise-500,#1C959B)] focus-visible:bg-[var(--Purple-50,#D9CCE4)]
                                       transition-[border-color,background-color,border-width] duration-200 whitespace-nowrap"
                            >
                                <?php echo esc_html($secondary_cta['title']); ?>
                            </a>
                        <?php endif; ?>

                    </div>
                <?php endif; ?>

            </header>

            <!-- Media -->
            <div class="<?php echo esc_attr($media_wrap_classes); ?>">

                <figure class="<?php echo esc_attr($media_figure_classes); ?>">

                    <?php if ($media_type === 'video'): ?>
                        <?php
                        $video_media_classes = 'absolute inset-0 w-full h-full object-cover';
                        if (
                            ($video_source === 'local' && is_array($video_file) && !empty($video_file['url'])) ||
                            ($video_source === 'url' && $video_url !== '')
                        ) :
                            $video_src = $video_source === 'url' ? $video_url : $video_file['url'];
                            $mime = 'video/mp4';
                            if ($video_source === 'local' && !empty($video_file['mime_type'])) {
                                $mime = (string) $video_file['mime_type'];
                            } elseif ($video_source === 'url') {
                                $filetype = wp_check_filetype((string) $video_src);
                                if (!empty($filetype['type'])) {
                                    $mime = (string) $filetype['type'];
                                }
                            }
                            ?>
                            <video
                                class="<?php echo esc_attr($video_media_classes); ?>"
                                <?php echo $video_controls ? 'controls' : ''; ?>
                                <?php echo $video_autoplay ? 'autoplay' : ''; ?>
                                <?php echo $video_muted ? 'muted' : ''; ?>
                                <?php echo $video_loop ? 'loop' : ''; ?>
                                <?php echo $video_playsinline ? 'playsinline' : ''; ?>
                                preload="metadata"
                            >
                                <source src="<?php echo esc_url($video_src); ?>" type="<?php echo esc_attr($mime); ?>">
                            </video>
                        <?php elseif (($video_source === 'youtube' || $video_source === 'vimeo') && $video_embed_url !== '') : ?>
                            <iframe
                                class="<?php echo esc_attr($video_media_classes); ?>"
                                src="<?php echo esc_url($video_embed_url); ?>"
                                title="<?php echo esc_attr($hero_iframe_title); ?>"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen
                                loading="eager"
                            ></iframe>
                        <?php endif; ?>

                    <?php elseif (!empty($media_image)): ?>
                        <?php echo wp_get_attachment_image($media_image, 'full', false, [
                            'class' => $image_classes
                        ]); ?>
                    <?php endif; ?>

                </figure>

            </div>

        </div>

    </div>

</section>