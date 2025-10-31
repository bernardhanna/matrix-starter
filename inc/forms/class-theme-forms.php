<?php
/**
 * Theme_Forms — robust form handler (idempotent)
 * - Hidden config via _cfg_* + Theme Options fallback
 * - Skips tech fields; normalizes values (dedup + undouble)
 * - Forces From/From-Name + PHPMailer Sender (Return-Path)
 * - Multi-recipient To/Bcc; attachments; save-to-DB; autoresponder
 * - reCAPTCHA v3 / Cloudflare Turnstile
 * - **Idempotency**: token-only lock, DB uniqueness check, mail-guard
 * - JSON responses for AJAX; redirect fallback otherwise
 */
class Theme_Forms {

  private $last_mail_error = '';
  private $phpmailer_sender = null;

  public function __construct() {
    add_action('admin_post_nopriv_theme_form_submit', [ $this, 'handle' ]);
    add_action('admin_post_theme_form_submit',        [ $this, 'handle' ]);
  }

  /* ---------- Helpers ---------- */

  private function parse_emails($val): array {
    $raw = [];
    if (is_array($val)) {
      foreach ($val as $v) { $raw[] = $v; }
    } elseif (is_string($val)) {
      $raw = preg_split('/[,\s;]+/u', $val);
    }
    $out = [];
    foreach ($raw as $e) {
      $e = trim((string)$e);
      if (!$e) continue;
      $e = sanitize_email($e);
      if ($e && is_email($e) && !in_array($e, $out, true)) $out[] = $e;
    }
    return $out;
  }

  private function maybe_undouble_string(string $val): string {
    $val = trim($val);
    $len = strlen($val);
    if ($len > 0 && $len % 2 === 0) {
      $half = substr($val, 0, $len / 2);
      if ($val === $half . $half) return $half;
    }
    return $val;
  }

  private function normalize_field(string $key, $val) {
    if (is_array($val)) {
      $vals = array_map('sanitize_text_field', $val);
      $vals = array_filter($vals, static fn($v) => $v !== '' && $v !== null);
      $vals = array_map([$this, 'maybe_undouble_string'], $vals);
      $vals = array_values(array_unique($vals));
      if ($key === 'email') return $vals[0] ?? '';
      return count($vals) > 1 ? implode(', ', $vals) : ($vals[0] ?? '');
    }
    $val = sanitize_text_field($val);
    return $this->maybe_undouble_string($val);
  }

  private function normalize_fields_from_post(): array {
    $skip = [
      'action','is_ajax',
      'g-recaptcha-response','cf-turnstile-response',
      '_submission_uid',
    ];
    $out = [];
    foreach ($_POST as $k => $v) {
      if (str_starts_with($k, '_') || in_array($k, $skip, true)) continue;
      $out[$k] = $this->normalize_field($k, $v);
    }
    return $out;
  }

  private function is_ajax(): bool {
    return (isset($_POST['is_ajax']) && $_POST['is_ajax'] === '1')
        || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
  }

