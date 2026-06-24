<?php
// File: inc/theme-options/instagram_feed.php
// Curated Instagram "Follow" strip shown above the footer (e.g. About page).

use StoutLogic\AcfBuilder\FieldsBuilder;

$fields = new FieldsBuilder('instagram_feed');

$fields
    ->addAccordion('instagram_feed_heading_acc', [
        'label' => 'Heading',
        'open'  => 1,
    ])
        ->addText('instagram_follow_eyebrow', [
            'label' => 'Eyebrow',
            'instructions' => 'Small label above the heading.',
            'default_value' => 'Follow',
        ])
        ->addText('instagram_follow_heading', [
            'label' => 'Heading',
            'default_value' => 'The Rolling Donut',
        ])
        ->addText('instagram_follow_handle', [
            'label' => 'Handle text',
            'instructions' => 'Shown next to the Instagram icon (e.g. The Rolling Donut).',
            'default_value' => 'The Rolling Donut',
        ])
        ->addUrl('instagram_follow_profile_url', [
            'label' => 'Profile URL',
            'instructions' => 'Link for the handle/icon. Leave empty to use the global Instagram profile URL.',
        ])
    ->addAccordion('instagram_feed_heading_acc_end')->endpoint()

    ->addAccordion('instagram_feed_posts_acc', [
        'label' => 'Posts',
    ])
        ->addRepeater('instagram_follow_items', [
            'label' => 'Instagram posts',
            'instructions' => 'Add the images you want to feature. Each links to its Instagram post (or the profile if left empty).',
            'button_label' => 'Add post',
            'layout' => 'block',
            'min' => 0,
            'collapsed' => 'caption',
        ])
            ->addImage('image', [
                'label' => 'Image',
                'return_format' => 'id',
                'preview_size' => 'medium',
                'required' => 1,
            ])
            ->addUrl('link', [
                'label' => 'Instagram post URL',
                'instructions' => 'Optional. Falls back to the profile URL.',
            ])
            ->addText('caption', [
                'label' => 'Caption / alt text',
                'instructions' => 'Used for the image alt attribute (accessibility).',
            ])
        ->endRepeater()
    ->addAccordion('instagram_feed_posts_acc_end')->endpoint();

return $fields;
