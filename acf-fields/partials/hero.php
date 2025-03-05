<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

// Check if ACF is active
if (function_exists('acf_add_local_field_group')) {

    // Collect all block files
    $block_files = glob(get_template_directory() . '/acf-fields/partials/hero/*.php');
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
    $hero_content = new FieldsBuilder('hero_content_blocks');

    $hero_content
        ->setLocation('post_type', '==', 'page') // Apply to 'page' post type
    // ->and('page_template', '!=', 'front-page.php') Example of not including
    ->or('post_type', '==', 'post') // Apply to single posts as well
        ->addFlexibleContent('hero_content_blocks', [
            'label' => 'Hero Blocks',
            'button_label' => 'Add Block',
        ])
        ->addLayouts($layouts) // Dynamically add all layouts
        ->endFlexibleContent();

    // Register the field group
    acf_add_local_field_group($hero_content->build());
}
