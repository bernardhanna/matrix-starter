import fs from "node:fs/promises";
import path from "node:path";
import { COMPONENT_LIBRARY_ROOT, THEME_ROOT } from "../config.js";
import {
  componentLibraryExists,
  readLibraryComponent,
  resolveComponentFiles,
} from "./library.js";
import { getComponentTypeConfig, resolveThemeDestPaths } from "./component-map.js";

export type CopyPlanItem = {
  source: string;
  destination: string;
  action: "copy" | "skip_exists";
};

export type CopyFromLibraryResult = {
  success: boolean;
  type: string;
  folder: string;
  dryRun: boolean;
  copied: string[];
  skipped: string[];
  plan: CopyPlanItem[];
  themeDestinations: { acf?: string; template?: string };
  hint: string;
  message?: string;
};

async function fileExists(filePath: string): Promise<boolean> {
  try {
    await fs.access(filePath);
    return true;
  } catch {
    return false;
  }
}

export async function copyFromLibrary(input: {
  type: string;
  folder: string;
  dryRun?: boolean;
  overwrite?: boolean;
}): Promise<CopyFromLibraryResult> {
  const type = input.type.trim();
  const folder = input.folder.trim();
  const dryRun = input.dryRun ?? false;
  const overwrite = input.overwrite ?? false;

  if (!(await componentLibraryExists())) {
    return {
      success: false,
      type,
      folder,
      dryRun,
      copied: [],
      skipped: [],
      plan: [],
      themeDestinations: {},
      hint: "Run npm run library:sync or activate matrix-component-importer.",
      message: `Component library not found at ${COMPONENT_LIBRARY_ROOT}`,
    };
  }

  const files = await resolveComponentFiles(type, folder);
  if (!files.acfPath && !files.templatePath) {
    return {
      success: false,
      type,
      folder,
      dryRun,
      copied: [],
      skipped: [],
      plan: [],
      themeDestinations: {},
      hint: "Use find_library_component to locate valid type/folder pairs.",
      message: `No component files at ${type}/${folder}`,
    };
  }

  const acfBasename = files.acfPath ? path.basename(files.acfPath) : null;
  const templateBasename = files.templatePath ? path.basename(files.templatePath) : null;
  const themeDestinations = resolveThemeDestPaths(type, acfBasename, templateBasename, folder);
  const config = getComponentTypeConfig(type);

  const plan: CopyPlanItem[] = [];
  const copied: string[] = [];
  const skipped: string[] = [];

  const queue: { source: string; destRelative: string }[] = [];

  if (files.acfPath && config.hasAcf && themeDestinations.acf) {
    queue.push({ source: files.acfPath, destRelative: themeDestinations.acf });
  }

  if (files.templatePath && themeDestinations.template) {
    queue.push({ source: files.templatePath, destRelative: themeDestinations.template });
  }

  if (queue.length === 0) {
    return {
      success: false,
      type,
      folder,
      dryRun,
      copied: [],
      skipped: [],
      plan: [],
      themeDestinations,
      hint: "Check component type map for this library category.",
      message: `No theme destinations resolved for ${type}/${folder}`,
    };
  }

  for (const item of queue) {
    const destAbsolute = path.join(THEME_ROOT, item.destRelative);
    const exists = await fileExists(destAbsolute);

    if (exists && !overwrite) {
      plan.push({
        source: path.relative(COMPONENT_LIBRARY_ROOT, item.source),
        destination: item.destRelative,
        action: "skip_exists",
      });
      skipped.push(item.destRelative);
      continue;
    }

    plan.push({
      source: path.relative(COMPONENT_LIBRARY_ROOT, item.source),
      destination: item.destRelative,
      action: "copy",
    });

    if (!dryRun) {
      await fs.mkdir(path.dirname(destAbsolute), { recursive: true });
      await fs.copyFile(item.source, destAbsolute);
      copied.push(item.destRelative);
    }
  }

  const wouldCopy = plan.filter((p) => p.action === "copy");
  const success = dryRun ? wouldCopy.length > 0 : copied.length > 0;

  const hint =
    skipped.length > 0 && copied.length === 0 && !dryRun
      ? "Destination files exist. Pass overwrite:true to replace, or edit the theme files directly."
      : dryRun
        ? "Re-run with dryRun:false to write files. Edit only the copied drop-in paths."
        : "Imported finished component. For new flexi blocks, reference library patterns with get_library_component instead of copying.";

  return {
    success,
    type,
    folder,
    dryRun,
    copied,
    skipped,
    plan,
    themeDestinations,
    hint,
    message: dryRun
      ? `Dry run: ${plan.filter((p) => p.action === "copy").length} file(s) would be copied.`
      : copied.length > 0
        ? `Copied ${copied.length} file(s) from library.`
        : skipped.length > 0
          ? "All destination files already exist."
          : undefined,
  };
}

export async function getLibraryComponentDetail(type: string, folder: string) {
  const block = await readLibraryComponent(type, folder);
  const files = await resolveComponentFiles(type, folder);
  const acfBasename = files.acfPath ? path.basename(files.acfPath) : null;
  const templateBasename = files.templatePath ? path.basename(files.templatePath) : null;

  return {
    type,
    folder,
    acf: block.acf,
    template: block.template,
    themeDestinations: resolveThemeDestPaths(type, acfBasename, templateBasename, folder),
    libraryPaths: {
      acf: files.acfPath ? path.relative(COMPONENT_LIBRARY_ROOT, files.acfPath) : null,
      template: files.templatePath
        ? path.relative(COMPONENT_LIBRARY_ROOT, files.templatePath)
        : null,
    },
  };
}
