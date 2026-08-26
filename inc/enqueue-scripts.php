<?php
/**
 * inc/enqueue-scripts.php
 */

function matrix_is_wc_flow_page(): bool {
  if (! function_exists('is_woocommerce')) return false;
  return is_cart()
      || is_checkout()
      || is_account_page()
      || is_wc_endpoint_url('order-received')
      || is_wc_endpoint_url('order-pay')
      || is_wc_endpoint_url('add-payment-method')
      || is_wc_endpoint_url('view-order');
}

/**
 * Whether Headroom.js is enabled (Theme Options) and allowed on this request.
 */
function matrix_headroom_enabled(): bool {
  if (matrix_is_wc_flow_page()) {
    return false;
  }
  // Rolling Donut uses Alpine headroom (matrix-rd-headroom.js), not Headroom.js.
  if (function_exists('matrix_rd_nav_should_show') && matrix_rd_nav_should_show()) {
    return false;
  }
  if (! function_exists('get_field')) {
    return false;
  }
  $enabled_scripts = get_field('enabled_scripts', 'option');
  return is_array($enabled_scripts) && in_array('headroom', $enabled_scripts, true);
}

/**
 * Base Tailwind classes for #site-nav when Headroom is active (also safelisted).
 * Position fixed is toggled in JS only after scroll (headroom--not-top).
 */
function matrix_headroom_nav_classes(): string {
  if (! matrix_headroom_enabled()) {
    return '';
  }
  return 'relative w-full z-50 transition-transform duration-300 ease-in-out translate-y-0';
}

/**
 * ACF option values may be arrays (label/value), nested arrays, or objects.
 * Normalize to a plain UTF-8 string for CAPTCHA keys and inline JSON.
 */
function matrix_acf_scalar_string( $raw ): string {
  if ( is_string( $raw ) ) {
    return trim( $raw );
  }
  if ( is_int( $raw ) || is_float( $raw ) ) {
    return trim( (string) $raw );
  }
  if ( is_bool( $raw ) ) {
    return $raw ? '1' : '';
  }
  if ( is_array( $raw ) ) {
    if ( array_key_exists( 'value', $raw ) ) {
      return matrix_acf_scalar_string( $raw['value'] );
    }
    if ( array_key_exists( 'key', $raw ) ) {
      return matrix_acf_scalar_string( $raw['key'] );
    }
    if ( array_key_exists( 'sitekey', $raw ) ) {
      return matrix_acf_scalar_string( $raw['sitekey'] );
    }
    $first = reset( $raw );
    if ( false !== $first ) {
      return matrix_acf_scalar_string( $first );
    }
    return '';
  }
  if ( is_object( $raw ) && method_exists( $raw, '__toString' ) ) {
    try {
      return trim( (string) $raw );
    } catch ( \Throwable $e ) {
      return '';
    }
  }
  return '';
}

/**
 * Enqueue theme assets + optional libs
 */
