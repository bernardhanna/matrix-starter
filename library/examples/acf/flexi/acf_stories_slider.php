<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$storiesSlider = new FieldsBuilder('stories_slider', [
    'label' => 'Stories Slider',
]);

$storiesSlider
    ->addTab('Content', ['label' => 'Content'])
        ->addText('section_id', [
            'label' => 'Section ID',
            'instructions' => 'Optional anchor ID for this section.',
        ])
        ->addText('section_class', [
            'label' => 'Section Class',
            'instructions' => 'Optional additional classes for the section.',
        ])
        ->addText('heading', [
            'label' => 'Heading',
            'instructions' => 'Main heading for the stories section.',
            'default_value' => 'Stories',
        ])
        ->addSelect('heading_tag', [
            'label' => 'Heading Tag',
            'instructions' => 'Choose the HTML tag for the heading.',
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
            'default_value' => 'h2',
            'ui' => 1,
        ])
        ->addLink('read_all_link', [
            'label' => 'Read All Link',
            'instructions' => 'Optional link for the "Read all stories" button.',
            'return_format' => 'array',
        ])
        ->addImage('background_image', [
            'label' => 'Background Image',
            'instructions' => 'Optional background image applied as a CSS background.',
            'return_format' => 'array',
            'preview_size' => 'medium',
            'library' => 'all',
        ])
        ->addImage('slider_background_image', [
            'label' => 'Slider Default Background Image',
            'instructions' => 'Default image shown behind the slider. When hovering a story card, it switches to that story\'s featured image. Leave empty for a solid dark background.',
            'return_format' => 'array',
            'preview_size' => 'medium',
            'library' => 'all',
        ])
        ->addNumber('posts_per_page', [
            'label' => 'Posts Per Page',
            'instructions' => 'How many story posts to display in the slider.',
            'default_value' => 8,
            'min' => 1,
            'max' => 24,
            'step' => 1,
        ])
        ->addTrueFalse('show_excerpt', [
            'label' => 'Show Excerpt',
            'instructions' => 'Display the post excerpt under the title.',
            'default_value' => 1,
            'ui' => 1,
        ])
        ->addText('category_slug', [
            'label' => 'Category Slug',
            'instructions' => 'Category slug to query posts from.',
            'default_value' => 'stories',
        ])

    ->addTab('Design', ['label' => 'Design'])
        ->addColorPicker('section_overlay_color', [
            'label' => 'Overlay Color',
            'instructions' => 'Optional overlay colour shown over the slider area.',
            'default_value' => '#081226',
        ])
        ->addNumber('overlay_opacity_from', [
            'label' => 'Overlay From Opacity',
            'instructions' => 'Top/bottom gradient start opacity, between 0 and 100.',
            'default_value' => 75,
            'min' => 0,
            'max' => 100,
            'step' => 1,
            'append' => '%',
        ])
        ->addNumber('overlay_opacity_via', [
            'label' => 'Overlay Mid Opacity',
            'instructions' => 'Middle gradient opacity, between 0 and 100.',
            'default_value' => 35,
            'min' => 0,
            'max' => 100,
            'step' => 1,
            'append' => '%',
        ])

    ->addTab('Layout', ['label' => 'Layout'])
        ->addRepeater('padding_settings', [
            'label' => 'Padding Settings',
            'instructions' => 'Customize top and bottom padding for different screen sizes.',
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
                'ui' => 1,
            ])
            ->addNumber('padding_top', [
                'label' => 'Padding Top',
                'instructions' => 'Set top padding in rem.',
                'min' => 0,
                'max' => 20,
                'step' => 0.1,
                'append' => 'rem',
                'default_value' => 5,
            ])
            ->addNumber('padding_bottom', [
                'label' => 'Padding Bottom',
                'instructions' => 'Set bottom padding in rem.',
                'min' => 0,
                'max' => 20,
                'step' => 0.1,
                'append' => 'rem',
                'default_value' => 5,
            ])
        ->endRepeater();

return $storiesSlider;