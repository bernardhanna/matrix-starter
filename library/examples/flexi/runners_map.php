<?php
/**
 * Frontend: Runners Map
 */

if (!defined('ABSPATH')) {
    exit;
}

// -------------------------------------------------
// Unique section ID
// -------------------------------------------------
$section_id = 'runners-map-' . wp_generate_uuid4();

// -------------------------------------------------
// Content
// -------------------------------------------------
$sr_heading_text = get_sub_field('sr_heading_text') ?: 'Community Connection and Friendship Building';
$sr_heading_tag  = get_sub_field('sr_heading_tag') ?: 'h1';
$description     = get_sub_field('description') ?: 'Every week, people from different backgrounds come together in blue to move, connect and build friendships. Show up, get in touch with the local organiser to say hello, or send us an email to find out more via our <a href="https://sanctuaryrunners.s1.matrix-test.com/contact/">contact us form</a>. You\'ll be met with a warm welcome.';

// -------------------------------------------------
// Images
// -------------------------------------------------
$background_image = get_sub_field('background_image');
$overlay_image    = get_sub_field('overlay_image');

$background_image_url   = '';
$background_image_alt   = 'Community background image';
$background_image_title = 'Community background image';

if (is_array($background_image) && !empty($background_image['ID'])) {
    $background_image_url   = $background_image['url'] ?? '';
    $background_image_alt   = wp_get_attachment_image_alt($background_image['ID']) ?: 'Community background image';
    $background_image_title = get_the_title($background_image['ID']) ?: 'Community background image';
}

$overlay_image_url   = '';
$overlay_image_alt   = 'Community background overlay';
$overlay_image_title = 'Community background overlay';

if (is_array($overlay_image) && !empty($overlay_image['ID'])) {
    $overlay_image_url   = $overlay_image['url'] ?? '';
    $overlay_image_alt   = wp_get_attachment_image_alt($overlay_image['ID']) ?: 'Community background overlay';
    $overlay_image_title = get_the_title($overlay_image['ID']) ?: 'Community background overlay';
}

// -------------------------------------------------
// Map settings
// -------------------------------------------------
$map_aria_label = get_sub_field('map_aria_label') ?: 'Interactive map showing runners groups';
$map_center_lat = get_sub_field('map_center_lat');
$map_center_lng = get_sub_field('map_center_lng');
$map_zoom       = get_sub_field('map_zoom');
$tile_provider  = get_sub_field('tile_provider') ?: 'osm';
$tile_api_key   = get_sub_field('tile_api_key') ?: '';

$map_center_lat = $map_center_lat !== null && $map_center_lat !== '' ? $map_center_lat : 53.349805;
$map_center_lng = $map_center_lng !== null && $map_center_lng !== '' ? $map_center_lng : -6.26031;
$map_zoom       = $map_zoom !== null && $map_zoom !== '' ? $map_zoom : 6;

// -------------------------------------------------
// Layout
// -------------------------------------------------
$padding_classes = [];

if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
        the_row();
        $screen_size    = get_sub_field('screen_size');
        $padding_top    = get_sub_field('padding_top');
        $padding_bottom = get_sub_field('padding_bottom');

        if ($screen_size !== '' && $screen_size !== null && $padding_top !== null && $padding_top !== '') {
            $padding_classes[] = "{$screen_size}:pt-[{$padding_top}rem]";
        }

        if ($screen_size !== '' && $screen_size !== null && $padding_bottom !== null && $padding_bottom !== '') {
            $padding_classes[] = "{$screen_size}:pb-[{$padding_bottom}rem]";
        }
    }
}

// -------------------------------------------------
// Map markers
// -------------------------------------------------
$markers_payload = [];

$running_group_ids = get_posts([
    'post_type'      => 'running_group',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'fields'         => 'ids',
]);

foreach ($running_group_ids as $group_id) {
    $lat_raw = get_post_meta($group_id, 'latitude', true);
    $lng_raw = get_post_meta($group_id, 'longitude', true);

    // Fallbacks for alternate field names and ACF map fields.
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

    $marker_item = [
        'title'       => (string) get_the_title($group_id),
        'description' => (string) wp_strip_all_tags((string) get_field('address', $group_id)),
        'lat'         => (float) $lat,
        'lng'         => (float) $lng,
    ];

    if ($show_popup_link) {
        $marker_item['link'] = [
            'url'    => (string) get_permalink($group_id),
            'title'  => 'View group',
            'target' => '_self',
        ];
    }

    $markers_payload[] = $marker_item;
}
?>

