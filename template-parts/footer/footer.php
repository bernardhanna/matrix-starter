<?php
/**
 * Rolling Donut footer content — legacy partials/footer-content.blade.php.
 */
$footer_logo_url = matrix_rd_acf_image_url(get_field('footer_logo', 'option'));
$about_text      = (string) get_field('footer_about_text', 'option');
$copyright_logo  = matrix_rd_acf_image_url(get_field('copyright_logo', 'option'));
$copyright_text  = (string) get_field('copyright_text', 'option');
$copyright_area  = (string) get_field('copyright_text_area', 'option');

$twitter   = (string) get_field('twitter_profile_url', 'option');
$facebook  = (string) get_field('facebook_profile_url', 'option');
$tiktok    = (string) get_field('tiktok_profile_url', 'option');
$instagram = (string) get_field('instagram_profile_url', 'option');

$menu_one   = matrix_rd_footer_menu_links('footer_menu_one', 'footer_menu_one_link');
$menu_two   = matrix_rd_footer_menu_links('footer_menu_two', 'footer_menu_two_link');
$menu_three = matrix_rd_footer_menu_links('footer_menu_three', 'footer_menu_three_link');
$menu_four  = matrix_rd_footer_menu_links('footer_menu_four', 'footer_menu_four_link');
$copyright_menu = matrix_rd_footer_menu_links('copyright_menu_four', 'copyright_menu_link');

