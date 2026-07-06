<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$get_involved_form_001 = new FieldsBuilder('get_involved_form_001', [
    'label' => 'Get Involved Form',
]);

$get_involved_form_001
    ->addTab('Content')
        ->addText('heading', [
            'label' => 'Heading',
            'default_value' => 'Join Sanctuary Runners',
        ])
        ->addSelect('heading_tag', [
            'label' => 'Heading Tag',
            'choices' => [
                'h1' => 'H1',
                'h2' => 'H2',
                'h3' => 'H3',
                'h4' => 'H4',
                'h5' => 'H5',
                'h6' => 'H6',
            ],
            'default_value' => 'h2',
            'ui' => 1,
            'return_format' => 'value',
        ])
        ->addTextarea('description', [
            'label' => 'Description',
            'rows' => 3,
            'default_value' => 'Fill in the form below to join your nearest Sanctuary Runners group.',
        ])
        ->addTrueFalse('enable_existing_member_switch', [
            'label' => 'Enable Existing Member Renewal Switch',
            'ui' => 1,
            'default_value' => 1,
        ])
        ->addText('existing_member_info_text', [
            'label' => 'Existing Member Info Text',
            'default_value' => 'For existing members complete this form here to renew your membership.',
            'conditional_logic' => [[['field' => 'enable_existing_member_switch', 'operator' => '==', 'value' => 1]]],
        ])
        ->addText('existing_member_trigger_text', [
            'label' => 'Existing Member Trigger Text',
            'instructions' => 'The clickable text that opens the renewal form.',
            'default_value' => 'this form here',
            'conditional_logic' => [[['field' => 'enable_existing_member_switch', 'operator' => '==', 'value' => 1]]],
        ])
        ->addText('renewal_heading', [
            'label' => 'Renewal Form Heading',
            'default_value' => 'Renew your membership',
            'conditional_logic' => [[['field' => 'enable_existing_member_switch', 'operator' => '==', 'value' => 1]]],
        ])
        ->addText('primary_form_name', [
            'label' => 'Primary Form Name',
            'default_value' => 'Get Involved Form',
        ])
        ->addText('renewal_form_name', [
            'label' => 'Renewal Form Name',
            'default_value' => 'Existing Member Renewal Form',
            'conditional_logic' => [[['field' => 'enable_existing_member_switch', 'operator' => '==', 'value' => 1]]],
        ])
        ->addText('marketing_heading', [
            'label' => 'Keeping In Touch Heading',
            'default_value' => 'Keeping in touch with you',
        ])
        ->addTextarea('marketing_description', [
            'label' => 'Keeping In Touch Description',
            'rows' => 5,
            'default_value' => "Stay connected with the Sanctuary Runners community! Join our monthly newsletter for the latest news, upcoming runs, and ways to support our mission of solidarity and friendship. We'll also send occasional invitations to special events and community gatherings. It's important to us that you receive timely and relevant updates. If you'd like to hear from us, please tick the box below.",
        ])
        ->addSelect('location_fields_version', [
            'label' => 'Location Fields Version',
            'choices' => [
                'ireland' => 'Ireland (County + Eircode + Country)',
                'uk' => 'UK (County + Postcode + Country)',
                'australia' => 'Australia (State/Territory + Postcode + Country)',
                'global' => 'Global (City + Country only)',
            ],
            'default_value' => 'ireland',
            'ui' => 1,
            'return_format' => 'value',
        ])
        ->addTextarea('country_options', [
            'label' => 'Country Dropdown Options',
            'instructions' => 'One country per line (or comma separated).',
            'rows' => 6,
            'default_value' => "Ireland\nUnited Kingdom\nAustralia\nUnited States\nCanada\nNew Zealand\nOther",
        ])
        ->addUrl('privacy_policy_url', [
            'label' => 'Privacy Policy URL',
            'default_value' => '#',
        ])
        ->addUrl('terms_conditions_url', [
            'label' => 'Terms & Conditions URL',
            'default_value' => '#',
        ])

    ->addTab('Email')
        ->addText('from_name', [
            'label' => 'From Name (override)',
        ])
        ->addEmail('from_email', [
            'label' => 'From Email (override)',
        ])
        ->addText('email_to', [
            'label' => 'Send To',
            'default_value' => get_option('admin_email'),
            'instructions' => 'One or more addresses. Separate with commas or semicolons.',
        ])
        ->addText('email_bcc', [
            'label' => 'BCC',
            'instructions' => 'Optional. Separate multiple with commas or semicolons.',
        ])
        ->addText('email_subject', [
            'label' => 'Subject',
            'default_value' => 'New get involved form enquiry',
        ])
        ->addText('primary_email_subject', [
            'label' => 'Primary Form Subject Override',
            'instructions' => 'Optional. If set, this subject is used only for the Get Involved form.',
            'default_value' => '',
        ])
        ->addText('renewal_email_subject', [
            'label' => 'Renewal Form Subject Override',
            'instructions' => 'Optional. If set, this subject is used only for the Renew Membership form.',
            'default_value' => '',
            'conditional_logic' => [[['field' => 'enable_existing_member_switch', 'operator' => '==', 'value' => 1]]],
        ])
        ->addTrueFalse('save_entries_to_db', [
            'label' => 'Save to DB?',
            'ui' => 1,
            'default_value' => 1,
        ])

    ->addTab('Autoresponder')
        ->addTrueFalse('enable_autoresponder', [
            'label' => 'Enable?',
            'ui' => 1,
            'default_value' => 0,
        ])
        ->addText('autoresponder_subject', [
            'label' => 'Autoresponder Subject',
            'default_value' => 'Thank you for your message',
            'conditional_logic' => [[['field' => 'enable_autoresponder', 'operator' => '==', 'value' => 1]]],
        ])
        ->addWysiwyg('autoresponder_message', [
            'label' => 'Autoresponder Message',
            'toolbar' => 'basic',
            'media_upload' => 0,
            'default_value' => '<p>Thank you for contacting us. We will get back to you as soon as possible.</p>',
            'conditional_logic' => [[['field' => 'enable_autoresponder', 'operator' => '==', 'value' => 1]]],
        ])
        ->addTrueFalse('autoresponder_include_logo', [
            'label' => 'Include Logo',
            'ui' => 1,
            'default_value' => 0,
            'conditional_logic' => [[['field' => 'enable_autoresponder', 'operator' => '==', 'value' => 1]]],
        ])
        ->addImage('autoresponder_logo', [
            'label' => 'Autoresponder Logo',
            'return_format' => 'array',
            'preview_size' => 'medium',
            'library' => 'all',
            'conditional_logic' => [[['field' => 'autoresponder_include_logo', 'operator' => '==', 'value' => 1]]],
        ])
        ->addText('autoresponder_footer_text', [
            'label' => 'Footer Text',
            'default_value' => '',
            'conditional_logic' => [[['field' => 'enable_autoresponder', 'operator' => '==', 'value' => 1]]],
        ])
        ->addSelect('autoresponder_name_field', [
            'label' => 'Greeting Name Field',
            'choices' => [
                '' => 'None',
                'first_name' => 'First Name',
                'name' => 'Name',
            ],
            'default_value' => 'first_name',
            'ui' => 1,
            'return_format' => 'value',
            'conditional_logic' => [[['field' => 'enable_autoresponder', 'operator' => '==', 'value' => 1]]],
        ])
        ->addEmail('autoresponder_reply_to_email', [
            'label' => 'Reply-To Email',
            'instructions' => 'Optional. Set where recipients should reply.',
            'conditional_logic' => [[['field' => 'enable_autoresponder', 'operator' => '==', 'value' => 1]]],
        ])

    ->addTab('Design')
        ->addColorPicker('background_color', [
            'label' => 'Section Background Color',
            'default_value' => '#ffffff',
        ])
        ->addColorPicker('form_background_color', [
            'label' => 'Form Background Color',
            'default_value' => '#fef3c7',
        ])

    ->addTab('Layout')
        ->addRepeater('padding_settings', [
            'label' => 'Padding Settings',
            'button_label' => 'Add Padding',
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
                'min' => 0,
                'max' => 20,
                'step' => 0.1,
                'append' => 'rem',
            ])
            ->addNumber('padding_bottom', [
                'label' => 'Padding Bottom',
                'min' => 0,
                'max' => 20,
                'step' => 0.1,
                'append' => 'rem',
            ])
        ->endRepeater();

return $get_involved_form_001;
