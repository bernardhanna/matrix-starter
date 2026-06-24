<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$navigationFields = new FieldsBuilder('navigation_settings');

$navigationFields
    ->addGroup('navigation_settings_start', [
        'label' => 'Navigation Settings',
    ])
        ->addText('phone_number', [
            'label' => 'Phone Number',
            'instructions' => 'Enter the phone number to display in the header (e.g., +353 1 283 2967)',
            'placeholder' => '+353 1 283 2967',
        ])
        ->addLink('contact_button', [
            'label' => 'Header CTA button',
            'instructions' => 'Yellow pill button on the right of the header. Used in the desktop header and mobile menu.',
        ])
        ->addImage('main_logo', [
            'label' => 'Main logo (desktop)',
            'return_format' => 'url',
        ])
        ->addImage('mobile_logo', [
            'label' => 'Mobile logo (menu closed)',
            'return_format' => 'url',
        ])
        ->addImage('mobile_logo_open', [
            'label' => 'Mobile logo (menu open)',
            'return_format' => 'url',
        ])
        ->addText('office_telephone', [
            'label' => 'Office telephone',
            'instructions' => 'Shown in top utility row (legacy field name: office_telephone).',
        ])
        ->addImage('mobile_menu_bg', [
            'label'         => 'Mobile Menu Open Background',
            'instructions'  => 'Full-screen pattern shown when the hamburger menu is open (legacy Theme Options field).',
            'return_format' => 'url',
        ])
        // Top bar fields now live in their own "Top Bar" tab (inc/theme-options/topbar.php).
    ->addAccordion('navigation_settings_end')->endpoint();

return $navigationFields;
