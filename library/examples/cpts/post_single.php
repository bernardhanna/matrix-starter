<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$post_single = new FieldsBuilder('post_single', [
    'label' => 'Post External Source Settings',
]);

$post_single
    ->setLocation('post_type', '==', 'post')
    ->addLink('post_external_source_link', [
        'label' => 'External Source Link',
        'instructions' => 'Optional. Add an external source URL for this post.',
        'return_format' => 'array',
    ])
    ->addTrueFalse('post_listing_open_external_source', [
        'label' => 'Open external source from blog listings',
        'instructions' => 'When enabled and an external source URL is provided, blog listing cards open the external source in a new tab.',
        'default_value' => 0,
        'ui' => 1,
    ])
    ->addTrueFalse('post_force_index', [
        'label' => 'Force index this post',
        'instructions' => 'Override default SEO behavior and allow indexing even if this post is an external link-out in media categories.',
        'default_value' => 0,
        'ui' => 1,
    ])
    ->addSelect('post_featured_image_fit', [
        'label' => 'Featured Image Fit (Listings)',
        'instructions' => 'Controls how this post thumbnail is displayed in listing cards. Default behavior: Stories = Portrait (contain), all others = Landscape (cover).',
        'choices' => [
            'auto' => 'Auto (Recommended)',
            'landscape' => 'Landscape (object-cover)',
            'portrait' => 'Portrait (object-contain)',
        ],
        'default_value' => 'auto',
        'ui' => 1,
        'return_format' => 'value',
    ])
    ->addImage('post_listing_logo_custom', [
        'label' => 'Media Listing Logo (Custom)',
        'instructions' => 'Optional. Upload a custom logo/image for In the Media / press listing thumbnails.',
        'return_format' => 'id',
        'preview_size' => 'medium',
        'library' => 'all',
    ])
    ->addSelect('post_listing_logo_quick_select', [
        'label' => 'Media Listing Logo (Quick Select)',
        'instructions' => 'Optional. Pick a predefined source logo. Ignored when a custom logo is uploaded above.',
        'choices' => [
            '' => 'None',
            'https://sanctuaryrunners.s1.matrix-test.com/wp-content/uploads/2026/04/www.thejournal.ie_president-michael-d-higgins-leaves-aras-for-final-time-6870482-Nov2025_.png' => 'The Journal',
            'https://sanctuaryrunners.s1.matrix-test.com/wp-content/uploads/2026/04/www.bbc_.com_usingthebbc_cookies_how-can-i-change-my-bbc-cookie-settings_.png' => 'BBC',
            'https://sanctuaryrunners.s1.matrix-test.com/wp-content/uploads/2026/04/Irish_Examiner_logo.png' => 'Irish Examiner',
            'https://sanctuaryrunners.s1.matrix-test.com/wp-content/uploads/2026/04/logo-rte-hh-primary.svg' => 'RTE',
            'https://sanctuaryrunners.s1.matrix-test.com/wp-content/uploads/2026/04/1647534191733.svg' => 'Irish Times',
            'https://sanctuaryrunners.s1.matrix-test.com/wp-content/uploads/2025/10/mwn-Logo-33-2.png' => 'MidWest News',
            'https://sanctuaryrunners.s1.matrix-test.com/wp-content/uploads/2026/04/Echo-live-logo.png' => 'Echo Live',
        ],
        'default_value' => '',
        'ui' => 1,
        'allow_null' => 0,
    ])
    ->addColorPicker('post_listing_logo_bg_color', [
        'label' => 'Media Listing Logo Background Color',
        'instructions' => 'Optional. Background color behind media logos in listings.',
        'default_value' => '#FFFFFF',
    ]);

return $post_single;
