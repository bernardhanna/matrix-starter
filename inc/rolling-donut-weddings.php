<?php
/**
 * Rolling Donut — Weddings & Corporate enquiry form (Theme_Forms, replaces GF #36).
 */

require_once get_template_directory() . '/inc/helpers/theme-forms-display.php';
require_once get_template_directory() . '/inc/helpers/gravity-forms-autoresponder.php';

/** Gravity Forms id for the legacy Wedding Enquiry form. */
const MATRIX_RD_WEDDINGS_GF_FORM_ID = 36;

/**
 * Admin notification recipient (GF #36 admin notification → footer email → site admin).
 */
function matrix_rd_weddings_form_recipient(): string {
    $admin = matrix_gf_get_admin_notification(MATRIX_RD_WEDDINGS_GF_FORM_ID);
    if ($admin !== null && !empty($admin['to']) && is_email($admin['to'])) {
        return $admin['to'];
    }

    $email = '';
    if (function_exists('get_field')) {
        $email = sanitize_email((string) get_field('footer_contact_email', 'option'));
    }

    if ($email === '' || !is_email($email)) {
        $email = sanitize_email((string) get_option('admin_email'));
    }

    return $email;
}

/**
 * Admin email subject (GF merge tags are not expanded here).
 */
function matrix_rd_weddings_form_admin_subject(): string {
    $admin = matrix_gf_get_admin_notification(MATRIX_RD_WEDDINGS_GF_FORM_ID);
    if ($admin !== null && !empty($admin['subject'])) {
        $subject = matrix_gf_replace_notification_tags((string) $admin['subject'], MATRIX_RD_WEDDINGS_GF_FORM_ID);
        $subject = preg_replace('/\{[^}]+\}/', '', $subject);
        $subject = trim(preg_replace('/\s+/', ' ', $subject));
        if ($subject !== '') {
            return $subject;
        }
    }

    return __('New Wedding & Events Enquiry', 'matrix-starter');
}

/**
 * Inject Theme_Forms markup where the Gravity Forms shortcode lived.
 */
function matrix_rd_weddings_inject_form(string $content): string {
    if (!is_page('weddings-events')) {
        return $content;
    }

    $has_gf = (bool) preg_match('/\[gravityform[^\]]*\]/i', $content)
        || str_contains($content, 'gform_wrapper');

    // Always strip leftover Gravity Forms markup/shortcodes on this page, even in
    // content blocks we do not inject into (the heading and the [gravityform]
    // shortcode live in separate flexible-content blocks).
    if ($has_gf) {
        $content = matrix_rd_strip_gravity_forms_from_content($content);
    }

    // `the_content` runs more than once on this page (each flexible-content block
    // filters its own copy), so guard against injecting the form twice.
    static $injected = false;
    if ($injected || str_contains($content, 'rd-weddings-form')) {
        return $content;
    }

    if (!str_contains($content, 'eventForm')) {
        return $content;
    }

    $injected = true;

    ob_start();
    get_template_part('template-parts/forms/weddings-events');
    $form_html = (string) ob_get_clean();

    if ($form_html === '') {
        return $content;
    }

    if (preg_match('/(<h[2-4][^>]*\bid=["\']eventForm["\'][^>]*>.*?<\/h[2-4]>)/is', $content, $matches)) {
        return str_replace($matches[1], $matches[1] . $form_html, $content);
    }

    return $content . $form_html;
}
add_filter('the_content', 'matrix_rd_weddings_inject_form', 12);

/**
 * Server-side validation for required checkbox groups.
 *
 * @param array<string, mixed> $fields
 */
function matrix_rd_weddings_validate_submission(string $message, int $form_id, array $fields): string {
    if ($form_id !== MATRIX_RD_WEDDINGS_GF_FORM_ID) {
        return $message;
    }

    if (empty($fields['event_type'])) {
        return 'event_type_required';
    }

    if (empty($fields['donuts_interested'])) {
        return 'donuts_required';
    }

    if (empty($fields['event_date'])) {
        return 'event_date_required';
    }

    return $message;
}
add_filter('matrix_theme_forms_before_send', 'matrix_rd_weddings_validate_submission', 10, 3);

function matrix_rd_weddings_disable_gravity_forms_enqueue($enqueue, $form, $is_ajax) {
    if (is_page('weddings-events')) {
        return false;
    }

    return $enqueue;
}
add_filter('gform_enqueue_scripts', 'matrix_rd_weddings_disable_gravity_forms_enqueue', 10, 3);

function matrix_rd_weddings_remove_gravity_shortcode(): void {
    if (!is_page('weddings-events')) {
        return;
    }

    remove_shortcode('gravityform');
    remove_shortcode('gravityforms');
}
add_action('wp', 'matrix_rd_weddings_remove_gravity_shortcode');

function matrix_rd_weddings_dequeue_gravity_forms(): void {
    if (!is_page('weddings-events')) {
        return;
    }

    $handles = [
        'gform_gravityforms',
        'gform_gravityforms_theme',
        'gform_gravityforms_theme_v2',
        'gform_basic',
        'gform_recaptcha',
        'gform_placeholder',
        'gform_json',
        'gform_masked_input',
        'gform_formsmain',
        'gform_tooltip',
        'gform_chosen',
        'gform_reset_css',
        'gform_datepicker',
        'gform_formsmain_css',
        'gform_ready_class_css',
        'gform_browsers_css',
        'gravity-forms',
        'gravityforms',
        'gravity_forms_theme_reset',
        'gravity_forms_theme_foundation',
        'gravity_forms_theme_framework',
        'gravity_forms_orbital_theme',
    ];

    foreach ($handles as $handle) {
        wp_dequeue_script($handle);
        wp_deregister_script($handle);
        wp_dequeue_style($handle);
        wp_deregister_style($handle);
    }
}
add_action('wp_enqueue_scripts', 'matrix_rd_weddings_dequeue_gravity_forms', 120);
