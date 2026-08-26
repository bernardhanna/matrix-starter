<?php
/**
 * Default page template — legacy page.blade.php (RD header + gutenburg content).
 */
get_header();

$matrix_rd_is_account_page = function_exists('is_account_page') && is_account_page();
$matrix_rd_account_logged_in = $matrix_rd_is_account_page && is_user_logged_in();

$matrix_rd_is_thankyou = function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('order-received');

$matrix_rd_myaccount_bg_url = function_exists('get_field') && function_exists('matrix_rd_acf_image_url')
    ? matrix_rd_acf_image_url(get_field('myaccount_bg', 'option'))
    : '';

$matrix_rd_ty_bg_url = $matrix_rd_is_thankyou && function_exists('get_field') && function_exists('matrix_rd_acf_image_url')
    ? matrix_rd_acf_image_url(get_field('ty_bg', 'option'))
    : '';

$matrix_rd_account_has_bg = $matrix_rd_is_account_page && $matrix_rd_myaccount_bg_url !== '';
$matrix_rd_thankyou_has_bg = $matrix_rd_is_thankyou && $matrix_rd_ty_bg_url !== '';
$matrix_rd_special_bg = $matrix_rd_account_has_bg || $matrix_rd_thankyou_has_bg;

$matrix_rd_bg_url = '';
if ($matrix_rd_account_has_bg) {
    $matrix_rd_bg_url = $matrix_rd_myaccount_bg_url;
} elseif ($matrix_rd_thankyou_has_bg) {
    $matrix_rd_bg_url = $matrix_rd_ty_bg_url;
}

$main_classes = 'site-main w-full overflow-hidden';
if ($matrix_rd_special_bg) {
    $main_classes .= ' bg-repeat bg-black-full min-h-[1000px] max-tablet:py-8';
} elseif ($matrix_rd_account_logged_in || $matrix_rd_is_thankyou) {
    $main_classes .= ' bg-black-full';
} else {
    $main_classes .= ' bg-white';
}

$main_style = $matrix_rd_bg_url !== ''
    ? ' style="background-image: url(' . esc_url($matrix_rd_bg_url) . ');"'
    : '';
?>
<main id="main-content" class="<?php echo esc_attr($main_classes); ?>"<?php echo $main_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
  <?php
  while (have_posts()) :
      the_post();

      if (function_exists('is_account_page') && is_account_page()) {
          wc_get_template('custom/woocommerce-header.php');
      } else {
          get_template_part('template-parts/header/page-header-rd');
      }

      if ((function_exists('is_account_page') && is_account_page()) || $matrix_rd_is_thankyou) {
          $content_wrap = 'mx-auto lg:max-w-max-1568 px-4 pt-6 pb-12 lg:pb-20';
          $content_inner_class = 'max-w-none';
      } else {
          $content_wrap = 'w-full px-4 pt-10 pb-16 mx-auto lg:pt-16';
          $content_inner_class = 'gutenburg e-content rd-page-prose text-base-font font-lighter text-black-full';
      }
      ?>
  <div class="<?php echo esc_attr($content_wrap); ?>">
    <div class="<?php echo esc_attr($content_inner_class); ?>">
      <?php the_content(); ?>
      <?php
      wp_link_pages([
          'before' => '<nav class="page-nav"><p>' . esc_html__('Pages:', 'matrix-starter') . '</p>',
          'after'  => '</nav>',
      ]);
      ?>
    </div>
  </div>
      <?php
      matrix_rd_load_page_flexi_if_present(get_the_ID());
  endwhile;
  ?>
</main>
<?php
get_footer();
