<?php
/**
 * PACE footer — Figma 3:199 (desktop), 7:4837 (mobile)
 *
 * @package Matrix_Starter
 */

if (!function_exists('pace_footer_link_url')) {
    function pace_footer_link_url($link_field) {
        return (is_array($link_field) && !empty($link_field['url'])) ? $link_field['url'] : '';
    }
}

if (!function_exists('pace_footer_link_target')) {
    function pace_footer_link_target($link_field) {
        return (is_array($link_field) && !empty($link_field['target'])) ? $link_field['target'] : '_self';
    }
}

if (!function_exists('pace_footer_img_meta')) {
    function pace_footer_img_meta($image_id, $fallback_alt = '') {
        $alt = (string) get_post_meta($image_id, '_wp_attachment_image_alt', true);
        $title = (string) get_the_title($image_id);
        if ($alt === '') {
            $alt = $fallback_alt !== '' ? $fallback_alt : ($title !== '' ? $title : get_bloginfo('name'));
        }
        return [$alt, $title !== '' ? $title : $alt];
    }
}

if (!function_exists('pace_footer_copyright_text')) {
    /**
     * Replace {year} with the current site year (WordPress timezone).
     */
    function pace_footer_copyright_text(string $template): string {
        $template = trim($template);
        if ($template === '') {
            $template = '© {year} PACE Project Consortium';
        }
        return str_replace('{year}', (string) date_i18n('Y'), $template);
    }
}

if (!function_exists('pace_footer_legal_link_markup')) {
    /**
     * Legal link with mobile two-line label (Figma 7:4766) and desktop single line (7:4193).
     */
    function pace_footer_legal_link_markup(string $url, string $title, string $target = '_self'): string {
        $title = trim($title);
        if ($url === '' || $title === '') {
            return '';
        }

        $words = preg_split('/\s+/', $title, 2);
        $line_one = $words[0] ?? $title;
        $line_two = $words[1] ?? '';

        $mobile_label = $line_two !== ''
            ? sprintf(
                '<span class="flex flex-col leading-[18px] lg:hidden"><span>%1$s</span><span>%2$s</span></span>',
                esc_html($line_one),
                esc_html($line_two)
            )
            : sprintf('<span class="lg:hidden">%s</span>', esc_html($title));

        return sprintf(
            '<a href="%1$s" target="%2$s" class="shrink-0 font-comfortaa text-[12px] font-normal leading-[18px] text-white/65 transition-colors duration-200 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1C959B]"><span class="hidden lg:inline">%3$s</span>%4$s</a>',
            esc_url($url),
            esc_attr($target),
            esc_html($title),
            $mobile_label
        );
    }
}

if (!function_exists('pace_footer_eu_logo_markup')) {
    /**
     * EU co-funded badge (Figma 7:4193) — ACF image or bundled theme asset.
     */
    function pace_footer_eu_logo_markup(): string {
        $eu_logo_id = (int) get_field('footer_eu_logo', 'option');
        $eu_alt     = __('Co-funded by the European Union', 'matrix-starter');

        if ($eu_logo_id > 0) {
            [$eu_alt] = pace_footer_img_meta($eu_logo_id, $eu_alt);
            $img = wp_get_attachment_image($eu_logo_id, 'medium', false, [
                'class' => 'h-[38px] w-[172px] max-w-full object-contain object-left',
                'alt'   => esc_attr($eu_alt),
            ]);
            if ($img) {
                return $img;
            }
        }

        $fallback = get_template_directory_uri() . '/assets/images/eu-co-funded-white.png';
        if (!is_readable(get_template_directory() . '/assets/images/eu-co-funded-white.png')) {
            return '';
        }

        return sprintf(
            '<img src="%1$s" alt="%2$s" width="172" height="38" class="h-[38px] w-[172px] max-w-full object-contain object-left" loading="lazy" decoding="async" />',
            esc_url($fallback),
            esc_attr($eu_alt)
        );
    }
}

if (!function_exists('pace_footer_overlay_style')) {
    /**
     * Figma overlays on navy, or optional solid override from Theme Options.
     */
    function pace_footer_overlay_style(string $acf_color, string $figma_rgba): string {
        $color = trim($acf_color);
        if ($color !== '') {
            return 'background-color:' . $color . ';';
        }
        return 'background-color:' . $figma_rgba . ';';
    }
}

