<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$cta_large_button = new FieldsBuilder('cta_large_button', [
    'label' => 'CTA Large Button',
]);

$cta_large_button
    ->addTab('Content', ['label' => 'Content'])
    ->addText('heading', [
        'label' => 'Heading (optional)',
        'instructions' => 'Optional heading shown above the button.',
        'default_value' => '',
    ])
    ->addSelect('heading_tag', [
        'label' => 'Heading Tag',
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
        'default_value' => 'h3',
    ])
    ->addWysiwyg('description', [
        'label' => 'Description (optional)',
        'instructions' => 'Optional copy shown above the button.',
        'media_upload' => 0,
        'tabs' => 'all',
        'toolbar' => 'basic',
    ])
    ->addLink('button', [
        'label' => 'Button Link',
        'instructions' => 'Primary button link and label.',
        'return_format' => 'array',
        'required' => 1,
    ])
    ->addTrueFalse('show_button_icon', [
        'label' => 'Show Arrow Icon',
        'ui' => 1,
        'default_value' => 1,
    ])
    ->addSelect('alignment', [
        'label' => 'Alignment',
        'choices' => [
            'left' => 'Left',
            'center' => 'Center',
            'right' => 'Right',
        ],
        'default_value' => 'center',
        'ui' => 1,
    ])
    ->addSelect('button_size', [
        'label' => 'Button Size',
        'instructions' => 'Choose the button size style.',
        'choices' => [
            'xsmall' => 'X-Small',
            'medium' => 'Medium',
            'large' => 'Large',
            'full_width' => 'Full Width',
        ],
        'default_value' => 'large',
        'ui' => 1,
    ])

    ->addTab('Design', ['label' => 'Design'])
    ->addColorPicker('background_color', [
        'label' => 'Section Background Color',
        'default_value' => '#ffffff',
    ])

    ->addTab('Layout', ['label' => 'Layout'])
    ->addRepeater('padding_settings', [
        'label' => 'Padding Settings',
        'instructions' => 'Customize vertical spacing per breakpoint.',
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
            'min' => 0,
            'max' => 20,
            'step' => 0.1,
            'append' => 'rem',
            'default_value' => 2,
        ])
        ->addNumber('padding_bottom', [
            'label' => 'Padding Bottom',
            'min' => 0,
            'max' => 20,
            'step' => 0.1,
            'append' => 'rem',
            'default_value' => 2,
        ])
    ->endRepeater();

return $cta_large_button;
