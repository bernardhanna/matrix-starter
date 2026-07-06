<?php
$section_id = 'get-involved-form-' . wp_generate_uuid4();
$main_form_heading_id = $section_id . '-main-form-heading';
$renewal_form_heading_id = $section_id . '-renewal-form-heading';
$main_form_instructions_id = $section_id . '-main-form-instructions';
$renewal_form_instructions_id = $section_id . '-renewal-form-instructions';
$existing_member_legend_id = $section_id . '-existing-member-legend';
$existing_member_yes_id = $section_id . '-existing-member-yes';
$existing_member_no_id = $section_id . '-existing-member-no';
$terms_checkbox_id = $section_id . '-terms-consent';
$marketing_checkbox_id = $section_id . '-marketing-opt-in';

$heading = get_sub_field('heading') ?: 'Join Sanctuary Runners';
$heading_tag = get_sub_field('heading_tag') ?: 'h2';
$description = get_sub_field('description') ?: 'Fill in the form below to join your nearest Sanctuary Runners group.';

$enable_existing_member_switch = (bool) get_sub_field('enable_existing_member_switch');
$existing_member_info_text = (string) get_sub_field('existing_member_info_text');
$existing_member_trigger_text = trim((string) get_sub_field('existing_member_trigger_text'));
$renewal_heading = get_sub_field('renewal_heading') ?: 'Renew your membership';
$marketing_heading = get_sub_field('marketing_heading') ?: 'Keeping in touch with you';
$marketing_description = get_sub_field('marketing_description') ?: "Stay connected with the Sanctuary Runners community! Join our monthly newsletter for the latest news, upcoming runs, and ways to support our mission of solidarity and friendship. We'll also send occasional invitations to special events and community gatherings. It's important to us that you receive timely and relevant updates. If you'd like to hear from us, please tick the box below.";

$primary_form_name = get_sub_field('primary_form_name') ?: 'Get Involved Form';
$renewal_form_name = get_sub_field('renewal_form_name') ?: 'Existing Member Renewal Form';

$location_fields_version = (string) get_sub_field('location_fields_version');
if ($location_fields_version === '') {
    $location_fields_version = 'ireland';
}

$running_group_posts = get_posts([
    'post_type' => 'running_group',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
    'fields' => 'ids',
    'no_found_rows' => true,
]);
$group_options = [];
if (!empty($running_group_posts)) {
    foreach ($running_group_posts as $running_group_id) {
        $group_title = trim((string) get_the_title((int) $running_group_id));
        if ($group_title !== '') {
            $group_options[] = $group_title;
        }
    }
}

$country_options_raw = (string) get_sub_field('country_options');
$country_options = preg_split('/[\r\n,;]+/', $country_options_raw);
$country_options = array_values(array_unique(array_filter(array_map('trim', (array) $country_options))));
if (empty($country_options)) {
    $country_options = ['Ireland', 'United Kingdom', 'Australia', 'United States', 'Canada', 'New Zealand', 'Other'];
}

$privacy_policy_url = get_sub_field('privacy_policy_url') ?: '#';
$terms_conditions_url = get_sub_field('terms_conditions_url') ?: '#';

$background_color = get_sub_field('background_color') ?: '#ffffff';
$form_background_color = get_sub_field('form_background_color') ?: '#fef3c7';

$padding_classes = [''];
if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
        the_row();
        $screen_size = (string) get_sub_field('screen_size');
        $padding_top = (string) get_sub_field('padding_top');
        $padding_bottom = (string) get_sub_field('padding_bottom');
        if ($screen_size !== '') {
            $padding_classes[] = "{$screen_size}:pt-[{$padding_top}rem]";
            $padding_classes[] = "{$screen_size}:pb-[{$padding_bottom}rem]";
        }
    }
}

