<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$events_listing = new FieldsBuilder('events_listing', [
    'label' => 'Events Listing with Filters',
]);

$events_listing
    ->addTab('Content', ['label' => 'Content'])
    ->addText('section_heading', [
        'label' => 'Section Heading',
        'instructions' => 'Optional heading for the events section.',
        'default_value' => 'Events',
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
    ->addTrueFalse('show_filters', [
        'label' => 'Show Location Filters',
        'instructions' => 'Display filter pills for event locations.',
        'default_value' => 1,
    ])
    ->addTrueFalse('show_search', [
        'label' => 'Show Search',
        'instructions' => 'Display search functionality.',
        'default_value' => 1,
    ])
    ->addNumber('events_per_page', [
        'label' => 'Events Per Page',
        'instructions' => 'Number of events to display per page.',
        'default_value' => 6,
        'min' => 1,
        'max' => 50,
    ])

    ->addTab('Design', ['label' => 'Design'])
    ->addColorPicker('background_color', [
        'label' => 'Background Color',
        'instructions' => 'Set the background color for the section.',
        'default_value' => '#ffffff',
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
            'default_value' => 'md',
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

return $events_listing;
