# Theme scripts

## Docker (containerized WordPress)

Alternative to Local — full WP stack via Docker Compose. **See [docs/wiki/5-docker-environments.md](../docs/wiki/5-docker-environments.md)** for the complete guide.

```bash
npm run docker:up          # start MariaDB + WordPress (:8080)
npm run docker:bootstrap   # GitHub auth prompt + WP install + flexi-install
npm run docker:down        # stop (keep data)
```

Bootstrap runs `scripts/docker-ensure-github.sh` on the host first (uses `gh auth login` or prompts for a token). Same plugin stack as `flexi-install` below.

---

## `flexi-install.sh` — project bootstrap installer

**Run from the theme root** (or Local’s “Open Site Shell” with the site running):

```bash
npm run flexi:install
# or
bash scripts/flexi-install.sh
# optional: bash scripts/flexi-install.sh --force-activate
```

### What it is

One-shot setup for a **new Matrix Starter site** (Local, staging, or similar). It does **not** build theme assets (`npm run build` is separate). It prepares WordPress with the theme and the standard Matrix plugin stack.

### What it does

| Step | Action |
|------|--------|
| 1 | Resolves WordPress root (`WP_PATH` in theme `.env`, or auto-detect four levels up from `scripts/`) |
| 2 | **Clones** private Matrix plugins from GitHub into `wp-content/plugins/` (skips if folder already exists) |
| 3 | **Installs** common plugins from WordPress.org via WP-CLI |
| 4 | **Activates** the active theme directory + all plugins (when WP-CLI can reach the database) |
| 5 | **Configures Password Protected** for local/staging (see below) |

### Password Protected (auto-configured)

When the database is reachable, the script applies these defaults:

| Setting | Value |
|---------|--------|
| Whole-site protection | **On** |
| Allow administrators | **On** (logged-in admins skip the gate) |
| Remember me | **On** |
| Remember for | **21 days** |
| Password | `matrix` + current year (e.g. `matrix2026`) — set in the script; staging/crawler gate only |

Optional override in `.env`: `MATRIX_SITE_PASSWORD=matrix2026`

### WP Mail SMTP (auto-configured when `.env` is filled in)

Copy `.env.example` → `.env` and set `MATRIX_SMTP_GOOGLE_CLIENT_SECRET` (and other vars as needed).

| Setting | Value |
|---------|--------|
| From email | `devs@matrixinternet.ie` (force on) |
| From name | Site title, or `MATRIX_SMTP_FROM_NAME` / `MATRIX_PROJECT_NAME` (force on) |
| Mailer | Google / Gmail (manual OAuth app) |
| Client ID | Default Matrix app ID, or `MATRIX_SMTP_GOOGLE_CLIENT_ID` |

