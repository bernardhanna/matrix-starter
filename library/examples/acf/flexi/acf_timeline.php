<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$timeline = new FieldsBuilder('timeline', [
    'label' => 'Timeline',
]);

$timeline
    ->addTab('content', ['label' => 'Content'])
        ->addText('heading_text', [
            'label' => 'Heading',
            'default_value' => 'History lorem ipsum dolor h2',
        ])
        ->addSelect('heading_tag', [
            'label' => 'Heading Tag',
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
        ])

        ->addTrueFalse('show_subheading', [
            'label' => 'Show Subheading',
            'ui' => 1,
            'default_value' => 1,
        ])
        ->addText('subheading_text', [
            'label' => 'Subheading',
            'default_value' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
        ])
        ->addSelect('subheading_tag', [
            'label' => 'Subheading Tag',
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
        ])
        ->conditional('show_subheading', '==', 1)

        ->addTrueFalse('show_body', [
            'label' => 'Show Body Text',
            'ui' => 1,
            'default_value' => 1,
        ])
        ->addWysiwyg('body_text', [
            'label' => 'Body Text',
            'instructions' => 'Supports basic formatting.',
            'tabs' => 'all',
            'toolbar' => 'basic',
            'media_upload' => 0,
            'default_value' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris. At vero eos et accusam et justo duo dolores et ea rebum.',
        ])
        ->conditional('show_body', '==', 1)

        ->addRepeater('timeline_items', [
            'label' => 'Timeline Items',
            'instructions' => 'Add events to the timeline. Each item can appear on the left or right of the center line.',
            'button_label' => 'Add Timeline Item',
            'layout' => 'row',
            'min' => 1,
        ])
            ->addSelect('side', [
                'label' => 'Side',
                'choices' => [
                    'left' => 'Left',
                    'right' => 'Right',
                ],
                'default_value' => 'left',
            ])
            ->addDatePicker('event_date', [
                'label' => 'Event Date',
                'instructions' => 'Used for semantic <time datetime="...">.',
                'return_format' => 'Y-m-d',
                'display_format' => 'd.m.Y',
            ])
            ->addText('event_date_label', [
                'label' => 'Event Date Label (optional)',
                'instructions' => 'Overrides the displayed date text (e.g. "2.8.2023"). If empty, the date picker value is used.',
            ])
            ->addText('item_heading', [
                'label' => 'Card Heading',
                'default_value' => 'Headline',
            ])
            ->addSelect('item_heading_tag', [
                'label' => 'Card Heading Tag',
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
            ])
            ->addWysiwyg('item_text', [
                'label' => 'Card Text',
                'tabs' => 'all',
                'toolbar' => 'basic',
                'media_upload' => 0,
                'default_value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            ])
            ->addTrueFalse('show_cta', [
                'label' => 'Show CTA',
                'ui' => 1,
                'default_value' => 1,
            ])
            ->addLink('cta_link', [
                'label' => 'CTA Link',
                'instructions' => 'Uses ACF link array (url/title/target).',
            ])
            ->conditional('show_cta', '==', 1)
        ->endRepeater()

    ->addTab('design', ['label' => 'Design'])
        ->addText('background_color', [
            'label' => 'Background Color',
            'instructions' => 'CSS color value (e.g. rgba(252,244,197,1) or #FCF4C5).',
            'default_value' => 'rgba(252,244,197,1)',
        ])
        ->addText('heading_color', [
            'label' => 'Heading Color',
            'default_value' => '#00628f',
        ])
        ->addText('subheading_color', [
            'label' => 'Subheading Color',
            'default_value' => '#475467',
        ])
        ->addText('body_text_color', [
            'label' => 'Body Text Color',
            'default_value' => '#00263e',
        ])
        ->addText('card_bg_color', [
            'label' => 'Card Background Color',
            'default_value' => '#ffffff',
        ])
        ->addText('card_text_color', [
            'label' => 'Card Text Color',
            'default_value' => '#001929',
        ])
        ->addText('timeline_line_color', [
            'label' => 'Timeline Line Color',
            'default_value' => '#00628f',
        ])
        ->addText('date_text_color', [
            'label' => 'Date Text Color',
            'default_value' => '#00628f',
        ])
        ->addSelect('card_radius', [
            'label' => 'Card Border Radius',
            'instructions' => 'Tailwind radius class for event cards.',
            'choices' => [
                'rounded-none' => 'rounded-none',
                'rounded-sm' => 'rounded-sm',
                'rounded' => 'rounded',
                'rounded-md' => 'rounded-md',
                'rounded-lg' => 'rounded-lg',
                'rounded-xl' => 'rounded-xl',
                'rounded-2xl' => 'rounded-2xl',
                'rounded-3xl' => 'rounded-3xl',
            ],
            'default_value' => 'rounded-none',
        ])

    ->addTab('layout', ['label' => 'Layout'])
        ->addRepeater('padding_settings', [
            'label' => 'Padding Settings',
            'instructions' => 'Customize padding for different screen sizes.',
            'button_label' => 'Add Screen Size Padding',
            'layout' => 'row',
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

return $timeline;