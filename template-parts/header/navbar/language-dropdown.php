<?php
/**
 * Language dropdown for the navbar.
 * Opens on hover; styled as a white panel with no box shadow.
 * Uses manual country options from theme settings, otherwise a static list.
 */

$languages = [];
$nav_settings = get_field('navigation_settings_start', 'option') ?: [];
$show_country_picker = (bool) ($nav_settings['show_country_picker'] ?? false);

if (!$show_country_picker) {
    return;
}

$country_picker_options = $nav_settings['country_picker_options'] ?? [];

if (!empty($country_picker_options) && is_array($country_picker_options)) {
    foreach ($country_picker_options as $option) {
        $value = sanitize_title((string) ($option['value'] ?? ''));
        $label = trim((string) ($option['label'] ?? ''));
        $link = $option['link'] ?? null;
        $flag_icon = $option['flag_icon'] ?? null;

        $url = '#';
        $target = '';
        if (is_array($link) && !empty($link['url'])) {
            $url = $link['url'];
            $target = !empty($link['target']) ? $link['target'] : '';
        }

        $flag_url = null;
        if (is_array($flag_icon) && !empty($flag_icon['url'])) {
            $flag_url = $flag_icon['url'];
        }

        if ($value === '' || $label === '') {
            continue;
        }

        $languages[] = [
            'value' => $value,
            'label' => $label,
            'url'   => $url,
            'flag'  => $flag_url,
            'target' => $target,
        ];
    }
}

if (empty($languages)) {
    // Fallback when no manual options are configured
    $languages = [
        ['value' => 'ie', 'label' => 'Ireland', 'url' => '#', 'flag' => 'https://api.builder.io/api/v1/image/assets/f35586c581c84ecf82b6de32c55ed39e/ab16b90e79c12d127d396904f040cf46e7cb5eaa?placeholderIfAbsent=true', 'target' => ''],
        ['value' => 'uk', 'label' => 'United Kingdom', 'url' => '#', 'flag' => null, 'target' => ''],
        ['value' => 'australia', 'label' => 'Australia', 'url' => '#', 'flag' => 'https://api.builder.io/api/v1/image/assets/f35586c581c84ecf82b6de32c55ed39e/01e57acce2567c69db9be0aa06c4f52ee1a53efd?placeholderIfAbsent=true', 'target' => ''],
        ['value' => 'global', 'label' => 'Global', 'url' => '#', 'flag' => null, 'target' => ''],
    ];
}

// In multisite, options are per-site. Merge network sites so IE/UK/Global are always present,
// while preserving any manual labels/flags configured in theme options.
if (is_multisite()) {
    $manual_languages = $languages;
    $manual_by_value = [];
    foreach ($manual_languages as $manual_lang) {
        $manual_value = sanitize_title((string) ($manual_lang['value'] ?? ''));
        if ($manual_value !== '') {
            $manual_by_value[$manual_value] = $manual_lang;
        }
    }

    $network_languages = [];
    $sites = get_sites([
        'number'   => 0,
        'deleted'  => 0,
        'archived' => 0,
        'spam'     => 0,
    ]);

    foreach ($sites as $site) {
        $blog_id = (int) ($site->blog_id ?? 0);
        if ($blog_id <= 0) {
            continue;
        }

        $url = get_home_url($blog_id, '/');
        $host = strtolower((string) wp_parse_url($url, PHP_URL_HOST));
        $path = trim((string) wp_parse_url($url, PHP_URL_PATH), '/');
        $host_no_www = preg_replace('/^www\./', '', $host);

        $value = sanitize_title((string) $site->path);
        if ($value === '') {
            if (strpos($host_no_www, 'uk.') === 0 || strpos($host_no_www, '.co.uk') !== false || $path === 'uk') {
                $value = 'uk';
            } elseif (strpos($host_no_www, 'global.') === 0 || strpos($host_no_www, '.com') !== false || $path === 'global') {
                $value = 'global';
            } else {
                $value = 'ie';
            }
        }

        $label = get_blog_option($blog_id, 'blogname', '');
        if (!is_string($label) || trim($label) === '') {
            if ($value === 'uk') {
                $label = 'United Kingdom';
            } elseif ($value === 'global') {
                $label = 'Global';
            } else {
                $label = 'Ireland';
            }
        }

        $entry = [
            'value'  => $value,
            'label'  => trim((string) $label),
            'url'    => $url,
            'flag'   => null,
            'target' => '',
        ];

        if (isset($manual_by_value[$value]) && is_array($manual_by_value[$value])) {
            $manual_entry = $manual_by_value[$value];
            if (!empty($manual_entry['label'])) {
                $entry['label'] = (string) $manual_entry['label'];
            }
            if (!empty($manual_entry['flag'])) {
                $entry['flag'] = (string) $manual_entry['flag'];
            }
            if (!empty($manual_entry['target'])) {
                $entry['target'] = (string) $manual_entry['target'];
            }
        }

        $network_languages[] = $entry;
    }

    if (!empty($network_languages)) {
        $languages = $network_languages;
    }
}

