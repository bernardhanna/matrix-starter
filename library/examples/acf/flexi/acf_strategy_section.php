<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$strategy_section = new FieldsBuilder('strategy_section', [
    'label' => 'Strategy Section',
]);

$strategy_section
    ->addTab('Content', ['label' => 'Content'])
    ->addText('heading', [
        'label' => 'Heading Text',
        'instructions' => 'Enter the main heading for the strategy section.',
        'default_value' => 'Strategy/vision h2',
        'required' => 1,
    ])
    ->addSelect('heading_tag', [
        'label' => 'Heading Tag',
        'instructions' => 'Select the HTML tag for the heading.',
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
    ->addWysiwyg('content', [
        'label' => 'Content',
        'instructions' => 'Enter the main content text for the strategy section.',
        'default_value' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris. At vero eos et accusam et justo duo dolores et ea rebum.<br><br>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris. Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris. At vero eos et accusam et justo duo dolores et ea rebum.',
        'media_upload' => 0,
        'tabs' => 'all',
        'toolbar' => 'full',
        'required' => 1,
    ])
    ->addImage('main_image', [
        'label' => 'Main Image',
        'instructions' => 'Upload the large main image for the strategy section.',
        'return_format' => 'id',
        'preview_size' => 'medium',
        'library' => 'all',
        'required' => 1,
    ])
    ->addImage('secondary_image_1', [
        'label' => 'Secondary Image 1',
        'instructions' => 'Upload the first secondary image (top right).',
        'return_format' => 'id',
        'preview_size' => 'medium',
        'library' => 'all',
        'required' => 1,
    ])
    ->addImage('secondary_image_2', [
        'label' => 'Secondary Image 2',
        'instructions' => 'Upload the second secondary image (bottom right).',
        'return_format' => 'id',
        'preview_size' => 'medium',
        'library' => 'all',
        'required' => 1,
    ])
    ->addLink('button', [
        'label' => 'Call to Action Button',
        'instructions' => 'Add a link for the call to action button.',
        'return_format' => 'array',
        'required' => 0,
    ])

    ->addTab('Design', ['label' => 'Design'])
    ->addColorPicker('background_color', [
        'label' => 'Background Color',
        'instructions' => 'Choose the background color for the section.',
        'default_value' => '#f3f4f6',
    ])

    ->addTab('Layout', ['label' => 'Layout'])
    ->addTrueFalse('reverse_layout', [
        'label' => 'Reverse Layout',
        'instructions' => 'Swap image and content columns on desktop.',
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

return $strategy_section;
