<?php
// === Variables (always use get_sub_field) ===
$heading = get_sub_field('heading') ?: 'Newsletter signup';
$heading_tag = get_sub_field('heading_tag') ?: 'h2';
$icon_image = get_sub_field('icon_image');
$background_color = get_sub_field('background_color') ?: '#fef3c7';
$form_background_color = get_sub_field('form_background_color') ?: '#000000';
$text_color = get_sub_field('text_color') ?: '#ffffff';
$button_color = get_sub_field('button_color') ?: '#fde047';
$button_text_color = get_sub_field('button_text_color') ?: '#1e293b';

// Form configuration
$brevo_list_ids = get_sub_field('brevo_list_ids') ?: '';
$privacy_policy_url = get_sub_field('privacy_policy_url') ?: '#';
$terms_conditions_url = get_sub_field('terms_conditions_url') ?: '#';
$captcha_provider = function_exists('get_field') ? (string) (get_field('captcha_provider', 'option') ?: 'none') : 'none';
$captcha_provider = strtolower(trim($captcha_provider));
$turnstile_site_key = function_exists('get_field') ? (string) get_field('turnstile_site_key', 'option') : '';
$turnstile_site_key = trim($turnstile_site_key);
$newsletter_status = isset($_GET['newsletter_status']) ? sanitize_text_field((string) wp_unslash($_GET['newsletter_status'])) : '';
$newsletter_message = isset($_GET['newsletter_message']) ? sanitize_text_field((string) wp_unslash($_GET['newsletter_message'])) : '';

// Padding classes
$padding_classes = ['pt-5', 'pb-5'];
if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
        the_row();
        $screen_size = get_sub_field('screen_size');
        $padding_top = get_sub_field('padding_top');
        $padding_bottom = get_sub_field('padding_bottom');
        if ($screen_size !== '') {
            $padding_classes[] = "{$screen_size}:pt-[{$padding_top}rem]";
            $padding_classes[] = "{$screen_size}:pb-[{$padding_bottom}rem]";
        }
    }
}

// Unique section id
$section_id = 'newsletter-' . esc_attr(wp_generate_uuid4());

