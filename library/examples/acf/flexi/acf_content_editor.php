<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$content_editor = new FieldsBuilder('content_editor', [
    'label' => 'Content Editor',
]);

$content_editor
    ->addTab('content', ['label' => 'Content'])
        ->addText('heading_text', [
            'label' => 'Heading',
            'default_value' => 'Lorem ipsum dolor h3',
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
        ->addWysiwyg('subheading_wysiwyg', [
            'label' => 'Subheading (WYSIWYG)',
            'instructions' => 'Recommended for short supporting text under the heading.',
            'tabs' => 'all',
            'toolbar' => 'basic',
            'media_upload' => 0,
            'default_value' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
        ])
        ->conditional('show_subheading', '==', 1)

        ->addAccordion('columns', [
            'label' => 'Columns',
            'open' => 1,
            'multi_expand' => 0,
        ])
            ->addWysiwyg('column_1', [
                'label' => 'Column 1 (WYSIWYG)',
                'tabs' => 'all',
                'toolbar' => 'basic',
                'media_upload' => 0,
            ])
            ->addWysiwyg('column_2', [
                'label' => 'Column 2 (WYSIWYG)',
                'tabs' => 'all',
                'toolbar' => 'basic',
                'media_upload' => 0,
            ])
            ->addTrueFalse('show_column_3', [
                'label' => 'Show Column 3',
                'instructions' => 'Adds a third editor below the columns (full width).',
                'ui' => 1,
                'default_value' => 0,
            ])
            ->addWysiwyg('column_3', [
                'label' => 'Column 3 (WYSIWYG)',
                'instructions' => 'Full-width content shown below the two columns.',
                'tabs' => 'all',
                'toolbar' => 'basic',
                'media_upload' => 0,
            ])
            ->conditional('show_column_3', '==', 1)
        ->addAccordion('columns_end')->endpoint()

    ->addTab('design', ['label' => 'Design'])
        ->addText('background_color', [
            'label' => 'Background Color',
            'instructions' => 'CSS color value (e.g. rgba(255,255,255,1) or #ffffff).',
            'default_value' => 'rgba(255,255,255,1)',
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

return $content_editor;