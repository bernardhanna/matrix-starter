<?php
/**
 * Weddings & Corporate — Theme_Forms (replaces Gravity Forms #36 on /weddings-events/).
 */
if (!defined('ABSPATH')) {
    exit;
}

$form_uid       = 'weddings-events-' . wp_generate_uuid4();
$recipient      = matrix_rd_weddings_form_recipient();
$admin_subject  = matrix_rd_weddings_form_admin_subject();
$privacy_url    = function_exists('get_privacy_policy_url') ? get_privacy_policy_url() : '';
$show_captcha   = matrix_theme_form_captcha_enabled();
$captcha_html   = matrix_theme_form_captcha_markup([
    'class' => 'cf-turnstile rd-weddings-captcha',
    'theme' => 'light',
]);

$event_types = [
    'Wedding'          => __('Wedding', 'matrix-starter'),
    'Corporate event'  => __('Corporate event', 'matrix-starter'),
    'Party'            => __('Party', 'matrix-starter'),
    'Other'            => __('Other', 'matrix-starter'),
];

$donut_types = [
    'Midi Donuts ( filled )'  => __('Midi Donuts ( filled )', 'matrix-starter'),
    'Large Donuts ( filled )' => __('Large Donuts ( filled )', 'matrix-starter'),
    'Ring Donuts'             => __('Ring Donuts', 'matrix-starter'),
    'Heart Donuts'            => __('Heart Donuts', 'matrix-starter'),
];

$personalisation_options = [
    'Logo'                                                              => __('Logo', 'matrix-starter'),
    'Custom Coloured Glaze ( Only available for more than 12 donuts )'  => __('Custom Coloured Glaze ( Only available for more than 12 donuts )', 'matrix-starter'),
    'Individually Boxed (Only available for more than 12 donuts)'       => __('Individually Boxed (Only available for more than 12 donuts)', 'matrix-starter'),
];

