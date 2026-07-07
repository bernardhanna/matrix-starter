import fs from "node:fs/promises";
import path from "node:path";
import { COMPONENT_LIBRARY_ROOT } from "../config.js";
import { describeThemeDestinations } from "./component-map.js";

export type LibraryComponentRef = {
  type: string;
  folder: string;
  themeDestination: string;
};

const SKIP_TOP_LEVEL = new Set([".git", "scripts", ".github", "README.md", "CATALOG.md", "GOLD-STANDARD.md"]);

/** Single PHP per slug at type root. */
const FLAT_SLUG_TYPES = new Set(["custom-post-types", "taxonomies", "theme-options"]);

/** ACF FieldsBuilder tabs — returned as `acf`, not template. */
const FIELDS_BUILDER_TYPES = new Set(["theme-options"]);

async function dirEntries(dir: string): Promise<string[]> {
  try {
    return await fs.readdir(dir);
  } catch {
    return [];
  }
}

async function readIfExists(filePath: string): Promise<string | null> {
  try {
    return await fs.readFile(filePath, "utf8");
  } catch {
    return null;
  }
}

export async function componentLibraryExists(): Promise<boolean> {
  try {
    await fs.access(COMPONENT_LIBRARY_ROOT);
    return true;
  } catch {
    return false;
  }
}

async function listGalleryRootComponents(typePath: string): Promise<LibraryComponentRef[]> {
  const components: LibraryComponentRef[] = [];
  const phpFiles = (await dirEntries(typePath)).filter((f) => f.endsWith(".php"));

  for (const file of phpFiles) {
    if (file.startsWith("acf_")) {
      continue;
    }
    const match = /^(.+)\.php$/.exec(file);
    if (!match) {
      continue;
    }
    const slug = match[1];
    const acfCandidate = path.join(typePath, `acf_${slug}.php`);
    try {
      await fs.access(acfCandidate);
      components.push({
        type: "gallery",
        folder: slug,
        themeDestination: describeThemeDestinations("gallery"),
      });
    } catch {
      /* template without paired acf — still list */
      components.push({
        type: "gallery",
        folder: slug,
        themeDestination: describeThemeDestinations("gallery"),
      });
    }
  }

  return components;
}

export async function listLibraryComponents(): Promise<LibraryComponentRef[]> {
  if (!(await componentLibraryExists())) {
    return [];
  }

  const components: LibraryComponentRef[] = [];
  const types = await dirEntries(COMPONENT_LIBRARY_ROOT);

  for (const type of types) {
    if (SKIP_TOP_LEVEL.has(type) || type.startsWith(".")) {
      continue;
    }

    const typePath = path.join(COMPONENT_LIBRARY_ROOT, type);
    let stat;
    try {
      stat = await fs.stat(typePath);
    } catch {
      continue;
    }
    if (!stat.isDirectory()) {
      continue;
    }

    if (type === "gallery") {
      components.push(...(await listGalleryRootComponents(typePath)));
      continue;
    }

    if (FLAT_SLUG_TYPES.has(type)) {
      for (const file of await dirEntries(typePath)) {
        if (file.endsWith(".php")) {
          components.push({
            type,
            folder: file.replace(/\.php$/, ""),
            themeDestination: describeThemeDestinations(type),
          });
        }
      }
      continue;
    }

    for (const folder of await dirEntries(typePath)) {
      if (folder.startsWith(".")) {
        continue;
      }
      const variantPath = path.join(typePath, folder);
      try {
        const variantStat = await fs.stat(variantPath);
        if (variantStat.isDirectory()) {
          components.push({
            type,
            folder,
            themeDestination: describeThemeDestinations(type),
          });
        }
      } catch {
        /* skip */
      }
    }
  }

  return components.sort(
    (a, b) => a.type.localeCompare(b.type) || a.folder.localeCompare(b.folder),
  );
}

export type ResolvedComponentFiles = {
  acfPath: string | null;
  templatePath: string | null;
};

export async function resolveComponentFiles(
  type: string,
  folder: string,
): Promise<ResolvedComponentFiles> {
  const safeType = type.trim();
  const safeFolder = folder.trim();
  if (!/^[a-z0-9-]+$/.test(safeType) || !/^[a-zA-Z0-9_-]+$/.test(safeFolder)) {
    throw new Error("Invalid library component reference.");
  }

  const base = path.join(COMPONENT_LIBRARY_ROOT, safeType);

  if (FLAT_SLUG_TYPES.has(safeType)) {
    const filePath = path.join(base, `${safeFolder}.php`);
    try {
      await fs.access(filePath);
      return { acfPath: null, templatePath: filePath };
    } catch {
      return { acfPath: null, templatePath: null };
    }
  }

  if (safeType === "gallery") {
    const variantDir = path.join(base, safeFolder);
    try {
      const variantStat = await fs.stat(variantDir);
      if (variantStat.isDirectory()) {
        return resolvePhpPairInDir(variantDir);
      }
    } catch {
      /* fall through to root pairing */
    }

    const templatePath = path.join(base, `${safeFolder}.php`);
    const acfPath = path.join(base, `acf_${safeFolder}.php`);
    return {
      acfPath: (await readIfExists(acfPath)) ? acfPath : null,
      templatePath: (await readIfExists(templatePath)) ? templatePath : null,
    };
  }

  const variantDir = path.join(base, safeFolder);
  return resolvePhpPairInDir(variantDir);
}

