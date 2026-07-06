<?php
// Get ACF fields
$section_heading     = get_sub_field('section_heading') ?: 'Events';
$heading_tag         = get_sub_field('heading_tag') ?: 'h2';
$show_filters        = get_sub_field('show_filters');
$show_search         = get_sub_field('show_search');
$events_per_page     = get_sub_field('events_per_page') ?: 6;
$show_pagination     = get_sub_field('show_pagination');
$background_color    = get_sub_field('background_color') ?: '#ffffff';

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

// Get current page for pagination
$paged = get_query_var('paged') ? get_query_var('paged') : 1;

// Get filter parameters
$selected_location = isset($_GET['event_location']) ? sanitize_text_field($_GET['event_location']) : '';
$search_keyword    = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

// Build query args
$query_args = array(
    'post_type'      => 'event',
    'posts_per_page' => $events_per_page,
    'paged'          => $paged,
    'post_status'    => 'publish',
    'meta_key'       => 'event_start',
    'orderby'        => 'meta_value',
    'order'          => 'ASC',
);

// Add taxonomy filter
if (!empty($selected_location)) {
    $query_args['tax_query'] = array(
        array(
            'taxonomy' => 'event_location',
            'field'    => 'slug',
            'terms'    => $selected_location,
        ),
    );
}

// Add search filter
if (!empty($search_keyword)) {
    $query_args['s'] = $search_keyword;
}

// Execute query
$events_query = new WP_Query($query_args);

// Get all event locations for filters
$event_locations = get_terms(array(
    'taxonomy'   => 'event_location',
    'hide_empty' => true,
    'orderby'    => 'name',
    'order'      => 'ASC',
));

$section_id = 'events-listing-' . uniqid();
$search_input_id = $section_id . '-search';
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="relative flex overflow-hidden <?php echo esc_attr(implode(' ', $padding_classes)); ?>"
    style="background-color: <?php echo esc_attr($background_color); ?>;"
    x-data="eventsFilter()"
