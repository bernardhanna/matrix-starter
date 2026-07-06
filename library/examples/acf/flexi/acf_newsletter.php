<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$newsletter_001 = new FieldsBuilder('newsletter_001', [
    'label' => 'Newsletter Signup Form',
]);

$newsletter_001
    ->addTab('Content')
        ->addText('heading', [
            'label' => 'Heading Text',
            'instructions' => 'Enter the heading text for the newsletter signup section.',
            'default_value' => 'Newsletter signup'
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
        ->addImage('icon_image', [
            'label' => 'Icon Image',
            'instructions' => 'Upload an icon to display next to the heading.',
            'return_format' => 'id',
            'preview_size' => 'thumbnail',
        ])
        ->addText('brevo_list_ids', [
            'label' => 'Brevo List IDs',
            'instructions' => 'Enter Brevo list IDs separated by commas (e.g., 1,2,3). Leave empty to use default from theme options.',
            'placeholder' => '1,2,3',
        ])
        ->addUrl('privacy_policy_url', [
            'label' => 'Privacy Policy URL',
            'instructions' => 'Enter the URL for your privacy policy page.',
            'default_value' => '#'
        ])
        ->addUrl('terms_conditions_url', [
            'label' => 'Terms & Conditions URL',
            'instructions' => 'Enter the URL for your terms and conditions page.',
            'default_value' => '#'
        ])

    ->addTab('Design')
        ->addColorPicker('background_color', [
            'label' => 'Section Background Color',
            'instructions' => 'Choose the background color for the entire section.',
            'default_value' => '#fef3c7'
        ])
        ->addColorPicker('form_background_color', [
            'label' => 'Form Container Background Color',
            'instructions' => 'Choose the background color for the form container.',
            'default_value' => '#000000'
        ])
        ->addColorPicker('text_color', [
            'label' => 'Text Color',
            'instructions' => 'Choose the color for text elements.',
            'default_value' => '#ffffff'
        ])
        ->addColorPicker('button_color', [
            'label' => 'Subscribe Button Color',
            'instructions' => 'Choose the background color for the subscribe button.',
            'default_value' => '#fde047'
        ])
        ->addColorPicker('button_text_color', [
            'label' => 'Button Text Color',
            'instructions' => 'Choose the text color for the subscribe button.',
            'default_value' => '#1e293b'
        ])

    ->addTab('Layout')
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

return $newsletter_001;
