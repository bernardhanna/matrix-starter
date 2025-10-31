<?php get_header(); ?>
<?php
$settings = get_field( 'Blog_settings', 'option' ) ?: [];

/* ── background image ───────────────────────────────────────────────── */
$hero_bg = ! empty( $settings['hero_background_image']['url'] )
  ? $settings['hero_background_image']
  : null;

if ( $hero_bg ) {
  $bg_url         = esc_url( $hero_bg['url'] );
  $section_style  = "style=\"background-image:url('{$bg_url}');background-size:cover;background-position:center;\"";
  $fallback_class = '';
} else {
  $section_style  = '';
  $fallback_class = 'bg-primary'; // tailwind class if no image
}

/* ── heading + texts ─────────────────────────────────────────────────── */
$hero_tag   = $settings['hero_heading_tag']  ?? 'h1';
$hero_text  = $settings['hero_heading_text'] ?? 'Our Blog';

$sub_text     = $settings['hero_subheading_text'] ?? 'Take a look at what we’ve built';
$filter_title = $settings['filter_section_title'] ?? 'Filter News';
?>
<style>
  /* room for the line under each thumb */
  .testimonial-indicators .indicator-slide{ position:relative; padding-bottom:32px; }
  /* make the button a positioning context */
  .testimonial-indicators .testimonial-nav-btn{ position:relative; display:inline-flex; }
  /* red lead line for the active (center) thumb */
  .testimonial-indicators .slick-current .testimonial-nav-btn::after{
    content:"";
    position:absolute;
    left:50%;
    transform:translateX(-50%);
    top:calc(100% + 8px);
    width:2px;
    height:26px;
    background:var(--indicator-ring, #dc2626);
    border-radius:9999px;
  }
</style>

<div class="mt-[5rem] xs:mt-[10rem] w-full">
  <section class="flex overflow-hidden relative">
    <div class="flex flex-col items-center mx-auto w-full bg-black">
      <div class="overflow-hidden relative max-w-[1158px] px-5 w-full hero-background" <?php //echo $section_style; ?>>

        <div class="flex z-0 flex-col pt-14 pb-8 w-full max-md:max-w-full">

          <!-- Breadcrumb Navigation -->
          <nav class="flex gap-2 items-center self-start mb-4" aria-label="Breadcrumb">
            <div class="pr-2 w-[30px]">
              <div class="flex w-full min-h-[21px]" aria-hidden="true">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M3 9L12 2L21 9V20C21 20.5304 20.7893 21.0391 20.4142 21.4142C20.0391 21.7893 19.5304 22 19 22H5C4.46957 22 3.96086 21.7893 3.58579 21.4142C3.21071 21.0391 3 20.5304 3 20V9Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  <path d="M9 22V12H15V22" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </div>
            </div>
            <ol class="flex gap-2 items-center pt-0.5 min-w-60">
              <li class="flex gap-2 items-center">
                <a href="<?php echo esc_url(home_url()); ?>" class="text-sm font-semibold leading-none text-white whitespace-nowrap hover:text-yellow-100 focus:text-yellow-100 focus:outline-2 focus:outline-white focus:outline-offset-2" aria-label="Home">
                  Home
                </a>
                <?php if (!is_front_page()) : ?>
                  <div class="flex gap-2 items-center pt-0.5 w-4 text-white" aria-hidden="true">
                    <svg width="16" height="17" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M5.99023 12.2104L9.99023 8.21045L5.99023 4.21045" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </div>
                <?php endif; ?>
              </li>

              <?php
              // Determine if we're on the main blog archive page (often the "Posts Page")
              $blog_page_id = get_option('page_for_posts');
              $is_blog_home = is_home() && !is_front_page();

              // If we are on the blog home (Posts Page) or a category/single post/tag/date/author
              if ($is_blog_home || is_category() || is_single() || is_tag() || is_date() || is_author()) {
                // "Resources" trail (optional)
                $resources_page_id = get_page_by_path('resources');
                if ($resources_page_id) {
                  ?>
                  <li class="flex gap-2 items-center">
                    <a href="<?php echo esc_url(get_permalink($resources_page_id)); ?>" class="text-sm font-semibold leading-none text-white whitespace-nowrap hover:text-yellow-100 focus:text-yellow-100 focus:outline-2 focus:outline-white focus:outline-offset-2" aria-label="Resources">
                      Resources
                    </a>
                    <?php if (!is_home() && !is_post_type_archive('Blog') && !is_page($resources_page_id->ID)) : ?>
                      <div class="flex gap-2 items-center pt-0.5 w-4" aria-hidden="true">
                        <svg width="16" height="17" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                          <path d="M5.99023 12.2104L9.99023 8.21045L5.99023 4.21045" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                      </div>
                    <?php endif; ?>
                  </li>
                  <?php
                }

                // Category breadcrumb for single posts
                if (is_single()) {
                  $categories = get_the_category();
                  if (!empty($categories)) {
                    $category = $categories[0];
                    echo '<li class="flex gap-2 items-center">';
                    echo '<a href="' . esc_url(get_category_link($category->term_id)) . '" class="text-sm font-semibold leading-none text-white whitespace-nowrap hover:text-yellow-100 focus:text-yellow-100 focus:outline-2 focus:outline-white focus:outline-offset-2" aria-label="' . esc_attr($category->name) . '">';
                    echo esc_html($category->name);
                    echo '</a>';
                    echo '<div class="flex gap-2 items-center pt-0.5 w-4" aria-hidden="true">';
                    echo '<svg width="16" height="17" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M5.99023 12.2104L9.99023 8.21045L5.99023 4.21045" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                          </svg>';
                    echo '</div>';
                    echo '</li>';
                  }
                }
              }

              // Current page/post/archive title
              echo '<li><span class="text-sm font-semibold leading-none text-white">';
              if (is_single()) {
                the_title();
              } elseif (is_page()) {
                the_title();
              } elseif (is_category()) {
                single_cat_title();
              } elseif (is_tag()) {
                single_tag_title();
              } elseif (is_author()) {
                the_author();
              } elseif (is_date()) {
                if (is_day()) {
                  echo get_the_date('F j, Y');
                } elseif (is_month()) {
                  echo get_the_date('F Y');
                } elseif (is_year()) {
                  echo get_the_date('Y');
                }
              } elseif (is_search()) {
                echo 'Search Results for "' . get_search_query() . '"';
              } elseif (is_404()) {
                echo 'Page Not Found';
              } elseif (is_post_type_archive('Blog')) {
                echo 'Blog';
              } elseif ($is_blog_home) {
                echo 'What\'s new at The tyre care';
              }
              echo '</span></li>';
              ?>
            </ol>
          </nav>

          <!-- Main Heading Section -->
          <header class="w-full max-md:max-w-full">
            <?php
              // dynamic heading tag
              printf(
                '<%1$s class="text-6xl font-bold leading-tight text-white max-md:max-w-full max-md:text-4xl">%2$s</%1$s>',
                esc_attr($hero_tag),
                esc_html($hero_text)
              );
            ?>
          </header>
        </div>

        <!-- Filter and Search Section -->
        <div class="flex overflow-hidden z-0 flex-wrap gap-6 items-end pb-14 w-full max-md:max-w-full">

          <!-- Filter Section -->
          <?php
            $all_cats = get_terms( [
              'taxonomy'   => 'category',
              'hide_empty' => true,
            ] );
            $current_slug = 'all';
            if ( is_tax( 'category' ) ) {
              $queried = get_queried_object();
              $current_slug = $queried->slug;
            }
          ?>
          <div class="flex-1 pb-2 text-base shrink min-w-60 max-md:max-w-full">
            <span class="mb-2 font-bold text-white"><?php echo esc_html($filter_title); ?></span>
            <div
              role="radiogroup"
              aria-label="Filter news by category"
              class="flex flex-wrap gap-4 items-start mt-2 w-full font-medium max-md:max-w-full"
            >
              <button role="radio"
                      class="gap-2 px-6 py-2 whitespace-nowrap bg-white rounded-lg hover:text-white hover:bg-primary btn filter-btn focus:outline-none focus:ring-2 focus:ring-offset-2"
                      data-filter="all"
                      aria-checked="<?php echo $current_slug === 'all' ? 'true' : 'false'; ?>"
                      tabindex="<?php echo $current_slug === 'all' ? '0' : '-1'; ?>">
                All Blog
              </button>

              <?php foreach ( $all_cats as $cat ) :
                $slug    = esc_attr( $cat->slug );
                $name    = esc_html( $cat->name );
                $checked = ( $slug === $current_slug ) ? 'true' : 'false';
                $tab     = ( $slug === $current_slug ) ? '0' : '-1';
              ?>
                <button
                  role="radio"
                  class="gap-2 px-6 py-2 whitespace-nowrap bg-white rounded-lg hover:bg-primary filter-btn hover:bg-teritary hover:border-white hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white"
                  data-filter="<?php echo $slug; ?>"
                  aria-checked="<?php echo $checked; ?>"
                  tabindex="<?php echo $tab; ?>"
                >
                  <?php echo $name; ?>
                </button>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Search Section -->
          <div class="flex items-center w-96 min-w-60">
            <form class="flex w-full" role="search" aria-label="Search articles">
              <div class="flex-1 my-auto text-base shrink min-h-14 min-w-60 text-slate-600">
                <div class="flex-1 w-full">
                  <div class="flex flex-1 justify-between items-center px-4 py-3 bg-white rounded-l size-full">
                    <label for="article-search" class="sr-only">Search articles</label>
                    <input
                      type="search"
                      id="article-search"
                      placeholder="Search articles"
                      class="flex-1 px-4 py-3 bg-white rounded-l border-none size-full text-slate-600 placeholder-slate-600"
                      aria-label="Search articles"
                    />
                  </div>
                </div>
              </div>

              <button type="submit" class="flex gap-2 justify-center items-center px-6 py-4 bg-primary border-2 border-white rounded-none min-h-14 w-[72px] max-md:px-5 search-btn" aria-label="Search">
                <svg width="24" height="25" viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M21 21.0408L16.65 16.6908M19 11.0408C19 15.459 15.4183 19.0408 11 19.0408C6.58172 19.0408 3 15.459 3 11.0408C3 6.62249 6.58172 3.04077 11 3.04077C15.4183 3.04077 19 6.62249 19 11.0408Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </button>
            </form>
          </div>
        </div>

      </div>
    </div>
  </section>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Filter button functionality
      const filterButtons = document.querySelectorAll('[data-filter]');

      filterButtons.forEach(button => {
        button.addEventListener('click', function() {
          // Remove active state from all buttons
          filterButtons.forEach(btn => {
            btn.setAttribute('aria-pressed', 'false');
          });

          // Add active state to clicked button
          this.setAttribute('aria-pressed', 'true');

          // Trigger filter event (can be extended for actual filtering)
          const filterValue = this.getAttribute('data-filter');
          const filterEvent = new CustomEvent('newsFilter', {
            detail: { filter: filterValue }
          });
          document.dispatchEvent(filterEvent);
        });
      });

      // Search form functionality
      const searchForm = document.querySelector('form[role="search"]');
      if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
          e.preventDefault();
          const searchInput = this.querySelector('input[type="search"]');
          const searchValue = searchInput.value.trim();

          if (searchValue) {
            const searchEvent = new CustomEvent('newsSearch', {
              detail: { query: searchValue }
            });
            document.dispatchEvent(searchEvent);
            console.log('Searching for:', searchValue);
          }
        });
      }
    });
  </script>

