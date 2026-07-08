<?php
/**
 * Shop breadcrumb — delegates to shared Rolling Donut breadcrumb nav.
 *
 * @see woocommerce_breadcrumb()
 */

defined('ABSPATH') || exit;

if (empty($breadcrumb) || ! function_exists('matrix_rd_render_breadcrumbs')) {
    return;
}

matrix_rd_render_breadcrumbs(matrix_rd_breadcrumb_crumbs_from_woocommerce($breadcrumb));