// Icon fallback
$icon_src = $icon_image ? wp_get_attachment_image_url($icon_image, 'full') : '';
$icon_alt = $icon_image ? get_post_meta($icon_image, '_wp_attachment_image_alt', true) : 'Newsletter icon';
if (!$icon_alt) {
    $icon_alt = 'Newsletter icon';
}
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="relative flex overflow-hidden <?php echo esc_attr(implode(' ', $padding_classes)); ?>"
    style="background-color: <?php echo esc_attr($background_color); ?>;"
    role="region"
    aria-labelledby="<?php echo esc_attr($section_id); ?>-heading">

    <div class="flex flex-col items-center mx-auto w-full max-w-container max-lg:px-5">
        <div class="flex overflow-hidden flex-wrap gap-10 justify-center items-center px-8 py-8 w-full rounded-lg md:px-14 md:py-14 bg-[linear-gradient(313deg,#059DED_24.08%,#28B2FA_63%)]">

            <!-- Heading Section -->
            <header class="flex gap-2 items-start">
                <?php if ($icon_src): ?>
                    <div class="flex gap-2 items-center pt-1 w-8" role="img" aria-label="<?php echo esc_attr($icon_alt); ?>">
                        <img
                            src="<?php echo esc_url($icon_src); ?>"
                            alt="<?php echo esc_attr($icon_alt); ?>"
                            class="object-contain w-8 h-8"
                            width="32"
                            height="32"
                        />
                    </div>
                <?php endif; ?>

                <?php if ($heading): ?>
                    <<?php echo esc_attr($heading_tag); ?>
                        id="<?php echo esc_attr($section_id); ?>-heading"
                        class="text-2xl font-light leading-10 md:text-3xl lg:max-w-[200px] break-words"
                        style="color: <?php echo esc_attr($text_color); ?>;">
                        <?php echo esc_html($heading); ?>
                    </<?php echo esc_attr($heading_tag); ?>>
                <?php endif; ?>
            </header>

            <!-- Newsletter Form -->
            <div class="w-full max-w-2xl">
                <form
                    class="w-full newsletter-form"
                    data-brevo-newsletter="1"
                    action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>"
                    method="post"
                    role="form"
                    aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
                    novalidate>

                    <!-- Hidden fields for Brevo integration -->
                    <input type="hidden" name="action" value="matrix_subscribe_brevo">
                    <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('matrix_brevo_subscribe')); ?>">
                    <input type="hidden" name="list_ids" value="<?php echo esc_attr($brevo_list_ids); ?>">

                    <div class="flex flex-wrap gap-4 items-center w-full text-sm leading-none">
                        <!-- Full Name Field -->
                        <div class="w-full md:w-64 min-w-60">
                            <label for="newsletter-name-<?php echo esc_attr($section_id); ?>" class="sr-only">
                                Full name
                            </label>
                            <div class="flex justify-between items-center w-full bg-white rounded border border-gray-300 focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-blue-500">
                                <input
                                    type="text"
                                    id="newsletter-name-<?php echo esc_attr($section_id); ?>"
                                    name="name"
                                    placeholder="Full name"
                                    required
                                    aria-required="true"
                                    aria-describedby="newsletter-name-error-<?php echo esc_attr($section_id); ?>"
                                    class="flex-1 w-full placeholder-gray-400 text-gray-700 bg-transparent border-none outline-none !border-none"
                                />
                            </div>
                            <div id="newsletter-name-error-<?php echo esc_attr($section_id); ?>" class="hidden mt-1 text-xs text-red-600" role="alert" aria-live="polite"></div>
                        </div>

                        <!-- Email Field with Subscribe Button -->
                        <div class="flex flex-1 min-w-60">
                            <div class="flex-1">
                                <label for="newsletter-email-<?php echo esc_attr($section_id); ?>" class="sr-only">
                                    Email address
                                </label>
                                <div class="flex justify-between items-center w-full bg-white rounded-l border border-r-0 border-gray-300 focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-blue-500">
                                    <input
                                        type="email"
                                        id="newsletter-email-<?php echo esc_attr($section_id); ?>"
                                        name="email"
                                        placeholder="Email address"
                                        required
                                        aria-required="true"
                                        aria-describedby="newsletter-email-error-<?php echo esc_attr($section_id); ?>"
                                        autocomplete="email"
                                        class="flex-1 w-full placeholder-gray-400 text-gray-700 bg-transparent border-none outline-none !border-none"
                                    />
                                </div>
                                <div id="newsletter-email-error-<?php echo esc_attr($section_id); ?>" class="hidden mt-1 text-xs text-red-600" role="alert" aria-live="polite"></div>
                            </div>

                            <button
                                type="submit"
                                class="flex gap-2 justify-center items-center px-6 py-4 font-bold whitespace-nowrap rounded-r transition-colors duration-200 focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 h-[50px] rounded-r-[100px] hover:!bg-[#fcf4c5]"
                                style="background-color: <?php echo esc_attr($button_color); ?>; color: <?php echo esc_attr($button_text_color); ?>;"
                                aria-describedby="newsletter-submit-help-<?php echo esc_attr($section_id); ?>">
                                <span>Subscribe</span>
                            </button>
                        </div>
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="flex flex-wrap gap-2 items-start mt-4 w-full md:items-center">
                        <div class="flex overflow-hidden flex-col flex-shrink-0 justify-center items-center w-6 rounded min-h-6">
                            <input
                                type="checkbox"
                                id="newsletter-consent-<?php echo esc_attr($section_id); ?>"
                                name="consent"
                                required
                                aria-required="true"
                                aria-describedby="newsletter-consent-error-<?php echo esc_attr($section_id); ?>"
                                class="w-4 h-4 text-blue-600 bg-gray-100 rounded border-white focus:ring-blue-500 focus:ring-2"
                            />
                        </div>
                        <label
                            for="newsletter-consent-<?php echo esc_attr($section_id); ?>"
                            class="flex-1 text-xs leading-none cursor-pointer"
                            style="color: <?php echo esc_attr($text_color); ?>;">
                            By signing to our newsletter, you agree to our
                            <a href="<?php echo esc_url($terms_conditions_url); ?>"
                               class="underline rounded hover:no-underline focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1"
                               target="_blank"
                               rel="noopener">
                                Terms & Conditions
                            </a>
                            &
                            <a href="<?php echo esc_url($privacy_policy_url); ?>"
                               class="underline rounded hover:no-underline focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1"
                               target="_blank"
                               rel="noopener">
                                Privacy Policy
                            </a>.
                        </label>
                        <div id="newsletter-consent-error-<?php echo esc_attr($section_id); ?>" class="hidden mt-1 w-full text-xs text-red-600" role="alert" aria-live="polite"></div>
                    </div>

                    <?php if ($captcha_provider === 'turnstile' && $turnstile_site_key !== ''): ?>
                        <div class="mt-4">
                            <div class="cf-turnstile" data-sitekey="<?php echo esc_attr($turnstile_site_key); ?>"></div>
                        </div>
                    <?php endif; ?>

                    <div id="newsletter-submit-help-<?php echo esc_attr($section_id); ?>" class="mt-2 text-xs sr-only">
                        Please fill in all required fields and accept the terms to subscribe.
                    </div>

                    <!-- Success/Error Messages -->
                    <div
                        id="newsletter-messages-<?php echo esc_attr($section_id); ?>"
                        class="<?php echo $newsletter_message ? 'mt-4' : 'hidden mt-4'; ?>"
                        role="alert"
                        aria-live="polite">
                        <?php if ($newsletter_message): ?>
                            <div class="<?php echo $newsletter_status === 'success' ? 'text-green-700' : 'text-red-700'; ?>">
                                <?php echo esc_html($newsletter_message); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set the global Brevo nonce if not already set
    if (typeof window.matrixBrevoNonce === 'undefined') {
        window.matrixBrevoNonce = '<?php echo wp_create_nonce('matrix_brevo_subscribe'); ?>';
    }

    // Form validation enhancement
    const form = document.querySelector('#<?php echo esc_js($section_id); ?> form[data-brevo-newsletter]');
    if (form) {
        const nameInput = form.querySelector('input[name="name"]');
        const emailInput = form.querySelector('input[name="email"]');
        const consentInput = form.querySelector('input[name="consent"]');

        // Real-time validation
        function validateField(input, errorId, validationFn) {
            const errorEl = document.getElementById(errorId);
            const isValid = validationFn(input.value);

            if (isValid) {
                input.classList.remove('border-red-500');
                errorEl.classList.add('hidden');
                errorEl.textContent = '';
            } else {
                input.classList.add('border-red-500');
                errorEl.classList.remove('hidden');
            }

            return isValid;
        }

        nameInput.addEventListener('blur', function() {
            validateField(this, 'newsletter-name-error-<?php echo esc_js($section_id); ?>', function(value) {
                if (!value.trim()) {
                    document.getElementById('newsletter-name-error-<?php echo esc_js($section_id); ?>').textContent = 'Name is required';
                    return false;
                }
                return true;
            });
        });

        emailInput.addEventListener('blur', function() {
            validateField(this, 'newsletter-email-error-<?php echo esc_js($section_id); ?>', function(value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!value.trim()) {
                    document.getElementById('newsletter-email-error-<?php echo esc_js($section_id); ?>').textContent = 'Email is required';
                    return false;
                } else if (!emailRegex.test(value)) {
                    document.getElementById('newsletter-email-error-<?php echo esc_js($section_id); ?>').textContent = 'Please enter a valid email address';
                    return false;
                }
                return true;
            });
        });

        consentInput.addEventListener('change', function() {
            const errorEl = document.getElementById('newsletter-consent-error-<?php echo esc_js($section_id); ?>');
            if (this.checked) {
                errorEl.classList.add('hidden');
                errorEl.textContent = '';
            } else {
                errorEl.classList.remove('hidden');
                errorEl.textContent = 'You must accept the terms and conditions';
            }
        });
    }
});
</script>
