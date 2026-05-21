#!/bin/bash

echo ""
echo "🌐 wpFlexiTheme Installer"
echo "========================="
echo ""

set -euo pipefail

# ---------------- Helpers ----------------
WP_TIMEOUT="${WP_TIMEOUT:-8}"  # seconds

run_wp() {
  # Usage: run_wp <args...>  (args after --path/--skip are auto-added)
  local cmd=(wp --path="$WP_ROOT" --skip-plugins --skip-themes "$@")
  # Run with a manual timeout that works on macOS (no coreutils `timeout` required)
  local wp_pid waiter_pid status
  set +e
  "${cmd[@]}" &
  wp_pid=$!
  (
    sleep "$WP_TIMEOUT"
    if kill -0 "$wp_pid" 2>/dev/null; then
      echo "⏳ WP-CLI timed out after ${WP_TIMEOUT}s (cmd: ${cmd[*]})"
      kill -9 "$wp_pid" 2>/dev/null
    fi
  ) &
  waiter_pid=$!
  wait "$wp_pid"
  status=$?
  kill "$waiter_pid" 2>/dev/null || true
  set -e
  return $status
}

# --------------- Flags -------------------
FORCE_ACTIVATE="no"
while [[ $# -gt 0 ]]; do
  case "$1" in
    --force-activate) FORCE_ACTIVATE="yes"; shift ;;
    *) shift ;;
  esac
done

# --------------- Env ---------------------
if [ -f .env ]; then
  set -a
  # shellcheck disable=SC1091
  source .env
  set +a
else
  echo "ℹ️ .env not found — proceeding with default path detection."
fi

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
if [ -n "${WP_PATH:-}" ]; then
  WP_ROOT="$(realpath "$WP_PATH")"
else
  WP_ROOT="$(realpath "$SCRIPT_DIR/../../../..")"
fi
echo "📁 Using WordPress path: $WP_ROOT"

THEME_DIR="$(realpath "$SCRIPT_DIR/..")"
THEME_SLUG="$(basename "$THEME_DIR")"

PLUGINS_DIR="$WP_ROOT/wp-content/plugins"
mkdir -p "$PLUGINS_DIR"

COMP_IMPORTER_DIR="$PLUGINS_DIR/matrix-component-importer"
COMP_IMPORTER_REPO="https://github.com/bernardhanna/matrix-component-importer.git"
SITEMAP_DIR="$PLUGINS_DIR/matrix-sitemap-generator"
SITEMAP_REPO="https://github.com/bernardhanna/matrix-sitemap-generator.git"

PLUGINS_WP_ORG=(
  "classic-editor"
  "duplicate-page"
  "password-protected"
  "seo-by-rank-math"
  "prevent-browser-caching"
)

# --------------- Clone custom repos ---------------
if [ ! -d "$COMP_IMPORTER_DIR" ]; then
  echo "📦 Cloning Matrix Component Importer..."
  git clone "$COMP_IMPORTER_REPO" "$COMP_IMPORTER_DIR"
else
  echo "✅ Matrix Component Importer already exists."
fi

if [ ! -d "$SITEMAP_DIR" ]; then
  echo "📦 Cloning Matrix Sitemap Generator..."
  git clone "$SITEMAP_REPO" "$SITEMAP_DIR"
else
  echo "✅ Matrix Sitemap Generator already exists."
fi

# --------------- WP-CLI detection -----------------
CAN_ACTIVATE="no"
DB_OK="no"

if command -v wp >/dev/null 2>&1; then
  if [ -f "$WP_ROOT/wp-config.php" ] || [ -f "$WP_ROOT/wp-load.php" ]; then
    echo "🧪 Probing WordPress via WP-CLI (timeout: ${WP_TIMEOUT}s)…"
    if run_wp option get siteurl >/dev/null 2>&1; then
      DB_OK="yes"
      CAN_ACTIVATE="yes"
      echo "✅ WP reachable."
    else
      echo "⚠️ WP-CLI reached WP files but couldn't query the DB (or timed out)."
      echo "   • Make sure the site is STARTED in Local."
      echo "   • Run this script from Local → 'Open Site Shell'."
      echo "   • Or re-run with:  ./scripts/flexi-install.sh --force-activate"
      if [ "$FORCE_ACTIVATE" = "yes" ]; then
        CAN_ACTIVATE="yes"
        echo "👉 Proceeding due to --force-activate."
      else
        CAN_ACTIVATE="maybe"
      fi
    fi
  else
    echo "ℹ️ No wp-config.php/wp-load.php at: $WP_ROOT (skipping activation; items are installed)."
  fi
else
  echo "ℹ️ WP-CLI not found (skipping activation; items are installed)."
fi

