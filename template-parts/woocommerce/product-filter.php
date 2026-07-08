<?php
/**
 * Category filter for merch / box product pages.
 *
 * Expects query var matrix_rd_filter_categories as list<WP_Term>.
 * Parent wrapper must provide Alpine scope: x-data="{ showFilter: false }".
 *
 * @var string $render button|panel|both
 */

$ordered_categories = get_query_var('matrix_rd_filter_categories');
if (! is_array($ordered_categories) || $ordered_categories === []) {
    return;
}

$render = $args['render'] ?? 'both';
$show_button = $render === 'both' || $render === 'button';
$show_panel  = $render === 'both' || $render === 'panel';
?>

<?php if ($show_button) : ?>
  <div class="rd-woo-filter flex shrink-0 justify-end pt-4">
    <button
      type="button"
      @click="toggleFilter()"
      :aria-expanded="showFilter"
      :class="{
        'bg-yellow-primary border-black text-black-full': showFilter,
        'bg-white border-black text-black-full': !showFilter,
      }"
      class="relative z-30 flex h-[36px] items-center justify-center gap-2 rounded-[4px] border border-solid px-3 lg:h-auto lg:rounded-none lg:border-0 lg:px-10 lg:py-4 editflilter"
    >
      <span
        x-show="!showFilter"
        class="font-reg420 text-sm-md-font text-black-full"
      ><?php esc_html_e('Sort by', 'matrix-starter'); ?></span>
      <span
        x-show="showFilter"
        x-cloak
        class="font-reg420 text-sm-md-font text-black-full"
      ><?php esc_html_e('Close', 'matrix-starter'); ?></span>
      <svg
        x-show="!showFilter"
        xmlns="http://www.w3.org/2000/svg"
        width="20"
        height="20"
        viewBox="0 0 24 24"
        fill="none"
        class="block shrink-0 text-black-full lg:hidden"
        aria-hidden="true"
      >
        <path d="M3 5h18l-7 8.2V20l-4 2v-8.8L3 5Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
      </svg>
      <svg
        x-show="!showFilter"
        xmlns="http://www.w3.org/2000/svg"
        width="28"
        height="29"
        viewBox="0 0 28 29"
        fill="none"
        class="hidden lg:block"
        aria-hidden="true"
      >
        <path d="M12.9089 23.4414C12.5998 23.4414 12.3409 23.3364 12.1321 23.1264C11.9227 22.9171 11.8179 22.6576 11.8179 22.3477L11.8179 15.7852L5.49046 7.69141C5.21772 7.32682 5.177 6.94401 5.36827 6.54297C5.55883 6.14193 5.89047 5.94141 6.36321 5.94141L21.6364 5.94141C22.1092 5.94141 22.4412 6.14193 22.6324 6.54297C22.823 6.94401 22.7819 7.32682 22.5092 7.69141L16.1817 15.7852V22.3477C16.1817 22.6576 16.0773 22.9171 15.8686 23.1264C15.6591 23.3364 15.3999 23.4414 15.0908 23.4414H12.9089Z" fill="black" fill-opacity="0.2" stroke="black" />
      </svg>
      <svg
        x-show="showFilter"
        x-cloak
        xmlns="http://www.w3.org/2000/svg"
        width="20"
        height="20"
        viewBox="0 0 24 24"
        fill="none"
        class="shrink-0 text-black-full"
        aria-hidden="true"
      >
        <path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
      </svg>
    </button>
  </div>
<?php endif; ?>

<?php if ($show_panel) : ?>
  <div
    id="filter-slider-container"
    class="rd-woo-filter__panel w-full"
    x-cloak
    x-show="showFilter"
  >
    <div class="flex w-full items-center justify-center pb-2 text-black-full lg:pb-3">
      <ul id="custom-filter" class="ml-4 flex h-[120px] flex-row flex-nowrap items-center justify-start gap-2 overflow-x-auto px-2 lg:pl-0">
        <li>
          <a class="rounded-113xl border-3 border-solid border-black-full bg-white px-8 py-4 font-reg420 text-mob-md-font text-black-full hover:bg-yellow-primary" href="#" data-filter="all"><?php esc_html_e('All', 'matrix-starter'); ?></a>
        </li>
        <?php foreach ($ordered_categories as $product_category) : ?>
        <li>
          <a class="whitespace-nowrap rounded-113xl border-3 border-solid border-black-full bg-white px-8 py-4 font-reg420 text-mob-md-font text-black-full hover:bg-yellow-primary" href="#" data-filter="<?php echo (int) $product_category->term_id; ?>"><?php echo esc_html($product_category->name); ?></a>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
<?php endif; ?>
