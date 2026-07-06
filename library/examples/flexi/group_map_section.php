<?php
/**
 * Frontend: Group Map Section
 * Displays running groups on an interactive map with call-to-action content
 */

if (!defined('ABSPATH')) exit;

// -------------------------------------------------
// ACF: Content
// -------------------------------------------------
$heading               = get_sub_field('heading') ?: 'Find your tribe.';
$heading_tag           = get_sub_field('heading_tag') ?: 'h2';
$description           = get_sub_field('description') ?: 'You don\'t have to run alone. Use our map to find a Sanctuary Runners group near you.<br>Whether you want to run, jog, or just walk and have a coffee, there is a place for you here. Simply enter your city or use your location to see where we meet this week.';
$secondary_heading     = get_sub_field('secondary_heading') ?: 'No group in your area?';
$secondary_heading_tag = get_sub_field('secondary_heading_tag') ?: 'h3';
$start_group_button    = get_sub_field('start_group_button');
$find_groups_button    = get_sub_field('find_groups_button');
$reverse_layout        = (bool) get_sub_field('reverse_layout');

// -------------------------------------------------
// ACF: Map settings
// -------------------------------------------------
// Default: whole of Ireland
$map_center_lat = get_sub_field('map_center_lat') ?: 53.35;
$map_center_lng = get_sub_field('map_center_lng') ?: -8;
$map_zoom       = get_sub_field('map_zoom') ?: 6;

// Default to OSM so the map always renders even without a Jawg token
$tile_provider  = get_sub_field('tile_provider') ?: 'osm';
$tile_api_key   = get_sub_field('tile_api_key') ?: '';

// -------------------------------------------------
// ACF: Design
// -------------------------------------------------
$background_color = get_sub_field('background_color') ?: '#fef3c7';

// -------------------------------------------------
// ACF: Padding settings (optional repeater)
// -------------------------------------------------
$padding_classes = [];
if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
        the_row();
        $screen_size    = (string) get_sub_field('screen_size');
        $padding_top    = get_sub_field('padding_top');
        $padding_bottom = get_sub_field('padding_bottom');

        if ($screen_size !== '' && $padding_top !== null) {
            $padding_classes[] = "{$screen_size}:pt-[{$padding_top}rem]";
        }
        if ($screen_size !== '' && $padding_bottom !== null) {
            $padding_classes[] = "{$screen_size}:pb-[{$padding_bottom}rem]";
        }
    }
}

// -------------------------------------------------
// Unique section ID
// -------------------------------------------------
$section_id = 'group-map-' . wp_generate_uuid4();

// -------------------------------------------------
// Fetch Running Groups (markers)
// IMPORTANT: read coords from postmeta directly (most reliable)
// -------------------------------------------------
$running_group_ids = get_posts([
    'post_type'      => 'running_group',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'fields'         => 'ids',
]);

