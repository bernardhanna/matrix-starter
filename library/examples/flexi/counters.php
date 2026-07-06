<?php

$section_id = 'stats-' . wp_rand(1000, 999999);

$show_section = (bool) get_sub_field('show_section');
if (!$show_section) {
    return;
}

$heading_text = (string) get_sub_field('heading_text');
$heading_tag  = (string) get_sub_field('heading_tag');
$description  = get_sub_field('description');

$stats = get_sub_field('stats');
$running_groups_count = (int) wp_count_posts('running_group')->publish;

$image = get_sub_field('image');
$image_fallback_alt = (string) get_sub_field('image_fallback_alt');

$background_color   = (string) get_sub_field('background_color');
$heading_color      = (string) get_sub_field('heading_color');
$text_color         = (string) get_sub_field('text_color');
$stat_number_color  = (string) get_sub_field('stat_number_color');
$stat_text_color    = (string) get_sub_field('stat_text_color');
$image_bg_color     = (string) get_sub_field('image_bg_color');
$image_radius       = (string) get_sub_field('image_radius');

if ($image_radius === '') {
    $image_radius = 'rounded-lg';
}

$allowed_heading_tags = ['h1','h2','h3','h4','h5','h6','span','p'];
if (!in_array($heading_tag, $allowed_heading_tags, true)) {
    $heading_tag = 'h3';
}

$padding_classes = [];
if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
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

/** Image handling (alt + title from WP media) */
$image_url = '';
$image_id  = 0;

if (is_array($image)) {
    $image_url = (string) ($image['url'] ?? '');
    $image_id  = (int) ($image['ID'] ?? 0);
}

$image_alt = '';
$image_title = '';

if ($image_id > 0) {
    $image_alt = (string) get_post_meta($image_id, '_wp_attachment_image_alt', true);
    $image_title = (string) get_the_title($image_id);
}

if ($image_alt === '') {
    $image_alt = $image_fallback_alt !== '' ? $image_fallback_alt : 'Image';
}
if ($image_title === '') {
    $image_title = $image_alt;
}
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="flex overflow-hidden relative"
    role="region"
    aria-labelledby="<?php echo esc_attr($section_id . '-heading'); ?>"
    style="background-color: <?php echo esc_attr($background_color ?: '#ffffff'); ?>;"
>
    <div class="flex flex-col items-center w-full mx-auto max-w-[1208px] max-xl:px-5 <?php echo esc_attr($padding_class_string); ?>">
        <div class="w-full max-w-[1208px] mx-auto">
            <div class="flex flex-col gap-7 items-center self-start py-12 w-full h-auto md:gap-14 md:pt-16 md:pb-16 md:flex-row md:justify-start max-xl:px-5">

                <div class="flex flex-col gap-4 pr-5 min-w-0 md:justify-start md:items-start md:pr-8 md:flex-1">
                    <<?php echo esc_html($heading_tag); ?>
                        id="<?php echo esc_attr($section_id . '-heading'); ?>"
                        class="break-words text-left text-[24px] font-bold leading-[32px] font-['Public_Sans'] text-[var(--Blue-SR-500,#00628F)] w-full"
                        <?php if ($heading_color): ?>style="color: <?php echo esc_attr($heading_color); ?>;"<?php endif; ?>
                    >
                        <?php echo esc_html($heading_text ?: 'Solidarity in Numbers'); ?>
                    </<?php echo esc_html($heading_tag); ?>>

                    <?php if (!empty($description)) : ?>
                        <div
                            class="counters-description wp_editor break-words text-left text-[1rem] font-normal leading-[1.375rem] font-['Public_Sans'] text-[var(--Gray-600,#475467)] w-full"
                            <?php if ($text_color): ?>style="color: <?php echo esc_attr($text_color); ?>;"<?php endif; ?>
                        >
                            <?php echo wp_kses_post($description); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($stats) && is_array($stats)) : ?>
                        <div class="flex flex-col flex-wrap gap-4 self-start w-full md:items-start md:grid md:grid-cols-3" role="list" aria-label="Key statistics">
                            <?php foreach ($stats as $stat_index => $stat_item) : ?>
                                <?php
                                $show_stat = (bool) ($stat_item['show_stat'] ?? true);
                                if (!$show_stat) {
                                    continue;
                                }

                                $value_source = (string) ($stat_item['value_source'] ?? 'manual');
                                $value = (int) ($stat_item['value'] ?? 0);
                                if ($value_source === 'running_groups_count') {
                                    $value = $running_groups_count;
                                }
                                $suffix = (string) ($stat_item['suffix'] ?? '');
                                $format = (string) ($stat_item['format'] ?? 'number');
                                $stat_text = (string) ($stat_item['stat_text'] ?? '');

                                if ($format !== 'compact' && $format !== 'number') {
                                    $format = 'number';
                                }

                                $desc_id = $section_id . '-stat-desc-' . (int) $stat_index;
                                ?>
                                <div
                                    class="flex flex-col gap-4 md:flex-col md:justify-start md:items-start"
                                    role="listitem"
                                    x-data="srCounter({ value: <?php echo esc_attr($value); ?>, suffix: '<?php echo esc_attr($suffix); ?>', format: '<?php echo esc_attr($format); ?>' })"
                                    x-init="init($el)"
                                    x-bind:aria-label="a11yLabel"
                                    <?php if ($stat_text !== '') : ?>
                                        aria-describedby="<?php echo esc_attr($desc_id); ?>"
                                    <?php endif; ?>
                                >
                                    <h3
                                        class="break-words text-left text-[36px] font-bold leading-[44px] tracking-[-0.72px] lg:text-[60px] lg:leading-[72px] lg:tracking-[-1.2px] font-['Public_Sans'] text-[var(--Light-Blue-500,#008FC5)] w-full"
                                        <?php if ($stat_number_color): ?>style="color: <?php echo esc_attr($stat_number_color); ?>;"<?php endif; ?>
                                    >
                                        <!-- aria-hidden to avoid screen readers reading rapid changes -->
                                        <span aria-hidden="true">
                                            <span x-text="display"></span><span x-text="suffix"></span>
                                        </span>

                                        <!-- Screen reader stable value -->
                                        <span class="sr-only" x-text="a11yLabel"></span>
                                    </h3>

                                    <?php if ($stat_text !== '') : ?>
                                        <p
                                            id="<?php echo esc_attr($desc_id); ?>"
                                            class="break-words text-left text-[14px] font-normal leading-5 font-['Public_Sans'] text-[var(--Gray-600,#475467)] w-full"
                                            <?php if ($stat_text_color): ?>style="color: <?php echo esc_attr($stat_text_color); ?>;"<?php endif; ?>
                                        >
                                            <?php echo esc_html($stat_text); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div
                    class="<?php echo esc_attr($image_radius); ?> overflow-hidden bg-center md:flex-1 min-w-0"
                    
                >
                    <?php if ($image_url !== '') : ?>
                        <img
                            src="<?php echo esc_url($image_url); ?>"
                            alt="<?php echo esc_attr($image_alt); ?>"
                            title="<?php echo esc_attr($image_title); ?>"
                            loading="lazy"
                            decoding="async"
                            class="object-cover w-full h-full max-h-[385px] max-w-[433px] rounded-lg max-md:h-auto max-md:object-contain"
                        />
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</section>

