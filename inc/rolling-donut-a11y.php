<?php
/**
 * Accessibility enhancements for third-party / plugin output that we cannot edit
 * at the source (e.g. the WooCommerce "Save & Share Cart" email form fields).
 *
 * Theme-owned markup is labelled directly in the templates. This file only adds
 * accessible names to controls that render without an associated <label>,
 * aria-label, aria-labelledby, or non-empty title attribute, so they stop
 * failing the WCAG "form elements must have labels" rule.
 */

defined('ABSPATH') || exit;

/**
 * Load the most recent accessibility scan report produced by
 * `npm run test:a11y:full` (scripts/run-a11y.js writes to tests/a11y-report/).
 *
 * @return array<string,mixed>|null Decoded report payload, or null if none.
 */
function matrix_rd_a11y_latest_report(): ?array {
    $dir = get_template_directory() . '/tests/a11y-report';
    if (! is_dir($dir)) {
        return null;
    }

    $files = glob($dir . '/a11y-*.json');
    if (empty($files)) {
        return null;
    }

    // Filenames are ISO-timestamped, so a reverse sort puts the newest first.
    rsort($files, SORT_STRING);

    $contents = file_get_contents($files[0]);
    if ($contents === false) {
        return null;
    }

    $data = json_decode($contents, true);

    return is_array($data) ? $data : null;
}

/**
 * Human-friendly label for a scanned path.
 */
function matrix_rd_a11y_path_label(string $path): string {
    $path = trim($path);
    if ($path === '' || $path === '/') {
        return __('Home', 'matrix-starter');
    }

    $slug = trim($path, '/');
    $slug = preg_replace('#^product/#', '', $slug);
    $slug = str_replace(array('-', '/'), ' ', $slug);

    return ucwords($slug);
}

/**
 * Render the latest accessibility scores as an accessible table.
 */
function matrix_rd_a11y_render_scores(): string {
    $report = matrix_rd_a11y_latest_report();
    if ($report === null || empty($report['results'])) {
        return '';
    }

    $rows    = array();
    $total   = 0;
    $counted = 0;

    foreach ($report['results'] as $result) {
        if (! empty($result['error'])) {
            continue;
        }
        $url   = isset($result['url']) ? (string) $result['url'] : '';
        $base  = isset($report['baseUrl']) ? (string) $report['baseUrl'] : '';
        $path  = $base !== '' ? str_replace($base, '', $url) : $url;
        $score = isset($result['score']) ? (int) $result['score'] : 0;

        $rows[]   = array(
            'label'      => matrix_rd_a11y_path_label($path),
            'score'      => $score,
            'violations' => isset($result['violationCount']) ? (int) $result['violationCount'] : 0,
        );
        $total   += $score;
        $counted += 1;
    }

    if ($counted === 0) {
        return '';
    }

    $average  = (int) round($total / $counted);
    $scanned  = isset($report['scannedAt']) ? strtotime((string) $report['scannedAt']) : false;
    $date_str = $scanned ? date_i18n(get_option('date_format'), $scanned) : '';

    $score_class = static function (int $score): string {
        if ($score >= 90) {
            return 'bg-yellow-primary text-black-full';
        }
        if ($score >= 80) {
            return 'bg-black-full text-yellow-primary';
        }
        return 'bg-red-critical text-white';
    };

    ob_start();
    ?>
    <section class="rd-a11y-scores w-full" aria-labelledby="rd-a11y-scores-heading">
      <div class="flex flex-col items-center mb-8 text-center">
        <h2 id="rd-a11y-scores-heading" class="text-lg-font font-reg420 text-black-full"><?php esc_html_e('Our accessibility scores', 'matrix-starter'); ?></h2>
        <p class="mt-2 max-w-max-720 text-base-font font-lighter text-black-full">
          <?php esc_html_e('We continuously audit our most-visited pages with automated accessibility testing (axe-core, WCAG 2.1 A/AA). The latest results are shown below.', 'matrix-starter'); ?>
        </p>
        <div class="flex items-center gap-4 mt-6">
          <span class="flex items-center justify-center w-24 h-24 rounded-full <?php echo esc_attr($score_class($average)); ?> text-xl-font font-reg420" aria-hidden="true"><?php echo esc_html((string) $average); ?></span>
          <span class="text-base-font font-medium text-black-full text-left">
            <?php
            printf(
                /* translators: %d: average accessibility score out of 100. */
                esc_html__('Average score: %d / 100', 'matrix-starter'),
                (int) $average
            );
            ?>
            <?php if ($date_str !== '') : ?>
              <br /><span class="text-xs-font font-lighter"><?php printf(esc_html__('Last tested %s', 'matrix-starter'), esc_html($date_str)); ?></span>
            <?php endif; ?>
          </span>
        </div>
      </div>

      <div class="overflow-x-auto mx-auto max-w-max-720">
        <table class="w-full text-left border-collapse">
          <caption class="sr-only"><?php esc_html_e('Accessibility scores by page', 'matrix-starter'); ?></caption>
          <thead>
            <tr class="border-b-2 border-black-full">
              <th scope="col" class="py-3 pr-4 text-base-font font-reg420 text-black-full"><?php esc_html_e('Page', 'matrix-starter'); ?></th>
              <th scope="col" class="py-3 px-4 text-base-font font-reg420 text-black-full"><?php esc_html_e('Score', 'matrix-starter'); ?></th>
              <th scope="col" class="py-3 pl-4 text-base-font font-reg420 text-black-full"><?php esc_html_e('Issues', 'matrix-starter'); ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $row) : ?>
            <tr class="border-b border-grey-border">
              <th scope="row" class="py-3 pr-4 text-base-font font-lighter text-black-full"><?php echo esc_html($row['label']); ?></th>
              <td class="py-3 px-4">
                <span class="inline-flex items-center justify-center min-w-[3rem] px-3 py-1 rounded-full text-sm-font font-reg420 <?php echo esc_attr($score_class($row['score'])); ?>"><?php echo esc_html($row['score'] . '/100'); ?></span>
              </td>
              <td class="py-3 pl-4 text-base-font font-lighter text-black-full"><?php echo esc_html((string) $row['violations']); ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
    <?php

    return (string) ob_get_clean();
}

