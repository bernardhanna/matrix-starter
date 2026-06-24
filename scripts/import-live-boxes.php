<?php
/**
 * Import / sync the new "set boxes" from production (therollingdonut.ie).
 *
 * Creates (or updates) fixed-content WPC bundle products ("set boxes" that the
 * customer cannot alter) to mirror live. Bundle contents are mapped to local
 * "Large" variation IDs. Products are created as DRAFT so they can be reviewed
 * before publishing. Re-running is safe (matches by slug, rebuilds contents).
 *
 * Usage (from app/public):
 *   wp eval-file wp-content/themes/matrix-starter/scripts/import-live-boxes.php dry
 *   wp eval-file wp-content/themes/matrix-starter/scripts/import-live-boxes.php
 *   wp eval-file wp-content/themes/matrix-starter/scripts/import-live-boxes.php publish
 *
 * Args: dry (no changes) | publish (set status publish instead of draft)
 *
 * @package matrix-starter
 */

if (! defined('ABSPATH')) {
    exit;
}

require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$argv_in = isset($args) && is_array($args) ? $args : array();
$dry     = in_array('dry', $argv_in, true);
$status  = in_array('publish', $argv_in, true) ? 'publish' : 'draft';
$base    = 'https://therollingdonut.ie';
$UA      = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36';

/**
 * Box definitions mirrored from live. `contents` = [ large_variation_id => qty ].
 */
$boxes = array(
    array(
        'slug'  => 'the-best-dad-gift-box',
        'title' => 'The Best Dad - Gift Box',
        'price' => '35',
        'whole' => 6,
        'desc'  => "Treat Dad to something sweet this Father's Day! Our special Gift Box includes 6 of our delicious donuts, and a mug – because Dad deserves the best with every bite and sip!\n\nPlease Note - This is a set box and so flavours cannot be altered!",
        'contents' => array(
            46984 => 1, // Kinder Crunch - Large
            41271 => 1, // Dubai Donut - Large
            1899  => 1, // The Dub - Large
            1895  => 1, // Old Fashioned Raspberry Jam - Large
            2056  => 1, // The Milkybar Kid - Large
            2029  => 1, // Caramelised Biscuit - Large
        ),
    ),
    array(
        'slug'  => 'thank-you-large-sourdough',
        'title' => 'Thank You - Large Sourdough',
        'price' => '36',
        'whole' => 12,
        'desc'  => "Celebrate a teacher, staff member, or a special someone with our 'Thank You' box of 12 large sourdough donuts!\n\nThis box contains 12 of our best-selling flavours.\n\n*This is a set box and so it cannot be altered.",
        'contents' => array(
            1911  => 1, // Ferrero Rocher
            2056  => 1, // The Milkybar Kid
            2029  => 1, // Caramelised Biscuit
            2112  => 1, // Blueberry Cheesecake
            2071  => 1, // Apple Crumble
            1903  => 1, // Kinder Bueno
            2052  => 1, // White Kinder Bueno
            1899  => 1, // The Dub
            1923  => 1, // Cookies & Cream
            41271 => 1, // Dubai Donut
            1915  => 1, // Red Velvet
            1895  => 1, // Old Fashioned Raspberry Jam
        ),
    ),
    array(
        'slug'  => 'class-of-2026-large-sourdough',
        'title' => 'Class of 2026 - Large Sourdough',
        'price' => '36',
        'whole' => 12,
        'desc'  => "Celebrate the end of academic year with our 'Class of 2026' themed box of 12 of our delicious sourdough donuts!\n\nThis box contains 12 of our best-selling flavours.\n\n*This is a set box and so it cannot be altered.",
        'contents' => array(
            1911  => 1,
            2056  => 1,
            2029  => 1,
            2112  => 1,
            2071  => 1,
            1903  => 1,
            2052  => 1,
            1899  => 1,
            1923  => 1,
            41271 => 1,
            1915  => 1,
            1895  => 1,
        ),
    ),
);

/** Build the woosb_ids meta array from a [variation_id => qty] map. */
$build_woosb_ids = static function (array $contents): array {
    $out = array();
    foreach ($contents as $vid => $qty) {
        $key       = substr(md5($vid . microtime() . wp_rand()), 0, 4);
        $out[$key] = array(
            'id'  => (string) $vid,
            'sku' => '',
            'qty' => (string) $qty,
            'min' => '',
            'max' => '',
        );
    }
    return $out;
};

