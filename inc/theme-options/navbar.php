<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$navigationFields = new FieldsBuilder('navigation_settings');

$navigationFields
  ->addGroup('navigation_settings_start', [
    'label' => 'Navigation Settings',
  ])

  ->addRadio('logo_position', [
    'label' => 'Logo Position',
    'choices' => [
      'left' => 'Left',
      'center' => 'Center'
    ],
  'default_value' => 'left',
  'return_format' => 'value',
  'required' => 1
])
  ->addAccordion('navigation_settings_end')->endpoint();
return $navigationFields;
