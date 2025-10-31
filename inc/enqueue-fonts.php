<?php
// File: inc/enqueue-fonts.php

/**
 * Enqueue Google Fonts (Lato and Poppins)
 */
function matrix_starter_enqueue_fonts() {
  wp_enqueue_style(
    'google-fonts-lato',
    'https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap',
    [],
    null
  );
  
  wp_enqueue_style(
    'google-fonts-poppins',
    'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap',
    [],
    null
  );
}
add_action('wp_enqueue_scripts', 'matrix_starter_enqueue_fonts');