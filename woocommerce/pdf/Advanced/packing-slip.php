<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<?php do_action( 'wpo_wcpdf_before_document', $this->type, $this->order ); ?>
<style>
    table { width: 100%; }

    /* Header logo/shop table is hidden — only the "Packing Slip" label shows. */
    .container.head { display: none; }

    /* Right-hand meta column: right aligned with bold labels. */
    td.order-data { width: 60%; text-align: right; }
    .order-data tbody { float: right; }
    .order-data tbody tr { text-align: right; float: right; width: 100%; }
    td.order-data table th { font-weight: bold; }

    /* Shipping method emphasised (bold + larger) than the other meta rows. */
    .order-data tr.shipping-method th,
    .order-data tr.shipping-method td { font-weight: bold; font-size: 16px; line-height: 1.3rem; }

    .order-data tr.pickup-location th,
    .order-data tr.pickup-location td { font-weight: bold; }

    /* Order details table. */
    .order-details td { vertical-align: middle; text-align: left; }
    .order-details td,
    .order-details th { border-bottom: 1px #ccc solid; border-top: 1px #ccc solid; padding: 0; }
    .order-details .item-name { width: 100%; }
    .order-details .product-bundle .item-name,
    .order-details .product-bundle .quantity { font-weight: bold; }

    /* Allergens appear on their own line under each product name. */
    .allergen { display: block; }

    /* Hide attribute images that WooCommerce/woosb may inject into item meta. */
    .item-meta img,
    .wc-item-meta li img { display: none !important; }

    .customer-notes { padding-bottom: 1rem; }
    .customer-notes h3 { font-size: 16px; font-weight: bold; }
    .customer-notes p { line-height: 1.3rem; }
</style>

<table class="container head">
    <tr>
        <td class="header">
        <?php if ( $this->has_header_logo() ) { $this->header_logo(); } else { echo $this->get_title(); } ?>
        </td>
        <td class="shop-info">
            <div class="shop-name"><h3><?php $this->shop_name(); ?></h3></div>
            <div class="shop-address"><?php $this->shop_address(); ?></div>
        </td>
    </tr>
</table>

<h1 class="document-type-label"><?php echo $this->get_title(); ?></h1>

<?php do_action( 'wpo_wcpdf_after_document_label', $this->type, $this->order ); ?>

<?php
// Determine whether this order is a collection (local pickup) order so we can
// switch the address block + date label accordingly.
$is_local_pickup = false;
$pickup_location_id = '';
foreach ( $this->order->get_items( 'shipping' ) as $shipping_item ) {
    $method_id = $shipping_item->get_method_id();
    if ( 'local_pickup' === $method_id || 'local_pickup_plus' === $method_id ) {
        $is_local_pickup    = true;
        $pickup_location_id = $shipping_item->get_meta( '_pickup_location_id' );
        break;
    }
}

$billing_phone  = $this->order->get_billing_phone();
$shipping_phone = $this->order->get_shipping_phone();
$billing_email  = $this->order->get_billing_email();
$ship_eircode   = $this->order->get_meta( '_custom_shipping_eircode' );
$ship_postcode  = $this->order->get_shipping_postcode();

$has_shipping_address = (bool) $this->order->get_formatted_shipping_address();
$use_shipping_primary = ! $is_local_pickup && $has_shipping_address && $this->ships_to_different_address();
?>

<table class="order-data-addresses">
    <tr>
        <td class="address billing-address">
            <?php
            if ( $is_local_pickup ) {
                // Collection: name + contact only.
                echo esc_html( $this->order->get_formatted_billing_full_name() );
            } elseif ( $use_shipping_primary ) {
                do_action( 'wpo_wcpdf_before_shipping_address', $this->type, $this->order );
                $this->shipping_address();
                $code = ! empty( $ship_eircode ) ? $ship_eircode : $ship_postcode;
                if ( ! empty( $code ) ) {
                    echo '<div>' . esc_html( $code ) . '</div>';
                }
                do_action( 'wpo_wcpdf_after_shipping_address', $this->type, $this->order );
            } else {
                do_action( 'wpo_wcpdf_before_billing_address', $this->type, $this->order );
                $this->billing_address();
                do_action( 'wpo_wcpdf_after_billing_address', $this->type, $this->order );
            }

            if ( ! empty( $billing_email ) ) {
                echo '<div class="billing-email">' . esc_html( $billing_email ) . '</div>';
            }
            if ( ! empty( $billing_phone ) ) {
                echo '<div class="billing-phone"><strong>' . esc_html__( 'Billing phone:', 'woocommerce-pdf-invoices-packing-slips' ) . '</strong> ' . esc_html( $billing_phone ) . '</div>';
            }
            if ( ! empty( $shipping_phone ) ) {
                echo '<div class="shipping-phone"><strong>' . esc_html__( 'Shipping phone:', 'woocommerce-pdf-invoices-packing-slips' ) . '</strong> ' . esc_html( $shipping_phone ) . '</div>';
            }
            ?>
        </td>
        <td class="address shipping-address"></td>
        <td class="order-data">
            <table>
                <?php do_action( 'wpo_wcpdf_before_order_data', $this->type, $this->order ); ?>
                <tr class="order-number">
                    <th><?php _e( 'Order Number:', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
                    <td><?php $this->order_number(); ?></td>
                </tr>
                <tr class="order-date">
                    <th><?php _e( 'Order Date:', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
                    <td><?php $this->order_date(); ?></td>
                </tr>
                <?php if ( $this->get_shipping_method() ) : ?>
                <tr class="shipping-method">
                    <th><?php _e( 'Shipping Method:', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
                    <td><?php $this->shipping_method(); ?></td>
                </tr>
                <?php endif; ?>

                <?php
                $jckwds_date     = $this->order->get_meta( 'jckwds_date' );
                $jckwds_timeslot = $this->order->get_meta( 'jckwds_timeslot' );

                if ( $is_local_pickup && $pickup_location_id ) :
                    $location_post    = get_post( $pickup_location_id );
                    $location_name    = $location_post ? $location_post->post_title : '';
                    $location_address = get_post_meta( $pickup_location_id, '_pickup_location_address', true );
                    ?>
                    <?php if ( $location_name ) : ?>
                    <tr class="pickup-location">
                        <th><?php _e( 'Pick-up Location:', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
                        <td><?php echo esc_html( $location_name ); ?></td>
                    </tr>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ( $jckwds_date ) : ?>
                <tr class="delivery-or-collection-date">
                    <th><?php echo $is_local_pickup ? esc_html__( 'Collection Date:', 'woocommerce-pdf-invoices-packing-slips' ) : esc_html__( 'Delivery Date:', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
                    <td><?php echo esc_html( $jckwds_date ); ?><?php echo $jckwds_timeslot ? ' ' . esc_html( $jckwds_timeslot ) : ''; ?></td>
                </tr>
                <?php endif; ?>

                <?php do_action( 'wpo_wcpdf_after_order_data', $this->type, $this->order ); ?>
            </table>
        </td>
    </tr>
</table>

<?php do_action( 'wpo_wcpdf_before_order_details', $this->type, $this->order ); ?>

<table class="order-details">
    <thead>
        <tr>
            <th class="product"><?php _e( 'Product', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
            <th class="quantity"><?php _e( 'Quantity', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        $items = $this->order->get_items();
        if ( sizeof( $items ) > 0 ) : foreach ( $items as $item_id => $item ) :
            $product = $item->get_product();
            ?>
            <tr class="<?php echo apply_filters( 'wpo_wcpdf_item_row_class', $item_id, $this->type, $this->order, $item_id ); ?>">
                <td class="product">
                    <span class="item-name"><?php echo wp_kses_post( $item->get_name() ); ?></span>
                    <?php do_action( 'wpo_wcpdf_before_item_meta', $this->type, $item, $this->order ); ?>
                    <?php
                    // Allergens — resolved from the parent product so box contents
                    // (variations) inherit the donut's allergens.
                    $allergen_pid = function_exists( 'dbb_resolve_allergen_product_id' )
                        ? dbb_resolve_allergen_product_id( (int) $item->get_product_id() )
                        : (int) $item->get_product_id();
                    $allergen_names = function_exists( 'dbb_get_product_allergen_names' )
                        ? dbb_get_product_allergen_names( $allergen_pid )
                        : array();
                    if ( ! empty( $allergen_names ) ) :
                        ?>
                        <span class="allergen"><strong><?php esc_html_e( 'Allergens:', 'woocommerce-pdf-invoices-packing-slips' ); ?></strong> <?php echo esc_html( implode( ', ', $allergen_names ) ); ?></span>
                    <?php endif; ?>
                    <div class="item-meta"><?php wc_display_item_meta( $item ); ?></div>
                    <?php do_action( 'wpo_wcpdf_after_item_meta', $this->type, $item, $this->order ); ?>
                </td>
                <td class="quantity"><?php echo esc_html( $item->get_quantity() ); ?></td>
            </tr>
        <?php endforeach; endif; ?>
    </tbody>
</table>

<?php do_action( 'wpo_wcpdf_after_order_details', $this->type, $this->order ); ?>

<?php if ( $this->get_shipping_notes() ) : ?>
<div class="customer-notes">
    <h3><?php _e( 'Customer Notes', 'woocommerce-pdf-invoices-packing-slips' ); ?></h3>
    <p><?php echo wp_kses_post( $this->get_shipping_notes() ); ?></p>
</div>
<?php endif; ?>

<?php do_action( 'wpo_wcpdf_after_customer_notes', $this->type, $this->order ); ?>

<?php if ( $this->get_footer() ) : ?>
<div id="footer">
    <?php $this->footer(); ?>
</div><!-- #letter-footer -->
<?php endif; ?>
<?php do_action( 'wpo_wcpdf_after_document', $this->type, $this->order ); ?>
