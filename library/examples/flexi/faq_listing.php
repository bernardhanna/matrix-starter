<?php
/**
 * FAQ archive listing.
 * Renders all FAQ posts using the same accordion style as the FAQ block.
 */

if (!defined('ABSPATH')) {
    exit;
}

$section_id = 'faq-listing-' . wp_generate_uuid4();

$faq_posts = get_posts([
    'post_type'      => 'faq',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => 'date',
    'order'          => 'DESC',
]);
?>

<section id="<?php echo esc_attr($section_id); ?>" class="flex overflow-hidden relative py-8 lg:py-12" aria-label="<?php echo esc_attr__('Frequently Asked Questions', 'matrix-starter'); ?>">
    <div class="flex flex-col items-center mx-auto w-full max-w-[768px] max-xl:px-5">
        <?php if (!empty($faq_posts)) : ?>
            <div class="w-full" role="region" aria-label="<?php echo esc_attr__('Frequently Asked Questions', 'matrix-starter'); ?>">
                <?php foreach ($faq_posts as $index => $faq_post) : ?>
                    <?php
                    $faq_id = $section_id . '-faq-' . $index;
                    $button_id = $faq_id . '-button';
                    $content_id = $faq_id . '-content';
                    $answer_html = apply_filters('the_content', (string) $faq_post->post_content);
                    ?>
                    <div class="<?php echo $index > 0 ? 'mt-4' : ''; ?> w-full">
                        <div class="bg-yellow-50 rounded-lg faq-item" data-faq-item>
                            <button
                                id="<?php echo esc_attr($button_id); ?>"
                                class="flex flex-wrap gap-10 justify-between items-center pt-6 pb-6 px-6 w-full text-lg leading-none text-left outline-none focus:outline-none focus:ring-0 focus:ring-offset-0 max-md:px-5 max-md:max-w-full md:hover:bg-[#fcf192] transition-colors duration-200"
                                aria-expanded="false"
                                aria-controls="<?php echo esc_attr($content_id); ?>"
                                data-faq-toggle
                                type="button"
                            >
                                <span class="flex-1 self-stretch my-auto text-sky-800">
                                    <?php echo esc_html(get_the_title($faq_post)); ?>
                                </span>
                                <svg
                                    class="object-contain self-stretch my-auto w-8 h-8 transition-transform duration-200 shrink-0"
                                    width="32"
                                    height="32"
                                    viewBox="0 0 32 32"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M8 12L16 20L24 12"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="faq-icon-closed"
                                    />
                                    <path
                                        d="M8 20L16 12L24 20"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="hidden faq-icon-open"
                                    />
                                </svg>
                            </button>

                            <div
                                id="<?php echo esc_attr($content_id); ?>"
                                class="hidden faq-content"
                                aria-labelledby="<?php echo esc_attr($button_id); ?>"
                                role="region"
                            >
                                <div class="px-6 pb-6 mt-4 max-md:px-5">
                                    <div class="text-base font-normal leading-6 text-sky-950 max-md:max-w-full wp_editor">
                                        <?php echo wp_kses_post($answer_html); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <p class="text-sky-950"><?php echo esc_html__('No FAQs found.', 'matrix-starter'); ?></p>
        <?php endif; ?>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var section = document.getElementById('<?php echo esc_js($section_id); ?>');
    if (!section) {
        return;
    }

    var faqItems = section.querySelectorAll('[data-faq-item]');
    faqItems.forEach(function (item) {
        var toggle = item.querySelector('[data-faq-toggle]');
        var content = item.querySelector('.faq-content');
        var iconClosed = item.querySelector('.faq-icon-closed');
        var iconOpen = item.querySelector('.faq-icon-open');

        if (!toggle || !content) {
            return;
        }

        toggle.addEventListener('click', function () {
            var isExpanded = toggle.getAttribute('aria-expanded') === 'true';
            toggle.setAttribute('aria-expanded', String(!isExpanded));

            if (isExpanded) {
                content.classList.add('hidden');
                item.classList.remove('bg-white', 'border-4', 'border-yellow-300', 'border-solid');
                item.classList.add('bg-yellow-50');
                if (iconClosed) iconClosed.classList.remove('hidden');
                if (iconOpen) iconOpen.classList.add('hidden');
            } else {
                content.classList.remove('hidden');
                item.classList.remove('bg-yellow-50');
                item.classList.add('bg-white', 'border-4', 'border-yellow-300', 'border-solid');
                if (iconClosed) iconClosed.classList.add('hidden');
                if (iconOpen) iconOpen.classList.remove('hidden');
            }
        });

        toggle.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                toggle.click();
            }
        });
    });
});
</script>
