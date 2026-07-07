#!/usr/bin/env bash
# =============================================================================
# docker-ensure-github.sh — Ensure GH_TOKEN before docker bootstrap (runs on host)
#
# Order:
#   1. Use GH_TOKEN / GITHUB_TOKEN already in .env.docker
#   2. Use `gh auth token` from host GitHub CLI
#   3. Interactive prompt (TTY only) — optionally save to .env.docker
#   4. Non-interactive: warn and continue (WP.org plugins only)
# =============================================================================
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
THEME_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
ENV_FILE="$THEME_ROOT/.env.docker"

cd "$THEME_ROOT"

if [ ! -f "$ENV_FILE" ]; then
  cp .env.docker.example "$ENV_FILE"
fi

# shellcheck disable=SC1091
source "$ENV_FILE"

write_gh_token() {
  local token="$1"
  local tmp="${ENV_FILE}.tmp.$$"
  grep -v '^GH_TOKEN=' "$ENV_FILE" > "$tmp" || true
  printf 'GH_TOKEN=%s\n' "$token" >> "$tmp"
  mv "$tmp" "$ENV_FILE"
}

has_github_token() {
  [ -n "${GH_TOKEN:-}" ] || [ -n "${GITHUB_TOKEN:-}" ]
}

if has_github_token; then
  echo "✅ GitHub token already configured in .env.docker"
  exit 0
fi

if command -v gh >/dev/null 2>&1 && gh auth status >/dev/null 2>&1; then
  token="$(gh auth token 2>/dev/null || true)"
  if [ -n "$token" ]; then
    write_gh_token "$token"
    echo "✅ Using GitHub token from host \`gh auth login\` (saved to .env.docker)"
    exit 0
  fi
fi

if [ ! -t 0 ]; then
  echo ""
  echo "ℹ️  No GitHub token (non-interactive run)."
  echo "   Private Matrix-Internet plugins (ACF Pro, etc.) will be skipped."
  echo "   Add GH_TOKEN to .env.docker or set the GH_TOKEN repo secret for CI."
  echo ""
  exit 0
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  GitHub access for Matrix private plugins"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "  matrix-starter needs private repos from Matrix-Internet (ACF Pro,"
echo "  UpdraftPlus, etc.). Without a token the site will error until ACF"
echo "  is installed manually."
echo ""
echo "  Options:"
echo "    [1] Paste a GitHub personal access token (repo read access)"
echo "    [2] Run \`gh auth login\` on this machine, then re-run bootstrap"
echo "    [3] Skip for now"
echo ""
read -r -p "Choice [1/2/3] (default 1): " choice
choice="${choice:-1}"

case "$choice" in
  1)
    read -r -s -p "GitHub token (input hidden): " token
    echo ""
    if [ -z "$token" ]; then
      echo "⚠️  No token entered — continuing without private plugin clones."
      exit 0
    fi
    write_gh_token "$token"
    echo "✅ Saved GH_TOKEN to .env.docker (gitignored)"
    ;;
  2)
    echo ""
    echo "  Run:  gh auth login"
    echo "  Then: npm run docker:bootstrap"
    echo ""
    exit 1
    ;;
  3|*)
    echo "ℹ️  Skipping — add GH_TOKEN to .env.docker later and re-run bootstrap."
    ;;
esac
