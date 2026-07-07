#!/usr/bin/env bash
# Delegate to matrix-support-orchestrator (standalone repo).
# Set MATRIX_SUPPORT_HOME or clone next to your Local site folder.
set -euo pipefail

THEME_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

resolve_orchestrator() {
  if [ -n "${MATRIX_SUPPORT_HOME:-}" ] && [ -x "${MATRIX_SUPPORT_HOME}/bin/matrix-support" ]; then
    echo "${MATRIX_SUPPORT_HOME}"
    return 0
  fi
  local sibling="${THEME_ROOT}/../../../../../matrix-support-orchestrator"
  if [ -x "${sibling}/bin/matrix-support" ]; then
    echo "$(cd "$sibling" && pwd)"
    return 0
  fi
  local embedded="${THEME_ROOT}/support-orchestrator"
  if [ -x "${embedded}/bin/matrix-support" ]; then
    echo "$embedded"
    return 0
  fi
  return 1
}

# When sourced, expose ORCHESTRATOR_ROOT for other scripts.
if [[ "${BASH_SOURCE[0]}" != "${0}" ]]; then
  ORCHESTRATOR_ROOT="$(resolve_orchestrator)" || ORCHESTRATOR_ROOT=""
  return 0 2>/dev/null || exit 1
fi

ORCH="$(resolve_orchestrator)" || {
  echo "❌ matrix-support-orchestrator not found." >&2
  echo "   Clone: git clone git@github.com:Matrix-Internet/matrix-support-orchestrator.git" >&2
  echo "   Then: export MATRIX_SUPPORT_HOME=/path/to/matrix-support-orchestrator" >&2
  exit 1
}

export MATRIX_SUPPORT_HOME="$ORCH"
exec "${ORCH}/bin/matrix-support" "$@"
