<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$content_003 = new FieldsBuilder('content_003', [
    'label' => 'Start New Group Sectio -reversible content',
]);

$content_003
    ->addTab('Content', ['label' => 'Content'])
    ->addText('heading', [
        'label' => 'Heading Text',
        'instructions' => 'Enter the main heading for the section.',
        'default_value' => 'Start a new group',
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
    ->addText('description', [
        'label' => 'Description',
        'instructions' => 'Enter the description text below the heading.',
        'default_value' => 'Don\'t see a group near you? You can start one!',
    ])
    ->addWysiwyg('content', [
        'label' => 'Main Content',
        'instructions' => 'Add the main content text for the section.',
        'default_value' => '<p>We\'re always excited to support new communities across Ireland. If you\'re interested in bringing Sanctuary Runners to your area, fill in our contact form and we\'ll guide you through each step.</p>',
        'media_upload' => 0,
        'tabs' => 'all',
        'toolbar' => 'full',
    ])
    ->addRepeater('key_points', [
        'label' => 'Key Points',
        'instructions' => 'Add key benefit points that will be displayed as bullet points.',
        'button_label' => 'Add Key Point',
        'min' => 0,
        'max' => 6,
    ])
        ->addText('point_text', [
            'label' => 'Point Text',
            'instructions' => 'Enter the text for this key point.',
            'required' => 1,
        ])
    ->endRepeater()
    ->addImage('image', [
        'label' => 'Section Image',
        'instructions' => 'Upload an image for the section.',
        'return_format' => 'id',
        'preview_size' => 'medium',
    ])
    ->addLink('primary_button', [
        'label' => 'Primary Button',
        'instructions' => 'Configure the primary call-to-action button.',
        'return_format' => 'array',
    ])
    ->addLink('secondary_button', [
        'label' => 'Secondary Button',
        'instructions' => 'Configure the secondary call-to-action button.',
        'return_format' => 'array',
    ])

    ->addTab('Design', ['label' => 'Design'])
    ->addColorPicker('background_color', [
        'label' => 'Background Color',
        'instructions' => 'Set the background color for the section.',
        'default_value' => '#FFFFFF',
    ])

    ->addTab('Layout', ['label' => 'Layout'])
    ->addTrueFalse('reverse_layout', [
        'label' => 'Reverse Layout',
        'instructions' => 'Toggle to switch the image and text positions (image on left instead of right).',
        'default_value' => 0,
        'ui' => 1,
    ])
    ->addRepeater('padding_settings', [
        'label' => 'Padding Settings',
        'instructions' => 'Customize padding for different screen sizes.',
        'button_label' => 'Add Screen Size Padding',
        'min' => 0,
        'max' => 9,
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
            'required' => 1,
        ])
        ->addNumber('padding_top', [
            'label' => 'Padding Top',
            'instructions' => 'Set the top padding in rem units.',
            'min' => 0,
            'max' => 20,
            'step' => 0.1,
            'append' => 'rem',
            'default_value' => 2,
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

return $content_003;
