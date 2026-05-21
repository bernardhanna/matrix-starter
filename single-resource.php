<?php
/**
 * Single resource template.
 *
 * @package Matrix_Starter
 */

get_header();
?>
<main id="main-content" class="site-main w-full overflow-hidden bg-white font-montserrat">
    <?php
    while (have_posts()) :
        the_post();
        $post_id = get_the_ID();
        get_template_part(
            'template-parts/hero/subhero',
            null,
            matrix_pace_resource_single_subhero_args($post_id)
        );
        ?>
        <div class="<?php echo esc_attr(matrix_pace_content_container_classes()); ?>">
            <article class="<?php echo esc_attr(matrix_pace_content_article_classes()); ?>">
                <div class="entry-content">
                    <?php the_content(); ?>
                </div>
            </article>
        </div>
    <?php endwhile; ?>
</main>
<?php
get_footer();
