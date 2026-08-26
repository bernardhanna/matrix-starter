<?php
/**
 * Sync product images from production so local matches live.
 *
 * Pulls featured + gallery images from the WooCommerce Store API, then
 * variation images (the individual donuts on box-builder pages). Only
 * replaces a local image when the file bytes differ (md5). Prices, copy,
 * and other product data are not touched.
 *
 * Usage (from app/public):
 *   wp eval-file wp-content/themes/matrix-starter/scripts/sync-prod-thumbnails.php dry
 *   wp eval-file wp-content/themes/matrix-starter/scripts/sync-prod-thumbnails.php slug=apple-crumble
 *   wp eval-file wp-content/themes/matrix-starter/scripts/sync-prod-thumbnails.php limit=10
 *   wp eval-file wp-content/themes/matrix-starter/scripts/sync-prod-thumbnails.php
 *
 * Args (positional, any order):
 *   dry        - report only, change nothing
 *   limit=N    - process at most N local products (parents; variations follow)
 *   slug=foo   - only this product slug (parent or variation)
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

if (function_exists('wp_raise_memory_limit')) {
    wp_raise_memory_limit('admin');
}
@set_time_limit(0);

/** @var array $args provided by `wp eval-file` */
$argv_in = isset($args) && is_array($args) ? $args : array();

$dry         = in_array('dry', $argv_in, true);
$base        = 'https://therollingdonut.ie';
$limit       = 0;
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

$http_get = static function (string $url, int $timeout = 30) use ($UA) {
    return wp_remote_get(
        $url,
        array(
            'timeout'     => $timeout,
            'redirection' => 5,
            'user-agent'  => $UA,
            'headers'     => array('Accept' => 'application/json, text/html;q=0.9'),
        )
    );
};

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
 * Unique full-size image URLs from a Store API product payload.
 * First item is featured; the rest are gallery (featured duplicate stripped).
 *
 * @return string[]
 */
$images_from_store = static function (array $payload) use ($absolutize): array {
    $out  = array();
    $seen = array();
    foreach ((array) ($payload['images'] ?? array()) as $img) {
        $src = '';
        if (is_array($img)) {
            $src = (string) ($img['src'] ?? '');
        } elseif (is_string($img)) {
            $src = $img;
        }
        $src = $absolutize($src);
        if ($src === '' || strpos($src, 'data:') === 0) {
            continue;
        }
        $key = strtok(basename(wp_parse_url($src, PHP_URL_PATH) ?: $src), '?');
        $key = strtolower($key) . '|' . $src;
        if (isset($seen[ $src ]) || isset($seen[ $key ])) {
            continue;
        }
        $seen[ $src ] = true;
        $seen[ $key ] = true;
        $out[]        = $src;
    }
    return $out;
};

$store_fetch = static function (string $path) use ($base, $http_get) {
    $url  = $base . '/wp-json/wc/store/v1' . $path;
    $resp = $http_get($url);
    if (is_wp_error($resp) || (int) wp_remote_retrieve_response_code($resp) !== 200) {
        return null;
    }
    $json = json_decode((string) wp_remote_retrieve_body($resp), true);
    return is_array($json) ? $json : null;
};

echo "Fetching live catalog from Store API…\n";
$live_by_slug = array();
$live_by_id   = array();
$page         = 1;
while ($page <= 20) {
    $batch = $store_fetch('/products?per_page=100&page=' . $page);
    if (! is_array($batch) || $batch === array()) {
        break;
    }
    foreach ($batch as $item) {
        if (! is_array($item) || empty($item['id'])) {
            continue;
        }
        $id   = (int) $item['id'];
        $slug = (string) ($item['slug'] ?? '');
        $live_by_id[ $id ] = $item;
        if ($slug !== '') {
            $live_by_slug[ $slug ] = $item;
        }
    }
    if (count($batch) < 100) {
        break;
    }
    $page++;
}
echo '  catalog products: ' . count($live_by_id) . "\n";

echo "Fetching live variation images…\n";
$variation_fetches = 0;
foreach ($live_by_id as $item) {
    if (($item['type'] ?? '') !== 'variable') {
        continue;
    }
    foreach ((array) ($item['variations'] ?? array()) as $var) {
        $vid = is_array($var) ? (int) ($var['id'] ?? 0) : (int) $var;
        if ($vid <= 0 || isset($live_by_id[ $vid ])) {
            continue;
        }
        $payload = $store_fetch('/products/' . $vid);
        $variation_fetches++;
        if (! is_array($payload) || empty($payload['id'])) {
            continue;
        }
        $live_by_id[ (int) $payload['id'] ] = $payload;
        $vslug = (string) ($payload['slug'] ?? '');
        if ($vslug !== '') {
            $live_by_slug[ $vslug ] = $payload;
        }
    }
}
echo "  variation API calls: $variation_fetches\n";
echo '  live records: ' . count($live_by_id) . "\n\n";

/**
 * HTML fallback: featured from og:image / data-large_image.
 */
