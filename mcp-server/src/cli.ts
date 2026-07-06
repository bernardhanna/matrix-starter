#!/usr/bin/env node

import { validateFlexiA11yConventions } from "./lib/a11y-conventions.js";
import { validateFlexiBlocks } from "./lib/flexi.js";
import { validateThemeStructure } from "./lib/structure.js";

async function main(): Promise<void> {
  const command = process.argv[2];
  const layoutFlag = process.argv.find((a) => a.startsWith("--layout="));
  const layout = layoutFlag?.split("=")[1];

  switch (command) {
    case "validate-structure": {
      const result = await validateThemeStructure();
      console.log(JSON.stringify(result, null, 2));
      process.exit(result.valid ? 0 : 1);
    }
    case "validate-flexi": {
      const result = await validateFlexiBlocks();
      console.log(JSON.stringify(result, null, 2));
      process.exit(result.valid ? 0 : 1);
    }
    case "validate-a11y-conventions": {
      const result = await validateFlexiA11yConventions(layout ? { layout } : undefined);
      console.log(JSON.stringify(result, null, 2));
      process.exit(result.valid ? 0 : 1);
    }
    default:
      console.error("Usage: matrix-starter-mcp-cli <validate-structure|validate-flexi|validate-a11y-conventions> [--layout=name]");
      process.exit(2);
  }
}

main().catch((error) => {
  console.error(error);
  process.exit(1);
});