/** Pull a full-size featured image URL from a live product page. */
$fetch_image = static function (string $url) use ($UA, $base): string {
    $r = wp_remote_get($url, array('timeout' => 25, 'user-agent' => $UA, 'headers' => array('Accept' => 'text/html')));
    if (is_wp_error($r) || (int) wp_remote_retrieve_response_code($r) !== 200) {
        return '';
    }
    $html = (string) wp_remote_retrieve_body($r);
    $u    = '';
    if (preg_match('/data-large_image=["\']([^"\']+)["\']/i', $html, $m)) {
        $u = $m[1];
    } elseif (preg_match('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m)) {
        $u = $m[1];
    }
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

echo ($dry ? "DRY RUN — no changes\n" : "Applying (status: $status)\n");
echo "Source: $base\n\n";

foreach ($boxes as $box) {
    $existing = get_page_by_path($box['slug'], OBJECT, 'product');
    $verb     = $existing ? 'update' : 'create';
    echo strtoupper($verb) . ": {$box['title']} ({$box['slug']}) — €{$box['price']}, {$box['whole']} items, " . count($box['contents']) . " flavours\n";

    if ($dry) {
        continue;
    }

    if ($existing) {
        $pid = (int) $existing->ID;
        wp_update_post(array(
            'ID'           => $pid,
            'post_title'   => $box['title'],
            'post_content' => $box['desc'],
            'post_status'  => $status,
        ));
    } else {
        $pid = wp_insert_post(array(
            'post_type'    => 'product',
            'post_status'  => $status,
            'post_title'   => $box['title'],
            'post_name'    => $box['slug'],
            'post_content' => $box['desc'],
        ));
    }

    if (! $pid || is_wp_error($pid)) {
        echo "  ! failed to create/update post\n";
        continue;
    }

    // Product type + taxonomies.
    wp_set_object_terms($pid, 'woosb', 'product_type');
    wp_set_object_terms($pid, 'box', 'rd_product_type');
    wp_set_object_terms($pid, array('all'), 'product_cat');

    // Bundle (set box) meta — mirrors existing set boxes on the site.
    update_post_meta($pid, 'woosb_ids', $build_woosb_ids($box['contents']));
    update_post_meta($pid, 'woosb_disable_auto_price', 'on');   // fixed price, not summed
    update_post_meta($pid, 'woosb_optional_products', 'on');
    update_post_meta($pid, 'woosb_manage_stock', 'off');
    update_post_meta($pid, 'woosb_limit_each_min', '');
    update_post_meta($pid, 'woosb_limit_each_max', '');
    update_post_meta($pid, 'woosb_limit_whole_min', (string) $box['whole']);
    update_post_meta($pid, 'woosb_limit_whole_max', (string) $box['whole']);

    // Price + stock via the product API.
    $prod = wc_get_product($pid);
    if ($prod) {
        $prod->set_regular_price($box['price']);
        $prod->set_price($box['price']);
        $prod->set_stock_status('instock');
        $prod->set_catalog_visibility('visible');
        $prod->save();
    }

    // Featured image from live (only if missing locally).
    if (! get_post_thumbnail_id($pid)) {
        $img = $fetch_image($base . '/product/' . $box['slug'] . '/');
        if ($img) {
            $tmp = download_url($img, 30);
            if (! is_wp_error($tmp)) {
                $file_array = array('name' => basename(wp_parse_url($img, PHP_URL_PATH)), 'tmp_name' => $tmp);
                $att = media_handle_sideload($file_array, $pid, $box['title']);
                if (! is_wp_error($att)) {
                    set_post_thumbnail($pid, $att);
                    echo "  image: " . basename(wp_parse_url($img, PHP_URL_PATH)) . "\n";
                } else {
                    @unlink($tmp);
                    echo "  ! image sideload failed: " . $att->get_error_message() . "\n";
                }
            }
        } else {
            echo "  ! no image found on live page\n";
        }
    }

    echo "  done — product #$pid (status: $status)\n";
}

echo "\nDone.\n";
