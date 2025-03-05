<?php
// File: inc/hero-functions.php

/**
 * Get Available Hero Layouts
 * 
 * Returns an array of available hero layout names based on template files
 */
function get_available_hero_layouts()
{
  $hero_path = get_template_directory() . '/template-parts/hero/';
  $files = glob($hero_path . '*.php');

  return array_map(function ($file) {
    return basename($file, '.php');
  }, $files);
}

/**
 * Validate Hero Layout
 * 
 * Ensures that ACF field definitions have corresponding template files
 */
function validate_hero_layout($layout_name)
{
  $available_layouts = get_available_hero_layouts();
  if (!in_array($layout_name, $available_layouts)) {
    error_log("Warning: ACF hero layout '{$layout_name}' has no corresponding template file");
    return false;
  }
  return true;
}

/**
 * Load Hero Templates
 * 
 * Automatically loads hero templates based on available files in the hero directory
 */
function load_hero_templates($post_id = null)
{
  // If no post_id is provided, use the current page's ID
  if (!$post_id) {
    $post_id = is_home() ? get_option('page_for_posts') : get_the_ID();
  }

  // Debugging: Log which page ID is being used
  error_log("Loading Hero Templates for Post ID: " . $post_id);

  if ($post_id && have_rows('hero_content_blocks', $post_id)) {
    while (have_rows('hero_content_blocks', $post_id)) : the_row();
      $layout = get_row_layout();

      // Check for template file
      $template_path = get_template_directory() . '/template-parts/hero/' . $layout . '.php';
      if (file_exists($template_path)) {
        get_template_part('template-parts/hero/' . $layout);
      } else {
        error_log("Missing hero template file: {$layout}.php");
      }
    endwhile;
  } else {
    error_log("No ACF Hero Blocks found for Post ID: " . $post_id);
  }
}
