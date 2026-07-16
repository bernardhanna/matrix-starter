<?php
/**
 * WooCommerce page header band.
 */
static $matrix_rd_woo_header_rendered = false;
if ($matrix_rd_woo_header_rendered) {
    return;
}
$matrix_rd_woo_header_rendered = true;

$body_classes = get_body_class();
$image_id     = function_exists('get_field') ? get_field('woo_header_bg', 'option', false) : 0;
$image_url    = $image_id ? wp_get_attachment_url((int) $image_id) : '';
$image_alt    = $image_id ? (string) get_post_meta((int) $image_id, '_wp_attachment_image_alt', true) : '';
$image_srcset = $image_id ? wp_get_attachment_image_srcset((int) $image_id) : '';

$image_id_mobile     = function_exists('get_field') ? get_field('woo_mobile_bg', 'option', false) : 0;
$image_url_mobile    = $image_id_mobile ? wp_get_attachment_url((int) $image_id_mobile) : '';
$image_alt_mobile    = $image_id_mobile ? (string) get_post_meta((int) $image_id_mobile, '_wp_attachment_image_alt', true) : '';
$image_srcset_mobile = $image_id_mobile ? wp_get_attachment_image_srcset((int) $image_id_mobile) : '';

$is_product_archive = function_exists('is_shop') && (is_shop() || is_post_type_archive('product'));
$is_box_page        = is_page('donut-box');
$is_merch_page      = is_page('merch');
$filter_cats        = [];
if ($is_merch_page) {
    $filter_cats = matrix_rd_acf_terms(
        get_field('merch_ordered_categories') ?: get_post_meta(get_queried_object_id(), 'merch_ordered_categories', true)
    );
} elseif ($is_box_page) {
    $filter_cats = matrix_rd_acf_terms(
        get_field('ordered_categories') ?: get_post_meta(get_queried_object_id(), 'ordered_categories', true)
    );
}
if ($filter_cats !== []) {
    set_query_var('matrix_rd_filter_categories', $filter_cats);
}

$is_singular_product = is_singular('product');
$product_id          = $is_singular_product ? (int) get_queried_object_id() : 0;
$product_type_slug   = $product_id && function_exists('matrix_rd_product_type_slug')
    ? matrix_rd_product_type_slug($product_id)
    : null;
$is_box_product      = $product_type_slug === 'box'
    || in_array('rd-product-type-box', $body_classes, true);
$is_donut_product    = $product_type_slug === 'donut'
    || in_array('rd-product-type-donut', $body_classes, true);
$center_header_text  = ! $is_singular_product || $is_box_product;
$is_archive_header   = ! $is_singular_product;

if ($is_archive_header) {
    $inner_classes = 'rd-woo-header__inner relative w-full mx-auto lg:max-w-max-1549 flex flex-col items-stretch justify-start bg-black-full';
    $bc_classes    = 'rd-woo-bc relative z-30 flex w-full items-start justify-start pt-4 px-4 desktop:p-0';
    $band_classes  = 'rd-woo-header__band relative flex flex-col items-center justify-center gap-3 px-2 py-4 pb-8 text-center w-full mx-auto overflow-visible';
} elseif ($center_header_text) {
    $inner_classes = 'rd-woo-header__inner bg-black-full sm:bg-transparent min-h-[150px] max-md:min-h-[200px] mobile:absolute top-0 left-0 right-0 w-full h-auto mobile:h-full mx-auto max-w-max-1485 flex flex-col items-center justify-center text-center';
    $bc_classes    = 'rd-woo-bc hidden w-full pl-4 md:flex lg:max-w-max-1485 desktop:pl-0';
    $band_classes  = 'rd-woo-header__band max-md:h-[200px] relative flex items-center px-2 text-center mobile:pt-8 mobile:px-4 desktop:p-0 desktop:pt-6 flex-col md:flex-row w-full mx-auto';
} else {
    $inner_classes = 'rd-woo-header__inner bg-black-full sm:bg-transparent min-h-[150px] max-md:min-h-[200px] mobile:absolute top-0 left-0 right-0 w-full h-auto mobile:h-full mx-auto max-w-max-1485 tablet:flex-col max-tablet:flex max-tablet:justify-center max-tablet:items-center';
    $bc_classes    = 'rd-woo-bc hidden w-full pl-4 md:flex lg:max-w-max-1485 desktop:pl-0';
    $band_classes  = 'rd-woo-header__band max-md:h-[200px] relative flex items-center justify-center px-2 text-center mobile:pt-8 mobile:px-4 desktop:p-0 desktop:pt-6 flex-col w-full';
}
?>
<style>
/* Archive / shop headers: curved image band, then a solid title row below (not overlaid). */
.rd-woo-header--archive {
  display: flex;
  flex-direction: column;
}

