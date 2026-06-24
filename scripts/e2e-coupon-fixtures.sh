#!/usr/bin/env bash
#
# Coupon e2e fixtures for the Rolling Donut store.
#
# Creates (or removes) a small set of clearly-named, test-only WooCommerce
# coupons used by tests/e2e/coupons.spec.js. They are idempotent: running "up"
# again deletes and recreates them so the suite always starts from a known state.
#
# All fixtures carry the description "RD e2e test fixture" so they are easy to
# spot and safe to delete in wp-admin → Marketing → Coupons.
#
# Usage:
#   bash scripts/e2e-coupon-fixtures.sh up      # create/refresh fixtures
#   bash scripts/e2e-coupon-fixtures.sh down    # remove fixtures
#
# Coupon codes can be overridden via env (must match the spec):
#   RD_COUPON_PERCENT   (default rd-e2e-10pct)     10% off, no restrictions
#   RD_COUPON_MINSPEND  (default rd-e2e-min500)    10% off, requires €500 min spend
#   RD_COUPON_EXPIRED   (default rd-e2e-expired)   10% off, expired yesterday
#
# Requires WP-CLI with WooCommerce active (resolves WP from the current path).

set -euo pipefail

ACTION="${1:-}"

export RD_COUPON_PERCENT="${RD_COUPON_PERCENT:-rd-e2e-10pct}"
export RD_COUPON_MINSPEND="${RD_COUPON_MINSPEND:-rd-e2e-min500}"
export RD_COUPON_EXPIRED="${RD_COUPON_EXPIRED:-rd-e2e-expired}"

if ! command -v wp >/dev/null 2>&1; then
  echo "error: wp (WP-CLI) not found on PATH" >&2
  exit 1
fi

case "$ACTION" in
  up)
    wp eval '
      $codes = [
        "percent"  => [getenv("RD_COUPON_PERCENT"),  ["type" => "percent", "amount" => 10]],
        "minspend" => [getenv("RD_COUPON_MINSPEND"), ["type" => "percent", "amount" => 10, "min" => 500]],
        "expired"  => [getenv("RD_COUPON_EXPIRED"),  ["type" => "percent", "amount" => 10, "expires" => "yesterday"]],
      ];
      foreach ($codes as $cfg) {
        list($code, $args) = $cfg;
        $existing = wc_get_coupon_id_by_code($code);
        if ($existing) { wp_delete_post($existing, true); }
        $c = new WC_Coupon();
        $c->set_code($code);
        $c->set_discount_type($args["type"]);
        $c->set_amount($args["amount"]);
        $c->set_description("RD e2e test fixture - safe to delete");
        if (isset($args["min"]))     { $c->set_minimum_amount($args["min"]); }
        if (isset($args["expires"])) { $c->set_date_expires(strtotime($args["expires"])); }
        $id = $c->save();
        echo "created {$code} (#{$id})\n";
      }
    '
    ;;
  down)
    wp eval '
      foreach ([getenv("RD_COUPON_PERCENT"), getenv("RD_COUPON_MINSPEND"), getenv("RD_COUPON_EXPIRED")] as $code) {
        $id = wc_get_coupon_id_by_code($code);
        if ($id) { wp_delete_post($id, true); echo "removed {$code} (#{$id})\n"; }
        else     { echo "absent {$code}\n"; }
      }
    '
    ;;
  *)
    echo "usage: bash scripts/e2e-coupon-fixtures.sh [up|down]" >&2
    exit 1
    ;;
esac
