# 5. Docker environments

Run a full Matrix Starter WordPress site in **Docker** — for local dev, staging previews, CI, or cloning a live site. This is **optional**; [Local by Flywheel](2-local-setup-and-build-tools.md) still works the same way.

**Full reference:** this page. **Scripts:** `docker-compose.yml`, `docker/bootstrap.sh`, `scripts/docker-ensure-github.sh`.

---

## When to use Docker vs Local

| Use Docker when… | Use Local when… |
|------------------|-----------------|
| You want one-command spin-up without creating a Local site | You prefer the Local UI (SSL, logs, one-click admin) |
| You need the same stack on a VPS for staging | You’re already set up on Local and it works |
| CI needs a real WordPress instance | You don’t have Docker Desktop installed |
| You’re cloning a live DB into a dev environment | — |

Both paths use the same **`flexi-install`** plugin stack and theme.

---

## Prerequisites

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (or Docker Engine + Compose v2)
- **Node.js** + `npm install` in the theme folder (for `npm run dev` / `npm run build` on the host)
- **GitHub access** to [Matrix-Internet](https://github.com/Matrix-Internet/) private repos — via `gh auth login` or a personal access token (see below)

---

## First-time setup (local)

Run everything from the **theme root** (`wp-content/themes/matrix-starter/` or your project theme folder).

### 1. Install theme dependencies

```bash
composer install
npm install
```

### 2. Start the containers

```bash
npm run docker:up
```

This creates `.env.docker` from `.env.docker.example` if missing, then starts:

| Service | Role |
|---------|------|
| `db` | MariaDB 11 |
| `wordpress` | WordPress + PHP 8.3 on **http://localhost:8080** |
| `wpcli` | WP-CLI (used only when you run bootstrap/import) |

First run downloads images and may take a few minutes.

### 3. Bootstrap WordPress + Matrix stack

```bash
npm run docker:bootstrap
```

**What this does (in order):**

1. **GitHub auth** (`scripts/docker-ensure-github.sh` on your Mac)
   - Uses `GH_TOKEN` from `.env.docker` if set
   - Else uses `gh auth login` token automatically
   - Else **prompts** you to paste a PAT (saved to `.env.docker`, gitignored)
   - In CI: no prompt — set `GH_TOKEN` secret
2. **Wait** for database + WordPress files
3. **`wp core install`** (skipped if already installed)
4. **Activate** your theme
5. **`flexi-install`** — clone Matrix plugins, install WP.org plugins, Password Protected defaults

### 4. Finish manually (same as Local)

| Task | Where |
|------|--------|
| ACF Pro license | WP Admin → ACF |
| UpdraftPlus license | Settings → UpdraftPlus |
| Build theme assets | `npm run build` (or `npm run dev` while developing) |
| WP Mail SMTP OAuth | WP Admin → WP Mail SMTP → Authorize |

### 5. Open the site

- **Front end:** http://localhost:8080/
- **Admin:** http://localhost:8080/wp-admin/
- **Default login:** `admin` / `matrix2026` (change in `.env.docker`)

---

## Daily development

```bash
# Start stack (if stopped)
npm run docker:up

# Watch CSS/JS on your Mac (theme folder bind-mounted into container)
npm run dev

# View logs
npm run docker:logs

# Stop (keeps database + uploads)
npm run docker:down
```

Edit theme files on the host — changes appear in the container immediately (bind mount).

---

## npm scripts reference

| Script | What it does |
|--------|----------------|
| `npm run docker:up` | Start DB + WordPress |
| `npm run docker:bootstrap` | GitHub auth + WP install + flexi-install |
| `npm run docker:down` | Stop containers (data kept) |
| `npm run docker:down:clean` | Stop **and delete** all WP/DB volumes (full reset) |
| `npm run docker:logs` | Follow container logs |
| `npm run docker:import` | Import a `.sql` dump (live → local) |
| `npm run docker:mail` | Start Mailpit (web UI :8025, SMTP :1025) |

---

## GitHub authentication (private plugins)

matrix-starter **requires ACF Pro** (private repo). Without GitHub auth, the front end returns **HTTP 500** until ACF is installed.

| Method | How |
|--------|-----|
| **Recommended** | `gh auth login` on your Mac, then `npm run docker:bootstrap` — token auto-saved to `.env.docker` |
| **Prompt** | Run bootstrap in a terminal; choose “paste token” when asked |
| **Manual** | Add `GH_TOKEN=ghp_...` to `.env.docker` |
| **CI / server** | Set `GH_TOKEN` env or GitHub Actions secret — no prompt |

Re-run bootstrap after adding a token:

```bash
npm run docker:bootstrap
```

---

## Clone a live site into Docker

Use this to debug production content locally or spin up a staging mirror.

1. **Export from live** — UpdraftPlus backup or `wp db export`, plus `wp-content/uploads/`.
2. **Start stack:** `npm run docker:up` (bootstrap once if the DB is empty).
3. **Put the dump** in `docker/imports/` (gitignored — never commit production data).
4. **Import:**

   ```bash
   npm run docker:import -- ./docker/imports/live-dump.sql https://www.live-site.com http://localhost:8080
   ```

5. **Copy uploads** into the WordPress volume:

   ```bash
   docker cp ./uploads-from-live/. matrix-starter-wordpress-1:/var/www/html/wp-content/uploads/
   ```

   (Container name may differ — check `docker ps`.)

6. **Theme** is already mounted from your Git checkout.

**Caveats:** sanitize PII before sharing dumps; re-enter licenses; OAuth redirect URLs differ on local.

---

## Staging on a VPS

1. Clone the project repo on the server.
2. Copy `.env.docker.example` → `.env.docker`; set `WP_HOME`, `BASE_URL`, `WP_PORT`, and `GH_TOKEN`.
3. `npm run docker:up && npm run docker:bootstrap`
4. Put **Caddy** or **Traefik** in front for HTTPS.
5. Staging lock: flexi-install sets **Password Protected** (`matrix` + current year).

---

## How it fits with Local and Plesk

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│  Docker local   │     │  Local (Flywheel)│     │  Plesk prod     │
│  npm run docker │     │  flexi-install   │     │  Git deploy     │
└────────┬────────┘     └────────┬────────┘     └────────┬────────┘
         │                       │                       │
         └───────────────────────┴───────────────────────┘
                                 │
                    same theme + flexi-install stack
```

- **`.env.docker`** — Docker only (gitignored)
- **`.env`** — Local / WP_PATH / Playwright URLs (gitignored)
- Do **not** commit `wp-config.php` with machine-specific DB sockets

---

## Troubleshooting

| Problem | Fix |
|---------|-----|
| `Cannot connect to Docker daemon` | Start Docker Desktop |
| Port 8080 in use | Set `WP_PORT=8081` in `.env.docker` and update `WP_HOME` |
| Bootstrap times out | `npm run docker:logs` — first WP image download is slow |
| HTTP 500 on homepage | ACF Pro not cloned — fix GitHub auth, re-run bootstrap |
| Plugin clone fails | `GH_TOKEN` in `.env.docker` or `gh auth login` |
| Wrong URLs after import | Re-run `docker:import` with explicit live and local URLs |
| Start fresh | `npm run docker:down:clean` then `docker:up` + `docker:bootstrap` |

---

## CI

`.github/workflows/docker-smoke.yml` — starts compose, runs bootstrap, verifies WordPress responds. Set repo secret **`GH_TOKEN`** for full plugin install in CI.

---

## Related docs

- [4. flexi-install & environment](4-flexi-install-and-environment.md) — plugins, `.env`, Password Protected
- [2. Local setup](2-local-setup-and-build-tools.md) — non-Docker path
- `scripts/README.md` — flexi-install detail
