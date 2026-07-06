import path from "node:path";
import { fileURLToPath } from "node:url";

const moduleDir = path.dirname(fileURLToPath(import.meta.url));

/** Absolute path to the Matrix Starter theme root (parent of mcp-server/). */
export const THEME_ROOT = path.resolve(moduleDir, "../..");

/** Absolute path to wp-content/ (parent of themes/). */
export const WP_CONTENT_ROOT = path.resolve(THEME_ROOT, "../..");

/** Local component library installed by matrix-component-importer. */
export const COMPONENT_LIBRARY_ROOT = process.env.MATRIX_LIBRARY_DIR
  ? path.resolve(process.env.MATRIX_LIBRARY_DIR)
  : path.join(WP_CONTENT_ROOT, "matrix-component-library");

export const PATHS = {
  acfBlocks: path.join(THEME_ROOT, "acf-fields/partials/blocks"),
  flexiTemplates: path.join(THEME_ROOT, "template-parts/flexi"),
  tailwindConfig: path.join(THEME_ROOT, "tailwind.config.js"),
  dist: path.join(THEME_ROOT, "dist"),
  docs: path.join(THEME_ROOT, "docs"),
  envExample: path.join(THEME_ROOT, ".env.example"),
  flexiInstallScript: path.join(THEME_ROOT, "scripts/flexi-install.sh"),
  acfHeroBlocks: path.join(THEME_ROOT, "acf-fields/partials/hero"),
  heroTemplates: path.join(THEME_ROOT, "template-parts/hero"),
  referenceBlocksFlexi: path.join(THEME_ROOT, "reference-blocks/flexi"),
  themeStructureDoc: path.join(THEME_ROOT, "docs/theme-structure.md"),
  componentLibrary: COMPONENT_LIBRARY_ROOT,
  themeOptions: path.join(THEME_ROOT, "inc/theme-options"),
  cptPostTypes: path.join(THEME_ROOT, "inc/cpts/post-types"),
  cptTaxonomies: path.join(THEME_ROOT, "inc/cpts/taxonomies"),
  footerTemplates: path.join(THEME_ROOT, "template-parts/footer"),
  headerTemplates: path.join(THEME_ROOT, "template-parts/header"),
  blogTemplates: path.join(THEME_ROOT, "template-parts/blog"),
} as const;

export function layoutFromAcfFilename(filename: string): string | null {
  const match = /^acf_(.+)\.php$/.exec(filename);
  return match?.[1] ?? null;
}

export function layoutFromFlexiFilename(filename: string): string | null {
  const match = /^(.+)\.php$/.exec(filename);
  return match?.[1] ?? null;
}
