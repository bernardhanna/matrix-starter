<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$upcoming_events = new FieldsBuilder('upcoming_events', [
    'label' => 'Upcoming Events',
]);

$upcoming_events
    ->addTab('Content', ['label' => 'Content'])
    ->addText('section_heading', [
        'label' => 'Section Heading',
        'instructions' => 'Enter the heading text for the events section.',
        'default_value' => 'Upcoming events',
        'required' => 1,
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
        'required' => 1,
    ])
    ->addLink('view_all_button', [
        'label' => 'View All Events Button',
        'instructions' => 'Configure the "View all events" button link.',
        'return_format' => 'array',
        'required' => 0,
    ])
    ->addNumber('number_of_events', [
        'label' => 'Number of Events to Display',
        'instructions' => 'Set how many upcoming events to show (default: 3).',
        'default_value' => 3,
        'min' => 1,
        'max' => 12,
        'step' => 1,
        'required' => 1,
    ])
    ->addTab('Layout', ['label' => 'Layout'])
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
        'default_value' => 5,
        'required' => 1,
    ])
    ->addNumber('padding_bottom', [
        'label' => 'Padding Bottom',
        'instructions' => 'Set the bottom padding in rem units.',
        'min' => 0,
        'max' => 20,
        'step' => 0.1,
        'append' => 'rem',
        'default_value' => 5,
        'required' => 1,
    ])
    ->endRepeater();

return $upcoming_events;
