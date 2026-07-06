import fs from "node:fs/promises";
import path from "node:path";
import { THEME_ROOT } from "../config.js";
import { formatCommandResult, runCommand, type CommandResult } from "./exec.js";

export type ThemeEnv = Record<string, string>;

export async function readThemeEnv(): Promise<ThemeEnv> {
  const env: ThemeEnv = {};
  let raw: string;
  try {
    raw = await fs.readFile(path.join(THEME_ROOT, ".env"), "utf8");
  } catch {
    return env;
  }

  for (const line of raw.split("\n")) {
    const trimmed = line.trim();
    if (!trimmed || trimmed.startsWith("#")) {
      continue;
    }
    const eq = trimmed.indexOf("=");
    if (eq === -1) {
      continue;
    }
    const key = trimmed.slice(0, eq).trim();
    let value = trimmed.slice(eq + 1).trim();
    if (
      (value.startsWith('"') && value.endsWith('"')) ||
      (value.startsWith("'") && value.endsWith("'"))
    ) {
      value = value.slice(1, -1);
    }
    env[key] = value;
  }

  return env;
}

export async function getWpRoot(): Promise<string | null> {
  const env = await readThemeEnv();
  return env.WP_PATH?.trim() || null;
}

export async function runWpCli(
  args: string[],
  options: { timeoutMs?: number } = {},
): Promise<CommandResult> {
  const wpRoot = await getWpRoot();
  if (!wpRoot) {
    throw new Error("WP_PATH is not set in .env — required for WP-CLI tools.");
  }

  return runCommand("wp", ["--path", wpRoot, ...args], {
    cwd: THEME_ROOT,
    timeoutMs: options.timeoutMs ?? 2 * 60 * 1000,
  });
}

export async function seedFlexiReviewBlock(input: {
  layout: string;
  createPage?: boolean;
}): Promise<{
  success: boolean;
  layout: string;
  message: string;
  wpCli?: CommandResult;
}> {
  const layout = input.layout.trim();
  if (!/^[a-z][a-z0-9_]*$/.test(layout)) {
    throw new Error("Layout must be lowercase snake_case.");
  }

  const scriptPath = path.join(THEME_ROOT, "scripts/flexi-seed-review.php");
  const args = ["eval-file", scriptPath, layout];
  if (input.createPage ?? true) {
    args.push("--create-page");
  }

  const result = await runWpCli(args);
  const output = `${result.stdout}\n${result.stderr}`.trim();

  return {
    success: result.exitCode === 0,
    layout,
    message: output || (result.exitCode === 0 ? "Seeded /flexi/ review row." : "WP-CLI failed."),
    wpCli: result,
  };
}

export function formatWpCliResult(result: CommandResult): string {
  return formatCommandResult(result);
}
