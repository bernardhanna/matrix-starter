<?php
/**
 * Home (front page) content fields.
 *
 * Recreates the legacy "homepage" ACF field group that drove the static front
 * page. The field KEYS and NAMES intentionally match the legacy values still
 * stored in post meta (reference keys `field_homepage_*`), so existing content
 * loads straight into the editor and the front end renders unchanged.
 *
 * Consumed by template-parts/home/*.php (hero, services, featuredslider,
 * bestsellers, info, our-story, faqs) and inc/rolling-donut-sections.php.
 *
 * Returns a raw ACF local field group array (registered by
 * inc/autoload-acf-groups.php) rather than a StoutLogic builder, because the
 * legacy field keys must be reproduced verbatim.
 */

if (! defined('ABSPATH')) {
    exit;
}

$img = static fn (string $key, string $name, string $label): array => [
    'key'           => $key,
    'name'          => $name,
    'label'         => $label,
    'type'          => 'image',
    'return_format' => 'id',
    'preview_size'  => 'medium',
    'library'       => 'all',
];

$txt = static fn (string $key, string $name, string $label): array => [
    'key'   => $key,
    'name'  => $name,
    'label' => $label,
    'type'  => 'text',
];

$ta = static fn (string $key, string $name, string $label): array => [
    'key'       => $key,
    'name'      => $name,
    'label'     => $label,
    'type'      => 'textarea',
    'new_lines' => '',
    'rows'      => 3,
];

$wys = static fn (string $key, string $name, string $label): array => [
    'key'          => $key,
    'name'         => $name,
    'label'        => $label,
    'type'         => 'wysiwyg',
    'tabs'         => 'all',
    'toolbar'      => 'full',
    'media_upload' => 1,
];

$link = static fn (string $key, string $name, string $label): array => [
    'key'           => $key,
    'name'          => $name,
    'label'         => $label,
    'type'          => 'link',
    'return_format' => 'array',
];

$tab = static fn (string $key, string $label): array => [
    'key'       => $key,
    'name'      => '',
    'label'     => $label,
    'type'      => 'tab',
    'placement' => 'top',
    'endpoint'  => 0,
];

