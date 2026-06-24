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

function matrix_rd_is_locations_page(): bool {
    return is_page('our-shops');
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
            $locations[] = [
                'address'     => (string) get_field('address'),
                'coordinates' => [
                    get_field('latitude_coordinates'),
                    get_field('longitude_coordinates'),
                ],
            ];
        }
        wp_reset_postdata();
    }

    $pin_url = content_url('uploads/2023/09/13-1.png');
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

        var customPin = L.icon({ iconUrl: pinUrl, iconSize: [75, 75] });

        function geocodeAndAddMarker(address, fallbackCoordinates) {
          fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(address))
            .then(function (r) { return r.json(); })
            .then(function (data) {
              if (data.length > 0) {
                L.marker([data[0].lat, data[0].lon], { icon: customPin }).bindPopup(address).addTo(map);
              } else if (fallbackCoordinates && fallbackCoordinates[0] && fallbackCoordinates[1]) {
                L.marker(fallbackCoordinates, { icon: customPin }).bindPopup(address).addTo(map);
              }
            })
            .catch(function () {
              if (fallbackCoordinates && fallbackCoordinates[0] && fallbackCoordinates[1]) {
                L.marker(fallbackCoordinates, { icon: customPin }).bindPopup(address).addTo(map);
              }
            });
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
