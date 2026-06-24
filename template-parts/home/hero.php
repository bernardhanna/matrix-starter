<?php
/**
 * Home hero (legacy ACF fields).
 */
$banner_left          = matrix_rd_acf_image(get_field('banner_left'));
$banner_top_mobile    = matrix_rd_acf_image(get_field('banner_top_mobile'));
$banner_right         = matrix_rd_acf_image(get_field('banner_right'));
$banner_bottom_mobile = matrix_rd_acf_image(get_field('banner_bottom_mobile'));
$neon                 = matrix_rd_acf_image(get_field('neon'));
$neon_mobile          = matrix_rd_acf_image(get_field('neon_mobile'));
$hazelnut             = matrix_rd_acf_image(get_field('hazelnut'));
$hero_text            = get_field('hero_text');
$hero_link            = get_field('hero_link');

if ($banner_left['url'] === '' || $banner_right['url'] === '') {
    return;
}
?>
<section class="home-hero hero-curve relative z-[1] w-full">
  <div class="flex w-full flex-col-reverse lg:flex-row">
    <div
      class="max-lg:h-[450px] flex h-hero-mob w-full flex-col items-center justify-center bg-black-full lg:h-hero-height lg:w-heroleft"
      x-data="{
        isMobile: false,
        getBackgroundImage() {
          if (this.isMobile && '<?php echo esc_js($banner_top_mobile['url']); ?>') {
            return 'url(<?php echo esc_js($banner_top_mobile['url']); ?>)';
          }
          return 'url(<?php echo esc_js($banner_left['url']); ?>)';
        }
      }"
      x-init="setTimeout(() => { isMobile = window.innerWidth < 1084 }, 0); window.addEventListener('resize', () => { isMobile = window.innerWidth < 1084 })"
      style="background-image:url('<?php echo esc_url($banner_left['url']); ?>');background-size:cover;background-position:center;"
      :style="{ backgroundImage: getBackgroundImage(), backgroundSize: 'cover', backgroundPosition: 'center' }"
    >
      <div class="flex h-full w-full items-center justify-center bg-[#000000b5]">
        <div class="relative flex max-sm:h-full max-sm:justify-around flex-col xxl:mr-48 xl:mx-auto xxl:right-8">
          <div class="hidden lg:block animate-fade-right animate-once animate-ease-in">
            <?php if ($neon['url']) : ?>
              <img src="<?php echo esc_url($neon['url']); ?>" alt="<?php echo esc_attr($neon['alt'] ?: 'Rolling Donut Signature'); ?>" class="relative -top-2 mx-auto w-auto object-contain" />
            <?php endif; ?>
          </div>
          <div class="lg:hidden">
            <?php if ($neon_mobile['url']) : ?>
              <img src="<?php echo esc_url($neon_mobile['url']); ?>" alt="<?php echo esc_attr($neon_mobile['alt'] ?: 'Rolling Donut Signature'); ?>" class="-mt-34 mx-auto w-auto object-contain" />
            <?php endif; ?>
          </div>
          <?php if ($hero_text) : ?>
            <div class="relative -top-8 block w-full max-w-max-529 px-12 mx-auto lg:px-0">
              <p class="text-base-font font-laca font-lighter max-w-max-529 animate-fade-right animate-once animate-ease-in mx-auto text-center text-white">
                <?php echo esc_html($hero_text); ?>
              </p>
            </div>
          <?php endif; ?>
          <?php if (is_array($hero_link) && ! empty($hero_link['url'])) : ?>
            <div class="relative -top-8 z-10 mt-4 w-full animate-fade-right animate-once animate-ease-in px-12 lg:px-0">
              <a
                class="btn-width btn-icon-yellow rounded-btn-72 border-3 border-color-yellow-primary bg-black-full text-yellow-primary text-sm-md-font font-reg420 mx-auto flex h-[64px] w-full max-w-[280px] flex-row items-center justify-center hover:bg-yellow-primary hover:text-black-full max-md:w-[342px] md:w-[322px] lg:border-none lg:bg-white lg:text-black-full"
                href="<?php echo esc_url($hero_link['url']); ?>"
                <?php echo ! empty($hero_link['target']) ? 'target="' . esc_attr($hero_link['target']) . '"' : ''; ?>
              >
                <svg class="mr-4 yellow-donut fill-yellow-primary lg:fill-black-full hover:fill-black-full" xmlns="http://www.w3.org/2000/svg" width="34" height="34" viewBox="0 0 34 34" fill="none" aria-hidden="true">
                  <path d="M32 17C32 25.2843 25.2843 32 17 32V34C26.3888 34 34 26.3888 34 17H32ZM17 2C25.2843 2 32 8.71573 32 17H34C34 7.61116 26.3888 0 17 0V2ZM2 17C2 8.71573 8.71573 2 17 2V0C7.61116 0 0 7.61116 0 17H2ZM17 32C8.71573 32 2 25.2843 2 17H0C0 26.3888 7.61116 34 17 34V32ZM21.3333 17C21.3333 19.3932 19.3932 21.3333 17 21.3333V23.3333C20.4978 23.3333 23.3333 20.4978 23.3333 17H21.3333ZM17 12.6667C19.3932 12.6667 21.3333 14.6068 21.3333 17H23.3333C23.3333 13.5022 20.4978 10.6667 17 10.6667V12.6667ZM12.6667 17C12.6667 14.6068 14.6068 12.6667 17 12.6667V10.6667C13.5022 10.6667 10.6667 13.5022 10.6667 17H12.6667ZM17 21.3333C14.6068 21.3333 12.6667 19.3932 12.6667 17H10.6667C10.6667 20.4978 13.5022 23.3333 17 23.3333V21.3333Z" />
                  <path d="M27.5096 14.7855L27.5095 14.7881C27.4734 15.629 27.6744 16.3322 28.0488 16.8697C28.4217 17.405 28.9875 17.8065 29.7373 18.0132C29.9445 18.0683 30.1528 18.1231 30.3582 18.1752L27.5096 14.7855ZM27.5096 14.7855C27.5189 14.5391 27.5375 14.2911 27.557 14.0322L27.5571 14.0317L27.5598 13.9953C27.6164 13.2362 27.6817 12.3607 27.4741 11.5077C26.7838 8.64063 25.0424 6.7799 22.3239 6.05299L22.3221 6.05251C22.1362 6.00355 21.9485 5.95733 21.7653 5.91224L21.7633 5.91175L21.7633 5.91174L21.7611 5.9112C21.7256 5.90264 21.6903 5.89416 21.6552 5.88574C21.4143 5.8279 21.1831 5.77239 20.959 5.70434L20.9579 5.70402C19.6097 5.29799 18.7931 4.15317 18.8356 2.66202L18.8356 2.66115C18.8392 2.52667 18.85 2.39525 18.8623 2.24692C18.865 2.21395 18.8678 2.18013 18.8706 2.14527C18.8722 2.12502 18.8739 2.10441 18.8756 2.08348C18.8897 1.9094 18.9057 1.71265 18.9082 1.51652C21.4246 1.88478 23.6525 2.77875 25.539 4.16582C30.4025 7.7431 32.7356 12.6734 32.482 18.873M27.5096 14.7855L32.482 18.873M32.482 18.873C31.7807 18.5354 31.046 18.3493 30.3819 18.1812L30.3585 18.1753L32.482 18.873Z" />
                </svg>
                <?php echo esc_html($hero_link['title'] ?? __('Order now', 'matrix-starter')); ?>
              </a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div
      class="hero-28 flex w-full bg-black-full lg:h-hero-height lg:w-heroright lg:items-end lg:bg-cover lg:bg-no-repeat"
      x-data="{
        isMobile: false,
        getBackgroundImage() {
          if (this.isMobile && '<?php echo esc_js($banner_bottom_mobile['url']); ?>') {
            return 'url(<?php echo esc_js($banner_bottom_mobile['url']); ?>)';
          }
          return 'url(<?php echo esc_js($banner_right['url']); ?>)';
        }
      }"
      x-init="setTimeout(() => { isMobile = window.innerWidth < 1084 }, 0); window.addEventListener('resize', () => { isMobile = window.innerWidth < 1084 })"
      style="background-image:url('<?php echo esc_url($banner_right['url']); ?>');background-size:cover;background-position:center;"
      :style="{ backgroundImage: getBackgroundImage(), backgroundSize: 'cover', backgroundPosition: 'center' }"
    >
      <?php if ($hazelnut['url']) : ?>
        <img
          src="<?php echo esc_url($hazelnut['url']); ?>"
          alt="<?php echo esc_attr($hazelnut['alt'] ?: 'Rolling Donut'); ?>"
          class="relative inset-0 -mb-10 mx-auto h-[308px] object-contain px-4 animate-spin animate-once animate-ease-in animate-alternate-reverse lg:-ml-28 lg:mb-4 lg:mr-auto lg:h-[430px] lg:px-0"
        />
      <?php endif; ?>
    </div>
  </div>
</section>
