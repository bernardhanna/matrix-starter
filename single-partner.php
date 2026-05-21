<?php
/**
 * Single Partner template (Figma 5:3115 / 5:3298).
 */

get_header();

$enable_breadcrumbs = get_field('enable_breadcrumbs', 'option');
?>
<main id="main-content" class="site-main w-full overflow-hidden bg-white">
    <?php
    if ($enable_breadcrumbs !== false) {
        get_template_part('template-parts/header/breadcrumbs');
    }

    if (have_posts()) :
        while (have_posts()) :
            the_post();
            get_template_part('template-parts/single/partner');
        endwhile;
    endif;
    ?>
</main>
<?php
get_footer();
