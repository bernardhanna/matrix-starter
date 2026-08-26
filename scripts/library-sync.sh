#!/usr/bin/env bash
set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
WP_CONTENT_DIR="$(cd "$SCRIPT_DIR/../../.." && pwd)"
LIBRARY_DIR="${MATRIX_LIBRARY_DIR:-$WP_CONTENT_DIR/matrix-component-library}"
REPO_URL="${MATRIX_COMPONENTS_REPO:-https://github.com/Matrix-Internet/matrix-component-library.git}"

if [ -d "$LIBRARY_DIR/.git" ]; then
  git -C "$LIBRARY_DIR" pull --ff-only
else
  mkdir -p "$(dirname "$LIBRARY_DIR")"
  git clone --depth 1 "$REPO_URL" "$LIBRARY_DIR"
fi

echo "Component library synced to $LIBRARY_DIR"
