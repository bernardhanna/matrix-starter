<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$content_002 = new FieldsBuilder('content_002', [
    'label' => 'Content 002',
]);

$content_002
  ->addTab('Content', ['placement' => 'top'])
    ->addText('heading', ['label' => 'Heading'])
    ->addSelect('heading_tag', [
        'label' => 'Heading tag',
        'choices' => ['h2' => 'h2', 'h3' => 'h3', 'h4' => 'h4'],
        'default_value' => 'h2',
    ])
    ->addWysiwyg('body', ['label' => 'Body', 'media_upload' => 0, 'toolbar' => 'full'])
    ->addLink('content_button', ['label' => 'Button', 'return_format' => 'array'])
    ->addImage('image', ['label' => 'Image', 'return_format' => 'id'])

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

return $content_002;
