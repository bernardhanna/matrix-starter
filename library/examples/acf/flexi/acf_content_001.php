<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$content_001 = new FieldsBuilder('content_001', [
    'label' => 'About Us Section',
]);

$content_001
    ->addTab('Content', ['label' => 'Content'])
    ->addText('heading', [
        'label' => 'Heading Text',
        'instructions' => 'Set the main heading text for the section.',
        'default_value' => 'Solidarity. Friendship. Respect.',
    ])
    ->addSelect('heading_tag', [
        'label' => 'Heading Tag',
        'instructions' => 'Choose the HTML tag for the heading.',
        'choices' => [
            'h1' => 'H1',
            'h2' => 'H2',
            'h3' => 'H3',
            'h4' => 'H4',
            'h5' => 'H5',
            'h6' => 'H6',
            'p' => 'Paragraph',
            'span' => 'Span',
        ],
        'default_value' => 'h2',
    ])
    ->addWysiwyg('content', [
        'label' => 'Main Content',
        'instructions' => 'Add the main descriptive content for the section.',
        'default_value' => '<p>We believe that when we run together, we grow together. Sanctuary Runners isn\'t just about fitness; it\'s about belonging. We break down barriers between people living in Direct Provision and their local neighbors, one step at a time.</p>',
        'media_upload' => 0,
        'tabs' => 'all',
        'toolbar' => 'full',
    ])
    ->addSelect('media_type', [
        'label' => 'Media Type',
        'instructions' => 'Choose whether this section uses an image or a video.',
        'choices' => [
            'image' => 'Image',
            'video' => 'Video',
        ],
        'default_value' => 'image',
        'ui' => 1,
    ])
    ->addImage('image', [
        'label' => 'Section Image',
        'instructions' => 'Upload an image for the section.',
        'return_format' => 'id',
        'preview_size' => 'medium',
    ])
        ->conditional('media_type', '==', 'image')
    ->addSelect('video_source', [
        'label' => 'Video Source',
        'instructions' => 'Choose where the video should load from.',
        'choices' => [
            'local' => 'Local upload',
            'youtube' => 'YouTube',
            'vimeo' => 'Vimeo',
        ],
        'default_value' => 'local',
        'ui' => 1,
    ])
        ->conditional('media_type', '==', 'video')
    ->addFile('video_file', [
        'label' => 'Video File',
        'instructions' => 'Upload a local MP4/WebM video.',
        'return_format' => 'array',
        'library' => 'all',
        'mime_types' => 'mp4,webm,mov',
    ])
        ->conditional('media_type', '==', 'video')
        ->conditional('video_source', '==', 'local')
    ->addImage('video_poster', [
        'label' => 'Video Poster',
        'instructions' => 'Optional. Displayed before the video plays (local uploads only).',
        'return_format' => 'id',
        'preview_size' => 'medium',
        'library' => 'all',
    ])
        ->conditional('media_type', '==', 'video')
        ->conditional('video_source', '==', 'local')
    ->addUrl('video_youtube_url', [
        'label' => 'YouTube URL',
        'instructions' => 'Paste a YouTube URL (watch, share, shorts, or embed).',
    ])
        ->conditional('media_type', '==', 'video')
        ->conditional('video_source', '==', 'youtube')
    ->addUrl('video_vimeo_url', [
        'label' => 'Vimeo URL',
        'instructions' => 'Paste a Vimeo URL.',
    ])
        ->conditional('media_type', '==', 'video')
        ->conditional('video_source', '==', 'vimeo')
    ->addRepeater('bullet_points', [
        'label' => 'Key Points',
        'instructions' => 'Add key points with optional icons.',
        'button_label' => 'Add Key Point',
        'min' => 0,
        'max' => 5,
    ])
        ->addText('bullet_text', [
            'label' => 'Point Text',
            'instructions' => 'Enter the text for this key point.',
            'required' => 1,
        ])
        ->addImage('bullet_icon', [
            'label' => 'Point Icon',
            'instructions' => 'Optional: Upload a custom icon for this point. If left empty, a default checkmark will be used.',
            'return_format' => 'id',
            'preview_size' => 'thumbnail',
        ])
    ->endRepeater()
    ->addLink('button', [
        'label' => 'Call to Action Button',
        'instructions' => 'Add a button link for the section.',
        'return_format' => 'array',
    ])
    ->addTrueFalse('show_button_icon', [
        'label' => 'Show Button Icon',
        'instructions' => 'Display an arrow icon next to the button text.',
        'default_value' => 1,
    ])

    ->addTab('Design', ['label' => 'Design'])
    ->addColorPicker('background_color', [
        'label' => 'Background Color',
        'instructions' => 'Choose the background color for the section.',
        'default_value' => '#F2F4F7',
    ])

    ->addTab('Layout', ['label' => 'Layout'])
    ->addTrueFalse('reverse_layout', [
        'label' => 'Reverse Layout',
        'instructions' => 'Toggle to switch the image and content positions.',
        'default_value' => 0,
    ])
    ->addRepeater('padding_settings', [
        'label' => 'Padding Settings',
        'instructions' => 'Customize padding for different screen sizes.',
        'button_label' => 'Add Screen Size Padding',
    ])
        ->addSelect('screen_size', [
            'label' => 'Screen Size',
            'choices' => [
                'xxs' => 'xxs',
                'xs' => 'xs',
                'mob' => 'mob',
                'sm' => 'sm',
                'md' => 'md',
                'lg' => 'lg',
                'xl' => 'xl',
                'xxl' => 'xxl',
                'ultrawide' => 'ultrawide',
            ],
        ])
        ->addNumber('padding_top', [
            'label' => 'Padding Top',
            'instructions' => 'Set the top padding in rem.',
            'min' => 0,
            'max' => 20,
            'step' => 0.1,
            'append' => 'rem',
            'default_value' => 5,
        ])
        ->addNumber('padding_bottom', [
            'label' => 'Padding Bottom',
            'instructions' => 'Set the bottom padding in rem.',
            'min' => 0,
            'max' => 20,
            'step' => 0.1,
            'append' => 'rem',
            'default_value' => 5,
        ])
    ->endRepeater();

return $content_001;
