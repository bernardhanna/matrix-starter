# Matrix Starter MCP

Dev tooling MCP server for the Matrix Starter WordPress theme. Exposes filesystem, npm, and WP-CLI workflows to Cursor agents — **not** WordPress runtime admin.

Content seeders are not part of the default theme. Use `npm run flexi:install` for project bootstrap.

## Tools

| Tool | Purpose |
|------|---------|
| `find_library_component` | Search catalog for reference patterns |
| `get_library_component` | Read library ACF + template as **reference** when building new blocks |
| `copy_from_library` | Import finished component as-is (footer, CPT, hero) — not for new flexi layouts |
| `preflight_flexi_block` | One-shot: structure + flexi parity + a11y conventions |
| `seed_flexi_review_block` | WP-CLI: add layout row to `/flexi/` review page (needs `WP_PATH`) |
| `library_sync` | Clone/pull `wp-content/matrix-component-library` |
| `library_export` | Export validated component to library repo |
| `theme_status` | Repo health: `dist/`, dependencies, flexi parity |
| `list_flexi_layouts` | Inventory of ACF + template pairs |
| `validate_flexi_blocks` | Fail if any layout is missing its pair |
| `validate_theme_structure` | Drop-in contract: flexi/hero, theme-options, CPTs, templates |
| `list_theme_inventory` | List flexi layouts, CPTs, theme options, library + reference blocks |
| `validate_flexi_a11y_conventions` | Static a11y checks on flexi PHP templates |
| `validate_flexi_a11y` | Axe scan on `/flexi/` review page (needs BASE_URL) |
| `scaffold_flexi_block` | New flexi pair; optional `source` from reference-blocks or library |
| `get_theme_tokens` | Read `THEME_TOKENS` from `tailwind.config.js` |
| `update_theme_tokens` | Patch semantic tokens (then run `theme_build`) |
| `theme_build` | `npm run build` |
| `theme_test` | `test:php`, `test:e2e`, `test:a11y`, `test:links`, or `ci` |

## Resources

| URI | Content |
|-----|---------|
| `theme://architecture` | Folder map and bootstrap flow |
| `theme://docs/flexi-blocks-basics` | Flexi block conventions |
| `theme://docs/daily-flow` | Branch/build/PR workflow |
| `theme://structure` | Drop-in folder contract ([docs/theme-structure.md](../docs/theme-structure.md)) |
| `theme://reference-blocks/{layout}` | Gold-standard flexi block pair (read-only) |
| `theme://library` | Component library README |
| `theme://library/catalog` | Full CATALOG.md inventory |

Use `find_library_component` + `get_library_component` to **reference** patterns when building. `copy_from_library` is for importing finished components only.

## Install

```bash
cd mcp-server
npm install
npm run build
```

## Cursor configuration

```json
{
  "mcpServers": {
    "matrix-starter": {
      "command": "node",
      "args": ["/absolute/path/to/theme/mcp-server/dist/index.js"]
    }
  }
}
```

## Agent workflow (new flexi block)

1. `find_library_component` → `get_library_component` (read reference)
2. `scaffold_flexi_block` with `source: "library:content/031"` or `reference-blocks:content_002`
3. Adapt fields and markup for the design
4. `preflight_flexi_block` with `{ "layout": "content_042" }`
5. `seed_flexi_review_block` → `validate_flexi_a11y`
6. `theme_build` → PR tests

## CLI (CI)

```bash
cd mcp-server && npm run build
npm run validate-structure
npm run validate-flexi
npm run validate-a11y-conventions
node dist/cli.js preflight-flexi --layout=content_002
```

## Requirements

| Tool | Needs |
|------|--------|
| `seed_flexi_review_block` | `WP_PATH` in `.env`, WP-CLI, ACF active |
| `validate_flexi_a11y` | `BASE_URL` in `.env`, block on `/flexi/` |
| `library_sync` / `library_export` | git, php (library repo scripts) |
