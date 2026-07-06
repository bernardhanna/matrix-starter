<?php
// Get ACF fields (support both flexi context and template include context)
$section_heading     = (string) get_sub_field('section_heading');
$section_heading_tag = get_sub_field('section_heading_tag') ?: 'h2';

$has_show_filters_field = is_array(get_sub_field_object('show_filters'));
$show_filters = $has_show_filters_field ? (bool) get_sub_field('show_filters') : true;

$has_show_search_field = is_array(get_sub_field_object('show_search'));
$show_search = $has_show_search_field ? (bool) get_sub_field('show_search') : true;

$has_limit_to_category_field = is_array(get_sub_field_object('limit_to_category'));
$limit_to_category_id = $has_limit_to_category_field ? (int) get_sub_field('limit_to_category') : 0;
$has_media_subcategory_filters_field = is_array(get_sub_field_object('show_media_subcategory_filters_only'));
$show_media_subcategory_filters_only = $has_media_subcategory_filters_field ? (bool) get_sub_field('show_media_subcategory_filters_only') : false;
$media_filter_slugs = ['local', 'national', 'international'];
$has_exclude_categories_field = is_array(get_sub_field_object('exclude_categories'));
$exclude_category_ids = [];
if ($has_exclude_categories_field) {
    $exclude_category_ids = get_sub_field('exclude_categories');
    if (!is_array($exclude_category_ids)) {
        $exclude_category_ids = $exclude_category_ids ? [$exclude_category_ids] : [];
    }
    $exclude_category_ids = array_values(array_filter(array_map('intval', $exclude_category_ids), static function ($id) {
        return $id > 0;
    }));
}
$has_exclude_posts_field = is_array(get_sub_field_object('exclude_posts'));
$exclude_post_ids = [];
if ($has_exclude_posts_field) {
    $exclude_post_ids = get_sub_field('exclude_posts');
    if (!is_array($exclude_post_ids)) {
        $exclude_post_ids = $exclude_post_ids ? [$exclude_post_ids] : [];
    }
    $exclude_post_ids = array_values(array_filter(array_map('intval', $exclude_post_ids), static function ($id) {
        return $id > 0;
    }));
}

$has_posts_per_page_field = is_array(get_sub_field_object('posts_per_page'));
$posts_per_page = $has_posts_per_page_field
    ? ((int) get_sub_field('posts_per_page') ?: (int) get_option('posts_per_page') ?: 12)
    : ((int) get_option('posts_per_page') ?: 12);

$has_show_pagination_field = is_array(get_sub_field_object('show_pagination'));
$show_pagination = $has_show_pagination_field ? (bool) get_sub_field('show_pagination') : true;
$background_color    = get_sub_field('background_color') ?: '#ffffff';
$layout_option       = get_sub_field('layout_option') ?: 'layout_1';
$is_layout_2         = $layout_option === 'layout_2';

// Padding settings
$padding_classes = [];
if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
        the_row();
        $screen_size    = get_sub_field('screen_size');
        $padding_top    = get_sub_field('padding_top');
        $padding_bottom = get_sub_field('padding_bottom');
        $padding_classes[] = "{$screen_size}:pt-[{$padding_top}rem]";
        $padding_classes[] = "{$screen_size}:pb-[{$padding_bottom}rem]";
    }
}

// Get current page for pagination (supports both paged and page vars)
$paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
$search_query = is_search() ? get_search_query() : '';
if ($search_query === '' && isset($_GET['s'])) {
    $search_query = sanitize_text_field(wp_unslash($_GET['s']));
}
$selected_filter_slug = '';
if (isset($_GET['blog_category'])) {
    $selected_filter_slug = sanitize_title((string) wp_unslash($_GET['blog_category']));
}
$queried_object = get_queried_object();
$no_posts_message = 'No posts found.';