$cfg_to = get_sub_field('email_to') ?: get_option('admin_email');
$cfg_bcc = get_sub_field('email_bcc') ?: '';
$cfg_subject = get_sub_field('email_subject') ?: '';
$primary_email_subject = get_sub_field('primary_email_subject') ?: '';
$renewal_email_subject = get_sub_field('renewal_email_subject') ?: '';
$cfg_from_name = get_sub_field('from_name') ?: '';
$cfg_from_email = get_sub_field('from_email') ?: '';
$enable_autoresponder = (bool) get_sub_field('enable_autoresponder');
$autoresponder_subject = get_sub_field('autoresponder_subject') ?: '';
$autoresponder_message = get_sub_field('autoresponder_message') ?: '';
$autoresponder_include_logo = (bool) get_sub_field('autoresponder_include_logo');
$autoresponder_logo = get_sub_field('autoresponder_logo');
$autoresponder_logo_url = is_array($autoresponder_logo) ? (string) ($autoresponder_logo['url'] ?? '') : '';
$autoresponder_footer_text = (string) get_sub_field('autoresponder_footer_text');
$autoresponder_name_field = (string) get_sub_field('autoresponder_name_field');
$autoresponder_reply_to_email = (string) get_sub_field('autoresponder_reply_to_email');
$save_entries_to_db = (bool) get_sub_field('save_entries_to_db');

$build_hidden_fields = static function ($form_name, $subject_override = '') use (
    $cfg_to,
    $cfg_bcc,
    $cfg_subject,
    $cfg_from_name,
    $cfg_from_email,
    $enable_autoresponder,
    $autoresponder_subject,
    $autoresponder_message,
    $autoresponder_include_logo,
    $autoresponder_logo_url,
    $autoresponder_footer_text,
    $autoresponder_name_field,
    $autoresponder_reply_to_email,
    $save_entries_to_db
) {
    $hidden = sprintf(
        '<input type="hidden" name="action" value="theme_form_submit">
        <input type="hidden" name="theme_form_nonce" value="%1$s">
        <input type="hidden" name="_theme_form_id" value="%2$s">
        <input type="hidden" name="_submission_uid" value="%3$s">
        <input type="hidden" name="_theme_form_name" value="%4$s">',
        esc_attr(wp_create_nonce('theme_form_submit')),
        esc_attr(get_row_index()),
        esc_attr(wp_generate_uuid4()),
        esc_attr($form_name)
    );

    if ($save_entries_to_db) {
        $hidden .= '<input type="hidden" name="_theme_save_to_db" value="1">';
    }

    $hidden .= '<input type="hidden" name="_cfg_to" value="' . esc_attr($cfg_to) . '">';
    $hidden .= '<input type="hidden" name="_cfg_bcc" value="' . esc_attr($cfg_bcc) . '">';
    $effective_subject = $subject_override !== '' ? $subject_override : $cfg_subject;
    $hidden .= '<input type="hidden" name="_cfg_subject" value="' . esc_attr($effective_subject) . '">';
    $hidden .= '<input type="hidden" name="_cfg_from_name" value="' . esc_attr($cfg_from_name) . '">';
    $hidden .= '<input type="hidden" name="_cfg_from_email" value="' . esc_attr($cfg_from_email) . '">';

    if ($enable_autoresponder) {
        $hidden .= '<input type="hidden" name="_cfg_auto_enabled" value="1">';
        $hidden .= '<input type="hidden" name="_cfg_auto_subject" value="' . esc_attr($autoresponder_subject) . '">';
        $hidden .= '<input type="hidden" name="_cfg_auto_message" value="' . esc_attr($autoresponder_message) . '">';
        $hidden .= '<input type="hidden" name="_cfg_auto_include_logo" value="' . ($autoresponder_include_logo ? '1' : '0') . '">';
        $hidden .= '<input type="hidden" name="_cfg_auto_logo_url" value="' . esc_attr($autoresponder_logo_url) . '">';
        $hidden .= '<input type="hidden" name="_cfg_auto_footer" value="' . esc_attr($autoresponder_footer_text) . '">';
        $hidden .= '<input type="hidden" name="_cfg_auto_name_field" value="' . esc_attr($autoresponder_name_field) . '">';
        $hidden .= '<input type="hidden" name="_cfg_auto_reply_to" value="' . esc_attr($autoresponder_reply_to_email) . '">';
    }

    return $hidden;
};

