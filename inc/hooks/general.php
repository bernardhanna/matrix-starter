<?php
/**
 * General hooks.
 *
 * @Author: Bernard Hanna
 * @Date: 2020-02-20 13:46:50
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2022-10-07 14:36:08
 *
 * @package matrix-starter
 */

namespace Matrix_Starter;

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function widgets_init() {
  register_sidebar( array(
    'name'          => esc_html__( 'Sidebar', 'matrix-starter' ),
    'id'            => 'sidebar-1',
    'description'   => esc_html__( 'Add widgets here.', 'matrix-starter' ),
    'before_widget' => '<section id="%1$s" class="widget %2$s">',
    'after_widget'  => '</section>',
    'before_title'  => '<h2 class="widget-title">',
    'after_title'   => '</h2>',
  ) );
} // end widgets_init
