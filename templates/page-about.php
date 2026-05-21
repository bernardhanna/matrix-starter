<?php
/**
 * Template Name: About page
 *
 * PACE About us — Figma 3:567 (desktop), 3:732 (mobile).
 *
 * @package Matrix_Starter
 */

get_header();
?>
<main id="main-content" class="overflow-hidden w-full site-main">
    <?php load_hero_templates(); ?>


    <?php
    if (have_posts()) :
        while (have_posts()) :
            the_post();
            get_template_part('template-parts/page/about');
        endwhile;
    endif;
    ?>
</main>
<?php
get_footer();
