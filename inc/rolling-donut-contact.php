<?php
/**
 * Rolling Donut — Contact Us page (Theme_Forms, no Gravity Forms).
 */

require_once get_template_directory() . '/inc/helpers/theme-forms-display.php';
require_once get_template_directory() . '/inc/helpers/gravity-forms-autoresponder.php';

/** Gravity Forms id for the Contact Us form (legacy). */
const MATRIX_RD_CONTACT_GF_FORM_ID = 33;

/**
 * Default recipient for contact form submissions (GF admin notification → footer email → admin).
 */
function matrix_rd_contact_form_recipient(): string {
    $admin = matrix_gf_get_admin_notification(MATRIX_RD_CONTACT_GF_FORM_ID);
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
 * Admin email subject line (matches GF admin notification when available).
 */
function matrix_rd_contact_form_admin_subject(): string {
    $admin = matrix_gf_get_admin_notification(MATRIX_RD_CONTACT_GF_FORM_ID);
    if ($admin !== null && !empty($admin['subject'])) {
        return $admin['subject'];
    }

    return __('New submission from Contact Us', 'matrix-starter');
}

/**
 * Optional site logo for HTML autoresponder (matches branded GF-style emails).
 */
function matrix_rd_contact_autoresponder_logo_url(): string {
    $custom_logo_id = (int) get_theme_mod('custom_logo');
    if ($custom_logo_id > 0) {
        $url = wp_get_attachment_image_url($custom_logo_id, 'medium');
        if (is_string($url) && $url !== '') {
            return $url;
        }
    }

    return '';
}

/**
 * Remove Gravity Forms shortcodes / markup from page content.
 */
function matrix_rd_strip_gravity_forms_from_content(string $content): string {
    if ($content === '') {
        return $content;
    }

    $content = (string) preg_replace('/\[gravityform[^\]]*\]/i', '', $content);
    $content = (string) preg_replace('/<div[^>]*\bgform_wrapper\b[^>]*>[\s\S]*?<\/form>\s*<\/div>/i', '', $content);
    $content = (string) preg_replace('/<h2[^>]*class="[^"]*gform_title[^"]*"[^>]*>[\s\S]*?<\/h2>/i', '', $content);
    $content = (string) preg_replace('/<p>\s*(?:&nbsp;|\s)*<\/p>/i', '', $content);

    return trim($content);
}

/**
 * Strip GF from contact page editor output (early pass for shortcode detection).
 */
function matrix_rd_filter_contact_page_content(string $content): string {
    if (!is_page('contact-us')) {
        return $content;
    }

    return matrix_rd_strip_gravity_forms_from_content($content);
}
add_filter('the_content', 'matrix_rd_filter_contact_page_content', 5);

/**
 * Do not enqueue Gravity Forms on the contact page.
 */
function matrix_rd_contact_disable_gravity_forms_enqueue($enqueue, $form, $is_ajax) {
    if (is_page('contact-us')) {
        return false;
    }

    return $enqueue;
}
add_filter('gform_enqueue_scripts', 'matrix_rd_contact_disable_gravity_forms_enqueue', 10, 3);

/**
 * Remove gravityform shortcode on contact page (belt-and-braces).
 */
function matrix_rd_contact_remove_gravity_shortcode(): void {
    if (!is_page('contact-us')) {
        return;
    }

    remove_shortcode('gravityform');
    remove_shortcode('gravityforms');
}
add_action('wp', 'matrix_rd_contact_remove_gravity_shortcode');

/**
 * Reduce Gravity Forms asset load on the contact page.
 */
function matrix_rd_contact_dequeue_gravity_forms(): void {
    if (!is_page('contact-us')) {
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
add_action('wp_enqueue_scripts', 'matrix_rd_contact_dequeue_gravity_forms', 120);
