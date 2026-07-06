<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$assets_links = new FieldsBuilder('assets_links', [
    'label' => 'Assets Links',
]);

$assets_links
    ->addTab('Content', ['label' => 'Content'])
    ->addText('heading', [
        'label' => 'Section Heading',
        'instructions' => 'Enter the main heading shown above the links grid.',
        'default_value' => 'Assets Links',
    ])
    ->addSelect('heading_tag', [
        'label' => 'Heading Tag',
        'instructions' => 'Select the HTML tag for the section heading.',
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
    ->addTrueFalse('center_heading', [
        'label' => 'Center Heading',
        'instructions' => 'Toggle to center-align the heading.',
        'default_value' => 0,
        'ui' => 1,
    ])
    ->addRepeater('asset_items', [
        'label' => 'Asset Items',
        'instructions' => 'Add each asset link item for the grid.',
        'button_label' => 'Add Asset Item',
        'layout' => 'block',
        'min' => 0,
        'max' => 12,
    ])
        ->addImage('icon', [
            'label' => 'Icon',
            'instructions' => 'Upload an icon for this item (optional).',
            'return_format' => 'id',
            'preview_size' => 'thumbnail',
        ])
        ->addText('title', [
            'label' => 'Title',
            'instructions' => 'Enter the title shown for this asset.',
        ])
        ->addLink('link', [
            'label' => 'Link',
            'instructions' => 'Add a link for this asset item.',
            'return_format' => 'array',
            'required' => 1,
        ])
    ->endRepeater()
    ->addTab('Design', ['label' => 'Design'])
    ->addColorPicker('background_color', [
        'label' => 'Background Color',
        'instructions' => 'Choose the background color for the section.',
        'default_value' => '#1e40af',
    ])
    ->addText('background_gradient', [
        'label' => 'Background Gradient',
        'instructions' => 'Paste a CSS gradient value (example: linear-gradient(313deg, #059DED 24.08%, #28B2FA 63%)). If set, this overrides Background Color.',
    ])
    ->addColorPicker('item_text_color', [
        'label' => 'Item Text Color',
        'instructions' => 'Choose the text color used for each item title.',
        'default_value' => '#FFFFFF',
    ])
    ->addSelect('item_text_size', [
        'label' => 'Item Text Size',
        'instructions' => 'Set a slightly larger text size for item titles.',
        'choices' => [
            'base' => 'Base',
            'lg' => 'Large',
            'xl' => 'Extra Large',
        ],
        'default_value' => 'lg',
    ])
    ->addTab('Layout', ['label' => 'Layout'])
    ->addRepeater('padding_settings', [
        'label' => 'Padding Settings',
        'instructions' => 'Customize padding for different screen sizes.',
        'button_label' => 'Add Screen Size Padding',
        'layout' => 'table',
    ])
        ->addSelect('screen_size', [
            'label' => 'Screen Size',
            'instructions' => 'Select the screen size for this padding setting.',
            'choices' => [
                'xxs' => 'XXS',
                'xs' => 'XS',
                'mob' => 'Mobile',
                'sm' => 'Small',
                'md' => 'Medium',
                'lg' => 'Large',
                'xl' => 'Extra Large',
                'xxl' => 'XXL',
                'ultrawide' => 'Ultrawide',
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

return $assets_links;
