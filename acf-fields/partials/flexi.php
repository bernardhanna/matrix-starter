<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

// Check if ACF is active
if (function_exists('acf_add_local_field_group')) {

    // Collect all block files
    $block_files = glob(get_template_directory() . '/acf-fields/partials/blocks/*.php');
    $layouts = [];

    foreach ($block_files as $file) {
        $block = require_once $file;
        if ($block instanceof FieldsBuilder) {
            $layouts[] = $block;
        } else {
            error_log("Invalid block returned from file: {$file}");
        }
    }

    // Create the Flexible Content field group
    $flexible_content = new FieldsBuilder('flexible_content_blocks');

    $flexible_content
        ->setLocation('post_type', '==', 'page') // Apply to 'page' post type
    ->or('page_template', '==', 'front-page.php') // Specifically apply to front page template
    ->or('page_type', '==', 'front') // Apply to front page (when set as homepage in WP settings)
    ->or('post_type', '==', 'post') // Apply to single posts as well
        ->addFlexibleContent('flexible_content_blocks', [
            'label' => 'Page Content Blocks',
            'button_label' => 'Add Block',
        ])
        ->addLayouts($layouts) // Dynamically add all layouts
        ->endFlexibleContent();

    // Register the field group
    acf_add_local_field_group($flexible_content->build());
}
