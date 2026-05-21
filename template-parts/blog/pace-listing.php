<?php
/**
 * PACE blog index / category listing (Figma 3:1233 / 3:1412).
 *
 * @package Matrix_Starter
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings          = matrix_pace_blog_settings();
$read_more_label   = (string) $settings['read_more_label'];
$featured_label    = (string) $settings['featured_label'];
$active_slug       = matrix_pace_blog_active_tab_slug();
$show_featured     = is_home() && !is_category();
$featured_id       = $show_featured ? matrix_pace_get_featured_post_id() : 0;
$query_args        = matrix_pace_blog_query_args();
$blog_query        = new WP_Query($query_args);
$categories        = matrix_pace_blog_tab_categories();
$posts_page_id     = (int) get_option('page_for_posts');
$posts_url         = $posts_page_id ? (string) get_permalink($posts_page_id) : home_url('/');
?>
<section class="pace-blog-listing bg-[#f1f5f8]" aria-label="<?php esc_attr_e('News listing', 'matrix-starter'); ?>">
    <div class="mx-auto max-w-[1280px] px-5 pb-16 pt-8 md:px-10 md:pb-24 md:pt-16">
        <?php if (!empty($categories)) : ?>
            <nav class="pace-blog-tabs mb-8 flex flex-wrap gap-x-8 gap-y-3 font-comfortaa text-[16px] leading-6 text-[#0a60a0] md:mb-10 md:gap-x-[33px]" aria-label="<?php esc_attr_e('News categories', 'matrix-starter'); ?>">
                <?php foreach ($categories as $term) :
                    $is_active = $active_slug === $term->slug;
                    $tab_url   = matrix_pace_blog_tab_url($term->slug);
                    ?>
                    <a
                        href="<?php echo esc_url($tab_url); ?>"
                        class="<?php echo $is_active ? 'font-semibold underline decoration-[#0a60a0] underline-offset-4' : 'underline decoration-[#0a60a0] underline-offset-4 hover:opacity-80'; ?>"
                        <?php echo $is_active ? 'aria-current="page"' : ''; ?>
                    >
                        <?php echo esc_html($term->name); ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>

        <?php if ($featured_id > 0) : ?>
            <div class="mb-6 md:mb-8">
                <?php
                setup_postdata($featured_id);
                get_template_part('template-parts/blog/partials/card', 'featured', [
                    'post_id'         => $featured_id,
                    'read_more_label' => $read_more_label,
                    'featured_label'  => $featured_label,
                ]);
                wp_reset_postdata();
                ?>
            </div>
        <?php endif; ?>

        <?php if ($blog_query->have_posts()) : ?>
            <div class="pace-blog-grid mt-6 grid grid-cols-1 gap-6 md:mt-8 md:grid-cols-2 md:gap-6">
                <?php
                while ($blog_query->have_posts()) :
                    $blog_query->the_post();
                    get_template_part('template-parts/blog/partials/card', 'standard', [
                        'post_id'         => get_the_ID(),
                        'read_more_label' => $read_more_label,
                    ]);
                endwhile;
                ?>
            </div>

            <?php
            matrix_pace_pagination([
                'total'      => (int) $blog_query->max_num_pages,
                'current'    => max(1, (int) get_query_var('paged'), (int) get_query_var('page')),
                'aria_label' => __('Posts pagination', 'matrix-starter'),
            ]);
            ?>
        <?php elseif ($featured_id === 0) : ?>
            <p class="font-comfortaa text-[16px] text-[#5c6b78]"><?php esc_html_e('No posts found.', 'matrix-starter'); ?></p>
        <?php endif; ?>

        <?php wp_reset_postdata(); ?>
    </div>
</section>
