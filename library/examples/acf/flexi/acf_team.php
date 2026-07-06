<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$team = new FieldsBuilder('team', [
    'label' => 'Team',
]);

$team
    ->addTab('content', ['label' => 'Content'])
        ->addText('heading_text', [
            'label' => 'Heading',
            'instructions' => 'Main section heading.',
            'default_value' => 'Meet our team',
        ])
        ->addSelect('heading_tag', [
            'label' => 'Heading Tag',
            'instructions' => 'Choose the semantic tag for the heading.',
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
        ->addTrueFalse('show_intro', [
            'label' => 'Show Intro Text',
            'ui' => 1,
            'default_value' => 1,
        ])
        ->addWysiwyg('intro_text', [
            'label' => 'Intro Text',
            'instructions' => 'Short supporting text under the heading.',
            'default_value' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit sed do eiusmod tempor incididunt.',
            'tabs' => 'all',
            'toolbar' => 'basic',
            'media_upload' => 0,
        ])
        ->conditional('show_intro', '==', 1)

        ->addAccordion('people_query_settings', [
            'label' => 'People Query Settings',
            'open' => 1,
            'multi_expand' => 0,
        ])
            ->addTrueFalse('use_manual_people', [
                'label' => 'Manually Select People',
                'instructions' => 'Enable to choose specific People posts instead of auto-query.',
                'ui' => 1,
                'default_value' => 0,
            ])
            ->addRelationship('manual_people', [
                'label' => 'Select People',
                'instructions' => 'Choose People posts to display (order is preserved).',
                'post_type' => ['people'],
                'return_format' => 'id',
                'filters' => ['search'],
                'max' => 120,
            ])
            ->conditional('use_manual_people', '==', 1)

            ->addNumber('posts_per_page', [
                'label' => 'People Count',
                'instructions' => 'How many People posts to display when not using manual selection.',
                'default_value' => 4,
                'min' => 1,
                'max' => 12,
                'step' => 1,
            ])
            ->conditional('use_manual_people', '!=', 1)

            ->addTaxonomy('role_filter', [
                'label' => 'Filter by Role (people_role)',
                'instructions' => 'Optional: show only People in selected roles.',
                'taxonomy' => 'people_role',
                'field_type' => 'multi_select',
                'add_term' => 0,
                'save_terms' => 0,
                'load_terms' => 0,
                'return_format' => 'id',
            ])
            ->conditional('use_manual_people', '!=', 1)

            ->addSelect('orderby', [
                'label' => 'Order By',
                'choices' => [
                    'date' => 'date',
                    'title' => 'title',
                    'menu_order' => 'menu_order',
                    'rand' => 'rand',
                ],
                'default_value' => 'date',
            ])
            ->conditional('use_manual_people', '!=', 1)

            ->addSelect('order', [
                'label' => 'Order',
                'choices' => [
                    'DESC' => 'DESC',
                    'ASC' => 'ASC',
                ],
                'default_value' => 'DESC',
            ])
            ->conditional('use_manual_people', '!=', 1)
        ->addAccordion('people_query_settings_end')->endpoint()

    ->addTab('design', ['label' => 'Design'])
        ->addText('background_color', [
            'label' => 'Background Color',
            'instructions' => 'CSS color value (e.g. rgba(242,244,247,1) or #F2F4F7).',
            'default_value' => 'rgba(242,244,247,1)',
        ])
        ->addText('heading_color', [
            'label' => 'Heading Text Color',
            'instructions' => 'CSS color value for the heading.',
            'default_value' => '#00628f',
        ])
        ->addText('body_text_color', [
            'label' => 'Body Text Color',
            'instructions' => 'CSS color value for paragraph/name/role text.',
            'default_value' => '#00263e',
        ])
        ->addSelect('image_radius', [
            'label' => 'Image Border Radius',
            'instructions' => 'Tailwind radius class for thumbnails.',
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
        ])

    ->addTab('layout', ['label' => 'Layout'])
        ->addSelect('desktop_columns', [
            'label' => 'Desktop Grid Columns',
            'instructions' => 'Choose the number of columns on desktop screens.',
            'choices' => [
                '4' => '4 columns',
                '3' => '3 columns',
            ],
            'default_value' => '4',
            'ui' => 1,
        ])
        ->addTrueFalse('show_content_snippet', [
            'label' => 'Show Post Content Snippet',
            'instructions' => 'Show a short excerpt from the team member post content under the role line.',
            'ui' => 1,
            'default_value' => 1,
        ])
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

return $team;