<?php
/**
 * @Author: Niku Hietanen
 * @Date: 2020-02-18 15:06:45
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2021-05-04 11:13:10
 *
 * @package matrix-starter
 **/

namespace Matrix_Starter;

/**
 * Registers the Your Post Type post type.
 */
class Your_Post_Type extends Post_Type {

  public function register() {

    // Modify all the i18ized strings here.
    $generated_labels = [
      'menu_name'          => __( 'Your Post Type', 'matrix-starter' ),
      'name'               => _x( 'Your Post Types', 'post type general name', 'matrix-starter' ),
      'singular_name'      => _x( 'Your Post Type', 'post type singular name', 'matrix-starter' ),
      'name_admin_bar'     => _x( 'Your Post Type', 'add new on admin bar', 'matrix-starter' ),
      'add_new'            => _x( 'Add New', 'thing', 'matrix-starter' ),
      'add_new_item'       => __( 'Add New Your Post Type', 'matrix-starter' ),
      'new_item'           => __( 'New Your Post Type', 'matrix-starter' ),
      'edit_item'          => __( 'Edit Your Post Type', 'matrix-starter' ),
      'view_item'          => __( 'View Your Post Type', 'matrix-starter' ),
      'all_items'          => __( 'All Your Post Types', 'matrix-starter' ),
      'search_items'       => __( 'Search Your Post Types', 'matrix-starter' ),
      'parent_item_colon'  => __( 'Parent Your Post Types:', 'matrix-starter' ),
      'not_found'          => __( 'No your post types found.', 'matrix-starter' ),
      'not_found_in_trash' => __( 'No your post types found in Trash.', 'matrix-starter' ),
    ];

    // Definition of the post type arguments. For full list see:
    // http://codex.wordpress.org/Function_Reference/register_post_type
    $args = [
      'labels'              => $generated_labels,
      'description'         => '',
      'menu_icon'           => null,
      'public'              => false,
      'has_archive'         => false,
      'exclude_from_search' => false,
      'show_ui'             => true,
      'show_in_menu'        => true,
      'show_in_rest'        => false,
      'rewrite'             => [
        'with_front'  => false,
        'slug'        => 'your-post-type',
      ],
      'supports'            => [ 'title', 'editor', 'thumbnail', 'revisions' ],
      'taxonomies'          => [],
    ];

    $this->register_wp_post_type( $this->slug, $args );
  }
}
