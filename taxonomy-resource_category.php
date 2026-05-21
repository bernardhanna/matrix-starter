<?php
/**
 * Resource category archive — PACE pathway listing (Figma 4:554 / 4:718).
 */

get_header();

$term = get_queried_object();
$slug = $term instanceof WP_Term ? (string) $term->slug : '';
?>
<main id="main-content" class="site-main w-full overflow-hidden">
    <?php get_template_part('template-parts/resources/archive-listing', null, ['pathway_slug' => $slug]); ?>
</main>
<?php
get_footer();
