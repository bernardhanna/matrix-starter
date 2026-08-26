<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

/**
 * Woo Product fields — per-product donut + box meta.
 *
 * Restores the legacy "Woo Product" ACF group (App\Fields\Products +
 * App\Fields\Partials\Product on the old Sage site) that previously lived only
 * in the database and was lost on the new build. The field NAMES are the
 * contract read across the theme and the box-builder plugins, so they are kept
 * identical:
 *
 *   Donut Fields → featured_donut_bg_color, thumb_image, product_allergens
 *     (drive template-parts/home/featuredslider.php + content-product.php +
 *      product allergen lists)
 *   Box Fields  → box_number, enable/require_special_occasion,
 *     enable/require_logo_upload, logo_upload, enable_additional_logos +
 *     additional_logos/logo, enable_stand_selection,
 *     include_stand_cost_in_box_price, enable/special_requests +
 *     special_requests_placeholder, product_with_additional_cost,
 *     custom_dropdown_groups (read by box-builder-woo + rd-box-builder).
 *
 * Note: the legacy partial carried conditional_logic on a `rd_product_type`
 * field, but rd_product_type is a TAXONOMY (not an ACF field), so that logic was
 * dead config that would permanently hide the box fields if reproduced. It is
 * intentionally dropped here — the new site distinguishes box products via the
 * product type / rd_box_builder_is_enabled(), not by hiding these meta fields.
 * The valid sibling-toggle conditions (enable_x ⇒ require_x) are kept.
 */

if (! defined('ABSPATH')) {
    exit;
}

$woo_product = new FieldsBuilder('woo_product', [
    'title' => 'Woo Product',
]);

$woo_product->setLocation('post_type', '==', 'product');

/* ----------------------------------------------------------- Donut Fields */
$woo_product
    ->addTab('Donut Fields', ['placement' => 'top'])
    ->addColorPicker('featured_donut_bg_color', [
        'label'         => 'Featured Donut Background Color',
        'instructions'  => 'Select the background color for the featured donut.',
        'default_value' => '#FFFFFF',
    ])
    ->addImage('thumb_image', [
        'label'         => 'Thumbnail Image',
        'return_format' => 'url',
        'preview_size'  => 'thumbnail',
    ])
    ->addPostObject('product_allergens', [
        'label'         => 'Allergens',
        'post_type'     => ['allergen'],
        'multiple'      => true,
        'return_format' => 'object',
    ]);

