<?php
/**
 * Single product price (stacked below title).
 *
 * @package WooCommerce\Templates
 * @version 3.0.0
 */

defined('ABSPATH') || exit;

global $product;
?>
<p class="price product-summary-price">
    <?php echo $product->get_price_html(); ?>
</p>
