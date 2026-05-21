<?php
/**
 * Singular template — PACE subhero + pace-prose body.
 *
 * @package Matrix_Starter
 */

get_header();
?>
<main id="main-content" class="site-main w-full overflow-hidden bg-white font-montserrat">
<?php if (have_posts()) : ?>
    <?php while (have_posts()) : the_post(); ?>
        <?php
        $post_id   = get_the_ID();
        $post_type = get_post_type($post_id);

        if ($post_type === 'post') {
            $subhero_args = matrix_pace_post_single_subhero_args($post_id);
        } elseif ($post_type === 'resource') {
            $subhero_args = matrix_pace_resource_single_subhero_args($post_id);
        } else {
            $post_type_obj = get_post_type_object($post_type);
            $archive_label = ($post_type_obj && !empty($post_type_obj->labels->name))
                ? (string) $post_type_obj->labels->name
                : 'Archive';
            $archive_url = get_post_type_archive_link($post_type) ?: home_url('/');
            $excerpt     = trim((string) get_the_excerpt($post_id));

            $subhero_args = matrix_pace_singular_subhero_args([
                'kicker'      => strtoupper($archive_label),
                'title'       => (string) get_the_title($post_id),
                'description' => $excerpt !== '' ? '<p>' . esc_html($excerpt) . '</p>' : '',
                'breadcrumbs' => [
                    [
                        'title'      => __('Home', 'matrix-starter'),
                        'url'        => home_url('/'),
                        'is_current' => false,
                    ],
                    [
                        'title'      => $archive_label,
                        'url'        => (string) $archive_url,
                        'is_current' => false,
                    ],
                    [
                        'title'      => (string) get_the_title($post_id),
                        'url'        => '',
                        'is_current' => true,
                    ],
                ],
            ]);
        }

        get_template_part('template-parts/hero/subhero', null, $subhero_args);

        $share_urls = matrix_pace_post_share_urls($post_id);
        ?>

        <div class="<?php echo esc_attr(matrix_pace_content_container_classes()); ?>">
            <div class="mx-auto w-full max-w-[720px]">
                <?php
                get_template_part('template-parts/single/share', null, [
                    'urls'     => $share_urls,
                    'position' => 'top',
                ]);
                ?>

                <article class="<?php echo esc_attr(matrix_pace_content_article_classes()); ?>">
                    <div class="entry-content">
                        <?php the_content(); ?>
                    </div>
                </article>

                <?php
                get_template_part('template-parts/single/share', null, [
                    'urls'     => $share_urls,
                    'position' => 'bottom',
                ]);
                ?>
            </div>
        </div>

        <?php if ($post_type === 'post') : ?>
            <?php get_template_part('template-parts/single/related-posts'); ?>
            <?php
            $newsletter = locate_template('template-parts/flexi/newsletter_001.php');
            if ($newsletter !== '') {
                get_template_part('template-parts/flexi/newsletter_001');
            }
            ?>
        <?php endif; ?>

    <?php endwhile; ?>
<?php else : ?>
    <section class="py-16">
        <div class="mx-auto w-full max-w-[1280px] px-5 text-center font-comfortaa text-[18px] text-[#1d1d1d]">
            <p><?php esc_html_e('No content found.', 'matrix-starter'); ?></p>
        </div>
    </section>
<?php endif; ?>
</main>
<?php
get_footer();