if (empty($languages)) {
    return;
}

$current = $languages[0] ?? null;

// Auto-select current country by matching the current site URL/host to option links.
$current_site_url = home_url('/');
$current_site_host = (string) wp_parse_url($current_site_url, PHP_URL_HOST);
$current_site_path = (string) wp_parse_url($current_site_url, PHP_URL_PATH);
$current_site_path = untrailingslashit($current_site_path);

$normalize_host = static function ($host) {
    $host = strtolower((string) $host);
    return preg_replace('/^www\./', '', $host);
};

$current_site_host = $normalize_host($current_site_host);

foreach ($languages as $lang) {
    $lang_url = (string) ($lang['url'] ?? '');
    if ($lang_url === '' || $lang_url === '#') {
        continue;
    }

    $lang_host = (string) wp_parse_url($lang_url, PHP_URL_HOST);
    $lang_path = (string) wp_parse_url($lang_url, PHP_URL_PATH);
    $lang_path = untrailingslashit($lang_path);

    if ($normalize_host($lang_host) !== $current_site_host) {
        continue;
    }

    // If host matches and no path is specified, this is the active country.
    if ($lang_path === '' || $lang_path === '/') {
        $current = $lang;
        break;
    }

    // If a path is specified, require exact or nested path match.
    if (
        $current_site_path === $lang_path ||
        strpos($current_site_path . '/', $lang_path . '/') === 0
    ) {
        $current = $lang;
        break;
    }
}
?>

<div
  id="language-dropdown"
  class="relative flex gap-1 justify-center items-center px-1 my-auto w-12 min-h-12 rounded-[1000px]"
  x-data="{ open: false }"
  @mouseenter="open = true"
  @mouseleave="open = false"
>
  <div class="w-full text-sm leading-none text-slate-900">
    <div class="rounded shadow-none">
      <!-- Compact trigger in nav (hover target): flag + arrow -->
      <button
        type="button"
        class="flex gap-1 justify-center items-center px-1 py-2.5 w-full bg-transparent rounded-t transition-colors duration-200"
        aria-haspopup="true"
        :aria-expanded="open"
        aria-labelledby="language-dropdown-label"
        id="language-dropdown-trigger"
        @click.prevent="open = !open"
      >
        <?php if (!empty($current['flag'])) : ?>
          <img
            src="<?php echo esc_url($current['flag']); ?>"
            alt=""
            class="object-contain w-6 h-6 shrink-0"
          />
        <?php else : ?>
          <div class="flex w-6 h-6 bg-gray-200 rounded shrink-0" aria-hidden="true"></div>
        <?php endif; ?>
        <span class="sr-only" id="language-dropdown-label"><?php echo esc_html($current['label'] ?? 'Language'); ?></span>
        <i
          class="text-sm transition-transform duration-200 fa-solid fa-chevron-down shrink-0 text-slate-700"
          :style="open ? 'transform: rotate(180deg)' : 'transform: rotate(0deg)'"
          aria-hidden="true"
        ></i>
      </button>

      <!-- Dropdown panel: list of language options only, no shadow -->
      <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="absolute left-0 top-full z-10 mt-0 w-full rounded-b border-t border-gray-100 shadow-none"
        x-cloak
      >
        <ul
          class="rounded-b rounded-t border-t border-gray-100 bg-white min-w-[200px]"
          role="listbox"
          aria-labelledby="language-dropdown-label"
          id="language-dropdown-menu"
        >
          <?php foreach ($languages as $lang) : ?>
            <li role="option" aria-selected="<?php echo ($current['value'] ?? '') === ($lang['value'] ?? '') ? 'true' : 'false'; ?>" tabindex="-1">
              <a
                href="<?php echo esc_url($lang['url']); ?>"
                class="flex items-center px-4 py-2.5 w-full text-left transition-colors duration-200 btn hover:bg-gray-50 focus:bg-gray-50"
                data-value="<?php echo esc_attr($lang['value']); ?>"
                <?php if (!empty($lang['target'])) : ?>target="<?php echo esc_attr($lang['target']); ?>"<?php endif; ?>
              >
                <div class="flex gap-2 items-center">
                  <?php if (!empty($lang['flag'])) : ?>
                    <img src="<?php echo esc_url($lang['flag']); ?>" alt="" class="object-contain w-8 h-8 shrink-0" />
                  <?php else : ?>
                    <div class="flex w-8 h-8 bg-gray-200 rounded shrink-0" aria-hidden="true"></div>
                  <?php endif; ?>
                  <span class="text-slate-900"><?php echo esc_html($lang['label']); ?></span>
                </div>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
</div>
