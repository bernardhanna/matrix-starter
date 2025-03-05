<?php
// File: inc/acf/options/theme-options/scripts.php

use StoutLogic\AcfBuilder\FieldsBuilder;

$fields = new FieldsBuilder('scripts');

$fields
  ->addAccordion('scripts_settings_start', [
    'label' => 'Enable & Disable Scripts and Styles',
  ])
  ->addCheckbox('enabled_scripts', [
    'label'        => 'Enable Scripts and Styles',
    'instructions' => 'Select the scripts and styles you want to enable.',
    'choices'      => [
    'font_awesome' => 'Font Awesome',
    'flowbite'     => 'Flowbite',
    'slick'     => 'Slick JS',
    'hamburger_css' => 'Hamburgers CSS',
    //'headhesive'   => 'Headhesive',
    'headroom'      => 'Headroom.js',
    ],
  'default_value' => [
    //'headhesive', 
    'slick',
    'font_awesome',
    'flowbite',
    'hamburger_css',
    'headroom' 
  ], // Default to enabled
    'layout'         => 'vertical',
  ])
  ->addAccordion('scripts_settings_end')->endpoint();
return $fields;