// Query posts
$args = array(
    'post_type'      => 'post',
    'posts_per_page' => $posts_per_page,
    'paged'          => $paged,
    'post_status'    => 'publish'
);
if (!empty($exclude_post_ids)) {
    $args['post__not_in'] = $exclude_post_ids;
}
if (!empty($exclude_category_ids)) {
    $args['category__not_in'] = $exclude_category_ids;
}
if ($search_query !== '') {
    $args['s'] = $search_query;
}
if (is_category() && $queried_object instanceof WP_Term && $queried_object->taxonomy === 'category') {
    $args['cat'] = (int) $queried_object->term_id;
    $category_label = trim((string) $queried_object->name);
    if ($category_label !== '') {
        $no_posts_message = sprintf('No %s found.', strtolower($category_label));
    }
} elseif ($limit_to_category_id > 0) {
    // Default to the configured category unless a valid child filter is selected below.
    $args['cat'] = $limit_to_category_id;
} elseif ($selected_filter_slug !== '') {
    $selected_filter_term = get_term_by('slug', $selected_filter_slug, 'category');
    if ($selected_filter_term instanceof WP_Term) {
        $args['cat'] = (int) $selected_filter_term->term_id;
        $selected_filter_slug = (string) $selected_filter_term->slug;
        $category_label = trim((string) $selected_filter_term->name);
        if ($category_label !== '') {
            $no_posts_message = sprintf('No %s found.', strtolower($category_label));
        }
    } else {
        $selected_filter_slug = '';
    }
}

// If this block is limited to a category, only allow filtering to that category or its descendants.
if (!is_category() && $limit_to_category_id > 0 && $selected_filter_slug !== '') {
    $selected_filter_term = get_term_by('slug', $selected_filter_slug, 'category');
    if ($selected_filter_term instanceof WP_Term) {
        $allowed_ids = get_term_children($limit_to_category_id, 'category');
        $allowed_ids = array_map('intval', is_array($allowed_ids) ? $allowed_ids : []);
        $allowed_ids[] = (int) $limit_to_category_id;

        if (in_array((int) $selected_filter_term->term_id, $exclude_category_ids, true)) {
            $selected_filter_slug = '';
            $args['cat'] = $limit_to_category_id;
        } elseif (
            $show_media_subcategory_filters_only
            && !in_array((string) $selected_filter_term->slug, $media_filter_slugs, true)
        ) {
            $selected_filter_slug = '';
            $args['cat'] = $limit_to_category_id;
        } elseif (in_array((int) $selected_filter_term->term_id, $allowed_ids, true)) {
            $args['cat'] = (int) $selected_filter_term->term_id;
        } else {
            $selected_filter_slug = '';
            $args['cat'] = $limit_to_category_id;
        }
    } else {
        $selected_filter_slug = '';
        $args['cat'] = $limit_to_category_id;
    }
}

$blog_query = new WP_Query($args);
$is_category_archive = is_category();
$initial_active_filter = $selected_filter_slug !== '' ? $selected_filter_slug : 'all';
$pagination_threshold = 12;

// Get categories for filters
$categories_args = array(
    'hide_empty' => true,
    'orderby'    => 'name',
    'order'      => 'ASC',
);

if (!$is_category_archive && $limit_to_category_id > 0) {
    // When locked to a category, show only its child categories as filters.
    $categories_args['parent'] = $limit_to_category_id;
}

$categories = get_categories($categories_args);
$categories = array_values(array_filter($categories, static function ($category) use ($exclude_category_ids) {
    return !in_array((int) $category->term_id, $exclude_category_ids, true);
}));
if (!$is_category_archive && $limit_to_category_id > 0 && $show_media_subcategory_filters_only) {
    $categories = array_values(array_filter($categories, static function ($category) use ($media_filter_slugs) {
        return in_array((string) $category->slug, $media_filter_slugs, true);
    }));

    usort($categories, static function ($a, $b) use ($media_filter_slugs) {
        $aIndex = array_search((string) $a->slug, $media_filter_slugs, true);
        $bIndex = array_search((string) $b->slug, $media_filter_slugs, true);
        $aIndex = $aIndex === false ? 999 : (int) $aIndex;
        $bIndex = $bIndex === false ? 999 : (int) $bIndex;
        return $aIndex <=> $bIndex;
    });
}

$section_id = 'blog-listing-' . uniqid();
$search_input_id = $section_id . '-search';
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="relative flex overflow-hidden <?php echo esc_attr(implode(' ', $padding_classes)); ?>"
    style="background-color: <?php echo esc_attr($background_color); ?>;"
    x-data="blogFilter('<?php echo esc_js($search_query); ?>', '<?php echo esc_js($initial_active_filter); ?>')"
    x-init="filterPosts()"
