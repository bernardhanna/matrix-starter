<?php
// Theme setup: fail hard on frontend if ACF missing (but allow admin to activate it)
if (! function_exists('get_field') && ! is_admin()) {
    wp_die(
        'The ACF plugin is not active. This theme depends on it. Please activate it.',
        'Plugin Missing',
        array('response' => 500)
    );
}

/**
 * Core theme supports & menus
 */
function matrix_starter_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
    add_theme_support('automatic-feed-links');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));
    add_theme_support('align-wide');
    add_theme_support('editor-styles');
    add_theme_support('responsive-embeds');

    add_image_size('hero-small',   768,  500, true);
    add_image_size('hero-medium',  1024, 600, true);
    add_image_size('hero-large',   1280, 800, true);
    add_image_size('hero-xlarge',  1600, 900, true);
    add_image_size('hero-xxlarge', 1920,1080, true);

    register_nav_menus(array(
        'primary'       => esc_html__('Primary Menu', 'matrix-starter'),
        'footer_one'    => esc_html__('Footer One Menu', 'matrix-starter'),
        'footer_two'    => esc_html__('Footer Two Menu', 'matrix-starter'),
        'footer_three'  => esc_html__('Footer Three Menu', 'matrix-starter'),
        'footer_four'   => esc_html__('Footer Four Menu', 'matrix-starter'),
        'copyright'     => esc_html__('Copyright Menu', 'matrix-starter'),
    ));
}
add_action('after_setup_theme', 'matrix_starter_setup');

/**
 * Menu link attributes (theme_location is the slug)
 */
add_filter('nav_menu_link_attributes', function ($atts, $item, $args) {
    if (!empty($args->theme_location) && $args->theme_location === 'footer_one') {
        $atts['class'] = trim(($atts['class'] ?? '') . ' block hover:underline focus:outline-none focus:ring-2 focus:ring-slate-900');
    }
    return $atts;
}, 10, 3);

/**
 * Composer (if present)
 */
if (file_exists(get_template_directory() . '/vendor/autoload.php')) {
    require_once get_template_directory() . '/vendor/autoload.php';
} else {
    error_log('Composer autoload file not found.');
}

/**
 * Includes that can load immediately (no ACF calls at include time)
 */
require_once get_template_directory() . '/inc/enqueue-fonts.php';
require_once get_template_directory() . '/inc/enqueue-scripts.php';
require_once get_template_directory() . '/inc/hero-functions.php';
require_once get_template_directory() . '/inc/flexible-content-functions.php';
require_once get_template_directory() . '/inc/helpers/utils/menu-icon.php';

/**
 * CPTs & taxonomies
 * These files typically add their own add_action('init', ...) which must be
 * registered BEFORE WP hits 'init', so require them now.
 */
require_once get_template_directory() . '/inc/cpts/init.php';

/**
 * ACF-dependent files — run them on acf/init (fires at/after init),
 * so ACF translations (textdomain 'acf') won’t be loaded too early.
 */
add_action('acf/init', function () {
    require_once get_template_directory() . '/inc/autoload-acf-fields.php';
    require_once get_template_directory() . '/inc/autoload-acf-groups.php';
    require_once get_template_directory() . '/inc/theme-options.php';
}, 5);

/**
 * Other theme includes
 */
require_once get_template_directory() . '/inc/login-customizations.php';
require_once get_template_directory() . '/inc/pagination.php';
require_once get_template_directory() . '/inc/woocommerce.php';

/**
 * Tailwind cache-busting on ACF options save
 */
function handle_tailwind_config_update() {
    update_option('theme_css_version', time());

    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }

    $css_path = get_template_directory() . '/assets/css/app.css';
    if (file_exists($css_path)) {
        touch($css_path);
        $temp_path = get_template_directory() . '/assets/css/.temp';
        file_put_contents($temp_path, '');
        usleep(100000);
        if (file_exists($temp_path)) {
            unlink($temp_path);
        }
        error_log('Tailwind config updated and rebuild triggered');
    } else {
        error_log('CSS file not found at: ' . $css_path);
    }
}
add_action('acf/save_post', function ($post_id) {
    if ($post_id === 'options') {
        handle_tailwind_config_update();
    }
}, 30);

/**
 * Excerpt more
 */
function custom_excerpt_more($more) {
    return '';
}
add_filter('excerpt_more', 'custom_excerpt_more');

/**
 * Extra image sizes & labels
 */
function my_custom_image_sizes() {
    add_image_size('hero-image',     1600, 900, true);
    add_image_size('hero-thumbnail',  800, 450, true);
}
add_action('after_setup_theme', 'my_custom_image_sizes');

function my_custom_image_size_names($sizes) {
    return array_merge($sizes, array(
        'hero-image'     => __('Hero Image'),
        'hero-thumbnail' => __('Hero Thumbnail'),
    ));
}
add_filter('image_size_names_choose', 'my_custom_image_size_names');

/**
 * SVG support
 */
