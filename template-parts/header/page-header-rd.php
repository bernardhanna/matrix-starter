<?php
/**
 * Rolling Donut interior page header (breadcrumb + title).
 */
if ((function_exists('is_cart') && is_cart()) || (function_exists('is_checkout') && is_checkout()) || is_single()) {
    return;
}

$image_id          = function_exists('get_field') ? get_field('page_header_bg', 'option', false) : 0;
$image_url         = $image_id ? wp_get_attachment_url((int) $image_id) : '';
$image_id_mobile   = function_exists('get_field') ? get_field('page_header_mobile_bg', 'option', false) : 0;
$image_url_mobile  = $image_id_mobile ? wp_get_attachment_url((int) $image_id_mobile) : '';
if (is_home()) {
    $title = __('Blog', 'matrix-starter');
} elseif (is_category()) {
    $title = single_cat_title('', false);
} elseif (is_tag()) {
    $title = single_tag_title('', false);
} elseif (is_author()) {
    $title = get_the_author();
} elseif (is_date()) {
    $title = get_the_date('F Y');
} elseif (is_search()) {
    $title = sprintf(
        /* translators: %s: search query */
        __('Search: %s', 'matrix-starter'),
        get_search_query()
    );
} elseif (is_archive()) {
    $title = wp_strip_all_tags(get_the_archive_title());
    $title = preg_replace('/^\s*Archives?:\s*/i', '', (string) $title);
} else {
    $title = html_entity_decode(get_the_title());
}
?>
<style>
/* Interior page headers use a photo band — keep breadcrumbs legible at every width. */
.rd-page-header .rd-breadcrumbs,
.rd-page-header .rd-breadcrumbs a,
.rd-page-header .rd-breadcrumbs .breadcrumb-item,
.rd-page-header .rd-breadcrumbs span {
  color: #fff;
}

.rd-page-header .rd-breadcrumbs .breadcrumb-item.text-yellow-primary {
  color: var(--color-yellow-primary, #f2e900);
}

/* On phones the page-header band switches to a solid dark background (set via the
 * Alpine init below at <=575px). Tighten title spacing on small screens. */
@media (max-width: 575px) {
  .rd-page-header__titlerow {
    padding-top: 1rem !important;
    padding-bottom: 1.25rem !important;
  }

  .rd-page-header__titlerow h1 {
    font-size: 2rem !important;
    line-height: 1.15 !important;
  }
}
</style>
<section class="rd-page-header relative z-20 w-full">
  <?php if ($image_url || $image_url_mobile) : ?>
  <div
    class="relative w-full bg-cover bg-center"
    style="<?php echo $image_url ? 'background-image:url(' . esc_url($image_url) . ');' : ''; ?>min-height:300px"
    x-data="{
      isMobile: false,
      init() {
        const set = () => {
          this.isMobile = window.innerWidth <= 575;
          if (this.isMobile) {
            this.$el.style.backgroundImage = 'none';
            this.$el.style.backgroundColor = '#0E1217';
            this.$el.style.minHeight = '150px';
          } else {
            this.$el.style.backgroundImage = 'url(<?php echo esc_js($image_url); ?>)';
            this.$el.style.backgroundColor = '';
            this.$el.style.minHeight = '300px';
          }
        };
        set();
        window.addEventListener('resize', set);
      }
    }"
  ></div>
  <?php endif; ?>
  <div class="absolute top-0 left-0 right-0 w-full h-full px-4 mx-auto desktop:p-0 lg:max-w-max-1549">
    <?php if (! function_exists('is_woocommerce') || ! is_woocommerce()) : ?>
    <div class="relative z-30 flex items-start justify-start w-full pt-4">
      <?php matrix_rd_render_breadcrumbs(); ?>
    </div>
    <?php endif; ?>
    <div class="rd-page-header__titlerow flex w-full flex-col items-center justify-center py-8 text-center md:py-12">
      <div class="relative inline-block mx-auto text-center text-container width-fit-content md:pt-8 sm:pt-0">
        <h1 class="relative left-0 z-10 m-auto text-center text-white font-reg420 text-mob-xxl-font lg:text-lg-font xl:text-lg-font xxl:text-xxxl-font"><?php echo esc_html($title); ?></h1>
      </div>
    </div>
  </div>
</section>
