<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

add_action('acf/init', function () {
    acf_add_options_page([
        'page_title'    => get_bloginfo('name') . ' Theme Options',
        'menu_title'    => 'Theme Options',
        'menu_slug'     => 'theme-options',
        'capability'    => 'edit_theme_options',
        'position'      => '999',
        'autoload'      => true,
        'update_button' => 'Update Options',
    ]);

    $options = new FieldsBuilder('theme_options', [
        'style' => 'seamless',
    ]);

    $options
        ->setLocation('options_page', '==', 'theme-options')

    ->addTab('Breadcrumbs', ['label' => 'Breadcrumbs', 'placement' => 'left'])
        ->addFields(require __DIR__ . '/theme-options/breadcrumbs.php')

        ->addTab('banner', ['placement' => 'left'])
        ->addTab('topbar', ['placement' => 'left'])
    ->addTab('Back to Top', ['label' => 'Back to Top Button', 'placement' => 'left'])
    ->addFields(require __DIR__ . '/theme-options/back_to_top.php')
    ->addTab('header_nav', ['label' => 'Navbar Settings', 'placement' => 'left'])
    ->addFields(require __DIR__ . '/theme-options/navbar.php')
        ->addTab('mobile_nav', ['placement' => 'left'])
        ->addFields(require __DIR__ . '/theme-options/mobile_nav.php')
        ->addTab('hero', ['placement' => 'left'])
        ->addTab('subpage_hero', ['placement' => 'left'])
        ->addTab('blog', ['placement' => 'left'])
        ->addTab('contact_information', ['placement' => 'left'])
    ->addFields(require __DIR__ . '/theme-options/contact_information.php')
        ->addTab('footer', ['placement' => 'left'])
        ->addTab('copyright', ['placement' => 'left'])
    ->addTab('not_found', ['placement' => 'left'])
    ->addFields(require __DIR__ . '/theme-options/404.php')
        ->addTab('woocommerce', ['placement' => 'left'])

    ->addTab('Fonts', ['label' => 'Fonts', 'placement' => 'left'])
        ->addFields(require __DIR__ . '/theme-options/fonts.php')

    ->addTab('scripts', ['label' => 'Scripts', 'placement' => 'left'])
        ->addFields(require __DIR__ . '/theme-options/scripts.php')

        ->addTab('advanced', ['placement' => 'left'])
    ->addTrueFalse('debug', ['label' => 'Enable Debug', 'ui' => 1]);

    acf_add_local_field_group($options->build());
});
