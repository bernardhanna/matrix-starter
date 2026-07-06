import fs from "node:fs/promises";
import path from "node:path";
import { PATHS, layoutFromAcfFilename, layoutFromFlexiFilename } from "../config.js";

async function listPhp(dir: string): Promise<string[]> {
  try {
    return (await fs.readdir(dir)).filter((f) => f.endsWith(".php")).sort();
  } catch {
    return [];
  }
}

export async function listLibraryExampleLayouts(): Promise<string[]> {
  const acf = await listPhp(PATHS.libraryExamplesAcfFlexi);
  const tpl = await listPhp(PATHS.libraryExamplesFlexi);
  const layouts = new Set<string>();
  for (const f of acf) {
    const l = layoutFromAcfFilename(f);
    if (l) layouts.add(l);
  }
  for (const f of tpl) {
    const l = layoutFromFlexiFilename(f);
    if (l) layouts.add(l);
  }
  return [...layouts].sort();
}

export async function readLibraryExample(layout: string): Promise<{ acf: string | null; template: string | null }> {
  const safe = layout.trim();
  if (!/^[a-z][a-z0-9_]*$/.test(safe)) throw new Error("Invalid layout slug.");
  let acf: string | null = null;
  let template: string | null = null;
  try { acf = await fs.readFile(path.join(PATHS.libraryExamplesAcfFlexi, `acf_${safe}.php`), "utf8"); } catch { /* */ }
  try { template = await fs.readFile(path.join(PATHS.libraryExamplesFlexi, `${safe}.php`), "utf8"); } catch { /* */ }
  if (!acf && !template) throw new Error(`No library example for layout: ${safe}`);
  return { acf, template };
}
