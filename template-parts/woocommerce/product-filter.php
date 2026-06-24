<?php
/**
 * Category filter pills for merch / box product pages.
 *
 * Expects query var matrix_rd_filter_categories as list<WP_Term>.
 */
$ordered_categories = get_query_var('matrix_rd_filter_categories');
if (! is_array($ordered_categories) || $ordered_categories === []) {
    return;
}
?>
<div class="rd-woo-filter mt-4 w-full flex justify-center lg:justify-end lg:pr-4" x-data="{ showFilter: false }">
  <button
    type="button"
    @click="showFilter = !showFilter"
    :class="{
      'bg-yellow-primary': showFilter,
      'lg:bg-white': !showFilter,
      'border-black': showFilter,
      'max-lg:border-white': !showFilter,
    }"
    class="relative z-30 max-lg:w-auto max-lg:h-[36px] max-lg:rounded-[4px] border-1 max-lg:border-solid max-lg:px-3 flex items-center justify-center gap-2 lg:px-10 lg:py-4 editflilter"
  >
    <span
      :class="{
        'text-black-full': showFilter,
        'text-white lg:text-black-full': !showFilter
      }"
      class="lg:text-sm-md-font font-reg420"
    ><?php esc_html_e('Sort by', 'matrix-starter'); ?></span>
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" class="block lg:hidden shrink-0 text-white" aria-hidden="true">
      <path d="M3 5h18l-7 8.2V20l-4 2v-8.8L3 5Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
    </svg>
    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="29" viewBox="0 0 28 29" fill="none" class="hidden lg:block" aria-hidden="true">
      <path d="M12.9089 23.4414C12.5998 23.4414 12.3409 23.3364 12.1321 23.1264C11.9227 22.9171 11.8179 22.6576 11.8179 22.3477L11.8179 15.7852L5.49046 7.69141C5.21772 7.32682 5.177 6.94401 5.36827 6.54297C5.55883 6.14193 5.89047 5.94141 6.36321 5.94141L21.6364 5.94141C22.1092 5.94141 22.4412 6.14193 22.6324 6.54297C22.823 6.94401 22.7819 7.32682 22.5092 7.69141L16.1817 15.7852V22.3477C16.1817 22.6576 16.0773 22.9171 15.8686 23.1264C15.6591 23.3364 15.3999 23.4414 15.0908 23.4414H12.9089Z" fill="black" fill-opacity="0.2" stroke="black" />
    </svg>
  </button>
  <div id="filter-slider-container" class="h-full" x-cloak x-show="showFilter">
    <div x-show="showFilter" class="absolute left-0 right-0 flex items-center justify-center w-full h-[1px] md:h-full md:my-4 text-black-full top-full">
      <ul id="custom-filter" class="ml-4 lg:pl-0 flex flex-nowrap overflow-x-auto flex-row justify-start h-[120px] items-center gap-2">
        <li>
          <a class="px-8 py-4 bg-white border-solid rounded-113xl border-3 border-black-full text-mob-md-font text-black-full font-reg420 hover:bg-yellow-primary" href="#" data-filter="all"><?php esc_html_e('All', 'matrix-starter'); ?></a>
        </li>
        <?php foreach ($ordered_categories as $product_category) : ?>
        <li>
          <a class="px-8 py-4 bg-white border-solid rounded-113xl whitespace-nowrap border-3 border-black-full text-mob-md-font text-black-full font-reg420 hover:bg-yellow-primary" href="#" data-filter="<?php echo (int) $product_category->term_id; ?>"><?php echo esc_html($product_category->name); ?></a>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</div>
