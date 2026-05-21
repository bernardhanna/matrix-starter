<?php
/**
 * 404 — PACE layout (navy subhero + helpful links).
 *
 * @package Matrix_Starter
 */

get_header();

$settings     = matrix_pace_not_found_settings();
$links        = matrix_pace_not_found_helpful_links();
$primary      = $links[0] ?? [
    'url'    => home_url('/'),
    'title'  => __('Return home', 'matrix-starter'),
    'target' => '_self',
];
$extra_links  = array_slice($links, 1);
$body_html    = trim((string) $settings['text']);
$media_markup = matrix_pace_not_found_media_markup();
?>

<main id="main-content" class="site-main font-montserrat">
    <?php
    get_template_part(
        'template-parts/hero/subhero',
        null,
        matrix_pace_not_found_subhero_args()
    );
    ?>

    <section
        class="pace-not-found bg-[#f1f5f8]"
        aria-labelledby="pace-not-found-heading"
        data-disable-nav-offset="true"
    >
        <div class="mx-auto w-full max-w-[1280px] px-5 py-12 md:px-10 md:py-16 lg:py-20">
            <div class="grid grid-cols-1 items-center gap-10 lg:grid-cols-2 lg:gap-14">
                <div class="w-full">
                    <?php echo $media_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper ?>
                </div>

                <article class="flex flex-col gap-6">
                    <div>
                        <h2
                            id="pace-not-found-heading"
                            class="font-montserrat text-[24px] font-bold leading-[28.8px] text-[#003b65] md:text-[28px] md:leading-[33.6px]"
                        >
                            <?php esc_html_e('Let us help you find your way', 'matrix-starter'); ?>
                        </h2>
                        <?php if ($body_html !== '') : ?>
                            <div class="pace-prose mt-4 font-comfortaa text-[16px] leading-[25.6px] text-[#5c6b78]">
                                <?php echo wp_kses_post($body_html); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($extra_links !== []) : ?>
                        <nav aria-label="<?php esc_attr_e('Helpful links', 'matrix-starter'); ?>">
                            <p class="mb-3 font-montserrat text-[14px] font-semibold uppercase tracking-[0.12em] text-[#0a60a0]">
                                <?php esc_html_e('Helpful links', 'matrix-starter'); ?>
                            </p>
                            <ul class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                                <?php foreach ($extra_links as $link) : ?>
                                    <li>
                                        <a
                                            href="<?php echo esc_url($link['url']); ?>"
                                            target="<?php echo esc_attr($link['target']); ?>"
                                            <?php echo $link['target'] === '_blank' ? 'rel="noopener noreferrer"' : ''; ?>
                                            class="<?php echo esc_attr(matrix_pace_btn_classes('secondary-dark')); ?>"
                                        >
                                            <?php echo esc_html($link['title']); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>

                    <div class="pt-2">
                        <a
                            href="<?php echo esc_url($primary['url']); ?>"
                            target="<?php echo esc_attr($primary['target']); ?>"
                            <?php echo $primary['target'] === '_blank' ? 'rel="noopener noreferrer"' : ''; ?>
                            class="<?php echo esc_attr(matrix_pace_btn_classes('primary', ['full_mobile' => true])); ?> w-full md:w-fit"
                        >
                            <?php echo esc_html($primary['title']); ?>
                        </a>
                    </div>

                    <form
                        method="get"
                        action="<?php echo esc_url(home_url('/')); ?>"
                        class="pace-search-form search-form flex w-full max-w-none flex-col gap-3 border-t border-[#e1e7ec] pt-8 lg:max-w-[480px] lg:flex-row lg:items-stretch lg:gap-3"
                        role="search"
                        aria-label="<?php esc_attr_e('Search the site', 'matrix-starter'); ?>"
                    >
                        <label for="pace-404-search" class="sr-only">
                            <?php esc_html_e('Search keyword', 'matrix-starter'); ?>
                        </label>
                        <input
                            id="pace-404-search"
                            type="search"
                            name="s"
                            placeholder="<?php esc_attr_e('Search the site…', 'matrix-starter'); ?>"
                            class="pace-search-form__input box-border block h-[52px] min-h-[52px] w-full rounded-full border border-[#e1e7ec] bg-white px-5 font-comfortaa text-[16px] leading-6 text-[#003b65] placeholder:text-[#5c6b78] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0a60a0] focus-visible:ring-offset-2 lg:min-w-0 lg:flex-1"
                        />
                        <button
                            type="submit"
                            class="<?php echo esc_attr(matrix_pace_btn_classes('primary')); ?> no-form-btn-style w-full min-h-[52px] shrink-0 lg:w-auto lg:min-w-[120px]"
                        >
                            <?php esc_html_e('Search', 'matrix-starter'); ?>
                        </button>
                    </form>
                </article>
            </div>
        </div>
    </section>
</main>

<?php
get_footer();