$ireland_counties = [
    'Antrim', 'Armagh', 'Carlow', 'Cavan', 'Clare', 'Cork', 'Derry', 'Donegal',
    'Down', 'Dublin', 'Fermanagh', 'Galway', 'Kerry', 'Kildare', 'Kilkenny', 'Laois',
    'Leitrim', 'Limerick', 'Longford', 'Louth', 'Mayo', 'Meath', 'Monaghan', 'Offaly',
    'Roscommon', 'Sligo', 'Tipperary', 'Tyrone', 'Waterford', 'Westmeath', 'Wexford', 'Wicklow',
];

$australia_states = [
    'New South Wales', 'Victoria', 'Queensland', 'Western Australia',
    'South Australia', 'Tasmania', 'Australian Capital Territory', 'Northern Territory',
];

$region_label = 'City';
$region_name = 'city';
$postal_label = '';
$postal_name = '';
$postal_placeholder = '';
$default_country = '';

if ($location_fields_version === 'ireland') {
    $region_label = 'County';
    $region_name = 'county';
    $postal_label = 'Eircode';
    $postal_name = 'eircode';
    $postal_placeholder = 'Eircode';
    $default_country = 'Ireland';
} elseif ($location_fields_version === 'uk') {
    $region_label = 'County';
    $region_name = 'county';
    $postal_label = 'Postcode';
    $postal_name = 'postcode';
    $postal_placeholder = 'Postcode';
    $default_country = 'United Kingdom';
} elseif ($location_fields_version === 'australia') {
    $region_label = 'State / Territory';
    $region_name = 'state';
    $postal_label = 'Postcode';
    $postal_name = 'postcode';
    $postal_placeholder = 'Postcode';
    $default_country = 'Australia';
}
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="relative flex overflow-hidden <?php echo esc_attr(implode(' ', $padding_classes)); ?>"
    style="background-color: <?php echo esc_attr($background_color); ?>;"
