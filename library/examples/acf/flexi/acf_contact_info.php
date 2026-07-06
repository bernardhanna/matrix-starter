<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$contact_info = new FieldsBuilder('contact_info', [
    'label' => 'Contact Info Section',
]);

$contact_info
    ->addTab('Content', ['label' => 'Content'])
    ->addText('heading', [
        'label' => 'Section Heading',
        'instructions' => 'Enter the main heading for the contact section.',
        'default_value' => 'Contact us',
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
    ->addRepeater('contact_locations', [
        'label' => 'Contact Locations',
        'instructions' => 'Add contact information for different office locations.',
        'button_label' => 'Add Location',
        'min' => 0,
        'max' => 6,
        'layout' => 'block',
    ])
        ->addText('office_name', [
            'label' => 'Office Name',
            'instructions' => 'Enter the name of the office (e.g., "Dublin Head Office").',
        ])
        ->addWysiwyg('address', [
            'label' => 'Address',
            'instructions' => 'Enter the full address for this location.',
            'media_upload' => 0,
            'tabs' => 'visual',
            'toolbar' => 'basic',
        ])
        ->addText('phone', [
            'label' => 'Phone Number',
            'instructions' => 'Enter the phone number for this location.',
        ])
        ->addEmail('email', [
            'label' => 'Email Address',
            'instructions' => 'Enter the email address for this location.',
        ])
        ->addLink('directions_link', [
            'label' => 'Directions Link',
            'instructions' => 'Add a link to directions (e.g., Google Maps).',
            'return_format' => 'array',
        ])
    ->endRepeater()

    ->addTab('Layout', ['label' => 'Layout'])
    ->addTrueFalse('use_two_columns_for_two_items', [
        'label' => 'Use 2 columns when only 2 items',
        'instructions' => 'If enabled, desktop layout switches to 2 columns when exactly 2 contact locations are added.',
        'ui' => 1,
        'default_value' => 1,
    ])
    ->addRepeater('padding_settings', [
        'label' => 'Padding Settings',
        'instructions' => 'Customize padding for different screen sizes.',
        'button_label' => 'Add Screen Size Padding',
        'layout' => 'table',
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
            'default_value' => 2.5,
        ])
        ->addNumber('padding_bottom', [
            'label' => 'Padding Bottom',
            'instructions' => 'Set the bottom padding in rem.',
            'min' => 0,
            'max' => 20,
            'step' => 0.1,
            'append' => 'rem',
            'default_value' => 3.5,
        ])
    ->endRepeater();

return $contact_info;