$stand_options = [
    'Donut wall Large 6ft'           => __('Donut wall Large 6ft', 'matrix-starter'),
    'Donut wall Small (table top)'   => __('Donut wall Small (table top)', 'matrix-starter'),
    'Tiered stand'                   => __('Tiered stand', 'matrix-starter'),
    'Folded stand'                   => __('Folded stand', 'matrix-starter'),
    '4 Tiered Stand x2'              => __('4 Tiered Stand x2', 'matrix-starter'),
    '4x Tiered Lazy Suzie Stand'     => __('4x Tiered Lazy Suzie Stand', 'matrix-starter'),
];
?>
<div class="rd-weddings-form-wrap w-full max-w-[900px] mx-auto mt-6">
  <form
    id="<?php echo esc_attr($form_uid); ?>"
    class="rd-weddings-form rd-contact-form flex flex-col w-full md:boxshadow-two md:rounded-form md:border md:bg-white md:border-black-full md:border-solid mobile:px-4 md:px-9 pt-8 pb-6 md:pb-10"
    action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
    method="post"
    enctype="multipart/form-data"
    novalidate
    data-theme-form="weddings-events"
    data-required-checkbox-groups="event_type,donuts_interested"
    data-success-message="<?php echo esc_attr__('Thank you — we have received your enquiry and will be in touch soon.', 'matrix-starter'); ?>"
  >
    <input type="hidden" name="action" value="theme_form_submit">
    <input type="hidden" name="theme_form_nonce" value="<?php echo esc_attr(wp_create_nonce('theme_form_submit')); ?>">
    <input type="hidden" name="_theme_form_id" value="<?php echo esc_attr((string) MATRIX_RD_WEDDINGS_GF_FORM_ID); ?>">
    <input type="hidden" name="_submission_uid" value="<?php echo esc_attr(wp_generate_uuid4()); ?>">
    <input type="hidden" name="_theme_form_name" value="<?php echo esc_attr__('Wedding Enquiry Form', 'matrix-starter'); ?>">
    <input type="hidden" name="_theme_save_to_db" value="1">
    <input type="hidden" name="_cfg_to" value="<?php echo esc_attr($recipient); ?>">
    <input type="hidden" name="_cfg_subject" value="<?php echo esc_attr($admin_subject); ?>">
    <input type="hidden" name="source_url" value="">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
      <div class="flex flex-col w-full">
        <label class="block text-black-full text-xs-font md:text-mob-md-font font-reg420 ml-2" for="<?php echo esc_attr($form_uid); ?>-first-name">
          <?php esc_html_e('First Name*', 'matrix-starter'); ?>
        </label>
        <input
          id="<?php echo esc_attr($form_uid); ?>-first-name"
          class="text-field rounded-lg-x h-input text-black-secondary text-xs-font md:text-mob-md-font font-laca font-lig px-4 flex w-full mb-4 md:mb-8"
          name="first_name"
          type="text"
          placeholder="<?php esc_attr_e('First Name', 'matrix-starter'); ?>"
          autocomplete="given-name"
          required
        >
      </div>
      <div class="flex flex-col w-full">
        <label class="block text-black-full text-xs-font md:text-mob-md-font font-reg420 ml-2" for="<?php echo esc_attr($form_uid); ?>-last-name">
          <?php esc_html_e('Surname*', 'matrix-starter'); ?>
        </label>
        <input
          id="<?php echo esc_attr($form_uid); ?>-last-name"
          class="text-field rounded-lg-x h-input text-black-secondary text-xs-font md:text-mob-md-font font-laca font-lig px-4 flex w-full mb-4 md:mb-8"
          name="last_name"
          type="text"
          placeholder="<?php esc_attr_e('Surname', 'matrix-starter'); ?>"
          autocomplete="family-name"
          required
        >
      </div>
      <div class="flex flex-col w-full">
        <label class="block text-black-full text-xs-font md:text-mob-md-font font-reg420 ml-2" for="<?php echo esc_attr($form_uid); ?>-email">
          <?php esc_html_e('Email*', 'matrix-starter'); ?>
        </label>
        <input
          id="<?php echo esc_attr($form_uid); ?>-email"
          class="rounded-lg-x h-input text-black-secondary text-xs-font md:text-mob-md-font font-laca font-light px-4 flex w-full mb-4 md:mb-8"
          name="email"
          type="email"
          placeholder="<?php esc_attr_e('Email Address', 'matrix-starter'); ?>"
          autocomplete="email"
          required
        >
      </div>
      <div class="flex flex-col w-full">
        <label class="block text-black-full text-xs-font md:text-mob-md-font font-reg420 ml-2" for="<?php echo esc_attr($form_uid); ?>-phone">
          <?php esc_html_e('Telephone', 'matrix-starter'); ?>
        </label>
        <input
          id="<?php echo esc_attr($form_uid); ?>-phone"
          class="rounded-lg-x h-input text-black-secondary text-xs-font md:text-mob-md-font font-laca font-light px-4 flex w-full mb-4 md:mb-8"
          name="phone"
          type="tel"
          placeholder="<?php esc_attr_e('Telephone', 'matrix-starter'); ?>"
          autocomplete="tel"
        >
      </div>
      <div class="flex flex-col w-full">
        <label class="block text-black-full text-xs-font md:text-mob-md-font font-reg420 ml-2" for="<?php echo esc_attr($form_uid); ?>-donut-quantity">
          <?php esc_html_e('Quantity of Donuts approx :', 'matrix-starter'); ?>
        </label>
        <input
          id="<?php echo esc_attr($form_uid); ?>-donut-quantity"
          class="rounded-lg-x h-input text-black-secondary text-xs-font md:text-mob-md-font font-laca font-light px-4 flex w-full mb-4 md:mb-8"
          name="donut_quantity"
          type="number"
          min="0"
          step="1"
          placeholder="<?php esc_attr_e('Enter Amount', 'matrix-starter'); ?>"
        >
      </div>
      <div class="flex flex-col w-full">
        <label class="block text-black-full text-xs-font md:text-mob-md-font font-reg420 ml-2" for="<?php echo esc_attr($form_uid); ?>-event-date">
          <?php esc_html_e('Date of event :*', 'matrix-starter'); ?>
        </label>
        <input
          id="<?php echo esc_attr($form_uid); ?>-event-date"
          class="rounded-lg-x h-input text-black-secondary text-xs-font md:text-mob-md-font font-laca font-light px-4 flex w-full mb-4 md:mb-8"
          name="event_date"
          type="date"
          required
        >
      </div>
    </div>

    <fieldset class="rd-weddings-fieldset mb-6 md:mb-8 border-0 p-0 m-0" data-checkbox-group="event_type">
      <legend class="block text-black-full text-xs-font md:text-mob-md-font font-reg420 mb-3">
        <?php esc_html_e('What type of event are you hosting?*', 'matrix-starter'); ?>
      </legend>
      <div class="flex flex-col gap-3">
        <?php foreach ($event_types as $value => $label) : ?>
          <label class="flex items-start gap-3 cursor-pointer text-black-full text-sm-font font-laca font-light">
            <input class="mt-1 shrink-0" type="checkbox" name="event_type[]" value="<?php echo esc_attr($value); ?>">
            <span><?php echo esc_html($label); ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </fieldset>

    <fieldset class="rd-weddings-fieldset mb-6 md:mb-8 border-0 p-0 m-0" data-checkbox-group="donuts_interested">
      <legend class="block text-black-full text-xs-font md:text-mob-md-font font-reg420 mb-3">
        <?php esc_html_e('Which donuts are you interested in?*', 'matrix-starter'); ?>
      </legend>
      <div class="flex flex-col gap-3">
        <?php foreach ($donut_types as $value => $label) : ?>
          <label class="flex items-start gap-3 cursor-pointer text-black-full text-sm-font font-laca font-light">
            <input class="mt-1 shrink-0" type="checkbox" name="donuts_interested[]" value="<?php echo esc_attr($value); ?>">
            <span><?php echo esc_html($label); ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </fieldset>

    <fieldset class="rd-weddings-fieldset mb-6 md:mb-8 border-0 p-0 m-0">
      <legend class="block text-black-full text-xs-font md:text-mob-md-font font-reg420 mb-3">
        <?php esc_html_e('Do you want personalisation?', 'matrix-starter'); ?>
      </legend>
      <div class="flex flex-col gap-3">
        <?php foreach ($personalisation_options as $value => $label) : ?>
          <label class="flex items-start gap-3 cursor-pointer text-black-full text-sm-font font-laca font-light">
            <input class="mt-1 shrink-0" type="checkbox" name="personalisation[]" value="<?php echo esc_attr($value); ?>">
            <span><?php echo esc_html($label); ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </fieldset>

    <fieldset class="rd-weddings-fieldset mb-6 md:mb-8 border-0 p-0 m-0">
      <legend class="block text-black-full text-xs-font md:text-mob-md-font font-reg420 mb-3">
        <?php esc_html_e('Do you require a donut stand?', 'matrix-starter'); ?>
      </legend>
      <div class="flex flex-col gap-3">
        <?php foreach ($stand_options as $value => $label) : ?>
          <label class="flex items-start gap-3 cursor-pointer text-black-full text-sm-font font-laca font-light">
            <input class="mt-1 shrink-0" type="checkbox" name="donut_stand[]" value="<?php echo esc_attr($value); ?>">
            <span><?php echo esc_html($label); ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </fieldset>

    <div class="flex flex-col w-full mb-4">
      <div class="flex lg:items-center leading-4 py-4">
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
      <div class="flex flex-col w-full mb-6">
        <?php echo $captcha_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
      </div>
    <?php endif; ?>

    <button
      type="submit"
      class="btn no-form-btn-style text-yellow-primary text-mob-lg-font lg:text-sm-md-font font-medium py-3 bg-black-full border-2 border-yellow-primary border-solid rounded-lg-x w-full mobile:w-[356px] hover:text-black-full hover:bg-yellow-primary disabled:opacity-60"
    >
      <?php esc_html_e('Submit', 'matrix-starter'); ?>
    </button>
  </form>
