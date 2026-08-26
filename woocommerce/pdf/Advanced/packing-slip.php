<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly 
?>

<?php do_action('wpo_wcpdf_before_document', $this->type, $this->order); ?>
<style>
    .del_info {
        display: none;
    }
    table {
        width: 100%;
    }

    h2 {
        line-height: 1.5rem;
    }

    .container.head {
        display: none;
    }

    .order-details td {
        vertical-align: middle;
        text-align: left;
    }

    td.order-data {
        width: 60%;
        text-align: right;
    }

    .order-data tbody {
        float: right;
    }

    .order-data tbody tr {
        text-align: right;
        float: right;
        width: 100%;
    }

    td.order-data table th {
        font-weight: bold;
    }

    .wc-item-meta li img {
        display: none !important;
    }

    .box .wc-item-meta li {
        margin-top: 5px!important;
    }

    ul li img {
        display: none !important;
    }

    .item-meta p img {
        display: none !important;
    }

    .pickup-location {
        font-weight: bold !important;
        font-size: 17px;
        margin-top: 1rem;
        line-height: 1rem;
    }

    .product {
        display: flex;
        flex-direction: row;
    }

    .shipping-method {
        line-height: 1rem;
        font-weight: bold;
        font-size: 16px;
    }

    .order-data tr.shipping-method th,
    .order-data tr.shipping-method td {
        font-weight: bold;
        font-size: 16px;
        line-height: 1.3rem;
    }

    .order-data tr.pickup-location th,
    .order-data tr.pickup-location td {
        font-weight: bold !important;
        font-size: 17px;
        line-height: 1.3rem;
    }

    .order-details td,
    .order-details th {
        border-bottom: 1px #ccc solid;
        border-top: 1px #ccc solid;
        padding: 0px;
    }

    .order-details th.quantity,
    .order-details td.quantity {
        text-align: right;
        width: 15%;
    }

    .item-name,
    .item-meta {
        width: 100%;
        /*white-space: nowrap; */
        text-overflow: ellipsis;
    }

    .item-meta .wc-item-meta {
        margin-top: 5px;
    }

    .item-meta .wc-item-meta li {
        line-height: 1.6em !important; /* Increases the space between lines */
        padding-top: 2px;
        padding-bottom: 2px;
        margin: 0;
    }

    /* Optional: Ensure the label (e.g. "Special Requests:") also follows the rule */
    .item-meta .wc-item-meta li strong {
        line-height: 1.6em !important;
    }

    .product {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .product-bundle .product .item-name {
        font-weight: bold;
    }

    .product-bundle .quantity {
        font-weight: bold;
    }

    .box {
        margin-top: 10px!important;
       margin-bottom: 10px!important;
    }

    .box .item-name {
        font-size: 16px;
        padding-top: 10px;
        padding-bottom: 10px;
        font-weight: bold;
    }

    .order-details .bundled-item {
        border-top: 1px solid grey;
        border-bottom: 1px #ccc solid;
        border-top: 1px #ccc solid;
    }

    .order-details tr.bundled-item td.product {
        padding-left: 0px;
    }

    .allergen {
        display: block;
    }

    .bundled-item .wc-item-meta {
        display: none;
    }

    .donut .wc-item-meta {
        display: none !important;
    }

    .donut-box-builder .wc-item-meta {
        display: block;
    }

    .box .wc-item-meta li p, .box .wc-item-meta li .wc-item-meta-label {
        font-size: 12pt;
        padding-top: 5px;
        padding-bottom: 5px;
        position: relative;
    }

    .box .product {
        padding-top: 10px;
        padding-bottom: 5px;
        position: relative;
    }

    /* Legacy packing slips never printed SKU/weight — hide if products have them. */
    .order-details dl.meta {
        display: none;
    }
</style>
<table class="container head">
    <tr style="padding-top: 5px; padding-bottom: 5px;">
        <td class="header">
            <?php
            if ($this->has_header_logo()) {
                $this->header_logo();
            } else {
                echo $this->get_title();
            }
            ?>
        </td>
        <td class="shop-info">
            <div class="shop-name">
                <h3><?php $this->shop_name(); ?></h3>
            </div>
            <div class="shop-address"><?php $this->shop_address(); ?></div>
        </td>
    </tr>
</table>

<h1 class="document-type-label">
    <?php if ($this->has_header_logo()) echo $this->get_title(); ?>
</h1>

<?php do_action('wpo_wcpdf_after_document_label', $this->type, $this->order); ?>

<?php
// Legacy template read $is_local_pickup before defining it. That falsy value
// made collection orders print the full address (see packing-slip-29798.pdf).
if (! isset($is_local_pickup)) {
    $is_local_pickup = false;
}

// Helper to normalize address strings for comparison
if (!function_exists('rd_normalize_addr')) {
    function rd_normalize_addr($addr){
        $plain = trim(wp_strip_all_tags((string)$addr));
        $plain = preg_replace('/\s+/', ' ', $plain);
        return strtolower($plain);
    }
}

// Build normalized billing/shipping strings
$billing_addr_raw  = $this->order->get_formatted_billing_address();   // HTML string
$shipping_addr_raw = $this->order->get_formatted_shipping_address();  // HTML string

$billing_norm  = rd_normalize_addr($billing_addr_raw);
$shipping_norm = rd_normalize_addr($shipping_addr_raw);

// Should we show shipping INSTEAD OF billing?
$use_shipping_in_primary = (!$is_local_pickup)
    && !empty($shipping_norm)
    && ($shipping_norm !== $billing_norm);

// Convenience vars
$billing_phone   = $this->order->get_billing_phone();
$shipping_phone   = $this->order->get_shipping_phone();
$billing_email   = $this->order->get_billing_email();
$order_number    = $this->order->get_order_number();

// Eircode/Postcode (prefer your custom shipping eircode when showing shipping)
$ship_eircode    = get_post_meta($this->order->get_id(), '_custom_shipping_eircode', true);
$ship_postcode   = $this->order->get_shipping_postcode();
$bill_eircode    = get_post_meta($this->order->get_id(), '_custom_billing_eircode', true); // if you have one
$bill_postcode   = $this->order->get_billing_postcode();
?>

<table class="order-data-addresses">
  <tr>
    <!-- PRIMARY ADDRESS COLUMN (shows Shipping if different, else Billing; pickup logic preserved) -->
<td class="address billing-address">
  <?php
  if ($is_local_pickup) {
      // Local pickup: show customer name + contact only
      echo esc_html($this->order->get_formatted_billing_full_name());

      if ($billing_phone) {
          echo '<br /><strong>Billing phone:</strong> '.esc_html($billing_phone);
      }

      if ($shipping_phone) {
          echo '<br /><strong>Shipping phone:</strong> '.esc_html($shipping_phone);
      }


      if ($billing_email) {
          echo '<br>' . esc_html($billing_email) . '</br>';
      }
      

  } elseif ($use_shipping_in_primary) {
      // Show SHIPPING (no labels)
      do_action('wpo_wcpdf_before_shipping_address', $this->type, $this->order);
      $this->shipping_address();

      // Show Eircode/Postcode if present
      if (!empty($ship_eircode) || !empty($ship_postcode)) {
          echo '<p style="margin:0;padding:0;">' . esc_html(!empty($ship_eircode) ? $ship_eircode : $ship_postcode) . '</p>';
      }

      // Keep contact info (phone/email from billing)
      if (!empty($billing_email)) {
          echo '<p style="margin:0;padding:0;">' . esc_html($billing_email) . '</p>';
      }
      
      if (!empty($billing_phone)) {
          echo '<p style="margin:0;padding:0;"><br /><strong>Billing phone:</strong> ' . esc_html($billing_phone) . '</p>';
      }

      if (!empty($shipping_phone)) {
          echo '<p style="margin:0;padding:0;"><br /><strong>Shipping phone:</strong> ' . esc_html($shipping_phone) . '</p>';
      }
     
      

      do_action('wpo_wcpdf_after_shipping_address', $this->type, $this->order);

  } else {
      // Show BILLING (no "Billing Address" label)
      do_action('wpo_wcpdf_before_billing_address', $this->type, $this->order);
      $this->billing_address();

      // Billing Eircode / Postcode
      $bill_code_out = !empty($bill_eircode) ? $bill_eircode : $bill_postcode;
      if (!empty($bill_code_out)) {
          echo '<p style="margin:0;padding:0;">' . esc_html($bill_code_out) . '</p>';
      }

      if (!empty($billing_email)) {
          echo '<p style="margin:0;padding:0;">' . esc_html($billing_email) . '</p>';
      }

      if (!empty($billing_phone)) {
          echo '<p style="margin:0;padding:0;"><br /><strong>Billing phone:</strong> ' . esc_html($billing_phone) . '</p>';
      }

      if (!empty($shipping_phone)) {
          echo '<p style="margin:0;padding:0;"><br /><strong>Shipping phone:</strong> ' . esc_html($shipping_phone) . '</p>';
      }
      

      do_action('wpo_wcpdf_after_billing_address', $this->type, $this->order);
  }
  ?>
</td>


    <!-- SECOND COLUMN: keep empty or use for extra info.
         We purposely DO NOT show billing here when shipping differs. -->
    <td class="address shipping-address">
      <?php
      // If you want to show nothing, leave blank.
      // If you ever want to show pickup location summary here for pickup orders, you can reuse your existing block.
      ?>
    </td>

    <!-- ORDER META COLUMN (unchanged) -->
    <td class="order-data">
      <table>
        <?php do_action('wpo_wcpdf_before_order_data', $this->type, $this->order); ?>
        <tr class="order-number">
          <th><?php _e('Order Number:', 'woocommerce-pdf-invoices-packing-slips'); ?></th>
          <td><?php $this->order_number(); ?></td>
        </tr>
        <tr class="order-date">
          <th><?php _e('Order Date:', 'woocommerce-pdf-invoices-packing-slips'); ?></th>
          <td><?php $this->order_date(); ?></td>
        </tr>
        <?php if ($this->get_shipping_method()) : ?>
          <tr class="shipping-method">
            <th><?php _e('Shipping Method:', 'woocommerce-pdf-invoices-packing-slips'); ?></th>
            <td><?php $this->shipping_method(); ?></td>
          </tr>
        <?php endif; ?>

        <?php
        // Your pickup/delivery date block (unchanged)
        $shipping_items = $this->order->get_items('shipping');
        $is_local_pickup_plus = false;
        $location_id = '';
        $location_name_meta = '';
        $pickup_date = $this->order->get_meta('jckwds_date');
        $timeslot    = $this->order->get_meta('jckwds_timeslot');

        foreach ($shipping_items as $item_id => $shipping_item) {
            $method_id = $shipping_item->get_method_id();
            if ($method_id === 'local_pickup_plus' || $method_id === 'local_pickup') {
                $is_local_pickup_plus = true;
                $location_id = $shipping_item->get_meta('_pickup_location_id');
                $location_name_meta = (string) $shipping_item->get_meta('_pickup_location_name');
                break;
            }
        }

        if ($is_local_pickup_plus) :
          $location_post         = $location_id ? get_post($location_id) : null;
          $location_name         = $location_post ? $location_post->post_title : '';
          if ($location_name === '' && $location_name_meta !== '') {
              $location_name = $location_name_meta;
          }
          $location_address_meta = $location_id ? get_post_meta($location_id, '_pickup_location_address', true) : '';
          $location_address_out  = is_string($location_address_meta) ? $location_address_meta : '';
        ?>
          <?php if ($location_name !== '') : ?>
          <tr class="pickup-location">
            <th><?php _e('Pick-up Location:', 'woocommerce-pdf-invoices-packing-slips'); ?></th>
            <td><?php echo esc_html($location_name); ?><?php if ($location_address_out !== '') { echo '<br>' . esc_html($location_address_out); } ?></td>
          </tr>
          <?php endif; ?>
          <tr class="delivery-or-collection-date">
            <th><?php _e('Collection Date:', 'woocommerce-pdf-invoices-packing-slips'); ?></th>
            <td><?php echo esc_html($pickup_date); ?><?php if ($timeslot) echo ' ' . esc_html($timeslot); ?></td>
          </tr>
        <?php elseif ($pickup_date) : ?>
          <tr class="delivery-or-collection-date">
            <th><?php _e('Delivery Date:', 'woocommerce-pdf-invoices-packing-slips'); ?></th>
            <td><?php echo esc_html($pickup_date); ?><?php if ($timeslot) echo ' ' . esc_html($timeslot); ?></td>
          </tr>
        <?php endif; ?>

        <?php do_action('wpo_wcpdf_after_order_data', $this->type, $this->order); ?>
      </table>
    </td>
  </tr>
</table>


<?php do_action('wpo_wcpdf_before_order_details', $this->type, $this->order); ?>
<table class="order-details">
    <?php do_action('display_custom_meta'); ?>
    <thead>
        <tr>
            <th class="product"><?php _e('Product', 'woocommerce-pdf-invoices-packing-slips'); ?></th>
            <th class="quantity"><?php _e('Quantity', 'woocommerce-pdf-invoices-packing-slips'); ?></th>
        </tr>
    </thead>
    <tbody>
        <style>
            .order-details .merch .wc-item-meta {
                display: block !important;
            }

            /* Ensure item meta data is displayed */
            .wc-item-meta {
                display: block;
            }
        </style>
        <?php do_action('display_custom_add_product_ons'); ?>
        <?php
        // Get the order items as objects
        $items = $this->order->get_items();
        $special_requests_displayed = false; // Initialize flag to check if special requests are displayed
        $counter = 0; // Initialize a counter outside of the loop

        if (sizeof($items) > 0) : foreach ($items as $item_id => $item) :
                $product_id = $item->get_product_id();
                $product = $item->get_product();

                // Get the product categories
                $categories = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'slugs'));
                $category_classes = implode(' ', array_map('sanitize_html_class', $categories));

                // Get the RD product type
                $rd_product_types = wp_get_post_terms($product_id, 'rd_product_type', array('fields' => 'slugs'));
                $rd_product_type_classes = implode(' ', array_map('sanitize_html_class', $rd_product_types));
        ?>
                <tr class="line_item <?php echo ($counter == 0) ? 'first-line-item' : ''; ?> <?php echo apply_filters('wpo_wcpdf_item_row_class', $item_id, $this->type, $this->order, $item_id); ?> <?php echo esc_attr($category_classes); ?> <?php echo esc_attr($rd_product_type_classes); ?>">
                    <td class="product">
                        <?php $description_label = __('Description', 'woocommerce-pdf-invoices-packing-slips'); // registering alternate label translation 
                        ?>
                        <span class="item-name"><?php echo $item->get_name(); ?></span>
                        <?php do_action('wpo_wcpdf_before_item_meta', $this->type, $item, $this->order); ?>
                        <?php

                        // Fetch allergens (parent product so box flavours / variations inherit them).
                        $allergen_pid = function_exists('dbb_resolve_allergen_product_id')
                            ? dbb_resolve_allergen_product_id((int) $item->get_product_id())
                            : (int) $item->get_product_id();
                        $allergen_names = function_exists('dbb_get_product_allergen_names')
                            ? dbb_get_product_allergen_names($allergen_pid)
                            : array();
                        if (empty($allergen_names) && function_exists('get_field')) {
                            $product_allergens = get_field('product_allergens', $allergen_pid);
                            if ($product_allergens) {
                                foreach ($product_allergens as $allergen) {
                                    if ($allergen instanceof WP_Post && $allergen->post_title !== '') {
                                        $allergen_names[] = $allergen->post_title;
                                    }
                                }
                            }
                        }
                        $allergen_text = implode(', ', $allergen_names);

                        if ($allergen_text !== '') {
                            echo '<span class="allergen"><strong>' . __('Allergens:', 'woocommerce') . '</strong> ' . esc_html($allergen_text) . '</span>';
                        } ?>
                        <div class="item-meta"> <?php
                            // Output item meta data
                            wc_display_item_meta($item);
                            ?>
                        </div>
                        <dl style="margin: 0px; padding: 0px;" class="meta">
                            <?php $description_label = __('SKU', 'woocommerce-pdf-invoices-packing-slips'); // registering alternate label translation 
                            ?>
                            <?php if ($product && !empty($product->get_sku())) : ?><dt class="sku"><?php _e('SKU:', 'woocommerce-pdf-invoices-packing-slips'); ?></dt>
                                <dd class="sku"><?php echo $product->get_sku(); ?></dd><?php endif; ?>
                            <?php if ($product && !empty($product->get_weight())) : ?><dt class="weight"><?php _e('Weight:', 'woocommerce-pdf-invoices-packing-slips'); ?></dt>
                                <dd class="weight"><?php echo $product->get_weight(); ?><?php echo get_option('woocommerce_weight_unit'); ?></dd><?php endif; ?>
                        </dl>
                        <?php do_action('wpo_wcpdf_after_item_meta', $this->type, $item, $this->order); ?>
                    </td>
                    <td class="quantity"><?php echo $item->get_quantity(); ?></td>
                </tr>
        <?php
                // Increment the counter after each row is processed
                $counter++;
            endforeach;
        endif;
        ?>
    </tbody>
