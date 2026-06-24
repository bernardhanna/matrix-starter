<?php
// File: inc/theme-options/footer.php
//
// Rolling Donut footer + "site links" quick-links grid options.
//
// IMPORTANT: this group is built with FieldsBuilder('theme_options') and
// registered as its own local field group (it returns null so the tabbed
// loader in inc/theme-options.php skips it). The builder name "theme_options"
// makes StoutLogic regenerate the exact field keys the legacy data references
// (field_theme_options_*), so ACF loads existing option values natively and
// the repeaters are editable without data loss.
//
// Field NAMES match the option meta read by:
//   template-parts/footer/footer.php and template-parts/footer/site-links.php

use StoutLogic\AcfBuilder\FieldsBuilder;

$footer = new FieldsBuilder('theme_options', [
    'title' => 'Footer',
]);

$footer
    ->addAccordion('footer_branding_acc', ['label' => 'Branding', 'open' => 1])
        ->addImage('footer_logo', [
            'label'         => 'Footer logo',
            'instructions'  => 'Logo shown top-left in the footer.',
            'return_format' => 'id',
            'preview_size'  => 'medium',
        ])
        ->addTextarea('footer_about_text', [
            'label'        => 'About text',
            'instructions' => 'Short paragraph shown beside the logo.',
            'rows'         => 4,
        ])
    ->addAccordion('footer_branding_acc_end')->endpoint()

    ->addAccordion('footer_social_acc', ['label' => 'Social links'])
        ->addUrl('facebook_profile_url', ['label' => 'Facebook URL'])
        ->addUrl('instagram_profile_url', ['label' => 'Instagram URL'])
        ->addUrl('tiktok_profile_url', ['label' => 'TikTok URL'])
        ->addUrl('twitter_profile_url', ['label' => 'X (Twitter) URL'])
    ->addAccordion('footer_social_acc_end')->endpoint()

    ->addAccordion('footer_menus_acc', ['label' => 'Footer menu columns'])
        ->addRepeater('footer_menu_one', ['label' => 'Column 1 links', 'button_label' => 'Add link', 'layout' => 'table'])
            ->addLink('footer_menu_one_link', ['label' => 'Link', 'return_format' => 'array'])
        ->endRepeater()
        ->addRepeater('footer_menu_two', ['label' => 'Column 2 links', 'button_label' => 'Add link', 'layout' => 'table'])
            ->addLink('footer_menu_two_link', ['label' => 'Link', 'return_format' => 'array'])
        ->endRepeater()
        ->addRepeater('footer_menu_three', ['label' => 'Column 3 links', 'button_label' => 'Add link', 'layout' => 'table'])
            ->addLink('footer_menu_three_link', ['label' => 'Link', 'return_format' => 'array'])
        ->endRepeater()
        ->addRepeater('footer_menu_four', ['label' => 'Column 4 links', 'button_label' => 'Add link', 'layout' => 'table'])
            ->addLink('footer_menu_four_link', ['label' => 'Link', 'return_format' => 'array'])
        ->endRepeater()
    ->addAccordion('footer_menus_acc_end')->endpoint()

    ->addAccordion('footer_site_links_acc', ['label' => 'Site links (quick-links grid)'])
        ->addRepeater('site_links', [
            'label'        => 'Quick links',
            'instructions' => 'The boxed quick-links grid shown above the footer on key pages.',
            'button_label' => 'Add quick link',
            'layout'       => 'table',
        ])
            ->addLink('site_links', ['label' => 'Link', 'return_format' => 'array'])
        ->endRepeater()
    ->addAccordion('footer_site_links_acc_end')->endpoint()

    ->addAccordion('footer_contact_acc', ['label' => 'Contact'])
        ->addEmail('footer_contact_email', [
            'label'        => 'Contact form recipient email',
            'instructions' => 'Where contact & weddings enquiries are sent. Leave empty to use the site admin email.',
        ])
    ->addAccordion('footer_contact_acc_end')->endpoint()

    ->addAccordion('footer_legal_acc', ['label' => 'Copyright & legal'])
        ->addText('copyright_text', [
            'label'        => 'Copyright name',
            'instructions' => 'Shown after the year, e.g. "The Rolling Donut".',
        ])
        ->addImage('copyright_logo', [
            'label'         => 'Copyright logo (optional)',
            'return_format' => 'id',
            'preview_size'  => 'thumbnail',
        ])
        ->addText('copyright_text_area', ['label' => 'Copyright area text (optional)'])
        ->addRepeater('copyright_menu_four', [
            'label'        => 'Legal / copyright menu',
            'instructions' => 'Links shown in the bottom copyright bar (Accessibility is always appended automatically).',
            'button_label' => 'Add link',
            'layout'       => 'table',
        ])
            ->addLink('copyright_menu_link', ['label' => 'Link', 'return_format' => 'array'])
        ->endRepeater()
    ->addAccordion('footer_legal_acc_end')->endpoint();

$footer->setLocation('options_page', '==', 'theme-options');

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group($footer->build());
}

// Returning null keeps the tabbed loader (inc/theme-options.php) from adding
// these fields a second time as a tab.
return null;
