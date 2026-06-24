<?php
/**
 * WooCommerce page header band.
 */
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
?>
<style>
/* Unified mobile hero band for every WooCommerce archive / page header
 * (shop, our-donuts, merch, donut-box, my-account). Matches the interior
 * page-header baseline used on pages like /contact-us/: a solid dark band with
 * the breadcrumb on top (white) and a centred title. Filterable archives keep
 * the "Sort by" pill centred directly under the title. Single product headers
 * are intentionally excluded (they have their own gallery title treatment). */
@media (max-width: 767px) {
  .rd-woo-header--archive .rd-woo-header__inner {
    display: flex !important;
    flex-direction: column !important;
    align-items: stretch !important;
    justify-content: flex-start !important;
    background-color: #0E1217 !important;
    min-height: 150px !important;
    overflow: hidden;
  }

  .rd-woo-header--archive .rd-woo-bc {
    display: flex !important;
    position: relative;
    z-index: 10;
    width: 100%;
    padding: 12px 16px 0;
  }
  .rd-woo-header--archive .rd-woo-bc,
  .rd-woo-header--archive .rd-woo-bc a,
  .rd-woo-header--archive .rd-woo-bc span { color: #fff; }

  .rd-woo-header--archive .rd-woo-header__band {
    position: relative;
    z-index: 10;
    flex: 1 1 auto;
    flex-direction: column !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 12px;
    width: 100%;
    height: auto !important;
    text-align: center;
    padding: 8px 16px 20px;
  }
  .rd-woo-header--archive .rd-woo-header__titlewrap {
    margin: 0 !important;
    padding: 0;
    text-align: center;
  }
  .rd-woo-header--archive .rd-woo-header__titlewrap h1 {
    margin: 0;
    text-align: center;
    font-size: 2rem;
    line-height: 1.15;
  }

  .rd-woo-header--archive .rd-woo-filter {
    margin-top: 0;
    width: 100%;
    justify-content: center;
  }
}

/* From tablet up the band is a single row (legacy layout): the title stays
 * centred and the "Sort by" filter shrinks to its content and floats to the
 * right, so the description below sits directly under the title instead of being
 * pushed down by a full-width filter row. */
@media (min-width: 768px) {
  .rd-woo-header .rd-woo-header__band > .rd-woo-filter {
    width: auto;
    margin-top: 0;
  }
}
</style>
<section class="rd-woo-header <?php echo $is_box_page ? 'rd-woo-header--box ' : ''; ?><?php echo $is_singular_product ? '' : 'rd-woo-header--archive '; ?>relative z-50 w-full mb-0 <?php echo $is_box_product ? '' : 'lg:mb-12'; ?>"
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
  <div>
    <?php if ($image_url) : ?>
    <img class="object-cover w-full min-h-[243px] hidden sm:block" src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($image_alt); ?>" <?php echo $image_srcset ? 'srcset="' . esc_attr($image_srcset) . '"' : ''; ?> sizes="(min-width: 640px) 100vw" />
    <?php endif; ?>
    <?php if ($image_url_mobile) : ?>
    <img class="block w-full max-mobile:hidden sm:hidden" src="<?php echo esc_url($image_url_mobile); ?>" alt="<?php echo esc_attr($image_alt_mobile); ?>" <?php echo $image_srcset_mobile ? 'srcset="' . esc_attr($image_srcset_mobile) . '"' : ''; ?> sizes="(max-width: 639px) 100vw" />
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <div class="rd-woo-header__inner bg-black-full sm:bg-transparent min-h-[150px] max-md:min-h-[200px] mobile:absolute top-0 left-0 right-0 w-full h-auto mobile:h-full mx-auto max-w-max-1485 <?php echo $is_singular_product ? 'tablet:flex-col max-tablet:flex max-tablet:justify-center max-tablet:items-center' : ($center_header_text ? 'flex flex-col items-center justify-center text-center' : 'tablet:flex-col max-tablet:flex max-tablet:justify-center max-tablet:items-center'); ?>">
    <?php if (function_exists('woocommerce_breadcrumb')) : ?>
    <div class="rd-woo-bc hidden w-full pl-4 md:flex lg:max-w-max-1485 desktop:pl-0">
      <?php woocommerce_breadcrumb(); ?>
    </div>
    <?php endif; ?>

    <div class="rd-woo-header__band max-md:h-[200px] relative flex items-center <?php echo $is_singular_product ? 'justify-center' : ''; ?> px-2 text-center mobile:pt-8 mobile:px-4 desktop:p-0 <?php echo $is_singular_product ? ($is_box_product ? 'desktop:pt-0 flex-col w-full' : 'desktop:pt-6 flex-col w-full') : ($center_header_text ? 'desktop:pt-6 flex-col md:flex-row w-full mx-auto' : 'desktop:pt-6 flex-row w-full'); ?>">
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

      <?php if ($filter_cats !== []) : ?>
      <?php get_template_part('template-parts/woocommerce/product-filter'); ?>
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

    <?php if (is_page('donut-box') && has_excerpt()) : ?>
    <div class="justify-center hidden w-2/3 px-2 mx-auto mt-3 text-center text-white md:flex text-base-font font-lighter laptop:font-light font-laca">
      <?php the_excerpt(); ?>
    </div>
    <?php elseif ($is_product_archive) : ?>
    <div class="justify-center hidden w-[90%] px-2 mx-auto mt-3 text-center text-white md:flex text-base-font font-lighter laptop:font-light font-laca">
      <?php esc_html_e('Our latest flavours are listed below. Donuts can be purchased as part of a box.', 'matrix-starter'); ?>
    </div>
    <?php elseif ($is_merch_page) : ?>
    <?php // Match the donut-box/shop headers: a subtitle under the title keeps the
          // breadcrumb + title group vertically aligned with the other archives
          // (without it the shorter content centres lower and looks pushed down).
          $merch_desc = has_excerpt()
              ? get_the_excerpt()
              : __('Show your love for The Rolling Donut with our official merchandise.', 'matrix-starter');
    ?>
    <div class="justify-center hidden w-2/3 px-2 mx-auto mt-3 text-center text-white md:flex text-base-font font-lighter laptop:font-light font-laca">
      <?php echo esc_html($merch_desc); ?>
    </div>
    <?php endif; ?>
  </div>
</section>
