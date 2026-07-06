# Agent instructions (Matrix Starter)

Follow this workflow when building or modifying theme code. **Do not invent new folder patterns.**

## Before writing code

1. Read `docs/theme-structure.md` and `docs/flexi-blocks-basics.md`
2. MCP resource: `theme://structure`
3. Run `validate_theme_structure` — fix any existing violations first

## New flexi block (strict: two files only)

**Reference, do not copy wholesale.** The library and `reference-blocks/` are pattern catalogs — read them, then write a **new** layout adapted to the design. Do not `copy_from_library` into a new flexi slug.

1. Find the closest pattern: `find_library_component` or `theme://library/catalog`
2. **Read** the pattern: `get_library_component` or `theme://reference-blocks/{layout}` — study ACF fields, helpers, a11y markup
3. Create the **new** block with `scaffold_flexi_block` (new layout slug), then adapt the reference into **only** these two files:
   - `acf-fields/partials/blocks/acf_{layout}.php`
   - `template-parts/flexi/{layout}.php`
4. Use existing helpers: `matrix_btn_classes()`, `matrix_flexi_padding_classes()`, `matrix_flexi_heading_html()`, `matrix_content_container_classes()`
5. Inside flexi templates: **`get_sub_field()` only** — never `get_field()`
6. Run `validate_flexi_a11y_conventions` (static template checks)
7. Add block row on `/flexi/` review page, then `validate_flexi_a11y` when site is running
8. Run `validate_flexi_blocks`, `validate_theme_structure`, then `theme_build`
9. PR: `npm run test:a11y:quick` (full site)

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
| `find_library_component` | Search catalog for a **reference** pattern before building |
| `get_library_component` | **Read** library ACF + template as reference (primary tool for new blocks) |
| `copy_from_library` | Import a finished component as-is (footer, CPT, etc.) — **not** for new flexi blocks |
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

**Reference when building (do not wholesale-copy):**

1. [`reference-blocks/flexi/`](../reference-blocks/flexi/) — primary gold standard for **flexi** patterns (always in theme git)
2. `get_library_component` / `theme://library/catalog` — extended patterns (maps, forms, listings, etc.)
3. `scaffold_flexi_block` — empty starting pair when nothing is close enough

Use `copy_from_library` only to drop in a **finished** non-flexi component (e.g. footer template, CPT registration) — same as WP Admin → Matrix Components import. When building a **new** flexi layout, read references and write adapted code under a new slug.

| Need | Library example | Theme destination |
|------|-----------------|-------------------|
| Flexi section | `content/002/` | `acf-fields/partials/blocks/` + `template-parts/flexi/` |
| Hero | `hero/001/` | `acf-fields/partials/hero/` + `template-parts/hero/` |
| Footer | `footer/001/` | `template-parts/footer/` |
| CPT | `custom-post-types/faqs.php` | `inc/cpts/post-types/` |
| Taxonomy | `taxonomies/faq-categories.php` | `inc/cpts/taxonomies/` |
| Theme option tab | `theme-options/{slug}.php` in library → `inc/theme-options/{slug}.php` |

**Export to the library** (after validation; flexi also needs `/flexi/` for screenshot):

```bash
npm run library:export -- --kind=flexi --layout=content_029
npm run library:export -- --kind=hero --slug=hero_001
npm run library:export -- --kind=theme-option --slug=footer
npm run library:export -- --kind=cpt --slug=faqs
npm run library:export -- --kind=taxonomy --slug=faq-categories
```

Full inventory: [CATALOG.md](https://github.com/Matrix-Internet/matrix-component-library/blob/main/CATALOG.md)

CI on the component library repo **rejects** exports that fail gold standard. See [GOLD-STANDARD.md](https://github.com/Matrix-Internet/matrix-component-library/blob/main/GOLD-STANDARD.md).

MCP: `theme://library/catalog` · `find_library_component` · `get_library_component` (reference) · `theme://reference-blocks/{layout}`