$render_menu = static function (array $links): void {
    if ($links === []) {
        return;
    }
    echo '<div class="footer-menu"><ul class="text-center lg:text-left">';
    foreach ($links as $link) {
        printf(
            '<li class="mb-4 text-white hover:text-yellow-primary"><a target="%1$s" class="font-medium text-center xs-font hover:underline" href="%2$s">%3$s</a></li>',
            esc_attr($link['target']),
            esc_url($link['url']),
            esc_html($link['title'])
        );
    }
    echo '</ul></div>';
};
?>
<div class="w-full bg-black-full">
  <div class="px-4 pt-10 mx-auto">
    <div class="relative flex flex-col items-center justify-center w-full m-auto lg:max-w-max-1549 lg:flex-row lg:justify-between lg:items-start">
      <div class="flex flex-col justify-between w-full lg:w-35 max-lg:items-center lg:flex-row">
        <a class="w-full max-w-max-40" href="<?php echo esc_url(home_url('/')); ?>">
          <?php if ($footer_logo_url !== '') : ?>
          <img class="h-[139px] w-[149px] mb-6 lg:mb-2" src="<?php echo esc_url($footer_logo_url); ?>" alt="<?php esc_attr_e('The Rolling Donut', 'matrix-starter'); ?>" />
          <?php endif; ?>
        </a>
        <div class="flex flex-col">
          <?php if ($about_text !== '') : ?>
          <p class="pr-4 text-white text-xs-font font-lighter max-tablet-sm:text-left font-laca lg:px-0 lg:text-left lg:max-w-max-358"><?php echo esc_html($about_text); ?></p>
          <?php endif; ?>
          <div class="flex my-6 space-x-8 text-center max-lg:justify-center lg:text-left">
            <?php if ($twitter !== '') : ?>
            <a href="<?php echo esc_url($twitter); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e('X (Twitter)', 'matrix-starter'); ?>">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 leading-none text-white hover:text-yellow-primary" viewBox="0 0 512 512" aria-hidden="true">
                <path class="fill-white hover:fill-yellow-primary" d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z" />
              </svg>
            </a>
            <?php endif; ?>
            <?php if ($facebook !== '') : ?>
            <a href="<?php echo esc_url($facebook); ?>" aria-label="<?php esc_attr_e('Visit The Rolling Donut on Facebook', 'matrix-starter'); ?>" target="_blank" rel="noopener noreferrer"><i class="leading-none text-white fab fa-facebook fa-2xl hover:text-yellow-primary" aria-hidden="true"></i></a>
            <?php endif; ?>
            <?php if ($tiktok !== '') : ?>
            <a href="<?php echo esc_url($tiktok); ?>" aria-label="<?php esc_attr_e('Visit The Rolling Donut on TikTok', 'matrix-starter'); ?>" target="_blank" rel="noopener noreferrer"><i class="leading-none text-white fab fa-tiktok fa-2xl hover:text-yellow-primary" aria-hidden="true"></i></a>
            <?php endif; ?>
            <?php if ($instagram !== '') : ?>
            <a href="<?php echo esc_url($instagram); ?>" aria-label="<?php esc_attr_e('Visit The Rolling Donut on Instagram', 'matrix-starter'); ?>" target="_blank" rel="noopener noreferrer"><i class="leading-none text-white fab fa-instagram fa-2xl hover:text-yellow-primary" aria-hidden="true"></i></a>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <div class="flex flex-col justify-around w-full mt-8 lg:mt-0 lg:w-60 mobile:flex-row mobile:flex-wrap mobile:justify-around lg:flex lg:flex-row lg:justify-around lg:items-start">
        <?php $render_menu($menu_one); ?>
        <?php $render_menu($menu_two); ?>
        <?php $render_menu($menu_three); ?>
        <?php $render_menu($menu_four); ?>
      </div>
    </div>
  </div>
  <div class="px-4 h-[2px] bg-white ml-auto mr-auto my-4 lg:max-w-max-1552"></div>
  <div class="flex flex-col-reverse items-center px-4 pl-4 pr-4 copyright sm:flex-col xl:flex-row justify-items-center laptop:justify-between macbook:max-w-max-1549 macbook:mx-auto desktop:pl-0 desktop:pr-0 lg:pb-6">
    <div class="order-last laptop:order-first mb-[10px] lg:mb-0 flex items-center flex-col-reverse sm:flex-col xl:flex-row my-4 xl:my-0">
      <span class="text-white max-sm:pt-4 text-mob-xs-font font-lighter">&copy; <?php echo esc_html((string) gmdate('Y')); ?> <?php echo esc_html($copyright_text); ?></span>
      <?php
      // Always surface the Accessibility statement alongside the legal links.
      $copyright_menu[] = [
          'title'  => __('Accessibility', 'matrix-starter'),
          'url'    => home_url('/accessibility/'),
          'target' => '',
      ];
      ?>
      <div class="copyright-menu">
        <ul class="rd-copyright-menu flex flex-wrap items-center justify-center text-white">
          <?php foreach ($copyright_menu as $item) : ?>
          <li class="pl-2 pr-2">
            <a target="<?php echo esc_attr($item['target']); ?>" class="text-white text-mob-xs-font font-lighter font-laca hover:text-yellow-primary hover:underline" href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['title']); ?></a>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <style>
        /* Visual "|" separators without putting stray text nodes inside the <ul>
           (which fails the WCAG "list structure" rule). */
        .rd-copyright-menu li:not(:last-child)::after {
          content: "|";
          margin-left: 0.5rem;
          color: currentColor;
        }
      </style>
    </div>
    <div class="flex flex-col items-center justify-between w-full laptop:w-40 macbook:1/2 lg:flex-row max-md:items-center">
      <?php if ($copyright_logo !== '') : ?>
      <div class="my-4 xl:my-0">
        <img src="<?php echo esc_url($copyright_logo); ?>" alt="" />
      </div>
      <?php endif; ?>
      <div class="my-4 mb-2 text-center text-white text-mob-xs-font font-lighter font-laca lg:mb-0 xl:my-0">
        <?php echo esc_html($copyright_area); ?>
        <span><?php esc_html_e('Designed & Developed by', 'matrix-starter'); ?> <a class="underline hover:no-underline font-bold text-[#f04000]" target="_blank" rel="noopener noreferrer" href="https://www.matrixinternet.ie/">Matrix Internet</a></span>
      </div>
    </div>
  </div>
</div>
