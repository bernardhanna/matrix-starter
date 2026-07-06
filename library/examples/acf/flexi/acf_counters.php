<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$counters = new FieldsBuilder('counters', [
    'label' => 'Counters Section',
]);

$counters
    ->addTab('content', [
        'label' => 'Content',
        'placement' => 'top',
    ])

        ->addTrueFalse('show_section', [
            'label' => 'Show Section',
            'instructions' => 'Toggle to show/hide this entire section.',
            'ui' => 1,
            'default_value' => 1,
        ])

        ->addText('heading_text', [
            'label' => 'Heading Text',
            'instructions' => 'Main heading text.',
            'default_value' => 'Solidarity in Numbers',
        ])

        ->addSelect('heading_tag', [
            'label' => 'Heading Tag',
            'instructions' => 'Choose the HTML tag for the heading.',
            'choices' => [
                'h1' => 'h1',
                'h2' => 'h2',
                'h3' => 'h3',
                'h4' => 'h4',
                'h5' => 'h5',
                'h6' => 'h6',
                'span' => 'span',
                'p' => 'p',
            ],
            'default_value' => 'h3',
            'return_format' => 'value',
        ])

        ->addWysiwyg('description', [
            'label' => 'Description',
            'instructions' => 'Intro text under the heading.',
            'media_upload' => 0,
            'toolbar' => 'basic',
            'default_value' => 'We are committed to the highest standards of governance and financial transparency. Your support builds lasting legacies of integration.',
        ])

        ->addRepeater('stats', [
            'label' => 'Stats',
            'instructions' => 'Add stats (recommended: 3). Value counts up on scroll.',
            'min' => 1,
            'layout' => 'row',
            'button_label' => 'Add Stat',
        ])
            ->addTrueFalse('show_stat', [
                'label' => 'Show Stat',
                'ui' => 1,
                'default_value' => 1,
            ])
            ->addSelect('value_source', [
                'label' => 'Value Source',
                'instructions' => 'Choose whether this stat uses a manual number or an auto-updating running groups count.',
                'choices' => [
                    'manual' => 'Manual value',
                    'running_groups_count' => 'Running groups count (auto)',
                ],
                'default_value' => 'manual',
                'return_format' => 'value',
            ])
            ->addNumber('value', [
                'label' => 'Value',
                'instructions' => 'Numeric value that will count up (e.g. 43, 10000, 92).',
                'min' => 0,
                'step' => 1,
                'default_value' => 43,
                'conditional_logic' => [
                    [
                        [
                            'field' => 'value_source',
                            'operator' => '==',
                            'value' => 'manual',
                        ],
                    ],
                ],
            ])
            ->addText('suffix', [
                'label' => 'Suffix',
                'instructions' => 'Suffix shown after the number (e.g. +, %, etc).',
                'default_value' => '+',
            ])
            ->addSelect('format', [
                'label' => 'Number Format',
                'instructions' => 'How the number should display during counting.',
                'choices' => [
                    'number' => 'number',
                    'compact' => 'compact',
                ],
                'default_value' => 'number',
                'return_format' => 'value',
            ])
            ->addTextarea('stat_text', [
                'label' => 'Stat Description',
                'instructions' => 'Short supporting text under the number.',
                'rows' => 2,
                'new_lines' => '',
                'default_value' => 'Groups across Ireland and expanding globally',
            ])
        ->endRepeater()

        ->addImage('image', [
            'label' => 'Image',
            'instructions' => 'Right-side image.',
            'return_format' => 'array',
            'preview_size' => 'medium',
        ])

        ->addText('image_fallback_alt', [
            'label' => 'Image Fallback Alt',
            'instructions' => 'Used if the media library alt text is empty.',
            'default_value' => 'Section image',
        ])

    ->addTab('design', [
        'label' => 'Design',
        'placement' => 'top',
    ])

        ->addColorPicker('background_color', [
            'label' => 'Background Color',
            'default_value' => '#ffffff',
        ])

        ->addColorPicker('heading_color', [
            'label' => 'Heading Color',
            'default_value' => '#00628f',
        ])

        ->addColorPicker('text_color', [
            'label' => 'Text Color',
            'default_value' => '#00263e',
        ])

        ->addColorPicker('stat_number_color', [
            'label' => 'Stat Number Color',
            'default_value' => '#008fc5',
        ])

        ->addColorPicker('stat_text_color', [
            'label' => 'Stat Text Color',
            'default_value' => '#475467',
        ])

        ->addColorPicker('image_bg_color', [
            'label' => 'Image Background Color',
            'default_value' => '#f2f4f7',
        ])

        ->addSelect('image_radius', [
            'label' => 'Image Border Radius',
            'instructions' => 'Controls the image wrapper radius.',
            'choices' => [
                'rounded-none' => 'rounded-none',
                'rounded-sm' => 'rounded-sm',
                'rounded' => 'rounded',
                'rounded-md' => 'rounded-md',
                'rounded-lg' => 'rounded-lg',
                'rounded-xl' => 'rounded-xl',
                'rounded-2xl' => 'rounded-2xl',
                'rounded-3xl' => 'rounded-3xl',
                'rounded-full' => 'rounded-full',
            ],
            'default_value' => 'rounded-none',
            'return_format' => 'value',
        ])

    ->addTab('layout', [
        'label' => 'Layout',
        'placement' => 'top',
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

return $counters;