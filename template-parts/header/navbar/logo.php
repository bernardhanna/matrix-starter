<?php
/**
 * Center logo (desktop + mobile swap).
 */
$logos = $args['logos'] ?? matrix_rd_nav_logos();
?>
<a
  class="nav-center hide-md relative z-[100] flex w-1/3 cursor-pointer items-center justify-center lg:relative lg:bottom-4 lg:w-1/6"
  href="<?php echo esc_url(home_url('/')); ?>"
  aria-label="<?php echo esc_attr(get_bloginfo('name') . ' — ' . __('Home', 'matrix-starter')); ?>"
>
  <?php if ($logos['main']) : ?>
    <img
      class="logo desktop-logo relative xxl:-left-4 -t-0-3"
      src="<?php echo esc_url($logos['main']); ?>"
      alt="<?php echo esc_attr($logos['main_alt']); ?>"
      width="152"
      height="152"
      decoding="async"
    />
  <?php endif; ?>
  <?php if ($logos['mobile']) : ?>
    <img
      class="logo mobile-logo mobile-logo--default"
      src="<?php echo esc_url($logos['mobile']); ?>"
      alt="<?php echo esc_attr($logos['mobile_alt']); ?>"
      width="86"
      height="80"
      decoding="async"
    />
    <img
      class="logo mobile-logo mobile-logo--open z-[100]"
      src="<?php echo esc_url($logos['mobile_open']); ?>"
      alt="<?php echo esc_attr($logos['mobile_open_alt']); ?>"
      width="86"
      height="80"
      decoding="async"
    />
  <?php endif; ?>
</a>