add_action('wp_footer', static function (): void {
    ?>
    <script>
    (function () {
        function labelOrphanControls() {
            var controls = document.querySelectorAll('input, textarea, select');
            Array.prototype.forEach.call(controls, function (el) {
                var type = (el.getAttribute('type') || '').toLowerCase();
                if (['hidden', 'submit', 'button', 'reset', 'image'].indexOf(type) !== -1) {
                    return;
                }
                var hasName = (el.labels && el.labels.length > 0) ||
                    el.hasAttribute('aria-label') ||
                    el.hasAttribute('aria-labelledby') ||
                    (el.getAttribute('title') || '').trim() !== '';
                if (hasName) {
                    return;
                }
                var source = el.getAttribute('placeholder') ||
                    el.getAttribute('name') ||
                    el.id || '';
                source = source
                    .replace(/cxecrt[-_]?/i, '')
                    .replace(/[\[\]]/g, ' ')
                    .replace(/[-_]+/g, ' ')
                    .trim();
                if (!source) {
                    return;
                }
                el.setAttribute('aria-label', source.charAt(0).toUpperCase() + source.slice(1));
            });
        }

        // The "Save & Share Cart" plugin injects its slide-out markup as direct
        // children of <body>, outside any landmark, which fails the WCAG "region"
        // rule. Mark those top-level containers as labelled regions IN PLACE (no
        // DOM moves, so the plugin's own behaviour is untouched).
        function containCartTools() {
            var index = 0;
            Array.prototype.forEach.call(document.body.children, function (el) {
                var className = (typeof el.className === 'string') ? el.className : '';
                if (!/cxecrt/.test(className)) {
                    return;
                }
                if (el.getAttribute('role')) {
                    return;
                }
                var hasContent = el.textContent.trim() !== '' ||
                    el.querySelector('input, textarea, select, button, a');
                if (!hasContent) {
                    return;
                }
                el.setAttribute('role', 'region');
                el.setAttribute(
                    'aria-label',
                    index === 0 ? 'Save and share your cart' : 'Cart sharing tools ' + (index + 1)
                );
                index += 1;
            });
        }

        function enhance() {
            labelOrphanControls();
            containCartTools();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', enhance);
        } else {
            enhance();
        }
        // Run again after full load: the plugin appends some nodes late.
        window.addEventListener('load', enhance);
    }());
    </script>
    <?php
}, 99);
