<?php
/**
 * Footer Template (Sanctuary Runner style) — NO Back to Top
 *
 * @package Matrix_Starter
 */

/** Helpers */
function matrix_link_url($link_field) {
    return (is_array($link_field) && !empty($link_field['url'])) ? $link_field['url'] : '';
}
function matrix_link_target($link_field) {
    return (is_array($link_field) && !empty($link_field['target'])) ? $link_field['target'] : '_self';
}
function matrix_img_meta($image_id, $fallback_alt = '') {
    $alt = (string) get_post_meta($image_id, '_wp_attachment_image_alt', true);
    $title = (string) get_the_title($image_id);

    if ($alt === '') {
        $alt = $fallback_alt !== '' ? $fallback_alt : ($title !== '' ? $title : get_bloginfo('name'));
    }
    if ($title === '') {
        $title = $alt;
    }

    return [$alt, $title];
}

/** Options */
$footer_logo = (int) get_field('footer_logo', 'option');

$footer_social_links = get_field('footer_social_links', 'option');
$footer_reg_text = (string) get_field('footer_reg_text', 'option');

$col1_heading = (string) get_field('footer_col1_heading', 'option');
$col2_heading = (string) get_field('footer_col2_heading', 'option');
$col3_heading = (string) get_field('footer_col3_heading', 'option');

$copyright_left_tpl = (string) get_field('footer_copyright_left', 'option');
$copyright_left = str_replace('{year}', date('Y'), $copyright_left_tpl ?: 'Sanctuary Runners © {year}');

$credit_prefix = (string) get_field('footer_credit_prefix', 'option');
$credit_link = get_field('footer_credit_link', 'option');

$top_bar_color   = (string) get_field('footer_top_bar_color', 'option');
$main_bg_color   = (string) get_field('footer_main_bg', 'option');
$bottom_bg_color = (string) get_field('footer_bottom_bg', 'option');

/** Padding classes (options repeater) */
$padding_classes = [];
if (have_rows('padding_settings', 'option')) {
    while (have_rows('padding_settings', 'option')) {
        the_row();
        $screen_size    = (string) get_sub_field('screen_size');
        $padding_top    = get_sub_field('padding_top');
        $padding_bottom = get_sub_field('padding_bottom');

        if ($screen_size !== '' && $padding_top !== null && $padding_top !== '') {
            $padding_classes[] = "{$screen_size}:pt-[{$padding_top}rem]";
        }
        if ($screen_size !== '' && $padding_bottom !== null && $padding_bottom !== '') {
            $padding_classes[] = "{$screen_size}:pb-[{$padding_bottom}rem]";
        }
    }
}
$padding_class_string = implode(' ', $padding_classes);

/** Menus */
$menu_about       = 'footer_one';
$menu_latest      = 'footer_two';
$menu_getinvolved = 'footer_three';
$menu_legal       = 'copyright';

$legal_menu_options = [];
$legal_menu_locations = get_nav_menu_locations();
if (!empty($legal_menu_locations[$menu_legal])) {
    $legal_menu_id = (int) $legal_menu_locations[$menu_legal];
    $legal_menu_items = wp_get_nav_menu_items($legal_menu_id);
    if (!empty($legal_menu_items) && is_array($legal_menu_items)) {
        foreach ($legal_menu_items as $item) {
            if (!is_object($item) || empty($item->url) || empty($item->title)) {
                continue;
            }
            $legal_menu_options[] = [
                'url' => (string) $item->url,
                'title' => wp_strip_all_tags((string) $item->title),
            ];
        }
    }
}

