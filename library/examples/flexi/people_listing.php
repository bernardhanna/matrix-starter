<?php
/**
 * People archive grid using team card design.
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wp_query;
$section_id = 'people-listing-' . wp_generate_uuid4();
?>

<section id="<?php echo esc_attr($section_id); ?>" class="flex overflow-hidden relative bg-white">
    <div class="mx-auto flex w-full max-w-[1120px] flex-col items-center px-5 py-12">
        <?php if (have_posts()) : ?>
            <div class="sr-people-grid grid grid-cols-2 gap-6 w-full md:grid-cols-3 lg:grid-cols-4">
                <?php while (have_posts()) : the_post(); ?>
                    <?php
                    $person_id = get_the_ID();
                    $person_name = get_the_title($person_id);
                    $person_permalink = get_permalink($person_id);

                    $thumb_id = get_post_thumbnail_id($person_id);
                    $thumb_url = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'large') : '';
                    $thumb_alt = $thumb_id ? get_post_meta($thumb_id, '_wp_attachment_image_alt', true) : '';
                    $thumb_title = $thumb_id ? get_the_title($thumb_id) : '';

                    if ($thumb_alt === '') {
                        $thumb_alt = $person_name !== '' ? $person_name : __('Team member photo', 'matrix-starter');
                    }
                    if ($thumb_title === '') {
                        $thumb_title = $person_name !== '' ? $person_name : __('Team member', 'matrix-starter');
                    }

                    $role_text = '';
                    $excerpt = trim((string) get_the_excerpt($person_id));
                    if ($excerpt !== '') {
                        $role_text = $excerpt;
                    } else {
                        $terms = get_the_terms($person_id, 'people_role');
                        if (!is_wp_error($terms) && !empty($terms)) {
                            $role_text = (string) $terms[0]->name;
                        }
                    }
                    ?>
                    <article class="flex flex-col gap-4">
                        <a href="<?php echo esc_url($person_permalink); ?>" class="flex flex-col gap-4 group" aria-label="<?php echo esc_attr(sprintf(__('View profile for %s', 'matrix-starter'), $person_name)); ?>">
                            <div class="sr-person-card__imgwrap w-full overflow-hidden rounded-lg bg-[#e8ebf4]">
                                <?php if ($thumb_url !== '') : ?>
                                    <img
                                        src="<?php echo esc_url($thumb_url); ?>"
                                        alt="<?php echo esc_attr($thumb_alt); ?>"
                                        title="<?php echo esc_attr($thumb_title); ?>"
                                        loading="lazy"
                                        decoding="async"
                                        class="sr-person-card__img w-full h-full rounded-lg"
                                    >
                                <?php else : ?>
                                    <div
                                        class="w-full h-full flex items-center justify-center bg-[#e8ebf4] rounded-lg"
                                        role="img"
                                        aria-label="<?php echo esc_attr($thumb_alt); ?>"
                                    >
                                        <span class="text-sm text-content-body">
                                            <?php echo esc_html($person_name); ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="flex flex-col gap-1">
                                <p class="break-words text-left font-['Public Sans'] text-[1.125rem] font-[700] leading-[1.5rem] text-content-body group-hover:underline">
                                    <?php echo esc_html($person_name); ?>
                                </p>

                                <?php if ($role_text !== '') : ?>
                                    <p class="break-words text-left font-['Public Sans'] text-[0.875rem] font-[400] leading-[1.25rem] text-content-body">
                                        <?php echo esc_html($role_text); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </a>
                    </article>
                <?php endwhile; ?>
            </div>

            <?php if ((int) $wp_query->max_num_pages > 1) : ?>
                <nav class="flex flex-wrap gap-2 justify-center items-center mt-10" aria-label="<?php esc_attr_e('People pagination', 'matrix-starter'); ?>">
                    <?php
                    echo wp_kses_post(paginate_links([
                        'total'     => (int) $wp_query->max_num_pages,
                        'current'   => max(1, get_query_var('paged')),
                        'type'      => 'list',
                        'prev_text' => __('Back', 'matrix-starter'),
                        'next_text' => __('Next', 'matrix-starter'),
                    ]));
                    ?>
                </nav>
            <?php endif; ?>
        <?php else : ?>
            <p class="text-sm text-left text-content-body">
                <?php esc_html_e('No team members found.', 'matrix-starter'); ?>
            </p>
        <?php endif; ?>
    </div>
</section>