$groups_payload = [];
foreach ($running_group_ids as $group_id) {
    $lat_raw = get_post_meta($group_id, 'latitude', true);
    $lng_raw = get_post_meta($group_id, 'longitude', true);

    // Fallbacks for legacy/alternate field naming or map-style ACF fields.
    if ($lat_raw === '' || $lat_raw === null) $lat_raw = get_post_meta($group_id, 'lat', true);
    if ($lng_raw === '' || $lng_raw === null) $lng_raw = get_post_meta($group_id, 'lng', true);
    if ($lat_raw === '' || $lat_raw === null) $lat_raw = get_field('latitude', $group_id);
    if ($lng_raw === '' || $lng_raw === null) $lng_raw = get_field('longitude', $group_id);
    if ($lat_raw === '' || $lat_raw === null) $lat_raw = get_field('lat', $group_id);
    if ($lng_raw === '' || $lng_raw === null) $lng_raw = get_field('lng', $group_id);

    if ($lat_raw === '' || $lat_raw === null || $lng_raw === '' || $lng_raw === null) {
        $location = get_field('location', $group_id);
        if (is_array($location)) {
            $lat_raw = $lat_raw === '' || $lat_raw === null ? ($location['lat'] ?? '') : $lat_raw;
            $lng_raw = $lng_raw === '' || $lng_raw === null ? ($location['lng'] ?? '') : $lng_raw;
        }
    }

    $lat = is_string($lat_raw) ? str_replace(',', '.', trim($lat_raw)) : $lat_raw;
    $lng = is_string($lng_raw) ? str_replace(',', '.', trim($lng_raw)) : $lng_raw;

    if ($lat === '' || $lng === '' || !is_numeric($lat) || !is_numeric($lng)) {
        continue;
    }

    $show_popup_link = get_field('show_map_popup_link', $group_id);
    $show_popup_link = ($show_popup_link === 1 || $show_popup_link === '1' || $show_popup_link === true);

    $group_item = [
        'id'           => (int) $group_id,
        'title'        => (string) get_the_title($group_id),
        'lat'          => (float) $lat,
        'lng'          => (float) $lng,
        'address'      => (string) get_field('address', $group_id),
        'meeting_time' => (string) get_field('meeting_time', $group_id),
        'contact_info' => (string) get_field('contact_info', $group_id),
    ];

    if ($show_popup_link) {
        $group_item['url'] = (string) get_permalink($group_id);
    }

    $groups_payload[] = $group_item;
}
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="relative overflow-hidden <?php echo esc_attr(implode(' ', $padding_classes)); ?>"
    style="background-color: <?php echo esc_attr($background_color); ?>;"
    aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
