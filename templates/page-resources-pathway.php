<?php
/**
 * Template Name: Resources pathway archive
 * Pathway listing for For Youth Workers / For Youth / Public Deliverables (Figma 4:554).
 *
 * @package Matrix_Starter
 */

get_header();

$slug = (string) get_post_field('post_name', get_queried_object_id());
?>
<main id="main-content" class="site-main w-full overflow-hidden">
    <?php get_template_part('template-parts/resources/archive-listing', null, ['pathway_slug' => $slug]); ?>
</main>
<?php
get_footer();
