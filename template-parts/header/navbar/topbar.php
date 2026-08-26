<?php
/**
 * Black promo bar above main navigation (Figma + legacy topbar).
 */
if (function_exists('is_cart') && (is_cart() || is_checkout())) {
    return;
}

if (! function_exists('get_field')) {
    return;
}

// Respect the "Show top bar" toggle (Theme Options → Top Bar). Only an explicit
// "off" hides it, so existing sites without the option set keep the bar visible.
if (get_option('options_topbar_enabled') === '0') {
    return;
}

$topbar_text   = trim((string) get_field('topbar_text', 'option'));
$discount_text = trim((string) get_field('discount_text', 'option'));
$signup_link   = get_field('signup_link', 'option');
$icon          = matrix_rd_acf_image(get_field('icon_image', 'option'), __('Rolling Donut', 'matrix-starter'));

if ($topbar_text === '' && $discount_text === '') {
    return;
}
?>
<section
  id="topbar"
  class="rd-topbar flextopbar relative z-[50] w-full bg-black-full transition-all duration-300"
  data-rd-topbar
>
  <div class="mx-auto flex w-full max-w-max-1514 items-center justify-between px-4 max-lg:px-4 lg:px-8">
    <div class="relative mx-auto flex items-center justify-center gap-2 lg:h-[40px]">
      <?php if ($icon['url']) : ?>
        <img
          class="icon hidden h-7 w-7 shrink-0 object-contain lg:block"
          src="<?php echo esc_url($icon['url']); ?>"
          alt="<?php echo esc_attr($icon['alt']); ?>"
          width="28"
          height="28"
          loading="lazy"
          decoding="async"
        />
      <?php endif; ?>
      <span class="relative text-center font-laca text-xs-font font-lighter leading-tight tracking-widest text-yellow-primary lg:text-left">
        <?php if ($topbar_text) : ?>
          <span><?php echo esc_html($topbar_text); ?></span>
          <?php
          if (is_array($signup_link) && ! empty($signup_link['url'])) :
              ?>
            <a
              href="<?php echo esc_url($signup_link['url']); ?>"
              class="underline text-yellow-primary hover:text-white"
              <?php echo ! empty($signup_link['target']) ? 'target="' . esc_attr($signup_link['target']) . '"' : ''; ?>
            ><?php echo esc_html($signup_link['title'] ?? __('Sign Up', 'matrix-starter')); ?></a>
          <?php endif; ?>
        <?php endif; ?>
        <?php if ($discount_text) : ?>
          <span><?php echo esc_html($discount_text); ?></span>
        <?php endif; ?>
      </span>
    </div>
    <button type="button" id="topbar-close" class="p-2 text-white" aria-label="<?php esc_attr_e('Close announcement', 'matrix-starter'); ?>">
      <span class="sr-only"><?php esc_html_e('Close announcement', 'matrix-starter'); ?></span>
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <path d="M16.2126 5.70711C16.6031 5.31658 17.2363 5.31658 17.6268 5.70711C18.0173 6.09763 18.0173 6.7308 17.6268 7.12132L7.12137 17.6267C6.73084 18.0173 6.09768 18.0173 5.70715 17.6267C5.31663 17.2362 5.31663 16.603 5.70715 16.2125L16.2126 5.70711Z" fill="currentColor"/>
        <path d="M7.12141 5.70711C6.73089 5.31658 6.09772 5.31658 5.7072 5.70711C5.31668 6.09763 5.31668 6.7308 5.7072 7.12132L16.2126 17.6267C16.6031 18.0173 17.2363 18.0173 17.6268 17.6267C18.0174 17.2362 18.0174 16.603 17.6268 16.2125L7.12141 5.70711Z" fill="currentColor"/>
      </svg>
    </button>
  </div>
</section>
