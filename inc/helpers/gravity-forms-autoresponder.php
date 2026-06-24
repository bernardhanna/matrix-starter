<?php
/**
 * Import Gravity Forms notification settings for Theme_Forms autoresponders.
 *
 * Preserves the same user-facing email copy GF used (subject/message) and caches
 * it in wp_options so it survives if Gravity Forms is later deactivated.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @return array<string, array<string, mixed>>
 */
function matrix_gf_get_notifications(int $form_id): array {
    static $runtime = [];

    if (isset($runtime[$form_id])) {
        return $runtime[$form_id];
    }

    global $wpdb;
    $table = $wpdb->prefix . 'gf_form_meta';
    $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
    if ($exists !== $table) {
        $runtime[$form_id] = [];
        return [];
    }

    $json = $wpdb->get_var($wpdb->prepare("SELECT notifications FROM {$table} WHERE form_id = %d", $form_id));
    if (!is_string($json) || $json === '') {
        $runtime[$form_id] = [];
        return [];
    }

    $decoded = json_decode($json, true);
    $runtime[$form_id] = is_array($decoded) ? $decoded : [];

    return $runtime[$form_id];
}

/**
 * @return array<string, mixed>|null
 */
function matrix_gf_get_form_title(int $form_id): ?string {
    global $wpdb;
    $table = $wpdb->prefix . 'gf_form';
    $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
    if ($exists !== $table) {
        return null;
    }

    $title = $wpdb->get_var($wpdb->prepare("SELECT title FROM {$table} WHERE id = %d", $form_id));

    return is_string($title) && $title !== '' ? $title : null;
}

/**
 * Replace common GF merge tags used in notification subjects.
 */
function matrix_gf_replace_notification_tags(string $text, int $form_id): string {
    $title = matrix_gf_get_form_title($form_id) ?: get_bloginfo('name');

    return str_replace(
        ['{form_title}', '{FormTitle}', '{admin_email}'],
        [$title, $title, sanitize_email((string) get_option('admin_email'))],
        $text
    );
}

/**
 * User-facing autoresponder (GF notification with toType=field or name contains "user").
 *
 * @return array{enabled: bool, subject: string, message: string, name_field: string}|null
 */
/**
 * Theme Options override for Contact Us (GF form #33) after GF is removed.
 *
 * @return array{enabled: bool, subject: string, message: string, name_field: string}|null
 */
function matrix_theme_forms_contact_autoresponder_override(): ?array {
    if (!function_exists('get_field') || !get_field('contact_autoresponder_override', 'option')) {
        return null;
    }

    $subject = trim((string) get_field('contact_autoresponder_subject', 'option'));
    $message = trim((string) get_field('contact_autoresponder_message', 'option'));

    if ($subject === '' && $message === '') {
        return null;
    }

    return [
        'enabled'    => true,
        'subject'    => $subject !== '' ? $subject : 'We have received your inquiry',
        'message'    => $message !== '' ? $message : 'Thank you for getting in touch.',
        'name_field' => 'first_name',
    ];
}

function matrix_gf_get_user_autoresponder(int $form_id): ?array {
    if ($form_id === 33) {
        $override = matrix_theme_forms_contact_autoresponder_override();
        if ($override !== null) {
            return $override;
        }
    }

    $cache_key = 'matrix_gf_user_autoresponder_' . $form_id;
    $cached    = get_option($cache_key);
    if (is_array($cached) && !empty($cached['enabled'])) {
        return $cached;
    }

    $chosen = null;
    foreach (matrix_gf_get_notifications($form_id) as $notification) {
        if (!is_array($notification)) {
            continue;
        }

        $name   = strtolower((string) ($notification['name'] ?? ''));
        $toType = strtolower((string) ($notification['toType'] ?? ''));

        if ($toType === 'field' || str_contains($name, 'user')) {
            $chosen = $notification;
            break;
        }
    }

    if ($chosen === null) {
        return null;
    }

    $config = [
        'enabled'    => true,
        'subject'    => matrix_gf_replace_notification_tags((string) ($chosen['subject'] ?? ''), $form_id),
        'message'    => matrix_gf_replace_notification_tags((string) ($chosen['message'] ?? ''), $form_id),
        'name_field' => 'first_name',
    ];

    update_option($cache_key, $config, false);

    return $config;
}

