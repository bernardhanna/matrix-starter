<?php

$section_id = 'team-' . wp_generate_uuid4();

$heading_text = get_sub_field('heading_text');
$heading_tag = get_sub_field('heading_tag');
$show_intro = get_sub_field('show_intro');
$intro_text = get_sub_field('intro_text');

$background_color = get_sub_field('background_color');
$heading_color = get_sub_field('heading_color');
$body_text_color = get_sub_field('body_text_color');
$image_radius = get_sub_field('image_radius');

$use_manual_people = get_sub_field('use_manual_people');
$manual_people = get_sub_field('manual_people');

$posts_per_page = get_sub_field('posts_per_page');
$role_filter = get_sub_field('role_filter');
$orderby = get_sub_field('orderby');
$order = get_sub_field('order');
$desktop_columns = (string) get_sub_field('desktop_columns');
$show_content_snippet_raw = get_sub_field('show_content_snippet');
$show_content_snippet = ($show_content_snippet_raw === null || $show_content_snippet_raw === '') ? true : (bool) $show_content_snippet_raw;

$padding_classes = [];
if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
        the_row();
        $screen_size = get_sub_field('screen_size');
        $padding_top = get_sub_field('padding_top');
        $padding_bottom = get_sub_field('padding_bottom');

        if ($screen_size !== '' && $padding_top !== '' && $padding_top !== null) {
            $padding_classes[] = $screen_size . ':pt-[' . $padding_top . 'rem]';
        }
        if ($screen_size !== '' && $padding_bottom !== '' && $padding_bottom !== null) {
            $padding_classes[] = $screen_size . ':pb-[' . $padding_bottom . 'rem]';
        }
    }
}
$padding_class_string = implode(' ', $padding_classes);

$allowed_heading_tags = ['h1','h2','h3','h4','h5','h6','span','p'];
if (!in_array($heading_tag, $allowed_heading_tags, true)) {
    $heading_tag = 'h3';
}

$heading_id = $section_id . '-heading';
$desktop_grid_class = ($desktop_columns === '3') ? 'lg:grid-cols-3' : 'lg:grid-cols-4';
$snippet_preview_max_chars = 217;

$query_args = [
    'post_type' => 'people',
    'post_status' => 'publish',
];

if (!empty($use_manual_people) && !empty($manual_people) && is_array($manual_people)) {
    $query_args['post__in'] = array_map('intval', $manual_people);
    $query_args['orderby'] = 'post__in';
    $query_args['posts_per_page'] = count($manual_people);
} else {
    $query_args['posts_per_page'] = -1; // show ALL people
    $query_args['orderby'] = !empty($orderby) ? $orderby : 'date';
    $query_args['order'] = !empty($order) ? $order : 'DESC';

    if (!empty($role_filter) && is_array($role_filter)) {
        $query_args['tax_query'] = [
            [
                'taxonomy' => 'people_role',
                'field' => 'term_id',
                'terms' => array_map('intval', $role_filter),
            ],
        ];
    }
}

$people_query = new WP_Query($query_args);
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="flex overflow-hidden relative"
    aria-labelledby="<?php echo esc_attr($heading_id); ?>"
    style="background-color: <?php echo esc_attr($background_color); ?>;"
