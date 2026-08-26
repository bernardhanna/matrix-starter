<?php
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
      'font_awesome'   => 'Font Awesome',
      'flowbite'       => 'Flowbite',
      'slick'          => 'Slick JS',
      'headroom'       => 'Headroom.js',
      'leaflet'        => 'Leaflet (OpenStreetMap)',
      'cloudflare_turnstile' => 'Cloudflare Turnstile',
    ],
    'default_value' => [
      'slick',
      'font_awesome',
      'headroom',
    ],
    'layout'       => 'vertical',
  ])
  ->addText('cookie_script_id', [
    'label'        => 'CookieScript ID',
    'instructions' => '32-character ID from CookieScript (cdn.cookie-script.com/s/{ID}.js). The banner only appears on domains allowed in the CookieScript dashboard — typically the live host, not localhost. Leave empty to use the built-in live ID.',
    'placeholder'  => defined('MATRIX_RD_COOKIESCRIPT_ID') ? MATRIX_RD_COOKIESCRIPT_ID : '',
  ])
  ->addAccordion('scripts_settings_end')->endpoint();

return $fields;

