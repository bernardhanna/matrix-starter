<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$testimonial = new FieldsBuilder('testimonial', [
    'label' => 'Testimonial Section',
]);

$testimonial
    ->addTab('Content', [
        'label' => 'Content',
        'placement' => 'top'
    ])
    ->addImage('main_image', [
        'label' => 'Main Image',
        'instructions' => 'Upload the main testimonial image (recommended size: 576x512px)',
        'return_format' => 'id',
        'preview_size' => 'medium',
        'library' => 'all'
    ])
    ->addText('main_quote', [
        'label' => 'Main Quote',
        'instructions' => 'Enter the main testimonial quote text',
        'default_value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit sed do',
        'maxlength' => 200,
        'required' => 1
    ])
    ->addSelect('main_quote_tag', [
        'label' => 'Main Quote HTML Tag',
        'instructions' => 'Select the appropriate heading tag for SEO and accessibility',
        'choices' => [
            'h1' => 'H1',
            'h2' => 'H2',
            'h3' => 'H3',
            'h4' => 'H4',
            'h5' => 'H5',
            'h6' => 'H6',
            'p' => 'Paragraph',
            'span' => 'Span'
        ],
        'default_value' => 'h2',
        'required' => 1
    ])
    ->addWysiwyg('highlighted_quote', [
        'label' => 'Highlighted Quote',
        'instructions' => 'Enter the detailed testimonial quote that will appear with a border',
        'default_value' => 'Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit.',
        'media_upload' => 0,
        'tabs' => 'visual,text',
        'toolbar' => 'basic',
        'required' => 1
    ])
    ->addText('author_name', [
        'label' => 'Author Name',
        'instructions' => 'Enter the name of the person giving the testimonial',
        'default_value' => 'Name Surname',
        'maxlength' => 100,
        'required' => 1
    ])
    ->addText('author_title', [
        'label' => 'Author Title/Position',
        'instructions' => 'Enter the job title or position of the testimonial author',
        'default_value' => 'Lead marketing specialist',
        'maxlength' => 150,
        'required' => 1
    ])
    ->addImage('signature_image', [
        'label' => 'Signature Image',
        'instructions' => 'Upload the author signature image (recommended size: 180x60px)',
        'return_format' => 'id',
        'preview_size' => 'thumbnail',
        'library' => 'all'
    ])

    ->addTab('Design', [
        'label' => 'Design',
        'placement' => 'top'
    ])
    ->addColorPicker('quotation_mark_color', [
        'label' => 'Quotation Mark Color',
        'instructions' => 'Choose the color for the large quotation mark',
        'default_value' => '#ec4899',
        'enable_opacity' => 0
    ])
    ->addColorPicker('background_color', [
        'label' => 'Background Color',
        'instructions' => 'Set the background color for the testimonial section',
        'default_value' => '#ffffff',
        'enable_opacity' => 0
    ])

    ->addTab('Layout', [
        'label' => 'Layout',
        'placement' => 'top'
    ])
    ->addRepeater('padding_settings', [
        'label' => 'Padding Settings',
        'instructions' => 'Customize padding for different screen sizes. Add multiple entries for responsive design.',
        'button_label' => 'Add Screen Size Padding',
        'layout' => 'table',
        'min' => 0,
        'max' => 10
    ])
        ->addSelect('screen_size', [
            'label' => 'Screen Size',
            'instructions' => 'Select the screen size breakpoint',
            'choices' => [
                'xxs' => 'Extra Extra Small (xxs)',
                'xs' => 'Extra Small (xs)',
                'mob' => 'Mobile (mob)',
                'sm' => 'Small (sm)',
                'md' => 'Medium (md)',
                'lg' => 'Large (lg)',
                'xl' => 'Extra Large (xl)',
                'xxl' => 'Extra Extra Large (xxl)',
                'ultrawide' => 'Ultra Wide (ultrawide)'
            ],
            'required' => 1,
            'wrapper' => [
                'width' => '30'
            ]
        ])
        ->addNumber('padding_top', [
            'label' => 'Padding Top',
            'instructions' => 'Set the top padding in rem units',
            'min' => 0,
            'max' => 20,
            'step' => 0.1,
            'append' => 'rem',
            'default_value' => 5,
            'wrapper' => [
                'width' => '35'
            ]
        ])
        ->addNumber('padding_bottom', [
            'label' => 'Padding Bottom',
            'instructions' => 'Set the bottom padding in rem units',
            'min' => 0,
            'max' => 20,
            'step' => 0.1,
            'append' => 'rem',
            'default_value' => 5,
            'wrapper' => [
                'width' => '35'
            ]
        ])
    ->endRepeater();

return $testimonial;
