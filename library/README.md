# Theme library

Reference material for building flexi blocks and sections. **Not loaded in production** — copy into the canonical drop-in paths documented in [docs/theme-structure.md](../docs/theme-structure.md).

## Contents

| Path | Purpose |
|------|---------|
| `examples/acf/flexi/` | ACF Builder field definitions (`acf_{layout}.php`) |
| `examples/flexi/` | Frontend templates (`{layout}.php`) |
| `examples/acf/hero/`, `examples/hero/` | Hero block pairs |
| `examples/cpts/` | CPT registration snippets |
| `matrix-starter-components/` | HTML/component reference ([bernardhanna/matrix-starter-components](https://github.com/bernardhanna/matrix-starter-components)) |

## Sync components repo

```bash
npm run library:sync
# or
bash scripts/library-sync.sh
```

`library/matrix-starter-components/` is gitignored — clone it locally after checkout.

## Copy to production

| From | To |
|------|-----|
| `library/examples/acf/flexi/acf_{layout}.php` | `acf-fields/partials/blocks/acf_{layout}.php` |
| `library/examples/flexi/{layout}.php` | `template-parts/flexi/{layout}.php` |

Also see committed gold standards in [`reference-blocks/flexi/`](../reference-blocks/flexi/).

## MCP

- `theme://library` — this README
- `theme://library/examples/{layout}` — read example pair before building