.rd-woo-header--archive .rd-woo-header__media {
  width: 100%;
  line-height: 0;
}

.rd-woo-header--archive .rd-woo-header__inner {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: stretch;
  justify-content: flex-start;
  width: 100%;
  background-color: #0E1217;
}

.rd-woo-header--archive .rd-woo-bc {
  display: flex;
  flex: 0 0 auto;
  align-items: flex-start;
  justify-content: flex-start;
  width: 100%;
}

.rd-woo-header--archive .rd-woo-bc,
.rd-woo-header--archive .rd-woo-bc a,
.rd-woo-header--archive .rd-woo-bc span {
  color: #fff;
}

.rd-woo-header--archive .rd-woo-header__band {
  flex: 0 0 auto;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  width: 100%;
  margin: 0;
  padding: 0.5rem 0.5rem 1.25rem;
}

.rd-woo-header--archive .rd-woo-header__subtitle {
  margin: 0;
  max-width: 90%;
  color: #fff !important;
}

.rd-woo-header--archive .rd-woo-header__subtitle p {
  margin: 0;
  color: inherit !important;
}

.rd-woo-header--archive .rd-woo-header__titlewrap {
  margin: 0 !important;
  padding: 0;
}

.rd-woo-header__toolbar {
  position: relative;
  z-index: 40;
}

.rd-woo-header__toolbar .rd-woo-bc {
  display: flex !important;
  flex: 1 1 auto;
  min-width: 0;
  padding: 0;
}

.rd-woo-header__toolbar .rd-woo-filter {
  margin: 0;
  width: auto;
  justify-content: flex-end;
}

.rd-woo-header--has-filter {
  overflow: visible;
}

.rd-woo-header--has-filter .rd-woo-header__inner {
  overflow: visible;
}

.rd-woo-header--has-filter .rd-woo-header__headline {
  position: relative;
  width: 100%;
}

.rd-woo-header--has-filter .rd-woo-filter__panel {
  position: absolute;
  top: calc(100% + 1rem);
  right: 0;
  left: 0;
  z-index: 60;
  margin-top: 0;
  width: 100%;
}

@media (max-width: 767px) {
  .rd-woo-header--archive .rd-woo-header__band {
    gap: 12px;
    padding: 8px 16px 20px;
  }

  .rd-woo-header--archive .rd-woo-header__titlewrap h1 {
    margin: 0;
    text-align: center;
    font-size: 2rem;
    line-height: 1.15;
  }
}

/* From tablet up the band is a single row (legacy layout) for non-archive headers. */
@media (min-width: 768px) {
  .rd-woo-header:not(.rd-woo-header--archive) .rd-woo-header__band > .rd-woo-filter {
    width: auto;
    margin-top: 0;
  }
}
</style>
<section class="rd-woo-header <?php echo $is_box_page ? 'rd-woo-header--box ' : ''; ?><?php echo $filter_cats !== [] ? 'rd-woo-header--has-filter ' : ''; ?><?php echo $is_singular_product ? '' : 'rd-woo-header--archive '; ?>relative z-50 w-full mb-0 <?php echo $is_box_product ? '' : 'lg:mb-12'; ?>"
  x-data="{
    activeTab: 'sign-in',
    showLostPassword: false,
    isAccountPage: <?php echo is_account_page() ? 'true' : 'false'; ?>,
    isLoggedIn: <?php echo is_user_logged_in() ? 'true' : 'false'; ?>,
    isProductArchive: <?php echo $is_product_archive ? 'true' : 'false'; ?>,
    isBoxPage: <?php echo $is_box_page ? 'true' : 'false'; ?>,
    pageTitle: '<?php echo esc_js(get_the_title()); ?>',
    init() {
      window.addEventListener('update-active-tab', (e) => { this.activeTab = e.detail.tab; });
      window.addEventListener('update-show-lost-password', (e) => { this.showLostPassword = e.detail.show; });
    }
  }"
