import fs from "node:fs/promises";
import path from "node:path";
import { PATHS, THEME_ROOT, layoutFromAcfFilename, layoutFromFlexiFilename } from "../config.js";
import { getFlexiInventory, validateFlexiBlocks } from "./flexi.js";

export type StructureViolation = {
  severity: "error" | "warning";
  code: string;
  message: string;
  path?: string;
};

export type ThemeInventory = {
  flexiLayouts: string[];
  heroAcfLayouts: string[];
  heroTemplates: string[];
  themeOptionsTabs: string[];
  cptFiles: string[];
  taxonomyFiles: string[];
  helperUtils: string[];
};

async function listPhpBasenames(dir: string): Promise<string[]> {
  try {
    const entries = await fs.readdir(dir);
    return entries.filter((name) => name.endsWith(".php")).sort();
  } catch {
    return [];
  }
}

async function dirExists(dir: string): Promise<boolean> {
  try {
    await fs.access(dir);
    return true;
  } catch {
    return false;
  }
}

async function validateHeroParity(): Promise<StructureViolation[]> {
  const violations: StructureViolation[] = [];
  if (!(await dirExists(PATHS.acfHeroBlocks))) {
    return violations;
  }

  const acfFiles = await listPhpBasenames(PATHS.acfHeroBlocks);
  const templateFiles = await listPhpBasenames(PATHS.heroTemplates);
  const acfLayouts = new Map<string, string>();
  for (const file of acfFiles) {
    const layout = layoutFromAcfFilename(file);
    if (layout) acfLayouts.set(layout, file);
  }
  const templateLayouts = new Map<string, string>();
  for (const file of templateFiles) {
    const layout = layoutFromFlexiFilename(file);
    if (layout) templateLayouts.set(layout, file);
  }

  for (const [layout, file] of acfLayouts) {
    if (!templateLayouts.has(layout)) {
      violations.push({
        severity: "error",
        code: "hero_missing_template",
        message: `Hero ACF layout "${layout}" has no template-parts/hero/${layout}.php`,
        path: path.join("acf-fields/partials/hero", file),
      });
    }
  }

  for (const [layout, file] of templateLayouts) {
    if (!acfLayouts.has(layout)) {
      violations.push({
        severity: "warning",
        code: "hero_template_without_acf",
        message: `Hero template "${layout}" has no matching acf-fields/partials/hero/acf_${layout}.php`,
        path: path.join("template-parts/hero", file),
      });
    }
  }

  return violations;
}

async function validateForbiddenPaths(): Promise<StructureViolation[]> {
  const violations: StructureViolation[] = [];
  for (const relative of ["inc/blocks", "inc/flexi", "inc/partials", "template-parts/blocks"]) {
    const absolute = path.join(THEME_ROOT, relative);
    if (await dirExists(absolute)) {
      const files = await listPhpBasenames(absolute);
      if (files.length > 0) {
        violations.push({
          severity: "error",
          code: "forbidden_directory",
          message: `Forbidden directory contains PHP files: ${relative}/`,
          path: relative,
        });
      }
    }
  }

  try {
    await fs.access(path.join(THEME_ROOT, "inc/autoload-flexi-blocks.php"));
    violations.push({
      severity: "warning",
      code: "dead_autoload_script",
      message: "Remove inc/autoload-flexi-blocks.php — use acf-fields/partials/flexi.php",
      path: "inc/autoload-flexi-blocks.php",
    });
  } catch {
    // ok
  }

  return violations;
}

