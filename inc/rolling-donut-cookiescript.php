<?php
/**
 * CookieScript CMP (cookie-script.com).
 *
 * Live already uses this item ID on the Cookie Policy report widget. The
 * banner script was not in this theme, so it would disappear on go-live.
 * Output it in <head> (not delayed/minified) so it runs on the allowed
 * production host. CookieScript itself only shows the banner on domains
 * listed in its dashboard — localhost typically will not.
 *
 * @package matrix-starter
 */

defined('ABSPATH') || exit;

/** CookieScript item ID from the live Cookie Policy report widget. */
const MATRIX_RD_COOKIESCRIPT_ID = '44a2240fea54936e8c471d5b2fd9c666';

/**
 * CookieScript item ID (Theme Options override, then built-in live ID).
 */
function matrix_rd_cookiescript_id(): string {
    $from_acf = '';
    if (function_exists('get_field')) {
        $from_acf = trim((string) get_field('cookie_script_id', 'option'));
    }

    $id = $from_acf !== '' ? $from_acf : MATRIX_RD_COOKIESCRIPT_ID;
    $id = (string) apply_filters('matrix_rd_cookiescript_id', $id);
    $id = strtolower(preg_replace('/[^a-f0-9]/i', '', $id) ?? '');

    return strlen($id) === 32 ? $id : '';
}

/**
 * Print the official CookieScript snippet as early as possible in <head>.
 */
function matrix_rd_output_cookiescript(): void {
    if (is_admin()) {
        return;
    }

    $id = matrix_rd_cookiescript_id();
    if ($id === '') {
        return;
    }

    $src = 'https://cdn.cookie-script.com/s/' . $id . '.js';

    echo "\n<!-- CookieScript CMP -->\n";
    printf(
        '<script type="text/javascript" charset="UTF-8" src="%s"></script>' . "\n",
        esc_url($src)
    );
}
add_action('wp_head', 'matrix_rd_output_cookiescript', 1);

/**
 * Keep CookieScript off WP Rocket delay/minify/defer so consent runs on live.
 *
 * @param array<int, string> $exclusions Exclusions.
 * @return array<int, string>
 */
function matrix_rd_cookiescript_rocket_exclusions(array $exclusions): array {
    $exclusions[] = 'cdn.cookie-script.com';
    $exclusions[] = 'report.cookie-script.com';
    $exclusions[] = 'cookie-script.com';
    $exclusions[] = 'cookiescript';
    return array_values(array_unique($exclusions));
}
add_filter('rocket_delay_js_exclusions', 'matrix_rd_cookiescript_rocket_exclusions');
add_filter('rocket_exclude_defer_js', 'matrix_rd_cookiescript_rocket_exclusions');
add_filter('rocket_exclude_js', 'matrix_rd_cookiescript_rocket_exclusions');
add_filter('rocket_minify_excluded_external_js', 'matrix_rd_cookiescript_rocket_exclusions');

add_filter('wp_resource_hints', static function (array $hints, string $relation): array {
    if (matrix_rd_cookiescript_id() === '') {
        return $hints;
    }

    if ($relation === 'dns-prefetch' || $relation === 'preconnect') {
        $hints[] = array(
            'href'        => 'https://cdn.cookie-script.com',
            'crossorigin' => 'anonymous',
        );
    }

    return $hints;
}, 10, 2);