>
    <div class="flex flex-col items-center pt-5 lg:pt-[3.5rem] pb-5 mx-auto w-full max-w-container max-lg:px-5">

        <?php if ($show_filters || $show_search): ?>
            <div class="grid grid-cols-1 gap-6 items-center pb-4 w-full text-sm leading-none md:grid-cols-[60%_40%]">

                <?php if ($show_filters && !empty($event_locations)): ?>
                    <!-- Filters -->
                    <div class="flex gap-4 items-center self-stretch my-auto w-full min-w-0">
                        <div class="self-stretch my-auto text-sky-950">
                            Filter by:
                        </div>

                        <div class="relative flex-1 min-w-0">
                            <div
                                class="chip-slider flex gap-2 items-center self-stretch my-auto font-semibold text-sky-900 overflow-x-auto whitespace-nowrap pr-6"
                                role="radiogroup"
                                aria-label="Filter events by location"
                                data-chip-slider
                            >
                                <button
                                    type="button"
                                    role="radio"
                                    class="shrink-0 flex flex-col justify-center items-center self-stretch px-4 py-2 my-auto whitespace-nowrap rounded-full border-2 border-sky-600 transition-colors duration-200 min-h-9 w-fit focus:ring-sky-600"
                                    data-filter-option=""
                                    :class="selectedLocation === ''
                                        ? 'bg-sky-700 text-white'
                                        : 'bg-white text-sky-900 hover:bg-[#d7ebf7] hover:text-sky-900'"
                                    @click="filterEvents('')"
                                    :aria-checked="selectedLocation === '' ? 'true' : 'false'"
                                    :tabindex="selectedLocation === '' ? '0' : '-1'"
                                    @keydown="onFilterKeydown($event, '')"
                                >
                                    All
                                </button>

                                <?php foreach ($event_locations as $location): ?>
                                    <button
                                        type="button"
                                        role="radio"
                                        class="shrink-0 flex flex-col justify-center items-center self-stretch px-4 py-2 my-auto whitespace-nowrap rounded-full transition-colors duration-200 min-h-9 w-fit focus:ring-sky-600"
                                        data-filter-option="<?php echo esc_attr($location->slug); ?>"
                                        :class="selectedLocation === '<?php echo esc_attr($location->slug); ?>'
                                            ? 'bg-sky-700 text-white'
                                            : 'bg-[#d7ebf7] text-sky-900 hover:bg-[#b9dcf1] hover:text-sky-900'"
                                        @click="filterEvents('<?php echo esc_attr($location->slug); ?>')"
                                        :aria-checked="selectedLocation === '<?php echo esc_attr($location->slug); ?>' ? 'true' : 'false'"
                                        :tabindex="selectedLocation === '<?php echo esc_attr($location->slug); ?>' ? '0' : '-1'"
                                        @keydown="onFilterKeydown($event, '<?php echo esc_attr($location->slug); ?>')"
                                    >
                                        <?php echo esc_html($location->name); ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                            <span class="pointer-events-none absolute right-0 top-0 h-full w-8 bg-gradient-to-l from-white to-transparent"></span>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($show_search): ?>
                <!-- Search -->
                <form
                    class="flex self-stretch my-auto min-h-[60px] w-full max-w-[396px] items-center lg:ml-auto"
                    @submit.prevent="searchEvents()"
                    role="search"
                    aria-label="Search events"
                >
                    <div class="flex-1 shrink basis-12 min-w-0 w-full shadow-[0px_0px_20px_rgba(63,0,119,0.07)] rounded-r-[30px] overflow-hidden">
                        <div class="w-full">
                            <div class="flex items-center">
                                <div
                                    class="flex flex-1 gap-2 items-center self-stretch my-auto w-full shrink basis-0 min-w-60 px-4 py-2 max-h-[60px] rounded-l-[4px] border border-transparent bg-white transition-all duration-200 hover:bg-[#CBF3F6] hover:border-[#1C959B] focus-within:bg-[#C2EDFF] focus-within:border-[#C2EDFF]"
                                >
                                    <svg
                                        class="object-contain self-stretch my-auto w-6 shrink-0 aspect-square"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                        aria-hidden="true"
                                    >
                                        <path d="M21 21L16.65 16.65M19 11C19 15.4183 15.4183 19 11 19C6.58172 19 3 15.4183 3 11C3 6.58172 6.58172 3 11 3C15.4183 3 19 6.58172 19 11Z" stroke="#9CA3AF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>

                                    <input
                                        type="search"
                                        id="<?php echo esc_attr($search_input_id); ?>"
                                        class="flex-1 self-stretch my-auto text-slate-900 placeholder:text-slate-600 bg-transparent !border-0 !outline-none !ring-0 focus:!border-0 focus:!outline-none focus:!ring-0 active:!border-0 active:!outline-none active:!ring-0 appearance-none shrink basis-0 min-h-[28px]"
                                        placeholder="Type keyword"
                                        x-model="searchKeyword"
                                        aria-label="Search events"
                                    />
                                    <label for="<?php echo esc_attr($search_input_id); ?>" class="sr-only">Search events</label>
                                </div>

                                <button
                                    type="submit"
                                    class="flex -ml-px gap-2 justify-center items-center px-6 py-4 h-full min-h-[60px] font-bold whitespace-nowrap bg-yellow-300 text-slate-800 max-md:px-5 w-fit hover:bg-[#FCF4C5] focus:ring-yellow-300 border-0 rounded-r-[100px] shadow-none min-[769px]:max-[998px]:px-4"
                                    aria-label="Search events"
                                >
                                    <span class="min-[769px]:max-[998px]:hidden">Search</span>
                                    <span class="hidden min-[769px]:max-[998px]:inline-flex" aria-hidden="true">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M21 21L16.65 16.65M19 11C19 15.4183 15.4183 19 11 19C6.58172 19 3 15.4183 3 11C3 6.58172 6.58172 3 11 3C15.4183 3 19 6.58172 19 11Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            <?php endif; ?>


            </div>
        <?php endif; ?>

        <!-- Events Grid -->
        <section class="flex flex-col mt-4 w-full max-md:max-w-full" aria-label="Events listing">
            <div class="grid grid-cols-1 gap-x-6 gap-y-6 w-full md:grid-cols-2 lg:grid-cols-3 lg:gap-y-24 max-md:max-w-full" x-show="!loading">

                <?php if ($events_query->have_posts()): ?>
                    <?php while ($events_query->have_posts()): $events_query->the_post(); ?>
                        <?php
                        $post_id               = get_the_ID();
                        $event_start           = get_post_meta($post_id, 'event_start', true);
                        $event_date            = $event_start ? date('j M Y', strtotime($event_start)) : '';
                        $event_locations_terms = get_the_terms($post_id, 'event_location');
                        $location_name         = ($event_locations_terms && !is_wp_error($event_locations_terms)) ? $event_locations_terms[0]->name : 'Location TBD';
                        $featured_image        = get_post_thumbnail_id($post_id);
                        $image_alt             = get_post_meta($featured_image, '_wp_attachment_image_alt', true) ?: get_the_title();
                        $event_permalink       = get_permalink($post_id);
                        $registration_link     = get_field('event_registration_link', $post_id);
                        $direct_registration   = get_field('event_listing_open_registration_direct', $post_id);
                        $direct_registration   = ($direct_registration === 1 || $direct_registration === '1' || $direct_registration === true);
                        $has_registration_url  = is_array($registration_link) && !empty($registration_link['url']);
                        $card_target_url       = $event_permalink;
                        $card_target_window    = '_self';
                        $card_rel              = '';

                        if ($direct_registration && $has_registration_url) {
                            $card_target_url = (string) $registration_link['url'];
                            $card_target_window = '_blank';
                            $card_rel = 'noopener noreferrer';
                        }
                        ?>

                        <article 
    class="overflow-hidden flex-1 bg-sky-950 rounded-lg shrink basis-0 min-w-60 event-item cursor-pointer transition-all duration-200 hover:shadow-[0_0_0_4px_#F68DA7]"
    data-url="<?php echo esc_url($card_target_url); ?>"
    data-url-target="<?php echo esc_attr($card_target_window); ?>"
    tabindex="0"
    aria-label="<?php echo esc_attr('Open event: ' . wp_strip_all_tags(get_the_title())); ?>"
