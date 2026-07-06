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

- **WP Admin → Matrix Components** — import sections into production theme paths
- **MCP** — `theme://library`, `theme://library/{type}/{folder}`
- **Agents** — read `wp-content/matrix-component-library/` for coding patterns

## Add new sections

Contribute back via `scripts/add-section.php` in the component library repo.