</table>
<?php do_action('wpo_wcpdf_after_order_details', $this->type, $this->order); ?>
<style>
    .customer-notes {
        padding-bottom: 1rem;
    }

    .customer-notes h3 {
        font-size: 18px;
        font-weight: bold;
    }

    .customer-notes p {
        font-size: 18px;
        line-height: 1.2rem;
    }
</style>
<div class="customer-notes">
    <?php if ($this->get_shipping_notes()) : ?>
        <h3><?php _e('Customer Notes', 'woocommerce-pdf-invoices-packing-slips'); ?></h3>
        <p><?php echo $this->get_shipping_notes(); ?></p>
    <?php endif; ?>
</div>

<?php do_action('wpo_wcpdf_after_customer_notes', $this->type, $this->order); ?>

<div class="order-notes customer-notes">
    <?php
    // Retrieve admin-only order notes excluding system-generated notes
    $order_notes = wc_get_order_notes(array('order_id' => $this->order->get_id()));
    $filtered_notes = array_filter($order_notes, function ($note) {
        return function_exists('matrix_rd_packing_slip_note_is_visible')
            ? matrix_rd_packing_slip_note_is_visible($note)
            : ((int) $note->customer_note === 1);
    });
    if (!empty($filtered_notes)) : ?>
        <h3><?php _e('Order Notes', 'woocommerce-pdf-invoices-packing-slips'); ?></h3>
        <ul>
            <?php foreach ($filtered_notes as $note) : ?>
                <li>
                    <p><?php echo esc_html($note->content); ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>


<?php if ($this->get_footer()): ?>
    <div id="footer">
        <?php $this->footer(); ?>
    </div><!-- #letter-footer -->
<?php endif; ?>

<?php do_action('wpo_wcpdf_after_document', $this->type, $this->order); ?>