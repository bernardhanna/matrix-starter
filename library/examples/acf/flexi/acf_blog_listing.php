<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$blog_listing = new FieldsBuilder('blog_listing', [
    'label' => 'Blog Listing with Filters',
]);

$blog_listing
    ->addTab('Content', ['label' => 'Content'])
    ->addText('section_heading', [
        'label' => 'Section Heading',
        'instructions' => 'Optional heading for the blog section.',
        'required' => 0,
    ])
    ->addSelect('section_heading_tag', [
        'label' => 'Heading Tag',
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
    ->addTrueFalse('show_filters', [
        'label' => 'Show Category Filters',
        'instructions' => 'Display category filter buttons above the posts.',
        'default_value' => 1,
    ])
    ->addTrueFalse('show_search', [
        'label' => 'Show Search',
        'instructions' => 'Display search functionality.',
        'default_value' => 1,
    ])
    ->addTaxonomy('limit_to_category', [
        'label' => 'Limit to Category',
        'instructions' => 'Optional. If selected, this block will only show posts from this category (and its children). Filters (if enabled) will show child categories of the selected category.',
        'taxonomy' => 'category',
        'field_type' => 'select',
        'allow_null' => 1,
        'add_term' => 0,
        'return_format' => 'id',
        'multiple' => 0,
    ])
    ->addTrueFalse('show_media_subcategory_filters_only', [
        'label' => 'Show Only Media Subcategory Filters',
        'instructions' => 'When enabled (with Limit to Category set), filter chips will only show Local, National, and International in that order.',
        'default_value' => 0,
        'ui' => 1,
    ])
    ->addTaxonomy('exclude_categories', [
        'label' => 'Exclude Categories',
        'instructions' => 'Optional. Posts in these categories will be excluded from this feed.',
        'taxonomy' => 'category',
        'field_type' => 'multi_select',
        'allow_null' => 1,
        'add_term' => 0,
        'return_format' => 'id',
        'multiple' => 1,
    ])
    ->addRelationship('exclude_posts', [
        'label' => 'Exclude Posts',
        'instructions' => 'Optional. Select specific posts to exclude from this feed.',
        'post_type' => ['post'],
        'filters' => ['search', 'taxonomy'],
        'return_format' => 'id',
    ])
    ->addNumber('posts_per_page', [
        'label' => 'Posts Per Page',
        'instructions' => 'Number of posts to display per page.',
        'default_value' => 12,
        'min' => 1,
    ])
    ->addTrueFalse('show_pagination', [
        'label' => 'Show Pagination',
        'instructions' => 'Display pagination controls when there are multiple pages.',
        'default_value' => 1,
    ])

    ->addTab('Design', ['label' => 'Design'])
    ->addColorPicker('background_color', [
        'label' => 'Background Color',
        'instructions' => 'Set the background color for the section.',
        'default_value' => '#ffffff',
    ])

    ->addTab('Layout', ['label' => 'Layout'])
    ->addSelect('layout_option', [
        'label' => 'Layout Option',
        'instructions' => 'Choose which blog listing layout to use.',
        'choices' => [
            'layout_1' => 'Layout 1',
            'layout_2' => 'Layout 2',
        ],
        'default_value' => 'layout_1',
        'required' => 1,
        'ui' => 1,
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
            'default_value' => 3.5,
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

return $blog_listing;