function matrix_starter_enqueue_scripts() {
  $theme_version = get_option('theme_css_version', '1.0');

  // Ensure jQuery is present early
  wp_enqueue_script('jquery');

  // Dev/prod asset base
  $is_dev = defined('WP_ENV') && WP_ENV === 'development';
  $base   = get_template_directory_uri();

  $app_js  = $is_dev ? '/wp-content/themes/matrix-starter/dist/app.js'  : $base . '/dist/app.js';
  $app_css = $is_dev ? '/wp-content/themes/matrix-starter/dist/app.css' : $base . '/dist/app.css';

  // Main bundle (footer)
  wp_enqueue_script('matrix-starter', $app_js, ['jquery'], '1.0.0', true);
  wp_enqueue_style('matrix-starter', $app_css, [], $theme_version);

  // Alpine + Intersect
  $load_alpine_intersect = ! is_front_page();
  if ($load_alpine_intersect) {
    wp_enqueue_script('alpine-intersect','https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js',[],null,true);
  }
  wp_enqueue_script(
    'alpine',
    'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js',
    $load_alpine_intersect ? ['alpine-intersect'] : [],
    null,
    true
  );
  if ($load_alpine_intersect) {
    wp_add_inline_script('alpine', "document.addEventListener('alpine:init',()=>{ if(window.Alpine&&window.AlpineIntersect) Alpine.plugin(window.AlpineIntersect); });");
  }
  wp_add_inline_style('matrix-starter', '[x-cloak]{display:none !important;}');

  /*
   * Klaviyo sign-up popup: the form design ships its close (X) icon with a
   * transparent stroke (rgba(255,255,255,0)) sitting on the white logo panel, so
   * the X is invisible. Force a visible glyph on a light, contrasting chip so it
   * reads on both the dark and white halves of the popup. Targets Klaviyo''s
   * own close button class, which is stable across forms.
   */
  wp_add_inline_style('matrix-starter', '
    .klaviyo-close-form{
      background:rgba(255,255,255,.92) !important;
      box-shadow:0 1px 4px rgba(0,0,0,.25),0 0 0 1px rgba(0,0,0,.12) !important;
      opacity:1 !important;
      visibility:visible !important;
    }
    .klaviyo-close-form svg{opacity:1 !important;overflow:visible !important;}
    .klaviyo-close-form svg *{stroke:#111 !important;opacity:1 !important;}
  ');

  // Stat counters (IntersectionObserver — no Alpine dependency). Homepage has none.
  $stat_counters_js = get_template_directory() . '/assets/js/stat-counters.js';
  if (file_exists($stat_counters_js) && ! is_front_page()) {
    wp_enqueue_script(
      'stat-counters',
      $base . '/assets/js/stat-counters.js',
      [],
      (string) filemtime($stat_counters_js),
      true
    );
  }

  // Theme forms helper — not used on the homepage (Klaviyo newsletter).
  $forms_js_path = get_template_directory() . '/inc/forms/js/forms.js';
  $load_theme_forms = ! is_front_page();
  if ($load_theme_forms) {
    wp_enqueue_script(
      'theme-forms',
      $base . '/inc/forms/js/forms.js',
      ['jquery'],
      file_exists($forms_js_path) ? filemtime($forms_js_path) : null,
      true
    );
  }

  // ---- CAPTCHA provider switch (Google reCAPTCHA v3 / Cloudflare Turnstile) ----
  $provider     = (function_exists('get_field') ? (get_field('captcha_provider', 'option') ?: 'none') : 'none');
  $provider     = matrix_acf_scalar_string( $provider ) ?: 'none';
  $recaptchaKey = matrix_acf_scalar_string( function_exists('get_field') ? get_field('recaptcha_site_key', 'option') : '' );
  $turnstileKey = matrix_acf_scalar_string( function_exists('get_field') ? get_field('turnstile_site_key', 'option') : '' );

  // Normalize provider value to lowercase for consistency
  $provider = strtolower( $provider );

  // Captcha scripts: contact forms, logged-out My Account, and checkout login
  // — live host only. Local/staging stay captcha-free.
  $captcha_active = function_exists('matrix_theme_form_captcha_enabled') && matrix_theme_form_captcha_enabled();
  $load_theme_captcha = $load_theme_forms && ! matrix_is_wc_flow_page() && $captcha_active;
  $load_account_captcha = $captcha_active
    && function_exists('is_account_page')
    && is_account_page()
    && ! is_user_logged_in();
  $load_checkout_login_captcha = $captcha_active
    && function_exists('is_checkout')
    && is_checkout()
    && ! is_user_logged_in();
  $expose_captcha_keys = $load_theme_captcha || $load_account_captcha || $load_checkout_login_captcha;

  if ($load_theme_forms) {
    wp_add_inline_script(
      'theme-forms',
      'window.themeFormsCaptchaProvider = ' . wp_json_encode( $expose_captcha_keys ? $provider : 'none', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . ';
   window.themeFormsRecaptchaV3      = ' . wp_json_encode( $expose_captcha_keys ? $recaptchaKey : '', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . ';
   window.themeFormsTurnstileSiteKey = ' . wp_json_encode( $expose_captcha_keys ? $turnstileKey : '', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . ';',
      'before'
    );
  }
  if ($expose_captcha_keys && $provider === 'recaptcha_v3' && $recaptchaKey) {
    wp_enqueue_script('recaptcha', "https://www.google.com/recaptcha/api.js?render={$recaptchaKey}", [], null, true);
  }

  if ($expose_captcha_keys && $provider === 'turnstile' && $turnstileKey) {
    wp_enqueue_script('turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js', [], null, true);
  }

  // Register optional third-parties
  wp_register_style('font-awesome','https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',[],null);

  wp_register_script('flowbite','https://unpkg.com/flowbite@1.6.5/dist/flowbite.min.js',['alpine'],'1.6.5',true);
  wp_register_style('slick-css','https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css',[],'1.8.1');
  wp_register_script('slick-js','https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js',['jquery'],'1.8.1',true);
  wp_register_script('headroom','https://cdnjs.cloudflare.com/ajax/libs/headroom/0.12.0/headroom.min.js',[],'0.12.0',true);
  wp_register_style('nice-select-css','https://cdn.jsdelivr.net/npm/jquery-nice-select@1.1.0/css/nice-select.css',[],'1.1.0');
  wp_register_script('nice-select-js','https://cdn.jsdelivr.net/npm/jquery-nice-select@1.1.0/js/jquery.nice-select.min.js',['jquery'],'1.1.0',true);

  // Leaflet
  wp_register_style('leaflet','https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',[],'1.9.4');
  wp_register_script('leaflet','https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',[],'1.9.4',true);

  // Conditionally enqueue based on Theme Options
  $enabled_scripts = function_exists('get_field') ? get_field('enabled_scripts', 'option') : [];
  if (is_array($enabled_scripts)) {
    // Font Awesome is already loaded site-wide by the footer (handle
    // 'fontawesome-6' in inc/rolling-donut-footer.php). Enqueuing this second
    // copy shipped two full Font Awesome stylesheets + webfonts on every page,
    // so the 'font_awesome' toggle no longer loads a duplicate.
    if (in_array('hamburger_css', $enabled_scripts, true)) wp_enqueue_style('hamburgers-css');
    if (in_array('flowbite',      $enabled_scripts, true)) wp_enqueue_script('flowbite');
    if (in_array('slick',         $enabled_scripts, true)) { wp_enqueue_style('slick-css'); wp_enqueue_script('slick-js'); }
    if (matrix_headroom_enabled()) {
      wp_enqueue_script('headroom');
      wp_add_inline_script('headroom', "
        (function () {
          var fixedClasses = ['fixed', 'top-0', 'left-0'];

          function matrixSyncSiteHeaderHeight() {
            var header = document.getElementById('site-nav');
            if (!header) return;
            document.documentElement.style.setProperty('--site-header-height', header.offsetHeight + 'px');
          }
          window.matrixSyncSiteHeaderHeight = matrixSyncSiteHeaderHeight;

          function matrixHeadroomSetAtTop(header, atTop) {
            if (atTop) {
              fixedClasses.forEach(function (cls) { header.classList.remove(cls); });
              header.classList.add('relative');
              header.classList.remove('-translate-y-full');
              header.classList.add('translate-y-0');
            } else {
              header.classList.remove('relative');
              fixedClasses.forEach(function (cls) { header.classList.add(cls); });
            }
            matrixSyncSiteHeaderHeight();
          }

          document.addEventListener('DOMContentLoaded', function () {
            var header = document.querySelector('#site-nav');
            if (!header || typeof Headroom === 'undefined') return;
            matrixSyncSiteHeaderHeight();
            var headroom = new Headroom(header, { tolerance: 5, offset: 100 });
            headroom.onTop = function () {
              matrixHeadroomSetAtTop(header, true);
            };
            headroom.onNotTop = function () {
              matrixHeadroomSetAtTop(header, false);
            };
            headroom.onPin = function () {
              header.classList.remove('-translate-y-full');
              header.classList.add('translate-y-0');
              matrixSyncSiteHeaderHeight();
            };
            headroom.onUnpin = function () {
              if (header.classList.contains('headroom--top')) return;
              header.classList.remove('translate-y-0');
              header.classList.add('-translate-y-full');
              matrixSyncSiteHeaderHeight();
            };
            headroom.init();
            matrixHeadroomSetAtTop(header, window.scrollY <= headroom.offset);
          });
          window.addEventListener('resize', matrixSyncSiteHeaderHeight);
          window.addEventListener('scroll', matrixSyncSiteHeaderHeight, { passive: true });
        })();
      ");
    }
    if (in_array('leaflet', $enabled_scripts, true)) { wp_enqueue_style('leaflet'); wp_enqueue_script('leaflet'); }

    // Optional: force-load Turnstile globally (even if provider not selected)
    if ($expose_captcha_keys && in_array('cloudflare_turnstile', $enabled_scripts, true) && !wp_script_is('turnstile','enqueued')) {
      wp_enqueue_script('turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js', [], null, true);
    }
  }

  // Contact/get-involved form custom select UI.
  // Load only on likely form pages to avoid unnecessary JS/CSS on every page.
  $should_load_nice_select = false;
  if (is_singular()) {
    $post_id = get_queried_object_id();
    if ($post_id) {
      $content = (string) get_post_field('post_content', $post_id);
      $content_lower = strtolower($content);
      $should_load_nice_select =
        has_block('acf/contact-form-001', $post_id)
        || has_block('acf/get-involved-form-001', $post_id)
        || has_block('acf/contact_form_001', $post_id)
        || has_block('acf/get_involved_form_001', $post_id)
        || str_contains($content_lower, 'contact_form_001')
        || str_contains($content_lower, 'get_involved_form_001')
        || str_contains($content_lower, 'data-contact-structured');
    }
  }
  if (!$should_load_nice_select) {
    $request_uri = strtolower((string) ($_SERVER['REQUEST_URI'] ?? ''));
    $should_load_nice_select =
      str_contains($request_uri, '/get-involved')
      || str_contains($request_uri, '/contact');
  }
  if ($should_load_nice_select) {
    wp_enqueue_style('nice-select-css');
    wp_enqueue_script('nice-select-js');
  }


  // Woo fragments
  if (class_exists('WooCommerce')) {
    wp_enqueue_script('wc-cart-fragments');
  }

  /**
   * Defer only non-critical scripts (NOT on checkout; NEVER WP packages)
   */
  add_filter('script_loader_tag', function ($tag, $handle) {
    if (is_admin()) return $tag;

    // Zero risk on checkout: don't touch script tags
    if (function_exists('is_checkout') && is_checkout()) {
      return $tag;
    }

    // Never defer these handles
    $no_defer = [
      // WP packages used by Woo + inline “*-js-after” snippets
      'wp-i18n','wp-hooks','wp-element','wp-components','wp-compose','wp-data',
      'wp-keycodes','wp-html-entities','wp-is-shallow-equal','wp-private-apis',
      'wp-priority-queue','wp-url','wp-api-fetch',

      // Foundations some packages rely on
      'react','react-dom','lodash','moment',

      // Theme + Woo essentials
      'jquery','jquery-core','jquery-migrate',
      'matrix-starter','theme-forms','matrix-newsletter','matrix-rd-cart',
      'wc-cart','wc-cart-fragments','woocommerce',
      'recaptcha','turnstile',
      'alpine-intersect','alpine',
      'wc-checkout','wc-country-select','wc-address-i18n',
      'selectWoo','jquery-blockui','jquery-payment',
      'slick-js',
      'wc-add-to-cart-variation','wc-credit-card-form',
      'wc-password-strength-meter',
    ];

    if (in_array($handle, $no_defer, true)) {
      return $tag;
    }

    return (strpos($tag, ' src=') !== false) ? str_replace(' src', ' defer src', $tag) : $tag;
  }, 10, 2);
}
add_action('wp_enqueue_scripts', 'matrix_starter_enqueue_scripts', 20);

/**
 * Keep jQuery in header group so it prints early if needed
 */
add_action('wp_enqueue_scripts', function () {
  if (!is_admin()) {
    wp_scripts()->add_data('jquery', 'group', 0);
    wp_scripts()->add_data('jquery-core', 'group', 0);
    wp_scripts()->add_data('jquery-migrate', 'group', 0);
  }
}, 5);

/**
 * 1) Always load core WP packages in the header (group 0)
 */
add_action('wp_enqueue_scripts', function () {
  $wp_pkgs = [
    'wp-i18n','wp-hooks','wp-element','wp-components','wp-compose','wp-data',
    'wp-keycodes','wp-html-entities','wp-is-shallow-equal','wp-private-apis',
    'wp-priority-queue','wp-url','wp-api-fetch',
    // common deps
    'react','react-dom','lodash','moment',
  ];
  $scripts = wp_scripts();
  if (!$scripts) return;
  foreach ($wp_pkgs as $h) {
    $scripts->add_data($h, 'group', 0);
  }
}, 1);

/**
 * 2) Never async/defer WP packages or /wp-includes/js/dist/* (global, very late)
 */
add_filter('script_loader_tag', function ($tag, $handle) {
  if (is_admin()) return $tag;

  $critical = [
    'wp-i18n','wp-hooks','wp-element','wp-components','wp-compose','wp-data',
    'wp-keycodes','wp-html-entities','wp-is-shallow-equal','wp-private-apis',
    'wp-priority-queue','wp-url','wp-api-fetch',
    'react','react-dom','lodash','moment',
  ];

  $is_core_pkg = in_array($handle, $critical, true)
              || (strpos($tag, '/wp-includes/js/dist/') !== false);

  if ($is_core_pkg) {
    // strip both async and defer regardless of who added them
    $tag = preg_replace('/\sdefer(=("|\').*?\2)?/i', '', $tag);
    $tag = preg_replace('/\sasync(=("|\').*?\2)?/i', '', $tag);
  }

  return $tag;
}, 9999, 2);

/**
 * 3) On checkout, remove ANY async/defer that slipped through
 */
add_filter('script_loader_tag', function ($tag) {
  if (function_exists('is_checkout') && is_checkout()) {
    $tag = preg_replace('/\sdefer(=("|\').*?\2)?/i', '', $tag);
    $tag = preg_replace('/\sasync(=("|\').*?\2)?/i', '', $tag);
  }
  return $tag;
}, 10000);

/**
 * 4) (Optional) Ask optimizers to back off on checkout
 */
add_action('template_redirect', function () {
  if (! function_exists('is_checkout') || ! is_checkout()) return;

  // Autoptimize
  if (function_exists('autoptimize_do_cache')) {
    add_filter('autoptimize_filter_js_defer', '__return_false', 99);
    add_filter('autoptimize_filter_js_async', '__return_false', 99);
    add_filter('autoptimize_filter_js_deferthis', '__return_empty_array', 99);
  }

  // LiteSpeed Cache
  if (defined('LSCWP_V')) {
    add_filter('litespeed_optimize_js_defer', '__return_false', 99);
    add_filter('litespeed_optimize_js_delayed', '__return_false', 99);
  }

  // WP Rocket etc.
  if (function_exists('rocket_is_plugin_active')) {
    add_filter('rocket_delay_js', '__return_false', 99);
    add_filter('rocket_minify_js', '__return_false', 99);
    add_filter('rocket_defer_js', '__return_false', 99);
  }
});
