#!/bin/bash
set -euo pipefail

# Force use of nodenv shims (Node 21 + npm) for Plesk deploys
export PATH="/var/www/vhosts/matrix-test.com/.nodenv/shims:$PATH"

echo "==== Plesk deploy script started: $(date) ===="
echo "Node path: $(which node || echo 'node not found')"
echo "Node version: $(node -v || echo 'node failed')"
echo "npm path: $(which npm || echo 'npm not found')"

# One-time dependency install (runs only if .deps_installed does NOT exist)
if [ ! -f .deps_installed ]; then
  echo "Deps marker not found, installing dependencies..."

  if command -v composer >/dev/null 2>&1; then
    echo "Running composer install..."
    composer install --no-dev --optimize-autoloader
  else
    echo "WARNING: composer not found, skipping PHP deps"
  fi

  if command -v npm >/dev/null 2>&1; then
    echo "Running npm ci --ignore-scripts..."
    npm ci --ignore-scripts
  else
    echo "ERROR: npm not found, cannot install JS deps"
    exit 1
  fi

  echo "Creating .deps_installed marker"
  touch .deps_installed
else
  echo ".deps_installed found, skipping dependency install"
fi

# Always build assets on deploy
if command -v npm >/dev/null 2>&1; then
  echo "Running npm run build..."
  npm run build
  echo "npm run build completed."
else
  echo "ERROR: npm not found, cannot run build"
  exit 1
fi

echo "==== Plesk deploy script finished OK ===="