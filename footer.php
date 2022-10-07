<?php
/**
 * Template for displaying the footer
 *
 * Description for template.
 *
 * @Author: Bernard Hanna
 * @Date: 2020-05-11 13:33:49
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2022-09-07 11:57:45
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 * @package matrix-starter
 */

namespace Matrix_Starter;

?>

</div><!-- #content -->

<footer id="colophon" class="site-footer">


</footer><!-- #colophon -->

</div><!-- #page -->

<?php wp_footer(); ?>

<a
  href="#page"
  id="top"
  class="top no-external-link-indicator"
  data-version="<?php echo esc_attr( MATRIX_STARTER_VERSION ); ?>"
>
  <span class="screen-reader-text"><?php echo esc_html( get_default_localization( 'Back to top' ) ); ?></span>
  <span aria-hidden="true">&uarr;</span>
</a>

</body>
</html>