# --------------- Theme + plugins via WP-CLI -------
if [ "$CAN_ACTIVATE" != "no" ]; then
  echo ""
  echo "🎨 Activating theme (if present): $THEME_SLUG"
  if [ ! -d "$WP_ROOT/wp-content/themes/$THEME_SLUG" ]; then
    echo "ℹ️ Theme directory not found in this WP install: $WP_ROOT/wp-content/themes/$THEME_SLUG"
    echo "   (Skipping theme activation; continue with plugins.)"
  else
    set +e
    run_wp theme activate "$THEME_SLUG"
    THEME_ACT=$?
    set -e
    if [ ${THEME_ACT:-1} -ne 0 ]; then
      echo "⚠️ Theme activation reported an error, but proceeding."
    else
      echo "✅ Theme activated: $THEME_SLUG"
    fi
  fi

  echo ""
  echo "🔌 Installing WordPress.org plugins via WP-CLI…"
  for PLUGIN_SLUG in "${PLUGINS_WP_ORG[@]}"; do
    set +e
    run_wp plugin is-installed "$PLUGIN_SLUG" >/dev/null 2>&1
    IS_INSTALLED=$?
    set -e
    if [ $IS_INSTALLED -eq 0 ]; then
      echo "✅ $PLUGIN_SLUG already installed."
    else
      echo "📦 Installing $PLUGIN_SLUG…"
      set +e
      run_wp plugin install "$PLUGIN_SLUG"
      INSTALL_EXIT=$?
      set -e
      if [ $INSTALL_EXIT -ne 0 ]; then
        echo "⚠️ Failed to install $PLUGIN_SLUG (continuing)."
      fi
    fi
  done

  echo ""
  echo "🔌 Activating plugins via WP-CLI…"
  set +e
  run_wp plugin activate matrix-component-importer
  ACT1=$?
  run_wp plugin activate matrix-sitemap-generator
  ACT2=$?
  run_wp plugin activate classic-editor duplicate-page password-protected seo-by-rank-math prevent-browser-caching
  ACT_WP_ORG=$?
  set -e

  echo ""
  echo "🔎 Status:"
  set +e
  run_wp theme status "$THEME_SLUG" 2>/dev/null | sed -n '1,12p' || true
  run_wp plugin status matrix-component-importer 2>/dev/null | sed -n '1,12p' || true
  run_wp plugin status matrix-sitemap-generator 2>/dev/null | sed -n '1,12p' || true
  for PLUGIN_SLUG in "${PLUGINS_WP_ORG[@]}"; do
    run_wp plugin status "$PLUGIN_SLUG" 2>/dev/null | sed -n '1,12p' || true
  done
  set -e

  if [ ${THEME_ACT:-0} -ne 0 ] || [ ${ACT1:-0} -ne 0 ] || [ ${ACT2:-0} -ne 0 ] || [ ${ACT_WP_ORG:-0} -ne 0 ]; then
    echo ""
    echo "⚠️ One or more activations reported errors."
    if [ "$DB_OK" != "yes" ]; then
      echo "   Likely DB connectivity. If using Local, START the site and run from 'Open Site Shell'."
    end_if=true
    fi
    echo "   Retry manually with:"
    echo "   wp --path=\"$WP_ROOT\" theme activate \"$THEME_SLUG\" --skip-plugins --skip-themes"
    echo "   wp --path=\"$WP_ROOT\" plugin activate matrix-component-importer matrix-sitemap-generator --skip-plugins --skip-themes"
    echo "   wp --path=\"$WP_ROOT\" plugin activate classic-editor duplicate-page password-protected seo-by-rank-math prevent-browser-caching --skip-plugins --skip-themes"
  else
    echo "✅ Plugins and theme activated."
  fi

else
  echo ""
  echo "ℹ️ Skipped activation (no WP-CLI or WordPress not detected)."
  echo "   To install + activate later via WP-CLI, run:"
  echo "   wp --path=\"$WP_ROOT\" plugin install classic-editor duplicate-page password-protected seo-by-rank-math prevent-browser-caching"
  echo "   wp --path=\"$WP_ROOT\" plugin activate classic-editor duplicate-page password-protected seo-by-rank-math prevent-browser-caching"
  echo ""
  echo "   And for theme + custom plugins:"
  echo "   wp --path=\"$WP_ROOT\" theme activate \"$THEME_SLUG\" --skip-plugins --skip-themes"
  echo "   wp --path=\"$WP_ROOT\" plugin activate matrix-component-importer matrix-sitemap-generator --skip-plugins --skip-themes"
fi

echo ""
echo "🎉 Setup complete!"
echo "- Theme (directory): $THEME_DIR"
echo "- Plugins cloned to:"
echo "   • $COMP_IMPORTER_DIR"
echo "   • $SITEMAP_DIR"
echo ""
