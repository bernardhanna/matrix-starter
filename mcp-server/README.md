# Matrix Starter MCP (Phase 1)

Dev tooling MCP server for the Matrix Starter WordPress theme. Exposes filesystem and npm workflows to Cursor agents — **not** WordPress runtime admin.

Content seeders are not part of the default theme. Use `npm run flexi:install` for project bootstrap.

## Tools

| Tool | Purpose |
|------|---------|
| `find_library_component` | Search catalog for reference patterns |
| `get_library_component` | Read library ACF + template as **reference** when building new blocks |
| `copy_from_library` | Import finished component as-is (footer, CPT, hero) — not for new flexi layouts |
| `theme_status` | Repo health: `dist/`, dependencies, flexi parity |
| `list_flexi_layouts` | Inventory of ACF + template pairs |
| `validate_flexi_blocks` | Fail if any layout is missing its pair |
| `validate_theme_structure` | Enforce drop-in folder contract (no extra partials/requires) |
| `list_theme_inventory` | List flexi layouts, CPTs, theme options, library + reference blocks |
| `validate_flexi_a11y_conventions` | Static a11y checks on flexi PHP templates |
| `validate_flexi_a11y` | Axe scan on `/flexi/` review page (needs BASE_URL) |
| `scaffold_flexi_block` | Generate `acf_{layout}.php` + `{layout}.php` |
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

Use `find_library_component` + `get_library_component` to **reference** patterns when building. `copy_from_library` is for importing finished components only (same as WP Admin import), not for creating new flexi blocks.

## Install

```bash
cd mcp-server
npm install
npm run build
```

## Cursor configuration

Add to your MCP settings (adjust the theme path):

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

## Phase 2 (planned)

WP-CLI tools for Theme Options reads, plugin checks, and generic page seeding.


## CLI (CI)

```bash
cd mcp-server && npm run build
npm run validate-structure
npm run validate-flexi
```

```bash
npm run validate-a11y-conventions
npm run validate-a11y-conventions -- --layout=wysiwyg
```
