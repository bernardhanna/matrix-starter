<?php
/**
 * Render legacy flexible_content rows from flat post meta when ACF have_rows() fails.
 */

function matrix_rd_flexi_layouts(int $post_id): array {
    $raw = get_post_meta($post_id, 'flexible_content', true);
    if (is_array($raw)) {
        return $raw;
    }
    if (is_string($raw) && $raw !== '') {
        $parsed = maybe_unserialize($raw);
        return is_array($parsed) ? $parsed : [];
    }
    return [];
}

function matrix_rd_flexi_meta(int $post_id, int $row, string $field) {
    return get_post_meta($post_id, "flexible_content_{$row}_{$field}", true);
}

function matrix_rd_flexi_meta_image(int $post_id, int $row, string $field): array {
    return matrix_rd_acf_image(matrix_rd_flexi_meta($post_id, $row, $field));
}

function matrix_rd_flexi_meta_link(int $post_id, int $row, string $field): array {
    return matrix_rd_acf_link(matrix_rd_flexi_meta($post_id, $row, $field));
}

/**
 * Does the current singular view contain a kudos/testimonial block?
 * Checks both the legacy meta-based flexi list and ACF-managed flexible content.
 */
function matrix_rd_page_has_kudos(): bool {
    if (! is_singular()) {
        return false;
    }
    $post_id = (int) get_queried_object_id();
    if (! $post_id) {
        return false;
    }

    foreach (matrix_rd_flexi_layouts($post_id) as $layout) {
        if ((string) $layout === 'kudos_block') {
            return true;
        }
    }

    foreach (['flexible_content_blocks', 'flexible_content'] as $field) {
        $raw = get_post_meta($post_id, $field, true);
        if (is_array($raw) && in_array('kudos_block', $raw, true)) {
            return true;
        }
    }

    return false;
}

/**
 * Enqueue Splide + the testimonial carousel initialiser on pages that render a
 * kudos block (e.g. Weddings & Events). The home page enqueues Splide already,
 * but interior flexi pages do not, so the slider never mounts without this.
 */
function matrix_rd_kudos_enqueue_assets(): void {
    if (is_admin() || ! matrix_rd_page_has_kudos()) {
        return;
    }

    if (! wp_style_is('splide', 'registered') && ! wp_style_is('splide', 'enqueued')) {
        wp_register_style('splide', 'https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/css/splide.min.css', [], '4.1.4');
    }
    if (! wp_script_is('splide', 'registered') && ! wp_script_is('splide', 'enqueued')) {
        wp_register_script('splide', 'https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/js/splide.min.js', [], '4.1.4', true);
    }
    wp_enqueue_style('splide');
    wp_enqueue_script('splide');

    $init = get_template_directory() . '/assets/js/rolling-donut-testimonials.js';
    wp_enqueue_script(
        'matrix-rd-testimonials',
        get_template_directory_uri() . '/assets/js/rolling-donut-testimonials.js',
        ['splide'],
        is_readable($init) ? (string) filemtime($init) : (string) get_option('theme_css_version', '1.0'),
        true
    );
}
add_action('wp_enqueue_scripts', 'matrix_rd_kudos_enqueue_assets', 30);

function matrix_rd_load_flexi_from_meta(int $post_id): void {
    $layouts = matrix_rd_flexi_layouts($post_id);
    foreach ($layouts as $row_index => $layout) {
        $layout = (string) $layout;
        $row    = (int) $row_index;

        switch ($layout) {
            case 'padding_block':
                matrix_rd_flexi_render_padding($post_id, $row);
                break;
            case 'imagewithtext_block':
                matrix_rd_flexi_render_imagewithtext($post_id, $row);
                break;
            case 'wedding_block':
                matrix_rd_flexi_render_wedding($post_id, $row);
                break;
            case 'editor_block':
                matrix_rd_flexi_render_editor($post_id, $row);
                break;
            case 'kudos_block':
                matrix_rd_flexi_render_kudos($post_id, $row);
                break;
            default:
                break;
        }
    }
}

