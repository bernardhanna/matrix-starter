<?php
/**
 * Non-destructive product featured-image sync from production.
 *
 * For every published WooCommerce product it reads the matching product page on
 * production (matched by slug), extracts the full-size featured image, and only
 * updates the LOCAL featured image when the image actually differs (md5 compare).
 * Nothing else is touched — galleries, content, prices, options and all other
 * local development work are left exactly as they are.
 *
 * Usage (from app/public):
 *   wp eval-file wp-content/themes/matrix-starter/scripts/sync-prod-thumbnails.php dry
 *   wp eval-file wp-content/themes/matrix-starter/scripts/sync-prod-thumbnails.php slug=chocolate-ring-large
 *   wp eval-file wp-content/themes/matrix-starter/scripts/sync-prod-thumbnails.php limit=10
 *   wp eval-file wp-content/themes/matrix-starter/scripts/sync-prod-thumbnails.php           (apply all)
 *
 * Args (positional, any order):
 *   dry        - report only, change nothing
 *   limit=N    - process at most N products
 *   slug=foo   - only this product slug
 *   base=URL   - override production base (default https://therollingdonut.ie)
 *
 * @package matrix-starter
 */

if (! defined('ABSPATH')) {
    exit;
}

require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

/** @var array $args provided by `wp eval-file` */
$argv_in = isset($args) && is_array($args) ? $args : array();

$dry   = in_array('dry', $argv_in, true);
$base  = 'https://therollingdonut.ie';
$limit = 0;
$slug_filter = '';

foreach ($argv_in as $a) {
    if (strpos($a, 'limit=') === 0) {
        $limit = (int) substr($a, 6);
    } elseif (strpos($a, 'slug=') === 0) {
        $slug_filter = trim(substr($a, 5));
    } elseif (strpos($a, 'base=') === 0) {
        $base = rtrim(trim(substr($a, 5)), '/');
    }
}

$UA = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36';

/** Resolve a possibly protocol-relative or root-relative URL to an absolute one. */
$absolutize = static function (string $u) use ($base): string {
    $u = trim(html_entity_decode($u));
    if ($u === '') {
        return '';
    }
    if (strpos($u, '//') === 0) {
        return 'https:' . $u;
    }
    if ($u[0] === '/') {
        return $base . $u;
    }
    return $u;
};

/**
 * Pull the full-size featured image URL out of a WooCommerce product page.
 */
$extract_image = static function (string $html) use ($absolutize): string {
    // 1) WooCommerce gallery exposes the full image via data-large_image.
    if (preg_match('/data-large_image=["\']([^"\']+)["\']/i', $html, $m)) {
        return $absolutize($m[1]);
    }
    // 2) Open Graph image (usually the featured image).
    if (preg_match('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m)) {
        return $absolutize($m[1]);
    }
    if (preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:image["\']/i', $html, $m)) {
        return $absolutize($m[1]);
    }
    // 3) The post thumbnail <img> (handle lazy-load attributes too).
    if (preg_match('/<img[^>]+wp-post-image[^>]*>/i', $html, $img)) {
        foreach (array('data-large_image', 'data-src', 'src') as $attr) {
            if (preg_match('/' . preg_quote($attr, '/') . '=["\']([^"\']+)["\']/i', $img[0], $m)) {
                $u = $absolutize($m[1]);
                if ($u && strpos($u, 'data:') !== 0) {
                    return $u;
                }
            }
        }
    }
    return '';
};

$products = wc_get_products(array(
    'status' => 'publish',
    'limit'  => -1,
    'return' => 'objects',
    'orderby'=> 'title',
    'order'  => 'ASC',
));

$counts = array('updated' => 0, 'unchanged' => 0, 'no_page' => 0, 'no_image' => 0, 'error' => 0, 'scanned' => 0);
$processed = 0;

echo ($dry ? "DRY RUN — no changes will be made\n" : "LIVE RUN — featured images will be updated\n");
echo "Source: $base\n\n";

foreach ($products as $product) {
    $slug = $product->get_slug();
    if ($slug_filter && $slug !== $slug_filter) {
        continue;
    }
    if ($limit && $processed >= $limit) {
        break;
    }
    $processed++;
    $counts['scanned']++;

    $pid  = $product->get_id();
    $name = $product->get_name();
    $url  = $base . '/product/' . rawurlencode($slug) . '/';

    $resp = wp_remote_get($url, array(
        'timeout'     => 25,
        'redirection' => 5,
        'user-agent'  => $UA,
        'headers'     => array('Accept' => 'text/html'),
    ));

    if (is_wp_error($resp) || (int) wp_remote_retrieve_response_code($resp) !== 200) {
        $code = is_wp_error($resp) ? $resp->get_error_message() : wp_remote_retrieve_response_code($resp);
        echo "  [no page]   $name ($slug) — $code\n";
        $counts['no_page']++;
        continue;
    }

    $img = $extract_image((string) wp_remote_retrieve_body($resp));
    if (! $img) {
        echo "  [no image]  $name ($slug)\n";
        $counts['no_image']++;
        continue;
    }

    $tmp = download_url($img, 30);
    if (is_wp_error($tmp)) {
        echo "  [error]     $name ($slug) — download: " . $tmp->get_error_message() . "\n";
        $counts['error']++;
        continue;
    }

    $remote_md5 = md5_file($tmp);
    $cur_id     = get_post_thumbnail_id($pid);
    $cur_file   = $cur_id ? get_attached_file($cur_id) : '';
    $cur_md5    = ($cur_file && file_exists($cur_file)) ? md5_file($cur_file) : '';

    if ($remote_md5 && $remote_md5 === $cur_md5) {
        @unlink($tmp);
        $counts['unchanged']++;
        continue;
    }

    if ($dry) {
        echo "  [would set] $name ($slug) <- " . basename(wp_parse_url($img, PHP_URL_PATH)) . ($cur_md5 ? " (replaces existing)" : " (no current image)") . "\n";
        @unlink($tmp);
        $counts['updated']++;
        continue;
    }

    $file_array = array(
        'name'     => basename(wp_parse_url($img, PHP_URL_PATH)),
        'tmp_name' => $tmp,
    );
    $att_id = media_handle_sideload($file_array, $pid, $name);
    if (is_wp_error($att_id)) {
        @unlink($tmp);
        echo "  [error]     $name ($slug) — sideload: " . $att_id->get_error_message() . "\n";
        $counts['error']++;
        continue;
    }

    set_post_thumbnail($pid, $att_id);
    echo "  [updated]   $name ($slug) <- " . basename(wp_parse_url($img, PHP_URL_PATH)) . "\n";
    $counts['updated']++;
}

echo "\n----------------------------------------\n";
echo sprintf(
    "Scanned: %d | %s: %d | Unchanged: %d | No page: %d | No image: %d | Errors: %d\n",
    $counts['scanned'],
    $dry ? 'Would update' : 'Updated',
    $counts['updated'],
    $counts['unchanged'],
    $counts['no_page'],
    $counts['no_image'],
    $counts['error']
);
