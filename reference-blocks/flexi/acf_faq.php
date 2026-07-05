<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$faq = new FieldsBuilder('faq', [
    'label' => 'FAQ',
]);

$faq
  ->addTab('Content', ['placement' => 'top'])
    ->addText('heading', ['label' => 'Section heading'])
    ->addRepeater('faq_items', ['label' => 'FAQ items', 'button_label' => 'Add question'])
      ->addText('question', ['label' => 'Question'])
      ->addWysiwyg('answer', ['label' => 'Answer', 'media_upload' => 0, 'toolbar' => 'basic'])
    ->endRepeater()

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

return $faq;
