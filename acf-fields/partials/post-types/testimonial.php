<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

/**
 * Testimonial CPT fields.
 *
 * The testimonial quote is the post content (editor) and the person's name is
 * the post title. These extra fields match the legacy meta keys (`kudos_job`,
 * `kudos_image`) the migrated testimonials already use, and the kudos flexi
 * block reads (template-parts/flexi/kudos_block.php).
 */

$testimonial_fields = new FieldsBuilder('testimonial_details', [
    'title' => 'Testimonial details',
]);

$testimonial_fields
    ->setLocation('post_type', '==', 'testimonial')

    ->addText('kudos_job', [
        'label' => 'Role / job line',
        'instructions' => 'Shown under the name on the testimonial card (e.g. Customer, Bride, Event Planner).',
    ])
    ->addImage('kudos_image', [
        'label' => 'Avatar / logo',
        'instructions' => 'Small image shown on the testimonial card (40×40).',
        'return_format' => 'array',
        'preview_size' => 'thumbnail',
        'library' => 'all',
    ]);

return $testimonial_fields;
