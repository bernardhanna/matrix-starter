<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$partners = new FieldsBuilder('partners', [
    'label' => 'Partners',
]);

$partners
    ->addTab('content', [
        'label' => 'Content',
        'placement' => 'top',
    ])

        ->addText('heading_text', [
            'label' => 'Heading Text',
            'instructions' => 'Main heading text.',
            'default_value' => 'A growing global movement',
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

        ->addText('subheading_text', [
            'label' => 'Subheading Text',
            'instructions' => 'Supporting text under the heading.',
            'default_value' => 'From Cork to London to Brighton, we are expanding. We are proud to work alongside our partners who believe in a more inclusive world.',
        ])

        ->addSelect('subheading_tag', [
            'label' => 'Subheading Tag',
            'instructions' => 'Choose the HTML tag for the subheading.',
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
            'default_value' => 'h4',
            'return_format' => 'value',
        ])

        ->addRepeater('logos', [
            'label' => 'Partner Logos',
            'instructions' => 'Add partner logos (image alt/title pulled from Media Library).',
            'layout' => 'row',
            'button_label' => 'Add Logo',
            'min' => 1,
        ])
            ->addImage('logo_image', [
                'label' => 'Logo Image',
                'return_format' => 'array',
                'preview_size' => 'medium',
            ])
            ->addLink('logo_link', [
                'label' => 'Logo Link',
                'instructions' => 'Optional. If set, the logo becomes a link.',
                'return_format' => 'array',
            ])
            ->addSelect('logo_card_style', [
                'label' => 'Logo Card Style',
                'instructions' => 'Choose card background style for this logo slide.',
                'choices' => [
                    'white' => 'White',
                    'translucent' => 'Translucent White',
                    'transparent' => 'Transparent',
                ],
                'default_value' => 'white',
                'ui' => 1,
                'return_format' => 'value',
            ])
        ->endRepeater()
        ->addTrueFalse('enable_logo_slider', [
            'label' => 'Enable Logo Slider',
            'instructions' => 'Show logos in a slider with swipe support and optional autoplay.',
            'default_value' => 0,
            'ui' => 1,
        ])
        ->addSelect('logo_visual_style', [
            'label' => 'Logo Visual Style',
            'instructions' => 'Control how logos are visually rendered.',
            'choices' => [
                'original' => 'Original Colors',
                'muted' => 'Muted (Blend/Luminosity)',
                'white' => 'White Logos (No Card Background)',
            ],
            'default_value' => 'original',
            'ui' => 1,
            'return_format' => 'value',
        ])
        ->addTrueFalse('show_slider_arrows', [
            'label' => 'Show Slider Arrows',
            'default_value' => 1,
            'ui' => 1,
            'conditional_logic' => [[['field' => 'enable_logo_slider', 'operator' => '==', 'value' => 1]]],
        ])
        ->addTrueFalse('show_mobile_dots', [
            'label' => 'Show Mobile Dots',
            'default_value' => 0,
            'ui' => 1,
            'conditional_logic' => [[['field' => 'enable_logo_slider', 'operator' => '==', 'value' => 1]]],
        ])
        ->addTrueFalse('slider_autoplay', [
            'label' => 'Autoplay',
            'default_value' => 1,
            'ui' => 1,
            'conditional_logic' => [[['field' => 'enable_logo_slider', 'operator' => '==', 'value' => 1]]],
        ])
        ->addNumber('slider_autoplay_speed', [
            'label' => 'Autoplay Speed (ms)',
            'default_value' => 3000,
            'min' => 1000,
            'step' => 100,
            'conditional_logic' => [[['field' => 'slider_autoplay', 'operator' => '==', 'value' => 1]]],
        ])
        ->addNumber('slider_start_delay', [
            'label' => 'Autoplay Start Delay (ms)',
            'instructions' => 'Delay before first auto-slide starts. Use 0 for immediate.',
            'default_value' => 500,
            'min' => 0,
            'step' => 100,
            'conditional_logic' => [[['field' => 'slider_autoplay', 'operator' => '==', 'value' => 1]]],
        ])
        ->addNumber('slider_transition_speed', [
            'label' => 'Slide Transition Speed (ms)',
            'instructions' => 'Duration of slide animation.',
            'default_value' => 450,
            'min' => 100,
            'max' => 3000,
            'step' => 50,
            'conditional_logic' => [[['field' => 'enable_logo_slider', 'operator' => '==', 'value' => 1]]],
        ])
        ->addNumber('slider_slides_desktop', [
            'label' => 'Desktop Slides To Show',
            'default_value' => 5,
            'min' => 1,
            'max' => 8,
            'step' => 1,
            'conditional_logic' => [[['field' => 'enable_logo_slider', 'operator' => '==', 'value' => 1]]],
        ])

    ->addTab('design', [
        'label' => 'Design',
        'placement' => 'top',
    ])
        ->addColorPicker('background_color', [
            'label' => 'Background Color',
            'default_value' => '#009DE6',
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

return $partners;