<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$runners_map = new FieldsBuilder('runners_map', [
    'label' => 'Runners Map',
]);

$runners_map
    ->addTab('Content', [
        'label' => 'Content',
    ])
    ->addText('sr_heading_text', [
        'label' => 'Screen Reader Heading',
        'instructions' => 'Accessible heading for the section.',
        'default_value' => 'Community Connection and Friendship Building',
    ])
    ->addSelect('sr_heading_tag', [
        'label' => 'Screen Reader Heading Tag',
        'instructions' => 'Select the HTML tag for the accessible heading.',
        'choices' => [
            'h1' => 'H1',
            'h2' => 'H2',
            'h3' => 'H3',
            'h4' => 'H4',
            'h5' => 'H5',
            'h6' => 'H6',
            'span' => 'Span',
            'p' => 'Paragraph',
        ],
        'default_value' => 'h1',
    ])
    ->addWysiwyg('description', [
        'label' => 'Description',
        'instructions' => 'Main content below the map.',
        'default_value' => '<p>Every week, people from different backgrounds come together in blue to move, connect and build friendships. Show up, get in touch with the local organiser to say hello, or send us an email to find out more via our <a href="https://sanctuaryrunners.s1.matrix-test.com/contact/">contact us form</a>. You\'ll be met with a warm welcome.</p>',
        'media_upload' => 0,
        'tabs' => 'all',
        'toolbar' => 'full',
    ])
    ->addText('map_aria_label', [
        'label' => 'Map Aria Label',
        'instructions' => 'Accessibility label for the map.',
        'default_value' => 'Interactive map showing runners groups',
    ])
    ->addImage('background_image', [
        'label' => 'Background Image',
        'instructions' => 'Main background image.',
        'return_format' => 'array',
        'preview_size' => 'medium',
    ])
    ->addImage('overlay_image', [
        'label' => 'Overlay Image',
        'instructions' => 'Secondary overlay image.',
        'return_format' => 'array',
        'preview_size' => 'medium',
    ])

    ->addTab('Map Settings', [
        'label' => 'Map Settings',
    ])
    ->addNumber('map_center_lat', [
        'label' => 'Map Center Latitude',
        'default_value' => 53.349805,
        'step' => 0.000001,
    ])
    ->addNumber('map_center_lng', [
        'label' => 'Map Center Longitude',
        'default_value' => -6.26031,
        'step' => 0.000001,
    ])
    ->addNumber('map_zoom', [
        'label' => 'Map Zoom Level',
        'default_value' => 6,
        'min' => 1,
        'max' => 20,
    ])
    ->addSelect('tile_provider', [
        'label' => 'Tile Provider',
        'choices' => [
            'osm' => 'OpenStreetMap',
            'jawg-light' => 'Jawg Light',
            'jawg-dark' => 'Jawg Dark',
        ],
        'default_value' => 'osm',
    ])
    ->addText('tile_api_key', [
        'label' => 'Tile API Key',
        'instructions' => 'Only needed for Jawg providers.',
    ])
    ->addRepeater('map_markers', [
        'label' => 'Map Markers',
        'instructions' => 'Add map markers.',
        'button_label' => 'Add Marker',
    ])
        ->addText('marker_title', [
            'label' => 'Marker Title',
            'default_value' => 'Runner Group',
        ])
        ->addWysiwyg('marker_description', [
            'label' => 'Marker Description',
            'media_upload' => 0,
            'tabs' => 'all',
            'toolbar' => 'basic',
            'default_value' => '<p>Weekly meetup location.</p>',
        ])
        ->addNumber('marker_lat', [
            'label' => 'Marker Latitude',
            'step' => 0.000001,
        ])
        ->addNumber('marker_lng', [
            'label' => 'Marker Longitude',
            'step' => 0.000001,
        ])
        ->addLink('marker_link', [
            'label' => 'Marker Link',
            'return_format' => 'array',
        ])
    ->endRepeater()

    ->addTab('Layout', [
        'label' => 'Layout',
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
        ])
        ->addNumber('padding_bottom', [
            'label' => 'Padding Bottom',
            'instructions' => 'Set the bottom padding in rem.',
            'min' => 0,
            'max' => 20,
            'step' => 0.1,
            'append' => 'rem',
        ])
    ->endRepeater();

return $runners_map;