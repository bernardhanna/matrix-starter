<?php
// File: inc/theme-options/topbar.php
//
// Dedicated "Top Bar" tab for the black promo bar above the header.
// Field NAMES match the option meta read by
// template-parts/header/navbar/topbar.php, so existing values are preserved
// (ACF loads option values by name).

use StoutLogic\AcfBuilder\FieldsBuilder;

$topbar = new FieldsBuilder('topbar_settings');

$topbar
    ->addTrueFalse('topbar_enabled', [
        'label'         => 'Show top bar',
        'instructions'  => 'Toggle the black promo bar above the header on or off site-wide.',
        'ui'            => 1,
        'default_value' => 1,
    ])
    ->addTextarea('topbar_text', [
        'label'        => 'Message',
        'instructions' => 'Plain text shown before the sign-up link.',
        'rows'         => 2,
    ])
    ->addLink('signup_link', [
        'label'        => 'Sign-up link',
        'instructions' => 'Optional link shown after the message.',
    ])
    ->addText('discount_text', [
        'label'        => 'Second line / discount text (optional)',
    ])
    ->addImage('icon_image', [
        'label'         => 'Icon (optional)',
        'instructions'  => 'Small icon shown to the left of the message on desktop.',
        'return_format' => 'id',
    ]);

return $topbar;
