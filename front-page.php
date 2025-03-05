<?php
get_header();
?>
<main class="w-full overflow-hidden site-main">
    <?php load_hero_templates(); ?>


    <?php get_template_part('template-parts/header/breadcrumbs'); ?>
    <?php
    if (have_posts()) :
        while (have_posts()) : the_post();
            if (trim(get_the_content()) != '') : ?>
                <div class="px-4 mx-auto max-w-container">
                    <?php
                    get_template_part('template-parts/content/content', 'page');
                    ?>
                </div>
    <?php endif;
        endwhile;
    else :
        echo '<p>No content found</p>';
    endif;
    ?>

    <?php load_flexible_content_templates(); ?>
</main>

<?php
get_footer();
?>