  private function captcha_ok(): bool {
    $provider = function_exists('get_field') ? (get_field('captcha_provider', 'option') ?: 'none') : 'none';
    if ($provider === 'none') return true;

    if ($provider === 'recaptcha_v3') {
      $token = sanitize_text_field($_POST['g-recaptcha-response'] ?? '');
      if (!$token) return false;
      $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
        'body' => [
          'secret'   => get_field('recaptcha_secret_key', 'option'),
          'response' => $token,
        ],
        'timeout' => 10,
      ]);
      $json = json_decode(wp_remote_retrieve_body($response), true);
      return !empty($json['success']);
    }

    if ($provider === 'turnstile') {
      $token = sanitize_text_field($_POST['cf-turnstile-response'] ?? '');
      if (!$token) return false;
      $response = wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
        'body' => [
          'secret'   => get_field('turnstile_secret_key', 'option'),
          'response' => $token,
        ],
        'timeout' => 10,
      ]);
      $json = json_decode(wp_remote_retrieve_body($response), true);
      return !empty($json['success']);
    }

    return true;
  }

  private function result($ok, $message = '', $extra = []) {
    if ($this->is_ajax()) {
      if ($ok) wp_send_json_success(['message' => $message ?: 'success'] + $extra);
      wp_send_json_error(['message' => $message ?: 'error'] + $extra);
    }
    $qs = $ok ? 'success' : 'error';
    wp_safe_redirect(add_query_arg('form_status', $qs, wp_get_referer()));
    exit;
  }

  public function phpmailer_set_sender($phpmailer) {
    if ($this->phpmailer_sender) $phpmailer->Sender = $this->phpmailer_sender;
  }

  /* ---------- Main ---------- */

  public function handle() {
    if (isset($_POST['action']) && $_POST['action'] === 'theme_form_submit') {
      remove_all_actions('phpmailer_init'); // keep SMTP plugins from overwriting From/Sender
    }

    // 1) Security
    if (empty($_POST['theme_form_nonce']) || !wp_verify_nonce($_POST['theme_form_nonce'], 'theme_form_submit')) {
      wp_die('Bad nonce.');
    }
    if (!$this->captcha_ok()) {
      $this->result(false, 'captcha_failed');
    }

    // 1.5) Idempotency lock (token-only; 10 min)
    $uid_raw = isset($_POST['_submission_uid']) ? sanitize_text_field($_POST['_submission_uid']) : '';
    if (!$uid_raw) $this->result(false, 'missing_token');
    $lock_key = 'theme_form_lock_' . md5($uid_raw);
    $first = false;
    if (function_exists('wp_cache_add')) {
      $first = wp_cache_add($lock_key, 1, '', 600);
    } else {
      if (!get_transient($lock_key)) {
        set_transient($lock_key, 1, 600);
        $first = true;
      }
    }
    if (!$first) {
      $this->result(true, 'sent'); // duplicate → pretend OK; no side effects
    }

    // 2) Identify + fields
    $form_id = absint($_POST['_theme_form_id'] ?? 0);
    $fields  = $this->normalize_fields_from_post();

    // 3) Files
    $attachments = [];
    foreach ($_FILES as $file) {
      if (!empty($file['tmp_name']) && is_uploaded_file($file['tmp_name'])) {
        $attachments[]         = $file['tmp_name'];
        $fields[$file['name']] = $file['name'];
      }
    }

    // 3.5) DB uniqueness check (if we will save)
    if (!empty($_POST['_theme_save_to_db'])) {
      $existing = get_posts([
        'post_type'      => 'form_entry',
        'post_status'    => 'private',
        'meta_key'       => '_submission_uid',
        'meta_value'     => $uid_raw,
        'fields'         => 'ids',
        'numberposts'    => 1,
        'no_found_rows'  => true,
        'orderby'        => 'ID',
        'order'          => 'DESC',
      ]);
      if (!empty($existing)) {
        $this->result(true, 'sent'); // already saved; skip mail too
      }
    }

    // 4) Optional DB save
    if (!empty($_POST['_theme_save_to_db'])) {
      $title = ( $_POST['_theme_form_name'] ?? 'Entry') . ' – ' . current_time('mysql');
      $entry = wp_insert_post([
        'post_type'   => 'form_entry',
        'post_status' => 'private',
        'post_title'  => sanitize_text_field($title),
      ]);
      if ($entry && !is_wp_error($entry)) {
        update_post_meta($entry, '_submission_uid', $uid_raw);
        foreach ($fields as $key => $val) {
          update_post_meta($entry, $key, $val);
        }
      }
    }

    // 5) Resolve config
    $form_name       = sanitize_text_field($_POST['_theme_form_name'] ?? '');
    $default_subject = $form_name ? "$form_name – new entry" : 'Website form entry';

    $cfg_to         = $_POST['_cfg_to']        ?? '';
    $cfg_bcc        = $_POST['_cfg_bcc']       ?? '';
    $cfg_subject    = sanitize_text_field($_POST['_cfg_subject'] ?? '');
    $cfg_from_name  = wp_strip_all_tags($_POST['_cfg_from_name']  ?? '');
    $cfg_from_email = sanitize_email($_POST['_cfg_from_email'] ?? '');

    $opt_from_name  = function_exists('get_field') ? (trim((string) get_field('email_from_name','option') ?: get_bloginfo('name'))) : get_bloginfo('name');
    $opt_from_email = function_exists('get_field') ? sanitize_email(get_field('email_from_address','option')) : '';

    $to_list  = $this->parse_emails($cfg_to);
    if (!$to_list) $to_list = $this->parse_emails(get_option('admin_email'));
    $bcc_list = $this->parse_emails($cfg_bcc);

    $subject    = $cfg_subject ?: $default_subject;
    $from_name  = $cfg_from_name  ?: $opt_from_name;
    $from_email = $cfg_from_email ?: ($opt_from_email ?: ('no-reply@' . (parse_url(home_url(), PHP_URL_HOST) ?: 'localhost')));

    // 6) Headers
    $headers   = [ 'Content-Type: text/html; charset=utf-8' ];
    $headers[] = 'From: ' . sprintf('%s <%s>', $from_name, $from_email);
    if (!empty($fields['email']) && is_email($fields['email'])) {
      $headers[] = 'Reply-To: ' . sanitize_email($fields['email']);
    }
    foreach ($bcc_list as $bcc) {
      $headers[] = 'Bcc: ' . $bcc;
    }

    // 7) Message
    ob_start();
    echo '<h2>New form entry</h2><table>';
    foreach ($fields as $label => $val) {
      printf(
        '<tr><th style="text-align:left;padding-right:10px;">%s</th><td>%s</td></tr>',
        esc_html( ucwords( str_replace(['-', '_'], ' ', $label) ) ),
        esc_html( is_array($val) ? implode(', ', $val) : $val )
      );
    }
    echo '</table>';
    $message = ob_get_clean();

    // 8) Mail send guard (per token; 10 min)
    $mail_guard_key = 'theme_form_mail_sent_' . md5($uid_raw);
    if (function_exists('wp_cache_add')) {
      if (!wp_cache_add($mail_guard_key, 1, '', 600)) {
        $this->result(true, 'sent'); // already mailed
      }
    } else {
      if (get_transient($mail_guard_key)) $this->result(true, 'sent');
      set_transient($mail_guard_key, 1, 600);
    }

    // 8.1) Send (capture errors + enforce Return-Path + force From/From-Name)
    $this->last_mail_error = '';
    $err_catcher = function($wp_error){ $this->last_mail_error = $wp_error->get_error_message(); };
    add_action('wp_mail_failed', $err_catcher);

    $this->phpmailer_sender = $from_email;
    add_action('phpmailer_init', [ $this, 'phpmailer_set_sender' ]);

    $from_filter      = function() use ($from_email) { return $from_email; };
    $from_name_filter = function() use ($from_name)  { return $from_name;  };
    add_filter('wp_mail_from', $from_filter);
    add_filter('wp_mail_from_name', $from_name_filter);

    $sent = wp_mail($to_list, wp_strip_all_tags($subject), $message, $headers, $attachments);

    remove_filter('wp_mail_from', $from_filter);
    remove_filter('wp_mail_from_name', $from_name_filter);
    remove_action('phpmailer_init', [ $this, 'phpmailer_set_sender' ]);
    remove_action('wp_mail_failed', $err_catcher);
    $this->phpmailer_sender = null;

    // 9) Autoresponder
    $auto_enabled = !empty($_POST['_cfg_auto_enabled']);
    if ($sent && $auto_enabled && !empty($fields['email']) && is_email($fields['email'])) {
      $auto_subject = sanitize_text_field($_POST['_cfg_auto_subject'] ?? 'Thank you for your message');
      $auto_message = wp_kses_post($_POST['_cfg_auto_message'] ?? 'We received your message.');
      $auto_headers = [ 'Content-Type: text/html; charset=utf-8', 'From: ' . sprintf('%s <%s>', $from_name, $from_email) ];

      $auto_from_filter      = function() use ($from_email) { return $from_email; };
      $auto_from_name_filter = function() use ($from_name)  { return $from_name;  };
      add_filter('wp_mail_from', $auto_from_filter);
      add_filter('wp_mail_from_name', $auto_from_name_filter);

      wp_mail(sanitize_email($fields['email']), wp_strip_all_tags($auto_subject), $auto_message, $auto_headers);

      remove_filter('wp_mail_from', $auto_from_filter);
      remove_filter('wp_mail_from_name', $auto_from_name_filter);
    }

    // 10) Respond
    if (!$sent) {
      $this->result(false, 'mail_failed', [
        'mail_error' => $this->last_mail_error,
        'to'         => implode(', ', $to_list),
      ]);
    }
    $this->result(true, 'sent');
  }
}