>
                            <!-- Featured Image -->
                            <div class="flex overflow-hidden relative flex-col gap-2.5 items-start w-full aspect-[1.565] min-h-[232px] rounded-t-[8px]">
                                <?php if ($featured_image): ?>
                                    <?php echo wp_get_attachment_image($featured_image, 'large', false, [
                                        'alt'     => esc_attr($image_alt),
                                        'class'   => 'object-cover absolute inset-0 size-full',
                                        'loading' => 'lazy',
                                    ]); ?>
                                <?php endif; ?>
                            </div>

                            <!-- Event Content -->
                            <div class="flex flex-col p-6 w-full text-sm max-md:px-5">
                                <h2 class="font-sans text-[18px] font-bold not-italic leading-[24px] text-[#F68DA7]">
                                    <a
                                        href="<?php echo esc_url($card_target_url); ?>"
                                        target="<?php echo esc_attr($card_target_window); ?>"
                                        <?php if (!empty($card_rel)) : ?>rel="<?php echo esc_attr($card_rel); ?>"<?php endif; ?>
                                        class="hover:underline focus:underline"
                                    >
                                        <?php the_title(); ?>
                                    </a>
                                </h2>

                                <div class="self-start mt-2 text-sm leading-none text-gray-200">
                                    <?php if ($event_date): ?>
                                        <div class="flex gap-2 items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
