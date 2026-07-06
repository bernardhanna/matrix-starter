import fs from "node:fs/promises";
import path from "node:path";
import { COMPONENT_LIBRARY_ROOT } from "../config.js";

export type LibraryComponentRef = {
  type: string;
  folder: string;
};

const SKIP_TOP_LEVEL = new Set([".git", "scripts", ".github", "README.md"]);

const FLAT_TYPES = new Set(["custom-post-types", "taxonomies", "gallery"]);

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

    if (FLAT_TYPES.has(type)) {
      for (const file of await dirEntries(typePath)) {
        if (file.endsWith(".php")) {
          components.push({ type, folder: file.replace(/\.php$/, "") });
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
          components.push({ type, folder });
        }
      } catch {
        /* skip */
      }
    }
  }

  return components.sort((a, b) => a.type.localeCompare(b.type) || a.folder.localeCompare(b.folder));
}

export async function readLibraryComponent(
  type: string,
  folder: string,
): Promise<{ acf: string | null; template: string | null }> {
  const safeType = type.trim();
  const safeFolder = folder.trim();
  if (!/^[a-z0-9-]+$/.test(safeType) || !/^[a-zA-Z0-9_-]+$/.test(safeFolder)) {
    throw new Error("Invalid library component reference.");
  }

  const base = path.join(COMPONENT_LIBRARY_ROOT, safeType);
  const variantDir = FLAT_TYPES.has(safeType) ? base : path.join(base, safeFolder);

  let acf: string | null = null;
  let template: string | null = null;

  if (FLAT_TYPES.has(safeType)) {
    template = await readIfExists(path.join(base, `${safeFolder}.php`));
    return { acf: null, template };
  }

  for (const file of await dirEntries(variantDir)) {
    if (!file.endsWith(".php")) {
      continue;
    }
    if (file.startsWith("acf_")) {
      acf = await readIfExists(path.join(variantDir, file));
    } else {
      template = await readIfExists(path.join(variantDir, file));
    }
  }

  if (!acf && !template) {
    throw new Error(`No library component at ${safeType}/${safeFolder}`);
  }

  return { acf, template };
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
