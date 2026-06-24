<?php
// File: inc/flexible-content-functions.php

/**
 * Flexi loader lives in inc/legacy-flexi-bridge.php (supports flexible_content + flexible_content_blocks).
 */
/**
 * Get Available Flexible Content Layouts
 * 
 * Returns an array of available layout names based on template files
 */
function get_available_flexi_layouts()
{
  $flexi_path = get_template_directory() . '/template-parts/flexi/';
  $files = glob($flexi_path . '*.php');

  return array_map(function ($file) {
    return basename($file, '.php');
  }, $files);
}

/**
 * Validate Flexible Content Layout
 * 
 * Ensures that ACF field definitions have corresponding template files
 */
function validate_flexi_layout($layout_name)
{
  $available_layouts = get_available_flexi_layouts();
  if (!in_array($layout_name, $available_layouts)) {
    error_log("Warning: ACF flexible content layout '{$layout_name}' has no corresponding template file");
    return false;
  }
  return true;
}

function force_hero_as_first_block($value, $post_id, $field)
{
  if ($field['name'] === 'flexible_content_layout') {
    $hero_block = [];
    $other_blocks = [];

    foreach ($value as $block) {
      if ($block['acf_fc_layout'] === 'hero_001') {
        $hero_block = $block;
      } else {
        $other_blocks[] = $block;
      }
    }

    // Always place hero first
    if (!empty($hero_block)) {
      array_unshift($other_blocks, $hero_block);
    }

    return $other_blocks;
  }
  return $value;
}
add_filter('acf/update_value/name=flexible_content_layout', 'force_hero_as_first_block', 10, 3);

function apply_acf_to_blog_page($query)
{
  if (!is_admin() && $query->is_home() && $query->is_main_query()) {
    $query->set('page_id', get_option('page_for_posts'));
  }
}
add_action('pre_get_posts', 'apply_acf_to_blog_page');
