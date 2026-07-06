import { formatCommandResult, runCommand } from "./exec.js";
import { THEME_ROOT } from "../config.js";

export type LibraryExportKind =
  | "flexi"
  | "hero"
  | "footer"
  | "header"
  | "blog"
  | "cpt"
  | "taxonomy"
  | "theme-option";

export async function runLibrarySync(): Promise<{
  success: boolean;
  output: string;
}> {
  const result = await runCommand("npm", ["run", "library:sync"], {
    cwd: THEME_ROOT,
    timeoutMs: 5 * 60 * 1000,
  });

  return {
    success: result.exitCode === 0,
    output: formatCommandResult(result),
  };
}

export async function runLibraryExport(input: {
  kind: LibraryExportKind;
  slug: string;
  variant?: string;
  skipScreenshot?: boolean;
}): Promise<{ success: boolean; output: string }> {
  const args = ["run", "library:export", "--"];

  if (input.kind === "flexi") {
    args.push(`--kind=flexi`, `--layout=${input.slug}`);
  } else {
    args.push(`--kind=${input.kind}`, `--slug=${input.slug}`);
  }

  if (input.variant) {
    args.push(`--variant=${input.variant}`);
  }

  if (input.skipScreenshot) {
    args.push("--no-screenshot");
  }

  const result = await runCommand("npm", args, {
    cwd: THEME_ROOT,
    timeoutMs: 10 * 60 * 1000,
  });

  return {
    success: result.exitCode === 0,
    output: formatCommandResult(result),
  };
}