function matrix_rd_flexi_render_padding(int $post_id, int $row): void {
    $settings = matrix_rd_flexi_meta($post_id, $row, 'spacing_settings');
    if (! is_array($settings) || $settings === []) {
        $pt = matrix_rd_flexi_meta($post_id, $row, 'spacing_settings_0_padding_top');
        $pb = matrix_rd_flexi_meta($post_id, $row, 'spacing_settings_0_padding_bottom');
        if ($pt || $pb) {
            $style = 'padding-top:' . esc_attr((string) ($pt ?: '0')) . 'rem';
            if ($pb !== '' && $pb !== null) {
                $style .= ';padding-bottom:' . esc_attr((string) $pb) . 'rem';
            }
            printf('<div class="w-full" style="%s"></div>', $style);
        }
        return;
    }
    echo '<div class="w-full py-8"></div>';
}

function matrix_rd_flexi_render_imagewithtext(int $post_id, int $row): void {
    $image   = matrix_rd_flexi_meta_image($post_id, $row, 'event_image_imgtxt');
    if ($image['url'] === '') {
        $image = matrix_rd_flexi_meta_image($post_id, $row, 'event_image');
    }
    $heading = (string) matrix_rd_flexi_meta($post_id, $row, 'event_heading_imgtxt');
    if ($heading === '') {
        $heading = (string) matrix_rd_flexi_meta($post_id, $row, 'event_heading');
    }
    $text = (string) matrix_rd_flexi_meta($post_id, $row, 'event_text_imgtxt');
    if ($text === '') {
        $text = (string) matrix_rd_flexi_meta($post_id, $row, 'event_text');
    }
    $button = matrix_rd_flexi_meta_link($post_id, $row, 'event_button_imgtxt');
    if ($button['url'] === '') {
        $button = matrix_rd_flexi_meta_link($post_id, $row, 'event_button');
    }
    $reverse = (bool) matrix_rd_flexi_meta($post_id, $row, 'reverse_layout_imgtxt')
        || (bool) matrix_rd_flexi_meta($post_id, $row, 'reverse_layout');
    $tag     = (string) matrix_rd_flexi_meta($post_id, $row, 'event_heading_tag') ?: 'h2';
    $flex    = $reverse ? 'flex-col-reverse md:flex-row-reverse' : 'flex-col-reverse md:flex-row';

    if ($image['url'] === '' && $heading === '' && $text === '') {
        return;
    }
    ?>
    <section class="event-flexi py-8">
      <div class="flex <?php echo esc_attr($flex); ?> max-w-max-1364 mx-auto px-4 gap-8">
        <div class="text-left md:w-1/2">
          <?php if ($image['url']) : ?>
          <img class="rounded-20px object-cover border-2 border-black-full w-full" src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>" />
          <?php endif; ?>
        </div>
        <div class="w-full content md:w-1/2 lg:flex lg:flex-col p-4 lg:pl-8">
          <?php if ($heading) : ?>
          <<?php echo tag_escape($tag); ?> class="pb-5 text-lg-font lg:text-xl-font font-reg420 leading-3xl"><?php echo esc_html($heading); ?></<?php echo tag_escape($tag); ?>>
          <?php endif; ?>
          <?php if ($text) : ?>
          <div class="w-full leading-none text-reg-font text-black-font"><?php echo wp_kses_post($text); ?></div>
          <?php endif; ?>
          <?php if ($button['url']) : ?>
          <a href="<?php echo esc_url($button['url']); ?>" class="btn mt-8 w-full max-w-[318px] text-white text-sm-md-font bg-black-full border-radius-large py-4 font-reg420 hover:bg-yellow-primary hover:text-black-full"><?php echo esc_html($button['title']); ?></a>
          <?php endif; ?>
        </div>
      </div>
    </section>
    <?php
}

