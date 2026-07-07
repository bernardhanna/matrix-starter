/** Mirrors matrix-component-importer block type map (theme-relative destinations). */

export type ComponentTypeConfig = {
  hasAcf: boolean;
  acfDest: string;
  templateDest: string;
  templateRename?: (sourceBasename: string, folder: string) => string;
};

const FLEXI_DEST: ComponentTypeConfig = {
  hasAcf: true,
  acfDest: "acf-fields/partials/blocks/",
  templateDest: "template-parts/flexi/",
};

const TEMPLATE_ONLY = (templateDest: string): ComponentTypeConfig => ({
  hasAcf: false,
  acfDest: "",
  templateDest,
});

const BLOCK_TYPE_MAP: Record<string, ComponentTypeConfig> = {
  "404": TEMPLATE_ONLY("template-parts/404/"),
  accreditations: FLEXI_DEST,
  banner: TEMPLATE_ONLY("template-parts/header/"),
  "back-to-top": TEMPLATE_ONLY("template-parts/footer/"),
  blog: TEMPLATE_ONLY("template-parts/blog/"),
  breadcrumbs: TEMPLATE_ONLY("template-parts/header/"),
  contact: FLEXI_DEST,
  content: FLEXI_DEST,
  copyright: TEMPLATE_ONLY("template-parts/footer/"),
  counters: FLEXI_DEST,
  cta: FLEXI_DEST,
  "custom-post-types": TEMPLATE_ONLY("inc/cpts/post-types/"),
  faq: FLEXI_DEST,
  features: FLEXI_DEST,
  footer: TEMPLATE_ONLY("template-parts/footer/"),
  gallery: FLEXI_DEST,
  hero: {
    hasAcf: true,
    acfDest: "acf-fields/partials/hero/",
    templateDest: "template-parts/hero/",
  },
  intro: FLEXI_DEST,
  "navigation-desktop": TEMPLATE_ONLY("template-parts/header/"),
  "navigation-mobile": {
    hasAcf: false,
    acfDest: "",
    templateDest: "template-parts/header/navbar/",
    templateRename: () => "mobile.php",
  },
  newsletter: TEMPLATE_ONLY("template-parts/footer/"),
  pagination: TEMPLATE_ONLY("inc/"),
  partners: FLEXI_DEST,
  "single-hero": {
    hasAcf: true,
    acfDest: "acf-fields/partials/hero/",
    templateDest: "template-parts/single/",
  },
  sitemap: TEMPLATE_ONLY("templates/"),
  taxonomies: TEMPLATE_ONLY("inc/cpts/taxonomies/"),
  taxonomy: TEMPLATE_ONLY("inc/cpts/taxonomies/"),
  "theme-options": TEMPLATE_ONLY("inc/theme-options/"),
  team: FLEXI_DEST,
  testimonials: FLEXI_DEST,
  timeline: FLEXI_DEST,
  title: FLEXI_DEST,
  topbar: TEMPLATE_ONLY("template-parts/header/"),
  utility: FLEXI_DEST,
};

export function getComponentTypeConfig(type: string): ComponentTypeConfig {
  return BLOCK_TYPE_MAP[type] ?? FLEXI_DEST;
}

export function describeThemeDestinations(type: string): string {
  const config = getComponentTypeConfig(type);
  const parts: string[] = [];
  if (config.hasAcf && config.acfDest) {
    parts.push(config.acfDest);
  }
  if (config.templateDest) {
    parts.push(config.templateDest);
  }
  return parts.join(" + ") || "varies";
}

export function resolveThemeDestPaths(
  type: string,
  acfBasename: string | null,
  templateBasename: string | null,
  folder: string,
): { acf?: string; template?: string } {
  const config = getComponentTypeConfig(type);
  const result: { acf?: string; template?: string } = {};

  if (acfBasename && config.hasAcf && config.acfDest) {
    result.acf = `${config.acfDest}${acfBasename}`;
  }

  if (templateBasename && config.templateDest) {
    const name = config.templateRename?.(templateBasename, folder) ?? templateBasename;
    result.template = `${config.templateDest}${name}`;
  }

  return result;
}
