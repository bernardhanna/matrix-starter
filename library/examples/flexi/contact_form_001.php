<?php
$heading = get_sub_field('heading') ?: 'Get in touch';
$heading_tag = get_sub_field('heading_tag') ?: 'h2';
$description = (string) get_sub_field('description');
$privacy_policy_url = get_sub_field('privacy_policy_url') ?: '#';
$terms_conditions_url = get_sub_field('terms_conditions_url') ?: '#';
$country_mode = (string) get_sub_field('country_mode');
if ($country_mode === '') {
    $country_mode = 'ireland';
}
$ireland_county_options = [
    'Antrim', 'Armagh', 'Carlow', 'Cavan', 'Clare', 'Cork', 'Derry', 'Donegal',
    'Down', 'Dublin', 'Fermanagh', 'Galway', 'Kerry', 'Kildare', 'Kilkenny',
    'Laois', 'Leitrim', 'Limerick', 'Longford', 'Louth', 'Mayo', 'Meath',
    'Monaghan', 'Offaly', 'Roscommon', 'Sligo', 'Tipperary', 'Tyrone',
    'Waterford', 'Westmeath', 'Wexford', 'Wicklow',
];
$subject_options_raw = (string) get_sub_field('subject_options');
$subject_options = array_values(array_filter(array_map('trim', preg_split('/[\r\n]+/', $subject_options_raw))));
if (empty($subject_options)) {
    $subject_options = [
        'I would like to join my nearest group',
        'I am interested in setting up a group in my area',
        'I want to find out about taking part in an upcoming event',
        'Other',
    ];
}
$heard_about_options_raw = (string) get_sub_field('heard_about_options');
$heard_about_options = array_values(array_filter(array_map('trim', preg_split('/[\r\n]+/', $heard_about_options_raw))));
if (empty($heard_about_options)) {
    $heard_about_options = [
        'Social Media',
        'Word of Mouth',
        "I've seen you at a parkrun or event",
        'I know someone that is a Sanctuary Runner',
        'Other',
    ];
}
$uk_county_options_raw = (string) get_sub_field('uk_county_options');
$uk_county_options = array_values(array_filter(array_map('trim', preg_split('/[\r\n]+/', $uk_county_options_raw))));
if (empty($uk_county_options)) {
    $uk_county_options = ['England', 'Scotland', 'Wales', 'Northern Ireland'];
}
$new_zealand_region_options_raw = (string) get_sub_field('new_zealand_region_options');
$new_zealand_region_options = array_values(array_filter(array_map('trim', preg_split('/[\r\n]+/', $new_zealand_region_options_raw))));
if (empty($new_zealand_region_options)) {
    $new_zealand_region_options = [
        'Northland', 'Auckland', 'Waikato', 'Bay of Plenty', 'Gisborne', "Hawke's Bay", 'Taranaki',
        'Manawatū-Whanganui', 'Wellington', 'Tasman', 'Nelson', 'Marlborough', 'West Coast',
        'Canterbury', 'Otago', 'Southland',
    ];
}
$keeping_in_touch_text = (string) get_sub_field('keeping_in_touch_text');
if ($keeping_in_touch_text === '') {
    $keeping_in_touch_text = 'Please tick this box if you would like us to keep your contact details (e-mail), so we can inform you about ongoing activities, e.g. workshops, events, and webinars.';
}

$background_color = get_sub_field('background_color') ?: '#ffffff';
$form_background_color = get_sub_field('form_background_color') ?: '#fef3c7';
$autoresponder_include_logo = (bool) get_sub_field('autoresponder_include_logo');
$autoresponder_logo = get_sub_field('autoresponder_logo');
$autoresponder_logo_url = is_array($autoresponder_logo) ? (string) ($autoresponder_logo['url'] ?? '') : '';
$autoresponder_footer_text = (string) get_sub_field('autoresponder_footer_text');
$autoresponder_name_field = (string) get_sub_field('autoresponder_name_field');
$autoresponder_reply_to_email = (string) get_sub_field('autoresponder_reply_to_email');