/* ==== BREVO AJAX subscribe ==== */
add_action('wp_ajax_nopriv_matrix_subscribe_brevo', 'matrix_subscribe_brevo');
add_action('wp_ajax_matrix_subscribe_brevo',        'matrix_subscribe_brevo');

function matrix_subscribe_brevo() {
  $nonce_val = '';
  if (isset($_REQUEST['nonce']))    { $nonce_val = sanitize_text_field($_REQUEST['nonce']); }
  if (isset($_REQUEST['_wpnonce'])) { $nonce_val = sanitize_text_field($_REQUEST['_wpnonce']); }
  if (!wp_verify_nonce($nonce_val, 'matrix_brevo_subscribe')) {
    wp_send_json_error(['message' => 'Bad nonce.'], 400);
  }

  $enabled = function_exists('get_field') ? (bool) get_field('newsletter_enabled', 'option') : true;
  if (!$enabled) wp_send_json_error(['message' => 'Newsletter disabled.'], 400);

  $api_key = function_exists('get_field') ? (string) get_field('brevo_api_key', 'option') : '';
  if (!$api_key && defined('MATRIX_BREVO_KEY')) $api_key = MATRIX_BREVO_KEY;
  if (!$api_key) wp_send_json_error(['message' => 'Missing Brevo API key.'], 500);

  $name_raw = sanitize_text_field($_POST['name'] ?? ($_POST['nn'] ?? ''));
  $email    = sanitize_email($_POST['email'] ?? ($_POST['ne'] ?? ''));
  $consent  = isset($_POST['consent']) ? (bool) $_POST['consent'] : ( isset($_POST['ny']) );

  if (!$email || !is_email($email)) wp_send_json_error(['message' => 'Please enter a valid email address.'], 400);
  if (!$consent) wp_send_json_error(['message' => 'Please accept the terms.'], 400);

  $opt_lists   = function_exists('get_field') ? (string) get_field('brevo_list_ids', 'option') : '';
  $post_list_s = sanitize_text_field($_POST['list_ids'] ?? '');
  $post_list_a = (isset($_POST['list_ids']) && is_array($_POST['list_ids'])) ? array_map('sanitize_text_field', (array) $_POST['list_ids']) : [];

  $lists_str       = $post_list_s ?: $opt_lists;
  $_ids_from_str   = array_filter(array_map('absint', preg_split('/[,\s;]+/', (string) $lists_str)));
  $_ids_from_array = array_filter(array_map('absint', $post_list_a));
  $list_ids        = array_values(array_unique(array_filter(array_merge($_ids_from_str, $_ids_from_array), 'intval')));

  $first = ''; $last = '';
  if ($name_raw) {
    $parts = preg_split('/\s+/', trim($name_raw));
    $first = array_shift($parts) ?: '';
    $last  = implode(' ', $parts);
  }

  $ip   = $_SERVER['REMOTE_ADDR'] ?? '';
  $when = current_time('mysql');

  $endpoint = 'https://api.brevo.com/v3/contacts';
  $body = [
    'email'         => $email,
    'updateEnabled' => true,
    'attributes'    => array_filter([
      'FIRSTNAME' => $first,
      'LASTNAME'  => $last,
      'CONSENT'   => 'yes',
      'CONSENT_IP'=> $ip,
      'CONSENT_AT'=> $when,
    ]),
  ];
  if (!empty($list_ids)) $body['listIds'] = $list_ids;

  $args = [
    'headers' => [
      'accept'       => 'application/json',
      'content-type' => 'application/json',
      'api-key'      => $api_key,
    ],
    'timeout' => 12,
    'body'    => wp_json_encode($body),
    'method'  => 'POST',
  ];

  $res  = wp_remote_post($endpoint, $args);
  $code = wp_remote_retrieve_response_code($res);
  $raw  = wp_remote_retrieve_body($res);

  if (in_array($code, [200, 201, 204], true)) {
    $msg = function_exists('get_field') ? (string) get_field('brevo_default_confirm_message', 'option') : 'Thanks — you’re subscribed!';
    wp_send_json_success(['message' => $msg]);
  }

  $json     = json_decode($raw, true);
  $err      = is_array($json) && !empty($json['message']) ? $json['message'] : 'Subscription failed.';
  $fallback = function_exists('get_field') ? (string) get_field('brevo_error_message', 'option') : 'Sorry, something went wrong. Please try again.';
  wp_send_json_error(['message' => ($err ?: $fallback)], $code ?: 400);
}

/* ==== Safe singleton init (avoid duplicate action registration) ==== */
add_action('init', function () {
  static $done = false;
  if ($done) return;
  $done = true;

  if (!isset($GLOBALS['matrix_theme_forms']) || !($GLOBALS['matrix_theme_forms'] instanceof Theme_Forms)) {
    $GLOBALS['matrix_theme_forms'] = new Theme_Forms();
  }
});
