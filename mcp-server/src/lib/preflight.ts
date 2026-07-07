import { validateFlexiA11yConventions } from "./a11y-conventions.js";
import { getFlexiInventory, validateFlexiBlocks } from "./flexi.js";
import { validateThemeStructure } from "./structure.js";

export async function preflightFlexiBlock(layout?: string) {
  const [structure, flexi, a11y] = await Promise.all([
    validateThemeStructure(),
    validateFlexiBlocks(),
    validateFlexiA11yConventions(layout ? { layout } : undefined),
  ]);

  const layoutStatus = layout ? flexi.layouts.find((item) => item.layout === layout) : undefined;
  const layoutMissing = layout ? !layoutStatus : false;
  const layoutInvalid = layoutStatus ? !layoutStatus.valid : false;

  const checks = {
    structure: structure.valid,
    flexiParity: layout ? (layoutStatus?.valid ?? false) : flexi.valid,
    a11yConventions: a11y.valid,
  };

  const valid =
    structure.valid && checks.flexiParity && a11y.valid && !layoutMissing;

  const blockers: string[] = [];
  if (!structure.valid) {
    blockers.push(...structure.errors.map((e) => e.message));
  }
  if (layoutMissing) {
    blockers.push(`Layout "${layout}" not found in flexi inventory.`);
  } else if (layoutInvalid && layoutStatus) {
    blockers.push(...layoutStatus.issues);
  } else if (!layout && !flexi.valid) {
    blockers.push(flexi.summary);
  }
  if (!a11y.valid) {
    for (const result of a11y.results) {
      for (const error of result.errors) {
        blockers.push(`${result.layout}: ${error.message}`);
      }
    }
  }

  return {
    valid,
    layout: layout ?? null,
    checks,
    structure: {
      valid: structure.valid,
      errorCount: structure.errors.length,
      warningCount: structure.warnings.length,
      summary: structure.summary,
    },
    flexi: {
      valid: flexi.valid,
      summary: flexi.summary,
      layout: layoutStatus ?? null,
    },
    a11y: {
      valid: a11y.valid,
      summary: a11y.summary,
      scannedLayouts: a11y.results.map((r) => r.layout),
    },
    blockers,
    nextSteps: valid
      ? [
          "Run seed_flexi_review_block before validate_flexi_a11y (runtime axe).",
          "Run theme_build if Tailwind classes changed.",
        ]
      : ["Fix blockers above, then re-run preflight_flexi_block."],
  };
}

export async function preflightLayoutExists(layout: string): Promise<boolean> {
  const layouts = await getFlexiInventory();
  return layouts.some((item) => item.layout === layout && item.valid);
}