if (!function_exists('pace_footer_credit_markup')) {
    /**
     * Design credit — stacked on mobile (7:4766), single line on desktop (7:4193).
     */
    function pace_footer_credit_markup(string $prefix, $credit_link): string {
        $prefix = trim($prefix);
        $link_title = (is_array($credit_link) && !empty($credit_link['title'])) ? (string) $credit_link['title'] : 'Matrix Internet';
        $link_url   = (is_array($credit_link) && !empty($credit_link['url'])) ? (string) $credit_link['url'] : '';
        $link_target = (is_array($credit_link) && !empty($credit_link['target'])) ? (string) $credit_link['target'] : '_self';

        if ($prefix === '' && $link_url === '') {
            return '';
        }

        $link_html = $link_url !== ''
            ? sprintf(
                '<a href="%1$s" target="%2$s" class="text-white/55 transition-colors duration-200 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1C959B]">%3$s</a>',
                esc_url($link_url),
                esc_attr($link_target),
                esc_html($link_title)
            )
            : esc_html($link_title);

        return sprintf(
            '<span class="shrink-0 font-comfortaa text-[12px] font-normal leading-[18px] text-white/55"><span class="flex flex-col lg:inline"><span class="lg:hidden">%1$s</span><span class="hidden lg:inline">%1$s </span><span class="lg:inline">%2$s</span></span></span>',
            esc_html($prefix),
            $link_html
        );
    }
}

if (!function_exists('pace_footer_social_button')) {
    /**
     * PACE footer social: circle with letter or Instagram SVG.
     */
    function pace_footer_social_button($icon, $label, $url, $target = '_self') {
        $icon = (string) $icon;
        $classes = matrix_pace_btn_classes('icon');

        if ($icon === 'instagram') {
            return sprintf(
                '<a href="%1$s" target="%2$s" rel="noopener noreferrer" class="%3$s" aria-label="%4$s"><svg class="size-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="4.5" stroke="currentColor" stroke-width="2"/><circle cx="17.5" cy="6.5" r="1.25" fill="currentColor"/></svg></a>',
                esc_url($url),
                esc_attr($target),
                esc_attr($classes),
                esc_attr($label)
            );
        }

        $letter = $icon === 'linkedin' ? 'in' : 'f';

        return sprintf(
            '<a href="%1$s" target="%2$s" rel="noopener noreferrer" class="%3$s" aria-label="%4$s"><span aria-hidden="true">%5$s</span></a>',
            esc_url($url),
            esc_attr($target),
            esc_attr($classes),
            esc_attr($label),
            esc_html($letter)
        );
    }
}

$theme_logo_id = get_theme_mod('custom_logo');
$acf_logo_id   = (int) get_field('footer_logo', 'option');
$logo_id       = $acf_logo_id ?: $theme_logo_id;

$footer_tagline       = (string) get_field('footer_tagline', 'option');
$footer_eu_logo       = (int) get_field('footer_eu_logo', 'option');
$footer_social_links  = get_field('footer_social_links', 'option');
$footer_contact_email = (string) get_field('footer_contact_email', 'option');

$col1_heading = (string) get_field('footer_col1_heading', 'option') ?: 'PROJECT';
$col2_heading = (string) get_field('footer_col2_heading', 'option') ?: "WHAT'S NEW";
$col3_heading = (string) get_field('footer_col3_heading', 'option') ?: 'RESOURCES HUB';
$col4_heading = (string) get_field('footer_col4_heading', 'option') ?: 'CONTACT';

$footer_disclaimer    = (string) get_field('footer_disclaimer', 'option');
$footer_project_number = (string) get_field('footer_project_number', 'option');
$copyright_tpl  = (string) get_field('footer_copyright_left', 'option');
$copyright_text = pace_footer_copyright_text($copyright_tpl);

$privacy_link = get_field('footer_privacy_link', 'option');
$cookie_link  = get_field('footer_cookie_link', 'option');
$credit_prefix = (string) get_field('footer_credit_prefix', 'option');
$credit_link   = get_field('footer_credit_link', 'option');

$main_bg           = (string) get_field('footer_main_bg', 'option') ?: '#003b65';
$disclaimer_bg     = (string) get_field('footer_disclaimer_bg', 'option');
$bottom_bg         = (string) get_field('footer_bottom_bg', 'option');
$disclaimer_style  = pace_footer_overlay_style($disclaimer_bg, 'rgba(0,0,0,0.18)');
$bottom_style      = pace_footer_overlay_style($bottom_bg, 'rgba(0,0,0,0.3)');
$eu_logo_markup    = pace_footer_eu_logo_markup();

$menu_project   = 'footer_one';
$menu_whats_new = 'footer_two';
$menu_resources = 'footer_three';

$footer_link_class = 'font-comfortaa text-[14px] font-normal leading-[21px] text-white/75 transition-colors duration-200 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-offset-[#003b65] focus-visible:ring-[#1C959B]';

$hide_on_donation = function_exists('matrix_donations_is_donation_flow') && matrix_donations_is_donation_flow();
?>

