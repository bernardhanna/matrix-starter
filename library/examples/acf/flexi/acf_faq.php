<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$faq = new FieldsBuilder('faq', [
    'label' => 'FAQ Section',
]);

$faq
    ->addTab('Content', ['label' => 'Content'])
    ->addText('heading', [
        'label' => 'Heading Text',
        'instructions' => 'Set the main heading for the FAQ section.',
        'default_value' => 'Frequently asked questions',
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
    ->addText('subtitle', [
        'label' => 'Subtitle',
        'instructions' => 'Optional subtitle text.',
        'default_value' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
    ])
    ->addWysiwyg('description', [
        'label' => 'Description',
        'instructions' => 'Additional description text below the subtitle.',
        'default_value' => '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris. Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.</p>',
        'media_upload' => 0,
        'tabs' => 'all',
        'toolbar' => 'full',
    ])
    ->addSelect('faq_source', [
        'label' => 'FAQ Source',
        'instructions' => 'Choose how to populate the FAQ items.',
        'choices' => [
            'manual' => 'Manual Entry',
            'posts' => 'Select FAQ Posts',
            'all' => 'All FAQ Posts',
        ],
        'default_value' => 'manual',
    ])
    ->addRepeater('manual_faqs', [
        'label' => 'Manual FAQ Items',
        'instructions' => 'Add FAQ items manually.',
        'button_label' => 'Add FAQ Item',
        'conditional_logic' => [
            [
                [
                    'field' => 'faq_source',
                    'operator' => '==',
                    'value' => 'manual',
                ],
            ],
        ],
    ])
        ->addText('question', [
            'label' => 'Question',
            'instructions' => 'Enter the FAQ question.',
            'default_value' => 'Frequently Asked Question',
        ])
        ->addWysiwyg('answer', [
            'label' => 'Answer',
            'instructions' => 'Enter the FAQ answer.',
            'default_value' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>',
            'media_upload' => 0,
            'tabs' => 'all',
            'toolbar' => 'full',
        ])
        ->addTrueFalse('show_read_more', [
            'label' => 'Show Read More Link',
            'instructions' => 'Display a "Read more" link below the answer.',
            'default_value' => 1,
        ])
    ->endRepeater()
    ->addPostObject('selected_faqs', [
        'label' => 'Select FAQ Posts',
        'instructions' => 'Choose specific FAQ posts to display.',
        'post_type' => ['faq'],
        'return_format' => 'object',
        'multiple' => 1,
        'conditional_logic' => [
            [
                [
                    'field' => 'faq_source',
                    'operator' => '==',
                    'value' => 'posts',
                ],
            ],
        ],
    ])
    ->addLink('button', [
        'label' => 'More FAQs Button',
        'instructions' => 'Optional button to link to more FAQ content.',
        'return_format' => 'array',
    ])

    ->addTab('Design', ['label' => 'Design'])
    ->addColorPicker('background_color', [
        'label' => 'Background Color',
        'instructions' => 'Set the background color for the FAQ section.',
        'default_value' => '#e0f2fe',
    ])

    ->addTab('Layout', ['label' => 'Layout'])
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
            'default_value' => 5,
        ])
        ->addNumber('padding_bottom', [
            'label' => 'Padding Bottom',
            'instructions' => 'Set the bottom padding in rem.',
            'min' => 0,
            'max' => 20,
            'step' => 0.1,
            'append' => 'rem',
            'default_value' => 5,
        ])
    ->endRepeater();

return $faq;
