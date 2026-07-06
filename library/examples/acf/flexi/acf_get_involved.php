<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$get_involved = new FieldsBuilder('get_involved', [
    'label' => 'Get Involved Section',
]);

$get_involved
    ->addTab('Content', ['label' => 'Content'])
    ->addText('heading', [
        'label' => 'Section Heading',
        'instructions' => 'Enter the main heading for the get involved section.',
        'default_value' => 'Get involved',
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
    ])
    ->addLink('donate_button', [
        'label' => 'Donate Button',
        'instructions' => 'Configure the donate button link and text.',
        'return_format' => 'array',
    ])
    ->addRepeater('involvement_items', [
        'label' => 'Involvement Items',
        'instructions' => 'Add items that show different ways to get involved.',
        'button_label' => 'Add Involvement Item',
        'min' => 0,
        'max' => 4,
        'layout' => 'block',
    ])
        ->addImage('icon', [
            'label' => 'Icon',
            'instructions' => 'Upload an icon for this involvement item.',
            'return_format' => 'id',
            'preview_size' => 'thumbnail',
        ])
        ->addText('title', [
            'label' => 'Title',
            'instructions' => 'Enter the title for this involvement item.',
        ])
        ->addLink('link', [
            'label' => 'Link',
            'instructions' => 'Add a link for this involvement item (optional).',
            'return_format' => 'array',
        ])
        ->addTextarea('description', [
            'label' => 'Description',
            'instructions' => 'Enter a description for this involvement item.',
            'rows' => 3,
        ])
    ->endRepeater()

    ->addTab('Design', ['label' => 'Design'])
    ->addColorPicker('background_color', [
        'label' => 'Background Color',
        'instructions' => 'Choose the background color for the section.',
        'default_value' => '#1e40af',
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

return $get_involved;
