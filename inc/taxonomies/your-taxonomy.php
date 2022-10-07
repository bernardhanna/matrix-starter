<?php
/**
 * @Author: Bernard Hanna
 * @Date: 2020-02-18 15:05:35
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2021-05-04 11:13:17
 *
 * @package matrix-starter
 */

namespace Matrix_Starter;

/**
 * Registers the Your Taxonomy taxonomy.
 *
 * @param Array $post_types Optional. Post types in
 * which the taxonomy should be registered.
 */
class Your_Taxonomy extends Taxonomy {


  public function register( array $post_types = [] ) {
    // Taxonomy labels.
    $labels = [
      'name'                  => _x( 'Your Taxonomies', 'Taxonomy plural name', 'matrix-starter' ),
      'singular_name'         => _x( 'Your Taxonomy', 'Taxonomy singular name', 'matrix-starter' ),
      'search_items'          => __( 'Search Your Taxonomies', 'matrix-starter' ),
      'popular_items'         => __( 'Popular Your Taxonomies', 'matrix-starter' ),
      'all_items'             => __( 'All Your Taxonomies', 'matrix-starter' ),
      'parent_item'           => __( 'Parent Your Taxonomy', 'matrix-starter' ),
      'parent_item_colon'     => __( 'Parent Your Taxonomy', 'matrix-starter' ),
      'edit_item'             => __( 'Edit Your Taxonomy', 'matrix-starter' ),
      'update_item'           => __( 'Update Your Taxonomy', 'matrix-starter' ),
      'add_new_item'          => __( 'Add New Your Taxonomy', 'matrix-starter' ),
      'new_item_name'         => __( 'New Your Taxonomy', 'matrix-starter' ),
      'add_or_remove_items'   => __( 'Add or remove Your Taxonomies', 'matrix-starter' ),
      'choose_from_most_used' => __( 'Choose from most used Taxonomies', 'matrix-starter' ),
      'menu_name'             => __( 'Your Taxonomy', 'matrix-starter' ),
    ];

    $args = [
      'labels'            => $labels,
      'public'            => false,
      'show_in_nav_menus' => true,
      'show_admin_column' => true,
      'hierarchical'      => true,
      'show_tagcloud'     => false,
      'show_ui'           => true,
      'query_var'         => false,
      'rewrite'           => [
        'slug' => 'your-taxonomy',
      ],
    ];

    $this->register_wp_taxonomy( $this->slug, $post_types, $args );
  }

}