$padding_classes = [''];
if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
        the_row();
        $screen_size = get_sub_field('screen_size');
        $padding_top = (string) get_sub_field('padding_top');
        $padding_bottom = (string) get_sub_field('padding_bottom');
        if ($screen_size !== '') {
            $padding_classes[] = "{$screen_size}:pt-[{$padding_top}rem]";
            $padding_classes[] = "{$screen_size}:pb-[{$padding_bottom}rem]";
        }
    }
}

$section_id = 'contact-form-' . esc_attr(wp_generate_uuid4());
$default_country = 'Ireland';
$location_label = 'County';
$location_options = $ireland_county_options;
if ($country_mode === 'uk') {
    $default_country = 'United Kingdom';
    $location_label = 'County / Region';
    $location_options = $uk_county_options;
} elseif ($country_mode === 'new_zealand') {
    $default_country = 'New Zealand';
    $location_label = 'Region';
    $location_options = $new_zealand_region_options;
} elseif ($country_mode === 'global') {
    $default_country = 'Global';
    $location_label = 'Where do you live?';
}
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="relative flex overflow-hidden <?php echo esc_attr(implode(' ', $padding_classes)); ?>"
    style="background-color: <?php echo esc_attr($background_color); ?>;"
>
    <div class="flex flex-col items-center pt-10 pb-14 mx-auto w-full max-w-[1024px] max-xl:px-5">
        <div class="px-20 py-6 w-full rounded-2xl lg:py-16 max-lg:px-5 max-lg:max-w-full" style="background-color: <?php echo esc_attr($form_background_color); ?>;">
            <<?php echo esc_attr($heading_tag); ?> id="<?php echo esc_attr($section_id); ?>-heading" class="text-3xl leading-none text-sky-800 max-lg:max-w-full">
                <?php echo esc_html($heading); ?>
            </<?php echo esc_attr($heading_tag); ?>>

            <?php if ($description !== '') : ?>
                <p class="mt-4 text-base leading-none text-sky-950 max-lg:max-w-full"><?php echo esc_html($description); ?></p>
            <?php endif; ?>

            <form
                class="w-full mt-4"
                role="form"
                novalidate
                aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
                action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
                method="post"
                enctype="multipart/form-data"
                data-theme-form="<?php echo esc_attr(get_row_index()); ?>"
                data-contact-structured="1"
            >
                <input type="hidden" name="action" value="theme_form_submit">
                <input type="hidden" name="theme_form_nonce" value="<?php echo esc_attr(wp_create_nonce('theme_form_submit')); ?>">
                <input type="hidden" name="_theme_form_id" value="<?php echo esc_attr(get_row_index()); ?>">
                <input type="hidden" name="_submission_uid" value="<?php echo esc_attr(wp_generate_uuid4()); ?>">
                <input type="hidden" name="_theme_form_name" value="<?php echo esc_attr(get_sub_field('form_name') ?: 'Contact Form'); ?>">
                <?php if (get_sub_field('save_entries_to_db')) : ?>
                    <input type="hidden" name="_theme_save_to_db" value="1">
                <?php endif; ?>
                <input type="hidden" name="_cfg_to" value="<?php echo esc_attr(get_sub_field('email_to') ?: get_option('admin_email')); ?>">
                <input type="hidden" name="_cfg_bcc" value="<?php echo esc_attr(get_sub_field('email_bcc') ?: ''); ?>">
                <input type="hidden" name="_cfg_subject" value="<?php echo esc_attr(get_sub_field('email_subject') ?: 'Website contact form enquiry'); ?>">
                <input type="hidden" name="_cfg_from_name" value="<?php echo esc_attr(get_sub_field('from_name') ?: ''); ?>">
                <input type="hidden" name="_cfg_from_email" value="<?php echo esc_attr(get_sub_field('from_email') ?: ''); ?>">
                <?php if (get_sub_field('enable_autoresponder')) : ?>
                    <input type="hidden" name="_cfg_auto_enabled" value="1">
                    <input type="hidden" name="_cfg_auto_subject" value="<?php echo esc_attr(get_sub_field('autoresponder_subject') ?: 'Thank you for your message'); ?>">
                    <input type="hidden" name="_cfg_auto_message" value="<?php echo esc_attr(get_sub_field('autoresponder_message') ?: ''); ?>">
                    <input type="hidden" name="_cfg_auto_include_logo" value="<?php echo $autoresponder_include_logo ? '1' : '0'; ?>">
                    <input type="hidden" name="_cfg_auto_logo_url" value="<?php echo esc_attr($autoresponder_logo_url); ?>">
                    <input type="hidden" name="_cfg_auto_footer" value="<?php echo esc_attr($autoresponder_footer_text); ?>">
                    <input type="hidden" name="_cfg_auto_name_field" value="<?php echo esc_attr($autoresponder_name_field); ?>">
                    <input type="hidden" name="_cfg_auto_reply_to" value="<?php echo esc_attr($autoresponder_reply_to_email); ?>">
                <?php endif; ?>
                <input type="hidden" name="country" value="<?php echo esc_attr($default_country); ?>">
                <input type="hidden" name="site_country_mode" value="<?php echo esc_attr($country_mode); ?>">
                <input type="hidden" name="site_country_context" value="<?php echo esc_attr($default_country); ?>">
                <input type="hidden" name="source_url" value="">
                <input type="hidden" name="subject_topic" value="">
                <input type="hidden" name="heard_about_us" value="">

                <div class="flex flex-wrap gap-4 items-start w-full max-md:max-w-full">
                    <div class="flex-1 shrink basis-0 min-w-60 max-md:max-w-full">
                        <label class="block w-full text-xs text-slate-900" for="<?php echo esc_attr($section_id); ?>-first-name">First Name*</label>
                        <input id="<?php echo esc_attr($section_id); ?>-first-name" class="w-full p-4 mt-1 bg-white text-slate-600 outline-none min-h-[52px]" name="first_name" required type="text" placeholder="Your First Name" aria-required="true">
                    </div>
                    <div class="flex-1 shrink basis-0 min-w-60 max-md:max-w-full">
                        <label class="block w-full text-xs text-slate-900" for="<?php echo esc_attr($section_id); ?>-last-name">Last Name*</label>
                        <input id="<?php echo esc_attr($section_id); ?>-last-name" class="w-full p-4 mt-1 bg-white text-slate-600 outline-none min-h-[52px]" name="last_name" required type="text" placeholder="Your Last Name" aria-required="true">
                    </div>
                </div>

                <div class="flex flex-wrap gap-4 items-start mt-4 w-full max-md:max-w-full">
                    <div class="flex-1 shrink basis-0 min-w-60 max-md:max-w-full">
                        <label class="block w-full text-xs text-slate-900" for="<?php echo esc_attr($section_id); ?>-email">Contact Email*</label>
                        <input id="<?php echo esc_attr($section_id); ?>-email" class="w-full p-4 mt-1 bg-white text-slate-600 outline-none min-h-[52px]" autocomplete="email" name="email" required type="email" placeholder="Your Contact Email" aria-required="true">
                    </div>
                    <div class="flex-1 shrink basis-0 min-w-60 max-md:max-w-full">
                        <label class="block w-full text-xs text-slate-900" for="<?php echo esc_attr($section_id); ?>-phone">Phone Number</label>
                        <input id="<?php echo esc_attr($section_id); ?>-phone" class="w-full p-4 mt-1 bg-white text-slate-600 outline-none min-h-[52px]" autocomplete="tel" name="phone" type="tel" placeholder="Your Phone Number">
                    </div>
                </div>

                <div class="mt-4 w-full max-md:max-w-full">
                    <label class="block w-full text-xs text-slate-900" for="<?php echo esc_attr($section_id); ?>-subject-select">How can we help?*</label>
                    <select id="<?php echo esc_attr($section_id); ?>-subject-select" class="contact-select w-full p-4 mt-1 bg-white text-slate-600 outline-none min-h-[52px]" name="subject_topic_select" required aria-required="true" data-other-toggle="subject">
                        <option value="">Select your topic</option>
                        <?php foreach ($subject_options as $subject_option) : ?>
                            <option value="<?php echo esc_attr($subject_option); ?>"><?php echo esc_html($subject_option); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input id="<?php echo esc_attr($section_id); ?>-subject-other" class="hidden w-full p-4 mt-2 bg-white text-slate-600 outline-none min-h-[52px]" name="subject_topic_other" type="text" placeholder="Please provide more details on how we can help" data-other-input="subject">
                </div>

                <div class="mt-4 w-full max-md:max-w-full">
                    <label class="block w-full text-xs text-slate-900" for="<?php echo esc_attr($section_id); ?>-message">Please tell us more about your enquiry*</label>
                    <textarea id="<?php echo esc_attr($section_id); ?>-message" class="w-full p-4 mt-1 bg-white text-slate-600 outline-none resize-none" name="message" required rows="6" placeholder="Your Answer" aria-required="true"></textarea>
                </div>

                <div class="flex flex-wrap gap-4 items-start mt-4 w-full max-md:max-w-full">
                    <div class="flex-1 shrink basis-0 min-w-60 max-md:max-w-full">
                        <label class="block w-full text-xs text-slate-900" for="<?php echo esc_attr($section_id); ?>-area-town">Area / Town*</label>
                        <input id="<?php echo esc_attr($section_id); ?>-area-town" class="w-full p-4 mt-1 bg-white text-slate-600 outline-none min-h-[52px]" name="area_town" required type="text" placeholder="Your Area/Town" aria-required="true">
                    </div>
                    <div class="flex-1 shrink basis-0 min-w-60 max-md:max-w-full">
                        <label class="block w-full text-xs text-slate-900" for="<?php echo esc_attr($section_id); ?>-county-region"><?php echo esc_html($location_label); ?>*</label>
                        <?php if ($country_mode === 'global') : ?>
                            <input id="<?php echo esc_attr($section_id); ?>-county-region" class="w-full p-4 mt-1 bg-white text-slate-600 outline-none min-h-[52px]" name="county_region" required type="text" placeholder="Your Answer" aria-required="true">
                        <?php else : ?>
                            <select id="<?php echo esc_attr($section_id); ?>-county-region" class="contact-select w-full p-4 mt-1 bg-white text-slate-600 outline-none min-h-[52px]" name="county_region" required aria-required="true">
                                <option value="">Select <?php echo esc_html(strtolower($location_label)); ?></option>
                                <?php foreach ($location_options as $location_option) : ?>
                                    <option value="<?php echo esc_attr($location_option); ?>"><?php echo esc_html($location_option); ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-4 w-full max-md:max-w-full">
                    <label class="block w-full text-xs text-slate-900" for="<?php echo esc_attr($section_id); ?>-heard-select">How did you hear about us?*</label>
                    <select id="<?php echo esc_attr($section_id); ?>-heard-select" class="contact-select w-full p-4 mt-1 bg-white text-slate-600 outline-none min-h-[52px]" name="heard_about_select" required aria-required="true" data-other-toggle="heard">
                        <option value="">Select an option</option>
                        <?php foreach ($heard_about_options as $heard_about_option) : ?>
                            <option value="<?php echo esc_attr($heard_about_option); ?>"><?php echo esc_html($heard_about_option); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input id="<?php echo esc_attr($section_id); ?>-heard-other" class="hidden w-full p-4 mt-2 bg-white text-slate-600 outline-none min-h-[52px]" name="heard_about_other" type="text" placeholder="Where did you hear about us?" data-other-input="heard">
                </div>

                <div class="flex flex-wrap gap-2 items-center mt-4 w-full max-md:max-w-full">
                    <input id="<?php echo esc_attr($section_id); ?>-privacy-consent" class="w-8 h-8 bg-white rounded" name="terms_conditions" required type="checkbox" aria-required="true">
                    <label class="flex-1 shrink my-auto text-xs leading-5 basis-0 text-slate-900 max-md:max-w-full cursor-pointer" for="<?php echo esc_attr($section_id); ?>-privacy-consent">
                        By sending a message you agree with the
                        <a class="font-bold text-sky-800 hover:underline focus:underline rounded" href="<?php echo esc_url($terms_conditions_url); ?>">Terms and Conditions</a>
                        and
                        <a class="font-bold text-sky-800 hover:underline focus:underline rounded" href="<?php echo esc_url($privacy_policy_url); ?>">Privacy Policy</a>.
                    </label>
                </div>

                <div class="flex flex-wrap gap-2 items-center mt-4 w-full max-md:max-w-full">
                    <input id="<?php echo esc_attr($section_id); ?>-keeping-touch" class="w-8 h-8 bg-white rounded" name="keeping_in_touch_consent" type="checkbox" value="1">
                    <label class="flex-1 shrink my-auto text-xs leading-5 basis-0 text-slate-900 max-md:max-w-full cursor-pointer" for="<?php echo esc_attr($section_id); ?>-keeping-touch">
                        <?php echo esc_html($keeping_in_touch_text); ?>
                    </label>
                </div>

                <div class="mt-4 w-full">
                    <div class="cf-turnstile"></div>
                </div>

                <button class="mt-4 inline-flex gap-2 justify-center items-center px-6 py-3 text-sm font-bold text-white rounded-[100px] w-full border-[3px] border-[var(--Turquoise-500,#1C959B)] bg-[var(--Blue-SR-400,#008BCC)] hover:bg-[var(--Blue-SR-500,#00628F)] hover:text-white focus:outline-none focus-visible:ring-0 focus-visible:border-[3px] focus-visible:border-[var(--Turquoise-500,#1C959B)] focus-visible:bg-[var(--Blue-SR-500,#00628F)] focus-visible:text-white transition-colors duration-200 btn-primary" type="submit">
                    <span class="self-stretch my-auto">Submit</span>
                </button>

                <div id="<?php echo esc_attr($section_id); ?>-form-messages" class="mt-4 hidden" role="alert" aria-live="polite"></div>
            </form>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var section = document.getElementById('<?php echo esc_js($section_id); ?>');
    if (!section) return;

    function isOtherSelection(value) {
        var normalized = String(value || '').trim().toLowerCase();
        return normalized === 'other' || normalized.indexOf('other:') === 0 || normalized.indexOf('other ') === 0;
    }

    function updateOtherField(selectEl, inputEl) {
        if (!selectEl || !inputEl) return;
        var isOther = isOtherSelection(selectEl.value);
        inputEl.classList.toggle('hidden', !isOther);
        inputEl.required = isOther;
        if (!isOther) inputEl.value = '';
    }

    section.querySelectorAll('form[data-contact-structured="1"]').forEach(function (formEl) {
        var sourceUrlInput = formEl.querySelector('input[name="source_url"]');
        if (sourceUrlInput) {
            sourceUrlInput.value = window.location.href;
        }

        var subjectSelect = formEl.querySelector('[name="subject_topic_select"]');
        var subjectOther = formEl.querySelector('[name="subject_topic_other"]');
        var subjectFinal = formEl.querySelector('[name="subject_topic"]');

        var heardSelect = formEl.querySelector('[name="heard_about_select"]');
        var heardOther = formEl.querySelector('[name="heard_about_other"]');
        var heardFinal = formEl.querySelector('[name="heard_about_us"]');

        if (subjectSelect && subjectOther) {
            subjectSelect.addEventListener('change', function () {
                updateOtherField(subjectSelect, subjectOther);
            });
            updateOtherField(subjectSelect, subjectOther);
        }
        if (heardSelect && heardOther) {
            heardSelect.addEventListener('change', function () {
                updateOtherField(heardSelect, heardOther);
            });
            updateOtherField(heardSelect, heardOther);
        }

        formEl.addEventListener('submit', function () {
            if (subjectFinal && subjectSelect) {
                var subjectValue = subjectSelect.value || '';
                var subjectIsOther = isOtherSelection(subjectValue);
                if (subjectIsOther && subjectOther && subjectOther.value.trim() !== '') {
                    subjectFinal.value = 'Other: ' + subjectOther.value.trim();
                } else {
                    subjectFinal.value = subjectValue;
                }
            }

            if (heardFinal && heardSelect) {
                var heardValue = heardSelect.value || '';
                var heardIsOther = isOtherSelection(heardValue);
                if (heardIsOther && heardOther && heardOther.value.trim() !== '') {
                    heardFinal.value = 'Other: ' + heardOther.value.trim();
                } else {
                    heardFinal.value = heardValue;
                }
            }
        });
    });

    // Works for both native selects and Nice Select transformed UI.
    function refreshOtherFieldsForAllForms() {
        section.querySelectorAll('form[data-contact-structured="1"]').forEach(function (formEl) {
            var subjectSelect = formEl.querySelector('[name="subject_topic_select"]');
            var subjectOther = formEl.querySelector('[name="subject_topic_other"]');
            var heardSelect = formEl.querySelector('[name="heard_about_select"]');
            var heardOther = formEl.querySelector('[name="heard_about_other"]');
            updateOtherField(subjectSelect, subjectOther);
            updateOtherField(heardSelect, heardOther);
        });
    }

    section.addEventListener('change', function (event) {
        var target = event.target;
        if (!target || target.tagName !== 'SELECT') return;
        if (target.name === 'subject_topic_select' || target.name === 'heard_about_select') {
            refreshOtherFieldsForAllForms();
        }
    });

    section.addEventListener('click', function (event) {
        var optionEl = event.target.closest('.nice-select .option');
        if (!optionEl) return;
        setTimeout(function () {
            refreshOtherFieldsForAllForms();
        }, 0);
    });

    function initNiceSelectInSection() {
        if (!(window.jQuery && window.jQuery.fn && typeof window.jQuery.fn.niceSelect === 'function')) return;
        var $ = window.jQuery;
        var $selects = $(section).find('select.contact-select');
        if (!$selects.length) return;

        $selects.each(function () {
            var $el = $(this);
            if ($el.next('.nice-select').length) {
                $el.niceSelect('update');
            } else {
                $el.niceSelect();
            }
        });
    }

    var attempts = 0;
    var waitTimer = setInterval(function () {
        attempts++;
        if (window.jQuery && window.jQuery.fn && typeof window.jQuery.fn.niceSelect === 'function') {
            clearInterval(waitTimer);
            initNiceSelectInSection();
            refreshOtherFieldsForAllForms();
        } else if (attempts > 80) {
            clearInterval(waitTimer);
        }
    }, 50);

});
</script>

