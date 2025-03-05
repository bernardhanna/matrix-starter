<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$not_found = new FieldsBuilder('not_found', [
  'label' => '404 Page',
]);

$not_found
  ->addGroup('not_found_settings', [
    'label' => '404 Page Settings',
  ])
  ->addTab('Content', ['placement' => 'top'])
  ->addText('hero_title', [
    'label' => 'Hero Title',
    'default_value' => 'Sorry, We Can’t Find That Page.',
  ])
  ->addWysiwyg('hero_text', [
    'label' => 'Hero Text',
    'default_value' => 'Here are some helpful links to get you back on track:',
  ])
  ->addRepeater('links', [
    'label' => 'Helpful Links',
    'layout' => 'table',
    'button_label' => 'Add Link',
  ])
  ->addLink('link_data', [
    'label' => 'Link',
    'return_format' => 'array',
    'default_value' => [
      'url' => home_url('/'),
      'title' => 'Go to Homepage',
      'target' => '_self',
    ],
  ])
  ->endRepeater()
  ->addTab('Design')
  ->addColorPicker('background_color', ['label' => 'Background Color', 'default_value' => '#f8f9fa'])
  ->addColorPicker('text_color', ['label' => 'Text Color', 'default_value' => '#333'])
  ->addTab('Options', ['placement' => 'left'])
  ->addTrueFalse('enable_custom_404', [
    'label' => 'Enable Custom 404 Page',
    'instructions' => 'Check to enable a custom 404 page.',
    'ui' => 1,
    'default_value' => 1,
  ])
  ->addColorPicker('error_text_color', [
    'label' => 'Error Text Color',
    'default_value' => '#d9534f',
    'return_format' => 'string',
  ])
  ->addColorPicker('error_background_color', [
    'label' => 'Error Background Color',
    'default_value' => '#f8d7da',
    'return_format' => 'string',
  ])
  ->addAccordion('not_found_options_end')->endpoint();

return $not_found;
