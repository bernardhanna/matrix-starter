<?php
/**
 * Related posts — PACE blog card design (matches news listing).
 *
 * @package Matrix_Starter
 */

if (!defined('ABSPATH')) {
    exit;
}

$args = wp_parse_args($args ?? [], [
    'heading' => __('Related posts', 'matrix-starter'),
    'limit'   => 3,
    'orderby' => 'date',
    'order'   => 'DESC',
]);

$heading = is_string($args['heading']) ? $args['heading'] : __('Related posts', 'matrix-starter');
$limit   = max(1, (int) $args['limit']);
$orderby = in_array($args['orderby'], ['date', 'modified', 'rand', 'title'], true) ? $args['orderby'] : 'date';
$order   = strtoupper((string) $args['order']) === 'ASC' ? 'ASC' : 'DESC';

$current_id = get_the_ID();
if (!$current_id) {
    return;
}

$cat_ids = [];
$cats    = get_the_terms($current_id, 'category');
if (!empty($cats) && !is_wp_error($cats)) {
    foreach ($cats as $c) {
        $cat_ids[] = (int) $c->term_id;
    }
}

$q_args = [
    'post_type'           => 'post',
    'posts_per_page'      => $limit,
    'post_status'         => 'publish',
    'orderby'             => $orderby,
    'order'               => $order,
    'post__not_in'        => [$current_id],
    'ignore_sticky_posts' => true,
];

if ($cat_ids !== []) {
    $q_args['category__in'] = $cat_ids;
}

$q = new WP_Query($q_args);

if (!$q->have_posts() && $cat_ids !== []) {
    unset($q_args['category__in']);
    $q = new WP_Query($q_args);
}

if (!$q->have_posts()) {
    return;
}

$read_more_label = function_exists('matrix_pace_blog_settings')
    ? (string) matrix_pace_blog_settings()['read_more_label']
    : __('Read more →', 'matrix-starter');

$section_id = 'related-posts-' . wp_generate_uuid4();
?>
<section
    id="<?php echo esc_attr($section_id); ?>"
    class="pace-related-posts bg-[#f1f5f8]"
    aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
>
    <div class="mx-auto max-w-[1280px] px-5 py-12 md:px-10 md:py-16 lg:py-20">
        <?php if ($heading !== '') : ?>
            <h2
                id="<?php echo esc_attr($section_id); ?>-heading"
                class="font-montserrat text-[28px] font-bold leading-[35px] text-[#003b65] md:text-[32px] md:leading-[40px]"
            >
                <?php echo esc_html($heading); ?>
            </h2>
        <?php endif; ?>

        <div
            class="pace-related-posts__grid mt-8 grid grid-cols-1 gap-6 md:mt-10 md:grid-cols-2 lg:grid-cols-3"
            role="list"
        >
            <?php
            while ($q->have_posts()) :
                $q->the_post();
                echo '<div role="listitem">';
                get_template_part('template-parts/blog/partials/card', 'standard', [
                    'post_id'         => get_the_ID(),
                    'read_more_label' => $read_more_label,
                ]);
                echo '</div>';
            endwhile;
            wp_reset_postdata();
            ?>
        </div>
    </div>
</section>