/**
 * Admin notification (GF notification with name containing "admin" or explicit toEmail).
 *
 * @return array{to: string, subject: string, reply_to_field: string}|null
 */
function matrix_gf_get_admin_notification(int $form_id): ?array {
    $cache_key = 'matrix_gf_admin_notification_' . $form_id;
    $cached    = get_option($cache_key);
    if (is_array($cached) && !empty($cached['to'])) {
        return $cached;
    }

    $chosen = null;
    foreach (matrix_gf_get_notifications($form_id) as $notification) {
        if (!is_array($notification)) {
            continue;
        }

        $name = strtolower((string) ($notification['name'] ?? ''));
        if (str_contains($name, 'admin')) {
            $chosen = $notification;
            break;
        }
    }

    if ($chosen === null) {
        return null;
    }

    $to = (string) ($chosen['toEmail'] ?? $chosen['to'] ?? '');
    $to = matrix_gf_replace_notification_tags($to, $form_id);
    $to = sanitize_email($to);
    if ($to === '' || !is_email($to)) {
        return null;
    }

    $reply_to_field = 'email';
    $reply_to_raw   = (string) ($chosen['replyTo'] ?? '');
    if (preg_match('/\{Email:(\d+)\}/i', $reply_to_raw, $m)) {
        // GF field id 2 = email on contact form; Theme_Forms uses name "email".
        $reply_to_field = 'email';
    }

    $config = [
        'to'              => $to,
        'subject'         => matrix_gf_replace_notification_tags((string) ($chosen['subject'] ?? ''), $form_id),
        'reply_to_field'  => $reply_to_field,
    ];

    update_option($cache_key, $config, false);

    return $config;
}

/**
 * Echo hidden Theme_Forms autoresponder fields from a GF form id.
 *
 * @param array{
 *   include_logo?: bool,
 *   logo_url?: string,
 *   footer?: string,
 *   reply_to?: string,
 *   name_field?: string
 * } $args
 */
function matrix_theme_form_autoresponder_hidden_fields(int $gf_form_id, array $args = []): void {
    $auto = matrix_gf_get_user_autoresponder($gf_form_id);
    if ($auto === null || empty($auto['enabled'])) {
        return;
    }

    $name_field = (string) ($args['name_field'] ?? $auto['name_field'] ?? 'first_name');
    $logo_url   = (string) ($args['logo_url'] ?? '');
    $include_logo = !empty($args['include_logo']) && $logo_url !== '';
    $footer     = (string) ($args['footer'] ?? '');
    $reply_to   = sanitize_email((string) ($args['reply_to'] ?? (string) get_option('admin_email')));

    ?>
    <input type="hidden" name="_cfg_auto_enabled" value="1">
    <input type="hidden" name="_cfg_auto_subject" value="<?php echo esc_attr($auto['subject']); ?>">
    <input type="hidden" name="_cfg_auto_message" value="<?php echo esc_attr($auto['message']); ?>">
    <input type="hidden" name="_cfg_auto_name_field" value="<?php echo esc_attr($name_field); ?>">
    <?php if ($include_logo) : ?>
      <input type="hidden" name="_cfg_auto_include_logo" value="1">
      <input type="hidden" name="_cfg_auto_logo_url" value="<?php echo esc_attr($logo_url); ?>">
    <?php endif; ?>
    <?php if ($footer !== '') : ?>
      <input type="hidden" name="_cfg_auto_footer" value="<?php echo esc_attr($footer); ?>">
    <?php endif; ?>
    <?php if ($reply_to !== '' && is_email($reply_to)) : ?>
      <input type="hidden" name="_cfg_auto_reply_to" value="<?php echo esc_attr($reply_to); ?>">
    <?php endif; ?>
    <?php
}
