<?php
/**
 * Main navigation block (single instance; headroom wrapper in navbar.php).
 *
 * @var array $args {
 *   @type array  $nav_split
 *   @type array  $logos
 *   @type string $telephone
 *   @type array  $cart
 *   @type string $mobile_bg
 *   @type bool   $is_thankyou
 *   @type string $section_id Optional. Defaults to site-nav.
 * }
 */
$nav_split   = $args['nav_split'] ?? ['left' => [], 'right' => [], 'all' => []];
$logos       = $args['logos'] ?? matrix_rd_nav_logos();
$telephone   = $args['telephone'] ?? '';
$cart        = $args['cart'] ?? ['count' => 0, 'total_html' => ''];
$mobile_bg   = $args['mobile_bg'] ?? '';
$is_thankyou = ! empty($args['is_thankyou']);
$section_id  = $args['section_id'] ?? 'site-nav';
$logo_only   = $is_thankyou;
$show_topnav = $args['show_topnav'] ?? ! $logo_only;

$nav_all      = $args['nav_all'] ?? ($nav_split['all'] ?? array_merge($nav_split['left'], $nav_split['right']));
$nav_combined = array_merge($nav_split['left'], $nav_split['right']);

$nav_top_class = 'top-0';
if ($logo_only) {
    $nav_top_class = 'top-0';
} elseif (function_exists('is_cart') && (is_cart() || is_checkout())) {
    $nav_top_class = 'top-8 lg:pt-0';
}
?>
<section
  id="<?php echo esc_attr($section_id); ?>"
  class="navbar h-auto max-lg:flex max-lg:items-center max-lg:py-4 bg-white transition-colors duration-200<?php echo $is_thankyou ? ' mb-8 xl:mb-0 rd-nav--logo-only overflow-hidden' : ' xl:h-nav overflow-visible'; ?>"
  :class="{ 'max-lg:bg-transparent rd-nav--menu-open': open }"
>
  <div class="relative mx-auto w-full max-w-sitewidth px-4 lg:px-10">
    <?php if ($show_topnav) : ?>
      <?php
      get_template_part('template-parts/header/navbar/topnav', null, [
          'telephone' => $telephone,
          'cart'      => $cart,
      ]);
      ?>
    <?php endif; ?>

    <nav
      id="menu"
      class="relative z-[100] flex w-full items-center justify-between overflow-visible <?php echo esc_attr($nav_top_class); ?>"
      role="navigation"
      aria-label="<?php esc_attr_e('Main navigation', 'matrix-starter'); ?>"
    >
      <div class="max-lg:w-1/3 laptop:w-5/6">
        <?php if (! $logo_only) : ?>
          <?php get_template_part('template-parts/header/navbar/mobile-toggle'); ?>

          <?php
          get_template_part('template-parts/header/navbar/desktop-menu', null, [
              'nav_items'     => $nav_split['left'],
              'nav_ul_class'  => 'nav-left w-100 hidden laptop:flex lg:items-center lg:justify-around lg:relative',
              'nav_max_items' => 4,
              'nav_tabindex_start' => 6,
          ]);
          ?>
        <?php endif; ?>
      </div>

      <?php get_template_part('template-parts/header/navbar/logo', null, ['logos' => $logos]); ?>

      <div class="flex w-1/3 min-w-0 items-center justify-end max-lg:overflow-visible lg:w-full laptop:w-5/6 lg:justify-start">
        <?php if (! $logo_only) : ?>
          <?php get_template_part('template-parts/header/navbar/mobile-utilities'); ?>

          <?php
          get_template_part('template-parts/header/navbar/desktop-menu', null, [
              'nav_items'          => $nav_split['right'],
              'nav_ul_class'       => 'nav-right hidden w-full items-center laptop:flex lg:hidden lg:justify-around lg:relative',
              'nav_max_items'      => 4,
              'nav_mark_last_cta'  => true,
              'nav_tabindex_start' => 7,
          ]);

          get_template_part('template-parts/header/navbar/desktop-menu', null, [
              'nav_items'          => $nav_combined,
              'nav_ul_class'       => 'nav-combined hidden w-full lg:flex laptop:hidden lg:justify-between lg:items-center lg:ml-4 lg:relative one-xl:ml-0 one-xl:mr-0',
              'nav_max_items'      => null,
              'nav_mark_last_cta'  => true,
              'nav_item_li_class'  => 'group relative overflow-visible lg:px-2 laptop:px-4 one-xl:px-6',
          ]);
          ?>
        <?php endif; ?>
      </div>
    </nav>

    <?php
    if (! $logo_only) {
        get_template_part('template-parts/header/navbar/mobile-drawer', null, [
            'nav_items'      => $nav_all,
            'mobile_menu_bg' => $mobile_bg,
        ]);
    }
    ?>
  </div>
</section>
