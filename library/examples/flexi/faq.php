<?php
$section_id = 'faq-section-' . uniqid();
$heading = get_sub_field('heading');
$heading_tag = get_sub_field('heading_tag');
$subtitle = get_sub_field('subtitle');
$description = get_sub_field('description');
$faq_source = get_sub_field('faq_source');
$manual_faqs = get_sub_field('manual_faqs');
$selected_faqs = get_sub_field('selected_faqs');
$button = get_sub_field('button');
$background_color = get_sub_field('background_color');
$has_heading = !empty($heading);

$padding_classes = ['pt-5', 'pb-5'];
if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
        the_row();
        $screen_size = get_sub_field('screen_size');
        $padding_top = get_sub_field('padding_top');
        $padding_bottom = get_sub_field('padding_bottom');
        $padding_classes[] = "{$screen_size}:pt-[{$padding_top}rem]";
        $padding_classes[] = "{$screen_size}:pb-[{$padding_bottom}rem]";
    }
}

// Prepare FAQ items based on source
$faq_items = [];

if ($faq_source === 'manual' && have_rows('manual_faqs')) {
    while (have_rows('manual_faqs')) {
        the_row();
        $faq_items[] = [
            'question' => get_sub_field('question'),
            'answer' => get_sub_field('answer')
        ];
    }
} elseif ($faq_source === 'posts' && $selected_faqs) {
    foreach ($selected_faqs as $faq_post) {
        $faq_items[] = [
            'question' => get_the_title($faq_post->ID),
            'answer' => apply_filters('the_content', get_post_field('post_content', $faq_post->ID))
        ];
    }
} elseif ($faq_source === 'all') {
    $faq_posts = get_posts([
        'post_type' => 'faq',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ]);
    foreach ($faq_posts as $faq_post) {
        $faq_items[] = [
            'question' => get_the_title($faq_post->ID),
            'answer' => apply_filters('the_content', $faq_post->post_content)
        ];
    }
}
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="relative flex overflow-hidden font-bold text-sky-800 <?php echo esc_attr(implode(' ', $padding_classes)); ?>"
    style="background-color: <?php echo esc_attr($background_color); ?>;"
>
    <div class="flex flex-col items-center max-lg:py-5 py-16 mx-auto w-full max-w-[768px] max-xl:px-5">

        <?php if ($has_heading): ?>
            <<?php echo esc_attr($heading_tag); ?>
                id="<?php echo esc_attr($section_id); ?>-heading"
                class="self-start text-3xl leading-none text-sky-800 max-md:max-w-full"
            >
                <?php echo esc_html($heading); ?>
            </<?php echo esc_attr($heading_tag); ?>>
        <?php endif; ?>

        <?php if (!empty($subtitle)): ?>
            <div class="mt-4 text-2xl font-light leading-8 text-sky-950 max-md:max-w-full">
                <?php echo esc_html($subtitle); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($description)): ?>
            <div class="mt-4 text-base leading-6 text-sky-950 max-md:max-w-full wp_editor">
                <?php echo wp_kses_post($description); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($faq_items)): ?>
            <div class="pt-4 mt-4 w-full max-md:max-w-full" role="region" aria-label="Frequently Asked Questions">
                <?php foreach ($faq_items as $index => $faq): ?>
                    <?php
                    $faq_id = $section_id . '-faq-' . $index;
                    $button_id = $faq_id . '-button';
                    $content_id = $faq_id . '-content';
                    ?>
                    <div class="<?php echo $index > 0 ? 'mt-4' : ''; ?> w-full">
                        <div class="bg-yellow-50 rounded-lg faq-item" data-faq-item>
                            <button
                                id="<?php echo esc_attr($button_id); ?>"
                                class="flex flex-wrap gap-10 justify-between items-center pt-6 pb-6 px-6 w-full text-lg leading-none text-left outline-none focus:outline-none focus:ring-0 focus:ring-offset-0 max-md:px-5 max-md:max-w-full md:hover:bg-[#fcf192] transition-colors duration-200"
                                aria-expanded="false"
                                aria-controls="<?php echo esc_attr($content_id); ?>"
                                data-faq-toggle
                            >
                                <span class="flex-1 self-stretch my-auto text-sky-800">
                                    <?php echo esc_html($faq['question']); ?>
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
                                <div class="px-6 pb-6 max-md:px-5">
                                    <div class="text-base font-normal leading-6 text-sky-950 max-md:max-w-full wp_editor">
                                        <?php echo wp_kses_post($faq['answer']); ?>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($button && is_array($button) && isset($button['url'], $button['title'])): ?>
            <div class="self-start pt-4 mt-4 text-sm leading-none text-white">
                <a
                    href="<?php echo esc_url($button['url']); ?>"
                    class="btn-primary flex gap-2 justify-center items-center px-6 py-3.5 min-h-[52px] rounded-pill w-fit whitespace-nowrap max-md:px-5 active:!bg-brand-primary"
                    target="<?php echo esc_attr($button['target'] ?? '_self'); ?>"
                    aria-label="<?php echo esc_attr($button['title']); ?>"
                >
                    <span class="self-stretch my-auto font-bold">
                        <?php echo esc_html($button['title']); ?>
                    </span>
                    <svg
                        class="object-contain self-stretch my-auto w-6 h-6 shrink-0"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                        aria-hidden="true"
                    >
                        <path
                            d="M5 12H19M19 12L12 5M19 12L12 19"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const faqItems = document.querySelectorAll('[data-faq-item]');

    faqItems.forEach(function(item) {
        const toggle = item.querySelector('[data-faq-toggle]');
        const content = item.querySelector('.faq-content');
        const iconClosed = item.querySelector('.faq-icon-closed');
        const iconOpen = item.querySelector('.faq-icon-open');

        if (toggle && content) {
            toggle.addEventListener('click', function() {
                const isExpanded = toggle.getAttribute('aria-expanded') === 'true';

                // Toggle expanded state
                toggle.setAttribute('aria-expanded', !isExpanded);

                if (isExpanded) {
                    // Collapse
                    content.classList.add('hidden');
                    item.classList.remove('bg-white', 'border-4', 'border-yellow-300', 'border-solid');
                    item.classList.add('bg-yellow-50');
                    iconClosed.classList.remove('hidden');
                    iconOpen.classList.add('hidden');
                } else {
                    // Expand
                    content.classList.remove('hidden');
                    item.classList.remove('bg-yellow-50');
                    item.classList.add('bg-white', 'border-4', 'border-yellow-300', 'border-solid');
                    iconClosed.classList.add('hidden');
                    iconOpen.classList.remove('hidden');
                }
            });

            // Keyboard support
            toggle.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    toggle.click();
                }
            });
        }
    });
});
</script>
