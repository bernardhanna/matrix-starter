<?php
// File: inc/enqueue-scripts.php

/**
 * Enqueue Scripts and Styles for the Theme
 */

function matrix_starter_enqueue_scripts()
{
  // Get theme version for cache busting
  $theme_version = get_option('theme_css_version', '1.0');

  // Live Reoload for Local Development
  if (defined('WP_ENV') && WP_ENV === 'development') {
    wp_enqueue_script('matrix-starter', 'http://localhost:3000/wp-content/themes/matrix-starter/dist/app.js', array(), '1.0.0', true);
    wp_enqueue_style('matrix-starter', 'http://localhost:3000/wp-content/themes/matrix-starter/dist/app.css');
  } else {
    wp_enqueue_script('matrix-starter', get_template_directory_uri() . '/dist/app.js', array(), '1.0.0', true);
    wp_enqueue_style('matrix-starter', get_template_directory_uri() . '/dist/app.css');
  }

  // Get enabled scripts from ACF options
  $enabled_scripts = get_field('enabled_scripts', 'option');

  // First: Enqueue Alpine.js Intersect Plugin
  wp_enqueue_script(
    'alpine-intersect',
    'https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js',
    array(),
    null,
    true
  );

  // Second: Enqueue Alpine.js core with intersect as dependency
  wp_enqueue_script(
    'alpine',
    'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js',
    array('alpine-intersect'),
    null,
    true
  );

  // Third: Initialize Alpine Intersect plugin
  wp_add_inline_script('alpine', "
    document.addEventListener('alpine:init', () => {
        if (typeof Alpine !== 'undefined' && typeof Alpine.plugin !== 'undefined') {
            Alpine.plugin(window.AlpineIntersect);
        }
    });
");
  // ─────────────────────────────────────────────────────────────────────
  // CONDITIONAL ENQUEUING BASED ON THEME OPTIONS
  // ─────────────────────────────────────────────────────────────────────

  if (is_array($enabled_scripts)) {
    // Conditionally enqueue Font Awesome
    if (in_array('font_awesome', $enabled_scripts)) {
      wp_enqueue_style(
        'font-awesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
        [],
        null
      );
    }

    // Conditionally enqueue Hamburgers CSS
    if (in_array('hamburger_css', $enabled_scripts)) {
      wp_enqueue_style(
        'hamburgers-css',
        'https://cdnjs.cloudflare.com/ajax/libs/hamburgers/1.2.1/hamburgers.min.css',
        [],
        '1.2.1'
      );
    }

    // Conditionally enqueue Flowbite
    if (in_array('flowbite', $enabled_scripts)) {
      wp_enqueue_script(
        'flowbite',
        'https://unpkg.com/flowbite@1.6.5/dist/flowbite.min.js',
        ['alpine'], // Ensure Alpine is loaded first
        '1.6.5',
        true
      );
    }

    // Conditionally enqueue Slick Carousel
    if (in_array('slick', $enabled_scripts)) {
      // Enqueue Slick CSS
      wp_enqueue_style(
        'slick-css',
        'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css',
        [],
        '1.8.1'
      );

      // Enqueue Slick JS
      wp_enqueue_script(
        'slick-js',
        'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js',
        ['jquery'], // Ensure jQuery is loaded first
        '1.8.1',
        true
      );
    }
    if (in_array('headroom', $enabled_scripts)) {
      wp_enqueue_script(
        'headroom',
        'https://cdnjs.cloudflare.com/ajax/libs/headroom/0.12.0/headroom.min.js',
        [],
        '0.12.0',
        true
      );

      wp_add_inline_script('headroom', "
        document.addEventListener('DOMContentLoaded', function() {
            var header = document.querySelector('#site-nav');
            if (header) {
                // Always use fixed positioning with transition classes to animate transform.
                header.classList.add(
                    'fixed', 
                    'top-0', 
                    'left-0', 
                    'w-full', 
                    'z-50', 
                    'transition-transform', 
                    'duration-300', 
                    'ease-in-out'
                );
                
                // Set the initial state to visible.
                header.classList.add('translate-y-0');
                
                var headroom = new Headroom(header, {
                    tolerance: 5,
                    offset: 100
                });
                
                // When scrolling upward (or when the header should appear), ensure it's visible.
                headroom.onPin = function() {
                    header.classList.remove('-translate-y-full');
                    header.classList.add('translate-y-0');
                };

                // When scrolling downward, slide the header up out of view.
                headroom.onUnpin = function() {
                    header.classList.remove('translate-y-0');
                    header.classList.add('-translate-y-full');
                };

                headroom.init();
            }
        });
    ");
    }

  }

  // Enqueue main stylesheet with version control (enqueued last)
  wp_enqueue_style(
    'matrix-starter-style',
    get_template_directory_uri() . '/dist/styles.css',
    [],
    $theme_version
  );

  // Enqueue dynamic Tailwind styles with version control (enqueued last)
  wp_enqueue_style(
    'matrix-starter-dynamic-style',
    get_template_directory_uri() . '/dist/app.css',
    [],
    $theme_version
  );
}
add_action('wp_enqueue_scripts', 'matrix_starter_enqueue_scripts');
