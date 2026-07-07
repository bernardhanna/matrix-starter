import fs from "node:fs/promises";
import path from "node:path";
import { PATHS, THEME_ROOT, layoutFromFlexiFilename } from "../config.js";

export type A11yViolation = {
  severity: "error" | "warning";
  code: string;
  message: string;
  path: string;
  line?: number;
};

export type FlexiA11yResult = {
  valid: boolean;
  layout: string;
  templatePath: string;
  errors: A11yViolation[];
  warnings: A11yViolation[];
};

async function listFlexiTemplates(layout?: string): Promise<{ layout: string; path: string }[]> {
  let files: string[];
  try {
    files = (await fs.readdir(PATHS.flexiTemplates))
      .filter((name) => name.endsWith(".php"))
      .sort();
  } catch {
    return [];
  }

  const items: { layout: string; path: string }[] = [];
  for (const file of files) {
    const slug = layoutFromFlexiFilename(file);
    if (!slug) continue;
    if (layout && slug !== layout) continue;
    items.push({ layout: slug, path: path.join(PATHS.flexiTemplates, file) });
  }
  return items;
}

function lineNumber(source: string, index: number): number {
  return source.slice(0, index).split("\n").length;
}

function checkTemplate(layout: string, templatePath: string, source: string): FlexiA11yResult {
  const relativePath = path.relative(THEME_ROOT, templatePath).replace(/\\/g, "/");
  const errors: A11yViolation[] = [];
  const warnings: A11yViolation[] = [];

  const add = (severity: "error" | "warning", code: string, message: string, index = 0) => {
    const violation: A11yViolation = {
      severity, code, message, path: relativePath,
      line: index > 0 ? lineNumber(source, index) : undefined,
    };
    (severity === "error" ? errors : warnings).push(violation);
  };

  if (source.includes("<?=")) {
    add("error", "php_shorthand", "Do not use <?= shorthand PHP in flexi templates.");
  }

  if (/have_rows\s*\(\s*[\'"][^\'"]+[\'"]\s*\)/.test(source) && !source.includes("padding_settings")) {
    add("error", "outer_have_rows", "Do not wrap flexi templates in outer have_rows() loops.");
  }

  const hasSection = /<section\b/i.test(source);
  if (!hasSection) {
    add("warning", "missing_section", "Prefer a <section> wrapper with role=\"region\".");
  } else {
    if (!/role\s*=\s*[\'"]region[\'"]/i.test(source)) {
      add("error", "section_missing_role", "<section> must include role=\"region\".");
    }
    if (!/aria-labelledby\s*=/i.test(source)) {
      add("error", "section_missing_labelledby", "<section> must include aria-labelledby.");
    }
  }

  if (/uniqid\s*\(/.test(source) && !/wp_generate_uuid4\s*\(/.test(source)) {
    add("warning", "legacy_section_id", "Prefer wp_generate_uuid4() over uniqid() for section IDs.");
  }

  if (!/\$section_id\s*=/.test(source) && hasSection) {
    add("warning", "missing_section_id", "Declare a unique $section_id for scoped styles and ARIA.");
  }

  for (const match of source.matchAll(/<\?php\s+echo\s+([^;]+);/g)) {
    const expr = match[1];
    if (
      !/esc_(html|attr|url|js)\s*\(/.test(expr) &&
      !/wp_kses_post\s*\(/.test(expr) &&
      !/wp_get_attachment_image\s*\(/.test(expr) &&
      !/matrix_flexi_heading_html\s*\(/.test(expr) &&
      !/matrix_btn_classes\s*\(/.test(expr) &&
      !/matrix_content_container_classes\s*\(/.test(expr) &&
      !/implode\s*\(/.test(expr) &&
      !/esc_attr\s*\(/.test(expr)
    ) {
      add("error", "unescaped_echo", `Escape dynamic output: ${expr.trim().slice(0, 60)}`, match.index ?? 0);
    }
  }

  if (/<img\b(?![^>]*\balt\s*=)/i.test(source)) {
    add("warning", "img_missing_alt", "Images must include alt text.");
  }

  for (const match of source.matchAll(/target\s*=\s*[\'"]_blank[\'"]/gi)) {
    const slice = source.slice(match.index ?? 0, (match.index ?? 0) + 400);
    if (!/rel\s*=\s*[\'"][^\'"]*noopener/i.test(slice)) {
      add("error", "blank_without_noopener", "target=\"_blank\" links need rel=\"noopener noreferrer\".", match.index ?? 0);
    }
  }

  const hasCta = /content_button|btn-primary|flexi-cta-/i.test(source) && /<a\b[^>]*href=/i.test(source);
  if (hasCta) {
    if (!/\.btn\b|btn-theme-|btn-primary/.test(source)) {
      add("warning", "cta_missing_btn_class", "CTA links should use matrix_btn_classes() / .btn.");
    }
    if (!/:focus-visible/i.test(source) && !/a11y-focus/.test(source)) {
      add("warning", "cta_missing_focus_style", "CTA links need scoped :focus-visible styles.");
    }
  }

  if (/x-data|@click|accordion|faq-item/i.test(source) && !/aria-expanded/i.test(source)) {
    add("warning", "interactive_missing_aria_expanded", "Interactive UI should expose aria-expanded.");
  }

  if (/get_field\s*\(/.test(source)) {
    add("error", "get_field_in_flexi", "Use get_sub_field() in flexi templates, not get_field().");
  }

  return { valid: errors.length === 0, layout, templatePath: relativePath, errors, warnings };
}

export async function validateFlexiA11yConventions(input?: { layout?: string }) {
  const layout = input?.layout?.trim();
  if (layout && !/^[a-z][a-z0-9_]*$/.test(layout)) throw new Error("Invalid layout slug.");

  const templates = await listFlexiTemplates(layout);
  if (templates.length === 0) {
    throw new Error(layout ? `No template: template-parts/flexi/${layout}.php` : "No flexi templates found.");
  }

  const results: FlexiA11yResult[] = [];
  for (const item of templates) {
    results.push(checkTemplate(item.layout, item.path, await fs.readFile(item.path, "utf8")));
  }

  const invalid = results.filter((r) => !r.valid);
  const totalWarnings = results.reduce((s, r) => s + r.warnings.length, 0);
  return {
    valid: invalid.length === 0,
    results,
    summary: invalid.length === 0
      ? `All ${results.length} template(s) pass a11y conventions (${totalWarnings} warning(s)).`
      : `${invalid.length} of ${results.length} template(s) failed a11y convention checks.`,
  };
}
