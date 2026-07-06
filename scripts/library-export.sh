#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
THEME_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
WP_CONTENT_DIR="$(cd "$THEME_DIR/../../.." && pwd)"
LIBRARY_DIR="${MATRIX_LIBRARY_DIR:-$WP_CONTENT_DIR/matrix-component-library}"
EXPORT_SCRIPT="$LIBRARY_DIR/scripts/export-component.php"

KIND="flexi"
SLUG=""
SKIP_SCREENSHOT=0
VARIANT=""

while [[ $# -gt 0 ]]; do
  case "$1" in
    --kind=*) KIND="${1#*=}"; shift ;;
    --kind) KIND="$2"; shift 2 ;;
    --layout=*) SLUG="${1#*=}"; KIND="${KIND:-flexi}"; shift ;;
    --layout) SLUG="$2"; KIND="${KIND:-flexi}"; shift 2 ;;
    --slug=*) SLUG="${1#*=}"; shift ;;
    --slug) SLUG="$2"; shift 2 ;;
    --variant=*) VARIANT="${1#*=}"; shift ;;
    --variant) VARIANT="$2"; shift 2 ;;
    --no-screenshot) SKIP_SCREENSHOT=1; shift ;;
    *) echo "Unknown option: $1"; exit 1 ;;
  esac
done

if [[ -z "$SLUG" ]]; then
  echo "Usage: library-export.sh --kind=flexi --layout=content_029"
  echo "       library-export.sh --kind=hero --slug=hero_001"
  echo "       library-export.sh --kind=theme-option --slug=footer"
  echo "       library-export.sh --kind=cpt --slug=faqs"
  echo "       library-export.sh --kind=taxonomy --slug=faq-categories"
  exit 1
fi

if [[ ! -f "$EXPORT_SCRIPT" ]]; then
  echo "Component library not found at $LIBRARY_DIR — run npm run library:sync first."
  exit 1
fi

if [[ "$KIND" == "flexi" ]]; then
  echo "Validating flexi template (a11y conventions)..."
  if ! (cd "$THEME_DIR/mcp-server" && node dist/cli.js validate-a11y-conventions --layout="$SLUG" >/dev/null); then
    echo "Theme a11y conventions check failed for $SLUG — fix before exporting."
    exit 1
  fi
fi

PREVIEW_ARG=()
TMP_PREVIEW=""

if [[ "$SKIP_SCREENSHOT" -eq 0 && "$KIND" == "flexi" ]]; then
  TMP_PREVIEW="$(mktemp /tmp/matrix-preview-XXXXXX.png)"
  if node "$SCRIPT_DIR/capture-section-preview.js" --layout="$SLUG" --out="$TMP_PREVIEW" 2>/dev/null; then
    PREVIEW_ARG=(--preview "$TMP_PREVIEW")
    echo "Captured preview screenshot."
  else
    echo "Screenshot skipped (set BASE_URL and add block to /flexi/)."
  fi
fi

EXPORT_ARGS=(--kind="$KIND" --slug="$SLUG" --theme-root="$THEME_DIR")
[[ -n "$VARIANT" ]] && EXPORT_ARGS+=(--variant="$VARIANT")
[[ ${#PREVIEW_ARG[@]} -gt 0 ]] && EXPORT_ARGS+=("${PREVIEW_ARG[@]}")

php "$EXPORT_SCRIPT" "${EXPORT_ARGS[@]}"

rm -f "$TMP_PREVIEW"

echo "Export complete. Commit from: $LIBRARY_DIR"
