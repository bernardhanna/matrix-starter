<?php
use StoutLogic\AcfBuilder\FieldsBuilder;

// -----------------------------------------------------------------------------
// WooCommerce Settings (Theme Options)
// -----------------------------------------------------------------------------
$woocommerce = new FieldsBuilder('woocommerce', [
    'title'   => 'WooCommerce Settings',
    'post_id' => 'option',
]);

$woocommerce
    // Toggle on/off
    ->addTrueFalse('enable_woocommerce', [
        'label'         => 'Enable WooCommerce',
        'instructions'  => 'Check to turn on WooCommerce integration in this theme.',
        'ui'            => true,
        'message'       => 'WooCommerce Support',
        'default_value' => 0,
    ])

    // Accordion: Shop Archive
    ->addAccordion('shop_settings', ['label' => 'Shop Page Settings', 'open' => false])
        ->addTrueFalse('hide_shop_hero', [
            'label' => 'Hide Hero/Header',
            'ui'    => true,
        ])
        ->addTrueFalse('hide_shop_breadcrumbs', [
            'label' => 'Hide Breadcrumbs',
            'ui'    => true,
        ])
        ->addTrueFalse('hide_result_count', [
            'label' => 'Hide Result Count',
            'ui'    => true,
        ])
        ->addTrueFalse('hide_catalog_ordering', [
            'label' => 'Hide Sort Dropdown',
            'ui'    => true,
        ])
        ->addTrueFalse('hide_shop_sidebar', [
            'label' => 'Hide Sidebar',
            'ui'    => true,
        ])

    // Accordion: Single Product
    ->addAccordion('product_settings', ['label' => 'Single Product Settings', 'open' => false])
        ->addTrueFalse('hide_product_hero', [
            'label' => 'Hide Hero/Header',
            'ui'    => true,
        ])
        ->addTrueFalse('hide_product_sidebar', [
            'label' => 'Hide Sidebar',
            'ui'    => true,
        ])
        ->addTrueFalse('disable_reviews', [
            'label'         => 'Disable Reviews Tab',
            'ui'            => true,
            'default_value' => 0,
        ])
        ->addTrueFalse('hide_product_breadcrumbs', [
            'label' => 'Hide Breadcrumbs',
            'ui'    => true,
        ])
        ->addTrueFalse('hide_related_products', [
            'label' => 'Hide Related Products',
            'ui'    => true,
        ])
        ->addTrueFalse('hide_upsells', [
            'label' => 'Hide Upsells',
            'ui'    => true,
        ])

    // Accordion: Cart
    ->addAccordion('cart_settings', ['label' => 'Cart Page Settings', 'open' => false])
        ->addSelect('rd_cart_feedback_mode', [
            'label'         => 'Add to Cart Feedback',
            'instructions'  => 'Choose how customers are notified after adding items. Side cart slides in from the right (similar to Side Cart WooCommerce). Popup shows the legacy centred overlay.',
            'choices'       => [
                'popup'     => 'Popup notification (legacy)',
                'side_cart' => 'Side cart slide-out',
            ],
            'default_value' => 'popup',
            'ui'            => 1,
            'return_format' => 'value',
        ])
        ->addTrueFalse('hide_cart_hero', [
            'label' => 'Hide Hero/Header',
            'ui'    => true,
        ])
        ->addTrueFalse('hide_cart_cross_sells', [
            'label' => 'Hide Cross-sells',
            'ui'    => true,
        ])

    // Accordion: Checkout
    ->addAccordion('checkout_settings', ['label' => 'Checkout Page Settings', 'open' => false])
        ->addTrueFalse('hide_checkout_coupon_form', [
            'label' => 'Hide Coupon Form',
            'ui'    => true,
        ])
        ->addTrueFalse('hide_checkout_login_form', [
            'label' => 'Hide Login Prompt',
            'ui'    => true,
        ])
        ->addImage('ty_bg', [
            'label'         => 'Thank You Page Background',
            'instructions'  => 'Repeating pattern behind the order confirmation page. Leave empty for a solid background.',
            'return_format' => 'id',
            'preview_size'  => 'medium',
            'library'       => 'all',
        ])

    // Accordion: My Account
    ->addAccordion('myaccount_settings', ['label' => 'My Account Page', 'open' => false])
        ->addImage('myaccount_bg', [
            'label'         => 'Page Background Image',
            'instructions'  => 'Repeating pattern behind the My Account page (tabs, orders, addresses, etc.). Leave empty for a solid black background when logged in.',
            'return_format' => 'id',
            'preview_size'  => 'medium',
            'library'       => 'all',
        ])

->addAccordion('woo_email_branding', ['label' => 'Email Branding', 'open' => false])

    ->addTrueFalse('woo_email_enforce', [
      'label'         => 'Enforce Theme Email Colours',
      'instructions'  => 'Override WooCommerce email colours with the settings below.',
      'ui'            => 1,
      'default_value' => 1,
    ])

    ->addSelect('woo_email_preset', [
      'label'        => 'Preset',
      'instructions' => 'Choose a quick preset or use manual colours below.',
      'choices'      => [
        'brand_red' => 'Brand Red (#ED1C24 accents)',
        'black'     => 'Black (dark background, white text)',
        'custom'    => 'Custom (use pickers below)',
      ],
      'default_value' => 'brand_red',
      'ui'            => 1,
    ])

    // Manual overrides (used when preset = custom, but always visible for clarity)
    ->addColorPicker('woo_email_base_color', [
      'label'         => 'Base Color (buttons/accents)',
      'default_value' => '#ED1C24',
    ])
    ->addColorPicker('woo_email_background_color', [
      'label'         => 'Outer Background',
      'default_value' => '#ffffff',
    ])
    ->addColorPicker('woo_email_body_background_color', [
      'label'         => 'Inner Body Background',
      'default_value' => '#ffffff',
    ])
    ->addColorPicker('woo_email_text_color', [
      'label'         => 'Body Text',
      'default_value' => '#101828',
    ]);

return $woocommerce;
