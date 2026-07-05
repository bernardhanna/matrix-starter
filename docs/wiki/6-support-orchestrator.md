# 6. Support orchestrator (AI support pipeline)

Internal Matrix tool for client support tickets — **not part of the theme**. Lives in a separate repo:

**https://github.com/Matrix-Internet/matrix-support-orchestrator**

**Team setup guide (live clone, VPS, Windows/WSL):** orchestrator repo [`docs/TEAM-SETUP.md`](https://github.com/Matrix-Internet/matrix-support-orchestrator/blob/main/docs/TEAM-SETUP.md)

Use it to: submit tickets, spin up **isolated Docker test sites**, hand off to **Cursor + MCP**, open **PRs** gated by **QC PR Gate**, and (later) run **DB change sets** with dry-run → test site → human approval.

---

## Architecture

| Component | Where |
|-----------|--------|
| Orchestrator | `matrix-support-orchestrator` repo (Matrix staff only) |
| Theme MCP | `mcp-server/` in each client theme repo |
| QC Snags bridge | `matrix-qc-snags` plugin → **Support pipeline** settings |
| Test sites | Per-job Docker (unique port + compose project) |
| Production | Never touched by agent — human merge + Plesk deploy only |

Full design: orchestrator repo `docs/ARCHITECTURE.md`.  
**Rollout for the whole team:** `docs/TEAM-SETUP.md` (shared VPS, live production clone, Windows/WSL).

---

## Live site ticket (example)

Ticket on **https://wpflexitheme.com** (checkout broken):

1. Orchestrator clones **production** DB + page media into Docker (read-only SSH export from live).
2. URLs in DB change from `wpflexitheme.com` → sandbox URL (see below).
3. Agent fixes on git branch; PR merged; human deploys to live.

**Sandbox URL (where devs test — not the live domain):**

| Setup | URL |
|-------|-----|
| Local Mac/WSL | `http://localhost:8090/` (port varies per job) |
| Shared VPS + Caddy | `https://<job-id>.support.yourdomain.com/` |

Check: `matrix-support job show <job-id>`

---

## One-time setup (local)

```bash
git clone git@github.com:Matrix-Internet/matrix-support-orchestrator.git
cd matrix-support-orchestrator
./scripts/setup-local.sh
```

Set webhook token (use the same value in WordPress):

```bash
export MATRIX_SUPPORT_WEBHOOK_TOKEN="$(openssl rand -hex 24)"
matrix-support webhook serve --port 8787
```

**WordPress:** QC Snags → **Support pipeline**

| Field | Example |
|-------|---------|
| URL | `http://127.0.0.1:8787` |
| Token | (same as `MATRIX_SUPPORT_WEBHOOK_TOKEN`) |
| Client slug | `matrix-starter-demo` |

Point `MATRIX_SUPPORT_HOME` at the orchestrator clone, or place it at  
`Local Sites/<site>/matrix-support-orchestrator` (auto-detected from theme).

---

## From the theme folder (npm shortcuts)

```bash
npm run support:submit -- --client matrix-starter-demo --title "..." --body "..." --allow code
npm run support:sandbox:up -- <job-id>
npm run support:agent:prepare -- <job-id>
npm run support:agent:open -- <job-id>
npm run support:agent:pr -- <job-id>
npm run support:webhook    # start webhook server
npm run support:test       # run orchestrator test suite
```

Scripts delegate to `scripts/matrix-support.sh` → standalone orchestrator repo.

---

## Typical workflow

### A — From QC Snag (staging site)

1. Reviewer logs snag in QC Mode.
2. **Send to support pipeline** (snag edit or list row action).
3. On orchestrator host: `matrix-support agent status <job-id>`
4. `matrix-support agent open <job-id>` → fix in Cursor using sandbox URL from `AGENT_BRIEF.md`.
   - Theme MCP: `mcp-server/` tools for flexi blocks, build, tests
   - GitHub MCP: Docker MCP Toolkit → connect Cursor, or use job `cursor-mcp.json`
5. `matrix-support agent pr <job-id>` → PR opens → **QC PR Gate** runs on GitHub.

**Staging clone (QC snags):** enable **Auto clone staging** in QC Snags → Support pipeline. Orchestrator clones from `environments.staging` (or legacy `staging_db_dump`) + page media, then optionally runs agent prepare in the background.

**Live support:** webhook `source: live_support` defaults to `environments.production` clone (SSH `wp db export`). Override with `clone_source` in the payload.

```bash
matrix-support sandbox clone <job-id> --source production --media page
matrix-support watch <job-id>
```

**Hosted HTTPS (VPS):** set `MATRIX_SUPPORT_SANDBOX_EXPOSURE=hosted` in orchestrator `.env.local`, run Caddy (`docker/caddy-compose.yml`). Each job gets `https://<job-id>.your-domain/`. Sandboxes load `docker/mu-plugins/matrix-support-sandbox-guard.php` (noindex, robots block).

6. Human reviews PR, merges, `matrix-support deploy run <job-id>` (when `deploy.method` is `ssh_git_pull`).

### B — Manual CLI ticket

```bash
matrix-support submit --client acme-corp --title "Footer link 404" --body "..." --allow code
matrix-support sandbox clone <job-id> --source staging
matrix-support agent prepare <job-id>
matrix-support agent open <job-id>
# … fix …
matrix-support agent pr <job-id>
```

### C — DB / content fix (test site only)

```bash
matrix-support db dry-run <job-id> changeset.sh
matrix-support db summary <job-id>    # human review
matrix-support db apply <job-id> changeset.sh   # Docker test site ONLY
```

Never apply to live without human approval and staging verification.

---

## Client registry (`clients.json`)

Per client entry in the orchestrator repo (gitignored locally):

```json
{
  "slug": "client-slug",
  "repo_path": "/absolute/path/to/wp-content/themes/client-theme",
  "github_repo": "Matrix-Internet/client-theme",
  "base_branch": "main",
  "theme_slug": "client-theme",
  "production_url": "https://www.client.com",
  "environments": {
    "staging": { "url": "...", "ssh": "user@host", "wp_path": "...", "db_dump_path": "...", "uploads_path": "..." },
    "production": { "url": "...", "ssh": "user@host", "wp_path": "..." }
  },
  "deploy": { "method": "manual", "host": "", "path": "", "branch": "main" }
}
```

Docker sandboxes run from `repo_path` (must contain `docker-compose.yml`).

---

## Two agent paths in QC Snags

| Button | Path |
|--------|------|
| **Send to agent** | Cursor Cloud Agents API (no local Docker) |
| **Send to support pipeline** | Orchestrator → Docker sandbox → local Cursor + MCP |

Use the **support pipeline** when you need live DB clone, strict gates, or internal security boundaries.

---

## Related

- [Team setup guide](https://github.com/Matrix-Internet/matrix-support-orchestrator/blob/main/docs/TEAM-SETUP.md) — VPS, Windows/WSL, per-client checklist
- [5. Docker environments](5-docker-environments.md) — underlying Docker stack
- [3. Daily development flow](3-daily-flow-for-development.md) — branches and PRs
- [Tests](Tests.md) — QC PR gate in `.github/workflows/qc-pr.yml`
