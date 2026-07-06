<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$contact_form_001 = new FieldsBuilder('contact_form_001', [
    'label' => 'Contact Form',
]);

$contact_form_001
    ->addTab('Content')
        ->addText('heading', [
            'label' => 'Form Heading',
            'default_value' => 'Get in touch'
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
                'p' => 'Paragraph',
                'span' => 'Span'
            ],
            'default_value' => 'h2',
        ])
        ->addTextarea('description', [
            'label' => 'Form Description',
            'instructions' => 'Optional. Leave empty to hide the description text.',
            'default_value' => '',
            'rows' => 3
        ])
        ->addMessage('form_schema_note', 'This block now uses a fixed form schema (non-WYSIWYG) to keep field structure consistent.')
        ->addSelect('country_mode', [
            'label' => 'Country Mode',
            'instructions' => 'Controls location field behavior in this contact form.',
            'choices' => [
                'ireland' => 'Ireland',
                'uk' => 'United Kingdom',
                'new_zealand' => 'New Zealand',
                'global' => 'Global',
            ],
            'default_value' => 'ireland',
            'ui' => 1,
            'return_format' => 'value',
        ])
        ->addTextarea('subject_options', [
            'label' => 'How can we help? Options',
            'instructions' => 'One option per line.',
            'rows' => 6,
            'default_value' => "I would like to join my nearest group\nI am interested in setting up a group in my area\nI want to find out about taking part in an upcoming event\nOther",
        ])
        ->addTextarea('heard_about_options', [
            'label' => 'How did you hear about us? Options',
            'instructions' => 'One option per line.',
            'rows' => 6,
            'default_value' => "Social Media\nWord of Mouth\nI've seen you at a parkrun or event\nI know someone that is a Sanctuary Runner\nOther",
        ])
        ->addTextarea('uk_county_options', [
            'label' => 'UK County / Region Options',
            'instructions' => 'One option per line.',
            'rows' => 10,
            'default_value' => "England\nScotland\nWales\nNorthern Ireland",
        ])
        ->addTextarea('new_zealand_region_options', [
            'label' => 'New Zealand Region Options',
            'instructions' => 'One option per line.',
            'rows' => 16,
            'default_value' => "Northland\nAuckland\nWaikato\nBay of Plenty\nGisborne\nHawke's Bay\nTaranaki\nManawatū-Whanganui\nWellington\nTasman\nNelson\nMarlborough\nWest Coast\nCanterbury\nOtago\nSouthland",
        ])
        ->addTextarea('keeping_in_touch_text', [
            'label' => 'Keeping in Touch Consent Text',
            'instructions' => 'Shown beside the optional keeping-in-touch checkbox.',
            'rows' => 4,
            'default_value' => 'Please tick this box if you would like us to keep your contact details (e-mail), so we can inform you about ongoing activities, e.g. workshops, events, and webinars.',
        ])
        ->addTrueFalse('enable_existing_member_form_switch', [
            'label' => 'Enable Existing Member Form Switch',
            'instructions' => 'Allow clicking a link in the main form to switch to a different renewal form.',
            'ui' => 1,
            'default_value' => 0,
        ])
        ->addText('existing_member_trigger_text', [
            'label' => 'Trigger Link Text',
            'instructions' => 'Fallback text to find the link in the main form (e.g. "this form here"). You can also add data-renew-form-trigger to the link in your form HTML.',
            'default_value' => 'this form here',
            'conditional_logic' => [[['field' => 'enable_existing_member_form_switch', 'operator' => '==', 'value' => 1]]],
        ])
        ->addText('existing_member_form_name', [
            'label' => 'Existing Member Internal Form Name',
            'instructions' => 'Used in saved entries and email subjects for the renewal form.',
            'default_value' => 'Existing Member Renewal Form',
            'conditional_logic' => [[['field' => 'enable_existing_member_form_switch', 'operator' => '==', 'value' => 1]]],
        ])
        ->addWysiwyg('existing_member_form_markup', [
            'label' => 'Existing Member Form HTML',
            'instructions' => 'Paste the renewal form HTML shown when users click the existing member link.',
            'toolbar' => 'basic',
            'media_upload' => 0,
            'wrapper' => ['class' => 'wp_editor'],
            'conditional_logic' => [[['field' => 'enable_existing_member_form_switch', 'operator' => '==', 'value' => 1]]],
        ])
        ->addSelect('location_fields_version', [
            'label' => 'Location Fields Version',
            'instructions' => 'Choose how City/Country style fields are transformed for this form block.',
            'choices' => [
                'none' => 'No transform',
                'ireland' => 'Ireland (County + Eircode + Country)',
                'uk' => 'UK (County + Postcode + Country)',
                'australia' => 'Australia (State/Territory + Postcode + Country)',
                'global' => 'Global (City + Country)',
            ],
            'default_value' => 'none',
            'ui' => 1,
            'return_format' => 'value',
        ])
        ->addTextarea('location_country_options', [
            'label' => 'Country Dropdown Options',
            'instructions' => 'Optional. One country per line (or comma separated). Used for Country select in UK/Australia/Global/Ireland modes.',
            'rows' => 6,
            'default_value' => "Ireland\nUnited Kingdom\nAustralia\nUnited States\nCanada\nNew Zealand\nOther",
        ])
        ->addUrl('privacy_policy_url', [
            'label' => 'Privacy Policy URL',
            'default_value' => '#'
        ])
        ->addUrl('terms_conditions_url', [
            'label' => 'Terms and Conditions URL',
            'default_value' => '#'
        ])

    ->addTab('Email')
        ->addText('form_name', [
            'label' => 'Internal Form Name',
            'instructions' => 'Saved with each entry & used in email subject.',
            'default_value' => 'Contact Form'
        ])
        ->addText('from_name', [
            'label' => 'From Name (override)',
            'instructions' => 'Optional. Leave empty to use Theme Options.',
        ])
        ->addEmail('from_email', [
            'label' => 'From Email (override)',
            'instructions' => 'Use an address on your domain. Leave empty to use Theme Options.',
        ])
        ->addText('email_to', [
            'label' => 'Send To',
            'instructions' => 'One or more addresses. Separate with commas or semicolons.',
            'placeholder' => 'name@domain.ie, other@domain.ie',
            'default_value' => 'devs@matrixinternet.ie',
        ])
        ->addText('email_bcc', [
            'label' => 'BCC',
            'instructions' => 'Optional. Separate multiple with commas or semicolons.',
            'placeholder' => 'first@domain.ie; second@domain.ie',
            'default_value' => 'bernard@matrixinternet.ie',
        ])
        ->addText('email_subject', [
            'label' => 'Subject',
            'default_value' => 'Website contact form enquiry'
        ])
        ->addText('existing_member_email_subject', [
            'label' => 'Existing Member Form Subject Override',
            'instructions' => 'Optional. If set, this subject is used for the existing member renewal form.',
            'default_value' => '',
            'conditional_logic' => [[['field' => 'enable_existing_member_form_switch', 'operator' => '==', 'value' => 1]]],
        ])
        ->addTrueFalse('save_entries_to_db', [
            'label' => 'Save to DB?',
            'ui' => 1,
            'default_value' => 1
        ])

    ->addTab('Autoresponder')
        ->addTrueFalse('enable_autoresponder', [
            'label' => 'Enable?',
            'ui' => 1
        ])
        ->addText('autoresponder_subject', [
            'label' => 'Autoresponder Subject',
            'conditional_logic' => [[['field' => 'enable_autoresponder', 'operator' => '==', 'value' => 1]]],
            'default_value' => 'Thank you for your message'
        ])
        ->addWysiwyg('autoresponder_message', [
            'label' => 'Autoresponder Message',
            'conditional_logic' => [[['field' => 'enable_autoresponder', 'operator' => '==', 'value' => 1]]],
            'wrapper' => ['class' => 'wp_editor'],
            'default_value' => '<p>Thank you for contacting us. We will get back to you as soon as possible.</p>'
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
            'default_value' => '#ffffff'
        ])
        ->addColorPicker('form_background_color', [
            'label' => 'Form Background Color',
            'default_value' => '#fef3c7'
        ])

    ->addTab('Layout')
        ->addRepeater('padding_settings', [
            'label' => 'Padding Settings',
            'instructions' => 'Customize padding for different screen sizes.',
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

return $contact_form_001;