<style type="text/css">
#<?php echo esc_attr($section_id); ?> input[type="text"],
#<?php echo esc_attr($section_id); ?> input[type="email"],
#<?php echo esc_attr($section_id); ?> input[type="tel"],
#<?php echo esc_attr($section_id); ?> select,
#<?php echo esc_attr($section_id); ?> textarea {
    border-radius: 4px !important;
    border: 1px solid var(--Gray-600, #475467) !important;
}

#<?php echo esc_attr($section_id); ?> input[type="text"],
#<?php echo esc_attr($section_id); ?> input[type="email"],
#<?php echo esc_attr($section_id); ?> input[type="tel"],
#<?php echo esc_attr($section_id); ?> select {
    min-height: 52px !important;
    height: 52px !important;
    line-height: 20px !important;
    font-size: 14px !important;
    box-sizing: border-box !important;
}

#<?php echo esc_attr($section_id); ?> select.contact-select {
    appearance: none !important;
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    padding-top: 0.875rem !important;
    padding-bottom: 0.875rem !important;
    padding-right: 2.75rem !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none'%3E%3Cpath d='M6 9L12 15L18 9' stroke='%2300628F' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E") !important;
    background-repeat: no-repeat !important;
    background-position: right 0.9rem center !important;
    background-size: 1rem 1rem !important;
}