>
    <div class="flex flex-col items-center pt-10 pb-14 mx-auto w-full max-w-[1024px] max-xl:px-5">
        <div class="px-20 py-6 w-full rounded-2xl lg:py-16 max-lg:px-5 max-lg:max-w-full" style="background-color: <?php echo esc_attr($form_background_color); ?>;">
            <<?php echo esc_attr($heading_tag); ?> id="<?php echo esc_attr($main_form_heading_id); ?>" class="text-3xl leading-none text-sky-800 max-lg:max-w-full">
                <?php echo esc_html($heading); ?>
            </<?php echo esc_attr($heading_tag); ?>>

            <?php if (!empty($description)) : ?>
                <p id="<?php echo esc_attr($main_form_instructions_id); ?>" class="mt-4 text-base leading-none text-sky-950 max-lg:max-w-full"><?php echo esc_html($description); ?></p>
            <?php endif; ?>

            <div
                class="mt-4"
                x-data='{
                    formView: "main",
                    existingMember: "no",
                    selectedCountry: <?php echo wp_json_encode($default_country); ?> || "",
                    irelandCounties: <?php echo wp_json_encode($ireland_counties); ?>,
                    australiaStates: <?php echo wp_json_encode($australia_states); ?>,
                    normalizeCountry(value) {
                        return String(value || "").toLowerCase().trim();
                    },
                    getPhonePrefix() {
                        const country = this.normalizeCountry(this.selectedCountry);
                        if (country === "ireland") return "+353";
                        if (country === "united kingdom" || country === "uk" || country === "great britain") return "+44";
                        if (country === "australia") return "+61";
                        return "+";
                    },
                    get phonePlaceholder() {
                        return "Phone number";
                    },
                    get locationMode() {
                        const country = this.normalizeCountry(this.selectedCountry);
                        if (country === "ireland") return "ireland";
                        if (country === "united kingdom" || country === "uk" || country === "great britain") return "uk";
                        if (country === "australia") return "australia";
                        return "global";
                    },
                    get isPostalRequired() {
                        return this.locationMode === "ireland" || this.locationMode === "uk" || this.locationMode === "australia";
                    }
                }'
            >
                <div x-show="formView === 'main'">
                    <form class="w-full" role="form" novalidate aria-labelledby="<?php echo esc_attr($main_form_heading_id); ?>" aria-describedby="<?php echo esc_attr($main_form_instructions_id); ?>" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" enctype="multipart/form-data" data-theme-form="<?php echo esc_attr(get_row_index()); ?>">
                        <?php echo $build_hidden_fields($primary_form_name, $primary_email_subject); ?>

                        <?php if ($enable_existing_member_switch) : ?>
                            <fieldset class="flex flex-wrap gap-4 items-start mt-0 w-full">
                                <div style="align-items: center; gap: 32px; width: 100%; margin-top: 8px; padding: 16px; border-radius: 8px; border: 1px solid #D2D8EA; background: #E8EBF4; display: flex;">
                                    <legend id="<?php echo esc_attr($existing_member_legend_id); ?>" style="font-size: 12px; color: #0f172a;">Are you an existing member of Sanctuary Runners?</legend>
                                    <div style="display: flex; align-items: center; gap: 24px;">
                                        <label for="<?php echo esc_attr($existing_member_yes_id); ?>" style="display: flex; align-items: center; gap: 8px; font-size: 14px; cursor: pointer;">
                                            <input id="<?php echo esc_attr($existing_member_yes_id); ?>" style="width: 32px; height: 32px;" name="existing_member" type="radio" value="yes" x-model="existingMember" @change="formView = 'renewal'">Yes
                                        </label>
                                        <label for="<?php echo esc_attr($existing_member_no_id); ?>" style="display: flex; align-items: center; gap: 8px; font-size: 14px; cursor: pointer;">
                                            <input id="<?php echo esc_attr($existing_member_no_id); ?>" style="width: 32px; height: 32px;" name="existing_member" type="radio" value="no" x-model="existingMember" checked @change="formView = 'main'">No
                                        </label>
                                    </div>
                                </div>
                            </fieldset>
                        <?php endif; ?>

                        <div class="flex flex-wrap gap-4 mt-4 w-full">
                            <div class="flex-1 min-w-60">
                                <label class="text-xs text-slate-900">Which Sanctuary Runners group would you like to join?*</label>
                                <div class="mt-0">
                                    <select class="p-4 w-full bg-white rounded border border-slate-600" name="group" required aria-required="true">
                                        <?php if (!empty($group_options)) : ?>
                                            <option value="">Select group</option>
                                            <option value="I don't know">I don't know</option>
                                            <?php foreach ($group_options as $group_option) : ?>
                                                <option value="<?php echo esc_attr($group_option); ?>"><?php echo esc_html($group_option); ?></option>
                                            <?php endforeach; ?>
                                        <?php else : ?>
                                            <option value="">No groups available</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="flex-1 min-w-60">
                                <label class="text-xs text-slate-900">Phone number</label>
                                <div class="mt-0"><input class="p-4 w-full rounded border border-slate-600" type="tel" name="phone" :placeholder="phonePlaceholder" required aria-required="true"></div>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-4 mt-4 w-full">
                            <div class="flex-1 min-w-60"><label class="text-xs text-slate-900">First name*</label><input class="p-4 mt-0 w-full rounded border border-slate-600" type="text" name="first_name" placeholder="First name" required aria-required="true"></div>
                            <div class="flex-1 min-w-60"><label class="text-xs text-slate-900">Last name*</label><input class="p-4 mt-0 w-full rounded border border-slate-600" type="text" name="last_name" placeholder="Last name" required aria-required="true"></div>
                        </div>

                        <div class="flex flex-wrap gap-4 mt-4 w-full">
                            <div class="flex-1 min-w-60"><label class="text-xs text-slate-900">Email address*</label><input class="p-4 mt-0 w-full rounded border border-slate-600" type="email" name="email" placeholder="Email address" required aria-required="true"></div>
                            <div class="flex-1 min-w-60"><label class="text-xs text-slate-900">Date of birth</label><input class="p-4 mt-0 w-full rounded border border-slate-600" type="date" name="date_of_birth"></div>
                        </div>

                        <div class="flex flex-wrap gap-4 mt-4 w-full">
                            <div class="flex-1 min-w-60"><label class="text-xs text-slate-900">Address line 1</label><input class="p-4 mt-0 w-full rounded border border-slate-600" type="text" name="address_line_1" placeholder="Address line 1"></div>
                            <div class="flex-1 min-w-60"><label class="text-xs text-slate-900">Address line 2</label><input class="p-4 mt-0 w-full rounded border border-slate-600" type="text" name="address_line_2" placeholder="Address line 2"></div>
                        </div>

                        <div class="flex flex-wrap gap-4 mt-4 w-full">
                            <div class="flex-1 min-w-60" x-show="locationMode === 'ireland'">
                                <label class="text-xs text-slate-900">County*</label>
                                <select class="p-4 mt-0 w-full rounded border border-slate-600" name="county" required aria-required="true" :disabled="locationMode !== 'ireland'">
                                    <option value="">Select county</option>
                                    <template x-for="county in irelandCounties" :key="county">
                                        <option :value="county" x-text="county"></option>
                                    </template>
                                </select>
                            </div>

                            <div class="flex-1 min-w-60" x-show="locationMode === 'uk'">
                                <label class="text-xs text-slate-900">County*</label>
                                <input class="p-4 mt-0 w-full rounded border border-slate-600" type="text" name="county" placeholder="Enter county" required aria-required="true" :disabled="locationMode !== 'uk'">
                            </div>

                            <div class="flex-1 min-w-60" x-show="locationMode === 'australia'">
                                <label class="text-xs text-slate-900">State / Territory*</label>
                                <select class="p-4 mt-0 w-full rounded border border-slate-600" name="state" required aria-required="true" :disabled="locationMode !== 'australia'">
                                    <option value="">Select state / territory</option>
                                    <template x-for="state in australiaStates" :key="state">
                                        <option :value="state" x-text="state"></option>
                                    </template>
                                </select>
                            </div>

                            <div class="flex-1 min-w-60" x-show="locationMode === 'global'">
                                <label class="text-xs text-slate-900">City*</label>
                                <input class="p-4 mt-0 w-full rounded border border-slate-600" type="text" name="city" placeholder="Enter city" required aria-required="true" :disabled="locationMode !== 'global'">
                            </div>

                            <div class="flex-1 min-w-60">
                                <label class="text-xs text-slate-900">Country*</label>
                                <select class="p-4 mt-0 w-full rounded border border-slate-600" name="country" required aria-required="true" x-model="selectedCountry">
                                    <option value="">Select country</option>
                                    <?php foreach ($country_options as $country) : ?>
                                        <option value="<?php echo esc_attr($country); ?>" <?php selected($country, $default_country); ?>><?php echo esc_html($country); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-4 mt-4 w-full">
                            <div class="flex-1 min-w-60" x-show="locationMode === 'ireland'">
                                <label class="text-xs text-slate-900">Eircode*</label>
                                <input class="p-4 mt-0 w-full rounded border border-slate-600" type="text" name="eircode" placeholder="Eircode" :required="locationMode === 'ireland'" :aria-required="(locationMode === 'ireland').toString()" :disabled="locationMode !== 'ireland'">
                            </div>
                            <div class="flex-1 min-w-60" x-show="locationMode === 'uk' || locationMode === 'australia'">
                                <label class="text-xs text-slate-900">Postcode*</label>
                                <input class="p-4 mt-0 w-full rounded border border-slate-600" type="text" name="postcode" placeholder="Postcode" :required="locationMode === 'uk' || locationMode === 'australia'" :aria-required="(locationMode === 'uk' || locationMode === 'australia').toString()" :disabled="!(locationMode === 'uk' || locationMode === 'australia')">
                            </div>
                            <div class="flex-1 min-w-60" x-show="locationMode === 'global'">
                                <label class="text-xs text-slate-900">Postal code</label>
                                <input class="p-4 mt-0 w-full rounded border border-slate-600" type="text" name="postal_code" placeholder="Postal code (optional)" :disabled="locationMode !== 'global'">
                            </div>
                        </div>

                        <div class="flex gap-2 items-center mt-4">
                            <input id="<?php echo esc_attr($terms_checkbox_id); ?>" class="w-6 h-6" type="checkbox" name="terms_conditions" required aria-required="true">
                            <label for="<?php echo esc_attr($terms_checkbox_id); ?>" class="text-xs">
                                By submitting this form you agree with the
                                <a class="font-bold text-sky-800" href="<?php echo esc_url($terms_conditions_url); ?>">Terms and Conditions</a>
                                and
                                <a class="font-bold text-sky-800" href="<?php echo esc_url($privacy_policy_url); ?>">Privacy Policy</a>.
                            </label>
                        </div>

                        <div class="mt-4 w-full">
                            <h3 style="color: #00628f; font-family: 'Public Sans'; font-size: 18px; font-style: normal; font-weight: bold; line-height: 24px; margin: 0 0 4px 0;"><?php echo esc_html($marketing_heading); ?></h3>
                            <p class="text-sm leading-6 text-slate-700" style="margin: 0 0 6px 0;"><?php echo esc_html($marketing_description); ?></p>
                            <div class="flex gap-2 items-center mt-0">
                                <input id="<?php echo esc_attr($marketing_checkbox_id); ?>" class="w-6 h-6 shrink-0" type="checkbox" name="marketing_opt_in" value="yes">
                                <label for="<?php echo esc_attr($marketing_checkbox_id); ?>" class="text-xs">Please tick the box to tell us you are happy to receive emails.</label>
                            </div>
                        </div>

                        <div class="mt-4"><div class="cf-turnstile"></div></div>

                        <button class="inline-flex gap-2 justify-center items-center px-6 py-3 mt-4 w-full text-sm font-bold text-white rounded-pill btn-primary" type="submit">
                            <span class="self-stretch my-auto">Submit</span>
                        </button>
                    </form>
                </div>

                <?php if ($enable_existing_member_switch) : ?>
                    <div x-show="formView === 'renewal'" x-cloak>
                        <button type="button" class="inline-flex gap-2 items-center px-4 py-2 mb-4 text-sm font-bold bg-white transition-colors duration-200 rounded-pill text-brand-primary-hover hover:bg-brand-accent-soft a11y-focus" @click="formView = 'main'; existingMember = 'no'">Back to main form</button>
                        <h3 id="<?php echo esc_attr($renewal_form_heading_id); ?>" class="mb-4 text-2xl font-bold leading-8 text-sky-800"><?php echo esc_html($renewal_heading); ?></h3>
                        <p id="<?php echo esc_attr($renewal_form_instructions_id); ?>" class="mb-4 text-base leading-6 text-sky-950">Complete the renewal form fields below and submit to continue your membership.</p>

                        <form class="w-full" role="form" novalidate aria-labelledby="<?php echo esc_attr($renewal_form_heading_id); ?>" aria-describedby="<?php echo esc_attr($renewal_form_instructions_id); ?>" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" enctype="multipart/form-data" data-theme-form="<?php echo esc_attr(get_row_index()); ?>">
                            <?php echo $build_hidden_fields($renewal_form_name, $renewal_email_subject); ?>
                            <div class="flex flex-wrap gap-4 mt-0 w-full">
                                <div class="flex-1 min-w-60"><label class="text-xs text-slate-900">First name*</label><input class="p-4 mt-0 w-full rounded border border-slate-600" type="text" name="first_name" placeholder="First name" required aria-required="true"></div>
                                <div class="flex-1 min-w-60"><label class="text-xs text-slate-900">Last name*</label><input class="p-4 mt-0 w-full rounded border border-slate-600" type="text" name="last_name" placeholder="Last name" required aria-required="true"></div>
                            </div>
                            <div class="flex flex-wrap gap-4 mt-4 w-full">
                                <div class="flex-1 min-w-60"><label class="text-xs text-slate-900">Email address*</label><input class="p-4 mt-0 w-full rounded border border-slate-600" type="email" name="email" placeholder="Email address" required aria-required="true"></div>
                                <div class="flex-1 min-w-60"><label class="text-xs text-slate-900">Phone number*</label><input class="p-4 mt-0 w-full rounded border border-slate-600" type="tel" name="phone" :placeholder="phonePlaceholder" required aria-required="true"></div>
                            </div>
                            <div class="flex flex-wrap gap-4 mt-4 w-full">
                                <div class="flex-1 min-w-60"><label class="text-xs text-slate-900">Address line 1</label><input class="p-4 mt-0 w-full rounded border border-slate-600" type="text" name="address_line_1" placeholder="Address line 1"></div>
                                <div class="flex-1 min-w-60"><label class="text-xs text-slate-900">Address line 2</label><input class="p-4 mt-0 w-full rounded border border-slate-600" type="text" name="address_line_2" placeholder="Address line 2"></div>
                            </div>
                            <div class="flex flex-wrap gap-4 mt-4 w-full">
                                <div class="flex-1 min-w-60" x-show="locationMode === 'ireland'">
                                    <label class="text-xs text-slate-900">County*</label>
                                    <select class="p-4 mt-0 w-full rounded border border-slate-600" name="county" required aria-required="true" :disabled="locationMode !== 'ireland'">
                                        <option value="">Select county</option>
                                        <template x-for="county in irelandCounties" :key="'renew-'+county">
                                            <option :value="county" x-text="county"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="flex-1 min-w-60" x-show="locationMode === 'uk'">
                                    <label class="text-xs text-slate-900">County*</label>
                                    <input class="p-4 mt-0 w-full rounded border border-slate-600" type="text" name="county" placeholder="Enter county" required aria-required="true" :disabled="locationMode !== 'uk'">
                                </div>
                                <div class="flex-1 min-w-60" x-show="locationMode === 'australia'">
                                    <label class="text-xs text-slate-900">State / Territory*</label>
                                    <select class="p-4 mt-0 w-full rounded border border-slate-600" name="state" required aria-required="true" :disabled="locationMode !== 'australia'">
                                        <option value="">Select state / territory</option>
                                        <template x-for="state in australiaStates" :key="'renew-'+state">
                                            <option :value="state" x-text="state"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="flex-1 min-w-60" x-show="locationMode === 'global'">
                                    <label class="text-xs text-slate-900">City*</label>
                                    <input class="p-4 mt-0 w-full rounded border border-slate-600" type="text" name="city" placeholder="Enter city" required aria-required="true" :disabled="locationMode !== 'global'">
                                </div>
                                <div class="flex-1 min-w-60">
                                    <label class="text-xs text-slate-900">Country*</label>
                                    <select class="p-4 mt-0 w-full rounded border border-slate-600" name="country" required aria-required="true" x-model="selectedCountry">
                                        <option value="">Select country</option>
                                        <?php foreach ($country_options as $country) : ?>
                                            <option value="<?php echo esc_attr($country); ?>" <?php selected($country, $default_country); ?>><?php echo esc_html($country); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-4 mt-4 w-full">
                                <div class="flex-1 min-w-60" x-show="locationMode === 'ireland'">
                                    <label class="text-xs text-slate-900">Eircode*</label>
                                    <input class="p-4 mt-0 w-full rounded border border-slate-600" type="text" name="eircode" placeholder="Eircode" :required="locationMode === 'ireland'" :aria-required="(locationMode === 'ireland').toString()" :disabled="locationMode !== 'ireland'">
                                </div>
                                <div class="flex-1 min-w-60" x-show="locationMode === 'uk' || locationMode === 'australia'">
                                    <label class="text-xs text-slate-900">Postcode*</label>
                                    <input class="p-4 mt-0 w-full rounded border border-slate-600" type="text" name="postcode" placeholder="Postcode" :required="locationMode === 'uk' || locationMode === 'australia'" :aria-required="(locationMode === 'uk' || locationMode === 'australia').toString()" :disabled="!(locationMode === 'uk' || locationMode === 'australia')">
                                </div>
                                <div class="flex-1 min-w-60" x-show="locationMode === 'global'">
                                    <label class="text-xs text-slate-900">Postal code</label>
                                    <input class="p-4 mt-0 w-full rounded border border-slate-600" type="text" name="postal_code" placeholder="Postal code (optional)" :disabled="locationMode !== 'global'">
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-4 mt-4 w-full">
                                <div class="flex-1 min-w-60">
                                    <label class="text-xs text-slate-900">Which Sanctuary Runners group are you renewing with?*</label>
                                    <select class="p-4 w-full bg-white rounded border border-slate-600" name="group" required aria-required="true">
                                        <?php if (!empty($group_options)) : ?>
                                            <option value="">Select group</option>
                                            <option value="I don't know">I don't know</option>
                                            <?php foreach ($group_options as $group_option) : ?>
                                                <option value="<?php echo esc_attr($group_option); ?>"><?php echo esc_html($group_option); ?></option>
                                            <?php endforeach; ?>
                                        <?php else : ?>
                                            <option value="">No groups available</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="flex gap-2 items-center mt-4">
                                <input class="w-6 h-6" type="checkbox" name="terms_conditions" required aria-required="true">
                                <label class="text-xs">
                                    By submitting this form you agree with the
                                    <a class="font-bold text-sky-800" href="<?php echo esc_url($terms_conditions_url); ?>">Terms and Conditions</a>
                                    and
                                    <a class="font-bold text-sky-800" href="<?php echo esc_url($privacy_policy_url); ?>">Privacy Policy</a>.
                                </label>
                            </div>
                            <div class="mt-4"><div class="cf-turnstile"></div></div>
                            <button class="inline-flex gap-2 justify-center items-center px-6 py-3 mt-4 w-full text-sm font-bold text-white rounded-pill btn-primary" type="submit">
                                <span class="self-stretch my-auto">Submit renewal</span>
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<style>
#<?php echo esc_attr($section_id); ?> input:not([type="hidden"]),
#<?php echo esc_attr($section_id); ?> select,
#<?php echo esc_attr($section_id); ?> textarea {
    border-radius: 4px !important;
    border: 1px solid var(--Gray-600, #475467) !important;
}