function add_svg_support($file_types) {
    $file_types['svg'] = 'image/svg+xml';
    return $file_types;
}
add_filter('upload_mimes', 'add_svg_support');

function svg_mime_check($data, $file, $filename) {
    if (isset($data['ext']) && $data['ext'] === 'svg') {
        if ($data['type'] !== 'image/svg+xml') {
            $data['ext'] = $data['type'] = false;
        }
    }
    return $data;
}
add_filter('wp_check_filetype_and_ext', 'svg_mime_check', 10, 3);

/**
 * CF7 style tag tweak
 */
function add_type_attribute($tag, $handle, $src) {
    if ('contact-form-7' === $handle) {
        $tag = str_replace("rel='stylesheet'", "rel='stylesheet' type='text/css'", $tag);
    }
    return $tag;
}
add_filter('style_loader_tag', 'add_type_attribute', 10, 3);

/**
 * 404 template part loader
 */
function template_part_404() {
    $template_dir = get_template_directory() . '/template-parts/404';
    if (!is_dir($template_dir)) {
        return;
    }
    $files = scandir($template_dir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            get_template_part('template-parts/404/' . pathinfo($file, PATHINFO_FILENAME));
            return;
        }
    }
}

/**
 * Blog template part loader
 */
function template_part_blog() {
    $template_dir = get_template_directory() . '/template-parts/blog/';
    if (!is_dir($template_dir)) {
        return;
    }
    $files = scandir($template_dir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            get_template_part('template-parts/blog/' . pathinfo($file, PATHINFO_FILENAME));
            return;
        }
    }
}

/**
 * Forms
 */
require get_template_directory() . '/inc/forms/class-theme-forms.php';
new Theme_Forms();

/**
 * Template builder (your original timing kept)
 */
add_action('init', function () {
    require_once get_template_directory() . '/inc/template-builder/default-builder.php';
}, 20);

/**
 * TEAM POST FIELDS
 */
// Load ACF Team fields (safe include)
add_action('after_setup_theme', function () {
    $path = trailingslashit( get_stylesheet_directory() ) . 'acf-fields/register-team-fields.php';
    if ( file_exists( $path ) ) {
        require_once $path;
    } else {
        // Optional: log instead of fatal error
        error_log('[ACF] Missing file: ' . $path);
    }
});

/**
 * Debug marker to confirm Theme_Forms is loaded
 */
add_action('wp_footer', function () {
    if (class_exists('Theme_Forms')) {
        echo '<!-- Theme_Forms loaded -->';
    }
});

/**
 * Enqueue jQuery UI draggable early
 */
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script('jquery-ui-draggable');
}, 1);

/**
 * Build ACF Select choices from menus
 */
function mytheme_acf_menu_item_choices(): array {
    $cache_key = 'mytheme_nav_menu_item_choices';
    $choices = get_transient($cache_key);
    if (is_array($choices)) {
        return $choices;
    }

    $choices = [];
    $menus = wp_get_nav_menus();

    if (!empty($menus)) {
        foreach ($menus as $menu) {
            $items = wp_get_nav_menu_items($menu->term_id, ['update_post_term_cache' => false]);
            if (empty($items)) {
                continue;
            }

            $by_id = [];
            foreach ($items as $it) {
                $by_id[$it->ID] = $it;
            }

            foreach ($items as $it) {
                $crumbs = [$it->title];
                $p = $it->menu_item_parent;
                while (!empty($p) && isset($by_id[$p])) {
                    array_unshift($crumbs, $by_id[$p]->title);
                    $p = $by_id[$p]->menu_item_parent;
                }
                $label = $menu->name . ' › ' . implode(' › ', $crumbs);
                $choices[$it->ID] = $label;
            }
        }
    }

    set_transient($cache_key, $choices, HOUR_IN_SECONDS * 12);
    return $choices;
}

add_action('wp_update_nav_menu', function () {
    delete_transient('mytheme_nav_menu_item_choices');
});

add_filter('acf/load_field/name=menu_item', function ($field) {
    $field['choices'] = mytheme_acf_menu_item_choices();
    return $field;
});

/* ALLOW VCARDS */
// Allow .vcf uploads site-wide
add_filter('upload_mimes', function ($mimes) {
    // Common vCard MIME types
    $mimes['vcf']   = 'text/x-vcard';
    $mimes['vcard'] = 'text/x-vcard';
    // Some servers report vCards as text/vcard or application/vcard
    $mimes['vct']   = 'text/vcard';
    return $mimes;
});

// Help WP validate .vcf correctly (prevents false negatives)
add_filter('wp_check_filetype_and_ext', function ($wp_check, $file, $filename, $mimes, $real_mime = '' ) {
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    if (in_array(strtolower($ext), ['vcf','vcard'], true)) {
        $wp_check['ext']  = 'vcf';
        // Prefer text/x-vcard; some setups use text/vcard or application/vcard
        $wp_check['type'] = 'text/x-vcard';
        $wp_check['proper_filename'] = $filename;
    }
    return $wp_check;
}, 10, 5);