</div>

<section class="flex overflow-hidden relative">
  <div class="flex flex-col items-center pt-5 pb-5 mx-auto w-full max-w-[1158px] px-5">
    <div class="flex flex-col gap-8 pt-12 pb-14 w-full bg-white">

      <!-- Heading: Total posts + Clear Filters Button -->
      <div class="flex justify-between items-center w-full">
        <span class="text-2xl font-bold leading-7 text-slate-600">
          <?php echo (int) wp_count_posts()->publish; ?> posts
        </span>
        <button
          type="button"
          id="clear-filters"
          class="flex gap-2 items-center px-4 py-2 bg-gray-200 rounded cursor-pointer h-[42px] w-fit whitespace-nowrap hover:bg-hover hover:text-hover hidden"
          aria-label="Clear filters"
        >
          <svg width="16" height="17" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M12 4.04102L4 12.041M4 4.04102L12 12.041" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
          </svg>
          <span class="text-sm font-semibold leading-5 text-slate-700">Clear filters</span>
        </button>
      </div>

      <!-- Posts Grid -->
      <div class="grid grid-cols-3 gap-8 max-md:grid-cols-1 max-lg:grid-cols-2">
        <?php
        $args = [
          'posts_per_page' => 12,
          'paged'          => max( 1, get_query_var( 'paged' ) ),
        ];

        $query = new WP_Query( $args );

        if ( $query->have_posts() ) :
          while ( $query->have_posts() ) : $query->the_post();

            // categories for client-side filtering
            $terms  = get_the_terms( get_the_ID(), 'category' ) ?: [];
            $cats   = implode( ' ', wp_list_pluck( $terms, 'slug' ) );

            // post basics
            $post_id       = get_the_ID();
            $post_url      = get_permalink();
            $post_title    = get_the_title();
            $post_date     = get_the_date('j F Y');
            $post_excerpt  = get_the_excerpt();

            // reading time (same logic as related posts)
            $content       = get_post_field('post_content', $post_id);
            $word_count    = str_word_count( wp_strip_all_tags( $content ) );
            $words_per_min = 200;
            $minutes       = max( 1, (int) ceil( $word_count / $words_per_min ) );
            $read_time     = $minutes . ' min' . ( $minutes > 1 ? 's' : '' );

            // featured image + alt
            $featured_image_id  = get_post_thumbnail_id( $post_id );
            $featured_image_url = $featured_image_id
              ? get_the_post_thumbnail_url( $post_id, 'large' )
              : 'https://via.placeholder.com/425x240?text=No+Image';
            $featured_image_alt = $featured_image_id
              ? get_post_meta( $featured_image_id, '_wp_attachment_image_alt', true )
              : '';
            if ( empty( $featured_image_alt ) ) {
              $featured_image_alt = $post_title . ' featured image';
            }
            ?>
              <a
                href="<?php echo esc_url( $post_url ); ?>"
                class="block overflow-hidden pb-4 rounded-br-3xl rounded-bl-3xl border-2 border-gray-300 border-solid project-card group border-shape"
                data-categories="<?php echo esc_attr( $cats ); ?>"
                aria-label="<?php echo esc_attr( 'Read full article: ' . $post_title ); ?>"
              >
                <div class="overflow-hidden relative">
                  <img
                    width="425"
                    height="240"
                    alt="<?php echo esc_attr( $featured_image_alt ); ?>"
                    decoding="async"
                    src="<?php echo esc_url( $featured_image_url ); ?>"
                    class="object-cover w-full max-w-full transition-transform duration-500 group-hover:scale-105 h-[200px]"
                    loading="lazy"
                  />
                </div>

                <div class="py-4 bg-white">
                  <div class="px-8 py-2.5 text-sm font-medium leading-5 text-gray-500 opacity-80">
                    <time datetime="<?php echo esc_attr( get_the_date('c') ); ?>" class="text-sm text-gray-500">
                      <?php echo esc_html( $post_date ); ?> • Read time: <?php echo esc_html( $read_time ); ?>
                    </time>
                  </div>

                  <div class="px-8 mb-3.5">
                    <!-- h3 kept for search JS (querySelector('h3')) -->
                    <h3 class="text-2xl font-medium leading-8">
                      <span class="text-xl font-medium leading-7 transition-colors duration-200 cursor-pointer text-zinc-900 group-hover:text-primary">
                        <?php echo esc_html( $post_title ); ?>
                      </span>
                    </h3>
                  </div>

                  <div class="px-8 mb-2.5 text-lg leading-7 text-gray-500">
                    <p><?php echo esc_html( $post_excerpt ); ?></p>
                  </div>

                  <div class="px-8 mt-2.5 text-left">
                    <div
                      class="btn flex gap-2 justify-center items-center px-6 py-4 my-auto text-sm font-semibold leading-none bg-white border-primary border-2 border-solid min-h-[52px] max-md:px-5 transition-colors duration-200 group text-primary hover:text-white hover:bg-primary"
                      style="width:160px; height:48px;"
                    >
                      View More
                    </div>
                  </div>
                </div>
              </a>
            <?php
          endwhile;
          wp_reset_postdata();
        else :
          echo '<p>No Blog found.</p>';
        endif;
        ?>
      </div>
    </div>
  </div>