In [Google Cloud Console](https://console.cloud.google.com/), add this **Authorized redirect URI** to the OAuth client:

`https://connect.wpmailsmtp.com/google/`

Then in WordPress: **WP Mail SMTP → Settings** → complete **Authorize** (not done by the install script).

```bash
MATRIX_SMTP_GOOGLE_CLIENT_SECRET=your-secret-here
```

### Plugins installed

All **Matrix custom plugins** are private repos under **[Matrix-Internet](https://github.com/Matrix-Internet/)**. The installer reads the registry in `scripts/matrix-plugins.sh`.

**Before first run** (one-time per machine):

```bash
gh auth login
```

That gives `flexi-install` access to clone private org repos. SSH git credentials also work if your key has org access.

Override in `.env` if needed:

```dotenv
MATRIX_GITHUB_ORG=Matrix-Internet
MATRIX_GITHUB_FALLBACK_ORG=bernardhanna   # optional; used when a repo is not on the org yet
```

**From GitHub (cloned):**

| Folder | Repository | Purpose |
|--------|------------|---------|
| `advanced-custom-fields-pro` | [Matrix-Internet/acf](https://github.com/Matrix-Internet/acf) | ACF Pro — required by theme and content tools |
| `updraftplus` | [updraft-plus](https://github.com/Matrix-Internet/updraft-plus) | UpdraftPlus Premium — backups (license in WP Admin after install) |
| `matrix-component-importer` | [matrix-component-importer](https://github.com/Matrix-Internet/matrix-component-importer) | Installs `wp-content/matrix-component-library/` locally; import sections into theme |
| `matrix-sitemap-generator` | [matrix-sitemap-generator-plugin](https://github.com/Matrix-Internet/matrix-sitemap-generator-plugin) (fallback: `bernardhanna/matrix-sitemap-generator`) | Slickplan import → pages, CPTs, and main menu |
| `matrix-content-gathering` | [matrix-content-gathering-plugin](https://github.com/Matrix-Internet/matrix-content-gathering-plugin) (fallback: `bernardhanna/matrix-content-gathering`) | Client content editing form, CSV import/export for ACF flexi |
| `matrix-qc-snags` | [matrix-qc-snags-plugin](https://github.com/Matrix-Internet/matrix-qc-snags-plugin) (fallback: `bernardhanna/matrix-qc-snags`) | In-site QC snagging overlay + Cursor agent PR bridge |
| `matrix-golive-preflight-checks` | [Matrix-Go-Live-Preflight-Checks](https://github.com/Matrix-Internet/Matrix-Go-Live-Preflight-Checks) | Go-live preflight checks — admin dashboard + `wp matrix-preflight run` |

**From WordPress.org** (via `wp plugin install`, not Packagist):

| Slug | Plugin |
|------|--------|
| `classic-editor` | Classic Editor (WordPress Contributors) |
| `duplicate-page` | Duplicate Page (mndpsingh287) |
| `password-protected` | Password Protected |
| `prevent-browser-caching` | Prevent Browser Caching (Kostya Tereshchuk) |
| `seo-by-rank-math` | Rank Math SEO |
| `wp-mail-smtp` | WP Mail SMTP |

Packagist ([wpackagist.org](https://wpackagist.org)) can install WP plugins with Composer, but this project uses **WP-CLI** from the install script — no extra Composer wiring needed.

### Requirements

- **Git** — to clone custom plugins
- **GitHub org access** — membership in [Matrix-Internet](https://github.com/Matrix-Internet/) plus `gh auth login` (recommended) or SSH/HTTPS credentials
- **WP-CLI** — optional but recommended for install/activate (bundled in Local’s site shell)
- **Site running** — start the site in Local (status **Running**) before running
- **ACF Pro license** — plugin files are cloned by this script; enter license in WP Admin → ACF (or `ACF_PRO_LICENSE` in `wp-config.php`)
- **UpdraftPlus Premium license** — cloned from `Matrix-Internet/updraft-plus`; enter license in **Settings → UpdraftPlus**

### Local by Flywheel — database connection

If you run `npm run flexi:install` from a normal terminal (not Local’s site shell), WP-CLI may fail with “Error establishing a database connection” because `DB_HOST=localhost` points at the wrong MySQL (e.g. Homebrew on port 3306).

The script auto-detects Local sites and sets `DB_HOST` to the correct socket, for example:

`localhost:/Users/you/Library/Application Support/Local/run/<site-id>/mysql/mysqld.sock`

That change is written to `wp-config.php` once and fixes WP-CLI for future runs.

**Still required:** the **rollingdonut** site must be **Running** in Local when you run the script.

Plugin downloads use a longer timeout (`WP_TIMEOUT_INSTALL`, default 180s). If installs were skipped before, re-run after the site is running — the script installs all WordPress.org plugins in one batch with `--activate`.

### Flags

- `--force-activate` — try activation even when WP-CLI cannot query the database (e.g. site stopped)

### After install

- Matrix Component Importer: `/wp-admin/admin.php?page=matrix-ci-admin-page`
- Matrix Content Gathering: **Tools → Content Gathering**
- Matrix Go-Live Preflight Checks: **wp-admin → Matrix Go-Live Preflight Checks** (or `wp matrix-preflight run`)
- If content-gathering needs PHP deps: `cd wp-content/plugins/matrix-content-gathering && composer install`

### Related npm scripts

Use `npm run dev` and `npm run build` for assets. Client-specific content seeders are not included in the default theme — add pages and flexi content via WP admin or project-specific tooling.

---

## `post-live-install.sh` — post go-live tooling

**Not** part of `flexi:install`. Run on production or after go-live when you want ops/maintenance tools.

```bash
npm run post-live:install
# or a single tool:
bash scripts/post-live-install.sh --only=plugin-checker
```

| Tool | Repo | Install location | Usage |
|------|------|------------------|--------|
| Matrix Plugin Checker | [matrix-plugin-checker](https://github.com/Matrix-Internet/matrix-plugin-checker) | `wp-content/mu-plugins/matrix-plugin-checker.php` | **Tools → Plugin Checker** → **Run Checker** |

The script creates `wp-content/mu-plugins/` if needed, clones the private repo via `gh`, and copies the MU plugin file. Registry: `MATRIX_POST_LIVE_MU_PLUGINS` in `scripts/matrix-plugins.sh`.

Requires `gh auth login` (or git access to [Matrix-Internet](https://github.com/Matrix-Internet/)).
