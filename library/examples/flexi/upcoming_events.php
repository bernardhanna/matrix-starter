<?php
// Get ACF fields
$section_heading  = get_sub_field('section_heading') ?: 'Upcoming events';
$heading_tag      = get_sub_field('heading_tag') ?: 'h2';
$view_all_button  = get_sub_field('view_all_button');
$number_of_events = get_sub_field('number_of_events') ?: 3;

// Padding settings
$padding_classes = ['pt-5', 'pb-5'];
if (have_rows('padding_settings')) {
    $padding_classes = [];
    while (have_rows('padding_settings')) {
        the_row();
        $screen_size    = get_sub_field('screen_size');
        $padding_top    = get_sub_field('padding_top');
        $padding_bottom = get_sub_field('padding_bottom');
        $padding_classes[] = "{$screen_size}:pt-[{$padding_top}rem]";
        $padding_classes[] = "{$screen_size}:pb-[{$padding_bottom}rem]";
    }
}

// Query upcoming events
$current_date = current_time('Y-m-d H:i:s');
$events_query = new WP_Query([
    'post_type'      => 'event',
    'posts_per_page' => (int) $number_of_events,
    'post_status'    => 'publish',
    'meta_key'       => 'event_start',
    'orderby'        => 'meta_value',
    'order'          => 'ASC',
    'meta_query'     => [
        [
            'key'     => 'event_start',
            'value'   => $current_date,
            'compare' => '>=',
            'type'    => 'DATETIME',
        ],
    ],
]);

$section_id = 'events-' . uniqid();
?>

<section id="<?php echo esc_attr($section_id); ?>" class="flex overflow-hidden relative">
    <div class="flex flex-col items-center w-full mx-auto max-w-container2 <?php echo esc_attr(implode(' ', $padding_classes)); ?>">
        <div class="overflow-hidden px-14 pt-0 pb-0 w-full md:pt-14 md:pb-20 max-lg:px-5">
            <?php
            $view_all_html = '';
            if ($view_all_button && is_array($view_all_button) && isset($view_all_button['url'], $view_all_button['title'])) {
                ob_start();
                ?>
                <a href="<?php echo esc_url($view_all_button['url']); ?>"
                   class="flex gap-2 justify-center items-center self-stretch py-3 pr-4 pl-6 my-auto text-sm font-bold leading-none text-sky-800 min-h-[42px] rounded-[100px] max-md:pl-5 w-fit whitespace-nowrap hover:bg-[#A9D2EF] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-800 transition-colors duration-200"
                   target="<?php echo esc_attr($view_all_button['target'] ?? '_self'); ?>"
                   aria-label="<?php echo esc_attr($view_all_button['title']); ?>">
                    <span class="self-stretch my-auto text-sky-800">
                        <?php echo esc_html($view_all_button['title']); ?>
                    </span>
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" class="object-contain self-stretch my-auto w-4 shrink-0 aspect-square">
                        <path d="M6 12L10 8L6 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
                <?php
                $view_all_html = ob_get_clean();
            }
            ?>

            <header class="flex flex-wrap gap-10 justify-between items-center w-full">
                <div class="flex gap-3.5 items-center min-w-60">
                    <div class="flex justify-center items-center w-10 h-10 bg-rose-50 rounded-full border-4 border-red-200">
                        <svg xmlns="http://www.w3.org/2000/svg" width="19" height="21" viewBox="0 0 19 21" fill="none">
                            <path d="M13.5 0.5V4.5M5.5 0.5V4.5M0.5 8.5H18.5M2.5 2.5H16.5C17.6046 2.5 18.5 3.39543 18.5 4.5V18.5C18.5 19.6046 17.6046 20.5 16.5 20.5H2.5C1.39543 20.5 0.5 19.6046 0.5 18.5V4.5C0.5 3.39543 1.39543 2.5 2.5 2.5Z" stroke="black" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>

                    <<?php echo esc_attr($heading_tag); ?> class="text-3xl font-light text-sky-950">
                        <?php echo esc_html($section_heading); ?>
                    </<?php echo esc_attr($heading_tag); ?>>
                </div>

                <?php if ($view_all_html): ?>
                    <div class="hidden md:block">
                        <?php echo $view_all_html; ?>
                    </div>
                <?php endif; ?>
            </header>

            <?php if ($events_query->have_posts()): ?>
                <section class="grid grid-cols-1 gap-4 items-stretch mt-6 w-full xl:grid-cols-3" aria-label="Upcoming events">
                    <?php while ($events_query->have_posts()): $events_query->the_post(); ?>
                        <?php
                        $event_id  = get_the_ID();
                        $permalink = get_permalink($event_id);
                        $event_image = get_post_thumbnail_id($event_id);
                        $event_start = get_post_meta($event_id, 'event_start', true);

                        $event_date_formatted = '';
                        if ($event_start) {
                            $date_obj = DateTime::createFromFormat('Y-m-d H:i:s', $event_start);
                            if ($date_obj) {
                                $event_date_formatted = $date_obj->format('j/n/Y | gA');
                            }
                        }

                        $event_locations = get_the_terms($event_id, 'event_location');
                        $location_name = $event_locations[0]->name ?? '';
                        ?>

                        <article class="w-full">
                            <a href="<?php echo esc_url($permalink); ?>"
                               class="group flex h-full w-full flex-row gap-4 p-6 rounded-lg bg-sky-950 border border-transparent transition-all duration-200 hover:bg-white hover:shadow-[0_0_0_4px_#00628F]">

                                <?php if ($event_image): ?>
                                    <?php echo wp_get_attachment_image($event_image, 'thumbnail', false, ['class' => 'w-[100px] h-[100px] rounded shrink-0 self-start']); ?>
                                <?php endif; ?>

                                <div class="flex flex-col flex-1">
                                    <h3 class="text-lg font-bold transition-colors text-red group-hover:text-sky-950">
                                        <?php the_title(); ?>
                                    </h3>

                                    <?php if ($event_date_formatted): ?>
                                        <time class="text-sm text-gray-100 transition-colors group-hover:text-gray-600">
                                            <?php echo esc_html($event_date_formatted); ?>
                                        </time>
                                    <?php endif; ?>

                                    <?php if ($location_name): ?>
                                        <div class="self-start px-3 py-1 mt-1 text-xs rounded-full bg-red w-fit">
                                            <?php echo esc_html($location_name); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </article>
                    <?php endwhile; wp_reset_postdata(); ?>
                </section>

                <?php if ($view_all_html): ?>
                    <div class="flex justify-center mt-6 md:hidden">
                        <?php echo $view_all_html; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

        </div>
    </div>
</section>