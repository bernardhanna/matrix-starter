#!/usr/bin/env node

import { validateFlexiBlocks } from "./lib/flexi.js";
import { validateThemeStructure } from "./lib/structure.js";

async function main(): Promise<void> {
  const command = process.argv[2];

  switch (command) {
    case "validate-structure": {
      const result = await validateThemeStructure();
      console.log(JSON.stringify(result, null, 2));
      process.exit(result.valid ? 0 : 1);
      break;
    }
    case "validate-flexi": {
      const result = await validateFlexiBlocks();
      console.log(JSON.stringify(result, null, 2));
      process.exit(result.valid ? 0 : 1);
      break;
    }
    default:
      console.error("Usage: matrix-starter-mcp-cli <validate-structure|validate-flexi>");
      process.exit(2);
  }
}

main().catch((error) => {
  console.error(error);
  process.exit(1);
});