>
  <?php if ($image_url || $image_url_mobile) : ?>
  <div class="rd-woo-header__media">
    <?php if ($image_url) : ?>
    <img class="object-cover w-full min-h-[243px] hidden sm:block" src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($image_alt); ?>" <?php echo $image_srcset ? 'srcset="' . esc_attr($image_srcset) . '"' : ''; ?> sizes="(min-width: 640px) 100vw" />
    <?php endif; ?>
    <?php if ($image_url_mobile) : ?>
    <img class="block w-full max-mobile:hidden sm:hidden" src="<?php echo esc_url($image_url_mobile); ?>" alt="<?php echo esc_attr($image_alt_mobile); ?>" <?php echo $image_srcset_mobile ? 'srcset="' . esc_attr($image_srcset_mobile) . '"' : ''; ?> sizes="(max-width: 639px) 100vw" />
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <div class="<?php echo esc_attr($inner_classes); ?>">
    <?php if ($filter_cats !== [] && function_exists('woocommerce_breadcrumb')) : ?>
    <div
      class="rd-woo-header__filter-shell flex w-full flex-col"
      x-data="{
        showFilter: false,
        toggleFilter() {
          if (this.showFilter) {
            this.showFilter = false;
            if (window.matrixRdResetProductFilter) {
              window.matrixRdResetProductFilter();
            }
            return;
          }
          this.showFilter = true;
        }
      }"
      :class="showFilter ? 'rd-woo-header__filter-shell--open' : ''"
    >
      <div class="rd-woo-header__toolbar w-full px-4 pt-4 desktop:px-0">
        <div class="flex items-start justify-between gap-4">
          <div class="<?php echo esc_attr($bc_classes); ?> !p-0">
            <?php woocommerce_breadcrumb(); ?>
          </div>
          <?php get_template_part('template-parts/woocommerce/product-filter', null, ['render' => 'button']); ?>
        </div>
      </div>

      <div class="<?php echo esc_attr($band_classes); ?>">
    <?php elseif (function_exists('woocommerce_breadcrumb')) : ?>
    <div class="<?php echo esc_attr($bc_classes); ?>">
      <?php woocommerce_breadcrumb(); ?>
    </div>

    <div class="<?php echo esc_attr($band_classes); ?>">
    <?php else : ?>
    <div class="<?php echo esc_attr($band_classes); ?>">
    <?php endif; ?>

      <?php if ($filter_cats !== []) : ?>
      <div class="rd-woo-header__headline relative flex w-full flex-col items-center gap-3">
      <?php endif; ?>

      <div class="rd-woo-header__titlewrap relative inline-block px-4 <?php echo $is_singular_product ? '' : 'm-auto'; ?> text-container width-fit-content desktop:p-0">
        <h1 class="relative left-0 z-10 m-auto text-center text-white font-reg420 text-font-28 mobile:text-mob-xl-font lg:text-lg-font xl:text-lg-font xxl:text-xxl-font" x-text="
          showLostPassword ? 'Reset Password' :
          activeTab === 'register' ? 'Register' :
          isAccountPage && !isLoggedIn ? 'Sign In' :
          isBoxPage ? 'Our Boxes' :
          isProductArchive ? 'Our Donuts' :
          pageTitle
        "></h1>
      </div>

      <?php if (is_page('donut-box') && has_excerpt()) : ?>
      <p class="rd-woo-header__subtitle w-2/3 max-w-2xl px-2 text-center text-white text-base-font font-lighter laptop:font-light font-laca">
        <?php echo esc_html(wp_strip_all_tags(get_the_excerpt())); ?>
      </p>
      <?php elseif ($is_product_archive) : ?>
      <p class="rd-woo-header__subtitle w-[90%] max-w-3xl px-2 text-center text-white text-base-font font-lighter laptop:font-light font-laca">
        <?php esc_html_e('Our latest flavours are listed below. Donuts can be purchased as part of a box.', 'matrix-starter'); ?>
      </p>
      <?php elseif ($is_merch_page) : ?>
      <?php
      $merch_desc = has_excerpt()
          ? wp_strip_all_tags(get_the_excerpt())
          : __('Show your love for The Rolling Donut with our official merchandise.', 'matrix-starter');
      ?>
      <p class="rd-woo-header__subtitle w-2/3 max-w-2xl px-2 text-center text-white text-base-font font-lighter laptop:font-light font-laca">
        <?php echo esc_html($merch_desc); ?>
      </p>
      <?php endif; ?>

      <?php if ($filter_cats !== []) : ?>
      <?php get_template_part('template-parts/woocommerce/product-filter', null, ['render' => 'panel']); ?>
      </div>
      <?php endif; ?>

      <?php if ($is_singular_product) :
          global $product;
          $product = wc_get_product($product_id);
          if ($product instanceof WC_Product && (int) $product->get_id() !== 3947 && ! $is_donut_product) :
              ?>
      <div class="text-container">
        <div class="font-medium text-white product-price text-sm-md-font xs:text-md-font mobile:text-lg-font">
          <bdi class="relative z-50"><?php echo wp_kses_post($product->get_price_html()); ?></bdi>
        </div>
      </div>
          <?php endif; ?>
      <?php endif; ?>
    </div>
    <?php if ($filter_cats !== [] && function_exists('woocommerce_breadcrumb')) : ?>
    </div>
    <?php endif; ?>
  </div>
</section>