<script>
(function () {
  function registerSrCounter() {
    if (window.__srCounterRegistered) return;

    // Alpine may not be on window depending on bundling. Try both.
    var AlpineRef = window.Alpine || (window.Alpine = window.Alpine);

    if (!AlpineRef || typeof AlpineRef.data !== 'function') return;

    window.__srCounterRegistered = true;

    AlpineRef.data('srCounter', function (opts) {
      opts = opts || {};

      return {
        value: Number(opts.value || 0),
        suffix: String(opts.suffix || ''),
        format: String(opts.format || 'number'),
        duration: 1200,
        started: false,
        display: '0',
        a11yLabel: '',

        init: function (el) {
          this.display = this.formatValue(0);
          this.a11yLabel = this.formatA11y(this.value);

          // Alpine can invoke init() automatically without args.
          // Fall back to this.$el and avoid throwing if not available yet.
          var targetEl = el || this.$el || null;
          if (!(targetEl instanceof Element)) {
            this.start();
            return;
          }

          // Start when visible
          if (!('IntersectionObserver' in window)) {
            this.start();
            return;
          }

          var self = this;
          var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
              if (entry.isIntersecting) {
                self.start();
                observer.disconnect();
              }
            });
          }, { threshold: 0.25 });

          observer.observe(targetEl);
        },

        start: function () {
          if (this.started) return;
          this.started = true;

          var startVal = 0;
          var endVal = this.value;
          var t0 = performance.now();
          var self = this;

          function tick(now) {
            var progress = Math.min((now - t0) / self.duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3);
            var next = Math.round(startVal + (endVal - startVal) * eased);

            self.display = self.formatValue(next);

            if (progress < 1) requestAnimationFrame(tick);
          }

          requestAnimationFrame(tick);
        },

        formatValue: function (n) {
          if (this.format === 'compact') {
            if (n >= 1000) {
              var k = n / 1000;
              var str = (n % 1000 === 0) ? String(Math.round(k)) : String(Math.round(k * 10) / 10);
              return str.replace(/\.0$/, '') + 'K';
            }
            return String(n);
          }

          try { return new Intl.NumberFormat().format(n); }
          catch (e) { return String(n); }
        },

        formatA11y: function (n) {
          if (this.suffix === '%') return String(n) + ' percent';
          if (this.suffix === '+') return String(n) + ' plus';
          return String(n) + (this.suffix ? ' ' + this.suffix : '');
        }
      };
    });
  }

  // If Alpine already exists, register now
  registerSrCounter();

  // Also register on alpine:init for cases where Alpine loads later
  document.addEventListener('alpine:init', registerSrCounter);
})();
</script>