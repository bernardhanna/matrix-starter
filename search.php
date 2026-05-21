<?php
/**
 * Search results — PACE layout (navy subhero + blog-style listing).
 *
 * @package Matrix_Starter
 */

get_header();

$search_term    = trim((string) get_search_query());
$pdf_results    = matrix_pace_search_pdf_results($search_term);
$read_more      = matrix_pace_blog_settings()['read_more_label'] ?? __('Read more →', 'matrix-starter');
?>

<main id="main-content" class="site-main font-montserrat">
    <?php
    get_template_part(
        'template-parts/hero/subhero',
        null,
        matrix_pace_search_subhero_args($search_term)
    );
    ?>

    <section
        class="pace-search-listing bg-[#f1f5f8]"
        aria-label="<?php esc_attr_e('Search results', 'matrix-starter'); ?>"
        data-disable-nav-offset="true"
    >
        <div class="mx-auto w-full max-w-[1280px] px-5 pb-16 pt-8 md:px-10 md:pb-24 md:pt-12">
            <div class="mb-8 flex flex-col gap-4 lg:mb-10 lg:flex-row lg:items-end lg:justify-between lg:gap-6">
                <form
                    method="get"
                    action="<?php echo esc_url(home_url('/')); ?>"
                    class="pace-search-form search-form flex w-full min-w-0 max-w-none flex-col gap-3 lg:max-w-[560px] lg:flex-row lg:items-stretch lg:gap-3"
                    role="search"
                    aria-label="<?php esc_attr_e('Search site content', 'matrix-starter'); ?>"
                >
                    <label for="pace-search-input" class="sr-only">
                        <?php esc_html_e('Search keyword', 'matrix-starter'); ?>
                    </label>
                    <input
                        id="pace-search-input"
                        type="search"
                        name="s"
                        value="<?php echo esc_attr($search_term); ?>"
                        placeholder="<?php esc_attr_e('Search news, resources, events…', 'matrix-starter'); ?>"
                        class="pace-search-form__input box-border block h-[52px] min-h-[52px] w-full max-w-none rounded-full border border-[#e1e7ec] bg-white px-5 font-comfortaa text-[16px] leading-6 text-[#003b65] placeholder:text-[#5c6b78] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0a60a0] focus-visible:ring-offset-2 lg:min-w-0 lg:flex-1"
                        autocomplete="off"
                        enterkeyhint="search"
                    />
                    <button
                        type="submit"
                        class="<?php echo esc_attr(matrix_pace_btn_classes('primary')); ?> no-form-btn-style box-border w-full min-h-[52px] shrink-0 lg:w-auto lg:min-w-[140px]"
                    >
                        <?php esc_html_e('Search', 'matrix-starter'); ?>
                    </button>
                </form>

                <p class="w-full font-comfortaa text-[16px] leading-6 text-[#5c6b78] lg:shrink-0 lg:w-auto">
                    <?php
                    global $wp_query;
                    $total_results = (int) $wp_query->found_posts + count($pdf_results);
                    echo esc_html(
                        sprintf(
                            /* translators: 1: number of results, 2: optional empty (plural suffix handled below) */
                            _n('%d result', '%d results', $total_results, 'matrix-starter'),
                            $total_results
                        )
                    );
                    ?>
                </p>
            </div>

            <?php
            $grouped_results = [];
            if (have_posts()) {
                while (have_posts()) {
                    the_post();
                    $post_type = get_post_type() ?: 'post';
                    $grouped_results[$post_type][] = get_post();
                }
                wp_reset_postdata();
            }
            if ($pdf_results !== []) {
                $grouped_results['attachment_pdf'] = $pdf_results;
            }
            ?>

            <?php if ($grouped_results !== []) : ?>
                <div class="space-y-12 md:space-y-14">
                    <?php foreach ($grouped_results as $post_type => $results) : ?>
                        <?php $group_heading = matrix_pace_search_post_type_label($post_type); ?>
                        <section aria-label="<?php echo esc_attr($group_heading); ?>">
                            <h2 class="mb-6 font-montserrat text-[24px] font-bold leading-[28.8px] text-[#003b65] md:text-[28px] md:leading-[33.6px]">
                                <?php echo esc_html($group_heading); ?>
                            </h2>

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <?php foreach ($results as $result_post) :
                                    if (!$result_post instanceof WP_Post) {
                                        continue;
                                    }

                                    $result_type = get_post_type($result_post);
                                    $use_blog_card = in_array($result_type, ['post'], true);

                                    if ($use_blog_card) :
                                        get_template_part('template-parts/blog/partials/card', 'standard', [
                                            'post_id'         => (int) $result_post->ID,
                                            'read_more_label' => $read_more,
                                        ]);
                                        continue;
                                    endif;

                                    $result_url = $result_type === 'attachment'
                                        ? (string) wp_get_attachment_url((int) $result_post->ID)
                                        : (string) get_permalink($result_post);
                                    if ($result_url === '') {
                                        continue;
                                    }

                                    $result_title   = matrix_pace_search_result_title($result_post);
                                    $result_excerpt = matrix_pace_search_result_excerpt($result_post);
                                    $result_date    = get_the_date('j F Y', $result_post);
                                    $type_label     = matrix_pace_search_post_type_label($result_type);
                                    ?>
                                    <article class="pace-search-card group flex flex-col gap-3 rounded-2xl border border-[#ecf0f4] bg-white p-[25px]">
                                        <div class="flex flex-wrap items-center gap-2.5 font-comfortaa text-[12px] leading-[18px] text-[#757575]">
                                            <?php if ($result_date !== '') : ?>
                                                <time datetime="<?php echo esc_attr(get_the_date('c', $result_post)); ?>">
                                                    <?php echo esc_html($result_date); ?>
                                                </time>
                                                <span aria-hidden="true">·</span>
                                            <?php endif; ?>
                                            <span><?php echo esc_html($type_label); ?></span>
                                        </div>
                                        <h3 class="font-montserrat text-[22px] font-bold leading-[27.5px] text-[#003b65]">
                                            <a
                                                href="<?php echo esc_url($result_url); ?>"
                                                class="hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0a60a0]"
                                                <?php echo $result_type === 'attachment' ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>
                                            >
                                                <?php echo esc_html($result_title); ?>
                                            </a>
                                        </h3>
                                        <?php if ($result_excerpt !== '') : ?>
                                            <p class="font-comfortaa text-[15px] leading-[23.25px] text-[#5c6b78]">
                                                <?php echo esc_html($result_excerpt); ?>
                                            </p>
                                        <?php endif; ?>
                                        <p class="font-comfortaa text-[16px] leading-6 text-[#1d1d1d]">
                                            <a
                                                href="<?php echo esc_url($result_url); ?>"
                                                class="underline decoration-solid underline-offset-2 group-hover:text-[#0a60a0]"
                                                <?php echo $result_type === 'attachment' ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>
                                            >
                                                <?php echo esc_html($read_more); ?>
                                            </a>
                                        </p>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endforeach; ?>
                </div>

                <?php
                matrix_pace_pagination([
                    'total'      => (int) $wp_query->max_num_pages,
                    'current'    => max(1, (int) get_query_var('paged'), (int) get_query_var('page')),
                    'aria_label' => __('Search results pagination', 'matrix-starter'),
                ]);
                ?>
            <?php else : ?>
                <div class="rounded-2xl border border-[#ecf0f4] bg-white px-6 py-12 text-center md:px-10">
                    <h2 class="mb-3 font-montserrat text-[22px] font-bold leading-[27.5px] text-[#003b65]">
                        <?php esc_html_e('No results found', 'matrix-starter'); ?>
                    </h2>
                    <p class="font-comfortaa text-[16px] leading-6 text-[#5c6b78]">
                        <?php esc_html_e('Try a different keyword or a shorter phrase.', 'matrix-starter'); ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php
get_footer();
