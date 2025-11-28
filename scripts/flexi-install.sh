#!/bin/bash

echo ""
echo "🌐 wpFlexiTheme Installer"
echo "========================="
echo ""

set -e

# Load .env safely (supports spaces & quotes)
if [ -f .env ]; then
  set -a
  # shellcheck disable=SC1091
  source .env
  set +a
else
  echo "ℹ️ .env not found — proceeding with default path detection."
fi

# Locate WordPress root (use WP_PATH from .env if provided)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
if [ -n "${WP_PATH:-}" ]; then
  WP_ROOT="$(realpath "$WP_PATH")"
else
  WP_ROOT="$(realpath "$SCRIPT_DIR/../../../..")"
fi
echo "📁 Using WordPress path: $WP_ROOT"

# Theme (auto-detect from folder containing /scripts)
THEME_DIR="$(realpath "$SCRIPT_DIR/..")"
THEME_SLUG="$(basename "$THEME_DIR")"

# Ensure plugins dir exists before cloning
PLUGINS_DIR="$WP_ROOT/wp-content/plugins"
mkdir -p "$PLUGINS_DIR"

# Define plugin paths & repos
COMP_IMPORTER_DIR="$PLUGINS_DIR/matrix-component-importer"
COMP_IMPORTER_REPO="https://github.com/bernardhanna/matrix-component-importer.git"
SITEMAP_DIR="$PLUGINS_DIR/matrix-sitemap-generator"
SITEMAP_REPO="https://github.com/bernardhanna/matrix-sitemap-generator.git"

# Clone Matrix Component Importer
if [ ! -d "$COMP_IMPORTER_DIR" ]; then
  echo "📦 Cloning Matrix Component Importer..."
  git clone "$COMP_IMPORTER_REPO" "$COMP_IMPORTER_DIR"
else
  echo "✅ Matrix Component Importer already exists."
fi

# Clone Matrix Sitemap Generator
if [ ! -d "$SITEMAP_DIR" ]; then
  echo "📦 Cloning Matrix Sitemap Generator..."
  git clone "$SITEMAP_REPO" "$SITEMAP_DIR"
else
  echo "✅ Matrix Sitemap Generator already exists."
fi

# Try to activate theme & plugins if wp-cli + WordPress are available
CAN_ACTIVATE="no"
if command -v wp >/dev/null 2>&1; then
  if [ -f "$WP_ROOT/wp-config.php" ] || [ -f "$WP_ROOT/wp-load.php" ]; then
    if wp --path="$WP_ROOT" core is-installed --skip-plugins --skip-themes >/dev/null 2>&1; then
      CAN_ACTIVATE="yes"
    else
      echo "ℹ️ WP-CLI couldn't verify WordPress at: $WP_ROOT (skipping activation; items are installed)."
    fi
  else
    echo "ℹ️ No wp-config.php/wp-load.php at: $WP_ROOT (skipping activation; items are installed)."
  fi
else
  echo "ℹ️ WP-CLI not found (skipping activation; items are installed)."
fi

if [ "$CAN_ACTIVATE" = "yes" ]; then
  echo ""
  echo "🎨 Activating theme: $THEME_SLUG"
  if [ ! -d "$WP_ROOT/wp-content/themes/$THEME_SLUG" ]; then
    echo "ℹ️ Theme directory not found in this WP install: $WP_ROOT/wp-content/themes/$THEME_SLUG"
    echo "   (Skipping theme activation; continue with plugins.)"
  else
    set +e
    wp --path="$WP_ROOT" --skip-plugins --skip-themes theme activate "$THEME_SLUG"
    THEME_ACT=$?
    set -e
    if [ $THEME_ACT -ne 0 ]; then
      echo "⚠️ Theme activation reported an error, but proceeding."
    else
      echo "✅ Theme activated: $THEME_SLUG"
    fi
  fi

  echo ""
  echo "🔌 Activating plugins via WP-CLI…"
  set +e
  wp --path="$WP_ROOT" --skip-plugins --skip-themes plugin activate matrix-component-importer
  ACT1=$?
  wp --path="$WP_ROOT" --skip-plugins --skip-themes plugin activate matrix-sitemap-generator
  ACT2=$?
  set -e

  echo ""
  echo "🔎 Status:"
  wp --path="$WP_ROOT" --skip-plugins --skip-themes theme status "$THEME_SLUG" 2>/dev/null | sed -n '1,12p' || true
  wp --path="$WP_ROOT" --skip-plugins --skip-themes plugin status matrix-component-importer 2>/dev/null | sed -n '1,12p' || true
  wp --path="$WP_ROOT" --skip-plugins --skip-themes plugin status matrix-sitemap-generator 2>/dev/null | sed -n '1,12p' || true

  if [ ${THEME_ACT:-0} -ne 0 ] || [ $ACT1 -ne 0 ] || [ $ACT2 -ne 0 ]; then
    echo ""
    echo "⚠️ One or more activations reported errors, but installs completed."
    echo "   Try running from Local → 'Open Site Shell', then re-run:"
    echo "   wp --path=\"$WP_ROOT\" theme activate \"$THEME_SLUG\" --skip-plugins --skip-themes"
    echo "   wp --path=\"$WP_ROOT\" plugin activate matrix-component-importer matrix-sitemap-generator --skip-plugins --skip-themes"
  else
    echo "✅ Plugins and theme activated."
  fi
else
  echo ""
  echo "ℹ️ Skipped activation (no WP-CLI or WordPress not detected)."
  echo "   To activate later, run:"
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