async function resolvePhpPairInDir(dir: string): Promise<ResolvedComponentFiles> {
  let acfPath: string | null = null;
  let templatePath: string | null = null;

  for (const file of await dirEntries(dir)) {
    if (!file.endsWith(".php")) {
      continue;
    }
    const filePath = path.join(dir, file);
    if (file.startsWith("acf_")) {
      acfPath = filePath;
    } else if (file === "mobile.php" || !templatePath) {
      templatePath = filePath;
    }
  }

  return { acfPath, templatePath };
}

export async function readLibraryComponent(
  type: string,
  folder: string,
): Promise<{ acf: string | null; template: string | null }> {
  const files = await resolveComponentFiles(type, folder);

  if (!files.acfPath && !files.templatePath) {
    throw new Error(`No library component at ${type}/${folder}`);
  }

  const acfContent = files.acfPath ? await readIfExists(files.acfPath) : null;
  const templateContent = files.templatePath ? await readIfExists(files.templatePath) : null;

  if (FIELDS_BUILDER_TYPES.has(type)) {
    return { acf: templateContent, template: null };
  }

  return { acf: acfContent, template: templateContent };
}

export async function readLibraryCatalog(): Promise<string> {
  const catalogPath = path.join(COMPONENT_LIBRARY_ROOT, "CATALOG.md");
  const catalog = await readIfExists(catalogPath);
  if (catalog) {
    return catalog;
  }

  const components = await listLibraryComponents();
  const lines = [
    "# Component catalog",
    "",
    `Library path: ${COMPONENT_LIBRARY_ROOT}`,
    "",
    "CATALOG.md not found. Run `php scripts/generate-catalog.php --write` in the library repo.",
    "",
    `Found ${components.length} component(s) via filesystem scan.`,
    "",
  ];

  let currentType = "";
  for (const { type, folder, themeDestination } of components) {
    if (type !== currentType) {
      currentType = type;
      lines.push(`## ${type}/ → ${themeDestination}`, "");
    }
    lines.push(`- ${folder}`);
  }

  return lines.join("\n");
}

export type FindLibraryMatch = LibraryComponentRef & {
  score: number;
  path: string;
};

export async function findLibraryComponents(input: {
  query: string;
  type?: string;
  limit?: number;
}): Promise<FindLibraryMatch[]> {
  const query = input.query.trim().toLowerCase();
  if (!query) {
    throw new Error("query is required.");
  }

  const limit = input.limit ?? 20;
  const typeFilter = input.type?.trim().toLowerCase();
  const components = await listLibraryComponents();
  const matches: FindLibraryMatch[] = [];

  for (const component of components) {
    if (typeFilter && component.type !== typeFilter) {
      continue;
    }

    const pathKey = `${component.type}/${component.folder}`;
    const haystack = [
      component.type,
      component.folder,
      pathKey,
      component.themeDestination,
    ]
      .join(" ")
      .toLowerCase();

    let score = 0;
    if (pathKey === query) {
      score = 100;
    } else if (component.folder.toLowerCase() === query) {
      score = 90;
    } else if (component.type.toLowerCase() === query) {
      score = 80;
    } else if (pathKey.includes(query)) {
      score = 70;
    } else if (haystack.includes(query)) {
      score = 50;
    } else {
      const tokens = query.split(/\s+/).filter(Boolean);
      if (tokens.every((token) => haystack.includes(token))) {
        score = 40;
      }
    }

    if (score > 0) {
      matches.push({ ...component, score, path: pathKey });
    }
  }

  return matches.sort((a, b) => b.score - a.score || a.path.localeCompare(b.path)).slice(0, limit);
}

/** @deprecated Use listLibraryComponents — kept for inventory key name compat */
export async function listLibraryExampleLayouts(): Promise<string[]> {
  const components = await listLibraryComponents();
  return components.map((c) => `${c.type}/${c.folder}`);
}

export async function readLibraryExample(layout: string): Promise<{ acf: string | null; template: string | null }> {
  const [type, folder] = layout.split("/");
  if (!type || !folder) {
    throw new Error("Layout must be type/folder (e.g. content/002).");
  }
  return readLibraryComponent(type, folder);
}

export async function readLibraryReadme(): Promise<string> {
  return (
    (await readIfExists(path.join(COMPONENT_LIBRARY_ROOT, "README.md"))) ??
    `# Matrix component library\n\nExpected at: ${COMPONENT_LIBRARY_ROOT}\n\nInstall via matrix-component-importer plugin or npm run library:sync`
  );
}