>
    <div class="px-5 py-16 pt-6 pb-6 md:pt-16 md:pb-16 mx-auto w-full max-w-[1136px] lg:px-10">

        <div class="grid grid-cols-1 gap-10 items-start lg:grid-cols-12">

            <!-- Text Content -->
            <article class="flex flex-col justify-center p-0 pb-0 w-full h-full md:pt-8 md:pb-8 lg:col-span-5 <?php echo $reverse_layout ? 'lg:order-2' : 'lg:order-1'; ?>" role="article">
                <header>
                    <?php if (!empty($heading)): ?>
                        <<?php echo esc_attr($heading_tag); ?>
                            id="<?php echo esc_attr($section_id); ?>-heading"
                            class="font-['Public_Sans'] text-[24px] font-bold not-italic leading-[32px] text-[var(--Blue-SR-500,#00628F)] sm:text-[36px] sm:leading-[44px] sm:tracking-[-0.72px]"
                        >
                            <?php echo esc_html($heading); ?>
                        </<?php echo esc_attr($heading_tag); ?>>
                    <?php endif; ?>

                    <?php if (!empty($description)): ?>
                        <div class="mt-4 text-base leading-6 text-sky-950 lg:max-w-[380px] wp_editor max-sm:[&_p]:!text-[16px] max-sm:[&_p]:!font-normal max-sm:[&_p]:!not-italic max-sm:[&_p]:!leading-[22px]">
                            <?php echo wp_kses_post($description); ?>
                        </div>
                    <?php endif; ?>
                </header>

                <?php if (!empty($secondary_heading) || !empty($start_group_button)): ?>
                    <div class="mt-4 font-bold">
                        <?php if (!empty($secondary_heading)): ?>
                            <<?php echo esc_attr($secondary_heading_tag); ?> class="text-lg leading-none text-sky-950">
                                <?php echo esc_html($secondary_heading); ?>
                            </<?php echo esc_attr($secondary_heading_tag); ?>>
                        <?php endif; ?>

                        <?php if ($start_group_button && is_array($start_group_button) && !empty($start_group_button['url']) && !empty($start_group_button['title'])): ?>
                            <div class="mt-4">
                                <a
                                    href="<?php echo esc_url($start_group_button['url']); ?>"
                                    class="group-map-start-btn inline-flex w-full items-center justify-center rounded-full border border-[var(--Blue-SR-500,#00628F)] bg-transparent px-4 py-0 font-['Public_Sans'] text-[var(--Blue-SR-500,#00628F)] hover:border-[var(--Turquoise-500,#1C959B)] active:border-[var(--Blue-SR-500,#00628F)] active:bg-[var(--Blue-SR-10,#EBF9FF)] focus:outline-none focus-visible:border-[var(--Turquoise-500,#1C959B)] focus-visible:bg-[var(--Blue-SR-10,#EBF9FF)] focus-visible:ring-0 transition-colors duration-200 btn sm:px-6 sm:py-4 sm:text-[14px] sm:font-bold sm:leading-5"
                                    target="<?php echo esc_attr($start_group_button['target'] ?? '_self'); ?>"
                                    aria-label="<?php echo esc_attr($start_group_button['title']); ?>"
                                >
                                    <?php echo esc_html($start_group_button['title']); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </article>

            <!-- Map Column -->
            <div
                class="isolate relative lg:col-span-7 <?php echo $reverse_layout ? 'lg:order-1' : 'lg:order-2'; ?>"
                x-data="{ mapQuery: '' }"
            >
                <div
                    id="<?php echo esc_attr($section_id); ?>-map"
                    class="relative z-0 w-full h-[504px] md:h-[540px] rounded-lg overflow-hidden"
                    data-leaflet
                    data-provider="<?php echo esc_attr($tile_provider); ?>"
                    data-token="<?php echo esc_attr($tile_api_key); ?>"
                    data-lat="<?php echo esc_attr($map_center_lat); ?>"
                    data-lng="<?php echo esc_attr($map_center_lng); ?>"
                    data-zoom="<?php echo esc_attr($map_zoom); ?>"
                    role="application"
                    aria-label="Interactive map showing running group locations"
                ></div>

                <!-- After map in DOM + high z-index so the bar sits visually on top, centered -->
                <div
                    id="<?php echo esc_attr($section_id); ?>-map-search-wrap"
                    class="absolute inset-x-0 top-4 z-[8000] flex justify-center px-4 pointer-events-none md:top-5"
                    role="search"
                >
                    <label class="sr-only" for="<?php echo esc_attr($section_id); ?>-map-search">
                        <?php echo esc_html__('Search groups by city or county', 'matrix-starter'); ?>
                    </label>
                    <div
                        class="flex h-[38px] max-h-[38px] w-[258px] max-w-full shrink-0 items-center justify-between rounded-[1000px] border border-[var(--Gray-300,#D0D5DD)] bg-white px-6 py-0 pointer-events-auto sm:h-12 sm:max-h-none"
                    >
                        <input
                            id="<?php echo esc_attr($section_id); ?>-map-search"
                            type="search"
                            name="group_map_search"
                            autocomplete="off"
                            x-model="mapQuery"
                            @input.debounce.300ms="window.srGroupMapApplyFilter && window.srGroupMapApplyFilter('<?php echo esc_js($section_id); ?>', mapQuery)"
                            @keydown.enter.prevent="window.srGroupMapApplyFilter && window.srGroupMapApplyFilter('<?php echo esc_js($section_id); ?>', mapQuery)"
                            placeholder="<?php echo esc_attr__('Search by city or county...', 'matrix-starter'); ?>"
                            class="h-full min-w-0 flex-1 !border-0 bg-transparent !pl-0 pr-2 font-['Public_Sans'] text-[12px] font-normal not-italic leading-[18px] text-[var(--Blue-SR-500,#00628F)] placeholder:text-[var(--Blue-SR-500,#00628F)]/60 !outline-none !ring-0 focus:!border-0 focus:!ring-0 sm:text-[14px] sm:leading-[20px]"
                        />
                        <button
                            type="button"
                            class="flex h-full max-h-[38px] max-w-[38px] shrink-0 items-center justify-center rounded-full p-0 text-[#00628F] hover:opacity-80 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--Turquoise-500,#1C959B)] focus-visible:ring-offset-2 sm:max-h-none sm:max-w-none"
                            aria-label="<?php echo esc_attr__('Search map', 'matrix-starter'); ?>"
                            @click="window.srGroupMapApplyFilter && window.srGroupMapApplyFilter('<?php echo esc_js($section_id); ?>', mapQuery)"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                                <path d="M13 13L10.1 10.1M11.6667 6.33333C11.6667 9.27885 9.27885 11.6667 6.33333 11.6667C3.38781 11.6667 1 9.27885 1 6.33333C1 3.38781 3.38781 1 6.33333 1C9.27885 1 11.6667 3.38781 11.6667 6.33333Z" stroke="#00628F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Safe JSON payload (prevents broken HTML attributes) -->
                <script type="application/json" id="<?php echo esc_attr($section_id); ?>-groups">
