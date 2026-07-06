<?php
/**
 * Flexi Block: Stories Slider
 */

$section_id       = get_sub_field('section_id') ?: 'stories-slider-' . get_the_ID();
$section_class    = get_sub_field('section_class');
$heading          = get_sub_field('heading') ?: 'Stories';
$heading_tag      = get_sub_field('heading_tag') ?: 'h2';
$read_all_link    = get_sub_field('read_all_link');
$background_image = get_sub_field('background_image');
$slider_background_image = get_sub_field('slider_background_image');
$posts_per_page   = get_sub_field('posts_per_page') ?: 8;
$show_excerpt     = get_sub_field('show_excerpt');

$allowed_tags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'span'];
if (!in_array($heading_tag, $allowed_tags, true)) {
    $heading_tag = 'h2';
}

$background_style = '';
if (!empty($background_image) && !empty($background_image['url'])) {
    $background_style = "background-image: url('" . esc_url($background_image['url']) . "'); background-size: cover; background-position: center; background-repeat: no-repeat;";
}

$stories_query = new WP_Query([
    'post_type'           => 'post',
    'posts_per_page'      => (int) $posts_per_page,
    'post_status'         => 'publish',
    'ignore_sticky_posts' => true,
    'category_name'       => 'stories',
]);

$has_posts         = $stories_query->have_posts();
$slide_images      = [];
$default_slider_bg = '';

if (!empty($slider_background_image) && !empty($slider_background_image['url'])) {
    $default_slider_bg = $slider_background_image['url'];
}

// Collect slide images in a single pass
if ($has_posts) {
    foreach ($stories_query->posts as $p) {
        $slide_images[] = get_the_post_thumbnail_url($p->ID, 'large') ?: '';
    }
}
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    style="<?php echo esc_attr($background_style); ?>"
    class="py-12 lg:pt-[5rem] lg:pb-[9rem] <?php echo esc_attr($section_class); ?> max-xl:px-5"
    aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
