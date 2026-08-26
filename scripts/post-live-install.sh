#!/bin/bash
# =============================================================================
# post-live-install.sh — Post go-live / production tooling (NOT flexi bootstrap)
# =============================================================================
#
# Installs optional Matrix tools that should run on or after go-live — separate
# from npm run flexi:install (local/staging bootstrap).
#
# Run from the theme root:
#   npm run post-live:install
#   bash scripts/post-live-install.sh
#   bash scripts/post-live-install.sh --only=plugin-checker
#
# Requires: gh auth login (or git access to Matrix-Internet private repos)
# Registry: scripts/matrix-plugins.sh (MATRIX_POST_LIVE_MU_PLUGINS)
#
# See scripts/README.md
# =============================================================================

set -euo pipefail

ONLY=""
while [[ $# -gt 0 ]]; do
  case "$1" in
    --only)
      ONLY="$2"
      shift 2
      ;;
    -h|--help)
      echo "Usage: post-live-install.sh [--only=plugin-checker]"
      echo ""
      echo "  Install post go-live MU plugins from Matrix-Internet (not flexi-install)."
      exit 0
      ;;
    --only=*)
      ONLY="${1#*=}"
      shift
      ;;
    *)
      echo "Unknown option: $1"
      exit 1
      ;;
  esac
done

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  Matrix Starter — post-live install"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "  Installs must-use plugins under wp-content/mu-plugins/."
echo "  Not part of flexi-install — run after go-live or on production."
echo ""

if [ -f .env ]; then
  set -a
  # shellcheck disable=SC1091
  source .env
  set +a
fi

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=matrix-plugins.sh
source "$SCRIPT_DIR/matrix-plugins.sh"

if [ -n "${WP_PATH:-}" ]; then
  WP_ROOT="$(realpath "$WP_PATH")"
else
  WP_ROOT="$(realpath "$SCRIPT_DIR/../../../..")"
fi

MU_PLUGINS_DIR="$WP_ROOT/wp-content/mu-plugins"
echo "📁 WordPress path: $WP_ROOT"
echo "📁 MU plugins:     $MU_PLUGINS_DIR"
echo ""

ensure_matrix_github_access || true

mkdir -p "$MU_PLUGINS_DIR"

# WordPress expects a silent index.php in mu-plugins on some hosts.
if [ ! -f "$MU_PLUGINS_DIR/index.php" ]; then
  printf '%s\n' '<?php // Silence is golden.' > "$MU_PLUGINS_DIR/index.php"
fi

installed=0
skipped=0
failed=0

for entry in "${MATRIX_POST_LIVE_MU_PLUGINS[@]}"; do
  IFS='|' read -r id repo_name repo_file mu_file label <<< "$entry"

  if [ -n "$ONLY" ] && [ "$id" != "$ONLY" ]; then
    continue
  fi

  echo "▶ ${label}"
  if install_matrix_mu_plugin "$MU_PLUGINS_DIR" "$repo_name" "$repo_file" "$mu_file" "$label"; then
    installed=$((installed + 1))
  else
    failed=$((failed + 1))
  fi
  echo ""
done

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  Post-live install complete"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "  Installed: ${installed}   Failed: ${failed}"
echo ""
echo "  Matrix Plugin Checker:"
echo "    • WP Admin → Tools → Plugin Checker"
echo "    • Click Run Checker and wait for the plugin list"
echo "    • Red rows = not updated in 6+ months"
echo ""

if [ "$failed" -gt 0 ]; then
  exit 1
fi
