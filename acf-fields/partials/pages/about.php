<?php
/**
 * About us page content fields.
 *
 * Recreates the legacy "aboutpage" ACF field group. Field KEYS and NAMES match
 * the legacy values still stored in post meta (reference keys
 * `field_aboutpage_*`), so existing content loads straight into the editor and
 * the front end renders unchanged.
 *
 * Consumed by page-about-us.php via:
 *   - template-parts/pages/video-block.php   (video_thumbnail, youtube_video_id)
 *   - template-parts/pages/header-image.php  (header_image)
 *   - inc/rolling-donut-sections.php         (our-story: background images, mobile
 *                                             copy, stories repeater)
 *   - template-parts/pages/faqs-all.php      (faq_button)
 *
 * Located by page template (templates/template-about.blade.php) so it follows the
 * About page regardless of post ID. The page's main body copy stays editable in
 * the normal content editor.
 *
 * Returns a raw ACF local field group array (registered by
 * inc/autoload-acf-groups.php) so the legacy field keys are reproduced verbatim.
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

$link = static fn (string $key, string $name, string $label): array => [
    'key'           => $key,
    'name'          => $name,
    'label'         => $label,
    'type'          => 'link',
    'return_format' => 'array',
];

$bool = static fn (string $key, string $name, string $label): array => [
    'key'           => $key,
    'name'          => $name,
    'label'         => $label,
    'type'          => 'true_false',
    'ui'            => 1,
    'default_value' => 0,
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
    'key'                   => 'group_aboutpage_content',
    'title'                 => 'About page content',
    'fields'                => [

        /* --------------------------------------------------------- Video */
        $tab('field_aboutpage_tab_video', 'Video'),
        $img('field_aboutpage_video_thumbnail', 'video_thumbnail', 'Video thumbnail'),
        $txt('field_aboutpage_youtube_video_id', 'youtube_video_id', 'YouTube video ID'),

        /* -------------------------------------------------- Header image */
        $tab('field_aboutpage_tab_header', 'Header image'),
        $img('field_aboutpage_header_image', 'header_image', 'Full-width header image'),

        /* ----------------------------------------------------- Our Story */
        $tab('field_aboutpage_tab_story', 'Our Story'),
        $img('field_aboutpage_background_image', 'background_image', 'Background image (desktop)'),
        $img('field_aboutpage_mobile_background_image', 'mobile_background_image', 'Background image (mobile)'),
        $txt('field_aboutpage_title_mob', 'title_mob', 'Mobile heading'),
        $ta('field_aboutpage_span_one_mob', 'span_one_mob', 'Mobile sub-heading 1'),
        $ta('field_aboutpage_span_two_mob', 'span_two_mob', 'Mobile sub-heading 2'),
        $ta('field_aboutpage_description_mob', 'description_mob', 'Mobile description'),
        [
            'key'          => 'field_aboutpage_stories',
            'name'         => 'stories',
            'label'        => 'Timeline stories',
            'type'         => 'repeater',
            'layout'       => 'block',
            'button_label' => 'Add story',
            'sub_fields'   => [
                $txt('field_aboutpage_stories_title', 'title', 'Title'),
                $ta('field_aboutpage_stories_span_one', 'span_one', 'Sub-heading 1'),
                $ta('field_aboutpage_stories_span_two', 'span_two', 'Sub-heading 2'),
                $ta('field_aboutpage_stories_description', 'description', 'Description'),
                $img('field_aboutpage_stories_image', 'image', 'Card image (desktop)'),
                $img('field_aboutpage_stories_image_mobile', 'image_mobile', 'Card image (mobile)'),
                $img('field_aboutpage_stories_donut_img', 'donut_img', 'Timeline donut icon'),
                $ta('field_aboutpage_stories_timeline_text', 'timeline_text', 'Timeline caption'),
                $txt('field_aboutpage_stories_timeline_button', 'timeline_button', 'Timeline label (e.g. year)'),
                $bool('field_aboutpage_stories_is_desktop_only', 'is_desktop_only', 'Show on desktop only'),
                $bool('field_aboutpage_stories_is_mobile_only', 'is_mobile_only', 'Show on mobile only'),
            ],
        ],

        /* ---------------------------------------------------------- FAQs */
        $tab('field_aboutpage_tab_faqs', 'FAQs'),
        $link('field_aboutpage_faq_button', 'faq_button', 'FAQ "view all" button'),
        $img('field_aboutpage_faq_image', 'faq_image', 'FAQ image (unused on this layout)'),
        $txt('field_aboutpage_faq_title', 'faq_title', 'FAQ heading (unused on this layout)'),
        [
            'key'          => 'field_aboutpage_selected_faqs',
            'name'         => 'selected_faqs',
            'label'        => 'Selected FAQs (unused on this layout)',
            'type'         => 'repeater',
            'layout'       => 'table',
            'button_label' => 'Add FAQ',
            'sub_fields'   => [
                [
                    'key'           => 'field_aboutpage_selected_faqs_faq',
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
                'param'    => 'page_template',
                'operator' => '==',
                'value'    => 'templates/template-about.blade.php',
            ],
        ],
    ],
    'menu_order'            => 0,
    'position'              => 'normal',
    'style'                 => 'default',
    'label_placement'       => 'top',
    'instruction_placement' => 'label',
    'active'                => true,
    'description'           => 'About us page content (legacy aboutpage fields).',
];
