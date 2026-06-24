<?php
/**
 * Contact Us — Theme_Forms (replaces Gravity Forms on /contact-us/).
 *
 * Captcha: Theme Options → Contact Forms (reCAPTCHA v3 or Turnstile).
 */
if (!defined('ABSPATH')) {
    exit;
}

require_once get_template_directory() . '/inc/helpers/theme-forms-display.php';
require_once get_template_directory() . '/inc/helpers/gravity-forms-autoresponder.php';

$form_uid        = 'contact-us-' . wp_generate_uuid4();
$recipient       = matrix_rd_contact_form_recipient();
$admin_subject = matrix_rd_contact_form_admin_subject();
$logo_url      = matrix_rd_contact_autoresponder_logo_url();
$privacy_url  = function_exists('get_privacy_policy_url') ? get_privacy_policy_url() : '';
$show_captcha = matrix_theme_form_captcha_enabled();
$captcha_html = matrix_theme_form_captcha_markup([
    'class' => 'cf-turnstile rd-contact-captcha',
    'theme' => 'light',
]);
?>
<div class="rd-contact-form-wrap w-full">
  <form
    id="<?php echo esc_attr($form_uid); ?>"
    class="rd-contact-form flex flex-col justify-center w-full md:boxshadow-two md:rounded-form md:border md:bg-white md:border-black-full md:border-solid mobile:px-4 md:px-9 pt-8"
    action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
    method="post"
    enctype="multipart/form-data"
    novalidate
    data-theme-form="contact-us"
    data-contact-structured="1"
    data-success-message="<?php echo esc_attr__('Thanks for your message — we will be in touch ASAP.', 'matrix-starter'); ?>"
  >
    <input type="hidden" name="action" value="theme_form_submit">
    <input type="hidden" name="theme_form_nonce" value="<?php echo esc_attr(wp_create_nonce('theme_form_submit')); ?>">
    <input type="hidden" name="_theme_form_id" value="33">
    <input type="hidden" name="_submission_uid" value="<?php echo esc_attr(wp_generate_uuid4()); ?>">
    <input type="hidden" name="_theme_form_name" value="<?php echo esc_attr__('Contact Us', 'matrix-starter'); ?>">
    <input type="hidden" name="_theme_save_to_db" value="1">
    <input type="hidden" name="_cfg_to" value="<?php echo esc_attr($recipient); ?>">
    <input type="hidden" name="_cfg_subject" value="<?php echo esc_attr($admin_subject); ?>">
    <input type="hidden" name="source_url" value="">
    <?php
    matrix_theme_form_autoresponder_hidden_fields(
        MATRIX_RD_CONTACT_GF_FORM_ID,
        [
            'include_logo' => $logo_url !== '',
            'logo_url'     => $logo_url,
            'reply_to'     => $recipient,
        ]
    );
    ?>

    <div class="flex flex-col w-full">
      <div class="flex flex-col w-full tablet-sm:flex-row md:justify-between">
        <div class="flex flex-col w-full">
          <label class="block text-black-full text-xs-font md:text-mob-md-font font-reg420 ml-2" for="<?php echo esc_attr($form_uid); ?>-first-name">
            <?php esc_html_e('First Name*', 'matrix-starter'); ?>
          </label>
          <div class="relative mb-4 md:mb-10">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none" aria-hidden="true">
              <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 20 20"><path fill="#484848" d="M9.993 10.573a4.5 4.5 0 1 0 0-9a4.5 4.5 0 0 0 0 9ZM10 0a6 6 0 0 1 3.04 11.174c3.688 1.11 6.458 4.218 6.955 8.078c.047.367-.226.7-.61.745c-.383.045-.733-.215-.78-.582c-.54-4.19-4.169-7.345-8.57-7.345c-4.425 0-8.101 3.161-8.64 7.345c-.047.367-.397.627-.78.582c-.384-.045-.657-.378-.61-.745c.496-3.844 3.281-6.948 6.975-8.068A6 6 0 0 1 10 0Z"/></svg>
            </div>
            <input
              id="<?php echo esc_attr($form_uid); ?>-first-name"
              class="text-field rounded-lg-x h-input text-black-secondary text-xs-font md:text-mob-md-font font-laca font-lig pl-11 flex w-full tablet-sm:max-w-max-95"
              name="first_name"
              type="text"
              placeholder="<?php esc_attr_e('First Name', 'matrix-starter'); ?>"
              autocomplete="given-name"
              required
            >
          </div>
        </div>
        <div class="flex flex-col w-full">
          <label class="block text-black-full text-xs-font md:text-mob-md-font font-reg420 ml-2" for="<?php echo esc_attr($form_uid); ?>-last-name">
            <?php esc_html_e('Surname*', 'matrix-starter'); ?>
          </label>
          <div class="relative mb-4 md:mb-10">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none" aria-hidden="true">
              <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 20 20"><path fill="#484848" d="M9.993 10.573a4.5 4.5 0 1 0 0-9a4.5 4.5 0 0 0 0 9ZM10 0a6 6 0 0 1 3.04 11.174c3.688 1.11 6.458 4.218 6.955 8.078c.047.367-.226.7-.61.745c-.383.045-.733-.215-.78-.582c-.54-4.19-4.169-7.345-8.57-7.345c-4.425 0-8.101 3.161-8.64 7.345c-.047.367-.397.627-.78.582c-.384-.045-.657-.378-.61-.745c.496-3.844 3.281-6.948 6.975-8.068A6 6 0 0 1 10 0Z"/></svg>
            </div>
            <input
              id="<?php echo esc_attr($form_uid); ?>-last-name"
              class="text-field rounded-lg-x h-input text-black-secondary text-xs-font md:text-mob-md-font font-laca font-lig pl-11 flex w-full tablet-sm:max-w-max-95"
              name="last_name"
              type="text"
              placeholder="<?php esc_attr_e('Surname', 'matrix-starter'); ?>"
              autocomplete="family-name"
              required
            >
          </div>
        </div>
      </div>

      <div class="flex flex-col w-full tablet-sm:flex-row md:justify-between">
        <div class="flex flex-col w-full">
          <label class="block text-black-full text-xs-font md:text-mob-md-font font-reg420 ml-2" for="<?php echo esc_attr($form_uid); ?>-email">
            <?php esc_html_e('Email*', 'matrix-starter'); ?>
          </label>
          <div class="relative mb-4 md:mb-10">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none" aria-hidden="true">
              <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 32 32"><path fill="#484848" d="M28 6H4a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h24a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2Zm-2.2 2L16 14.78 6.2 8ZM4 24V8.91l11.43 7.91a1 1 0 0 0 1.14 0L28 8.91V24Z"/></svg>
            </div>
            <input
              id="<?php echo esc_attr($form_uid); ?>-email"
              class="rounded-lg-x h-input text-black-secondary text-xs-font md:text-mob-md-font font-laca font-light pl-11 flex w-full tablet-sm:max-w-max-95"
              name="email"
              type="email"
              placeholder="<?php esc_attr_e('Email Address', 'matrix-starter'); ?>"
              autocomplete="email"
              required
            >
          </div>
        </div>
        <div class="flex flex-col w-full">
          <label class="block text-black-full text-xs-font md:text-mob-md-font font-reg420 ml-2" for="<?php echo esc_attr($form_uid); ?>-phone">
            <?php esc_html_e('Telephone', 'matrix-starter'); ?>
          </label>
          <div class="relative mb-4 md:mb-10">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none" aria-hidden="true">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="25" viewBox="0 0 24 25" fill="none" aria-hidden="true">
                <path d="M20.999 16.9207V20.4569C20.9991 20.7101 20.9032 20.9539 20.7306 21.1392C20.558 21.3244 20.3216 21.4373 20.0691 21.455C19.6321 21.485 19.2751 21.501 18.9991 21.501C10.1626 21.501 3 14.3376 3 5.50011C3 5.2241 3.015 4.86708 3.046 4.43005C3.06372 4.17748 3.17657 3.94104 3.36178 3.76842C3.547 3.59581 3.79078 3.49989 4.04394 3.5H7.57975C7.70378 3.49987 7.82343 3.54586 7.91546 3.62903C8.00748 3.71219 8.06531 3.8266 8.07772 3.95003C8.10072 4.18004 8.12172 4.36305 8.14171 4.50206C8.34044 5.88906 8.74768 7.23804 9.34965 8.50328C9.44464 8.70329 9.38265 8.9423 9.20266 9.07031L7.04478 10.6124C8.36416 13.687 10.8141 16.1372 13.8884 17.4568L15.4283 15.3027C15.4913 15.2147 15.5831 15.1515 15.6878 15.1243C15.7925 15.0971 15.9034 15.1075 16.0013 15.1536C17.2662 15.7545 18.6147 16.1608 20.0011 16.3587C20.14 16.3787 20.323 16.4007 20.551 16.4227C20.6743 16.4354 20.7884 16.4933 20.8714 16.5853C20.9543 16.6773 21.0002 16.7969 21 16.9207H20.999Z" fill="black" fill-opacity="0.2" stroke="#484848" stroke-width="0.75"/>
              </svg>
            </div>
            <input
              id="<?php echo esc_attr($form_uid); ?>-phone"
              class="rounded-lg-x h-input text-black-secondary text-xs-font md:text-mob-md-font font-laca font-light pl-11 flex w-full tablet-sm:max-w-max-95"
              name="phone"
              type="tel"
              placeholder="<?php esc_attr_e('Telephone', 'matrix-starter'); ?>"
              autocomplete="tel"
            >
          </div>
        </div>
      </div>

      <div class="flex flex-col w-full">
        <label class="block text-black-full text-xs-font md:text-mob-md-font font-reg420 ml-2" for="<?php echo esc_attr($form_uid); ?>-message">
          <?php esc_html_e('Message*', 'matrix-starter'); ?>
        </label>
        <textarea
          id="<?php echo esc_attr($form_uid); ?>-message"
          class="rounded-sm-10 h-input text-black-secondary text-xs-font md:text-mob-md-font font-laca font-light pl-4 flex w-full min-h-[140px] mb-4 md:mb-6"
          name="message"
          rows="10"
          placeholder="<?php esc_attr_e('Message', 'matrix-starter'); ?>"
          required
        ></textarea>
      </div>

      <div class="flex flex-col w-full">
        <div class="flex lg:items-center leading-4 py-6 md:py-10">
          <input
            id="<?php echo esc_attr($form_uid); ?>-consent"
            class="bg-white mr-2 h-[20px] w-[20px] rounded-sm border-grey-input flex flex-col border-2 border-solid shrink-0"
            name="terms_conditions"
            type="checkbox"
            value="1"
            required
          >
          <label class="text-black text-sm-font font-light font-laca leading-reg1 cursor-pointer" for="<?php echo esc_attr($form_uid); ?>-consent">
            <?php
            if ($privacy_url !== '') {
                printf(
                    /* translators: %s: privacy policy URL */
                    wp_kses_post(__('I agree with the handling of my data in accordance with the company <a href="%s">Privacy Policy</a>.', 'matrix-starter')),
                    esc_url($privacy_url)
                );
            } else {
                esc_html_e('I agree with the handling of my data in accordance with the company Privacy Policy.', 'matrix-starter');
            }
            ?>
          </label>
        </div>
      </div>

      <?php if ($show_captcha && $captcha_html !== '') : ?>
        <div class="flex flex-col w-full mb-4 md:mb-6">
          <?php echo $captcha_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper ?>
        </div>
      <?php endif; ?>

      <div class="flex flex-col w-full lg:w-auto gap-4">
        <button
          type="submit"
          class="btn no-form-btn-style mb-4 md:mb-10 text-yellow-primary text-mob-lg-font lg:text-sm-md-font font-medium py-3 bg-black-full border-2 border-yellow-primary border-solid rounded-lg-x w-full mobile:w-[356px] hover:text-black-full hover:bg-yellow-primary disabled:opacity-60"
        >
          <?php esc_html_e('Submit', 'matrix-starter'); ?>
        </button>
      </div>
    </div>

  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var form = document.getElementById('<?php echo esc_js($form_uid); ?>');
  if (!form) return;
  var source = form.querySelector('input[name="source_url"]');
  if (source) source.value = window.location.href;
});
</script>
