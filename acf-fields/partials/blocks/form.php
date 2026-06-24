<?php
/**
 * Flexi block: Form.
 *
 * Lets an editor drop a form into any page's Flexible Content. Either renders the
 * built-in themed contact form (template-parts/forms/contact-us.php) or embeds a
 * Gravity Form by ID, with an optional heading and intro.
 *
 * Layout slug "form" -> rendered by template-parts/flexi/form.php.
 */

use StoutLogic\AcfBuilder\FieldsBuilder;

$form = new FieldsBuilder('form', [
    'label' => 'Form',
]);

$form
    ->addText('heading', [
        'label'        => 'Heading',
        'instructions' => 'Optional heading shown above the form.',
    ])
    ->addWysiwyg('intro', [
        'label'        => 'Intro text',
        'instructions' => 'Optional text shown above the form.',
        'media_upload' => 0,
        'toolbar'      => 'basic',
        'tabs'         => 'visual',
    ])
    ->addSelect('form_source', [
        'label'         => 'Form',
        'instructions'  => 'Choose which form to display.',
        'choices'       => [
            'contact' => 'Contact form (built-in)',
            'gravity' => 'Gravity Form (by ID)',
        ],
        'default_value' => 'contact',
        'return_format' => 'value',
        'ui'            => 0,
    ])
    ->addNumber('gravity_form_id', [
        'label'        => 'Gravity Form ID',
        'instructions' => 'The numeric ID of the Gravity Form to embed.',
        'min'          => 1,
    ])
        ->conditional('form_source', '==', 'gravity')
    ->addTrueFalse('gravity_show_title', [
        'label'         => 'Show form title',
        'ui'            => 1,
        'default_value' => 1,
    ])
        ->conditional('form_source', '==', 'gravity');

return $form;
