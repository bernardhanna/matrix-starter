<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$subhero = new FieldsBuilder('subhero', [
    'label' => 'Subpage hero Section',
]);

$subhero
    ->addTab('Content', ['label' => 'Content'])
    ->addText('heading', [
        'label' => 'Heading Text',
        'instructions' => 'Optional. If left empty, this falls back to the current page/post title.',
        'default_value' => '',
        'required' => 0,
    ])
    ->addSelect('heading_tag', [
        'label' => 'Heading HTML Tag',
        'instructions' => 'Select the appropriate heading tag for SEO and accessibility.',
        'choices' => [
            'h1' => 'H1',
            'h2' => 'H2',
            'h3' => 'H3',
            'h4' => 'H4',
            'h5' => 'H5',
            'h6' => 'H6',
            'p'  => 'Paragraph',
            'span' => 'Span',
        ],
        'default_value' => 'h1',
        'required' => 1,
    ])
    ->addWysiwyg('content', [
        'label' => 'Content Description',
        'instructions' => 'Add the descriptive content for the subhero section.',
        'default_value' => '',
        'media_upload' => 0,
        'tabs' => 'all',
        'toolbar' => 'full',
    ])

    ->addLink('primary_cta', [
        'label' => 'Primary CTA',
        'instructions' => 'Add the primary call to action button.',
        'return_format' => 'array',
    ])
    ->addImage('primary_cta_icon', [
        'label' => 'Primary CTA Icon',
        'instructions' => 'Optional icon displayed before the primary CTA label.',
        'return_format' => 'id',
        'preview_size' => 'thumbnail',
        'library' => 'all',
    ])

    ->addLink('secondary_cta', [
        'label' => 'Secondary CTA',
        'instructions' => 'Add the secondary call to action button.',
        'return_format' => 'array',
    ])
    ->addImage('secondary_cta_icon', [
        'label' => 'Secondary CTA Icon',
        'instructions' => 'Optional icon displayed before the secondary CTA label.',
        'return_format' => 'id',
        'preview_size' => 'thumbnail',
        'library' => 'all',
    ])
    ->addSelect('media_type', [
        'label' => 'Subhero Media Type',
        'instructions' => 'Choose whether this subhero should display an image or a video.',
        'choices' => [
            'image' => 'Image',
            'video' => 'Video',
        ],
        'default_value' => 'image',
        'ui' => 1,
    ])

    ->addImage('image', [
        'label' => 'Featured Image',
        'instructions' => 'Upload an image to display alongside the content.',
        'return_format' => 'id',
        'preview_size' => 'medium',
        'library' => 'all',
        'conditional_logic' => [
            [
                [
                    'field' => 'media_type',
                    'operator' => '==',
                    'value' => 'image',
                ],
            ],
        ],
    ])
    ->addFile('video_file', [
        'label' => 'Video File',
        'instructions' => 'Upload MP4 or WebM video for subhero media.',
        'return_format' => 'array',
        'mime_types' => 'mp4,webm',
        'library' => 'all',
        'conditional_logic' => [
            [
                [
                    'field' => 'media_type',
                    'operator' => '==',
                    'value' => 'video',
                ],
            ],
        ],
    ])
    ->addUrl('video_url', [
        'label' => 'External Video URL',
        'instructions' => 'Optional external MP4/WebM URL (used if no file is selected).',
        'conditional_logic' => [
            [
                [
                    'field' => 'media_type',
                    'operator' => '==',
                    'value' => 'video',
                ],
            ],
        ],
    ])
    ->addSelect('image_presentation', [
        'label' => 'Image Presentation',
        'instructions' => 'Choose how the image should fit. Use "Full-height right SVG" for decorative SVGs that should align to the right edge and fill the hero height.',
        'choices' => [
            'default'               => 'Default',
            'contain'               => 'Contain (centered)',
            'contain_right'         => 'Contain (right aligned)',
            'full_height_right_svg' => 'Full-height right SVG',
        ],
        'default_value' => 'default',
        'required' => 0,
        'ui' => 1,
        'conditional_logic' => [
            [
                [
                    'field' => 'media_type',
                    'operator' => '==',
                    'value' => 'image',
                ],
            ],
        ],
    ])
    ->addTrueFalse('custom_breadcrumbs', [
        'label' => 'Use Custom Breadcrumbs',
        'instructions' => 'Enable to manually configure breadcrumb navigation. When disabled, breadcrumbs will be generated automatically based on page hierarchy.',
        'default_value' => 0,
        'ui' => 1,
    ])
    ->addRepeater('breadcrumb_items', [
        'label' => 'Custom Breadcrumb Items',
        'instructions' => 'Add custom breadcrumb navigation items.',
        'button_label' => 'Add Breadcrumb Item',
        'min' => 1,
        'max' => 10,
        'conditional_logic' => [
            [
                [
                    'field' => 'custom_breadcrumbs',
                    'operator' => '==',
                    'value' => '1',
                ],
            ],
        ],
    ])
        ->addText('breadcrumb_title', [
            'label' => 'Breadcrumb Title',
            'instructions' => 'Enter the text for this breadcrumb item.',
            'required' => 1,
        ])
        ->addUrl('breadcrumb_url', [
            'label' => 'Breadcrumb URL',
            'instructions' => 'Enter the URL for this breadcrumb item. Leave empty for current page.',
        ])
        ->addTrueFalse('is_current_page', [
            'label' => 'Is Current Page',
            'instructions' => 'Mark this as the current page (will not be linked).',
            'default_value' => 0,
            'ui' => 1,
        ])
    ->endRepeater()

    ->addTab('Design', ['label' => 'Design'])
    ->addColorPicker('background_color', [
        'label' => 'Background Color',
        'instructions' => 'Choose the background color for the subhero section.',
        'default_value' => '#EEF6FC',
    ])
    ->addTrueFalse('use_white_text', [
        'label' => 'Use White Text',
        'instructions' => 'Enable to change all text to white for dark backgrounds.',
        'default_value' => 0,
        'ui' => 1,
    ])
    ->addTrueFalse('use_white_breadcrumbs', [
        'label' => 'Use White Breadcrumbs',
        'instructions' => 'Enable to render breadcrumb text and icons in white.',
        'default_value' => 0,
        'ui' => 1,
    ])

    ->addTab('Layout', ['label' => 'Layout'])
    ->addSelect('layout_option', [
        'label' => 'Layout Option',
        'instructions' => 'Choose which subhero layout to use.',
        'choices' => [
            'layout_1' => 'Layout 1',
            'layout_2' => 'Layout 2',
        ],
        'default_value' => 'layout_1',
        'required' => 1,
        'ui' => 1,
    ])
    ->addRepeater('padding_settings', [
        'label' => 'Padding Settings',
        'instructions' => 'Customize padding for different screen sizes.',
        'button_label' => 'Add Screen Size Padding',
        'min' => 0,
        'max' => 10,
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
            'default_value' => 4,
        ])
        ->addNumber('padding_bottom', [
            'label' => 'Padding Bottom',
            'instructions' => 'Set the bottom padding in rem units.',
            'min' => 0,
            'max' => 20,
            'step' => 0.1,
            'append' => 'rem',
            'default_value' => 4,
        ])
    ->endRepeater();

return $subhero;