/** SVGs for social icons */
function matrix_social_svg($icon) {
    if ($icon === 'bluesky') {
        return '<svg width="16" height="14" viewBox="0 0 16 14" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path d="M3.46829 0.94239C5.30251 2.31114 7.2757 5.08593 7.99995 6.57504C8.72421 5.08593 10.6975 2.31114 12.5317 0.94239C13.8555 -0.0449892 16 -0.809181 16 1.62217C16 2.10758 15.7199 5.70132 15.5555 6.28489C14.9845 8.31295 12.9032 8.83028 11.052 8.51726C14.288 9.06455 15.1111 10.8772 13.3333 12.69C9.95702 16.133 8.48052 11.8262 8.10248 10.7227C7.99345 10.405 8.00902 10.3977 7.89745 10.7227C7.51939 11.8262 6.04301 16.133 2.66676 12.69C0.888939 10.8772 1.71203 9.06455 4.94798 8.51726C3.09676 8.83028 1.01547 8.31295 0.44447 6.28489C0.280063 5.70132 0 2.10758 0 1.62217C0 -0.809181 2.14479 -0.0449892 3.46829 0.94239Z" fill="#00628F"/></svg>';
    }
    if ($icon === 'youtube') {
        return '<svg width="16" height="12" viewBox="0 0 16 12" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path d="M15.6667 3.19792C15.6667 2.22917 14.875 1.4375 13.9062 1.4375C12.5104 1.375 10.7917 1.33333 8.66667 1.33333C6.54167 1.33333 4.82292 1.375 3.42708 1.4375C2.45833 1.4375 1.66667 2.22917 1.66667 3.19792C1.60417 4.05208 1.5625 4.94792 1.5625 6C1.5625 7.05208 1.60417 7.94792 1.66667 8.80208C1.66667 9.77083 2.45833 10.5625 3.42708 10.5625C4.82292 10.625 6.54167 10.6667 8.66667 10.6667C10.7917 10.6667 12.5104 10.625 13.9062 10.5625C14.875 10.5625 15.6667 9.77083 15.6667 8.80208C15.7292 7.94792 15.7708 7.05208 15.7708 6C15.7708 4.94792 15.7292 4.05208 15.6667 3.19792ZM7.08333 8.125V3.875L10.75 6L7.08333 8.125Z" fill="#00628F"/></svg>';
    }
    if ($icon === 'tiktok') {
        return '<svg width="14" height="16" viewBox="0 0 14 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path d="M10.4375 0C10.5312 1.03125 11.1562 1.90625 12.0938 2.34375C12.5312 2.5625 13 2.65625 13.5 2.65625V5.125C12.625 5.125 11.7812 4.875 11.0625 4.40625V10.4062C11.0625 12.9688 8.96875 15.0625 6.40625 15.0625C3.84375 15.0625 1.75 12.9688 1.75 10.4062C1.75 7.84375 3.84375 5.75 6.40625 5.75C6.625 5.75 6.84375 5.78125 7.0625 5.8125V8.34375C6.84375 8.28125 6.625 8.25 6.40625 8.25C5.21875 8.25 4.25 9.21875 4.25 10.4062C4.25 11.5938 5.21875 12.5625 6.40625 12.5625C7.59375 12.5625 8.5625 11.5938 8.5625 10.4062V0H10.4375Z" fill="#00628F"/></svg>';
    }
    if ($icon === 'linkedin') {
        return '<svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path d="M11.99 9.60071C11.9898 10.025 11.8215 10.4319 11.5223 10.7318C11.223 11.0317 10.8172 11.2 10.3942 11.1998C9.9712 11.1996 9.5656 11.0308 9.26664 10.7307C8.96767 10.4305 8.79984 10.0234 8.80005 9.59911C8.80026 9.17479 8.9685 8.76793 9.26777 8.46804C9.56703 8.16815 9.9728 7.99979 10.3958 8C10.8188 8.00021 11.2244 8.16898 11.5234 8.46917C11.8223 8.76936 11.9902 9.17639 11.99 9.60071ZM12.0378 12.3846H8.8479V22.4H12.0378V12.3846ZM17.0779 12.3846H13.9039V22.4H17.046V17.1443C17.046 14.2165 20.85 13.9445 20.85 17.1443V22.4H24V16.0564C24 11.1206 18.3698 11.3046 17.046 13.7285L17.0779 12.3846Z" fill="#00628F"/></svg>';
    }
    if ($icon === 'instagram') {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15" fill="none">
          <path d="M14.3567 4.23145C14.3447 3.63415 14.2316 3.04321 14.022 2.48374C13.8412 2.00331 13.5578 1.5681 13.1916 1.20845C12.8319 0.84218 12.3967 0.558769 11.9163 0.377976C11.3568 0.168433 10.7658 0.0552748 10.1686 0.043312C9.40058 0.00830398 9.15542 0 7.2 0C5.24458 0 4.99942 0.00830398 4.23145 0.043312C3.63415 0.0552748 3.04321 0.168433 2.48374 0.377976C2.00331 0.558769 1.5681 0.84218 1.20845 1.20845C0.84218 1.5681 0.558769 2.00331 0.377976 2.48374C0.168433 3.04321 0.0552748 3.63415 0.043312 4.23145C0.00830398 4.99942 0 5.24458 0 7.2C0 9.15542 0.00830398 9.40058 0.043312 10.1686C0.0552748 10.7658 0.168433 11.3568 0.377976 11.9163C0.558769 12.3967 0.84218 12.8319 1.20845 13.1916C1.56808 13.5578 2.0033 13.8412 2.48374 14.022C3.04321 14.2316 3.63415 14.3447 4.23145 14.3567C4.99942 14.3918 5.24458 14.4 7.2 14.4C9.15542 14.4 9.40058 14.3918 10.1686 14.3567C10.7658 14.3447 11.3568 14.2316 11.9163 14.022C12.3946 13.837 12.8289 13.5542 13.1915 13.1916C13.5542 12.8289 13.837 12.3946 14.022 11.9163C14.2316 11.3568 14.3447 10.7659 14.3567 10.1686C14.3917 9.40058 14.4 9.15542 14.4 7.2C14.4 5.24458 14.3917 4.99942 14.3567 4.23145ZM13.0607 10.1094C13.0553 10.566 12.9715 11.0182 12.8128 11.4463C12.693 11.7569 12.5096 12.0389 12.2742 12.2742C12.0389 12.5096 11.7569 12.693 11.4463 12.8128C11.0182 12.9715 10.566 13.0553 10.1094 13.0608C9.3503 13.0954 9.12261 13.1027 7.2 13.1027C5.27739 13.1027 5.0497 13.0954 4.29058 13.0607C3.83402 13.0553 3.3818 12.9715 2.95366 12.8128C2.64092 12.6974 2.35802 12.5134 2.12578 12.2742C1.88664 12.042 1.7026 11.7591 1.5872 11.4463C1.42852 11.0182 1.34465 10.566 1.33925 10.1094C1.30464 9.35019 1.29731 9.12251 1.29731 7.2C1.29731 5.27749 1.30464 5.04981 1.33926 4.29058C1.34466 3.83402 1.42853 3.3818 1.5872 2.95366C1.70259 2.64092 1.88663 2.35802 2.12578 2.12578C2.35802 1.88664 2.64092 1.7026 2.95366 1.5872C3.38179 1.42853 3.83402 1.34466 4.29058 1.33925C5.04981 1.30464 5.27749 1.29731 7.2 1.29731C9.12251 1.29731 9.35019 1.30464 10.1094 1.33926C10.566 1.34467 11.0182 1.42854 11.4463 1.58721C11.7591 1.7026 12.042 1.88664 12.2742 2.12578C12.5134 2.35802 12.6974 2.64092 12.8128 2.95366C12.9715 3.38179 13.0553 3.83402 13.0608 4.29058C13.0954 5.04978 13.1027 5.27754 13.1027 7.2C13.1027 9.12246 13.0954 9.35019 13.0607 10.1094ZM7.2 3.50269C6.46874 3.50269 5.7539 3.71953 5.14588 4.1258C4.53786 4.53206 4.06397 5.1095 3.78413 5.7851C3.50429 6.46069 3.43107 7.2041 3.57373 7.92131C3.71639 8.63852 4.06853 9.29732 4.58561 9.81439C5.10268 10.3315 5.76148 10.6836 6.47869 10.8263C7.1959 10.9689 7.9393 10.8957 8.6149 10.6159C9.29049 10.336 9.86794 9.86214 10.2742 9.25412C10.6805 8.6461 10.8973 7.93126 10.8973 7.2C10.8973 6.21941 10.5078 5.279 9.81438 4.58562C9.121 3.89224 8.18058 3.5027 7.2 3.50269ZM7.2 9.6C6.72532 9.6 6.26131 9.45924 5.86663 9.19553C5.47195 8.93181 5.16434 8.55698 4.98269 8.11844C4.80104 7.6799 4.75351 7.19734 4.84612 6.73178C4.93872 6.26623 5.1673 5.83859 5.50294 5.50294C5.83859 5.1673 6.26623 4.93872 6.73178 4.84612C7.19734 4.75351 7.6799 4.80104 8.11844 4.98269C8.55698 5.16434 8.93181 5.47195 9.19553 5.86663C9.45924 6.26131 9.6 6.72532 9.6 7.2C9.6 7.83652 9.34714 8.44697 8.89706 8.89706C8.44697 9.34714 7.83652 9.6 7.2 9.6ZM11.9074 3.35664C11.9074 3.52752 11.8567 3.69457 11.7618 3.83665C11.6668 3.97874 11.5319 4.08948 11.374 4.15487C11.2161 4.22027 11.0424 4.23738 10.8748 4.20404C10.7072 4.1707 10.5533 4.08841 10.4324 3.96758C10.3116 3.84675 10.2293 3.6928 10.196 3.5252C10.1626 3.3576 10.1797 3.18388 10.2451 3.026C10.3105 2.86813 10.4213 2.73319 10.5634 2.63825C10.7054 2.54331 10.8725 2.49264 11.0434 2.49264C11.2725 2.49264 11.4923 2.58367 11.6543 2.7457C11.8163 2.90773 11.9074 3.12749 11.9074 3.35664Z" fill="#00628F"/>
        </svg>';
    }
    // facebook default
    return '<svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path d="M17.6055 17.3246H19.8992L20.8167 13.7924H17.6055V12.0262C17.6055 11.1167 17.6055 10.2601 19.4405 10.2601H20.8167V7.293C20.5176 7.25503 19.3882 7.16937 18.1954 7.16937C15.7045 7.16937 13.9356 8.63261 13.9356 11.3198V13.7924H11.1832V17.3246H13.9356V24.8307H17.6055V17.3246Z" fill="#00628F"/></svg>';
}
?>

