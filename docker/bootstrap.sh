#!/usr/bin/env bash
# =============================================================================
# docker/bootstrap.sh — First-run WordPress install + flexi-install
# Run via: npm run docker:bootstrap
# =============================================================================
set -euo pipefail

THEME_ROOT="/var/www/html/wp-content/themes/${THEME_SLUG:-matrix-starter}"
WP_ROOT="${WP_PATH:-/var/www/html}"

cd "$THEME_ROOT"

if [ -f .env.docker ]; then
  set -a
  # shellcheck disable=SC1091
  source .env.docker
  set +a
fi

export MATRIX_RUNTIME=docker
export WP_PATH="$WP_ROOT"
export WP_HOME="${WP_HOME:-http://localhost:8080/}"
export BASE_URL="${BASE_URL:-$WP_HOME}"

configure_github_auth() {
  local token="${GH_TOKEN:-${GITHUB_TOKEN:-}}"
  if [ -z "$token" ]; then
    return 0
  fi
  export GITHUB_TOKEN="$token"
  git config --global url."https://x-access-token:${token}@github.com/".insteadOf "https://github.com/" 2>/dev/null || true
  echo "✅ GitHub token configured for private plugin clones."
}

wait_for_wordpress() {
  local attempts=0
  local max=60

  echo "⏳ Waiting for WordPress files and database…"
  while [ $attempts -lt $max ]; do
    if [ -f "$WP_ROOT/wp-config.php" ]; then
      if wp --path="$WP_ROOT" --skip-plugins --skip-themes db check >/dev/null 2>&1; then
        echo "✅ Database reachable."
        return 0
      fi
    fi
    attempts=$((attempts + 1))
    sleep 2
  done

  echo "❌ Timed out waiting for WordPress / database."
  exit 1
}

install_wordpress_if_needed() {
  if wp --path="$WP_ROOT" --skip-plugins --skip-themes core is-installed >/dev/null 2>&1; then
    echo "✅ WordPress already installed."
    return 0
  fi

  local url="${WP_HOME%/}"
  local title="${WP_SITE_TITLE:-Matrix Starter}"
  local user="${WP_ADMIN_USER:-admin}"
  local pass="${WP_ADMIN_PASSWORD:-matrix2026}"
  local email="${WP_ADMIN_EMAIL:-devs@matrixinternet.ie}"

  echo ""
  echo "📦 Installing WordPress…"
  echo "   URL:   $url"
  echo "   Admin: $user"

  wp --path="$WP_ROOT" --skip-plugins --skip-themes core install \
    --url="$url" \
    --title="$title" \
    --admin_user="$user" \
    --admin_password="$pass" \
    --admin_email="$email" \
    --skip-email

  echo "✅ WordPress installed."
}

activate_theme() {
  local slug="${THEME_SLUG:-matrix-starter}"

  if [ ! -d "$WP_ROOT/wp-content/themes/$slug" ]; then
    echo "⚠️ Theme directory missing: wp-content/themes/$slug"
    return 1
  fi

  wp --path="$WP_ROOT" --skip-plugins --skip-themes theme activate "$slug"
  echo "✅ Theme activated: $slug"
}

run_flexi_install() {
  echo ""
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo "  Running flexi-install inside Docker"
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  set +e
  bash scripts/flexi-install.sh
  local rc=$?
  set -e
  if [ "$rc" -ne 0 ]; then
    echo "⚠️ flexi-install exited with code $rc (site may still be usable — check plugins)."
  fi
}

print_summary() {
  local url="${WP_HOME%/}"
  echo ""
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo "  Docker site ready"
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo "  Site:      $url"
  echo "  wp-admin:  ${url}/wp-admin/"
  echo "  Admin:     ${WP_ADMIN_USER:-admin} / ${WP_ADMIN_PASSWORD:-matrix2026}"
  echo "  Theme dev: npm run dev  (on host, from theme folder)"
  echo ""
}

configure_github_auth
wait_for_wordpress
install_wordpress_if_needed
activate_theme
run_flexi_install
print_summary
