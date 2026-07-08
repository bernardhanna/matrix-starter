<?php
/**
 * Reusable Rolling Donut sections (our story, etc.).
 */

/**
 * Full-bleed section shell — background spans the viewport; do not put max-width here.
 */
function matrix_rd_section_shell_classes(string $extra = ''): string
{
    return trim('w-full ' . $extra);
}

/**
 * Centered content column — matches footer/newsletter width (~1549px).
 */
function matrix_rd_section_inner_classes(string $extra = ''): string
{
    return trim('mx-auto w-full max-w-max-1549 px-4 lg:px-8 ' . $extra);
}

/**
 * Render our-story section for a given page (home = front page ID).
 */
function matrix_rd_render_our_story(?int $post_id = null): void {
    if ($post_id === null) {
        $post_id = get_queried_object_id() ?: (int) get_option('page_on_front');
    }

    $stories = matrix_rd_acf_repeater_rows(
        'stories',
        ['title', 'span_one', 'span_two', 'description', 'image', 'image_mobile', 'donut_img', 'timeline_text', 'timeline_button'],
        $post_id
    );

    if ($stories === []) {
        return;
    }

    set_query_var('matrix_rd_story_post_id', $post_id);
    set_query_var('matrix_rd_stories', $stories);
    get_template_part('template-parts/pages/our-story');
}
