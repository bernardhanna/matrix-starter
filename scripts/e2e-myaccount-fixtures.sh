#!/usr/bin/env bash
#
# My Account e2e fixtures for the Rolling Donut store.
#
# Creates (or removes) a single clearly-named, test-only WooCommerce customer
# used by the browser sign-in happy-path in tests/e2e/my-account-auth.spec.js
# ("user can log in with valid credentials") and the reorder flow in
# tests/e2e/order-again.spec.js. It is idempotent: running "up" again deletes
# and recreates the account (plus a seeded completed order so the "Order Again"
# button is available) so the suite starts from a known state.
#
# The fixture email uses the @matrix-e2e.test domain so it is easy to spot and
# safe to delete in wp-admin -> Users. "down" removes it again.
#
# Usage:
#   bash scripts/e2e-myaccount-fixtures.sh up      # create/refresh the fixture user
#   bash scripts/e2e-myaccount-fixtures.sh down    # remove the fixture user
#
# Credentials can be overridden via env (must match what the spec receives via
# MY_ACCOUNT_TEST_EMAIL / MY_ACCOUNT_TEST_PASSWORD):
#   RD_MYACCOUNT_EMAIL   (default rd-e2e-login@matrix-e2e.test)
#   RD_MYACCOUNT_PASS    (default RdE2eLogin!2024)
#
# Requires WP-CLI with WooCommerce active (resolves WP from the current path).

set -euo pipefail

ACTION="${1:-}"

export RD_MYACCOUNT_EMAIL="${RD_MYACCOUNT_EMAIL:-rd-e2e-login@matrix-e2e.test}"
export RD_MYACCOUNT_PASS="${RD_MYACCOUNT_PASS:-RdE2eLogin!2024}"

if ! command -v wp >/dev/null 2>&1; then
  echo "error: wp (WP-CLI) not found on PATH" >&2
  exit 1
fi

case "$ACTION" in
  up)
    wp eval '
      $email = getenv("RD_MYACCOUNT_EMAIL");
      $pass  = getenv("RD_MYACCOUNT_PASS");
      $existing = get_user_by("email", $email);
      if ($existing) { wp_delete_user($existing->ID, true); }
      $id = wp_insert_user([
        "user_login" => $email,
        "user_email" => $email,
        "user_pass"  => $pass,
        "role"       => "customer",
        "first_name" => "E2E",
        "last_name"  => "Login",
      ]);
      if (is_wp_error($id)) {
        fwrite(STDERR, "failed: " . $id->get_error_message() . "\n");
        exit(1);
      }
      echo "created {$email} (#{$id}) - safe to delete\n";

      // Seed a completed order so the "Order Again" button is available.
      // WooCommerce only offers reorder for statuses in
      // woocommerce_valid_order_statuses_for_order_again (default: completed).
      if (!function_exists("wc_create_order")) {
        echo "skip order seed - WooCommerce not active\n";
      } else {
        $products = wc_get_products([
          "limit"   => 1,
          "status"  => "publish",
          "type"    => ["simple", "variation"],
          "orderby" => "ID",
          "order"   => "ASC",
        ]);
        if (empty($products)) {
          echo "skip order seed - no purchasable product found\n";
        } else {
          $product = $products[0];
          $order = wc_create_order(["customer_id" => $id]);
          $order->add_product($product, 1);
          $order->set_address([
            "first_name" => "E2E",
            "last_name"  => "Login",
            "email"      => $email,
            "country"    => "IE",
          ], "billing");
          $order->calculate_totals();
          $order->update_status("completed", "Seeded by e2e-myaccount-fixtures.", true);
          echo "seeded completed order #" . $order->get_id() . " (" . $product->get_name() . ")\n";
        }
      }
    '
    ;;
  down)
    wp eval '
      $email = getenv("RD_MYACCOUNT_EMAIL");
      $u = get_user_by("email", $email);
      if ($u) {
        if (function_exists("wc_get_orders")) {
          $orders = wc_get_orders(["customer_id" => $u->ID, "limit" => -1, "return" => "ids"]);
          foreach ($orders as $oid) {
            $o = wc_get_order($oid);
            if ($o) { $o->delete(true); }
          }
          if ($orders) { echo "removed " . count($orders) . " seeded order(s)\n"; }
        }
        wp_delete_user($u->ID, true);
        echo "removed {$email} (#{$u->ID})\n";
      } else {
        echo "absent {$email}\n";
      }
    '
    ;;
  *)
    echo "usage: bash scripts/e2e-myaccount-fixtures.sh [up|down]" >&2
    exit 1
    ;;
esac