<section id="<?php echo esc_attr($section_id); ?>" class="flex overflow-hidden relative">
    <div class="flex flex-col items-center w-full mx-auto max-w-[896px] pt-5 pb-5 lg:py-24 max-lg:px-5 <?php echo esc_attr(implode(' ', $padding_classes)); ?>">

        <section class="flex overflow-hidden relative flex-col w-full rounded-none min-h-[641px] max-md:max-w-full" aria-labelledby="<?php echo esc_attr($section_id); ?>-heading">
            <?php if (!empty($background_image_url)) : ?>
                <img
                    src="<?php echo esc_url($background_image_url); ?>"
                    alt="<?php echo esc_attr($background_image_alt); ?>"
                    title="<?php echo esc_attr($background_image_title); ?>"
                    class="object-cover absolute inset-0 size-full"
                />
            <?php endif; ?>

            <div class="flex relative flex-col items-center w-full min-h-[641px] max-md:pt-8 max-md:max-w-full">
                <?php if (!empty($overlay_image_url)) : ?>
                    <img
                        src="<?php echo esc_url($overlay_image_url); ?>"
                        alt="<?php echo esc_attr($overlay_image_alt); ?>"
                        title="<?php echo esc_attr($overlay_image_title); ?>"
                        class="object-cover absolute inset-0 size-full"
                    />
                <?php endif; ?>

                <div
                    id="<?php echo esc_attr($section_id); ?>-map"
                    class="w-full h-full  max-h-[641.455px] min-h-[448px] relative z-10"
                    data-leaflet
                    data-provider="<?php echo esc_attr($tile_provider); ?>"
                    data-token="<?php echo esc_attr($tile_api_key); ?>"
                    data-lat="<?php echo esc_attr($map_center_lat); ?>"
                    data-lng="<?php echo esc_attr($map_center_lng); ?>"
                    data-zoom="<?php echo esc_attr($map_zoom); ?>"
                    role="application"
                    aria-label="<?php echo esc_attr($map_aria_label); ?>"
                ></div>

                <script type="application/json" id="<?php echo esc_attr($section_id); ?>-markers">
<?php echo wp_json_encode($markers_payload); ?>
                </script>
            </div>
        </section>

        <article class="mt-6 max-md:max-w-full">
            <<?php echo esc_attr($sr_heading_tag); ?> id="<?php echo esc_attr($section_id); ?>-heading" class="sr-only">
                <?php echo esc_html($sr_heading_text); ?>
            </<?php echo esc_attr($sr_heading_tag); ?>>

            <div class="text-2xl font-light leading-8 text-sky-950 max-md:max-w-full wp_editor">
                <?php echo wp_kses_post($description); ?>
            </div>
        </article>

    </div>
</section>