<?php echo wp_json_encode($groups_payload); ?>
                </script>

                <?php if ($find_groups_button && is_array($find_groups_button) && !empty($find_groups_button['url']) && !empty($find_groups_button['title'])): ?>
                    <div class="flex absolute right-0 left-0 bottom-8 z-[7500] justify-center items-center mx-auto pointer-events-none">
                        <a 
                          href="<?php echo esc_url($find_groups_button['url']); ?>" 
                          class="group-map-find-btn pointer-events-auto inline-flex w-[170px] shrink-0 items-center justify-center gap-2 rounded-full border-0 bg-[var(--Blue-SR-400,#008BCC)] p-0 font-['Public_Sans'] text-white shadow-none outline-none ring-0 hover:border-0 hover:bg-[var(--Blue-SR-500,#00628F)] hover:text-white hover:shadow-none hover:outline-none hover:ring-0 focus:border-0 focus:shadow-none focus:outline-none focus:ring-0 focus-visible:border-0 focus-visible:shadow-none focus-visible:outline-none focus-visible:ring-0 transition-colors duration-200 btn sm:h-[42px] sm:p-4 sm:text-[14px] sm:font-bold sm:leading-5"
                          target="<?php echo esc_attr($find_groups_button['target'] ?? '_self'); ?>"
                          aria-label="<?php echo esc_attr($find_groups_button['title']); ?>"
                        >
                          <?php echo esc_html($find_groups_button['title']); ?>
                        </a>                            
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</section>

