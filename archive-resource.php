<?php
/**
 * Resource post type archive — redirect to Resources Hub or first pathway.
 */

$hub = get_page_by_path('resources-hub', OBJECT, 'page');
if ($hub instanceof WP_Post) {
    wp_safe_redirect(get_permalink($hub), 302);
    exit;
}

get_header();
?>
<main id="main-content" class="site-main w-full overflow-hidden">
    <?php
    $slug = 'for-youth-workers';
    get_template_part('template-parts/resources/archive-listing', null, ['pathway_slug' => $slug]);
    ?>
</main>
<?php
get_footer();
