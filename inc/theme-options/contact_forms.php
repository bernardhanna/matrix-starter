<?php
use StoutLogic\AcfBuilder\FieldsBuilder;

$forms_opts = new FieldsBuilder('contact_forms_settings', [
  'label' => 'Contact Forms',
]);

$forms_opts
  // Email defaults
  ->addText('email_from_name', [
    'label'         => 'Default From Name',
    'default_value' => get_bloginfo('name'),
  ])
  ->addEmail('email_from_address', [
    'label'         => 'Default From Email',
    'instructions'  => 'Use a domain address e.g. no-reply@tyrecare.ie',
    'default_value' => 'no-reply@tyrecare.ie',
  ])

  // CAPTCHA provider
  ->addSelect('captcha_provider', [
    'label'         => 'Captcha Provider',
    'instructions'  => 'Choose a captcha provider (or None).',
    'choices'       => [
      'none'         => 'None',
      'recaptcha_v3' => 'Google reCAPTCHA v3',
      'turnstile'    => 'Cloudflare Turnstile',
    ],
    'default_value' => 'none',
    'ui'            => 1,
  ])

  // Google reCAPTCHA v3 keys
  ->addText('recaptcha_site_key', [
    'label'            => 'reCAPTCHA Site Key',
    'conditional_logic'=> [[['field' => 'captcha_provider','operator'=>'==','value'=>'recaptcha_v3']]],
  ])
  ->addText('recaptcha_secret_key', [
    'label'            => 'reCAPTCHA Secret Key',
    'conditional_logic'=> [[['field' => 'captcha_provider','operator'=>'==','value'=>'recaptcha_v3']]],
  ])

  // Cloudflare Turnstile keys
  ->addText('turnstile_site_key', [
    'label'            => 'Turnstile Site Key',
    'conditional_logic'=> [[['field' => 'captcha_provider','operator'=>'==','value'=>'turnstile']]],
  ])
  ->addText('turnstile_secret_key', [
    'label'            => 'Turnstile Secret Key',
    'conditional_logic'=> [[['field' => 'captcha_provider','operator'=>'==','value'=>'turnstile']]],
  ]);

return $forms_opts;
