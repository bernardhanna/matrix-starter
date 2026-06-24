<?php
/**
 * My Account sign-in / register behaviour (ported from legacy mu-plugin).
 */

if (!defined('ABSPATH')) {
    exit;
}

add_filter('woocommerce_registration_error_email_exists', function (string $message): string {
    return preg_replace('/href="#" class="showlogin"/', 'href="#checkout-login-container"', $message) ?? $message;
});

add_action('woocommerce_register_post', 'matrix_rd_validate_registration_fields', 10, 3);
function matrix_rd_validate_registration_fields($username, $email, $validation_errors): void
{
    if (!($validation_errors instanceof WP_Error)) {
        return;
    }

    if (email_exists($email)) {
        $validation_errors->add(
            'email_exists',
            __('An account is already registered with your email address. Please log in.', 'woocommerce')
        );
    }

    if (isset($_POST['password'], $_POST['confirm_password']) && $_POST['password'] !== $_POST['confirm_password']) {
        $validation_errors->add('password_mismatch', __('Passwords do not match.', 'woocommerce'));
    }
}

add_action('woocommerce_created_customer', 'matrix_rd_save_registration_fields');
function matrix_rd_save_registration_fields(int $customer_id): void
{
    if (isset($_POST['first_name'])) {
        $first_name = sanitize_text_field(wp_unslash((string) $_POST['first_name']));
        update_user_meta($customer_id, 'first_name', $first_name);
        update_user_meta($customer_id, 'billing_first_name', $first_name);
    }

    if (isset($_POST['last_name'])) {
        $last_name = sanitize_text_field(wp_unslash((string) $_POST['last_name']));
        update_user_meta($customer_id, 'last_name', $last_name);
        update_user_meta($customer_id, 'billing_last_name', $last_name);
    }

    if (!empty($_POST['password'])) {
        wp_set_password((string) wp_unslash($_POST['password']), $customer_id);
    }
}
