#!/bin/bash
# =============================================================================
# matrix-plugins.sh — Matrix-Internet private plugin registry + clone helpers
# =============================================================================
# Sourced by flexi-install.sh (and optionally setup-matrix-starter.sh).
#
# All custom plugins live in the Matrix-Internet GitHub org (private). Developers
# need org access plus either:
#   • GitHub CLI:  gh auth login
#   • or HTTPS/SSH git credentials that can read Matrix-Internet/*
#
# Override in .env:
#   MATRIX_GITHUB_ORG=Matrix-Internet
#   MATRIX_GITHUB_FALLBACK_ORG=bernardhanna   # optional; used if repo not on org yet
# =============================================================================

MATRIX_GITHUB_ORG="${MATRIX_GITHUB_ORG:-Matrix-Internet}"
MATRIX_GITHUB_FALLBACK_ORG="${MATRIX_GITHUB_FALLBACK_ORG:-bernardhanna}"

# WordPress plugin folder | primary repo (MATRIX_GITHUB_ORG) | label | fallback repo (optional)
# On fallback org (MATRIX_GITHUB_FALLBACK_ORG), the 4th field is used when set; otherwise the primary repo name.
MATRIX_CUSTOM_PLUGINS=(
  "advanced-custom-fields-pro|acf|ACF Pro"
  "updraftplus|updraft-plus|UpdraftPlus"
  "matrix-component-importer|matrix-component-importer|Matrix Component Importer|matrix-component-importer"
  "matrix-sitemap-generator|matrix-sitemap-generator-plugin|Matrix Sitemap Generator|matrix-sitemap-generator"
  "matrix-content-gathering|matrix-content-gathering-plugin|Matrix Content Gathering|matrix-content-gathering"
  "matrix-qc-snags|matrix-qc-snags-plugin|Matrix QC Snag|matrix-qc-snags"
  "matrix-golive-preflight-checks|Matrix-Go-Live-Preflight-Checks|Matrix Go-Live Preflight Checks"
)

MATRIX_CUSTOM_PLUGIN_ACTIVATE=(
  "advanced-custom-fields-pro/acf.php"
  "updraftplus/updraftplus.php"
  "matrix-component-importer"
  "matrix-sitemap-generator"
  "matrix-content-gathering/matrix-content-export.php"
  "matrix-qc-snags/matrix-qc-snag.php"
  "matrix-golive-preflight-checks/matrix-golive-preflight-checks.php"
)

# Post go-live only (scripts/post-live-install.sh) — NOT flexi-install.
# id|github_repo|file_in_repo|mu_plugins_filename|label
MATRIX_POST_LIVE_MU_PLUGINS=(
  "plugin-checker|matrix-plugin-checker|matrix-plugin-checker.php|matrix-plugin-checker.php|Matrix Plugin Checker"
)

matrix_plugin_repo_url() {
  local org="$1"
  local repo_name="$2"
  printf 'https://github.com/%s/%s.git' "$org" "$repo_name"
}

matrix_github_auth_hint() {
  echo ""
  echo "  Private plugins are hosted under: https://github.com/${MATRIX_GITHUB_ORG}/"
  echo "  Authenticate once, then re-run:"
  echo "    gh auth login"
  echo ""
  echo "  Or use SSH remotes if your SSH key has org access."
  echo ""
}

ensure_matrix_github_access() {
  if command -v gh >/dev/null 2>&1; then
    if gh auth status >/dev/null 2>&1; then
      echo "✅ GitHub CLI authenticated — can clone ${MATRIX_GITHUB_ORG} private repos."
      return 0
    fi
    echo "⚠️  GitHub CLI found but not logged in."
    matrix_github_auth_hint
    return 1
  fi

  echo "ℹ️  GitHub CLI (gh) not found — using git clone over HTTPS/SSH."
  echo "   For private Matrix-Internet repos, install gh and run: gh auth login"
  return 0
}

_matrix_repo_exists() {
  local org="$1"
  local repo_name="$2"

  if command -v gh >/dev/null 2>&1 && gh auth status >/dev/null 2>&1; then
    gh repo view "${org}/${repo_name}" >/dev/null 2>&1
    return $?
  fi

  git ls-remote "$(matrix_plugin_repo_url "$org" "$repo_name")" HEAD >/dev/null 2>&1
}

