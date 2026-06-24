<?php
/**
 * Template Name: Flexi (legacy layouts)
 * Template Post Type: page
 *
 * Renders flexible_content / flexible_content_blocks using ported legacy layout templates.
 */

get_header();
?>
<main id="main-content" class="overflow-hidden w-full site-main">
    <?php
    if (function_exists('load_hero_templates')) {
        load_hero_templates();
    }

    while (have_posts()) :
        the_post();
        if (trim(get_the_content()) !== '') :
            ?>
            <div class="<?php echo esc_attr(function_exists('matrix_content_container_classes') ? matrix_content_container_classes() : 'container mx-auto'); ?>">
                <?php get_template_part('template-parts/content/content', 'page'); ?>
            </div>
            <?php
        endif;
    endwhile;

    load_flexible_content_templates();
    ?>
</main>
<?php
get_footer();