$extract_featured_html = static function (string $html) use ($absolutize): string {
    if (preg_match('/data-large_image=["\']([^"\']+)["\']/i', $html, $m)) {
        return $absolutize($m[1]);
    }
    if (preg_match('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m)) {
        return $absolutize($m[1]);
    }
    if (preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:image["\']/i', $html, $m)) {
        return $absolutize($m[1]);
    }
    return '';
};

$resolve_live_images = static function (WC_Product $product) use (
    $live_by_id,
    $live_by_slug,
    $images_from_store,
    $extract_featured_html,
    $http_get,
    $base
): array {
    $slug = $product->get_slug();
    $pid  = $product->get_id();

    $payload = $live_by_slug[ $slug ] ?? $live_by_id[ $pid ] ?? null;

    // Variation fallback: parent slug + size attribute.
    if (! $payload && $product->is_type('variation')) {
        $parent = wc_get_product($product->get_parent_id());
        $pslug  = $parent ? $parent->get_slug() : '';
        $size   = strtolower((string) $product->get_attribute('pa_size'));
        if ($pslug && $size && isset($live_by_slug[ $pslug ])) {
            $parent_live = $live_by_slug[ $pslug ];
            foreach ((array) ($parent_live['variations'] ?? array()) as $var) {
                if (! is_array($var)) {
                    continue;
                }
                $vid = (int) ($var['id'] ?? 0);
                foreach ((array) ($var['attributes'] ?? array()) as $attr) {
                    $val = strtolower((string) ($attr['value'] ?? ''));
                    if ($val === $size && isset($live_by_id[ $vid ])) {
                        $payload = $live_by_id[ $vid ];
                        break 2;
                    }
                }
            }
        }
    }

    if (is_array($payload)) {
        $urls = $images_from_store($payload);
        if ($urls) {
            return $urls;
        }
    }

    $url  = $base . '/product/' . rawurlencode($slug) . '/';
    $resp = $http_get($url, 25);
    if (is_wp_error($resp) || (int) wp_remote_retrieve_response_code($resp) !== 200) {
        return array();
    }
    $feat = $extract_featured_html((string) wp_remote_retrieve_body($resp));
    return $feat ? array( $feat ) : array();
};

/** Remote URL => local attachment ID created or reused this run. */
$url_to_att = array();
$file_md5   = array();
$file_tmp   = array();

$download_remote = static function (string $url) use (&$file_md5, &$file_tmp): string {
    if (isset($file_md5[ $url ]) && isset($file_tmp[ $url ]) && file_exists($file_tmp[ $url ])) {
        return $file_md5[ $url ];
    }
    $tmp = download_url($url, 45);
    if (is_wp_error($tmp)) {
        return '';
    }
    $hash = md5_file($tmp);
    if (! is_string($hash) || $hash === '') {
        @unlink($tmp);
        return '';
    }
    $file_md5[ $url ] = $hash;
    $file_tmp[ $url ] = $tmp;
    return $hash;
};

$md5_of_attachment = static function (int $att_id): string {
    $file = $att_id ? get_attached_file($att_id) : '';
    if ($file && file_exists($file)) {
        $hash = md5_file($file);
        return is_string($hash) ? $hash : '';
    }
    return '';
};

$ensure_attachment = static function (string $url, int $parent_id, string $title) use (&$url_to_att, &$file_tmp, $download_remote): int {
    if (isset($url_to_att[ $url ]) && (int) $url_to_att[ $url ] > 0) {
        return (int) $url_to_att[ $url ];
    }

    if ($download_remote($url) === '') {
        echo "    download failed: $url\n";
        return 0;
    }

    $tmp = $file_tmp[ $url ] ?? '';
    if ($tmp === '' || ! file_exists($tmp)) {
        echo "    missing temp file: $url\n";
        return 0;
    }

    // media_handle_sideload moves the temp file; copy so a shared URL can be reused.
    $copy = wp_tempnam($url);
    if (! $copy || ! copy($tmp, $copy)) {
        echo "    could not copy temp file: $url\n";
        return 0;
    }

    $file_array = array(
        'name'     => basename(wp_parse_url($url, PHP_URL_PATH) ?: 'image.png'),
        'tmp_name' => $copy,
    );
    $att_id = media_handle_sideload($file_array, $parent_id, $title);
    if (is_wp_error($att_id)) {
        @unlink($copy);
        echo '    sideload failed: ' . $att_id->get_error_message() . "\n";
        return 0;
    }
    $url_to_att[ $url ] = (int) $att_id;
    return (int) $att_id;
};

$local_image_md5s = static function (WC_Product $product) use ($md5_of_attachment): array {
    $ids = array();
    $fid = (int) $product->get_image_id();
    if ($fid) {
        $ids[] = $fid;
    }
    foreach ($product->get_gallery_image_ids() as $gid) {
        $gid = (int) $gid;
        if ($gid && ! in_array($gid, $ids, true)) {
            $ids[] = $gid;
        }
    }
    $hashes = array();
    foreach ($ids as $id) {
        $hashes[] = $md5_of_attachment($id);
    }
    return array( $ids, $hashes );
};

$products = wc_get_products(
    array(
        'status'  => array( 'publish', 'private' ),
        'limit'   => -1,
        'return'  => 'objects',
        'orderby' => 'title',
        'order'   => 'ASC',
        'type'    => array( 'simple', 'variable', 'woosb', 'donut_box_builder', 'grouped', 'external' ),
    )
);

$counts = array(
    'updated'   => 0,
    'unchanged' => 0,
    'no_image'  => 0,
    'error'     => 0,
    'scanned'   => 0,
    'skipped'   => 0,
);
$processed = 0;

echo ($dry ? "DRY RUN — no changes will be made\n" : "LIVE RUN — product images will be updated\n");
echo "Source: $base\n\n";

$process_one = static function (WC_Product $product) use (
    &$counts,
    $resolve_live_images,
    $ensure_attachment,
    $local_image_md5s,
    $md5_of_attachment,
    $download_remote,
    &$file_md5,
    $dry
): void {
    $slug = $product->get_slug();
    $pid  = $product->get_id();
    $name = $product->get_name();
    $kind = $product->get_type();

    $counts['scanned']++;

    $remote_urls = $resolve_live_images($product);
    if (! $remote_urls) {
        echo "  [no image]  $name ($slug) [$kind]\n";
        $counts['no_image']++;
        return;
    }

    $remote_hashes = array();
    foreach ($remote_urls as $url) {
        $hash = $download_remote($url);
        if ($hash === '') {
            echo "  [error]     $name ($slug) — download " . basename(wp_parse_url($url, PHP_URL_PATH) ?: $url) . "\n";
            $counts['error']++;
            return;
        }
        $remote_hashes[] = $hash;
    }

    list($local_ids, $local_hashes) = $local_image_md5s($product);
    if ($local_hashes === $remote_hashes) {
        $counts['unchanged']++;
        return;
    }

    $label = implode(', ', array_map(static function ($u) {
        return basename(wp_parse_url($u, PHP_URL_PATH) ?: $u);
    }, $remote_urls));

    if ($dry) {
        echo "  [would set] $name ($slug) [$kind] <- $label";
        if ($local_ids) {
            echo ' (replaces ' . count($local_ids) . ' local file(s))';
        }
        echo "\n";
        $counts['updated']++;
        return;
    }

    $new_ids = array();
    foreach ($remote_urls as $i => $url) {
        $want = $file_md5[ $url ] ?? '';
        $reused = 0;
        foreach ($local_ids as $lid) {
            if ($want && $md5_of_attachment($lid) === $want) {
                $reused = $lid;
                break;
            }
        }
        if ($reused) {
            $new_ids[] = $reused;
            continue;
        }
        $att = $ensure_attachment($url, $pid, $name);
        if (! $att) {
            echo "  [error]     $name ($slug) — could not import " . basename(wp_parse_url($url, PHP_URL_PATH) ?: $url) . "\n";
            $counts['error']++;
            return;
        }
        $new_ids[] = $att;
    }

    $featured = (int) ($new_ids[0] ?? 0);
    $gallery  = array_values(array_filter(array_slice($new_ids, 1)));

    if ($featured) {
        if ($product->is_type('variation')) {
            update_post_meta($pid, '_thumbnail_id', $featured);
        } else {
            set_post_thumbnail($pid, $featured);
        }
        $product->set_image_id($featured);
    }

    if ($product->is_type('variation')) {
        update_post_meta($pid, '_product_image_gallery', implode(',', $gallery));
    } else {
        $product->set_gallery_image_ids($gallery);
        $product->save();
    }

    echo "  [updated]   $name ($slug) [$kind] <- $label\n";
    $counts['updated']++;
};

foreach ($products as $product) {
    $slug = $product->get_slug();

    $child_match = false;
    if ($slug_filter && $product->is_type('variable')) {
        foreach ($product->get_children() as $cid) {
            $child = wc_get_product($cid);
            if ($child && $child->get_slug() === $slug_filter) {
                $child_match = true;
                break;
            }
        }
    }
    if ($slug_filter && $slug !== $slug_filter && ! $child_match) {
        continue;
    }
    if ($limit && $processed >= $limit) {
        break;
    }
    $processed++;

    if (! $slug_filter || $slug === $slug_filter) {
        $process_one($product);
    }

    if ($product->is_type('variable')) {
        foreach ($product->get_children() as $cid) {
            $child = wc_get_product($cid);
            if (! $child) {
                continue;
            }
            if ($slug_filter && $child->get_slug() !== $slug_filter && $slug !== $slug_filter) {
                continue;
            }
            $process_one($child);
        }
    }
}

foreach ($file_tmp as $tmp_path) {
    if (is_string($tmp_path) && $tmp_path !== '' && file_exists($tmp_path)) {
        @unlink($tmp_path);
    }
}

echo "\n----------------------------------------\n";
echo sprintf(
    "Scanned: %d | %s: %d | Unchanged: %d | No live image: %d | Errors: %d\n",
    $counts['scanned'],
    $dry ? 'Would update' : 'Updated',
    $counts['updated'],
    $counts['unchanged'],
    $counts['no_image'],
    $counts['error']
);