<mask id="mask0_850_2579" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="16" height="16">
<rect width="16" height="16" fill="#D9D9D9"/>
</mask>
<g mask="url(#mask0_850_2579)">
<path d="M8.66634 7.73325V5.33325C8.66634 5.14436 8.60245 4.98603 8.47467 4.85825C8.3469 4.73047 8.18856 4.66658 7.99967 4.66658C7.81079 4.66658 7.65245 4.73047 7.52467 4.85825C7.3969 4.98603 7.33301 5.14436 7.33301 5.33325V7.98325C7.33301 8.07214 7.34967 8.15825 7.38301 8.24158C7.41634 8.32492 7.46634 8.39992 7.53301 8.46658L9.73301 10.6666C9.85523 10.7888 10.0108 10.8499 10.1997 10.8499C10.3886 10.8499 10.5441 10.7888 10.6663 10.6666C10.7886 10.5444 10.8497 10.3888 10.8497 10.1999C10.8497 10.011 10.7886 9.85547 10.6663 9.73325L8.66634 7.73325ZM7.99967 14.6666C7.07745 14.6666 6.21079 14.4916 5.39967 14.1416C4.58856 13.7916 3.88301 13.3166 3.28301 12.7166C2.68301 12.1166 2.20801 11.411 1.85801 10.5999C1.50801 9.78881 1.33301 8.92214 1.33301 7.99992C1.33301 7.0777 1.50801 6.21103 1.85801 5.39992C2.20801 4.58881 2.68301 3.88325 3.28301 3.28325C3.88301 2.68325 4.58856 2.20825 5.39967 1.85825C6.21079 1.50825 7.07745 1.33325 7.99967 1.33325C8.9219 1.33325 9.78856 1.50825 10.5997 1.85825C11.4108 2.20825 12.1163 2.68325 12.7163 3.28325C13.3163 3.88325 13.7913 4.58881 14.1413 5.39992C14.4913 6.21103 14.6663 7.0777 14.6663 7.99992C14.6663 8.92214 14.4913 9.78881 14.1413 10.5999C13.7913 11.411 13.3163 12.1166 12.7163 12.7166C12.1163 13.3166 11.4108 13.7916 10.5997 14.1416C9.78856 14.4916 8.9219 14.6666 7.99967 14.6666Z" fill="#F68DA7"/>
</g>
</svg>
                                            <time
                                                datetime="<?php echo esc_attr(date('Y-m-d', strtotime($event_start))); ?>"
                                                class="self-stretch my-auto text-gray-200"
                                            >
                                                <?php echo esc_html($event_date); ?>
                                            </time>
                                        </div>
                                    <?php endif; ?>

                                    <div class="flex gap-2 items-center mt-2">
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
<mask id="mask0_850_2584" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="16" height="16">
<rect width="16" height="16" fill="#D9D9D9"/>
</mask>
<g mask="url(#mask0_850_2584)">
<path d="M8.00033 7.99992C8.36699 7.99992 8.68088 7.86936 8.94199 7.60825C9.2031 7.34714 9.33366 7.03325 9.33366 6.66658C9.33366 6.29992 9.2031 5.98603 8.94199 5.72492C8.68088 5.46381 8.36699 5.33325 8.00033 5.33325C7.63366 5.33325 7.31977 5.46381 7.05866 5.72492C6.79755 5.98603 6.66699 6.29992 6.66699 6.66658C6.66699 7.03325 6.79755 7.34714 7.05866 7.60825C7.31977 7.86936 7.63366 7.99992 8.00033 7.99992ZM8.00033 14.6666C6.21144 13.1444 4.87533 11.7305 3.99199 10.4249C3.10866 9.11936 2.66699 7.91103 2.66699 6.79992C2.66699 5.13325 3.2031 3.80547 4.27533 2.81659C5.34755 1.8277 6.58921 1.33325 8.00033 1.33325C9.41144 1.33325 10.6531 1.8277 11.7253 2.81659C12.7975 3.80547 13.3337 5.13325 13.3337 6.79992C13.3337 7.91103 12.892 9.11936 12.0087 10.4249C11.1253 11.7305 9.78921 13.1444 8.00033 14.6666Z" fill="#F68DA7"/>
</g>
</svg>
                                        <span class="self-stretch my-auto text-gray-200">
                                            <?php echo esc_html($location_name); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-span-full py-12 w-full text-center">
                        <p class="text-lg text-gray-600">No events found matching your criteria.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Loading state -->
            <div x-show="loading" class="grid grid-cols-1 gap-x-6 gap-y-6 w-full md:grid-cols-2 lg:grid-cols-3 lg:gap-y-24">
                <?php for ($i = 0; $i < 3; $i++): ?>
                    <div class="overflow-hidden rounded-lg animate-pulse bg-sky-950">
                        <div class="bg-gray-300 w-full aspect-[1.56] min-h-[232px]"></div>
                        <div class="flex flex-col p-6 w-full">
                            <div class="mb-2 h-6 bg-gray-300 rounded"></div>
                            <div class="mb-2 w-24 h-4 bg-gray-300 rounded"></div>
                            <div class="w-32 h-4 bg-gray-300 rounded"></div>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>

            <?php if ($show_pagination && $events_query->max_num_pages > 1): ?>
                <!-- Pagination -->
                <nav
                    class="flex flex-wrap gap-3 justify-evenly items-center self-center pt-8 pb-6 mt-6 max-w-full text-base leading-none text-sky-600 whitespace-nowrap md:gap-8 md:justify-center md:pt-20 md:pb-12"
                    aria-label="Events pagination"
                >
                    <?php
                    $total_pages  = $events_query->max_num_pages;
                    $current_page = $paged;

                    $base_url = get_pagenum_link(1);

                    $build_page_url = function($page) use ($base_url, $selected_location, $search_keyword) {
                        $url = add_query_arg(array_filter([
                            'event_location' => $selected_location ?: null,
                            'search'         => $search_keyword ?: null,
                            'paged'          => $page > 1 ? $page : null,
                        ]), $base_url);

                        return $url;
                    };
                    ?>

                    <!-- Previous Button -->
                    <?php if ($current_page > 1): ?>
                        <a
                            href="<?php echo esc_url($build_page_url($current_page - 1)); ?>"
                            class="flex gap-1 items-center self-stretch py-1 pr-2 pl-1 my-auto text-sky-600 hover:text-sky-800 focus:text-sky-800 btn"
                            aria-label="Go to previous page"
                        >
                            <svg
                                class="object-contain self-stretch my-auto w-8 shrink-0 aspect-square"
                                width="32"
                                height="32"
                                viewBox="0 0 32 32"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                                aria-hidden="true"
                            >
                                <path d="M20 24L12 16L20 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="self-stretch my-auto">Back</span>
                        </a>
                    <?php else: ?>
                        <div class="flex gap-1 items-center self-stretch py-1 pr-2 pl-1 my-auto text-gray-300">
                            <svg
                                class="object-contain self-stretch my-auto w-8 shrink-0 aspect-square"
                                width="32"
                                height="32"
                                viewBox="0 0 32 32"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                                aria-hidden="true"
                            >
                                <path d="M20 24L12 16L20 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="self-stretch my-auto text-gray-300">Back</span>
                        </div>
                    <?php endif; ?>

                    <!-- Page Numbers -->
                    <div class="flex gap-2 items-center self-stretch my-auto">
                        <?php
                        $pagination_link_class = 'flex flex-col justify-center items-center self-stretch my-auto w-12 min-h-12 rounded-full text-sky-600 transition-all duration-200 hover:bg-[#009DE6] hover:text-white hover:shadow-[0_0_0_4px_#009DE6] focus:bg-[#009DE6] focus:text-white focus:shadow-[0_0_0_4px_#009DE6]';

                        $pagination_current_class = 'flex flex-col justify-center items-center self-stretch my-auto w-12 min-h-12 rounded-full text-white bg-[#009DE6] shadow-[0_0_0_4px_#009DE6]';
                        ?>

                        <?php if ($current_page == 1): ?>
                            <div class="<?php echo esc_attr($pagination_current_class); ?>">
                                <span aria-current="page">1</span>
                            </div>
                        <?php else: ?>
                            <a
                                href="<?php echo esc_url($build_page_url(1)); ?>"
                                class="<?php echo esc_attr($pagination_link_class); ?>"
                                aria-label="Go to page 1"
                            >
                                <span>1</span>
                            </a>
                        <?php endif; ?>

                        <?php
                        for ($i = max(2, $current_page - 2); $i <= min($total_pages - 1, $current_page + 2); $i++):
                            if ($i == $current_page): ?>
                                <div class="<?php echo esc_attr($pagination_current_class); ?>">
                                    <span aria-current="page"><?php echo esc_html($i); ?></span>
                                </div>
                            <?php else: ?>
                                <a
                                    href="<?php echo esc_url($build_page_url($i)); ?>"
                                    class="<?php echo esc_attr($pagination_link_class); ?>"
                                    aria-label="Go to page <?php echo esc_attr($i); ?>"
                                >
                                    <span><?php echo esc_html($i); ?></span>
                                </a>
                            <?php endif;
                        endfor;
                        ?>

                        <?php if ($total_pages > 5 && $current_page < $total_pages - 2): ?>
                            <div class="flex flex-col justify-center items-start self-stretch py-1 my-auto w-6">
                                <span class="text-sky-600" aria-hidden="true">...</span>
                            </div>
                            <a
                                href="<?php echo esc_url($build_page_url($total_pages)); ?>"
                                class="<?php echo esc_attr($pagination_link_class); ?>"
                                aria-label="Go to page <?php echo esc_attr($total_pages); ?>"
                            >
                                <span><?php echo esc_html($total_pages); ?></span>
                            </a>
                        <?php endif; ?>
                    </div>

                    
                </nav>
            <?php endif; ?>
        </section>
    </div>
