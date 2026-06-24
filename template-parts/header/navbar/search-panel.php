<?php
/**
 * Product search overlay (Alpine; replaces [fibosearch]).
 */
$mobile_menu_bg = $args['mobile_menu_bg'] ?? '';
$style_attr     = $mobile_menu_bg !== '' ? '--mobile-bg-image: url(' . esc_url($mobile_menu_bg) . ');' : '';
?>
<div
  id="rd-search-panel"
  class="search-bar-container background-cover relative z-[1050] max-md:h-screen max-md:w-full max-md:overflow-hidden max-md:pt-12 md:relative"
  x-cloak
  x-show="showSearch"
  x-transition:enter="transition ease-out duration-700"
  x-transition:enter-start="-translate-y-12 opacity-0"
  x-transition:enter-end="translate-y-0 opacity-100"
  x-transition:leave="transition ease-in duration-700"
  x-transition:leave-start="translate-y-0 opacity-100"
  x-transition:leave-end="-translate-y-12 opacity-0"
  x-effect="showSearch && $nextTick(() => document.getElementById('rd-product-search-input')?.focus())"
  style="<?php echo esc_attr($style_attr); ?>"
  role="search"
  aria-label="<?php esc_attr_e('Site search', 'matrix-starter'); ?>"
>
  <div class="mx-auto flex w-full max-w-max-1182 flex-row items-start justify-center px-4">
    <div
      class="rd-product-search relative w-full max-w-max-1182"
      x-data="matrixRdProductSearch"
      @click.outside="open = false"
    >
      <form
        class="rd-product-search__form relative w-full"
        role="search"
        method="get"
        :action="viewAllUrl || '<?php echo esc_js(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/')); ?>'"
        @submit.prevent="if (viewAllUrl) { window.location.href = viewAllUrl; }"
      >
        <label class="sr-only" for="rd-product-search-input"><?php esc_html_e('Search the site', 'matrix-starter'); ?></label>
        <span class="rd-product-search__icon pointer-events-none absolute left-6 top-1/2 z-10 -translate-y-1/2" aria-hidden="true">
          <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none">
            <path d="M10.5 18C14.6421 18 18 14.6421 18 10.5C18 6.35786 14.6421 3 10.5 3C6.35786 3 3 6.35786 3 10.5C3 14.6421 6.35786 18 10.5 18Z" stroke="currentColor" stroke-width="2"/>
            <path d="M16 16L21 21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </span>
        <input
          id="rd-product-search-input"
          type="search"
          name="s"
          class="rd-product-search__input w-full"
          autocomplete="off"
          x-model="query"
          x-ref="searchInput"
          @focus="onFocus()"
          @blur="onBlur()"
          @keydown="onKeydown($event)"
          placeholder="<?php esc_attr_e('Search…', 'matrix-starter'); ?>"
        />
        <input type="hidden" name="post_type" value="product" />
      </form>

      <div
        class="rd-product-search__dropdown"
        x-show="open && (results.length || (query.trim().length >= minChars && !loading))"
        x-cloak
        x-transition
      >
        <p class="rd-product-search__status" x-show="loading"><?php esc_html_e('Searching…', 'matrix-starter'); ?></p>
        <p class="rd-product-search__status" x-show="!loading && !results.length && query.trim().length >= minChars"><?php esc_html_e('No results found', 'matrix-starter'); ?></p>

        <ul class="rd-product-search__results" role="listbox" x-show="results.length">
          <template x-for="(item, index) in results" :key="item.id">
            <li>
              <a
                :href="item.url"
                class="rd-product-search__result"
                :class="{ 'rd-product-search__result--active': activeIndex === index }"
                role="option"
                @mouseenter="activeIndex = index"
                @mousedown.prevent="selectResult(index)"
              >
                <img
                  class="rd-product-search__thumb"
                  x-show="item.image"
                  :src="item.image"
                  :alt="item.title"
                  width="56"
                  height="56"
                  loading="lazy"
                />
                <span class="rd-product-search__meta">
                  <span class="rd-product-search__title" x-text="item.title"></span>
                  <span class="rd-product-search__type" x-text="item.type_label"></span>
                  <span class="rd-product-search__price" x-show="item.price_html" x-html="item.price_html"></span>
                </span>
              </a>
            </li>
          </template>
        </ul>

        <a
          class="rd-product-search__view-all"
          x-show="viewAllUrl && query.trim().length >= minChars"
          :href="viewAllUrl"
          x-text="'<?php echo esc_js(__('View all results', 'matrix-starter')); ?>'"
        ></a>
      </div>
    </div>

    <button
      type="button"
      class="border-black-full bg-red-critical relative ml-2 flex h-[40px] w-[40px] shrink-0 items-center justify-center rounded-full border-2 border-solid hover:bg-white max-md:overflow-hidden"
      @click="showSearch = false"
      aria-label="<?php esc_attr_e('Close search', 'matrix-starter'); ?>"
    >
      <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true">
        <path d="M0.659928 3.12523L3.10369 0.681466L5.97737 3.55515L8.87368 0.658838L11.3174 3.1026L8.42113 5.99891L11.3174 8.89522L8.87368 11.339L5.97737 8.44267L3.10369 11.3164L0.659929 8.87259L3.53361 5.99891L0.659928 3.12523Z" fill="black"/>
      </svg>
    </button>
  </div>
</div>
