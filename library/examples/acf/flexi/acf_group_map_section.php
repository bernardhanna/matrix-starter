<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$group_map_section = new FieldsBuilder('group_map_section', [
    'label' => 'Group Map Section',
]);

$group_map_section
    ->addTab('Content', ['label' => 'Content'])
    ->addText('heading', [
        'label' => 'Main Heading',
        'instructions' => 'Enter the main heading text.',
        'default_value' => 'Find your tribe.',
    ])
    ->addSelect('heading_tag', [
        'label' => 'Heading Tag',
        'instructions' => 'Select the HTML tag for the main heading.',
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
    ->addWysiwyg('description', [
        'label' => 'Description',
        'instructions' => 'Enter the main description text.',
        'default_value' => 'You don\'t have to run alone. Use our map to find a Sanctuary Runners group near you.<br>Whether you want to run, jog, or just walk and have a coffee, there is a place for you here. Simply enter your city or use your location to see where we meet this week.',
        'media_upload' => 0,
        'tabs' => 'all',
        'toolbar' => 'full',
    ])
    ->addText('secondary_heading', [
        'label' => 'Secondary Heading',
        'instructions' => 'Enter the secondary heading text.',
        'default_value' => 'No group in your area?',
    ])
    ->addSelect('secondary_heading_tag', [
        'label' => 'Secondary Heading Tag',
        'instructions' => 'Select the HTML tag for the secondary heading.',
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
        'default_value' => 'h3',
    ])
    ->addLink('start_group_button', [
        'label' => 'Start Group Button',
        'instructions' => 'Configure the "Start a Group Today" button.',
        'return_format' => 'array',
    ])
    ->addLink('find_groups_button', [
        'label' => 'Find Groups Button',
        'instructions' => 'Configure the "Find nearby groups" button.',
        'return_format' => 'array',
    ])
    ->addTab('Map Settings', ['label' => 'Map Settings'])
    ->addNumber('map_center_lat', [
        'label' => 'Map Center Latitude',
        'instructions' => 'Set the default center latitude for the map.',
        'default_value' => 53.349805,
        'step' => 0.000001,
    ])
    ->addNumber('map_center_lng', [
        'label' => 'Map Center Longitude',
        'instructions' => 'Set the default center longitude for the map.',
        'default_value' => -6.26031,
        'step' => 0.000001,
    ])
    ->addNumber('map_zoom', [
        'label' => 'Map Zoom Level',
        'instructions' => 'Set the default zoom level for the map.',
        'default_value' => 10,
        'min' => 1,
        'max' => 20,
    ])
    ->addSelect('tile_provider', [
        'label' => 'Map Tile Provider',
        'instructions' => 'Select the map tile provider.',
        'choices' => [
            'jawg-light' => 'Jawg Light',
            'jawg-dark' => 'Jawg Dark',
            'osm' => 'OpenStreetMap',
        ],
        'default_value' => 'jawg-light',
    ])
    ->addText('tile_api_key', [
        'label' => 'Tile API Key',
        'instructions' => 'Enter the API key for Jawg tiles (if using Jawg provider).',
        'default_value' => 'zxWPtYn9xCoXLAzkN6ckqMOHRw7Xf0zsTWBN0EmR7BSjUMW2F0hsBScanw15iLpX',
    ])
    ->addTab('Design', ['label' => 'Design'])
    ->addColorPicker('background_color', [
        'label' => 'Background Color',
        'instructions' => 'Set the background color for the section.',
        'default_value' => '#fef3c7',
    ])
    ->addTab('Layout', ['label' => 'Layout'])
    ->addTrueFalse('reverse_layout', [
        'label' => 'Reverse Layout',
        'instructions' => 'Swap text and map columns on desktop.',
        'default_value' => 0,
        'ui' => 1,
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

return $group_map_section;
