<?php
/**
 * Our Shops page map (Leaflet).
 */

function matrix_rd_locations_enqueue_assets(): void {
    if (! matrix_rd_is_locations_page() || is_admin()) {
        return;
    }
    wp_enqueue_style('leaflet');
    wp_enqueue_script('leaflet');
}
add_action('wp_enqueue_scripts', 'matrix_rd_locations_enqueue_assets', 28);

function matrix_rd_locations_enqueue_card_styles(): void {
    if (! matrix_rd_is_locations_page() || is_admin()) {
        return;
    }

    $locations_css = get_template_directory() . '/assets/css/rolling-donut-locations.css';
    if (is_readable($locations_css)) {
        wp_enqueue_style(
            'matrix-rd-locations',
            get_template_directory_uri() . '/assets/css/rolling-donut-locations.css',
            ['matrix-rd-legacy', 'matrix-starter'],
            (string) filemtime($locations_css)
        );
    }
}
add_action('wp_enqueue_scripts', 'matrix_rd_locations_enqueue_card_styles', 35);

function matrix_rd_is_locations_page(): bool {
    return is_page('our-shops');
}

/**
 * Parse an ACF lat/lng value into a finite float, or null if empty/invalid.
 */
function matrix_rd_location_coord($value): ?float {
    if ($value === null || $value === false || $value === '') {
        return null;
    }
    if (! is_numeric($value)) {
        return null;
    }
    $coord = (float) $value;
    return is_finite($coord) ? $coord : null;
}

/**
 * Theme-bundled pin, with the migrated uploads path as a fallback.
 */
function matrix_rd_locations_pin_url(): string {
    $theme_rel  = '/assets/images/locations/map-pin.png';
    $theme_path = get_template_directory() . $theme_rel;
    if (is_readable($theme_path)) {
        return get_template_directory_uri() . $theme_rel;
    }

    $uploads = wp_upload_dir();
    return trailingslashit((string) ($uploads['baseurl'] ?? content_url('uploads'))) . '2023/09/13-1.png';
}

function matrix_rd_locations_map_script(): void {
    if (! matrix_rd_is_locations_page()) {
        return;
    }

    $query     = new WP_Query(['post_type' => 'location', 'posts_per_page' => -1]);
    $locations = [];

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $lat = matrix_rd_location_coord(get_field('latitude_coordinates'));
            $lng = matrix_rd_location_coord(get_field('longitude_coordinates'));
            $locations[] = [
                'address'     => (string) get_field('address'),
                'coordinates' => ($lat !== null && $lng !== null) ? [$lat, $lng] : null,
            ];
        }
        wp_reset_postdata();
    }

    $pin_url = matrix_rd_locations_pin_url();
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
      var locations = <?php echo wp_json_encode($locations); ?>;
      var pinUrl = <?php echo wp_json_encode($pin_url); ?>;

      function initializeMap() {
        var southWest = L.latLng(49.0, -11.0);
        var northEast = L.latLng(59.0, 2.0);
        var bounds = L.latLngBounds(southWest, northEast);
        var map = L.map('map', {
          scrollWheelZoom: false,
          maxBounds: bounds,
          maxBoundsViscosity: 1.0,
          minZoom: 5,
          zoomControl: false
        }).setView([53.34543, -6.2591], 15);

        L.control.zoom({ position: 'bottomleft' }).addTo(map);
        map.on('click', function () {
          if (map.scrollWheelZoom.enabled()) {
            map.scrollWheelZoom.disable();
          } else {
            map.scrollWheelZoom.enable();
          }
        });

        L.tileLayer('https://{s}.tile.jawg.io/jawg-streets/{z}/{x}/{y}.png?access-token=qmJcZHS9yKXscVXS3eJS7kC3pJs7ZduqFMUzNQHAMKG9Dk2HBz8ksbfeLxe8CX7W', {
          attribution: 'Map data © OpenStreetMap contributors, Jawg Maps',
          maxZoom: 22
        }).addTo(map);

        var customPin = L.icon({
          iconUrl: pinUrl,
          iconSize: [76, 76],
          iconAnchor: [38, 76],
          popupAnchor: [0, -70],
          className: 'rd-shop-map-marker'
        });

        function hasCoords(coords) {
          return Array.isArray(coords)
            && coords.length >= 2
            && Number.isFinite(Number(coords[0]))
            && Number.isFinite(Number(coords[1]));
        }

        function addMarker(latlng, address) {
          L.marker(latlng, { icon: customPin }).bindPopup(address).addTo(map);
        }

        function geocodeAndAddMarker(address, fallbackCoordinates) {
          if (hasCoords(fallbackCoordinates)) {
            addMarker([Number(fallbackCoordinates[0]), Number(fallbackCoordinates[1])], address);
            return;
          }

          fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=ie&q=' + encodeURIComponent(address))
            .then(function (r) { return r.json(); })
            .then(function (data) {
              if (data.length > 0) {
                addMarker([data[0].lat, data[0].lon], address);
              }
            })
            .catch(function () {});
        }

        locations.forEach(function (loc) {
          if (loc.address) {
            geocodeAndAddMarker(loc.address, loc.coordinates);
          }
        });
      }

      function waitForLeaflet() {
        if (typeof L !== 'undefined' && typeof L.map === 'function') {
          initializeMap();
        } else {
          setTimeout(waitForLeaflet, 100);
        }
      }

      waitForLeaflet();
    });
    </script>
    <?php
}
add_action('wp_footer', 'matrix_rd_locations_map_script', 50);
