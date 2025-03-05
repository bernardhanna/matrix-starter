<?php
get_header();
?>
<main class="w-full min-h-screen overflow-hidden site-main">
    <?php get_template_part('template-parts/single/hero'); ?>

    <?php
    if (function_exists('load_hero_templates')) {
        load_hero_templates();
    }
    ?>

    <?php
    $enable_breadcrumbs = get_field('enable_breadcrumbs', 'option');

    if ($enable_breadcrumbs !== false) :
        get_template_part('template-parts/header/breadcrumbs');
    endif;
    ?>

    <?php
    if (have_posts()) :
        while (have_posts()) : the_post();
            if (trim(get_the_content()) != '') : ?>
                <div class="max-w-[1095px] px-4 mx-auto">
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