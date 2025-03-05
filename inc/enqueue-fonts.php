<?php
// File: inc/enqueue-fonts.php

/**
 * Enqueue Fonts Based on Theme Options
 */
function matrix_starter_enqueue_fonts()
{
  // Get enabled fonts from ACF options
  $enabled_fonts = get_field('enabled_fonts', 'option');

  if (is_array($enabled_fonts)) {
    // Conditionally enqueue Poppins
    if (in_array('poppins', $enabled_fonts)) {
      wp_enqueue_style(
        'google-fonts-poppins',
        'https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap',
        [],
        null
      );
    }

    // Conditionally enqueue Roboto
    if (in_array('roboto', $enabled_fonts)) {
      wp_enqueue_style(
        'google-fonts-roboto',
        'https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap',
        [],
        null
      );
    }

    // Conditionally enqueue Lato
    if (in_array('lato', $enabled_fonts)) {
      wp_enqueue_style(
        'google-fonts-lato',
        'https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap',
        [],
        null
      );
    }

    if (in_array('kanit', $enabled_fonts)) {
      wp_enqueue_style(
        'google-fonts-kanit',
        'https://fonts.googleapis.com/css2?family=Josefin+Sans&family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap',
        [],
        null
      );
    }

    // Add more fonts here as needed
  }
}
add_action('wp_enqueue_scripts', 'matrix_starter_enqueue_fonts');