>
    <div class="mx-auto max-w-[1024px] w-full">

        <!-- Header row: heading + desktop CTA -->
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <<?php echo esc_html($heading_tag); ?>
                id="<?php echo esc_attr($section_id); ?>-heading"
                class="font-display text-center text-[24px] not-italic font-bold leading-[32px] tracking-[-0.72px] text-brand-blue md:text-left md:text-[36px] md:leading-[1.1] md:tracking-[-0.04em] lg:text-[44px]"
            >
                <?php echo esc_html($heading); ?>
            </<?php echo esc_html($heading_tag); ?>>

            <?php if (!empty($read_all_link['url']) && !empty($read_all_link['title'])) : ?>
                <a
                    href="<?php echo esc_url($read_all_link['url']); ?>"
                    target="<?php echo esc_attr($read_all_link['target'] ?: '_self'); ?>"
                    class="hidden min-[1085px]:inline-flex h-[42px] justify-center items-center gap-2 py-4 pl-6 pr-4 font-['Public_Sans'] text-[14px] font-bold leading-5 text-brand-primary-hover rounded-pill bg-white hover:bg-brand-accent-soft active:bg-brand-accent-strong focus:outline-none focus-visible:ring-0 focus-visible:border-[3px] focus-visible:border-brand-accent focus-visible:bg-white transition-colors duration-200"
                >
                    <?php echo esc_html($read_all_link['title']); ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                        <path d="M1 5.66667H10.3333M10.3333 5.66667L5.66667 1M10.3333 5.66667L5.66667 10.3333" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            <?php endif; ?>
        </div>

        <!-- Slider wrapper -->
        <div
            class="stories-slider-wrap relative mt-4 rounded-[12px] shadow-story overflow-visible"
            data-slide-images="<?php echo esc_attr(wp_json_encode($slide_images)); ?>"
            data-default-bg="<?php echo esc_attr($default_slider_bg); ?>"
        >
            <!-- Crossfade background layers -->
            <div class="absolute inset-0 z-0 pointer-events-none overflow-hidden rounded-[12px]">
                <div
                    class="absolute inset-0 bg-center bg-no-repeat bg-cover transition-opacity duration-500 stories-bg stories-bg-a"
                    style="background-color: #081226;<?php echo $default_slider_bg ? " background-image: url('" . esc_url($default_slider_bg) . "');" : ''; ?>"
                ></div>
                <div
                    class="absolute inset-0 bg-center bg-no-repeat bg-cover opacity-0 transition-opacity duration-500 stories-bg stories-bg-b"
                    style="background-color: #081226;"
                ></div>
                <div class="absolute inset-x-0 bottom-0 z-10 h-2/3 bg-gradient-to-t to-transparent pointer-events-none from-black/40"></div>
            </div>

            <!-- Nav arrows (desktop only) -->
            <?php if ($has_posts) : ?>
                <button
                    type="button"
                    class="group stories-prev absolute left-3 top-1/2 z-20 hidden h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-white shadow-[0_8px_24px_rgba(0,0,0,0.12)] transition hover:opacity-100 hover:bg-[#009DE6] xl:-left-6 md:flex"
                    aria-label="Previous stories"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M15 8H1M1 8L8 15M1 8L8 1"
                              class="transition-colors duration-200 group-hover:stroke-white"
                              stroke="#1D2939"
                              stroke-width="2"
                              stroke-linecap="round"
                              stroke-linejoin="round"/>
                    </svg>
                </button>

                <button
                    type="button"
                    class="group stories-next absolute right-3 top-1/2 z-20 hidden h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-white shadow-[0_8px_24px_rgba(0,0,0,0.12)] transition hover:opacity-100 hover:bg-[#009DE6] xl:-right-6 md:flex"
                    aria-label="Next stories"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M5 12H19M19 12L12 5M19 12L12 19"
                              class="transition-colors duration-200 group-hover:stroke-white"
                              stroke="#1D2939"
                              stroke-width="2"
                              stroke-linecap="round"
                              stroke-linejoin="round"/>
                    </svg>
                </button>
            <?php endif; ?>

            <div class="relative z-10 min-h-[480px] md:min-h-[500px] flex flex-col justify-end">
                <?php if ($has_posts) : ?>

                    <!-- ── DESKTOP: Slick slider ── -->
                    <div class="hidden px-8 pb-8 w-full md:block">
                        <div class="js-stories-slider">
                            <?php $slide_index = 0; while ($stories_query->have_posts()) : $stories_query->the_post(); ?>
                                <?php
                                $post_id   = get_the_ID();
                                $image_url = get_the_post_thumbnail_url($post_id, 'medium_large');
                                $image_id  = get_post_thumbnail_id($post_id);
                                $image_alt = $image_id ? get_post_meta($image_id, '_wp_attachment_image_alt', true) : '';
                                $image_alt = $image_alt ?: get_the_title($post_id);
                                $excerpt   = wp_trim_words(get_the_excerpt(), 12, '…');
                                ?>
                                <div class="px-2 stories-slide" data-slide-index="<?php echo esc_attr((string) $slide_index); ?>">
                                    <article class="flex h-full gap-3 rounded-[12px] bg-white p-4 shadow-md">
                                        <?php if ($image_url) : ?>
                                            <img
                                                src="<?php echo esc_url($image_url); ?>"
                                                alt="<?php echo esc_attr($image_alt); ?>"
                                                <?php echo $slide_index === 0 ? 'loading="eager"' : 'loading="lazy"'; ?>
                                                class="h-[100px] w-[100px] shrink-0 rounded-[8px] object-cover self-start"
                                            />
                                        <?php endif; ?>
                                        <div class="flex flex-col flex-1 gap-3 justify-between items-start min-w-0">
                                            <p class="font-sans text-[14px] font-normal leading-[20px] text-[#475467]">
                                                <?php echo esc_html('"' . $excerpt . '"'); ?>
                                            </p>
                                            <a
                                                href="<?php the_permalink(); ?>"
                                                class="inline-flex justify-center items-center gap-2 px-5 py-2.5 w-full font-['Public_Sans'] text-[12px] not-italic font-bold leading-[18px] text-white rounded-pill md:w-fit whitespace-nowrap bg-brand-primary border-0 hover:border-0 hover:bg-brand-primary-hover transition-colors duration-200"
                                            >
                                                <?php echo esc_html('Read story'); ?>
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                    <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </article>
                                </div>
                            <?php $slide_index++; endwhile; ?>
                        </div>
                    </div>


                    <!-- ── MOBILE: CSS scroll snap ── -->
                    <div class="pb-4 w-full md:hidden">
                        <div class="stories-scroll-track">
                            <?php foreach ($stories_query->posts as $i => $p) :
                                $img_url = get_the_post_thumbnail_url($p->ID, 'medium_large') ?: '';
                                $img_id  = get_post_thumbnail_id($p->ID);
                                $img_alt = $img_id ? get_post_meta($img_id, '_wp_attachment_image_alt', true) : '';
                                $img_alt = $img_alt ?: get_the_title($p->ID);
                                $exc     = wp_trim_words(get_the_excerpt($p->ID), 12, '…');
                            ?>
                                <div class="stories-scroll-slide" data-mobile-index="<?php echo esc_attr((string) $i); ?>">
                                    <article class="flex gap-3 rounded-[12px] bg-white p-3 shadow-md h-full">
                                        <?php if ($img_url) : ?>
                                            <img
                                                src="<?php echo esc_url($img_url); ?>"
                                                alt="<?php echo esc_attr($img_alt); ?>"
                                                loading="<?php echo $i === 0 ? 'eager' : 'lazy'; ?>"
                                                class="h-[80px] w-[80px] shrink-0 rounded-[8px] object-cover self-start"
                                            />
                                        <?php endif; ?>
                                        <div class="flex flex-col flex-1 gap-2 justify-between items-start min-w-0">
                                            <p class="font-sans text-[14px] font-normal leading-[20px] text-[#475467]">
                                                <?php echo esc_html('"' . $exc . '"'); ?>
                                            </p>
                                            <a
                                                href="<?php echo esc_url(get_permalink($p->ID)); ?>"
                                                class="inline-flex justify-center items-center gap-2 px-4 py-2 w-full  font-['Public_Sans'] text-[12px] not-italic font-bold leading-[18px] text-white rounded-full btn-primary whitespace-nowrap"
                                            >
                                                <?php echo esc_html('Read story'); ?>
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                    <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </article>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                <?php else : ?>
                    <p class="p-8 text-white">No story posts found in the stories category.</p>
                <?php endif; ?>
            </div>
        </div>
        <!-- END slider wrapper -->

        <?php wp_reset_postdata(); ?>

        <?php if ($has_posts) : ?>
            <!-- Mobile dots + CTA — outside the rounded container -->
            <div class="flex flex-col gap-5 justify-center items-center mt-5 min-[1085px]:hidden">
                <div class="stories-mobile-dots md:hidden"></div>

                <?php if (!empty($read_all_link['url']) && !empty($read_all_link['title'])) : ?>
                    <a
                        href="<?php echo esc_url($read_all_link['url']); ?>"
                        target="<?php echo esc_attr($read_all_link['target'] ?: '_self'); ?>"
                        class="inline-flex h-[52px] items-center justify-center gap-2 rounded-pill bg-white px-6 text-[16px] font-bold leading-5 text-brand-primary-hover transition-colors duration-200 hover:bg-brand-accent-soft active:bg-brand-accent-strong focus:outline-none focus-visible:ring-0"
                    >
                        <?php echo esc_html($read_all_link['title']); ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php if ($has_posts) : ?>