function matrix_rd_flexi_render_wedding(int $post_id, int $row): void {
    $title = (string) matrix_rd_flexi_meta($post_id, $row, 'wedding_title');
    if ($title === '') {
        $title = (string) matrix_rd_flexi_meta($post_id, $row, 'event_heading');
    }
    $text = (string) matrix_rd_flexi_meta($post_id, $row, 'wedding_text');
    if ($text === '') {
        $text = (string) matrix_rd_flexi_meta($post_id, $row, 'event_text');
    }
    $pt = matrix_rd_flexi_meta($post_id, $row, 'wedding_padding_top') ?: '0';
    $pb = matrix_rd_flexi_meta($post_id, $row, 'wedding_padding_bottom') ?: '0';
    $count = matrix_rd_flexi_meta($post_id, $row, 'wedding_products');
    $count = is_numeric($count) ? (int) $count : 0;

    get_template_part('template-parts/flexi/partials/wedding-grid-styles');
    ?>
    <div class="flex flex-col items-center justify-center w-full px-4" style="padding-top:<?php echo esc_attr((string) $pt); ?>rem">
      <?php if ($title) : ?>
      <span class="leading-10 text-center text-black text-md-font lg:text-lg-font font-reg420"><?php echo esc_html($title); ?></span>
      <?php endif; ?>
      <?php if ($text) : ?>
      <p class="w-full py-8 mx-auto mb-12 font-light leading-none text-center text-reg-font lg:text-mob-md-font text-black-font lg:w-3/4"><?php echo esc_html($text); ?></p>
      <?php endif; ?>
    </div>
    <div class="mb-12 wedding-grid-container" style="padding-bottom:<?php echo esc_attr((string) $pb); ?>rem">
      <div class="max-w-6xl px-4 m-auto">
        <div class="grid-container">
          <?php for ($i = 0; $i < $count; $i++) :
              $main  = matrix_rd_flexi_meta_image($post_id, $row, "wedding_products_{$i}_wedding_image");
              $hover = matrix_rd_flexi_meta_image($post_id, $row, "wedding_products_{$i}_wedding_image_hover");
              $ptitle = (string) get_post_meta($post_id, "flexible_content_{$row}_wedding_products_{$i}_product_title", true);
              $pdesc  = (string) get_post_meta($post_id, "flexible_content_{$row}_wedding_products_{$i}_product_desc", true);
              if ($main['url'] === '') {
                  continue;
              }
              ?>
          <div class="grid-item">
            <div class="group">
              <div class="image-container">
                <img class="main-image" src="<?php echo esc_url($main['url']); ?>" alt="<?php echo esc_attr($main['alt']); ?>" />
                <?php if ($hover['url']) : ?>
                <img class="hover-image" src="<?php echo esc_url($hover['url']); ?>" alt="<?php echo esc_attr($hover['alt']); ?>" />
                <?php endif; ?>
              </div>
              <div class="product-details">
                <?php if ($ptitle) : ?><h3 class="leading-10 text-black text-mob-md-font font-reg420"><?php echo esc_html($ptitle); ?></h3><?php endif; ?>
                <?php if ($pdesc) : ?><p class="leading-7 text-black text-mob-md-font font-lighter font-laca"><?php echo esc_html($pdesc); ?></p><?php endif; ?>
              </div>
            </div>
          </div>
          <?php endfor; ?>
        </div>
      </div>
    </div>
    <?php
}

function matrix_rd_flexi_render_editor(int $post_id, int $row): void {
    $content = (string) matrix_rd_flexi_meta($post_id, $row, 'editor_content');
    if ($content === '') {
        return;
    }
    $pt = matrix_rd_flexi_meta($post_id, $row, 'padding_top') ?: '0';
    $pb = matrix_rd_flexi_meta($post_id, $row, 'padding_bottom') ?: '0';
    ?>
    <section class="editor-flexi w-full px-4 py-8" style="padding-top:<?php echo esc_attr((string) $pt); ?>;padding-bottom:<?php echo esc_attr((string) $pb); ?>">
      <div class="max-w-max-1364 m-auto e-content gutenburg"><?php echo apply_filters('the_content', $content); ?></div>
    </section>
    <?php
}