<script>
(function () {
    var LEAFLET_CSS = "https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css";
    var LEAFLET_JS = "https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js";

    function loadLeafletIfNeeded(callback) {
        if (typeof window.L !== "undefined") {
            callback();
            return;
        }

        if (!document.querySelector('link[data-leaflet-css]')) {
            var link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = LEAFLET_CSS;
            link.crossOrigin = '';
            link.setAttribute('data-leaflet-css', '1');
            document.head.appendChild(link);
        }

        var existingScript = document.querySelector('script[data-leaflet-js]');
        if (existingScript) {
            var checkReady = setInterval(function () {
                if (typeof window.L !== "undefined") {
                    clearInterval(checkReady);
                    callback();
                }
            }, 30);

            setTimeout(function () {
                clearInterval(checkReady);
            }, 8000);

            return;
        }

        var script = document.createElement('script');
        script.src = LEAFLET_JS;
        script.defer = true;
        script.crossOrigin = '';
        script.setAttribute('data-leaflet-js', '1');
        script.onload = callback;
        document.body.appendChild(script);
    }

    function escapeHtml(str) {
        return String(str)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function initMap(container) {
        if (!container || container.dataset.initialized === "1" || typeof window.L === "undefined") {
            return;
        }

        function setMapInteractions(mapInstance, enabled) {
            if (enabled) {
                if (mapInstance.scrollWheelZoom) mapInstance.scrollWheelZoom.enable();
                if (mapInstance.dragging) mapInstance.dragging.enable();
                if (mapInstance.touchZoom) mapInstance.touchZoom.enable();
                if (mapInstance.doubleClickZoom) mapInstance.doubleClickZoom.enable();
                if (mapInstance.boxZoom) mapInstance.boxZoom.enable();
                if (mapInstance.keyboard) mapInstance.keyboard.enable();
                if (mapInstance.tap) mapInstance.tap.enable();
                container.style.touchAction = 'auto';
            } else {
                if (mapInstance.scrollWheelZoom) mapInstance.scrollWheelZoom.disable();
                if (mapInstance.dragging) mapInstance.dragging.disable();
                if (mapInstance.touchZoom) mapInstance.touchZoom.disable();
                if (mapInstance.doubleClickZoom) mapInstance.doubleClickZoom.disable();
                if (mapInstance.boxZoom) mapInstance.boxZoom.disable();
                if (mapInstance.keyboard) mapInstance.keyboard.disable();
                if (mapInstance.tap) mapInstance.tap.disable();
                container.style.touchAction = 'pan-y';
            }
        }

        function addMobileMapOverlayLock(mapInstance) {
            if (container.querySelector('[data-mobile-map-overlay]')) {
                return;
            }

            var interactive = false;
            var overlay = document.createElement('button');
            overlay.type = 'button';
            overlay.setAttribute('data-mobile-map-overlay', '1');
            overlay.setAttribute('aria-label', 'Tap to interact with map');
            overlay.style.position = 'absolute';
            overlay.style.inset = '0';
            overlay.style.zIndex = '6000';
            overlay.style.display = 'flex';
            overlay.style.alignItems = 'center';
            overlay.style.justifyContent = 'center';
            overlay.style.padding = '0';
            overlay.style.background = 'linear-gradient(to top, rgba(0,38,62,0.12), rgba(0,38,62,0.03))';
            overlay.style.color = '#00628F';
            overlay.style.fontSize = '12px';
            overlay.style.fontWeight = '700';
            overlay.style.lineHeight = '1';
            overlay.style.cursor = 'pointer';
            overlay.style.textShadow = '0 1px 0 rgba(255,255,255,0.55)';
            overlay.textContent = 'Tap map to interact';

            function lockMap() {
                interactive = false;
                setMapInteractions(mapInstance, false);
                overlay.style.display = 'flex';
            }

            function unlockMap() {
                interactive = true;
                setMapInteractions(mapInstance, true);
                overlay.style.display = 'none';
            }

            overlay.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                unlockMap();
            });

            document.addEventListener('pointerdown', function (event) {
                if (!interactive) {
                    return;
                }

                if (!container.contains(event.target)) {
                    lockMap();
                }
            }, true);

            container.appendChild(overlay);
            lockMap();
        }

        var provider = container.getAttribute('data-provider') || 'osm';
        var token = container.getAttribute('data-token') || '';
        var lat = parseFloat(container.getAttribute('data-lat') || '53.349805');
        var lng = parseFloat(container.getAttribute('data-lng') || '-6.26031');
        var zoom = parseInt(container.getAttribute('data-zoom') || '6', 10);

        var map = L.map(container, {
            scrollWheelZoom: true,
            zoomControl: false
        }).setView([lat, lng], zoom);

        var isTouchViewport = false;
        if (window.matchMedia) {
            isTouchViewport = window.matchMedia('(pointer: coarse)').matches || window.matchMedia('(max-width: 1024px)').matches;
        }

        if (isTouchViewport) {
            setMapInteractions(map, false);
            addMobileMapOverlayLock(map);
        } else {
            setMapInteractions(map, true);
        }

        // Keep popups above any floating controls layered over the map.
        var popupPane = map.getPanes && map.getPanes().popupPane ? map.getPanes().popupPane : null;
        if (popupPane) {
            popupPane.style.zIndex = '9000';
        }

        var tileUrl = '';
        var tileOptions = {};

        if (provider === 'jawg-dark' && token) {
            tileUrl = 'https://tile.jawg.io/jawg-dark/{z}/{x}/{y}{r}.png?access-token=' + encodeURIComponent(token);
            tileOptions = {
                maxZoom: 22,
                attribution: '&copy; Jawg &copy; OpenStreetMap contributors'
            };
        } else if (provider === 'jawg-light' && token) {
            tileUrl = 'https://tile.jawg.io/jawg-light/{z}/{x}/{y}{r}.png?access-token=' + encodeURIComponent(token);
            tileOptions = {
                maxZoom: 22,
                attribution: '&copy; Jawg &copy; OpenStreetMap contributors'
            };
        } else {
            tileUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
            tileOptions = {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            };
        }

        L.tileLayer(tileUrl, tileOptions).addTo(map);

        var customIcon = L.divIcon({
            className: 'runners-map-marker',
            html: '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="27" viewBox="0 0 22 27" fill="none"><path d="M10.6667 13.3333C11.4 13.3333 12.0278 13.0722 12.55 12.55C13.0722 12.0278 13.3333 11.4 13.3333 10.6667C13.3333 9.93333 13.0722 9.30556 12.55 8.78333C12.0278 8.26111 11.4 8 10.6667 8C9.93333 8 9.30556 8.26111 8.78333 8.78333C8.26111 9.30556 8 9.93333 8 10.6667C8 11.4 8.26111 12.0278 8.78333 12.55C9.30556 13.0722 9.93333 13.3333 10.6667 13.3333ZM10.6667 26.6667C7.08889 23.6222 4.41667 20.7944 2.65 18.1833C0.883333 15.5722 0 13.1556 0 10.9333C0 7.6 1.07222 4.94444 3.21667 2.96667C5.36111 0.988889 7.84444 0 10.6667 0C13.4889 0 15.9722 0.988889 18.1167 2.96667C20.2611 4.94444 21.3333 7.6 21.3333 10.9333C21.3333 13.1556 20.45 15.5722 18.6833 18.1833C16.9167 20.7944 14.2444 23.6222 10.6667 26.6667Z" fill="#00628F"/></svg>',
            iconSize: [22, 27],
            iconAnchor: [11, 27]
        });

        var markers = [];
        var payloadEl = document.getElementById(container.id.replace(/-map$/, '-markers'));

        if (payloadEl) {
            try {
                markers = JSON.parse(payloadEl.textContent || '[]');
            } catch (error) {
                markers = [];
            }
        }

        var bounds = [];

        markers.forEach(function (markerItem) {
            if (!markerItem) {
                return;
            }

            var markerLat = Number(markerItem.lat);
            var markerLng = Number(markerItem.lng);

            if (!Number.isFinite(markerLat) || !Number.isFinite(markerLng)) {
                return;
            }

            var markerTitle = markerItem.title ? String(markerItem.title) : 'Running group location';
            var marker = L.marker([markerLat, markerLng], {
                icon: customIcon,
                title: markerTitle,
                alt: markerTitle,
                keyboard: false
            }).addTo(map);
            marker.on('add', function () {
                if (marker && marker._icon) {
                    marker._icon.setAttribute('aria-label', markerTitle);
                    marker._icon.setAttribute('title', markerTitle);
                }
            });
            if (marker && marker._icon) {
                marker._icon.setAttribute('aria-label', markerTitle);
                marker._icon.setAttribute('title', markerTitle);
            }
            bounds.push([markerLat, markerLng]);

            var popup = '<div style="max-width:240px;">';
            popup += '<div style="font-weight:700;margin-bottom:4px;">' + escapeHtml(markerItem.title || '') + '</div>';

            if (markerItem.description) {
                popup += '<div style="font-size:12px;margin-bottom:6px;">' + escapeHtml(markerItem.description) + '</div>';
            }

            if (markerItem.link && markerItem.link.url && markerItem.link.title) {
                popup += '<a href="' + escapeHtml(markerItem.link.url) + '" target="' + escapeHtml(markerItem.link.target || '_self') + '" style="font-size:12px;text-decoration:underline;">' + escapeHtml(markerItem.link.title) + '</a>';
            }

            popup += '</div>';

            marker.bindPopup(popup);
        });

        if (bounds.length > 1) {
            map.fitBounds(bounds, { padding: [30, 30] });
        }

        if (bounds.length === 1) {
            map.setView(bounds[0], Math.max(zoom, 12));
        }

        container.dataset.initialized = '1';

        setTimeout(function () {
            map.invalidateSize();
        }, 50);

        setTimeout(function () {
            map.invalidateSize();
        }, 250);
    }

    document.addEventListener('DOMContentLoaded', function () {
        var mapEl = document.getElementById('<?php echo esc_js($section_id); ?>-map');
        if (!mapEl) {
            return;
        }

        loadLeafletIfNeeded(function () {
            initMap(mapEl);
        });
    });
})();
</script>