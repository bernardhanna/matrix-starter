<?php
/**
 * Rolling Donut 404 — legacy 404.blade.php.
 */
$bg_url  = '';
$img_url = '';

if (function_exists('get_field')) {
    $bg_raw  = get_field('bg_404', 'option');
    $img_raw = get_field('img_404', 'option');
    $bg_url  = matrix_rd_acf_image_url($bg_raw);
    $img_url = matrix_rd_acf_image_url($img_raw);
}

if ($bg_url === '') {
    $bg_url = matrix_rd_acf_image_url(get_option('options_bg_404'));
}
if ($img_url === '') {
    $img_url = matrix_rd_acf_image_url(get_option('options_img_404'));
}

$bg_style = $bg_url !== ''
    ? 'background-image:url(' . esc_url($bg_url) . ');'
    : '';
?>
<main id="main-content" class="site-main w-full overflow-hidden">
  <div class="bg-cover bg-no-repeat py-24 h-full bg-black-full"<?php echo $bg_style !== '' ? ' style="' . esc_attr($bg_style) . '"' : ''; ?>>
    <div class="relative flex flex-col-reverse lg:flex-row justify-end lg:justify-between m-auto max-w-[1222px] h-full px-8">
      <div class="text-center w-full lg:w-1/2 flex flex-col justify-center items-center">
        <h1 class="hidden mobile:block text-[96px] font-reg420 text-white"><?php esc_html_e('Oops!', 'matrix-starter'); ?></h1>
        <p class="text-sm-md-font font-reg420 mobile:font-lighter text-white font-laca"><?php esc_html_e('Page not found.', 'matrix-starter'); ?></p>
        <p class="text-sm-md-font font-lighter text-white font-laca"><?php esc_html_e('Can’t find what you’re looking for?', 'matrix-starter'); ?></p>
        <a href="<?php echo esc_url(home_url('/')); ?>" class="rounded-btn-72 inline-flex justify-center items-center mt-8 h-[64px] text-sm-md-font text-black-full font-reg420 bg-yellow-primary hover:bg-white w-full max-w-[356px]">
          <svg xmlns="http://www.w3.org/2000/svg" width="33" height="33" viewBox="0 0 33 33" fill="none" aria-hidden="true">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M20.4139 26.688C20.6779 26.952 21.1059 26.952 21.3699 26.688L22.951 25.107C23.2148 24.8432 23.215 24.4155 22.9515 24.1514L15.1891 16.3721L22.9515 8.59286C23.215 8.32876 23.2148 7.90111 22.9509 7.6373L21.3699 6.0563C21.1059 5.79228 20.6779 5.79229 20.4139 6.0563L10.098 16.3721L20.4139 26.688Z" fill="black" />
          </svg>
          <?php esc_html_e('Back to home', 'matrix-starter'); ?>
        </a>
      </div>
      <div class="flex justify-center items-center flex-col w-full lg:w-1/2">
        <?php if ($img_url !== '') : ?>
        <img src="<?php echo esc_url($img_url); ?>" alt="" class="w-full h-full object-contain max-w-[268px] mobile:max-w-[384.457px] max-h-[232px] mobile:max-h-[332.813px]" />
        <?php endif; ?>
      </div>
    </div>
  </div>
</main>