</div>

<style>
/* Match the legacy Gravity Forms styling (brand grey border, pill radius, soft
 * shadow). Scoped + element-qualified so it wins over @tailwindcss/forms' base
 * [type='…'] reset, which otherwise forces a 1px grey square field. */
.rd-weddings-form input[type="text"],
.rd-weddings-form input[type="email"],
.rd-weddings-form input[type="tel"],
.rd-weddings-form input[type="number"],
.rd-weddings-form input[type="date"],
.rd-weddings-form select,
.rd-weddings-form textarea {
  width: 100%;
  height: 3.5rem;
  padding: 0 1.25rem;
  background-color: #fff;
  border: 2px solid #d8d7ce;
  border-radius: 72px;
  box-shadow: 0 1px 4px rgba(18, 25, 97, 0.08);
  color: #2d2a2a;
  font-size: 1rem;
  line-height: 1.2;
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
}

.rd-weddings-form textarea {
  height: auto;
  min-height: 8rem;
  padding: 0.85rem 1.25rem;
  border-radius: 1.5rem;
}

.rd-weddings-form input[type="text"]:hover,
.rd-weddings-form input[type="email"]:hover,
.rd-weddings-form input[type="tel"]:hover,
.rd-weddings-form input[type="number"]:hover,
.rd-weddings-form input[type="date"]:hover,
.rd-weddings-form select:hover,
.rd-weddings-form textarea:hover {
  border-color: #c5c3b6;
}