/* ------------------------------------------------------------- Box Fields */
$woo_product
    ->addTab('Box Fields', ['placement' => 'top'])
    ->addText('box_number', [
        'label'        => 'Number of Products in Box',
        'instructions' => 'Enter a number to represent the quantity of products in the box.',
    ])
    ->addTrueFalse('enable_special_occasion', [
        'label'        => 'Enable Special Occasion Select',
        'instructions' => 'Enable this to show the Special Occasion select box on the product page.',
        'ui'           => 1,
    ])
    ->addTrueFalse('require_special_occasion', [
        'label'             => 'Require Special Occasion Select',
        'instructions'      => 'Enable this to make selecting a Special Occasion mandatory before adding to cart.',
        'ui'                => 1,
        'default_value'     => 0,
        'conditional_logic' => [
            [
                ['field' => 'enable_special_occasion', 'operator' => '==', 'value' => '1'],
            ],
        ],
    ])
    ->addTrueFalse('enable_logo_upload', [
        'label'        => 'Enable Logo Upload',
        'instructions' => 'Enable this to show the single logo upload field on the product page.',
        'ui'           => 1,
    ])
    ->addTrueFalse('require_logo_upload', [
        'label'             => 'Require Logo Upload',
        'instructions'      => 'Enable this to make the logo upload mandatory before adding to cart.',
        'ui'                => 1,
        'default_value'     => 0,
        'conditional_logic' => [
            [
                ['field' => 'enable_logo_upload', 'operator' => '==', 'value' => '1'],
            ],
        ],
    ])
    ->addFile('logo_upload', [
        'label'             => 'Upload Logo',
        'instructions'      => 'Upload a logo (PNG, PDF, JPEG, JPG).',
        'return_format'     => 'url',
        'mime_types'        => 'png,pdf,jpeg,jpg',
        'conditional_logic' => [
            [
                ['field' => 'enable_logo_upload', 'operator' => '==', 'value' => '1'],
            ],
        ],
    ])
    ->addTrueFalse('enable_additional_logos', [
        'label'        => 'Enable Additional Logo Uploads',
        'instructions' => 'Enable this to show the additional logo uploads field on the product page.',
        'ui'           => 1,
    ])
    ->addRepeater('additional_logos', [
        'label'             => 'Additional Logos',
        'instructions'      => 'Upload additional logos.',
        'conditional_logic' => [
            [
                ['field' => 'enable_additional_logos', 'operator' => '==', 'value' => '1'],
            ],
        ],
    ])
        ->addFile('logo', [
            'label'         => 'Logo',
            'instructions'  => 'Upload a logo (PNG, PDF, JPEG, JPG).',
            'return_format' => 'url',
            'mime_types'    => 'png,pdf,jpg',
        ])
    ->endRepeater()
    ->addTrueFalse('enable_stand_selection', [
        'label'        => 'Enable Stand Type Selection',
        'instructions' => 'Enable this to show the options to select a stand.',
        'ui'           => 1,
    ])
    ->addTrueFalse('include_stand_cost_in_box_price', [
        'label'         => 'Include Stand Cost in Box Price',
        'instructions'  => 'Check this to add the cost of selected stands to the box price.',
        'ui'            => 1,
        'default_value' => 0,
    ])
    ->addTrueFalse('enable_special_requests', [
        'label'        => 'Enable Customer notes',
        'instructions' => 'Enable this to show the Customer notes text area on the product page.',
        'ui'           => 1,
    ])
    ->addTextarea('special_requests', [
        'label'             => 'Customer notes',
        'instructions'      => 'Enter any information for the team here.',
        'conditional_logic' => [
            [
                ['field' => 'enable_special_requests', 'operator' => '==', 'value' => '1'],
            ],
        ],
    ])
    ->addText('special_requests_placeholder', [
        'label'             => 'Customer notes placeholder',
        'instructions'      => 'Optional. Overrides the placeholder text shown in the Customer notes box on the product page.',
        'default_value'     => '',
        'conditional_logic' => [
            [
                ['field' => 'enable_special_requests', 'operator' => '==', 'value' => '1'],
            ],
        ],
    ])
    ->addRepeater('product_with_additional_cost', [
        'label'        => 'Add Product to box',
        'instructions' => 'Optional extras. On Custom Order, published Box products are listed automatically — use this only for non-box add-ons (or boxes you want to include on other products).',
        'layout'       => 'block',
        'button_label' => 'Add Product',
    ])
        ->addRelationship('product', [
            'label'         => 'Product',
            'instructions'  => 'Select a product or product variation.',
            'post_type'     => ['product', 'product_variation'],
            'filters'       => ['search'],
            'return_format' => 'object',
        ])
        ->addTrueFalse('additional_cost', [
            'label'         => 'Additional Cost',
            'instructions'  => 'Include the selected product at additional cost.',
            'ui'            => 1,
            'default_value' => 0,
        ])
    ->endRepeater()
    ->addRepeater('custom_dropdown_groups', [
        'label'        => 'Custom Dropdown Groups',
        'instructions' => 'Optional. Add custom dropdowns like Football Theme or Music Theme.',
        'layout'       => 'block',
        'button_label' => 'Add Dropdown Group',
    ])
        ->addText('group_label', [
            'label'        => 'Group Label',
            'instructions' => 'Example: Football Theme',
            'required'     => 1,
        ])
        ->addText('group_key', [
            'label'        => 'Group Key',
            'instructions' => 'Optional. Leave blank to auto-generate from label.',
            'required'     => 0,
        ])
        ->addTrueFalse('group_required', [
            'label'         => 'Required',
            'instructions'  => 'Require selection before adding to cart.',
            'ui'            => 1,
            'default_value' => 0,
        ])
        ->addWysiwyg('group_select_note', [
            'label'        => 'Select note',
            'instructions' => 'Optional note shown above this dropdown on the product page. The heading "Please note:" is added automatically — enter only the body text here.',
            'required'     => 0,
            'tabs'         => 'all',
            'toolbar'      => 'basic',
            'media_upload' => 0,
        ])
        ->addRepeater('group_options', [
            'label'        => 'Options',
            'instructions' => 'Add selectable options for this dropdown.',
            'layout'       => 'table',
            'button_label' => 'Add Option',
            'required'     => 1,
            'min'          => 1,
        ])
            ->addText('option_label', [
                'label'    => 'Option Label',
                'required' => 1,
            ])
            ->addText('option_value', [
                'label'        => 'Option Value',
                'instructions' => 'Optional. Leave blank to use label.',
                'required'     => 0,
            ])
        ->endRepeater()
    ->endRepeater();

return $woo_product;
