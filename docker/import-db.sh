#!/usr/bin/env bash
# =============================================================================
# docker/import-db.sh — Import a live/staging SQL dump into the Docker stack
#
# Usage:
#   npm run docker:import -- path/to/dump.sql
#   npm run docker:import -- path/to/dump.sql https://live.example.com http://localhost:8080
# =============================================================================
set -euo pipefail

THEME_ROOT="/var/www/html/wp-content/themes/${THEME_SLUG:-matrix-starter}"
WP_ROOT="${WP_PATH:-/var/www/html}"
DUMP_FILE="${1:-}"
FROM_URL="${2:-}"
TO_URL="${3:-}"

cd "$THEME_ROOT"

if [ -f .env.docker ]; then
  set -a
  # shellcheck disable=SC1091
  source .env.docker
  set +a
fi

export WP_PATH="$WP_ROOT"
TO_URL="${TO_URL:-${WP_HOME:-http://localhost:8080/}}"

usage() {
  echo "Usage: $0 <dump.sql> [from_url] [to_url]"
  echo ""
  echo "  dump.sql   Path to .sql file (mounted into container or in theme dir)"
  echo "  from_url   Production URL to replace (auto-detected from DB if omitted)"
  echo "  to_url     Target URL (default: WP_HOME from .env.docker)"
  exit 1
}

if [ -z "$DUMP_FILE" ] || [ ! -f "$DUMP_FILE" ]; then
  echo "❌ SQL dump not found: ${DUMP_FILE:-<missing>}"
  usage
fi

echo "⏳ Waiting for database…"
attempts=0
while [ $attempts -lt 30 ]; do
  if wp --path="$WP_ROOT" --skip-plugins --skip-themes db check >/dev/null 2>&1; then
    break
  fi
  attempts=$((attempts + 1))
  sleep 2
done

if ! wp --path="$WP_ROOT" --skip-plugins --skip-themes db check >/dev/null 2>&1; then
  echo "❌ Database not reachable. Run npm run docker:up first."
  exit 1
fi

echo "📥 Importing: $DUMP_FILE"
wp --path="$WP_ROOT" --skip-plugins --skip-themes db import "$DUMP_FILE"

if [ -z "$FROM_URL" ]; then
  FROM_URL="$(wp --path="$WP_ROOT" --skip-plugins --skip-themes option get siteurl 2>/dev/null || true)"
  if [ -z "$FROM_URL" ]; then
    echo "⚠️ Could not detect source URL — pass from_url as second argument."
    exit 1
  fi
  echo "ℹ️ Detected source URL: $FROM_URL"
fi

FROM_URL="${FROM_URL%/}"
TO_URL="${TO_URL%/}"

echo "🔄 search-replace: $FROM_URL → $TO_URL"
wp --path="$WP_ROOT" --skip-plugins --skip-themes search-replace "$FROM_URL" "$TO_URL" --all-tables --precise
wp --path="$WP_ROOT" --skip-plugins --skip-themes search-replace \
  "$(echo "$FROM_URL" | sed 's|http://|https://|')" \
  "$TO_URL" --all-tables --precise 2>/dev/null || true

wp --path="$WP_ROOT" --skip-plugins --skip-themes cache flush 2>/dev/null || true
wp --path="$WP_ROOT" --skip-plugins --skip-themes rewrite flush

echo ""
echo "✅ Import complete."
echo "   Site: ${TO_URL}/"
echo ""
echo "   Tip: copy wp-content/uploads from live into the wp_data volume if media is missing."
