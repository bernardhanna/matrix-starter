# Theme folder structure (drop-in contract)

Matrix Starter uses **autoloaded folders** so you can copy files between projects without editing `functions.php`. Every feature type has exactly one drop-in location.

Related: [flexi-blocks-basics.md](flexi-blocks-basics.md), [AGENTS.md](../AGENTS.md).

---

## Flexi blocks: two files only

Each flexi block is **exactly two files**. No loaders, no partials, no `require_once`.

| File | Path |
|------|------|
| ACF definition | `acf-fields/partials/blocks/acf_{layout}.php` |
| Frontend template | `template-parts/flexi/{layout}.php` |

**Registration is automatic:**

- [`acf-fields/partials/flexi.php`](../acf-fields/partials/flexi.php) globs `acf-fields/partials/blocks/*.php` into `flexible_content_blocks`
- [`inc/flexible-content-functions.php`](../inc/flexible-content-functions.php) loads `template-parts/flexi/{layout}.php` at runtime
- [`inc/autoload-acf-fields.php`](../inc/autoload-acf-fields.php) **skips** block files under `partials/blocks/` (parent loader owns them)

Copy both files to another project — nothing else is required.

---

## Full drop-in map

| Concern | Drop-in path | Loaded by |
|---------|--------------|-----------|
| Flexi blocks | `acf-fields/partials/blocks/acf_{layout}.php` + `template-parts/flexi/{layout}.php` | `flexi.php` + `load_flexible_content_templates()` |
| Hero blocks | `acf-fields/partials/hero/acf_{layout}.php` + `template-parts/hero/{layout}.php` | `hero.php` + `load_hero_templates()` |
| Page ACF | `acf-fields/partials/pages/*.php` | `inc/autoload-acf-groups.php` |
| CPT / taxonomy ACF | `acf-fields/partials/post-types/`, `acf-fields/partials/taxonomies/` | `inc/autoload-acf-groups.php` |
| Theme options tabs | `inc/theme-options/{name}.php` (returns `FieldsBuilder`) | `inc/theme-options.php` glob |
| CPTs | `inc/cpts/post-types/{name}.php` | `inc/cpts/init.php` glob |
| Taxonomies | `inc/cpts/taxonomies/{name}.php` | `inc/cpts/init.php` glob |
| Shared helpers | `inc/helpers/utils/{name}.php` | `inc/autoload-helpers-setup.php` |
| Page partials | `template-parts/page/{name}.php` | `get_template_part()` from page templates |

---

## Forbidden without human approval

Do **not** create these when adding a flexi block:

- New `require_once` in `functions.php` for block code
- Files under `inc/blocks/`, `inc/flexi/`, `inc/partials/`
- Duplicate aggregators (second `flexi.php`, `autoload-flexi-blocks.php`, etc.)
- Per-block CSS/JS assets — use Tailwind + scoped `<style>` in the template
- ACF files outside `acf-fields/partials/blocks/` for flexi layouts
- Templates outside `template-parts/flexi/` for flexi layouts

---

## Reference implementations

Committed examples:

- [`library/examples/`](../library/examples/) — full PHP flexi/hero/CPT reference set
- [`reference-blocks/flexi/`](../reference-blocks/flexi/) — smaller gold-standard subset
- `library/matrix-starter-components/` — HTML components (`npm run library:sync`, gitignored clone)

Copy from `reference-blocks/flexi/` into the production paths above. The gitignored `examples/` folder is for client-specific blocks only.

---

## Copy-between-projects checklist

1. Copy `acf-fields/partials/blocks/acf_{layout}.php` → same path in target theme
2. Copy `template-parts/flexi/{layout}.php` → same path in target theme
3. Run `npm run build` if new Tailwind classes were added
4. Run MCP `validate_theme_structure` and `validate_flexi_blocks`
5. Do **not** edit `functions.php`

---

## MCP validation

Use the Matrix Starter MCP server:

- `validate_theme_structure` — parity, forbidden paths, suspicious requires
- `validate_flexi_blocks` — ACF/template pairs for flexi
- `scaffold_flexi_block` — creates only the two canonical files

Resource: `theme://structure` (this document).
