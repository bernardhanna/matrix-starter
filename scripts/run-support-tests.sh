#!/usr/bin/env bash
set -euo pipefail
THEME_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
# shellcheck disable=SC1091
source "${THEME_ROOT}/scripts/matrix-support.sh"
if [ -z "${ORCHESTRATOR_ROOT:-}" ] || [ ! -f "${ORCHESTRATOR_ROOT}/tests/run-tests.sh" ]; then
  echo "❌ Orchestrator not found. Clone matrix-support-orchestrator and/or set MATRIX_SUPPORT_HOME." >&2
  exit 1
fi
exec bash "${ORCHESTRATOR_ROOT}/tests/run-tests.sh"
