# Component library

The full Matrix component library is **not stored in the theme repo**. It is installed locally at:

```
wp-content/matrix-component-library/
```

## Install

- Activate **matrix-component-importer** (clones on activation), or
- `npm run library:sync` from the theme root

Source repo: [Matrix-Internet/matrix-component-library](https://github.com/Matrix-Internet/matrix-component-library)

## Use

- **WP Admin → Matrix Components** — import sections into production theme paths (flexi, hero, footer, header, CPTs, and more — see component types below)
- **MCP** — `theme://library`, `theme://library/{type}/{folder}`
- **Agents** — read `wp-content/matrix-component-library/` for coding patterns

## Component types (not just flexi)

The library and importer cover many drop-in targets. Examples:

| Library folder | Imports to (active theme) |
|----------------|---------------------------|
| `content/`, `cta/`, `faq/`, `contact/`, … | `acf-fields/partials/blocks/` + `template-parts/flexi/` |
| `hero/`, `single-hero/` | `acf-fields/partials/hero/` + `template-parts/hero/` or `template-parts/single/` |
| `footer/`, `newsletter/`, `copyright/`, `back-to-top/` | `template-parts/footer/` |
| `navigation-desktop/`, `navigation-mobile/`, `breadcrumbs/`, `topbar/` | `template-parts/header/` (mobile → `navbar/mobile.php`) |
| `blog/`, `404/`, `sitemap/` | `template-parts/blog/`, `template-parts/404/`, `templates/` |
| `custom-post-types/` | `inc/cpts/post-types/` (copy manually or import via admin) |
| `taxonomies/` | `inc/cpts/taxonomies/` |

Theme option tabs (`inc/theme-options/`) are not in the library yet — add those to the theme directly per [theme-structure.md](../docs/theme-structure.md).

**Export from theme** — use `npm run library:export` with `--kind`:

| Kind | Example |
|------|---------|
| `flexi` | `--kind=flexi --layout=content_029` |
| `hero` | `--kind=hero --slug=hero_001` |
| `theme-option` | `--kind=theme-option --slug=footer` |
| `cpt` | `--kind=cpt --slug=faqs` |
| `taxonomy` | `--kind=taxonomy --slug=faq-categories` |

Full inventory: [CATALOG.md](https://github.com/Matrix-Internet/matrix-component-library/blob/main/CATALOG.md)

From theme root (after block is on `/flexi/`):

```bash
npm run library:export -- --layout=content_029
```

See [GOLD-STANDARD.md](https://github.com/Matrix-Internet/matrix-component-library/blob/main/GOLD-STANDARD.md) in the component library repo.
