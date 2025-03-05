<?php
// File: inc/acf/options/theme-options/fonts.php

use StoutLogic\AcfBuilder\FieldsBuilder;

$fields = new FieldsBuilder('fonts');

$fields
  ->addCheckbox('enabled_fonts', [
    'label'        => 'Enable Fonts',
    'instructions' => 'Select the fonts you want to enable.',
    'choices'      => [
      'poppins'   => 'Poppins',
      'roboto'    => 'Roboto',
      'lato'      => 'Lato',
    'kanit'      => 'Kanit',
    ],
    'default_value' => ['poppins'], // Default to enabling Poppins
    'layout'         => 'vertical',
  ]);

return $fields;
