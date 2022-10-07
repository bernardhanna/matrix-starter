<?php
/**
 * Edit link
 *
 * This function shows edit links.
 *
 * @Author:		Bernard Hanna
 * @Date:   		2022-02-08 17:18:33
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2022-02-08 17:26:27
 *
 * @package matrix-starter
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 */

namespace Matrix_Starter;

function matrix_edit_link() {

  if ( ! get_edit_post_link() ) {
    return;
  } ?>

    <p class="edit-link">
      <a href="<?php echo esc_url( get_edit_post_link() ); ?>">
        <?php echo esc_html( get_default_localization( 'Edit' ) ); ?>
        <span class="screen-reader-text"><?php echo esc_html( get_the_title() ); ?></span>
      </a>
    </p>
  <?php

}
