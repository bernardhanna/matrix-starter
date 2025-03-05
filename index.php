<?php
get_header();

// Ensure hero section always loads first (if homepage
$blog_page_id = get_option('page_for_posts');

// Load hero templates in the header area
if (is_home() && $blog_page_id) {
    load_hero_templates($blog_page_id);
} elseif (is_category()) {
    $category_id = get_queried_object_id();

    // Check if the category has a custom hero layout
    if (have_rows('hero_flexible_content', 'category_' . $category_id)) {
        echo '<div class="w-full">';
        load_hero_templates('category_' . $category_id);
        echo '</div>';
    } else {
        // Fallback: Load the blog page hero if no category-specific hero exists
        if ($blog_page_id) {
            echo '<div class="w-full">';
            load_hero_templates($blog_page_id);
            echo '</div>';
        }
    }
}
?>

<main class="w-full min-h-screen overflow-hidden site-main">
    <?php template_part_blog(); ?>
</main>

<?php
if ($blog_page_id) {
    echo '<div class="w-full">';
    load_flexible_content_templates($blog_page_id);
    echo '</div>';
}
?>

<?php get_footer(); ?>