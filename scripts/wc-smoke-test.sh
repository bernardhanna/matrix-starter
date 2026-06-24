#!/usr/bin/env bash
#
# WooCommerce storefront smoke test.
#
# Loads the key storefront pages and fails if any returns an error status or
# contains a PHP fatal / WordPress "critical error" notice. Intended as a fast
# guard to run after template/override changes (e.g. deleting theme overrides),
# plugin updates, or deploys.
#
# Usage:
#   ./wc-smoke-test.sh [BASE_URL]
#
#   BASE_URL defaults to http://localhost:10029 (Local by Flywheel dev site).
#   Example (staging): ./wc-smoke-test.sh https://staging.example.com
#
# Exit code: 0 = all passed, 1 = one or more failed (suitable for cron/CI).
#
# Run automatically (example crontab entry, hourly):
#   0 * * * * /path/to/wc-smoke-test.sh >> /tmp/wc-smoke.log 2>&1

set -u

BASE="${1:-http://localhost:10029}"
TIMEOUT=30
UA="rd-wc-smoke-test"

# Key storefront surfaces. Box-builder + a simple product are included because
# they exercise the most custom template/plugin code paths.
PATHS=(
  "/"
  "/shop/"
  "/our-donuts/"
  "/donut-box/"
  "/product/large-sourdough-donuts-box-of-12/"
  "/product/branded-mug/"
  "/cart/"
  "/checkout/"
  "/my-account/"
)

fail=0
printf "%-50s %-6s %-7s %s\n" "PATH" "HTTP" "FATAL" "RESULT"
printf -- "%.0s-" {1..78}; echo

for p in "${PATHS[@]}"; do
  resp=$(curl -s -L --max-time "$TIMEOUT" -A "$UA" -w '\n__HTTP__%{http_code}' "$BASE$p" || true)
  code="${resp##*__HTTP__}"
  body="${resp%__HTTP__*}"

  fatal="no"
  if printf '%s' "$body" | grep -qiE "Fatal error|There has been a critical error|Parse error|Uncaught (Error|Exception)"; then
    fatal="YES"
  fi

  case "$code" in
    200|301|302) status_ok=1 ;;
    *) status_ok=0 ;;
  esac

  if [ "$fatal" = "YES" ] || [ "$status_ok" -ne 1 ]; then
    res="FAIL"
    fail=1
  else
    res="PASS"
  fi

  printf "%-50s %-6s %-7s %s\n" "$p" "$code" "$fatal" "$res"
done

echo
if [ "$fail" -eq 0 ]; then
  echo "ALL PASSED"
else
  echo "FAILURES DETECTED"
fi
exit "$fail"