#<?php echo esc_attr($section_id); ?> .nice-select {
    width: 100% !important;
    min-height: 52px !important;
    height: 52px !important;
    border-radius: 4px !important;
    border: 1px solid var(--Gray-600, #475467) !important;
    float: none !important;
    display: flex !important;
    align-items: center !important;
    color: #475467 !important;
    font-family: "Public Sans", sans-serif !important;
    font-size: 14px !important;
    line-height: 20px !important;
    padding: 0 2.75rem 0 1rem !important;
    background-color: #fff !important;
    transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease !important;
}

#<?php echo esc_attr($section_id); ?> .nice-select:hover {
    border-color: var(--Turquoise-500, #1C959B) !important;
    background-color: #F9FEFF !important;
}

#<?php echo esc_attr($section_id); ?> .nice-select:focus,
#<?php echo esc_attr($section_id); ?> .nice-select:focus-visible,
#<?php echo esc_attr($section_id); ?> .nice-select.open {
    outline: 2px solid var(--Turquoise-500, #1C959B) !important;
    outline-offset: 1px !important;
    border-color: var(--Turquoise-500, #1C959B) !important;
    box-shadow: 0 0 0 3px rgba(28,149,155,0.18) !important;
    background-color: #ffffff !important;
}

#<?php echo esc_attr($section_id); ?> .nice-select:active {
    border-color: var(--Blue-SR-500, #00628F) !important;
}

#<?php echo esc_attr($section_id); ?> .nice-select .current {
    line-height: 20px !important;
    color: #475467 !important;
}