>
    <div class="flex flex-col items-center pt-5 lg:pt-[3.5rem] pb-5 mx-auto w-full max-w-container max-lg:px-5">

        <?php if (trim((string) $section_heading) !== '') : ?>
            <<?php echo esc_attr($section_heading_tag); ?>
                class="w-full font-['Public_Sans'] text-[24px] font-bold not-italic leading-[32px] text-[var(--Blue-SR-500,#00628F)] sm:text-[36px] sm:leading-[44px] sm:tracking-[-0.72px]"
            >
                <?php echo esc_html($section_heading); ?>
            </<?php echo esc_attr($section_heading_tag); ?>>
        <?php endif; ?>

        <!-- Filters and Search Section -->
        <div class="grid grid-cols-1 gap-6 items-center pb-4 w-full text-sm leading-none md:grid-cols-[60%_40%]">

            <?php if ($show_filters && !empty($categories)): ?>
                <!-- Filters -->
                <div
                    class="flex gap-4 items-center self-stretch my-auto min-w-0 w-full <?php echo $is_category_archive ? 'invisible' : ''; ?>"
                    <?php if ($is_category_archive) : ?>aria-hidden="true"<?php endif; ?>
                >
                    <div class="self-stretch my-auto text-sky-950">
                        Filter by:
                    </div>

                    <div class="relative flex-1 min-w-0">
                        <div
                            class="chip-slider flex gap-2 items-center self-stretch my-auto font-semibold text-sky-800 overflow-x-auto whitespace-nowrap pr-6"
                            role="radiogroup"
                            aria-label="Filter posts by category"
                            data-chip-slider
                        >
                            <button
                                type="button"
                                role="radio"
                                class="shrink-0 flex flex-col justify-center items-center self-stretch px-4 py-2 my-auto whitespace-nowrap rounded-full border-2 border-sky-600 min-h-9 w-fit transition-colors duration-200 focus:ring-sky-600"
                                data-filter-option="all"
                                :class="activeFilter === 'all'
                                    ? 'bg-sky-700 text-white'
                                    : 'bg-white text-sky-900 hover:bg-[#d7ebf7] hover:text-sky-900'"
                                @click="setFilter('all')"
                                :aria-checked="activeFilter === 'all' ? 'true' : 'false'"
                                :tabindex="activeFilter === 'all' ? '0' : '-1'"
                                @keydown="onFilterKeydown($event, 'all')"
                            >
                                All
                            </button>

                            <?php foreach ($categories as $category): ?>
                                <button
                                    type="button"
                                    role="radio"
                                    class="shrink-0 flex flex-col justify-center items-center self-stretch px-4 py-2 my-auto whitespace-nowrap rounded-full min-h-9 w-fit transition-colors duration-200 focus:ring-sky-600"
                                    data-filter-option="<?php echo esc_attr($category->slug); ?>"
                                    :class="activeFilter === '<?php echo esc_attr($category->slug); ?>'
                                        ? 'bg-sky-700 text-white'
                                        : 'bg-[#d7ebf7] text-sky-900 hover:bg-[#b9dcf1] hover:text-sky-900'"
                                    @click="setFilter('<?php echo esc_attr($category->slug); ?>')"
                                    :aria-checked="activeFilter === '<?php echo esc_attr($category->slug); ?>' ? 'true' : 'false'"
                                    :tabindex="activeFilter === '<?php echo esc_attr($category->slug); ?>' ? '0' : '-1'"
                                    @keydown="onFilterKeydown($event, '<?php echo esc_attr($category->slug); ?>')"
                                >
                                    <?php echo esc_html($category->name); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <span class="pointer-events-none absolute right-0 top-0 h-full w-8 bg-gradient-to-l from-white to-transparent"></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($show_search): ?>
                <!-- Search -->
                <div class="flex self-stretch my-auto w-full min-h-[60px] items-center lg:justify-end">
                    <div class="flex-1 shrink basis-12 min-w-0 w-full max-w-[396px] shadow-[0px_0px_20px_rgba(63,0,119,0.07)] rounded-r-[30px] overflow-hidden">
                        <div class="w-full">
                            <div class="flex items-center">
                                <div 
                                class="flex flex-1 gap-2 items-center self-stretch my-auto w-full shrink basis-0 min-w-60 px-4 py-2 max-h-[60px] rounded-l-[4px] border border-transparent bg-white transition-all duration-200 hover:bg-[#CBF3F6] hover:border-[#1C959B] focus-within:bg-[#C2EDFF] focus-within:border-[#C2EDFF]">
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
                                        x-model="searchTerm"
                                        @input="filterPosts()"
                                        aria-label="Search posts"
                                    />
                                    <label for="<?php echo esc_attr($search_input_id); ?>" class="sr-only">Search posts</label>
                                </div>

                                <button
                                    type="button"
                                    class="flex gap-2 justify-center items-center px-6 py-4 h-full min-h-[60px] font-bold whitespace-nowrap bg-yellow-300 text-slate-800 max-md:px-5 w-fit hover:bg-[#FCF4C5] focus:ring-yellow-300 border-0 rounded-r-[100px] shadow-none"
                                    @click="filterPosts()"
                                    aria-label="Search posts"
                                >
                                    Search
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
        </div>

        <!-- Blog Posts Grid -->
        <section class="flex flex-col mt-4 w-full max-md:max-w-full" aria-label="Blog posts">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-6 lg:gap-y-24 w-full min-h-[454px] max-md:max-w-full pb-16" id="posts-container" data-disable-nav-offset="true">

                <?php if ($blog_query->have_posts()): ?>
                    <?php while ($blog_query->have_posts()): $blog_query->the_post(); ?>
                        <?php
                        $post_id         = get_the_ID();
                        $post_categories = get_the_category($post_id);
                        $category_slugs  = array();
                        $primary_category = null;
                        $queried_category_slug = (is_category() && $queried_object instanceof WP_Term && $queried_object->taxonomy === 'category')
                            ? (string) $queried_object->slug
                            : '';

                        $ordered_categories = $post_categories;
                        if (!empty($post_categories)) {
                            foreach ($post_categories as $cat) {
                                $category_slugs[] = $cat->slug;
                                // On category archives, prefer showing the currently viewed category as the badge.
                                if ($queried_category_slug !== '' && $cat->slug === $queried_category_slug) {
                                    $primary_category = $cat;
                                }
                            }
                            if (!$primary_category) {
                                $primary_category = $post_categories[0];
                            }
                            if ($primary_category instanceof WP_Term) {
                                $ordered_categories = array_values(array_merge(
                                    [$primary_category],
                                    array_filter($post_categories, static function ($cat) use ($primary_category) {
                                        return $cat instanceof WP_Term && (int) $cat->term_id !== (int) $primary_category->term_id;
                                    })
                                ));
                            }
                        }

                        $featured_image = get_post_thumbnail_id($post_id);
                        $image_alt      = get_post_meta($featured_image, '_wp_attachment_image_alt', true) ?: get_the_title();
                        $post_date      = get_the_date('j M Y');
                        $reading_time   = '12 mins';
                        $post_permalink = get_permalink($post_id);
                        $has_media_listing_category = function_exists('matrix_post_has_media_category')
                            ? matrix_post_has_media_category($post_id)
                            : in_array('press-releases', $category_slugs, true);
                        $press_logo_custom_id = (int) get_field('post_listing_logo_custom', $post_id);
                        $press_logo_quick_select = trim((string) get_field('post_listing_logo_quick_select', $post_id));
                        $press_logo_bg_raw = trim((string) get_field('post_listing_logo_bg_color', $post_id));
                        $press_logo_bg = sanitize_hex_color($press_logo_bg_raw) ?: '#FFFFFF';
                        $press_logo_bg_style = $has_media_listing_category ? 'background-color: ' . $press_logo_bg . ';' : '';
                        $use_press_logo_override = $has_media_listing_category && ($press_logo_custom_id > 0 || $press_logo_quick_select !== '');
                        $featured_image_fit = (string) get_field('post_featured_image_fit', $post_id);
                        $is_story_post = in_array('stories', $category_slugs, true);
                        $resolved_fit = in_array($featured_image_fit, ['landscape', 'portrait'], true)
                            ? $featured_image_fit
                            : ($is_story_post ? 'portrait' : 'landscape');
                        $featured_image_object_class = $resolved_fit === 'portrait' ? 'object-contain' : 'object-cover';
                        $external_source_link = get_field('post_external_source_link', $post_id);
                        $open_external_source = get_field('post_listing_open_external_source', $post_id);
                        $open_external_source = ($open_external_source === 1 || $open_external_source === '1' || $open_external_source === true);
                        $has_external_source_url = is_array($external_source_link) && !empty($external_source_link['url']);
                        $auto_external_for_press_release = $has_media_listing_category && $has_external_source_url;
                        if ($auto_external_for_press_release) {
                            $open_external_source = true;
                        }
                        $card_target_url = $post_permalink;
                        $card_target_window = '_self';
                        $card_rel = '';

                        if ($open_external_source && $has_external_source_url) {
                            $card_target_url = (string) $external_source_link['url'];
                            $card_target_window = '_blank';
                            $card_rel = 'noopener noreferrer';
                        }
                        ?>

                        <article
                            class="overflow-hidden flex-1 bg-yellow-50 rounded-[4px] shrink basis-0 min-w-60 post-item cursor-pointer transition-all duration-200 hover:bg-[#FCF4C5] hover:shadow-[0_0_0_4px_#009DE6]"
                            data-categories="<?php echo esc_attr(implode(' ', $category_slugs)); ?>"
                            data-title="<?php echo esc_attr(strtolower(get_the_title())); ?>"
                            data-url="<?php echo esc_url($card_target_url); ?>"
                            data-url-target="<?php echo esc_attr($card_target_window); ?>"
                            tabindex="0"
                            aria-label="<?php echo esc_attr('Open post: ' . wp_strip_all_tags(get_the_title())); ?>"
                        >
                            <!-- Featured Image with Tag -->
                            <div class="flex overflow-hidden relative flex-col gap-2.5 items-start pt-6 pb-44 w-full text-xs font-bold text-sky-800 whitespace-nowrap aspect-[1.565] min-h-[232px] max-md:pb-24 rounded-t-[8px]">
                                <?php if ($use_press_logo_override): ?>
                                    <?php if ($press_logo_custom_id > 0): ?>
                                        <?php echo wp_get_attachment_image($press_logo_custom_id, 'large', false, [
                                            'alt'     => esc_attr($image_alt),
                                            'class'   => 'object-contain absolute inset-0 size-full',
                                            'style'   => $press_logo_bg_style,
                                            'loading' => 'lazy'
                                        ]); ?>
                                    <?php else: ?>
                                        <img
                                            src="<?php echo esc_url($press_logo_quick_select); ?>"
                                            alt="<?php echo esc_attr($image_alt); ?>"
                                            class="object-contain absolute inset-0 size-full"
                                            style="<?php echo esc_attr($press_logo_bg_style); ?>"
                                            loading="lazy"
                                            decoding="async"
                                        />
                                    <?php endif; ?>
                                <?php elseif ($featured_image): ?>
                                    <?php echo wp_get_attachment_image($featured_image, 'large', false, [
                                        'alt'     => esc_attr($image_alt),
                                        'class'   => $has_media_listing_category
                                            ? 'object-contain absolute inset-0 size-full'
                                            : $featured_image_object_class . ' absolute inset-0 size-full',
                                        'style'   => $press_logo_bg_style,
                                        'loading' => 'lazy'
                                    ]); ?>
                                <?php endif; ?>

                            </div>

                            <!-- Post Content -->
                            <div class="flex flex-col p-6 w-full text-sm text-sky-950 max-md:px-5">
                                <?php if (!empty($ordered_categories)) : ?>
                                    <div class="mb-3 flex flex-wrap gap-2 items-center self-start">
                                        <?php foreach ($ordered_categories as $cat) :
                                            if (!$cat instanceof WP_Term) {
                                                continue;
                                            }
                                            $chip_bg_custom = sanitize_hex_color((string) get_field('category_chip_bg_color', $cat));
                                            $chip_text_custom = sanitize_hex_color((string) get_field('category_chip_text_color', $cat));
                                            $chip_classes = 'bg-sky-200 text-sky-900';
                                            if ($cat->slug === 'press-releases') {
                                                $chip_classes = 'bg-[#D7EDF8] text-sky-900';
                                            } elseif ($cat->slug === 'news' || $cat->slug === 'our-news') {
                                                $chip_classes = 'bg-[#9EDCF6] text-sky-900';
                                            } elseif ($cat->slug === 'event') {
                                                $chip_classes = 'bg-[#FBEA5E] text-slate-800';
                                            }
                                            $chip_inline_style = '';
                                            if ($chip_bg_custom) {
                                                $chip_inline_style .= 'background-color:' . $chip_bg_custom . ';';
                                            }
                                            if ($chip_text_custom) {
                                                $chip_inline_style .= 'color:' . $chip_text_custom . ';';
                                            }
                                        ?>
                                            <a
                                                href="<?php echo esc_url(get_category_link($cat->term_id)); ?>"
                                                class="inline-flex items-center rounded-[100px] px-3 py-1 text-xs font-bold leading-none transition-all duration-200 hover:shadow-[0_0_0_4px_var(--Mint-500,#87CEB7)] focus:outline-none focus-visible:ring-0 <?php echo esc_attr($chip_classes); ?>"
                                                aria-label="<?php echo esc_attr('View category: ' . $cat->name); ?>"
                                                <?php echo $chip_inline_style !== '' ? ' style="' . esc_attr($chip_inline_style) . '"' : ''; ?>
                                            >
                                                <?php echo esc_html($cat->name); ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <div class="flex gap-2 items-start self-start leading-none <?php echo $is_layout_2 ? 'hidden' : ''; ?>">
                                    <time class="text-sky-950" datetime="<?php echo esc_attr(get_the_date('Y-m-d')); ?>">
                                        <?php echo esc_html($post_date); ?>
                                    </time>
                                    <span class="text-sky-950" aria-hidden="true">|</span>
                                    <span class="text-sky-950">
                                        Read time <?php echo esc_html($reading_time); ?>
                                    </span>
                                </div>

                                <h2 class="mt-2 font-sans text-[18px] font-bold not-italic leading-[24px] <?php echo $is_layout_2 ? 'text-[#F68DA7]' : 'text-sky-800'; ?>">
                                    <a
                                        href="<?php echo esc_url($card_target_url); ?>"
                                        target="<?php echo esc_attr($card_target_window); ?>"
                                        <?php if (!empty($card_rel)) : ?>rel="<?php echo esc_attr($card_rel); ?>"<?php endif; ?>
                                        class="hover:underline focus:underline"
                                    >
                                        <?php the_title(); ?>
                                    </a>
                                </h2>

                                <div class="mt-2 leading-5 text-sky-950">
                                    <?php echo esc_html(wp_trim_words(get_the_excerpt(), 20)); ?>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                    <?php wp_reset_postdata(); ?>
                <?php else: ?>
                    <div class="py-12 w-full text-center">
                        <p class="text-lg text-gray-600"><?php echo esc_html($no_posts_message); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($show_pagination && $blog_query->max_num_pages > 1 && (int) $blog_query->found_posts > (int) $pagination_threshold): ?>
                <!-- Pagination -->
                <nav
                    class="flex flex-wrap gap-3 md:gap-8 justify-evenly md:justify-center items-center self-center max-w-full pt-8 pb-6 md:pt-20 md:pb-12 mt-6 text-base leading-none text-sky-600 whitespace-nowrap"
                    aria-label="Posts pagination"
                >
                    <!-- Previous Button -->
                    <?php if ($paged > 1): ?>
                        <a
                            href="<?php echo esc_url(get_pagenum_link($paged - 1)); ?>"
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
    $total_pages  = $blog_query->max_num_pages;
    $current_page = $paged;

    $pagination_link_class = 'flex flex-col justify-center items-center self-stretch my-auto w-12 min-h-12 rounded-full text-sky-600 transition-all duration-200 hover:bg-[#009DE6] hover:text-white hover:shadow-[0_0_0_4px_#009DE6] focus:bg-[#009DE6] focus:text-white focus:shadow-[0_0_0_4px_#009DE6]';

    $pagination_current_class = 'flex flex-col justify-center items-center self-stretch my-auto w-12 min-h-12 rounded-full text-white bg-[#009DE6] shadow-[0_0_0_4px_#009DE6]';
    ?>

    <?php if ($current_page == 1): ?>
        <div class="<?php echo esc_attr($pagination_current_class); ?>">
            <span aria-current="page">1</span>
        </div>
    <?php else: ?>
        <a
            href="<?php echo esc_url(get_pagenum_link(1)); ?>"
            class="<?php echo esc_attr($pagination_link_class); ?>"
            aria-label="Go to page 1"
        >
            <span>1</span>
        </a>
    <?php endif; ?>

    <?php for ($i = max(2, $current_page - 2); $i <= min($total_pages - 1, $current_page + 2); $i++): ?>
        <?php if ($i == $current_page): ?>
            <div class="<?php echo esc_attr($pagination_current_class); ?>">
                <span aria-current="page"><?php echo esc_html($i); ?></span>
            </div>
        <?php else: ?>
            <a
                href="<?php echo esc_url(get_pagenum_link($i)); ?>"
                class="<?php echo esc_attr($pagination_link_class); ?>"
                aria-label="Go to page <?php echo esc_attr($i); ?>"
            >
                <span><?php echo esc_html($i); ?></span>
            </a>
        <?php endif; ?>
    <?php endfor; ?>

    <?php if ($total_pages > 5 && $current_page < $total_pages - 2): ?>
        <div class="flex flex-col justify-center items-start self-stretch py-1 my-auto w-6">
            <span class="text-sky-600" aria-hidden="true">...</span>
        </div>
        <a
            href="<?php echo esc_url(get_pagenum_link($total_pages)); ?>"
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
function blogFilter(initialSearchTerm = '', initialFilter = 'all') {
    return {
        activeFilter: initialFilter || 'all',
        searchTerm: initialSearchTerm || '',
        filterOptions: ['all', <?php
            $slugs = array_map(static function($category) {
                return "'" . esc_js((string) $category->slug) . "'";
            }, $categories);
            echo implode(', ', $slugs);
        ?>],

        setFilter(filter) {
            if (this.activeFilter === filter) return;
            this.activeFilter = filter;

            const url = new URL(window.location.href);
            if (filter === 'all') {
                url.searchParams.delete('blog_category');
            } else {
                url.searchParams.set('blog_category', filter);
            }
            url.searchParams.delete('paged');
            url.searchParams.delete('page');
            window.location.href = url.toString();
        },

        onFilterKeydown(event, currentFilter) {
            const key = event.key;
            if (!['ArrowRight', 'ArrowDown', 'ArrowLeft', 'ArrowUp', 'Home', 'End', 'Enter', ' '].includes(key)) {
                return;
            }

            if (key === 'Enter' || key === ' ') {
                event.preventDefault();
                this.setFilter(currentFilter);
                return;
            }

            event.preventDefault();
            const options = this.filterOptions || [];
            if (!options.length) return;

            let currentIndex = options.indexOf(currentFilter);
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

            const nextFilter = options[nextIndex];
            if (!nextFilter) return;

            this.activeFilter = nextFilter;
            this.$nextTick(() => {
                const nextBtn = this.$root.querySelector('[data-filter-option="' + nextFilter + '"]');
                if (nextBtn) nextBtn.focus();
            });
            this.setFilter(nextFilter);
        },

        filterPosts() {
            const posts = document.querySelectorAll('#<?php echo esc_js($section_id); ?> .post-item');

            posts.forEach(post => {
                const categories = post.dataset.categories || '';
                const title = post.dataset.title || '';

                const matchesFilter = this.activeFilter === 'all' || categories.includes(this.activeFilter);
                const matchesSearch = this.searchTerm === '' || title.includes(this.searchTerm.toLowerCase());

                if (matchesFilter && matchesSearch) {
                    post.style.display = 'block';
                } else {
                    post.style.display = 'none';
                }
            });
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
    var cards = document.querySelectorAll('#<?php echo esc_js($section_id); ?> .post-item[data-url]');
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