#<?php echo esc_attr($section_id); ?> input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]),
#<?php echo esc_attr($section_id); ?> select {
    height: 52px !important;
    padding: 0 16px !important;
    font-size: 14px !important;
    line-height: 20px !important;
    box-sizing: border-box !important;
}

#<?php echo esc_attr($section_id); ?> textarea {
    padding: 12px 16px !important;
}

#<?php echo esc_attr($section_id); ?> select {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    padding-right: 44px !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 14 14' fill='none'%3E%3Cpath d='M3 5.25L7 9.25L11 5.25' stroke='%23475467' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 16px center;
    background-size: 14px 14px;
}

#<?php echo esc_attr($section_id); ?> input:focus,
#<?php echo esc_attr($section_id); ?> select:focus,
#<?php echo esc_attr($section_id); ?> textarea:focus {
    outline: none !important;
    border-color: #1C959B !important;
    box-shadow: 0 0 0 2px rgba(28, 149, 155, 0.2) !important;
}

#<?php echo esc_attr($section_id); ?> input:not([type="hidden"])::placeholder,
#<?php echo esc_attr($section_id); ?> textarea::placeholder {
    color: var(--Gray-600, #475467) !important;
    font-family: "Public Sans", sans-serif !important;
    font-size: 14px !important;
    font-style: normal !important;
    font-weight: 400 !important;
    line-height: 20px !important;
    opacity: 1 !important;
}

#<?php echo esc_attr($section_id); ?> label {
    color: var(--Gray-800, #001929) !important;
    font-family: "Public Sans", sans-serif !important;
    font-size: 12px !important;
    font-style: normal !important;
    font-weight: 400 !important;
    line-height: 18px !important;
}

#<?php echo esc_attr($section_id); ?> button[type="submit"] {
    min-height: 52px;
    border-radius: 9999px !important;
}

#<?php echo esc_attr($section_id); ?> a {
    text-underline-offset: 2px;
}
</style>