</section>

<script>
function eventsFilter() {
    return {
        selectedLocation: '<?php echo esc_js($selected_location); ?>',
        searchKeyword: '<?php echo esc_js($search_keyword); ?>',
        loading: false,
        filterOptions: ['', <?php
            $event_slugs = array_map(static function($location) {
                return "'" . esc_js((string) $location->slug) . "'";
            }, is_array($event_locations) ? $event_locations : []);
            echo implode(', ', $event_slugs);
        ?>],

        filterEvents(location) {
            this.selectedLocation = location;
            this.updateUrl();
        },

        onFilterKeydown(event, currentLocation) {
            const key = event.key;
            if (!['ArrowRight', 'ArrowDown', 'ArrowLeft', 'ArrowUp', 'Home', 'End', 'Enter', ' '].includes(key)) {
                return;
            }

            if (key === 'Enter' || key === ' ') {
                event.preventDefault();
                this.filterEvents(currentLocation);
                return;
            }

            event.preventDefault();
            const options = this.filterOptions || [];
            if (!options.length) return;

            let currentIndex = options.indexOf(currentLocation);
            if (currentIndex < 0) currentIndex = 0;
            let nextIndex = currentIndex;

            if (key === 'ArrowRight' || key === 'ArrowDown') {
                nextIndex = (currentIndex + 1) % options.length;
            } else if (key === 'ArrowLeft' || key === 'ArrowUp') {
                nextIndex = (currentIndex - 1 + options.length) % options.length;
            } else if (key === 'Home') {
                nextIndex = 0;
            } else if (key === 'End') {
                nextIndex = options.length - 1;
            }

            const nextLocation = options[nextIndex];
            if (typeof nextLocation === 'undefined') return;

            this.selectedLocation = nextLocation;
            this.$nextTick(() => {
                const selector = '[data-filter-option="' + nextLocation + '"]';
                const nextBtn = this.$root.querySelector(selector);
                if (nextBtn) nextBtn.focus();
            });
            this.filterEvents(nextLocation);
        },

        searchEvents() {
            this.updateUrl();
        },

        updateUrl() {
            this.loading = true;
            const url = new URL(window.location);

            if (this.selectedLocation) {
                url.searchParams.set('event_location', this.selectedLocation);
            } else {
                url.searchParams.delete('event_location');
            }

            if (this.searchKeyword) {
                url.searchParams.set('search', this.searchKeyword);
            } else {
                url.searchParams.delete('search');
            }

            url.searchParams.delete('paged');

            window.location.href = url.toString();
        }
    }
}
</script>