<footer role="contentinfo" aria-label="Site footer">
  <div class="h-2" style="background-color: <?php echo esc_attr($top_bar_color ?: '#E6F4FB'); ?>;"></div>

  <?php if ( ! ( function_exists( 'matrix_donations_is_donation_flow' ) && matrix_donations_is_donation_flow() ) ) : ?>
  <div style="background-color: <?php echo esc_attr($main_bg_color ?: '#00263E'); ?>;">
    <div class="max-w-[1440px] mx-auto px-8 sm:px-8 lg:px-14 py-8 md:py-14 <?php echo esc_attr($padding_class_string); ?>">
      <div class="footer-main-layout flex flex-col gap-0 md:flex-row lg:gap-16 xl:gap-32">

        <div class="footer-brand-block flex flex-row gap-4 justify-around pb-6 md:flex-col md:justify-start shrink-0 md:pb-0">
          <div class="w-40">
            <?php if ($footer_logo) : ?>
              <?php
              [$alt, $title] = matrix_img_meta($footer_logo, 'Sanctuary Runners');
              echo wp_get_attachment_image($footer_logo, 'full', false, [
                  'class' => 'w-full h-auto',
                  'alt' => esc_attr($alt),
                  'title' => esc_attr($title),
              ]);
              ?>
            <?php else : ?>
              <span class="text-base font-bold text-white font-primary">
                <?php echo esc_html(get_bloginfo('name')); ?>
              </span>
            <?php endif; ?>
          </div>

          <div>

          <?php if (!empty($footer_social_links) && is_array($footer_social_links)) : ?>
            <nav class="footer-social-links flex gap-4 items-center" aria-label="Social media links">
              <?php foreach ($footer_social_links as $social) :
                $label = (string) ($social['label'] ?? 'Social link');
                $icon  = (string) ($social['icon'] ?? 'facebook');
                $link  = $social['link'] ?? null;

                $url = matrix_link_url($link);
                if ($url === '') {
                    continue;
                }
                $target = matrix_link_target($link);
              ?>
                <a
                  href="<?php echo esc_url($url); ?>"
                  target="<?php echo esc_attr($target); ?>"
                  rel="noopener"
                  aria-label="<?php echo esc_attr($label); ?>"
                  class="flex justify-center items-center w-8 h-8 bg-white rounded-full transition-opacity hover:opacity-80 shrink-0 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-transparent btn"
                >
                  <?php echo matrix_social_svg($icon); ?>
                </a>
              <?php endforeach; ?>
            </nav>
          <?php endif; ?>

          <?php if ($footer_reg_text !== '') : ?>
            <p class="mt-3 max-w-[180px] font-sans text-[12px] font-normal not-italic leading-[18px] text-white md:mt-5">
              <?php echo esc_html($footer_reg_text); ?>
            </p>
          <?php endif; ?>
          </div>
        </div>

        <nav class="footer-nav-block flex flex-col gap-4 py-6 border-y border-white/30 md:border-0 md:py-0" aria-labelledby="footer-col-1">
          <h3 id="footer-col-1" class="text-2xl font-bold leading-8 text-[#EEF6FC] font-['Public_Sans'] md:text-2xl md:font-light md:leading-8 md:text-brand-secondary md:font-primary">
            <?php echo esc_html($col1_heading ?: 'About us'); ?>
          </h3>
          <?php
          wp_nav_menu([
              'theme_location' => $menu_about,
              'container'      => false,
              'menu_class'     => 'flex flex-col gap-4',
              'fallback_cb'    => '__return_empty_string',
              'link_before'    => '<span class="font-sans text-[14px] font-bold not-italic leading-[20px] text-white transition-colors duration-200 hover:text-brand-accent-strong">',
              'link_after'     => '</span>',
          ]);
          ?>
        </nav>

        <nav class="footer-nav-block flex flex-col gap-4 py-6 border-y border-white/30 md:border-0 md:py-0" aria-labelledby="footer-col-2">
          <h3 id="footer-col-2" class="text-2xl font-bold leading-8 text-[#EEF6FC] font-['Public_Sans'] md:text-2xl md:font-light md:leading-8 md:text-brand-secondary md:font-primary">
            <?php echo esc_html($col2_heading ?: 'About us'); ?>
          </h3>
          <?php
          wp_nav_menu([
              'theme_location' => $menu_latest,
              'container'      => false,
              'menu_class'     => 'flex flex-col gap-4',
              'fallback_cb'    => '__return_empty_string',
              'link_before'    => '<span class="font-sans text-[14px] font-bold not-italic leading-[20px] text-white transition-colors duration-200 hover:text-brand-accent-strong">',
              'link_after'     => '</span>',
          ]);
          ?>
        </nav>

        <nav class="footer-nav-block flex flex-col gap-4 py-6 border-t border-white/30 md:border-0 md:py-0" aria-labelledby="footer-col-3">
          <h3 id="footer-col-3" class="text-2xl font-bold leading-8 text-[#EEF6FC] font-['Public_Sans'] md:text-2xl md:font-light md:leading-8 md:text-brand-secondary md:font-primary">
            <?php echo esc_html($col3_heading ?: 'Get involved'); ?>
          </h3>
          <?php
          wp_nav_menu([
              'theme_location' => $menu_getinvolved,
              'container'      => false,
              'menu_class'     => 'flex flex-col gap-4',
              'fallback_cb'    => '__return_empty_string',
              'link_before'    => '<span class="font-sans text-[14px] font-bold not-italic leading-[20px] text-white transition-colors duration-200 hover:text-brand-accent-strong">',
              'link_after'     => '</span>',
          ]);
          ?>
        </nav>

      </div>
    </div>
  </div>
  <?php endif; ?>

  <div style="background-color: <?php echo esc_attr($bottom_bg_color ?: '#F0F9FF'); ?>;">
    <div class="max-w-[1540px] mx-auto px-4 sm:px-8 lg:px-14 py-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div class="flex flex-wrap items-center gap-x-2 gap-y-1 max-[479px]:flex-col max-[479px]:items-start max-[479px]:w-full max-[479px]:gap-2 font-sans text-[12px] font-normal not-italic leading-[18px] text-content-muted">
        <?php if (!empty($legal_menu_options)) : ?>
          <div class="hidden max-[479px]:block max-[479px]:w-full">
            <label for="copyright-menu-select" class="sr-only">Copyright menu</label>
            <select
              id="copyright-menu-select"
              class="w-full rounded-pill border border-[#D0D5DD] bg-white px-4 py-2 font-sans text-[12px] font-normal leading-[18px] text-content-muted focus:outline-none focus:ring-2 focus:ring-brand-primary-hover"
              onchange="if (this.value) { window.location.href = this.value; }"
            >
              <option value="">Legal links</option>
              <?php foreach ($legal_menu_options as $option) : ?>
                <option value="<?php echo esc_url($option['url']); ?>">
                  <?php echo esc_html($option['title']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        <?php endif; ?>

        <span><?php echo esc_html($copyright_left); ?></span>
        <span class="text-content-muted max-[479px]:hidden" aria-hidden="true">|</span>

        <div class="max-[479px]:hidden">
          <?php
          wp_nav_menu([
              'theme_location' => $menu_legal,
              'container'      => false,
              'menu_class' => 'menu-copyright flex flex-wrap items-center gap-x-2 gap-y-1',
              'fallback_cb'    => '__return_empty_string',
              'link_before'    => '<span class="font-sans text-[12px] font-normal not-italic leading-[18px] text-content-muted transition-colors duration-200 hover:text-brand-primary-hover hover:underline underline-offset-2">',
              'link_after' => '</span><span class="px-3 text-content-muted" aria-hidden="true">|</span>',
          ]);
          ?>
        </div>
      </div>

      <div class="flex flex-wrap items-center gap-1 whitespace-nowrap font-sans text-[12px] font-normal not-italic leading-[18px] text-content-muted">
        <span><?php echo esc_html($credit_prefix ?: 'All Rights Reserved - Designed & Developed by'); ?></span>
        <?php if (is_array($credit_link) && !empty($credit_link['url'])) : ?>
          <a
            href="<?php echo esc_url($credit_link['url']); ?>"
            target="<?php echo esc_attr($credit_link['target'] ?? '_self'); ?>"
            class="font-sans text-[12px] font-normal not-italic leading-[18px] transition-colors duration-200 hover:text-brand-primary-hover hover:underline underline-offset-2 btn"
          >
            <?php echo esc_html($credit_link['title'] ?: 'Matrix Internet'); ?>
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</footer>

<style>
  /* Tablet-only footer layout tuning to avoid squashed columns */
  @media (min-width: 768px) and (max-width: 1084px) {
    .footer-main-layout {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      column-gap: 2rem;
      row-gap: 2rem;
    }

    .footer-brand-block {
      grid-column: 1 / -1;
      flex-direction: row;
      justify-content: space-between;
      align-items: flex-start;
      padding-bottom: 0;
    }

    .footer-nav-block {
      padding-top: 0;
      padding-bottom: 0;
      border-top: 0;
      border-bottom: 0;
    }
  }

  /* Mobile <=575px: max 4 social icons per row */
  @media (max-width: 575px) {
    .footer-social-links {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, max-content));
      gap: 0.75rem;
      justify-content: start;
      align-items: center;
    }
  }
</style>