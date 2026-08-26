<?php
/**
 * Cloudflare Turnstile on My Account and checkout login forms.
 *
 * Widgets and validation run only on the live host (therollingdonut.ie).
 * Local and staging stay captcha-free so hostname allowlists cannot block staff.
 */

if (! defined('ABSPATH')) {
    exit;
}

function matrix_rd_account_captcha_error_message(): string {
    return __('Please complete the captcha and try again.', 'matrix-starter');
}

function matrix_rd_account_captcha_failed(): bool {
    if (! function_exists('matrix_theme_form_captcha_enabled') || ! matrix_theme_form_captcha_enabled()) {
        return false;
    }

    if (! function_exists('matrix_theme_form_captcha_provider')
        || matrix_theme_form_captcha_provider() !== 'turnstile'
    ) {
        return false;
    }

    return ! matrix_theme_form_turnstile_token_valid();
}

function matrix_rd_account_captcha_markup(): string {
    if (! function_exists('matrix_theme_form_captcha_markup')) {
        return '';
    }

    $html = matrix_theme_form_captcha_markup(array(
        'class' => 'cf-turnstile rd-account-captcha mt-6',
        'theme' => 'light',
    ));

    return $html === '' ? '' : '<div class="w-full rd-account-captcha-wrap">' . $html . '</div>';
}

add_filter('woocommerce_process_login_errors', static function ($errors) {
    if (! ($errors instanceof WP_Error)) {
        return $errors;
    }
    if (matrix_rd_account_captcha_failed()) {
        $errors->add('rd_turnstile', matrix_rd_account_captcha_error_message());
    }

    return $errors;
});

add_filter('woocommerce_registration_errors', static function ($errors) {
    if (! ($errors instanceof WP_Error)) {
        return $errors;
    }
    if (matrix_rd_account_captcha_failed()) {
        $errors->add('rd_turnstile', matrix_rd_account_captcha_error_message());
    }

    return $errors;
});

add_action('lostpassword_post', static function ($errors) {
    if (! ($errors instanceof WP_Error)) {
        return;
    }
    if (matrix_rd_account_captcha_failed()) {
        $errors->add('rd_turnstile', matrix_rd_account_captcha_error_message());
    }
});

add_action('wp_enqueue_scripts', static function (): void {
    $on_account  = function_exists('is_account_page') && is_account_page();
    $on_checkout = function_exists('is_checkout') && is_checkout();
    if ((! $on_account && ! $on_checkout) || is_user_logged_in()) {
        return;
    }
    if (! function_exists('matrix_theme_form_captcha_enabled') || ! matrix_theme_form_captcha_enabled()) {
        return;
    }
    if (! function_exists('matrix_theme_form_captcha_provider')
        || matrix_theme_form_captcha_provider() !== 'turnstile'
    ) {
        return;
    }
    if (! wp_script_is('theme-forms', 'enqueued')) {
        return;
    }

    wp_add_inline_script(
        'theme-forms',
        <<<'JS'
(function () {
  function isShown(el) {
    if (!el) return false;
    if (el.style && el.style.display === 'none') return false;
    return !!(el.offsetWidth || el.offsetHeight || el.getClientRects().length);
  }
  function renderAccountTurnstile() {
    var key = window.themeFormsTurnstileSiteKey;
    if (!window.turnstile || !key) return;
    document.querySelectorAll('form .rd-account-captcha').forEach(function (el) {
      if (el.getAttribute('data-rd-ts-rendered')) return;
      var form = el.closest('form');
      if (form && !isShown(form)) return;
      try {
        var id = window.turnstile.render(el, {
          sitekey: key,
          theme: el.getAttribute('data-theme') || 'light',
          size: el.getAttribute('data-size') || 'normal'
        });
        el.setAttribute('data-rd-ts-rendered', String(id));
      } catch (e) {}
    });
  }
  function start() {
    var roots = document.querySelectorAll('[data-testid="rd-auth-card"], .woocommerce-ResetPassword, #checkout-login-container, form.woocommerce-form-login');
    if (!roots.length) return;
    renderAccountTurnstile();
    roots.forEach(function (root) {
      new MutationObserver(renderAccountTurnstile).observe(root, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ['style', 'class']
      });
    });
  }
  document.addEventListener('click', function (event) {
    if (event.target && event.target.closest && event.target.closest('.showlogin')) {
      window.setTimeout(renderAccountTurnstile, 50);
    }
  });
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
  } else {
    start();
  }
  var tries = 0;
  var timer = setInterval(function () {
    tries += 1;
    if (window.turnstile || tries > 40) {
      clearInterval(timer);
      renderAccountTurnstile();
    }
  }, 250);
})();
JS
        ,
        'after'
    );
}, 30);