_matrix_clone_from_org() {
  local org="$1"
  local repo_name="$2"
  local dir="$3"
  local spec="${org}/${repo_name}"

  if command -v gh >/dev/null 2>&1 && gh auth status >/dev/null 2>&1; then
    gh repo clone "$spec" "$dir"
    return $?
  fi

  git clone "$(matrix_plugin_repo_url "$org" "$repo_name")" "$dir"
}

clone_matrix_plugin() {
  local dir="$1"
  local repo_name="$2"
  local label="$3"
  local fallback_repo_name="${4:-$repo_name}"

  if [ -d "$dir/.git" ] || [ -d "$dir" ]; then
    echo "✅ ${label} already exists ($(basename "$dir"))."
    return 0
  fi

  local org tried_fallback=0 current_repo

  for org in "$MATRIX_GITHUB_ORG" "$MATRIX_GITHUB_FALLBACK_ORG"; do
    [ -n "$org" ] || continue
    if [ "$org" = "$MATRIX_GITHUB_ORG" ]; then
      current_repo="$repo_name"
    else
      tried_fallback=1
      current_repo="$fallback_repo_name"
    fi

    if ! _matrix_repo_exists "$org" "$current_repo"; then
      continue
    fi

    echo "📦 Cloning ${label} from ${org}/${current_repo}..."
    if _matrix_clone_from_org "$org" "$current_repo" "$dir"; then
      if [ "$tried_fallback" -eq 1 ] && [ "$org" = "$MATRIX_GITHUB_FALLBACK_ORG" ]; then
        echo "ℹ️  Cloned from fallback org ${org}/${current_repo}."
      fi
      return 0
    fi
  done

  echo "❌ Could not clone ${label} (${repo_name})."
  matrix_github_auth_hint
  return 1
}

clone_matrix_plugin_with_deps() {
  local dir="$1"
  local repo_name="$2"
  local label="$3"
  local fallback_repo_name="${4:-$repo_name}"

  clone_matrix_plugin "$dir" "$repo_name" "$label" "$fallback_repo_name"

  if [ -f "$dir/composer.json" ] && command -v composer >/dev/null 2>&1; then
    if [ ! -d "$dir/vendor" ]; then
      echo "📦 Running composer install in $(basename "$dir")..."
      (cd "$dir" && composer install --no-interaction --prefer-dist 2>/dev/null) || \
        echo "⚠️ composer install failed for $(basename "$dir") (optional; continue)."
    fi
  fi
}

# Clone a repo to a fresh directory (for one-off MU plugin file copies).
_matrix_clone_fresh() {
  local repo_name="$1"
  local dest_dir="$2"

  local org
  for org in "$MATRIX_GITHUB_ORG" "$MATRIX_GITHUB_FALLBACK_ORG"; do
    [ -n "$org" ] || continue
    if ! _matrix_repo_exists "$org" "$repo_name"; then
      continue
    fi
    rm -rf "$dest_dir"
    mkdir -p "$dest_dir"
    if _matrix_clone_from_org "$org" "$repo_name" "$dest_dir"; then
      return 0
    fi
  done
  return 1
}

# Copy a single file from a Matrix-Internet repo into wp-content/mu-plugins/.
install_matrix_mu_plugin() {
  local mu_dir="$1"
  local repo_name="$2"
  local repo_relative_file="$3"
  local mu_filename="$4"
  local label="$5"
  local dest="$mu_dir/$mu_filename"

  if [ -f "$dest" ]; then
    echo "✅ ${label} already at mu-plugins/${mu_filename}"
    return 0
  fi

  local tmpdir
  tmpdir="$(mktemp -d "${TMPDIR:-/tmp}/matrix-mu-XXXXXX")"

  echo "📦 Fetching ${label} from ${MATRIX_GITHUB_ORG}/${repo_name}..."
  if ! _matrix_clone_fresh "$repo_name" "$tmpdir"; then
    echo "❌ Could not clone ${repo_name}."
    matrix_github_auth_hint
    rm -rf "$tmpdir"
    return 1
  fi

  local source="$tmpdir/$repo_relative_file"
  if [ ! -f "$source" ]; then
    echo "❌ Expected file not found in repo: ${repo_relative_file}"
    rm -rf "$tmpdir"
    return 1
  fi

  cp "$source" "$dest"
  rm -rf "$tmpdir"
  echo "✅ Installed mu-plugins/${mu_filename}"
  return 0
}
