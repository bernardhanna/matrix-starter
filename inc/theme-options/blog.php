<?php
// File: theme-options/blog.php

use StoutLogic\AcfBuilder\FieldsBuilder;

$blogFields = new FieldsBuilder('blog_fields');

$blogFields
  ->addGroup('blog_settings', [
    'label' => 'Blog Settings',
  ])

    // — Background Image Upload —
    ->addImage('hero_background_image', [
      'label'         => 'Hero Background Image',
      'instructions'  => 'Upload a hero background; if blank, we fall back to green.',
      'return_format' => 'array',
      'preview_size'  => 'medium',
    ])

    // — Hero Heading Tag & Text —
    ->addSelect('hero_heading_tag', [
      'label'        => 'Hero Heading Tag',
      'choices'      => [
        'h1'   => '<h1>',
        'h2'   => '<h2>',
        'h3'   => '<h3>',
        'h4'   => '<h4>',
        'h5'   => '<h5>',
        'h6'   => '<h6>',
        'span' => '<span>',
        'p'    => '<p>',
      ],
      'default_value'=> 'h1',
      'ui'           => 1,
    ])
    ->addText('hero_heading_text', [
      'label'        => 'Hero Heading Text',
      'default_value'=> "What's new at Tyrecare",
    ])

    // — Hero Sub-heading —
    ->addText('hero_subheading_text', [
      'label'        => 'Hero Sub-heading Text',
      'default_value'=> 'Latest and greatest.',
    ])

    // — Filter Section Title —
    ->addText('filter_section_title', [
      'label'        => 'Filter Section Title',
      'default_value'=> 'Filter by',
    ])

  ->endGroup();

return $blogFields;
