<?php

/**
 * @Author: Bernard Hanna
 *
 * @Date:   2023-10-09 12:48:57
 *
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-10-09 12:49:18
 */

/**
 * Single Product Title
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/product-title.php.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 *
 * @version 3.6.0
 */
defined('ABSPATH') || exit;

the_title('<h2 class="pt-4 pb-2 font-bold leading-none entry-titles !max-lg:hidden">', '</h2>');
?>
<style>
  /* Product title — responsive sizing applied to every single product page.
     Uses element+class specificity so it wins over Tailwind utilities without a CSS rebuild. */
  h2.entry-titles {
    font-size: 1.875rem; /* 30px — mobile */
    line-height: 1.05;
  }
  @media (min-width: 768px) {
    h2.entry-titles { font-size: 2.25rem; } /* 36px — tablet */
  }
  @media (min-width: 1250px) {
    h2.entry-titles { font-size: 2.75rem; } /* 44px — desktop */
  }
</style>
<?php