<footer class="font-montserrat text-white" role="contentinfo" aria-label="<?php echo esc_attr__('Site footer', 'matrix-starter'); ?>" style="background-color: <?php echo esc_attr($main_bg); ?>;">

  <?php if (!$hide_on_donation) : ?>
    <div class="mx-auto w-full max-w-[1280px] px-5 pb-12 pt-16 lg:px-10 lg:pb-12 lg:pt-20">

        <!-- Brand + link columns -->
        <div class="grid grid-cols-1 gap-10 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,3fr)] lg:gap-16">
          <div class="flex flex-col gap-6 lg:gap-[23px]">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="inline-flex w-fit focus:outline-none focus-visible:ring-2 focus-visible:ring-[#f4bd0b] focus-visible:ring-offset-2 focus-visible:ring-offset-[#003b65]" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?> - <?php esc_attr_e('Go to homepage', 'matrix-starter'); ?>">
              <?php if ($logo_id) : ?>
                <?php
                [$logo_alt] = pace_footer_img_meta($logo_id, get_bloginfo('name'));
                echo wp_get_attachment_image($logo_id, 'full', false, [
                    'class' => 'h-9 w-[77px] object-contain object-left',
                    'alt'   => esc_attr($logo_alt),
                ]);
                ?>
              <?php else : ?>
                <span class="text-lg font-bold text-white"><?php echo esc_html(get_bloginfo('name')); ?></span>
              <?php endif; ?>
            </a>

            <?php if ($footer_tagline !== '') : ?>
              <p class="font-montserrat text-[14px] font-semibold leading-[21px] tracking-[0.14px] text-[#f4bd0b]">
                <?php echo wp_kses_post($footer_tagline); ?>
              </p>
            <?php endif; ?>

            <?php if ($eu_logo_markup !== '') : ?>
              <div class="shrink-0">
                <?php echo $eu_logo_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper ?>
              </div>
            <?php endif; ?>
          </div>

          <div class="grid grid-cols-2 gap-x-8 gap-y-10 sm:gap-x-10 lg:grid-cols-4 lg:gap-8">
            <nav class="flex flex-col gap-2.5" aria-labelledby="footer-col-1">
              <h2 id="footer-col-1" class="pb-1 font-montserrat text-[13px] font-bold uppercase leading-[19.5px] tracking-[0.78px] text-white">
                <?php echo esc_html($col1_heading); ?>
              </h2>
              <?php
              wp_nav_menu([
                  'theme_location' => $menu_project,
                  'container'      => false,
                  'menu_class'     => 'flex flex-col gap-2.5',
                  'fallback_cb'    => '__return_empty_string',
                  'link_before'    => '<span class="' . esc_attr($footer_link_class) . '">',
                  'link_after'     => '</span>',
              ]);
              ?>
            </nav>

            <nav class="flex flex-col gap-2.5" aria-labelledby="footer-col-2">
              <h2 id="footer-col-2" class="pb-1 font-montserrat text-[13px] font-bold uppercase leading-[19.5px] tracking-[0.78px] text-white">
                <?php echo esc_html($col2_heading); ?>
              </h2>
              <?php
              wp_nav_menu([
                  'theme_location' => $menu_whats_new,
                  'container'      => false,
                  'menu_class'     => 'flex flex-col gap-2.5',
                  'fallback_cb'    => '__return_empty_string',
                  'link_before'    => '<span class="' . esc_attr($footer_link_class) . '">',
                  'link_after'     => '</span>',
              ]);
              ?>
            </nav>

            <nav class="flex flex-col gap-2.5" aria-labelledby="footer-col-3">
              <h2 id="footer-col-3" class="pb-1 font-montserrat text-[13px] font-bold uppercase leading-[19.5px] tracking-[0.78px] text-white">
                <?php echo esc_html($col3_heading); ?>
              </h2>
              <?php
              wp_nav_menu([
                  'theme_location' => $menu_resources,
                  'container'      => false,
                  'menu_class'     => 'flex flex-col gap-2.5',
                  'fallback_cb'    => '__return_empty_string',
                  'link_before'    => '<span class="' . esc_attr($footer_link_class) . ' font-comfortaa">',
                  'link_after'     => '</span>',
              ]);
              ?>
            </nav>

            <div class="flex flex-col gap-2.5" aria-labelledby="footer-col-4">
              <h2 id="footer-col-4" class="pb-1 font-montserrat text-[13px] font-bold uppercase leading-[19.5px] tracking-[0.78px] text-white">
                <?php echo esc_html($col4_heading); ?>
              </h2>

              <?php if ($footer_contact_email !== '') : ?>
                <a
                  href="mailto:<?php echo esc_attr(sanitize_email($footer_contact_email)); ?>"
                  class="font-comfortaa text-[14px] font-normal leading-[21px] text-white/75 transition-colors duration-200 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-offset-[#003b65] focus-visible:ring-[#1C959B]"
                >
                  <?php echo esc_html($footer_contact_email); ?>
                </a>
              <?php endif; ?>

              <?php if (!empty($footer_social_links) && is_array($footer_social_links)) : ?>
                <nav class="flex gap-2 pt-4 md:pt-5" aria-label="<?php echo esc_attr__('Social media', 'matrix-starter'); ?>">
                  <?php
                  foreach ($footer_social_links as $social) {
                      $label = (string) ($social['label'] ?? 'Social link');
                      $icon  = (string) ($social['icon'] ?? 'facebook');
                      $link  = $social['link'] ?? null;
                      $url   = pace_footer_link_url($link);
                      if ($url === '') {
                          continue;
                      }
                      echo pace_footer_social_button($icon, $label, $url, pace_footer_link_target($link));
                  }
                  ?>
                </nav>
              <?php endif; ?>
            </div>
          </div>
        </div>
    </div>
  <?php endif; ?>

  <?php if ($footer_disclaimer !== '' || $footer_project_number !== '') : ?>
    <div class="w-full px-5 pb-8 pt-6 lg:px-20 lg:pb-8 lg:pt-[23px]" style="<?php echo esc_attr($disclaimer_style); ?>">
      <div class="mx-auto flex w-full max-w-[1280px] flex-col gap-2 lg:px-10">
        <?php if ($footer_disclaimer !== '') : ?>
          <div class="max-w-[980px] font-comfortaa text-[12px] font-normal leading-[19.2px] text-white/65">
            <?php echo wp_kses_post(wpautop($footer_disclaimer)); ?>
          </div>
        <?php endif; ?>
        <?php if ($footer_project_number !== '') : ?>
          <p class="max-w-[980px] font-mono text-[11px] font-normal leading-[17.6px] tracking-[0.44px] text-white/55">
            <?php echo esc_html($footer_project_number); ?>
          </p>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>

  <?php
  $has_privacy_acf = is_array($privacy_link) && !empty($privacy_link['url']);
  $has_cookie_acf  = is_array($cookie_link) && !empty($cookie_link['url']);
  $show_legal_menu = !$has_privacy_acf && !$has_cookie_acf;
  $credit_markup   = pace_footer_credit_markup($credit_prefix ?: 'Designed & developed by', $credit_link);
  ?>
  <div class="w-full px-5 py-4 lg:px-20" style="<?php echo esc_attr($bottom_style); ?>">
    <div class="pace-footer-bottom mx-auto flex w-full max-w-[1280px] flex-col gap-4 lg:flex-row lg:items-center lg:justify-between lg:px-10">
      <p class="shrink-0 font-comfortaa text-[12px] font-normal leading-[18px] text-white/55">
        <?php echo esc_html($copyright_text); ?>
      </p>

      <div class="flex flex-wrap items-center gap-x-[18px] gap-y-3 lg:flex-nowrap lg:justify-end">
        <?php if ($has_privacy_acf) : ?>
          <?php
          echo pace_footer_legal_link_markup(
              pace_footer_link_url($privacy_link),
              (string) ($privacy_link['title'] ?? __('Privacy Policy', 'matrix-starter')),
              pace_footer_link_target($privacy_link)
          );
          ?>
        <?php endif; ?>

        <?php if ($has_cookie_acf) : ?>
          <?php
          echo pace_footer_legal_link_markup(
              pace_footer_link_url($cookie_link),
              (string) ($cookie_link['title'] ?? __('Cookie Policy', 'matrix-starter')),
              pace_footer_link_target($cookie_link)
          );
          ?>
        <?php endif; ?>

        <?php if ($show_legal_menu) : ?>
          <nav class="flex flex-wrap items-center gap-x-[18px] gap-y-3" aria-label="<?php echo esc_attr__('Legal links', 'matrix-starter'); ?>">
            <?php
            wp_nav_menu([
                'theme_location' => 'copyright',
                'container'      => false,
                'menu_class'     => 'flex flex-wrap items-center gap-x-[18px] gap-y-3',
                'fallback_cb'    => '__return_empty_string',
                'link_before'    => '<span class="font-comfortaa text-[12px] leading-[18px] text-white/65 transition-colors duration-200 hover:text-white">',
                'link_after'     => '</span>',
            ]);
            ?>
          </nav>
        <?php endif; ?>

        <?php if ($credit_markup !== '') : ?>
          <?php echo $credit_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

</footer>
