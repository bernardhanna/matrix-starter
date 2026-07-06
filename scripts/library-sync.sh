#!/usr/bin/env bash
set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
THEME_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
COMPONENTS_DIR="$THEME_DIR/library/matrix-starter-components"
REPO_URL="${MATRIX_COMPONENTS_REPO:-https://github.com/bernardhanna/matrix-starter-components.git}"
mkdir -p "$THEME_DIR/library"
if [ -d "$COMPONENTS_DIR/.git" ]; then git -C "$COMPONENTS_DIR" pull --ff-only; else git clone --depth 1 "$REPO_URL" "$COMPONENTS_DIR"; fi
