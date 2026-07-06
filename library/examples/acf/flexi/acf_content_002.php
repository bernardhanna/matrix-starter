<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$content_002 = new FieldsBuilder('content_002', [
    'label' => 'Content with Image or Video',
]);

$content_002
    ->addTab('Content', ['label' => 'Content'])
    ->addText('heading', [
        'label' => 'Heading Text',
        'instructions' => 'Enter the main heading for this section.',
        'default_value' => 'Lorem ipsum dolor h3',
        'required' => 1,
    ])
    ->addSelect('heading_tag', [
        'label' => 'Heading Tag',
        'instructions' => 'Select the appropriate HTML heading tag for semantic structure.',
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
        'required' => 1,
    ])
    ->addWysiwyg('description', [
        'label' => 'Description Text',
        'instructions' => 'Enter the description text that appears below the heading.',
        'default_value' => '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.</p>',
        'media_upload' => 0,
        'tabs' => 'all',
        'toolbar' => 'full',
    ])
    ->addWysiwyg('body_content', [
        'label' => 'Body Content',
        'instructions' => 'Enter the main body content for the left column.',
        'default_value' => '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris. At vero eos et accusam et justo duo dolores et ea rebum.</p><br><p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris. Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris. At vero eos et accusam et justo duo dolores et ea rebum.</p>',
        'media_upload' => 1,
        'tabs' => 'all',
        'toolbar' => 'full',
    ])
    ->addLink('content_button', [
        'label' => 'Optional Button',
        'instructions' => 'Optional CTA button shown below the body content.',
        'return_format' => 'array',
    ])
    ->addSelect('media_type', [
        'label' => 'Media Type',
        'instructions' => 'Choose whether the right column uses an image or a video.',
        'choices' => [
            'image' => 'Image',
            'video' => 'Video',
        ],
        'default_value' => 'image',
        'ui' => 1,
    ])
    ->addImage('image', [
        'label' => 'Content Image',
        'instructions' => 'Upload an image for the right column. Recommended size: 448x448 pixels or larger.',
        'return_format' => 'id',
        'preview_size' => 'medium',
        'library' => 'all',
    ])
        ->conditional('media_type', '==', 'image')
    ->addFile('video_file', [
        'label' => 'Video File',
        'instructions' => 'Upload a local MP4/WebM video for the right column.',
        'return_format' => 'array',
        'library' => 'all',
        'mime_types' => 'mp4,webm,mov',
    ])
        ->conditional('media_type', '==', 'video')
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
    ->addImage('video_poster', [
        'label' => 'Video Poster',
        'instructions' => 'Optional. Displayed before the video plays.',
        'return_format' => 'id',
        'preview_size' => 'medium',
        'library' => 'all',
    ])
        ->conditional('media_type', '==', 'video')
        ->conditional('video_source', '==', 'local')

    ->addTab('Design', ['label' => 'Design'])
    ->addColorPicker('background_color', [
        'label' => 'Background Color',
        'instructions' => 'Set the background color for this section.',
        'default_value' => '#FFFFFF',
    ])

    ->addTab('Layout', ['label' => 'Layout'])
    ->addTrueFalse('reverse_layout', [
        'label' => 'Reverse Layout',
        'instructions' => 'Swap the body content and image columns on desktop.',
        'default_value' => 0,
        'ui' => 1,
    ])
    ->addRepeater('padding_settings', [
        'label' => 'Padding Settings',
        'instructions' => 'Customize padding for different screen sizes.',
        'button_label' => 'Add Screen Size Padding',
        'min' => 0,
        'max' => 10,
        'layout' => 'table',
    ])
    ->addSelect('screen_size', [
        'label' => 'Screen Size',
        'instructions' => 'Select the screen size for this padding setting.',
        'choices' => [
            'xxs' => 'XXS (Extra Extra Small)',
            'xs' => 'XS (Extra Small)',
            'mob' => 'Mobile',
            'sm' => 'SM (Small)',
            'md' => 'MD (Medium)',
            'lg' => 'LG (Large)',
            'xl' => 'XL (Extra Large)',
            'xxl' => 'XXL (Extra Extra Large)',
            'ultrawide' => 'Ultrawide',
        ],
        'required' => 1,
    ])
    ->addNumber('padding_top', [
        'label' => 'Padding Top',
        'instructions' => 'Set the top padding in rem units.',
        'min' => 0,
        'max' => 20,
        'step' => 0.1,
        'append' => 'rem',
        'default_value' => 5,
    ])
    ->addNumber('padding_bottom', [
        'label' => 'Padding Bottom',
        'instructions' => 'Set the bottom padding in rem units.',
        'min' => 0,
        'max' => 20,
        'step' => 0.1,
        'append' => 'rem',
        'default_value' => 5,
    ])
    ->endRepeater();

return $content_002;