.rd-weddings-form input[type="text"]:focus,
.rd-weddings-form input[type="email"]:focus,
.rd-weddings-form input[type="tel"]:focus,
.rd-weddings-form input[type="number"]:focus,
.rd-weddings-form input[type="date"]:focus,
.rd-weddings-form select:focus,
.rd-weddings-form textarea:focus {
  outline: none;
  border-color: #111;
  box-shadow: 0 1px 4px rgba(18, 25, 97, 0.12);
}

/* Date field: show a calendar affordance and make the whole field open the
 * native picker (Chrome only opens it from the tiny indicator otherwise). */
.rd-weddings-form input[type="date"] {
  position: relative;
  cursor: pointer;
  border: 2px solid #d8d7ce;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%232d2a2a' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='18' rx='2'/%3E%3Cpath d='M16 2v4M8 2v4M3 10h18'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 1rem center;
  background-size: 1.25rem 1.25rem;
  padding-right: 3rem;
}

.rd-weddings-form input[type="date"]::-webkit-calendar-picker-indicator {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  margin: 0;
  padding: 0;
  cursor: pointer;
  opacity: 0;
}

.rd-weddings-form input[type="date"]::-webkit-inner-spin-button,
.rd-weddings-form input[type="date"]::-webkit-clear-button {
  display: none;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var form = document.getElementById('<?php echo esc_js($form_uid); ?>');
  if (!form) return;

  var source = form.querySelector('input[name="source_url"]');
  if (source) {
    source.value = window.location.href;
  }

  // Native date inputs only open from their (tiny) indicator in Chrome; make a
  // click anywhere on the field open the picker so it behaves like the legacy
  // datepicker. showPicker() needs a user gesture, which the click provides.
  var dateField = form.querySelector('input[type="date"]');
  if (dateField) {
    dateField.addEventListener('click', function () {
      if (typeof this.showPicker === 'function') {
        try { this.showPicker(); } catch (e) {}
      }
    });
  }

  var groupMessages = {
    event_type: <?php echo wp_json_encode(__('Please select what type of event you are hosting.', 'matrix-starter')); ?>,
    donuts_interested: <?php echo wp_json_encode(__('Please select which donuts you are interested in.', 'matrix-starter')); ?>
  };

  form.addEventListener('submit', function (ev) {
    var groups = (form.getAttribute('data-required-checkbox-groups') || '').split(',').filter(Boolean);
    for (var i = 0; i < groups.length; i++) {
      var name = groups[i].trim();
      if (!name) continue;
      var checked = form.querySelector('input[name="' + name + '[]"]:checked');
      if (!checked) {
        ev.preventDefault();
        ev.stopImmediatePropagation();
        alert(groupMessages[name] || 'Please complete all required fields.');
        var fieldset = form.querySelector('[data-checkbox-group="' + name + '"]');
        if (fieldset && fieldset.scrollIntoView) {
          fieldset.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return;
      }
    }
  }, true);
});
</script>
