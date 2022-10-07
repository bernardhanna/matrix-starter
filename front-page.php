<?php
/**
 * The template for displaying front page
 *
 * Contains the closing of the #content div and all content after.
 * Initial styles for front page template.
 *
 * @Date:   2019-10-15 12:30:02
 * @Last Modified by:  Bernard Hanna
 * @Last Modified time: 2022-10-07 12:52:56
 *
 * @package matrix-starter
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 */

namespace Matrix_Starter;

// NB! Our dev version uses newtheme.sh build script which cleans ups things including this next line
$thumbnail = wp_get_attachment_url( get_post_thumbnail_id() ) ?: THEME_SETTINGS['default_featured_image'];

get_header(); ?>

<main class="site-main">
  <?php
    the_content();
    matrix_edit_link();
  ?>
</main>

<?php get_footer();
