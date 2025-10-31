#!/bin/bash

echo ""
echo "🌐 wpFlexiTheme Installer"
echo "========================="
echo ""

# Exit on error
set -e

# Load .env
if [ -f .env ]; then
  export $(grep -v '^#' .env | xargs)
else
  echo "❌ .env file not found!"
  exit 1
fi

# Locate WordPress root
SCRIPT_DIR=$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)
WP_ROOT=$(realpath "$SCRIPT_DIR/../../../..")

# Define plugin path
PLUGIN_DIR="$WP_ROOT/wp-content/plugins/matrix-component-importer"
GITHUB_URL="https://github.com/bernardhanna/matrix-component-importer.git"

# Clone the plugin
if [ ! -d "$PLUGIN_DIR" ]; then
  echo "📦 Cloning Matrix Component Importer..."
  git clone "$GITHUB_URL" "$PLUGIN_DIR"
else
  echo "✅ Matrix Component Importer already exists."
fi

# Final output
echo ""
echo "🎉 Setup complete!"
echo "- Plugin cloned to: $PLUGIN_DIR"
echo "- Please activate it via the WordPress admin panel."
echo ""