</section>

<div class="flex justify-center items-center py-12 w-full pagination" x-show="activeCategory === 'all'">
  <?php if ( function_exists('my_custom_pagination') ) { my_custom_pagination(); } ?>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const buttons      = document.querySelectorAll('.filter-btn');
    const cards        = document.querySelectorAll('.project-card');
    const searchInput  = document.getElementById('article-search');
    const clearFilters = document.getElementById('clear-filters');

    if (!clearFilters) {
      console.warn('⚠️ #clear-filters button not found');
      return;
    }

    let activeFilter = 'all';
    let searchTerm   = '';

    function cardVisible(card) {
      const cats = (card.getAttribute('data-categories') || '').split(' ').filter(Boolean);
      const titleEl = card.querySelector('h3');
      const title   = titleEl ? titleEl.textContent.toLowerCase() : '';

      const matchesCategory = (activeFilter === 'all') || cats.includes(activeFilter);
      const matchesSearch   = (searchTerm === '') || (title.indexOf(searchTerm) !== -1);

      return matchesCategory && matchesSearch;
    }

    function applyFilter() {
      cards.forEach(card => {
        card.style.display = cardVisible(card) ? '' : 'none';
      });

      const needsClear = (activeFilter !== 'all') || (searchTerm !== '');
      if (needsClear) {
        clearFilters.classList.remove('hidden');
      } else {
        clearFilters.classList.add('hidden');
      }
    }

    buttons.forEach(btn => {
      btn.addEventListener('click', () => {
        buttons.forEach(b => b.setAttribute('aria-pressed','false'));
        btn.setAttribute('aria-pressed','true');

        activeFilter = btn.getAttribute('data-filter');
        applyFilter();
      });
    });

    if (searchInput) {
      searchInput.addEventListener('input', () => {
        searchTerm = searchInput.value.trim().toLowerCase();
        applyFilter();
      });
    }

    clearFilters.addEventListener('click', () => {
      activeFilter = 'all';
      buttons.forEach(b => {
        b.setAttribute('aria-pressed', b.getAttribute('data-filter') === 'all' ? 'true' : 'false');
      });

      if (searchInput) {
        searchInput.value = '';
        searchTerm = '';
      }

      applyFilter();
    });

    applyFilter();
  });
</script>

<?php get_footer(); ?>
