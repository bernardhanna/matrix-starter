<?php
/**
 * Resource pathway archive — hero tabs + card grid (Figma 4:554 / 4:718).
 *
 * Expects $args['pathway_slug'] or detects from query.
 *
 * @package Matrix_Starter
 */

if (!defined('ABSPATH')) {
    exit;
}

$pathway_slug = '';
if (!empty($args['pathway_slug'])) {
    $pathway_slug = (string) $args['pathway_slug'];
} else {
    $pathway_slug = matrix_pace_resource_active_pathway_slug();
}

if ($pathway_slug === '') {
    return;
}

$active_slug = $pathway_slug;
$pathways    = matrix_pace_resource_pathways();
$query_args  = matrix_pace_resource_archive_query_args($pathway_slug);
$resource_query = new WP_Query($query_args);
?>
<section
    class="pace-resource-archive bg-white font-montserrat"
    aria-label="<?php esc_attr_e('Resources', 'matrix-starter'); ?>"
    data-disable-nav-offset="true"
>
    <?php
    get_template_part('template-parts/hero/subhero', null, matrix_pace_resource_archive_subhero_args($pathway_slug));
    ?>

    <div class="mx-auto w-full max-w-[1280px] px-5 pb-12 pt-6 lg:px-10 lg:pb-16 lg:pt-8 xl:px-[120px]">
        <nav
            class="pace-resource-tabs mb-8 flex flex-wrap gap-2 lg:mb-10"
            aria-label="<?php esc_attr_e('Resource pathways', 'matrix-starter'); ?>"
        >
            <?php foreach ($pathways as $slug => $pathway) :
                $tab_url   = matrix_pace_resource_pathway_url($slug);
                $is_active = $active_slug === $slug;
                ?>
                <a
                    href="<?php echo esc_url($tab_url); ?>"
                    class="<?php echo $is_active
                        ? 'border-[#0a60a0] bg-[#0a60a0] font-semibold text-white'
                        : 'border-[#e1e7ec] bg-white text-[#003b65] hover:border-[#0a60a0]'; ?> inline-flex rounded-full border px-5 py-[11px] text-[14px] leading-[21px] transition"
                    <?php echo $is_active ? 'aria-current="page"' : ''; ?>
                >
                    <?php echo esc_html((string) $pathway['tab_label']); ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <?php if ($resource_query->have_posts()) : ?>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-4">
                <?php
                while ($resource_query->have_posts()) :
                    $resource_query->the_post();
                    get_template_part('template-parts/resources/partials/card', null, [
                        'post_id' => get_the_ID(),
                    ]);
                endwhile;
                ?>
            </div>

            <?php
            matrix_pace_pagination([
                'total'      => (int) $resource_query->max_num_pages,
                'current'    => max(1, (int) get_query_var('paged'), (int) get_query_var('page')),
                'aria_label' => __('Resources pagination', 'matrix-starter'),
            ]);
            ?>
        <?php else : ?>
            <p class="font-comfortaa text-[16px] text-[#5c6b78]"><?php esc_html_e('No resources in this pathway yet.', 'matrix-starter'); ?></p>
        <?php endif; ?>

        <?php wp_reset_postdata(); ?>
    </div>
</section>
