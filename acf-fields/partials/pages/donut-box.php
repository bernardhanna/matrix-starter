<?php
/**
 * Donut Box page content fields ("Box Product").
 *
 * Recreates the legacy "box_product" ACF field group. Field KEYS and NAMES match
 * the legacy values still stored in post meta (reference keys
 * `field_box_product_*`), so existing content loads straight into the editor and
 * the front end renders unchanged.
 *
 * Consumed by page-donut-box.php:
 *   - ordered_categories -> matrix_rd_acf_terms() (which product_cat boxes to list)
 *   - services_list      -> template-parts/home/services.php (shared services row)
 *
 * Located by page template (templates/template-box-products.blade.php) so it
 * follows the Donut Box page regardless of post ID.
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

return [
    'key'                   => 'group_box_product_content',
    'title'                 => 'Box Product',
    'fields'                => [
        [
            'key'           => 'field_box_product_ordered_categories',
            'name'          => 'ordered_categories',
            'label'         => 'Box Product Type Categories',
            'instructions'  => 'Choose which product categories to show, in display order.',
            'type'          => 'taxonomy',
            'taxonomy'      => 'product_cat',
            'field_type'    => 'multi_select',
            'add_term'      => 0,
            'save_terms'    => 0,
            'load_terms'    => 0,
            'return_format' => 'id',
        ],
        [
            'key'          => 'field_box_product_services_list',
            'name'         => 'services_list',
            'label'        => 'Services List',
            'type'         => 'repeater',
            'layout'       => 'block',
            'button_label' => 'Add service',
            'sub_fields'   => [
                $img('field_box_product_services_list_image', 'image', 'Image'),
                [
                    'key'           => 'field_box_product_services_list_video',
                    'name'          => 'video',
                    'label'         => 'Video animation (mp4)',
                    'type'          => 'file',
                    'return_format' => 'id',
                    'library'       => 'all',
                    'mime_types'    => 'mp4',
                ],
                $txt('field_box_product_services_list_title', 'title', 'Title'),
                $ta('field_box_product_services_list_description', 'description', 'Description'),
            ],
        ],
    ],
    'location'              => [
        [
            [
                'param'    => 'page_template',
                'operator' => '==',
                'value'    => 'templates/template-box-products.blade.php',
            ],
        ],
    ],
    'menu_order'            => 0,
    'position'              => 'normal',
    'style'                 => 'default',
    'label_placement'       => 'top',
    'instruction_placement' => 'label',
    'active'                => true,
    'description'           => 'Donut Box page content (legacy box_product fields).',
];
