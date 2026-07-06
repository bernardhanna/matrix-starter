<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$cta_row = new FieldsBuilder('cta_row', [
    'label' => 'CTA Row',
]);

$cta_row
  ->addTab('Content', ['placement' => 'top'])
    ->addText('heading', ['label' => 'Heading'])
    ->addTextarea('intro', ['label' => 'Intro text', 'rows' => 3])
    ->addLink('content_button', ['label' => 'Button', 'return_format' => 'array'])

  ->addTab('Design', ['placement' => 'top'])
    ->addColorPicker('background_color', ['label' => 'Background colour'])

  ->addTab('Layout', ['placement' => 'top'])
    ->addRepeater('padding_settings', [
        'label' => 'Padding Settings',
        'button_label' => 'Add Screen Size Padding',
    ])
      ->addSelect('screen_size', [
          'label' => 'Screen Size',
          'choices' => [
              'xxs' => 'xxs', 'xs' => 'xs', 'mob' => 'mob', 'sm' => 'sm',
              'md' => 'md', 'lg' => 'lg', 'xl' => 'xl', 'xxl' => 'xxl', 'ultrawide' => 'ultrawide',
          ],
      ])
      ->addNumber('padding_top', ['label' => 'Padding Top', 'min' => 0, 'max' => 20, 'step' => 0.1, 'append' => 'rem'])
      ->addNumber('padding_bottom', ['label' => 'Padding Bottom', 'min' => 0, 'max' => 20, 'step' => 0.1, 'append' => 'rem'])
    ->endRepeater();

return $cta_row;
