<?php
// Theme setup
if (! function_exists('get_field') && ! is_admin()) {
    wp_die(
        'The ACF plugin is not active. This theme depends on it. Please activate it.',
        'Plugin Missing',
        array('response' => 500)
    );
}

function matrix_starter_setup()
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
    add_theme_support('automatic-feed-links');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));
    add_theme_support('align-wide');
    add_theme_support('editor-styles');
    add_theme_support('responsive-embeds');
    add_image_size('hero-small', 768, 500, true);
    add_image_size('hero-medium', 1024, 600, true);
    add_image_size('hero-large', 1280, 800, true);
    add_image_size('hero-xlarge', 1600, 900, true);
    add_image_size('hero-xxlarge', 1920, 1080, true);
    register_nav_menus(array(
        'primary' => esc_html__('Primary Menu', 'matrix-starter'),
        'Footer One' => esc_html__('Footer One Menu', 'matrix-starter'),
        'Footer Two' => esc_html__('Footer Two Menu', 'matrix-starter'),
        'Footer Three' => esc_html__('Footer Three Menu', 'matrix-starter'),
        'copyright' => esc_html__('Copyright Menu', 'matrix-starter'),
    ));
}
add_action('after_setup_theme', 'matrix_starter_setup');

// Temporary filter for footer menu
add_filter('nav_menu_link_attributes', function ($atts, $item, $args) {
    if ($args->theme_location === 'Footer One') {
        $atts['class'] = trim(($atts['class'] ?? '') . ' block hover:underline focus:outline-none focus:ring-2 focus:ring-slate-900');
    }
    return $atts;
}, 10, 3);

// Include the Enqueue Fonts
require_once get_template_directory() . '/inc/enqueue-fonts.php';
// Include the Enqueue Scripts and Styles
require_once get_template_directory() . '/inc/enqueue-scripts.php';
// load the helper functions
require_once get_template_directory() . '/inc/hero-functions.php';
require_once get_template_directory() . '/inc/flexible-content-functions.php';

// Function to handle Tailwind config updates and trigger rebuilds
function handle_tailwind_config_update()
{
    // Update the CSS version to force cache refresh
    update_option('theme_css_version', time());

    // Clear any WordPress caches
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }

    // Touch the CSS file to trigger rebuild
    $css_path = get_template_directory() . '/assets/css/app.css';
    if (file_exists($css_path)) {
        // Touch the main CSS file
        touch($css_path);

        // Create and remove a temporary file to trigger file system events
        $temp_path = get_template_directory() . '/assets/css/.temp';
        file_put_contents($temp_path, '');
        usleep(100000); // Wait 100ms
        if (file_exists($temp_path)) {
            unlink($temp_path);
        }

        // Log successful update for debugging
        error_log('Tailwind config updated and rebuild triggered');
    } else {
        error_log('CSS file not found at: ' . $css_path);
    }
}

// Hook to handle Tailwind updates when ACF options are saved
add_action('acf/save_post', function ($post_id) {
    if ($post_id === 'options') {
        handle_tailwind_config_update();
    }
}, 30);

// Autoload Composer dependencies
if (file_exists(get_template_directory() . '/vendor/autoload.php')) {
    require_once get_template_directory() . '/vendor/autoload.php';
} else {
    // Handle error or provide a fallback
    error_log('Composer autoload file not found.');
}

// Autoload ACF fields
require_once get_template_directory() . '/inc/autoload-acf-fields.php';

// Autoload Custom Post Types and Taxonomies
require_once get_template_directory() . '/inc/cpts/init.php';

// Include the ACF theme options setup
require_once get_template_directory() . '/inc/theme-options.php';

// Include login customizations
require_once get_template_directory() . '/inc/login-customizations.php';

// Include the pagination functions
require_once get_template_directory() . '/inc/pagination.php';

//Include WooCommerce
require_once get_template_directory() . '/inc/woocommerce.php';

// Customize excerpt more text
function custom_excerpt_more($more)
{
    return '';  // Return an empty string to remove the ellipsis
}
add_filter('excerpt_more', 'custom_excerpt_more');

// Add custom image sizes in your theme
function my_custom_image_sizes()
{
    add_image_size('hero-image', 1600, 900, true); // Example custom image size
    add_image_size('hero-thumbnail', 800, 450, true); // Example thumbnail for smaller screens
}
add_action('after_setup_theme', 'my_custom_image_sizes');

// Ensure the sizes are visible in the srcset
function my_custom_image_size_names($sizes)
{
    return array_merge($sizes, array(
        'hero-image' => __('Hero Image'),
        'hero-thumbnail' => __('Hero Thumbnail'),
    ));
}
add_filter('image_size_names_choose', 'my_custom_image_size_names');


// Allow SVG in Custom Logo
function add_svg_support($file_types)
{
    $new_filetypes = array();
    $new_filetypes['svg'] = 'image/svg+xml';
    $file_types = array_merge($file_types, $new_filetypes);
    return $file_types;
}
add_filter('upload_mimes', 'add_svg_support');

// Additional security check for SVG files
function svg_mime_check($data, $file, $filename)
{
    if (isset($data['ext']) && $data['ext'] === 'svg') {
        if ($data['type'] !== 'image/svg+xml') {
            $data['ext'] = $data['type'] = false;
        }
    }
    return $data;
}
add_filter('wp_check_filetype_and_ext', 'svg_mime_check', 10, 3);

// Ensure Tailwind classes are processed for Contact Form 7
function add_type_attribute($tag, $handle, $src)
{
    // Add style type for CF7 form styles
    if ('contact-form-7' === $handle) {
        $tag = str_replace("rel='stylesheet'", "rel='stylesheet' type='text/css'", $tag);
    }
    return $tag;
}
add_filter('style_loader_tag', 'add_type_attribute', 10, 3);


// 404 page
function template_part_404()
{
    // Scan the template-parts/404 directory for files
    $template_dir = get_template_directory() . '/template-parts/404';
    $files = scandir($template_dir);

    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            get_template_part('template-parts/404/' . pathinfo($file, PATHINFO_FILENAME));
            return;
        }
    }
}

//Load Blog
function template_part_blog()
{
    $template_dir = get_template_directory() . '/template-parts/blog/';
    $files = scandir($template_dir);

    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            get_template_part('template-parts/blog/' . pathinfo($file, PATHINFO_FILENAME));
            return;
        }
    }
}

//TEMPLATE FORMS
require get_template_directory() . '/inc/forms/class-theme-forms.php';
new Theme_Forms();

//TEMPLATE BUILDERS
add_action('init', function () {
    require_once get_template_directory() . '/inc/template-builder/default-builder.php';
}, 20);

add_action( 'wp_footer', function () {
    if ( class_exists( 'Theme_Forms' ) ) {
        echo '<!-- Theme_Forms loaded -->';
    }
} );

wp_enqueue_script('jquery-ui-draggable');
/**
 * Fill the choices with every top-level item in the “primary” menu.
 */
add_filter('acf/load_field/name=menu_item', function ($field) {
    $field['choices'] = [];

    $locations = get_nav_menu_locations();
    if (isset($locations['primary'])) {
        $menu_obj   = wp_get_nav_menu_object($locations['primary']);
        $menu_items = wp_get_nav_menu_items($menu_obj->term_id);
        foreach ($menu_items as $mi) {
            if ((int) $mi->menu_item_parent === 0) {
                $field['choices'][$mi->ID] = $mi->title;
            }
        }
    }
    return $field;
});

