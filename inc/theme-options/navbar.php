<?php
use StoutLogic\AcfBuilder\FieldsBuilder;

$navigationFields = new FieldsBuilder('navigation_settings');

/*
|--------------------------------------------------------------------------
|  Existing fields …
|--------------------------------------------------------------------------
*/
$navigationFields
    ->addGroup('navigation_settings_start', [
        'label' => 'Navigation Settings',
    ])
        ->addText('phone_number', [
            'label'       => 'Phone Number',
            'placeholder' => '+353 1 283 2967',
        ])
        ->addLink('contact_button', [
            'label' => 'Contact Button',
        ])

        /*
        |----------------------------------------------------------------------
        |  NEW — one row per dropdown you want to decorate with an image
        |----------------------------------------------------------------------
        */
        ->addRepeater('dropdown_images', [
            'label'        => 'Dropdown Images',
            'layout'       => 'row',
            'button_label' => 'Add Dropdown Image',
        ])
            ->addSelect('menu_item', [
                'label'   => 'Attach to menu item',
                'choices' => [],      // filled dynamically (see hook below)
                'ui'      => 1,
            ])
            ->addImage('image', [
                'label'         => 'Image',
                'return_format' => 'array',
                'preview_size'  => 'medium',
            ])
        ->endRepeater()

    ->addAccordion('navigation_settings_end')->endpoint();

return $navigationFields;
