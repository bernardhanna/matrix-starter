# Agent instructions (Matrix Starter)

Follow this workflow when building or modifying theme code. **Do not invent new folder patterns.**

## Before writing code

1. Read `docs/theme-structure.md` and `docs/flexi-blocks-basics.md`
2. MCP resource: `theme://structure`
3. Run `validate_theme_structure` — fix any existing violations first

## New flexi block (strict: two files only)

1. Find the closest layout in [`reference-blocks/flexi/`](../reference-blocks/flexi/), then `wp-content/matrix-component-library/`, or run `scaffold_flexi_block`
2. Edit **only** these two files:
   - `acf-fields/partials/blocks/acf_{layout}.php`
   - `template-parts/flexi/{layout}.php`
3. Use existing helpers: `matrix_btn_classes()`, `matrix_flexi_padding_classes()`, `matrix_flexi_heading_html()`, `matrix_content_container_classes()`
4. Inside flexi templates: **`get_sub_field()` only** — never `get_field()`
5. Run `validate_flexi_a11y_conventions` (static template checks)
6. Add block row on `/flexi/` review page, then `validate_flexi_a11y` when site is running
7. Run `validate_flexi_blocks`, `validate_theme_structure`, then `theme_build`
8. PR: `npm run test:a11y:quick` (full site)

## Never do this for a flexi block

- Add `require_once` to `functions.php`
- Create files under `inc/` for one block
- Create `template-parts/blocks/`, `inc/partials/`, or new loader scripts
- Wrap the template in outer `have_rows()` loops (parent already calls `the_row()`)
- Add per-block CSS/JS files

## Other drop-in locations

| Task | Where to put files |
|------|-------------------|
| Theme option tab | `inc/theme-options/{name}.php` → returns `FieldsBuilder` |
| CPT | `inc/cpts/post-types/{name}.php` |
| Taxonomy | `inc/cpts/taxonomies/{name}.php` |
| Shared utility | `inc/helpers/utils/{name}.php` |
| Hero block | `acf-fields/partials/hero/acf_{layout}.php` + `template-parts/hero/{layout}.php` |

## MCP tools

| Tool | When |
|------|------|
| `list_theme_inventory` | Discover layouts, CPTs, options tabs |
| `scaffold_flexi_block` | Start a new block pair |
| `validate_theme_structure` | Before commit / after changes |
| `validate_flexi_a11y_conventions` | Static a11y/convention checks on flexi template |
| `validate_flexi_a11y` | Axe scan on `/flexi/` (needs BASE_URL) |
| `validate_flexi_blocks` | Flexi ACF/template parity |
| `theme_build` | After CSS/Tailwind changes |
| `theme_test` | Before PR (`suite: php` minimum) |

Install: see `mcp-server/README.md`.

## Library (AI + developers)

The component library is **not flexi-only**. It includes flexi sections, **hero**, **footer**, **header/nav**, **blog/404 templates**, **CPTs**, **taxonomies**, and more. Use WP Admin → **Matrix Components** to import any type; the importer maps each library folder to the correct theme drop-in path.

**Copy from (in order):**

1. [`reference-blocks/flexi/`](../reference-blocks/flexi/) — primary gold standard for **flexi** blocks (always in theme git)
2. `wp-content/matrix-component-library/{type}/{folder}/` — extended patterns (e.g. `hero/001`, `footer/001`, `custom-post-types/faqs.php`, `content/031` for maps)
3. `scaffold_flexi_block` — new flexi block when nothing matches

| Need | Library example | Theme destination |
|------|-----------------|-------------------|
| Flexi section | `content/002/` | `acf-fields/partials/blocks/` + `template-parts/flexi/` |
| Hero | `hero/001/` | `acf-fields/partials/hero/` + `template-parts/hero/` |
| Footer | `footer/001/` | `template-parts/footer/` |
| CPT | `custom-post-types/faqs.php` | `inc/cpts/post-types/` |
| Taxonomy | `taxonomies/faq-categories.php` | `inc/cpts/taxonomies/` |
| Theme option tab | — | `inc/theme-options/{name}.php` (theme only for now) |

**Export flexi blocks to the library** (after a11y pass + on `/flexi/`):

```bash
npm run library:export -- --layout=content_029
```

CI on the component library repo **rejects** exports that fail gold standard. See [GOLD-STANDARD.md](https://github.com/Matrix-Internet/matrix-component-library/blob/main/GOLD-STANDARD.md).

MCP: `theme://library/{type}/{folder}` · `theme://reference-blocks/{layout}`