return [
    'key'                   => 'group_homepage_content',
    'title'                 => 'Home page content',
    'fields'                => [

        /* ---------------------------------------------------------- Hero */
        $tab('field_homepage_tab_hero', 'Hero'),
        [
            'key'          => 'field_homepage_hero_slides',
            'name'         => 'hero_slides',
            'label'        => 'Hero slider',
            'type'         => 'repeater',
            'layout'       => 'block',
            'button_label' => 'Add slide',
            'collapsed'    => 'field_homepage_hero_slides_heading',
            'min'          => 0,
            'max'          => 10,
            'instructions' => 'Add one row per homepage hero slide. Use a title image or heading text on the left, optional subtext and button, and a right-side image. Leave empty to use the built-in default slides.',
            'sub_fields'   => [
                [
                    'key'     => 'field_homepage_hero_slides_msg_left',
                    'name'    => '',
                    'label'   => 'Left panel — content',
                    'type'    => 'message',
                    'message' => 'Use a title image <em>or</em> heading text (or both). Optional mobile-only heading/subtext can replace the title image on small screens.',
                ],
                $img('field_homepage_hero_slides_left_image', 'slide_left_image', 'Title image (desktop)'),
                $img('field_homepage_hero_slides_left_image_mobile', 'slide_left_image_mobile', 'Title image (mobile)'),
                array_merge($txt('field_homepage_hero_slides_heading', 'slide_heading', 'Heading'), [
                    'instructions' => 'Desktop heading. On mobile, use “Heading (mobile only)” below to show different text instead of the title image.',
                ]),
                array_merge($ta('field_homepage_hero_slides_subtext', 'slide_subtext', 'Subtext'), [
                    'instructions' => 'Supporting copy under the title (shown on desktop, or on both if no mobile subtext is set).',
                    'new_lines'    => 'br',
                    'rows'         => 4,
                ]),
                array_merge($txt('field_homepage_hero_slides_heading_mobile', 'slide_heading_mobile', 'Heading (mobile only)'), [
                    'instructions' => 'Optional. Shown on mobile instead of the title image — e.g. desktop neon image, mobile text title.',
                ]),
                array_merge($ta('field_homepage_hero_slides_subtext_mobile', 'slide_subtext_mobile', 'Subtext (mobile only)'), [
                    'instructions' => 'Optional. Replaces the main subtext on mobile when set.',
                    'new_lines'    => 'br',
                    'rows'         => 4,
                ]),
                array_merge($link('field_homepage_hero_slides_hero_link', 'slide_hero_link', 'Button link'), [
                    'instructions' => 'Set the URL, optional target, and button label in the link text field.',
                ]),
                [
                    'key'     => 'field_homepage_hero_slides_msg_left_bg',
                    'name'    => '',
                    'label'   => 'Left panel — background',
                    'type'    => 'message',
                    'message' => 'Optional repeating pattern behind the left overlay.',
                ],
                $img('field_homepage_hero_slides_left_pattern', 'slide_left_pattern', 'Background pattern (desktop)'),
                $img('field_homepage_hero_slides_left_pattern_mobile', 'slide_left_pattern_mobile', 'Background pattern (mobile)'),
                [
                    'key'     => 'field_homepage_hero_slides_msg_right',
                    'name'    => '',
                    'label'   => 'Right panel',
                    'type'    => 'message',
                    'message' => 'Main hero image on the right.',
                ],
                $img('field_homepage_hero_slides_right_image', 'slide_right_image', 'Right image (desktop)'),
                $img('field_homepage_hero_slides_right_image_mobile', 'slide_right_image_mobile', 'Right image (mobile)'),
                [
                    'key'     => 'field_homepage_hero_slides_msg_design',
                    'name'    => '',
                    'label'   => 'Design',
                    'type'    => 'message',
                    'message' => 'Colours and button styling for this slide.',
                ],
                [
                    'key'           => 'field_homepage_hero_slides_left_overlay',
                    'name'          => 'slide_left_overlay',
                    'label'         => 'Left overlay colour',
                    'type'          => 'select',
                    'choices'       => [
                        'black'       => 'Black (71% opacity)',
                        'dark_brown'  => 'Dark brown (71% opacity)',
                        'yellow'      => 'Yellow (90% opacity)',
                        'transparent' => 'Transparent',
                        'custom'      => 'Custom colour',
                    ],
                    'default_value' => 'black',
                    'return_format' => 'value',
                ],
                [
                    'key'               => 'field_homepage_hero_slides_left_overlay_custom',
                    'name'              => 'slide_left_overlay_custom',
                    'label'             => 'Custom overlay colour',
                    'type'              => 'color_picker',
                    'default_value'     => '#000000',
                    'enable_opacity'    => 1,
                    'return_format'     => 'string',
                    'conditional_logic' => [[['field' => 'field_homepage_hero_slides_left_overlay', 'operator' => '==', 'value' => 'custom']]],
                ],
                [
                    'key'           => 'field_homepage_hero_slides_text_color',
                    'name'          => 'slide_text_color',
                    'label'         => 'Text colour',
                    'type'          => 'select',
                    'choices'       => [
                        'white' => 'White',
                        'black' => 'Black',
                    ],
                    'default_value' => 'white',
                    'return_format' => 'value',
                ],
                [
                    'key'           => 'field_homepage_hero_slides_button_style',
                    'name'          => 'slide_button_style',
                    'label'         => 'Button style',
                    'type'          => 'select',
                    'choices'       => [
                        'white'  => 'White',
                        'black'  => 'Black',
                        'yellow' => 'Yellow',
                    ],
                    'default_value' => 'white',
                    'return_format' => 'value',
                ],
                [
                    'key'           => 'field_homepage_hero_slides_button_icon',
                    'name'          => 'slide_button_icon',
                    'label'         => 'Show donut icon on button',
                    'type'          => 'true_false',
                    'default_value' => 1,
                    'ui'            => 1,
                ],
            ],
        ],

        /* ------------------------------------------------------ Services */
        $tab('field_homepage_tab_services', 'Delivery / Collection'),
        [
            'key'          => 'field_homepage_services_list',
            'name'         => 'services_list',
            'label'        => 'Services',
            'type'         => 'repeater',
            'layout'       => 'block',
            'button_label' => 'Add service',
            'sub_fields'   => [
                $img('field_homepage_services_list_image', 'image', 'Image'),
                [
                    'key'           => 'field_homepage_services_list_video',
                    'name'          => 'video',
                    'label'         => 'Hover video (mp4)',
                    'type'          => 'file',
                    'return_format' => 'id',
                    'library'       => 'all',
                    'mime_types'    => 'mp4',
                ],
                $txt('field_homepage_services_list_title', 'title', 'Title'),
                $ta('field_homepage_services_list_description', 'description', 'Description'),
            ],
        ],

        /* ----------------------------------------------- Featured slider */
        $tab('field_homepage_tab_featured', 'Featured slider'),
        [
            'key'           => 'field_homepage_donuts',
            'name'          => 'donuts',
            'label'         => 'Featured donuts',
            'type'          => 'relationship',
            'post_type'     => ['product'],
            'filters'       => ['search'],
            'return_format' => 'id',
            'min'           => 0,
        ],

        /* --------------------------------------------------- Bestsellers */
        $tab('field_homepage_tab_bestsellers', 'Bestsellers'),
        $txt('field_homepage_heading', 'heading', 'Section heading'),
        $img('field_homepage_text_image', 'text_image', 'Heading image (overlay)'),
        $img('field_homepage_bg_image', 'bg_image', 'Background image'),
        [
            'key'           => 'field_homepage_product',
            'name'          => 'product',
            'label'         => 'Bestselling products',
            'type'          => 'relationship',
            'post_type'     => ['product'],
            'filters'       => ['search'],
            'return_format' => 'id',
            'min'           => 0,
        ],

        /* --------------------------------------------- Events & Gift Cards */
        $tab('field_homepage_tab_info', 'Events & Gift Cards'),
        $img('field_homepage_event_image', 'event_image', 'Events image'),
        $txt('field_homepage_event_heading', 'event_heading', 'Events heading'),
        $wys('field_homepage_event_text', 'event_text', 'Events text'),
        $link('field_homepage_event_button', 'event_button', 'Events button'),
        $img('field_homepage_giftcard_image', 'giftcard_image', 'Gift card image'),
        $txt('field_homepage_giftcard_heading', 'giftcard_heading', 'Gift card heading'),
        $wys('field_homepage_giftcard_text', 'giftcard_text', 'Gift card text'),
        $link('field_homepage_giftcard_button', 'giftcard_button', 'Gift card button'),

        /* ----------------------------------------------------- Our Story */
        $tab('field_homepage_tab_story', 'Our Story'),
        $img('field_homepage_background_image', 'background_image', 'Background image (desktop)'),
        $img('field_homepage_mobile_background_image', 'mobile_background_image', 'Background image (mobile)'),
        $txt('field_homepage_title_mob', 'title_mob', 'Mobile heading'),
        $ta('field_homepage_span_one_mob', 'span_one_mob', 'Mobile sub-heading 1'),
        $ta('field_homepage_span_two_mob', 'span_two_mob', 'Mobile sub-heading 2'),
        $ta('field_homepage_description_mob', 'description_mob', 'Mobile description'),
        [
            'key'          => 'field_homepage_stories',
            'name'         => 'stories',
            'label'        => 'Timeline stories',
            'type'         => 'repeater',
            'layout'       => 'block',
            'button_label' => 'Add story',
            'sub_fields'   => [
                $txt('field_homepage_stories_title', 'title', 'Title'),
                $ta('field_homepage_stories_span_one', 'span_one', 'Sub-heading 1'),
                $ta('field_homepage_stories_span_two', 'span_two', 'Sub-heading 2'),
                $ta('field_homepage_stories_description', 'description', 'Description'),
                $img('field_homepage_stories_image', 'image', 'Card image (desktop)'),
                $img('field_homepage_stories_image_mobile', 'image_mobile', 'Card image (mobile)'),
                $img('field_homepage_stories_donut_img', 'donut_img', 'Timeline donut icon'),
                $ta('field_homepage_stories_timeline_text', 'timeline_text', 'Timeline caption'),
                $txt('field_homepage_stories_timeline_button', 'timeline_button', 'Timeline label (e.g. year)'),
            ],
        ],

        /* ---------------------------------------------------------- FAQs */
        $tab('field_homepage_tab_faqs', 'FAQs'),
        $img('field_homepage_faq_image', 'faq_image', 'FAQ image'),
        $txt('field_homepage_faq_title', 'faq_title', 'FAQ heading'),
        $link('field_homepage_faq_button', 'faq_button', 'FAQ "view all" button'),
        [
            'key'          => 'field_homepage_selected_faqs',
            'name'         => 'selected_faqs',
            'label'        => 'Selected FAQs',
            'type'         => 'repeater',
            'layout'       => 'table',
            'button_label' => 'Add FAQ',
            'sub_fields'   => [
                [
                    'key'           => 'field_homepage_selected_faqs_faq',
                    'name'          => 'faq',
                    'label'         => 'FAQ',
                    'type'          => 'post_object',
                    'post_type'     => ['faq'],
                    'return_format' => 'id',
                    'allow_null'    => 1,
                    'multiple'      => 0,
                ],
            ],
        ],
    ],
    'location'              => [
        [
            [
                'param'    => 'page_type',
                'operator' => '==',
                'value'    => 'front_page',
            ],
        ],
    ],
    'menu_order'            => 0,
    'position'              => 'normal',
    'style'                 => 'default',
    'label_placement'       => 'top',
    'instruction_placement' => 'label',
    'active'                => true,
    'description'           => 'Static front page content (legacy homepage fields).',
];
