<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

/**
 * Flexi Layout: Hero
 * Matches template-parts/hero/hero.php
 */

$hero = new FieldsBuilder('hero');

$hero
    ->addTab('Content')
        ->addText('kicker', [
            'label' => 'Kicker',
            'instructions' => 'Optional line above the title (e.g. "Running as one.")',
        ])
        ->addWysiwyg('title', [
          'label' => 'Title',
          'instructions' => 'Main heading text',
          'default_value' => 'Running as one.',
           'toolbar' => 'basic',
          'media_upload' => 0,
          'tabs' => 'all',
      ])
        ->addSelect('heading_tag', [
            'label' => 'Heading Tag',
            'choices' => [
                'h1' => 'H1',
                'h2' => 'H2',
                'h3' => 'H3',
            ],
            'default_value' => 'h1',
            'ui' => 1,
            'return_format' => 'value',
        ])
        ->addWysiwyg('description', [
            'label' => 'Description',
            'instructions' => 'Supports formatting. Output is wp_kses_post().',
            'toolbar' => 'basic',
            'media_upload' => 0,
        ])
        ->addLink('primary_cta', [
            'label' => 'Primary CTA',
            'return_format' => 'array',
        ])
        ->addLink('secondary_cta', [
            'label' => 'Secondary CTA',
            'return_format' => 'array',
        ])

    ->addTab('Media')
        ->addSelect('media_type', [
            'label' => 'Media type',
            'choices' => [
                'image' => 'Image',
                'video' => 'Video',
            ],
            'default_value' => 'image',
            'ui' => 1,
            'return_format' => 'value',
        ])
        ->addImage('media_image', [
            'label' => 'Media Image',
            'return_format' => 'id', // your template expects an attachment ID
            'preview_size' => 'medium',
            'library' => 'all',
        ])
            ->conditional('media_type', '==', 'image')
        ->addSelect('media_presentation', [
            'label' => 'Image Presentation',
            'instructions' => 'Choose how image media should fit. Use "Full-height right SVG" for decorative SVGs that should align to the right edge and fill the hero height.',
            'choices' => [
                'default'               => 'Default (cover)',
                'contain'               => 'Contain (centered)',
                'contain_right'         => 'Contain (right aligned)',
                'full_height_right_svg' => 'Full-height right SVG',
            ],
            'default_value' => 'default',
            'ui' => 1,
            'return_format' => 'value',
        ])
            ->conditional('media_type', '==', 'image')
        ->addSelect('video_source', [
            'label' => 'Video source',
            'choices' => [
                'local' => 'Uploaded file (MP4 / WebM)',
                'url' => 'External URL (MP4 / WebM, incl. Amazon S3/CloudFront)',
                'youtube' => 'YouTube',
                'vimeo' => 'Vimeo',
            ],
            'default_value' => 'local',
            'ui' => 1,
            'return_format' => 'value',
            'instructions' => 'Only used when media type is Video.',
        ])
            ->conditional('media_type', '==', 'video')
        ->addFile('video_file', [
            'label' => 'Video file',
            'instructions' => 'MP4 or WebM from the media library.',
            'return_format' => 'array',
            'mime_types' => 'mp4,webm,ogv',
            'library' => 'all',
        ])
            ->conditional('media_type', '==', 'video')
            ->and('video_source', '==', 'local')
        ->addUrl('video_url', [
            'label' => 'External video URL',
            'instructions' => 'Paste a direct MP4/WebM URL (e.g. Amazon S3/CloudFront).',
        ])
            ->conditional('media_type', '==', 'video')
            ->and('video_source', '==', 'url')
        ->addUrl('video_youtube_url', [
            'label' => 'YouTube URL',
            'instructions' => 'Paste a watch, youtu.be, shorts, or embed link.',
        ])
            ->conditional('media_type', '==', 'video')
            ->and('video_source', '==', 'youtube')
        ->addUrl('video_vimeo_url', [
            'label' => 'Vimeo URL',
            'instructions' => 'Paste a vimeo.com video link.',
        ])
            ->conditional('media_type', '==', 'video')
            ->and('video_source', '==', 'vimeo')
        ->addTrueFalse('video_autoplay', [
            'label' => 'Autoplay',
            'ui' => 1,
            'default_value' => 1,
        ])
            ->conditional('media_type', '==', 'video')
        ->addTrueFalse('video_muted', [
            'label' => 'Muted',
            'instructions' => 'Required for autoplay in most browsers.',
            'ui' => 1,
            'default_value' => 1,
        ])
            ->conditional('media_type', '==', 'video')
        ->addTrueFalse('video_loop', [
            'label' => 'Loop',
            'ui' => 1,
            'default_value' => 1,
        ])
            ->conditional('media_type', '==', 'video')
        ->addTrueFalse('video_playsinline', [
            'label' => 'Plays inline (mobile)',
            'instructions' => 'Keeps video inline instead of fullscreen on iOS; recommended with autoplay.',
            'ui' => 1,
            'default_value' => 1,
        ])
            ->conditional('media_type', '==', 'video')
        ->addTrueFalse('video_controls', [
            'label' => 'Show player controls',
            'ui' => 1,
            'default_value' => 0,
        ])
            ->conditional('media_type', '==', 'video')
        ->addText('media_ratio', [
            'label' => 'Media Aspect Ratio Class',
            'instructions' => "Tailwind aspect class, e.g. aspect-[16/9], aspect-square, aspect-[4/3].",
            'default_value' => 'aspect-[16/9]',
        ])

    ->addTab('Background')
        ->addText('background_color', [
            'label' => 'Background Color',
            'instructions' => "CSS color string (supports rgba()) e.g. rgba(0,157,230,1)",
            'default_value' => 'rgba(0,157,230,1)',
        ])
        ->addImage('background_image', [
            'label' => 'Background Image',
            'return_format' => 'array', // your template handles array OR ID
            'preview_size' => 'medium',
            'library' => 'all',
        ])
        ->addTextarea('background_overlay', [
            'label' => 'Background Overlay (optional)',
            'instructions' => "CSS background-image value such as a gradient. Example: linear-gradient(180deg, rgba(0,0,0,.35), rgba(0,0,0,.35))",
            'rows' => 3,
        ])

    ->addTab('Spacing')
        ->addRepeater('padding_settings', [
            'label' => 'Padding Settings (optional)',
            'instructions' => 'If empty, template defaults to: pt/pb 2.5rem and md:pt 5rem md:pb 3rem.',
            'layout' => 'row',
            'min' => 0,
            'button_label' => 'Add breakpoint padding',
        ])
            ->addSelect('screen_size', [
                'label' => 'Screen Size Prefix',
                'choices' => [
                    'sm'  => 'sm',
                    'md'  => 'md',
                    'lg'  => 'lg',
                    'xl'  => 'xl',
                    '2xl' => '2xl',
                ],
                'ui' => 1,
                'return_format' => 'value',
            ])
            ->addNumber('padding_top', [
                'label' => 'Padding Top (rem)',
                'step' => 0.25,
                'min' => 0,
            ])
            ->addNumber('padding_bottom', [
                'label' => 'Padding Bottom (rem)',
                'step' => 0.25,
                'min' => 0,
            ])
        ->endRepeater();

return $hero;