<style>
#<?php echo esc_attr($section_id); ?> .chip-slider {
    scrollbar-width: none;
    -ms-overflow-style: none;
}
#<?php echo esc_attr($section_id); ?> .chip-slider::-webkit-scrollbar {
    display: none;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var cards = document.querySelectorAll('#<?php echo esc_js($section_id); ?> .event-item[data-url]');
    if (!cards.length) return;

    cards.forEach(function (card) {
        card.addEventListener('click', function (e) {
            if (e.target.closest('a, button, input, textarea, select, label, [role="button"]')) return;
            var url = card.getAttribute('data-url');
            var target = card.getAttribute('data-url-target') || '_self';
            if (!url) return;
            if (target === '_blank') {
                window.open(url, '_blank', 'noopener');
                return;
            }
            window.location.href = url;
        });

        card.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter' && e.key !== ' ') return;
            if (e.target.closest('a, button, input, textarea, select, label, [role="button"]')) return;
            e.preventDefault();
            var url = card.getAttribute('data-url');
            var target = card.getAttribute('data-url-target') || '_self';
            if (!url) return;
            if (target === '_blank') {
                window.open(url, '_blank', 'noopener');
                return;
            }
            window.location.href = url;
        });
    });
});
</script>

<?php wp_reset_postdata(); ?>