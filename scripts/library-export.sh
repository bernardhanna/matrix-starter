#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
THEME_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
WP_CONTENT_DIR="$(cd "$THEME_DIR/../../.." && pwd)"
LIBRARY_DIR="${MATRIX_LIBRARY_DIR:-$WP_CONTENT_DIR/matrix-component-library}"
EXPORT_SCRIPT="$LIBRARY_DIR/scripts/export-section.php"

LAYOUT=""
SKIP_SCREENSHOT=0

while [[ $# -gt 0 ]]; do
  case "$1" in
    --layout=*) LAYOUT="${1#*=}"; shift ;;
    --layout) LAYOUT="$2"; shift 2 ;;
    --no-screenshot) SKIP_SCREENSHOT=1; shift ;;
    *) echo "Unknown option: $1"; exit 1 ;;
  esac
done

if [[ -z "$LAYOUT" ]]; then
  echo "Usage: library-export.sh --layout=content_029 [--no-screenshot]"
  exit 1
fi

if [[ ! -f "$EXPORT_SCRIPT" ]]; then
  echo "Component library not found at $LIBRARY_DIR — run npm run library:sync first."
  exit 1
fi

echo "Validating theme block (a11y conventions)..."
if ! (cd "$THEME_DIR/mcp-server" && node dist/cli.js validate-a11y-conventions --layout="$LAYOUT" >/dev/null); then
  echo "Warning: theme a11y conventions check failed for $LAYOUT — fix before exporting."
  exit 1
fi

PREVIEW_ARG=()
TMP_PREVIEW=""

if [[ "$SKIP_SCREENSHOT" -eq 0 ]]; then
  TMP_PREVIEW="$(mktemp /tmp/matrix-preview-XXXXXX.png)"
  if node "$SCRIPT_DIR/capture-section-preview.js" --layout="$LAYOUT" --out="$TMP_PREVIEW" 2>/dev/null; then
    PREVIEW_ARG=(--preview "$TMP_PREVIEW")
    echo "Captured preview screenshot."
  else
    echo "Screenshot skipped (is BASE_URL set and block on /flexi/?)."
  fi
fi

php "$EXPORT_SCRIPT" \
  --layout="$LAYOUT" \
  --theme-root="$THEME_DIR" \
  "${PREVIEW_ARG[@]}"

rm -f "$TMP_PREVIEW"

echo "Export complete. Commit from: $LIBRARY_DIR"