async function validateFunctionsPhpRequires(): Promise<StructureViolation[]> {
  const violations: StructureViolation[] = [];
  let source: string;
  try {
    source = await fs.readFile(path.join(THEME_ROOT, "functions.php"), "utf8");
  } catch {
    return violations;
  }

  const checks = [
    [/require(?:_once)?\s*\(?[^;\n]*acf-fields\/partials\/blocks\//, "functions_requires_flexi_block", "functions.php must not require individual flexi block files"],
    [/require(?:_once)?\s*\(?[^;\n]*template-parts\/flexi\//, "functions_requires_flexi_template", "functions.php must not require flexi templates"],
    [/require(?:_once)?\s*\(?[^;\n]*inc\/autoload-flexi-blocks/, "functions_requires_dead_autoload", "Do not require inc/autoload-flexi-blocks.php"],
  ] as const;

  for (const [regex, code, message] of checks) {
    if (regex.test(source)) {
      violations.push({ severity: "error", code, message, path: "functions.php" });
    }
  }

  return violations;
}

async function validateThemeOptionsTabs(): Promise<StructureViolation[]> {
  const violations: StructureViolation[] = [];
  if (!(await dirExists(PATHS.themeOptions))) {
    return violations;
  }

  for (const file of await listPhpBasenames(PATHS.themeOptions)) {
    if (file === "admin-dashboard-controls.php") {
      continue;
    }

    const relative = `inc/theme-options/${file}`;
    const content = await fs.readFile(path.join(THEME_ROOT, relative), "utf8");
    const trimmed = content.trim();

    if (trimmed === "" || trimmed === "<?php") {
      violations.push({
        severity: "warning",
        code: "empty_theme_option_stub",
        message: `Remove or implement empty theme option tab: ${relative}`,
        path: relative,
      });
      continue;
    }

    if (!content.includes("FieldsBuilder")) {
      violations.push({
        severity: "error",
        code: "theme_option_missing_fields_builder",
        message: `${relative} must use FieldsBuilder and return the builder instance`,
        path: relative,
      });
    }

    if (!/return\s+\$/.test(content)) {
      violations.push({
        severity: "error",
        code: "theme_option_missing_return",
        message: `${relative} must return a FieldsBuilder variable`,
        path: relative,
      });
    }
  }

  return violations;
}

async function validateCptAndTaxonomyFiles(): Promise<StructureViolation[]> {
  const violations: StructureViolation[] = [];

  const checks = [
    {
      dir: PATHS.cptPostTypes,
      prefix: "inc/cpts/post-types",
      needle: "register_post_type",
      code: "cpt_missing_registration",
      message: "must call register_post_type()",
    },
    {
      dir: PATHS.cptTaxonomies,
      prefix: "inc/cpts/taxonomies",
      needle: "register_taxonomy",
      code: "taxonomy_missing_registration",
      message: "must call register_taxonomy()",
    },
  ] as const;

  for (const check of checks) {
    if (!(await dirExists(check.dir))) {
      continue;
    }

    for (const file of await listPhpBasenames(check.dir)) {
      const relative = `${check.prefix}/${file}`;
      const content = await fs.readFile(path.join(THEME_ROOT, relative), "utf8");
      if (!content.includes(check.needle)) {
        violations.push({
          severity: "warning",
          code: check.code,
          message: `${relative} ${check.message}`,
          path: relative,
        });
      }
    }
  }

  return violations;
}

async function validateTemplateDropInDirs(): Promise<StructureViolation[]> {
  const violations: StructureViolation[] = [];
  const allowedRoots = [
    { dir: PATHS.footerTemplates, label: "template-parts/footer" },
    { dir: PATHS.headerTemplates, label: "template-parts/header" },
    { dir: PATHS.blogTemplates, label: "template-parts/blog" },
  ];

  for (const { dir, label } of allowedRoots) {
    if (!(await dirExists(dir))) {
      continue;
    }

    for (const file of await listPhpBasenames(dir)) {
      if (file.startsWith("_")) {
        violations.push({
          severity: "warning",
          code: "template_partial_underscore",
          message: `${label}/${file} — partials prefixed with _ are not standard drop-ins`,
          path: `${label}/${file}`,
        });
      }
    }
  }

  return violations;
}

async function validateEmptyHelperStubs(): Promise<StructureViolation[]> {
  const violations: StructureViolation[] = [];
  const utilsDir = path.join(THEME_ROOT, "inc/helpers/utils");
  if (!(await dirExists(utilsDir))) return violations;

  for (const file of await listPhpBasenames(utilsDir)) {
    const content = await fs.readFile(path.join(utilsDir, file), "utf8");
    if (content.trim() === "" || content.trim() === "<?php") {
      violations.push({
        severity: "warning",
        code: "empty_helper_stub",
        message: `Remove or implement empty helper inc/helpers/utils/${file}`,
        path: `inc/helpers/utils/${file}`,
      });
    }
  }

  return violations;
}

export async function validateThemeStructure() {
  const flexiValidation = await validateFlexiBlocks();
  const violations = [
    ...(await validateHeroParity()),
    ...(await validateForbiddenPaths()),
    ...(await validateFunctionsPhpRequires()),
    ...(await validateThemeOptionsTabs()),
    ...(await validateCptAndTaxonomyFiles()),
    ...(await validateTemplateDropInDirs()),
    ...(await validateEmptyHelperStubs()),
  ];

  if (!flexiValidation.valid) {
    for (const layout of flexiValidation.layouts.filter((l) => !l.valid)) {
      for (const issue of layout.issues) {
        violations.push({ severity: "error", code: "flexi_parity", message: issue });
      }
    }
  }

  const errors = violations.filter((v) => v.severity === "error");
  const warnings = violations.filter((v) => v.severity === "warning");
  const valid = errors.length === 0 && flexiValidation.valid;

  return {
    valid,
    errors,
    warnings,
    flexiValidation,
    summary: valid
      ? `Theme structure valid (${warnings.length} warning(s)).`
      : `${errors.length} error(s), ${warnings.length} warning(s).`,
  };
}

export async function getThemeInventory(): Promise<ThemeInventory> {
  return {
    flexiLayouts: (await getFlexiInventory()).filter((l) => l.valid).map((l) => l.layout),
    heroAcfLayouts: (await listPhpBasenames(PATHS.acfHeroBlocks))
      .map((f) => layoutFromAcfFilename(f))
      .filter((l): l is string => l !== null),
    heroTemplates: (await listPhpBasenames(PATHS.heroTemplates)).map((f) => f.replace(/\.php$/, "")),
    themeOptionsTabs: (await listPhpBasenames(path.join(THEME_ROOT, "inc/theme-options")))
      .filter((f) => f !== "admin-dashboard-controls.php")
      .map((f) => f.replace(/\.php$/, "")),
    cptFiles: await listPhpBasenames(path.join(THEME_ROOT, "inc/cpts/post-types")),
    taxonomyFiles: await listPhpBasenames(path.join(THEME_ROOT, "inc/cpts/taxonomies")),
    helperUtils: await listPhpBasenames(path.join(THEME_ROOT, "inc/helpers/utils")),
  };
}

export async function listReferenceBlockLayouts(): Promise<string[]> {
  if (!(await dirExists(PATHS.referenceBlocksFlexi))) return [];
  const layouts = new Set<string>();
  for (const file of await listPhpBasenames(PATHS.referenceBlocksFlexi)) {
    const fromAcf = layoutFromAcfFilename(file);
    if (fromAcf) layouts.add(fromAcf);
    else {
      const fromTpl = layoutFromFlexiFilename(file);
      if (fromTpl) layouts.add(fromTpl);
    }
  }
  return [...layouts].sort();
}

export async function readReferenceBlock(layout: string) {
  const safe = layout.trim();
  if (!/^[a-z][a-z0-9_]*$/.test(safe)) throw new Error("Invalid layout slug.");
  let acf: string | null = null;
  let template: string | null = null;
  try { acf = await fs.readFile(path.join(PATHS.referenceBlocksFlexi, `acf_${safe}.php`), "utf8"); } catch { /* */ }
  try { template = await fs.readFile(path.join(PATHS.referenceBlocksFlexi, `${safe}.php`), "utf8"); } catch { /* */ }
  if (!acf && !template) throw new Error(`No reference block found for layout: ${safe}`);
  return { acf, template };
}