>
    <div class="flex flex-col items-center w-full mx-auto max-w-[1120px] pt-5 pb-5 lg:py-12 max-xl:px-5 <?php echo esc_attr($padding_class_string); ?>">
        <div class="flex flex-col gap-8 w-full">
            <div class="flex flex-col gap-2 w-full">
                <<?php echo esc_html($heading_tag); ?>
                    id="<?php echo esc_attr($heading_id); ?>"
                    class="w-full break-words text-left text-[1.875rem] font-[600] leading-[2.375rem] font-['Public Sans']"
                    style="color: <?php echo esc_attr($heading_color); ?>;"
                >
                    <?php echo esc_html($heading_text); ?>
                </<?php echo esc_html($heading_tag); ?>>

                <?php if (!empty($show_intro) && !empty($intro_text)) { ?>
                    <div
                        class="wp_editor w-full break-words text-left text-[1rem] font-[400] leading-[1.375rem] font-['Public Sans']"
                        style="color: <?php echo esc_attr($body_text_color); ?>;"
                    >
                        <?php echo wp_kses_post($intro_text); ?>
                    </div>
                <?php } ?>
                </div>
          <?php if ($people_query->have_posts()) { ?>
              <div class="sr-people-grid grid grid-cols-2 gap-6 w-full md:grid-cols-3 <?php echo esc_attr($desktop_grid_class); ?>">
                  <?php while ($people_query->have_posts()) { ?>
                      <?php
                      $people_query->the_post();

                      $person_id = get_the_ID();
                      $person_name = get_the_title($person_id);

                      $thumb_id = get_post_thumbnail_id($person_id);
                      $thumb_url = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'large') : '';
                      $thumb_alt = $thumb_id ? get_post_meta($thumb_id, '_wp_attachment_image_alt', true) : '';
                      $thumb_title = $thumb_id ? get_the_title($thumb_id) : '';

                      if ($thumb_alt === '') {
                          $thumb_alt = $person_name ? $person_name : 'Team member photo';
                      }
                      if ($thumb_title === '') {
                          $thumb_title = $person_name ? $person_name : 'Team member';
                      }

                      $role_text = '';
                      $excerpt = get_the_excerpt($person_id);
                      if (!empty($excerpt)) {
                          $role_text = $excerpt;
                      } else {
                          $terms = get_the_terms($person_id, 'people_role');
                          if (!is_wp_error($terms) && !empty($terms)) {
                              $role_text = $terms[0]->name;
                          }
                      }

                      $content_snippet = '';
                      $content_snippet_preview = '';
                      $show_read_more = false;
                      if ($show_content_snippet) {
                          $raw_content = (string) get_post_field('post_content', $person_id);
                          if ($raw_content === '') {
                              $raw_content = (string) get_the_content(null, false, $person_id);
                          }
                          if ($raw_content === '') {
                              $raw_content = (string) get_the_excerpt($person_id);
                          }

                          $content_plain = trim((string) wp_strip_all_tags((string) strip_shortcodes($raw_content)));
                          if ($content_plain !== '') {
                              $content_snippet = $content_plain;
                              if (function_exists('mb_strlen') && function_exists('mb_substr')) {
                                  if (mb_strlen($content_snippet) > $snippet_preview_max_chars) {
                                      $truncated = mb_substr($content_snippet, 0, $snippet_preview_max_chars);
                                      $truncated = preg_replace('/\s+\S*$/u', '', $truncated);
                                      $content_snippet_preview = rtrim((string) $truncated, " \t\n\r\0\x0B.,;:!?") . '...';
                                      $show_read_more = true;
                                  } else {
                                      $content_snippet_preview = $content_snippet;
                                  }
                              } elseif (strlen($content_snippet) > $snippet_preview_max_chars) {
                                  $truncated = substr($content_snippet, 0, $snippet_preview_max_chars);
                                  $truncated = preg_replace('/\s+\S*$/', '', $truncated);
                                  $content_snippet_preview = rtrim((string) $truncated, " \t\n\r\0\x0B.,;:!?") . '...';
                                  $show_read_more = true;
                              } else {
                                  $content_snippet_preview = $content_snippet;
                              }
                          }
                      }
                      ?>
                      <article class="flex flex-col gap-4">
                          <div class="flex flex-col gap-4">
                              <div class="sr-person-card__imgwrap w-full overflow-hidden bg-[#e8ebf4] <?php echo esc_attr($image_radius); ?>">
                                  <?php if (!empty($thumb_url)) { ?>
                                      <img
                                          src="<?php echo esc_url($thumb_url); ?>"
                                          alt="<?php echo esc_attr($thumb_alt); ?>"
                                          title="<?php echo esc_attr($thumb_title); ?>"
                                          loading="lazy"
                                          decoding="async"
                                          class="sr-person-card__img w-full h-full rounded-lg"
                                      >
                                  <?php } else { ?>
                                      <div
                                          class="w-full h-full flex items-center justify-center bg-[#e8ebf4] <?php echo esc_attr($image_radius); ?>"
                                          role="img"
                                          aria-label="<?php echo esc_attr($thumb_alt); ?>"
                                      >
                                          <span class="text-sm" style="color: <?php echo esc_attr($body_text_color); ?>;">
                                              <?php echo esc_html($person_name); ?>
                                          </span>
                                      </div>
                                  <?php } ?>
                              </div>

                              <div class="flex flex-col gap-1">
                                  <p
                                      class="break-words text-left text-[1.125rem] font-[700] leading-[1.5rem] font-['Public Sans']"
                                      style="color: <?php echo esc_attr($body_text_color); ?>;"
                                  >
                                      <?php echo esc_html($person_name); ?>
                                  </p>

                                  <?php if (!empty($role_text)) { ?>
                                      <p
                                          class="break-words text-left text-[0.875rem] font-[400] leading-[1.25rem] font-['Public Sans']"
                                          style="color: <?php echo esc_attr($body_text_color); ?>;"
                                      >
                                          <?php echo esc_html($role_text); ?>
                                      </p>
                                  <?php } ?>

                                  <?php if (!empty($content_snippet_preview)) { ?>
                                      <div class="pt-1" data-team-snippet-wrap>
                                          <p
                                              class="break-words text-left text-[0.875rem] font-[400] leading-[1.25rem] font-['Public Sans']"
                                              style="color: <?php echo esc_attr($body_text_color); ?>;"
                                              data-team-snippet-content
                                              data-collapsed="<?php echo esc_attr($content_snippet_preview); ?>"
                                              data-expanded="<?php echo esc_attr($content_snippet); ?>"
                                          >
                                              <?php echo esc_html($content_snippet_preview); ?>
                                          </p>

                                          <?php if ($show_read_more) { ?>
                                              <button
                                                  type="button"
                                                  class="mt-2 inline-flex w-fit text-left text-[0.8125rem] font-[700] leading-[1.125rem] underline text-[var(--Blue-SR-400,#008BCC)] hover:text-[var(--Blue-SR-500,#00628F)] focus:outline-none focus-visible:ring-0 transition-colors duration-200"
                                                  data-team-snippet-toggle
                                                  aria-expanded="false"
                                              >
                                                  <?php echo esc_html__('Read more', 'matrix-starter'); ?>
                                              </button>
                                          <?php } ?>
                                      </div>
                                  <?php } ?>
                              </div>
                          </div>
                      </article>
                  <?php } ?>
              </div>
              <?php wp_reset_postdata(); ?>
          <?php } else { ?>
              <p class="text-sm text-left" style="color: <?php echo esc_attr($body_text_color); ?>;">
                  <?php echo esc_html__('No team members found.', 'matrix-starter'); ?>
              </p>
          <?php } ?>
        </div>
    </div>
</section>

<script>
(function() {
    var section = document.getElementById('<?php echo esc_js($section_id); ?>');
    if (!section) {
        return;
    }

    var toggles = section.querySelectorAll('[data-team-snippet-toggle]');
    toggles.forEach(function(toggle) {
        toggle.addEventListener('click', function() {
            var wrapper = toggle.closest('[data-team-snippet-wrap]');
            if (!wrapper) {
                return;
            }

            var content = wrapper.querySelector('[data-team-snippet-content]');
            if (!content) {
                return;
            }

            var isExpanded = toggle.getAttribute('aria-expanded') === 'true';
            if (isExpanded) {
                content.textContent = content.getAttribute('data-collapsed') || '';
                toggle.textContent = '<?php echo esc_js(__('Read more', 'matrix-starter')); ?>';
                toggle.setAttribute('aria-expanded', 'false');
            } else {
                content.textContent = content.getAttribute('data-expanded') || '';
                toggle.textContent = '<?php echo esc_js(__('Read less', 'matrix-starter')); ?>';
                toggle.setAttribute('aria-expanded', 'true');
            }
        });
    });
})();
</script>