function matrix_rd_flexi_render_kudos(int $post_id, int $row): void {
    $title = (string) matrix_rd_flexi_meta($post_id, $row, 'kudos_title');
    $text  = (string) matrix_rd_flexi_meta($post_id, $row, 'kudos_text');
    $image = matrix_rd_flexi_meta_image($post_id, $row, 'kudos_main_image');
    if ($image['url'] === '') {
        $image = matrix_rd_flexi_meta_image($post_id, $row, 'kudos_image');
    }
    $selected = matrix_rd_flexi_meta($post_id, $row, 'selected_kudos');
    $ids      = is_array($selected) ? $selected : [];

    if ($title === '' && $text === '' && $image['url'] === '' && $ids === []) {
        return;
    }

    $heading_tag = (string) matrix_rd_flexi_meta($post_id, $row, 'kudos_heading_tag') ?: 'h2';
    $heading_tag = tag_escape($heading_tag) ?: 'h2';
    ?>
    <section class="w-full kudos-flexi">
        <svg class="relative z-0 block mobile:hidden -bottom-40" xmlns="http://www.w3.org/2000/svg" width="100%" viewBox="0 0 390 367" fill="none">
            <path d="M-141 37.5225C-141 37.5225 -77.4426 0 11.4498 0C141.326 0 211.171 76.1981 317.958 76.1981H320.04C426.829 76.1981 496.674 0 626.55 0C715.443 0 779 37.5225 779 37.5225V367L-141 354.403V37.5225Z" fill="black" />
        </svg>
        <svg class="relative z-0 hidden mobile:block -bottom-10 md:-bottom-20 laptop:-bottom-40" width="100%" viewBox="0 0 1728 367" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M-582 37.5225C-582 37.5225 -462.623 0 -295.659 0C-51.7174 0 79.4694 76.1981 280.043 76.1981H283.953C484.531 76.1981 615.717 0 859.659 0C1026.62 0 1146 37.5225 1146 37.5225V367H-582V37.5225Z" fill="black" />
            <path d="M2300 37.5225C2300 37.5225 2180.62 0 2013.66 0C1769.72 0 1638.53 76.1981 1437.96 76.1981H1434.05C1233.47 76.1981 1102.28 0 858.341 0C691.377 0 572 37.5225 572 37.5225V367H2300V37.5225Z" fill="black" />
        </svg>
        <?php if ($image['url']) : ?>
        <div class="flex justify-center w-full px-4 mx-auto text-center bg-black-full">
            <img class="w-full object-contain bg-black-full relative -top-10 text-center max-w-[852.196px] h-[92px] mobile:h-[129px] laptop:h-[219px]" src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt'] ?: 'Testimonials Image'); ?>">
        </div>
        <?php endif; ?>
        <div class="relative z-10 w-full bg-black-full">
            <div class="flex flex-col w-full max-w-[1368px] mx-auto px-4">
                <?php if ($title) : ?>
                <<?php echo $heading_tag; ?> class="leading-10 text-white text-md-font lg:text-lg-font font-reg420"><?php echo esc_html($title); ?></<?php echo $heading_tag; ?>>
                <?php endif; ?>
                <?php if ($text) : ?>
                <div class="pb-5 font-light text-white font-laca text-sm-font mobile:text-sm-md-font"><?php echo esc_html($text); ?></div>
                <?php endif; ?>
            </div>
            <?php if ($ids !== []) : ?>
            <div id="testimonial-slider" class="relative z-50 flex flex-row justify-start w-full mx-auto splide flex-flow flex-nowrap">
                <div class="splide__track w-full max-w-[1530px] ml-auto">
                    <div class="splide__list flex justify-start max-w-[1530px] cursor-pointer">
                        <?php foreach ($ids as $tid) :
                            $testimonial = matrix_rd_acf_post($tid);
                            if (! $testimonial) {
                                continue;
                            }
                            $job   = (string) get_field('kudos_job', $testimonial->ID);
                            $kimg  = get_field('kudos_image', $testimonial->ID);
                            ?>
                        <div class="splide__slide item p-6 border-3 border-white border-solid rounded-md-32 w-full mobile:w-[497px] mobile:min-w-[497px] h-auto mobile:h-[497px] flex justify-between flex-col">
                            <div class="pb-8">
                                <svg xmlns="http://www.w3.org/2000/svg" width="116" height="91" viewBox="0 0 116 91" fill="none">
                                    <path d="M37.8974 3.83038L36.9937 2.57693L35.5527 3.13498C26.3029 6.71719 18.6393 13.1057 12.5572 22.191L12.5464 22.2072L12.5358 22.2236C6.73513 31.2653 3.31594 40.9628 2.30361 51.2961C1.29403 61.6013 2.7919 70.4631 6.97054 77.7454L6.98376 77.7684L6.99759 77.7911C11.5807 85.3085 18.9009 89 28.5555 89C31.499 89 34.3238 88.4658 37.0151 87.3937L37.0579 87.3767L37.0998 87.3577C39.9984 86.0457 42.529 84.3267 44.6706 82.1939C46.7928 80.0806 48.4088 77.6843 49.4952 75.0102C50.8331 72.3183 51.5085 69.4741 51.5085 66.5C51.5085 63.3235 50.85 60.3635 49.5096 57.6528C48.4322 54.7476 46.819 52.2133 44.6706 50.0739C42.5222 47.9344 39.9783 46.3288 37.0634 45.2569C34.3708 43.9348 31.5276 43.2679 28.5555 43.2679H28.4829C29.1015 40.8154 29.8435 38.7475 30.6945 37.0453C32.0618 34.5567 34.023 31.902 36.6141 29.0831C39.3978 26.3177 43.0359 23.6216 47.5667 21.0094L49.4851 19.9034L48.1901 18.1072L37.8974 3.83038ZM100.389 3.83038L99.4852 2.57693L98.0442 3.13498C88.7944 6.71719 81.1308 13.1057 75.0487 22.191L75.0379 22.2072L75.0273 22.2236C69.2266 31.2653 65.8074 40.9628 64.7951 51.2961C63.7855 61.6013 65.2834 70.4631 69.462 77.7454L69.4753 77.7684L69.4891 77.7911C74.0722 85.3085 81.3924 89 91.047 89C93.9905 89 96.8153 88.4658 99.5066 87.3937L99.5494 87.3767L99.5913 87.3577C102.49 86.0457 105.02 84.3267 107.162 82.1939C109.284 80.0806 110.9 77.6843 111.987 75.0102C113.325 72.3183 114 69.4741 114 66.5C114 63.3234 113.341 60.3635 112.001 57.6528C110.924 54.7476 109.31 52.2133 107.162 50.0739C105.014 47.9344 102.47 46.3289 99.5549 45.2569C96.8623 43.9348 94.0191 43.2679 91.047 43.2679H90.9743C91.593 40.8154 92.335 38.7475 93.186 37.0453C94.5533 34.5567 96.5145 31.902 99.1056 29.0831C101.889 26.3177 105.527 23.6216 110.058 21.0094L111.977 19.9034L110.682 18.1072L100.389 3.83038Z" fill="#FFED56" stroke="white" stroke-width="4" />
                                </svg>
                                <div class="pt-8 text-white text-base-font mobile:text-reg-font">
                                    <?php echo wp_kses_post(apply_filters('the_content', $testimonial->post_content)); ?>
                                </div>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-white text-xs-font font-laca"><?php echo esc_html($testimonial->post_title); ?></span>
                                <?php if ($job) : ?>
                                <span class="pb-6 text-white text-xs-font mobile:text-xs-font font-laca"><?php echo esc_html($job); ?></span>
                                <?php endif; ?>
                                <?php if (is_array($kimg) && ! empty($kimg['url'])) : ?>
                                <img class="w-[40px] h-[40px]" width="40" height="40" src="<?php echo esc_url($kimg['url']); ?>" alt="<?php echo esc_attr($kimg['alt'] ?? ''); ?>">
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <svg class="relative z-0 hidden mobile:block -top-10 laptop:-top-40" xmlns="http://www.w3.org/2000/svg" width="100%" viewBox="0 0 1728 367" fill="none">
            <path d="M0 329.477C0 329.477 119.377 367 286.341 367C530.283 367 661.469 290.802 862.043 290.802H865.953C1066.53 290.802 1197.72 367 1441.66 367C1608.62 367 1728 329.477 1728 329.477V0H0V329.477Z" fill="black" />
        </svg>
        <svg class="relative z-0 block mobile:hidden -top-40" xmlns="http://www.w3.org/2000/svg" width="100%" viewBox="0 0 390 366" fill="none">
            <path d="M537 328.58C537 328.58 473.374 366 384.384 366C254.367 366 184.446 290.009 77.5428 290.009L75.4593 290.009C-31.446 290.009 -101.367 366 -231.384 366C-320.374 366 -384 328.58 -384 328.58L-384 1.10364e-05L537 12.563L537 328.58Z" fill="black" />
        </svg>
    </section>
    <?php
}