<script>
(function () {
  // Use jsDelivr (less blocked than unpkg on many sites)
  const LEAFLET_CSS = "https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css";
  const LEAFLET_JS  = "https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js";

  function loadLeafletIfNeeded(cb) {
    if (typeof window.L !== "undefined") return cb();

    // CSS
    if (!document.querySelector('link[data-leaflet-css]')) {
      const link = document.createElement("link");
      link.rel = "stylesheet";
      link.href = LEAFLET_CSS;
      link.crossOrigin = "";
      link.setAttribute("data-leaflet-css", "1");
      document.head.appendChild(link);
    }

    // JS
    const existing = document.querySelector('script[data-leaflet-js]');
    if (existing) {
      const t = setInterval(() => {
        if (typeof window.L !== "undefined") {
          clearInterval(t);
          cb();
        }
      }, 30);
      setTimeout(() => clearInterval(t), 8000);
      return;
    }

    const script = document.createElement("script");
    script.src = LEAFLET_JS;
    script.defer = true;
    script.crossOrigin = "";
    script.setAttribute("data-leaflet-js", "1");
    script.onload = cb;
    document.body.appendChild(script);
  }

  function groupMatchesQuery(g, rawQuery) {
    const q = String(rawQuery || "").trim().toLowerCase();
    if (!q) return true;
    const parts = [g.title, g.address, g.contact_info].filter(Boolean).map((s) => String(s).toLowerCase());
    return parts.some((hay) => hay.indexOf(q) !== -1);
  }

  /**
   * sectionId is the PHP $section_id (without -map suffix).
   */
  window.srGroupMapApplyFilter = function (sectionId, query) {
    const el = document.getElementById(sectionId + "-map");
    if (el && typeof el._applyGroupMapFilter === "function") {
      el._applyGroupMapFilter(query);
    }
  };

  function initGroupMap(container) {
    if (!container || container.dataset.initialized === "1") return;
    if (typeof window.L === "undefined") return;

    function setMapInteractions(mapInstance, enabled) {
      if (enabled) {
        if (mapInstance.scrollWheelZoom) mapInstance.scrollWheelZoom.enable();
        if (mapInstance.dragging) mapInstance.dragging.enable();
        if (mapInstance.touchZoom) mapInstance.touchZoom.enable();
        if (mapInstance.doubleClickZoom) mapInstance.doubleClickZoom.enable();
        if (mapInstance.boxZoom) mapInstance.boxZoom.enable();
        if (mapInstance.keyboard) mapInstance.keyboard.enable();
        if (mapInstance.tap) mapInstance.tap.enable();
        container.style.touchAction = "auto";
      } else {
        if (mapInstance.scrollWheelZoom) mapInstance.scrollWheelZoom.disable();
        if (mapInstance.dragging) mapInstance.dragging.disable();
        if (mapInstance.touchZoom) mapInstance.touchZoom.disable();
        if (mapInstance.doubleClickZoom) mapInstance.doubleClickZoom.disable();
        if (mapInstance.boxZoom) mapInstance.boxZoom.disable();
        if (mapInstance.keyboard) mapInstance.keyboard.disable();
        if (mapInstance.tap) mapInstance.tap.disable();
        container.style.touchAction = "pan-y";
      }
    }

    function addMobileMapOverlayLock(mapInstance) {
      if (container.querySelector("[data-mobile-map-overlay]")) return;

      let interactive = false;
      const overlay = document.createElement("button");
      overlay.type = "button";
      overlay.setAttribute("data-mobile-map-overlay", "1");
      overlay.setAttribute("aria-label", "Tap to interact with map");
      overlay.style.position = "absolute";
      overlay.style.inset = "0";
      overlay.style.zIndex = "6000";
      overlay.style.display = "flex";
      overlay.style.alignItems = "center";
      overlay.style.justifyContent = "center";
      overlay.style.padding = "0";
      overlay.style.background = "linear-gradient(to top, rgba(0,38,62,0.12), rgba(0,38,62,0.03))";
      overlay.style.color = "#00628F";
      overlay.style.fontSize = "12px";
      overlay.style.fontWeight = "700";
      overlay.style.lineHeight = "1";
      overlay.style.cursor = "pointer";
      overlay.style.textShadow = "0 1px 0 rgba(255,255,255,0.55)";
      overlay.textContent = "Tap map to interact";

      function lockMap() {
        interactive = false;
        setMapInteractions(mapInstance, false);
        overlay.style.display = "flex";
      }

      function unlockMap() {
        interactive = true;
        setMapInteractions(mapInstance, true);
        overlay.style.display = "none";
      }

      overlay.addEventListener("click", function (event) {
        event.preventDefault();
        event.stopPropagation();
        unlockMap();
      });

      document.addEventListener("pointerdown", function (event) {
        if (!interactive) return;
        if (!container.contains(event.target)) {
          lockMap();
        }
      }, true);

      container.appendChild(overlay);
      lockMap();
    }

    const provider = container.getAttribute("data-provider") || "osm";
    const token    = container.getAttribute("data-token") || "";
    const lat      = parseFloat(container.getAttribute("data-lat") || "53.35");
    const lng      = parseFloat(container.getAttribute("data-lng") || "-8");
    const zoom     = parseInt(container.getAttribute("data-zoom") || "6", 10);

    // Whole-island frame (ROI + NI); avoids auto-fit zooming into a Dublin-only cluster
    const IRELAND_BOUNDS = L.latLngBounds([51.22, -10.75], [55.44, -5.98]);

    // Safe JSON read
    let groups = [];
    const jsonEl = document.getElementById(container.id.replace(/-map$/, "-groups"));
    if (jsonEl) {
      try { groups = JSON.parse(jsonEl.textContent || "[]"); } catch (e) {}
    }

    const map = L.map(container, { scrollWheelZoom: true, zoomControl: false }).setView([lat, lng], zoom);

    // Toggle map search overlay while any marker popup is open.
    const sectionPrefix = container.id.replace(/-map$/, "");
    const searchWrap = document.getElementById(sectionPrefix + "-map-search-wrap");
    function setSearchVisible(visible) {
      if (!searchWrap) return;
      searchWrap.style.display = visible ? "" : "none";
    }

    let isTouchViewport = false;
    if (window.matchMedia) {
      isTouchViewport = window.matchMedia('(pointer: coarse)').matches || window.matchMedia('(max-width: 1024px)').matches;
    }

    if (isTouchViewport) {
      setMapInteractions(map, false);
      addMobileMapOverlayLock(map);
    } else {
      setMapInteractions(map, true);
    }

    // Keep map popups above floating search controls.
    const popupPane = map.getPanes && map.getPanes().popupPane ? map.getPanes().popupPane : null;
    if (popupPane) {
      popupPane.style.zIndex = "9000";
    }

    map.on("popupopen", function () {
      setSearchVisible(false);
    });
    map.on("popupclose", function () {
      setSearchVisible(true);
    });

    // Tile layer with Jawg fallback if token missing
    let tileUrl, tileOpts = {};
    const wantsJawg = (provider === "jawg-light" || provider === "jawg-dark");

    if (provider === "osm" || (wantsJawg && !token)) {
      tileUrl = "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png";
      tileOpts = { maxZoom: 19, attribution: "&copy; OpenStreetMap" };
    } else if (provider === "jawg-dark") {
      tileUrl = "https://tile.jawg.io/jawg-dark/{z}/{x}/{y}{r}.png?access-token=" + encodeURIComponent(token);
      tileOpts = { maxZoom: 22, attribution: '&copy; <a href="https://www.jawg.io" target="_blank" rel="noopener">Jawg</a>' };
    } else {
      tileUrl = "https://tile.jawg.io/jawg-light/{z}/{x}/{y}{r}.png?access-token=" + encodeURIComponent(token);
      tileOpts = { maxZoom: 22, attribution: '&copy; <a href="https://www.jawg.io" target="_blank" rel="noopener">Jawg</a>' };
    }

    L.tileLayer(tileUrl, tileOpts).addTo(map);

    const customIcon = L.divIcon({
      className: 'group-map-marker',
      html: '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="27" viewBox="0 0 22 27" fill="none"><path d="M10.6667 13.3333C11.4 13.3333 12.0278 13.0722 12.55 12.55C13.0722 12.0278 13.3333 11.4 13.3333 10.6667C13.3333 9.93333 13.0722 9.30556 12.55 8.78333C12.0278 8.26111 11.4 8 10.6667 8C9.93333 8 9.30556 8.26111 8.78333 8.78333C8.26111 9.30556 8 9.93333 8 10.6667C8 11.4 8.26111 12.0278 8.78333 12.55C9.30556 13.0722 9.93333 13.3333 10.6667 13.3333ZM10.6667 26.6667C7.08889 23.6222 4.41667 20.7944 2.65 18.1833C0.883333 15.5722 0 13.1556 0 10.9333C0 7.6 1.07222 4.94444 3.21667 2.96667C5.36111 0.988889 7.84444 0 10.6667 0C13.4889 0 15.9722 0.988889 18.1167 2.96667C20.2611 4.94444 21.3333 7.6 21.3333 10.9333C21.3333 13.1556 20.45 15.5722 18.6833 18.1833C16.9167 20.7944 14.2444 23.6222 10.6667 26.6667Z" fill="#00628F"/></svg>',
      iconSize: [22, 27],
      iconAnchor: [11, 27]
    });

    function applyGroupMarkerA11y() {
      container.querySelectorAll('.group-map-marker.leaflet-interactive').forEach((iconEl) => {
        if (!iconEl.getAttribute('aria-label')) {
          iconEl.setAttribute('aria-label', 'Running group marker');
        }
        if (!iconEl.getAttribute('title')) {
          iconEl.setAttribute('title', 'Running group marker');
        }
      });
    }

    const markerEntries = [];
    const bounds = [];
    groups.forEach((g) => {
      if (!g) return;

      const glat = Number(g.lat);
      const glng = Number(g.lng);

      if (!Number.isFinite(glat) || !Number.isFinite(glng)) return;

      const markerTitle = String(g.title || 'Running group location');
      const marker = L.marker([glat, glng], {
        icon: customIcon,
        keyboard: false,
        title: markerTitle
      }).addTo(map);
      marker.on('add', () => {
        if (marker && marker._icon) {
          marker._icon.setAttribute('aria-label', markerTitle);
          marker._icon.setAttribute('title', markerTitle);
        }
        applyGroupMarkerA11y();
      });
      if (marker && marker._icon) {
        marker._icon.setAttribute('aria-label', markerTitle);
        marker._icon.setAttribute('title', markerTitle);
      }
      bounds.push([glat, glng]);
      markerEntries.push({ marker: marker, group: g });

      let popup = `<div style="max-width:240px;">`;
      popup += `<div style="font-weight:700;margin-bottom:4px;">${escapeHtml(g.title || "")}</div>`;
      if (g.address)      popup += `<div style="font-size:12px;margin-bottom:4px;"><strong>Address:</strong> ${escapeHtml(g.address)}</div>`;
      if (g.meeting_time) popup += `<div style="font-size:12px;margin-bottom:4px;"><strong>Meeting Time:</strong><div style="margin-top:2px;">${escapeHtml(g.meeting_time)}</div></div>`;
      if (g.contact_info) popup += `<div style="font-size:12px;margin-bottom:6px;"><strong>Contact:</strong> ${escapeHtml(g.contact_info)}</div>`;
      if (g.url)          popup += `<a href="${g.url}" style="font-size:12px;text-decoration:underline;">View Details</a>`;
      popup += `</div>`;

      marker.bindPopup(popup);
    });

    function applyGroupMapFilter(rawQuery) {
      const q = String(rawQuery || "").trim();
      const visibleBounds = [];
      markerEntries.forEach(({ marker, group }) => {
        const show = groupMatchesQuery(group, q);
        if (show) {
          if (!map.hasLayer(marker)) marker.addTo(map);
          const glat = Number(group.lat);
          const glng = Number(group.lng);
          if (Number.isFinite(glat) && Number.isFinite(glng)) visibleBounds.push([glat, glng]);
        } else if (map.hasLayer(marker)) {
          map.removeLayer(marker);
        }
      });
      if (visibleBounds.length === 0) {
        map.setView([lat, lng], zoom);
      } else if (visibleBounds.length === 1) {
        map.setView(visibleBounds[0], Math.max(zoom, 12));
      } else {
        map.fitBounds(visibleBounds, { padding: [30, 30] });
      }
    }

    container._applyGroupMapFilter = applyGroupMapFilter;
    map.on('zoomend moveend layeradd', applyGroupMarkerA11y);
    setTimeout(applyGroupMarkerA11y, 50);

    // Default view: all of Ireland (marker auto-fit was zooming tight on Dublin clusters)
    map.fitBounds(IRELAND_BOUNDS, { padding: [28, 28] });

    container.dataset.initialized = "1";

    // Sync Alpine search if user typed before map finished init
    const searchInput = document.getElementById(sectionPrefix + "-map-search");
    if (searchInput && searchInput.value) {
      applyGroupMapFilter(searchInput.value);
    }

    // Fix rendering when inside layout containers / after fonts load
    setTimeout(() => map.invalidateSize(), 50);
    setTimeout(() => map.invalidateSize(), 250);
  }

  function escapeHtml(str) {
    return String(str)
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");
  }

  document.addEventListener("DOMContentLoaded", function () {
    const el = document.querySelector("#<?php echo esc_js($section_id); ?>-map[data-leaflet]");
    if (!el) return;
    loadLeafletIfNeeded(() => initGroupMap(el));
  });
})();
</script>