#<?php echo esc_attr($section_id); ?> .nice-select:after {
    border-bottom: 2px solid #00628F !important;
    border-right: 2px solid #00628F !important;
    width: 8px !important;
    height: 8px !important;
    right: 1rem !important;
    margin-top: -5px !important;
}

#<?php echo esc_attr($section_id); ?> .nice-select .list {
    width: 100% !important;
    margin-top: 6px !important;
    border-radius: 8px !important;
    border: 1px solid var(--Gray-300, #D0D5DD) !important;
    box-shadow: 0 10px 24px rgba(0, 38, 62, 0.12) !important;
    max-height: 240px !important;
    overflow-y: auto !important;
}

#<?php echo esc_attr($section_id); ?> .nice-select .option {
    min-height: 42px !important;
    line-height: 42px !important;
    font-size: 14px !important;
}

#<?php echo esc_attr($section_id); ?> .nice-select .option:hover,
#<?php echo esc_attr($section_id); ?> .nice-select .option.focus,
#<?php echo esc_attr($section_id); ?> .nice-select .option.selected.focus {
    background-color: #EAF8FA !important;
    color: #001929 !important;
}

#<?php echo esc_attr($section_id); ?> select.contact-select:focus,
#<?php echo esc_attr($section_id); ?> input[type="text"]:focus,
#<?php echo esc_attr($section_id); ?> input[type="email"]:focus,
#<?php echo esc_attr($section_id); ?> input[type="tel"]:focus,
#<?php echo esc_attr($section_id); ?> textarea:focus {
    outline: 2px solid var(--Turquoise-500, #1C959B) !important;
    outline-offset: 1px !important;
    border-color: var(--Turquoise-500, #1C959B) !important;
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
</style>