<style>
    /* ── Hide nav arrows on mobile ── */
    @media (max-width: 767px) {
        #<?php echo esc_attr($section_id); ?> .stories-prev,
        #<?php echo esc_attr($section_id); ?> .stories-next {
            display: none !important;
        }
    }

    /* ── Desktop: keep left side clipped, extend viewport to right only ── */
    #<?php echo esc_attr($section_id); ?> .js-stories-slider .slick-list {
        overflow: hidden !important;
        width: calc(100% + 200px);
        max-width: none;
        margin-left: 0 !important;
        padding-left: 0 !important;
    }

    /* ── Desktop: equal height Slick slides ── */
    #<?php echo esc_attr($section_id); ?> .js-stories-slider .slick-track {
        display: flex !important;
        gap: 0 !important;
    }
    #<?php echo esc_attr($section_id); ?> .js-stories-slider .slick-slide {
        height: inherit !important;
    }
    #<?php echo esc_attr($section_id); ?> .js-stories-slider .slick-slide > div,
    #<?php echo esc_attr($section_id); ?> .stories-slide,
    #<?php echo esc_attr($section_id); ?> .stories-slide article {
        height: 100%;
    }

    /* ── Mobile: CSS scroll snap ── */
    #<?php echo esc_attr($section_id); ?> .stories-scroll-track {
        --mobile-story-card: clamp(250px, calc(100% - 104px), 300px);
        display: flex;
        overflow-x: scroll;
        overflow-y: visible;
        scroll-snap-type: x mandatory;
        -webkit-overflow-scrolling: touch;
        touch-action: pan-x;
        overscroll-behavior-x: contain;
        cursor: grab;
        scrollbar-width: none;
        /* Center first card and keep a visible peek of the next card */
        padding-inline: calc((100% - var(--mobile-story-card)) / 2);
        scroll-padding-left: calc((100% - var(--mobile-story-card)) / 2);
        gap: 12px;
    }
    #<?php echo esc_attr($section_id); ?> .stories-scroll-track:active {
        cursor: grabbing;
    }
    #<?php echo esc_attr($section_id); ?> .stories-scroll-track::-webkit-scrollbar {
        display: none;
    }
    /* Mobile: one primary card with next card peeking on the right */
    #<?php echo esc_attr($section_id); ?> .stories-scroll-slide {
        flex: 0 0 var(--mobile-story-card);
        scroll-snap-align: start;
        scroll-snap-stop: always;
    }
    #<?php echo esc_attr($section_id); ?> .stories-scroll-slide > article {
        width: 100%;
    }

    /* Keep mobile wrapper clean while preserving card peeking behavior */
    @media (max-width: 767px) {
        #<?php echo esc_attr($section_id); ?> .stories-slider-wrap {
            overflow: visible;
        }
    }

    /* Very small devices: reduce left gutter, preserve right-side peek */
    @media (max-width: 420px) {
        #<?php echo esc_attr($section_id); ?> .stories-scroll-track {
            --mobile-story-card: calc(100% - 56px);
            padding-left: 6px;
            padding-right: 28px;
            scroll-padding-left: 6px;
        }
    }

    /* ── Mobile dots ── */
    #<?php echo esc_attr($section_id); ?> .stories-mobile-dots {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 14px;
    }
    #<?php echo esc_attr($section_id); ?> .stories-mobile-dots button {
        width: 14px;
        height: 14px;
        padding: 0;
        border: 2px solid var(--color-brand-primary-hover, #00628F);
        border-radius: 9999px;
        background: transparent;
        cursor: pointer;
        flex-shrink: 0;
        transition: background 0.2s;
    }
    #<?php echo esc_attr($section_id); ?> .stories-mobile-dots button.is-active {
        background: var(--color-brand-primary-hover, #00628F);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var section = document.getElementById('<?php echo esc_js($section_id); ?>');
    if (!section) return;

    var $wrap = jQuery(section).find('.stories-slider-wrap');

    /* ── Background crossfade ── */
    var slideImages = [];
    var defaultBg   = '';
    try {
        var raw = $wrap.attr('data-slide-images');
        if (raw) slideImages = JSON.parse(raw);
        defaultBg = $wrap.attr('data-default-bg') || '';
    } catch (e) {}

    var bgActiveA = true, bgInitialized = false;

    function setStoriesBg(url) {
        var bgStyle = url ? "url('" + url + "')" : 'none';
        var $a = $wrap.find('.stories-bg-a');
        var $b = $wrap.find('.stories-bg-b');
        if (!bgInitialized) {
            $a.css('background-image', bgStyle).removeClass('opacity-0');
            $b.addClass('opacity-0');
            bgInitialized = true;
            return;
        }
        if (bgActiveA) {
            $b.css('background-image', bgStyle);
            $a.addClass('opacity-0'); $b.removeClass('opacity-0');
            bgActiveA = false;
        } else {
            $a.css('background-image', bgStyle);
            $a.removeClass('opacity-0'); $b.addClass('opacity-0');
            bgActiveA = true;
        }
    }

    setStoriesBg(defaultBg);

    /* ── DESKTOP: Slick ── */
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.slick !== 'undefined') {
        var $section = jQuery(section);
        var $slider  = $section.find('.js-stories-slider');

        function syncHiddenSlidesA11y($s) {
            if (!$s || !$s.length || !$s.hasClass('slick-initialized')) return;
            $s.find('.slick-slide').each(function () {
                var isHidden = this.getAttribute('aria-hidden') === 'true';
                this.setAttribute('tabindex', isHidden ? '-1' : '0');
                var focusables = this.querySelectorAll('a, button, input, textarea, select, [tabindex]');
                focusables.forEach(function (el) {
                    if (isHidden) {
                        el.setAttribute('tabindex', '-1');
                    } else if (el.getAttribute('tabindex') === '-1') {
                        el.removeAttribute('tabindex');
                    }
                });
            });
        }

        if ($slider.length && !$slider.hasClass('slick-initialized')) {
            $slider.slick({
                /* Desktop: 2 full cards + half of 3rd */
                slidesToShow   : 2.5,
                slidesToScroll : 1,
                infinite       : false,
                dots           : false,
                arrows         : true,
                prevArrow      : $section.find('.stories-prev'),
                nextArrow      : $section.find('.stories-next'),
                adaptiveHeight : false,
                responsive     : [
                    {
                        breakpoint : 1400,
                        settings   : { slidesToShow: 2.5, slidesToScroll: 1 }
                    },
                    {
                        breakpoint : 1280,
                        settings   : { slidesToShow: 2.35, slidesToScroll: 1 }
                    },
                    {
                        breakpoint : 1024,
                        settings   : { slidesToShow: 2.2, slidesToScroll: 1 }
                    }
                ]
            });
            syncHiddenSlidesA11y($slider);
            $slider.on('afterChange', function () {
                syncHiddenSlidesA11y($slider);
            });
        }

        $section.on('mouseenter', '.stories-slide', function () {
            if (window.innerWidth < 768) return;
            var idx = parseInt(jQuery(this).attr('data-slide-index'), 10);
            if (!isNaN(idx) && slideImages[idx]) setStoriesBg(slideImages[idx]);
        });
        $wrap.on('mouseleave', function () {
            if (window.innerWidth >= 768) setStoriesBg(defaultBg);
        });
    }

    /* ── MOBILE: scroll snap + dots ── */
    var track  = section.querySelector('.stories-scroll-track');
    var dotsEl = section.querySelector('.stories-mobile-dots');
    if (!track) return;

    var slides = Array.from(track.querySelectorAll('.stories-scroll-slide'));
    var total  = slides.length;
    if (total === 0) return;

    /* Build dots */
    var dots = [];
    for (var i = 0; i < total; i++) {
        var btn = document.createElement('button');
        btn.setAttribute('aria-label', 'Go to story ' + (i + 1));
        btn.setAttribute('data-i', String(i));
        if (i === 0) btn.classList.add('is-active');
        dotsEl.appendChild(btn);
        dots.push(btn);
    }

    function setActiveDot(idx) {
        dots.forEach(function (d, i) { d.classList.toggle('is-active', i === idx); });
    }

    /* Dot click: align slide to start (matches scroll-snap-align: start + 2+peek layout) */
    dotsEl.addEventListener('click', function (e) {
        var btn = e.target.closest('button[data-i]');
        if (!btn) return;
        var idx = parseInt(btn.getAttribute('data-i'), 10);
        var slide = slides[idx];
        var trackPad = parseFloat(window.getComputedStyle(track).paddingLeft) || 0;
        var targetScroll = Math.max(0, slide.offsetLeft - trackPad);
        track.scrollTo({ left: targetScroll, behavior: 'smooth' });
    });

    /* On scroll: find which slide center is closest to track center */
    var ticking = false;
    track.addEventListener('scroll', function () {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(function () {
            ticking = false;
            var trackCx = track.scrollLeft + track.offsetWidth / 2;
            var best = 0, bestDist = Infinity;
            slides.forEach(function (sl, i) {
                var dist = Math.abs((sl.offsetLeft + sl.offsetWidth / 2) - trackCx);
                if (dist < bestDist) { bestDist = dist; best = i; }
            });
            setActiveDot(best);
            setStoriesBg(slideImages[best] || defaultBg);
        });
    });
});
</script>
<?php endif; ?>
