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
        $txt('field_homepage_hero_text', 'hero_text', 'Hero text'),
        $link('field_homepage_hero_link', 'hero_link', 'Hero button'),
        $img('field_homepage_banner_left', 'banner_left', 'Banner — left (desktop)'),
        $img('field_homepage_banner_top_mobile', 'banner_top_mobile', 'Banner — top (mobile)'),
        $img('field_homepage_banner_right', 'banner_right', 'Banner — right (desktop)'),
        $img('field_homepage_banner_bottom_mobile', 'banner_bottom_mobile', 'Banner — bottom (mobile)'),
        $img('field_homepage_neon', 'neon', 'Neon sign (desktop)'),
        $img('field_homepage_neon_mobile', 'neon_mobile', 'Neon sign (mobile)'),
        $img('field_homepage_hazelnut', 'hazelnut', 'Spinning donut'),

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
