<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$counter_001 = new FieldsBuilder('counter_001', [
  'label' => 'Counter 001',
]);

$counter_001
  ->addTab('Content', ['label' => 'Content'])
  ->addText('section_title', [
    'label' => 'Section Title',
    'default_value' => 'Our Statistic',
  ])
  ->addSelect('title_tag', [
    'label' => 'Title HTML Tag',
    'choices' => [
      'h1' => 'H1',
      'h2' => 'H2',
      'h3' => 'H3',
      'h4' => 'H4',
      'h5' => 'H5',
      'h6' => 'H6',
      'p'  => 'Paragraph',
      'span' => 'Span',
    ],
    'default_value' => 'h2',
  ])
  ->addWysiwyg('section_description', [
    'label' => 'Description',
    'default_value' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
    'tabs' => 'all',
    'toolbar' => 'full',
    'media_upload' => false,
    'wrapper' => ['class' => 'wp_editor'],
  ])
  ->addRepeater('counters', [
    'label' => 'Counter Items',
    'layout' => 'block',
    'button_label' => 'Add Counter',
  ])
  ->addText('counter_number', [
    'label' => 'Number',
    'default_value' => '350',
  ])
  ->addText('counter_label', [
    'label' => 'Label',
    'default_value' => 'Happy Clients',
  ])
  ->addColorPicker('counter_bg', [
    'label' => 'Background Color',
    'default_value' => '#EFF5EC',
  ])
  ->endRepeater()

  ->addTab('Design', ['label' => 'Design'])
  ->addColorPicker('background_color', [
    'label' => 'Background Color',
    'default_value' => '#ffffff',
  ])
  ->addColorPicker('text_color', [
    'label' => 'Text Color',
    'default_value' => '#1D2939',
  ])

  ->addTab('Layout', ['label' => 'Layout'])
  ->addRepeater('padding_settings', [
    'label' => 'Padding Settings',
    'button_label' => 'Add Screen Size Padding',
  ])
  ->addSelect('screen_size', [
    'label' => 'Screen Size',
    'choices' => [
      'xxs' => 'xxs',
      'xs' => 'xs',
      'mob' => 'mob',
      'sm' => 'sm',
      'md' => 'md',
      'lg' => 'lg',
      'xl' => 'xl',
      'xxl' => 'xxl',
      'ultrawide' => 'ultrawide',
    ],
  ])
  ->addNumber('padding_top', [
    'label' => 'Padding Top',
    'append' => 'rem',
    'default_value' => 5,
  ])
  ->addNumber('padding_bottom', [
    'label' => 'Padding Bottom',
    'append' => 'rem',
    'default_value' => 5,
  ])
  ->endRepeater();

return $counter_001;
