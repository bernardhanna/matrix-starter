#!/usr/bin/env node

import { McpServer } from "@modelcontextprotocol/sdk/server/mcp.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";
import {
  CallToolRequestSchema,
  ListResourcesRequestSchema,
  ListToolsRequestSchema,
  ReadResourceRequestSchema,
} from "@modelcontextprotocol/sdk/types.js";
import { z } from "zod";
import { THEME_ROOT } from "./config.js";
import {
  getFlexiInventory,
  scaffoldFlexiBlock,
  validateFlexiBlocks,
} from "./lib/flexi.js";
import { formatCommandResult, runCommand, runNpmScript } from "./lib/exec.js";
import { getThemeStatus, readThemeDoc } from "./lib/status.js";
import { readThemeTokens, updateThemeTokens } from "./lib/tokens.js";
import { validateFlexiA11yConventions } from "./lib/a11y-conventions.js";
import {
  listLibraryComponents,
  readLibraryComponent,
  readLibraryExample,
  readLibraryReadme,
} from "./lib/library.js";
import {
  getThemeInventory,
  listReferenceBlockLayouts,
  readReferenceBlock,
  validateThemeStructure,
} from "./lib/structure.js";
import fs from "node:fs/promises";
import { PATHS } from "./config.js";

const server = new McpServer(
  {
    name: "matrix-starter",
    version: "0.1.0",
  },
  {
    capabilities: {
      tools: {},
      resources: {},
    },
  },
);

const TOOLS = [
  {
    name: "theme_status",
    description:
      "Report Matrix Starter repo health: dist assets, node_modules, .env, flexi layout parity.",
    inputSchema: {
      type: "object",
      properties: {},
      additionalProperties: false,
    },
  },
  {
    name: "list_flexi_layouts",
    description:
      "List flexi block layouts and whether each has a matching ACF definition and PHP template.",
    inputSchema: {
      type: "object",
      properties: {},
      additionalProperties: false,
    },
  },
  {
    name: "validate_flexi_blocks",
    description:
      "Validate that every acf-fields/partials/blocks/acf_{layout}.php has template-parts/flexi/{layout}.php.",
    inputSchema: {
      type: "object",
      properties: {},
      additionalProperties: false,
    },
  },
  {
    name: "scaffold_flexi_block",
    description:
      "Create a new flexi block pair (ACF Builder partial + template-parts/flexi template) from the theme starter pattern.",
    inputSchema: {
      type: "object",
      properties: {
        layout: {
          type: "string",
          description: "Layout slug, lowercase snake_case (e.g. content_002).",
        },
        label: {
          type: "string",
          description: "Human-readable block label shown in WordPress admin.",
        },
        overwrite: {
          type: "boolean",
          description: "Overwrite existing files when true. Defaults to false.",
        },
      },
      required: ["layout"],
      additionalProperties: false,
    },
  },
  {
    name: "validate_theme_structure",
    description:
      "Validate drop-in folder contract: flexi/hero parity, forbidden paths, suspicious functions.php requires.",
    inputSchema: { type: "object", properties: {}, additionalProperties: false },
  },
  {
    name: "list_theme_inventory",
    description:
      "List flexi layouts, hero files, theme option tabs, CPTs, taxonomies, and helper utils.",
    inputSchema: { type: "object", properties: {}, additionalProperties: false },
  },
  {
    name: "validate_flexi_a11y_conventions",
    description:
      "Static WCAG/convention checks on flexi PHP templates (aria, escaping, CTA focus) before finalize.",
    inputSchema: {
      type: "object",
      properties: {
        layout: { type: "string", description: "Optional layout slug; omit to scan all flexi templates." },
      },
      additionalProperties: false,
    },
  },
  {
    name: "validate_flexi_a11y",
    description:
      "Run axe accessibility scan on /flexi/ review page (requires BASE_URL in .env and block on review page).",
    inputSchema: {
      type: "object",
      properties: {
        layout: { type: "string", description: "Optional layout slug to scope the scan." },
        baseUrl: { type: "string", description: "Optional site URL override." },
      },
      additionalProperties: false,
    },
  },
  {
    name: "get_theme_tokens",
    description: "Read semantic THEME_TOKENS from tailwind.config.js.",
    inputSchema: {
      type: "object",
      properties: {},
      additionalProperties: false,
    },
  },
  {
    name: "update_theme_tokens",
    description:
      "Patch one or more semantic token values in tailwind.config.js. Run theme_build after changing colors.",
    inputSchema: {
      type: "object",
      properties: {
        patches: {
          type: "array",
          items: {
            type: "object",
            properties: {
              group: { type: "string", description: "Token group (brand, text, surface, ...)." },
              key: { type: "string", description: "Token key within the group." },
              value: { type: "string", description: "New token value." },
            },
            required: ["group", "key", "value"],
          },
          minItems: 1,
        },
      },
      required: ["patches"],
      additionalProperties: false,
    },
  },
  {
    name: "theme_build",
    description: "Run npm run build (PostCSS + Webpack) to regenerate dist/ assets.",
    inputSchema: {
      type: "object",
      properties: {},
      additionalProperties: false,
    },
  },
  {
    name: "theme_test",
    description:
      "Run theme test scripts. Supports php, e2e, a11y, links, or full ci pipeline.",
    inputSchema: {
      type: "object",
      properties: {
        suite: {
          type: "string",
          enum: ["php", "e2e", "a11y", "a11y:flexi", "links", "ci"],
          description: "Which npm test script to run. Defaults to php.",
        },
      },
      additionalProperties: false,
    },
  },
] as const;

const RESOURCES = [
  {
    uri: "theme://architecture",
    name: "Matrix Starter architecture",
    description: "High-level map of theme folders and extension points.",
    mimeType: "text/markdown",
  },
  {
    uri: "theme://docs/flexi-blocks-basics",
    name: "Flexi blocks basics",
    description: "How to build ACF flexible content blocks in Matrix Starter.",
    mimeType: "text/markdown",
  },
  {
    uri: "theme://docs/daily-flow",
    name: "Daily development flow",
    description: "Branching, build, and PR workflow for theme development.",
    mimeType: "text/markdown",
  },
  {
    uri: "theme://structure",
    name: "Theme drop-in structure",
    description: "Canonical folder contract for flexi blocks and autoloaded paths.",
    mimeType: "text/markdown",
  },
  {
    uri: "theme://library",
    name: "Theme library",
    description: "wp-content/matrix-component-library reference (install via matrix-component-importer).",
    mimeType: "text/markdown",
  },
] as const;

const architectureMarkdown = `# Matrix Starter architecture

Theme root: \`${THEME_ROOT}\`

## Extension points

| Concern | Location |
|---------|----------|
| Flexi ACF layouts | \`acf-fields/partials/blocks/acf_{layout}.php\` |
| Flexi templates | \`template-parts/flexi/{layout}.php\` |
| Flexi registration | \`acf-fields/partials/flexi.php\` (auto-loads blocks/*.php) |
| Hero fields | \`acf-fields/partials/hero.php\` + \`template-parts/hero/\` |
| Theme options | \`inc/theme-options.php\` + \`inc/theme-options/*.php\` |
| Semantic tokens | \`tailwind.config.js\` → \`THEME_TOKENS\` |
| Built assets | \`dist/\` (generated by \`npm run build\`) |

## Bootstrap

1. \`composer install\`
2. \`npm install\`
3. Copy \`.env.example\` → \`.env\` and set \`WP_PATH\`
4. \`npm run flexi:install\` — clones Matrix plugins, activates theme via WP-CLI
5. Install ACF Pro manually
6. \`npm run build\` or \`npm run dev\`

## Phase 1 MCP scope

Filesystem + npm tooling only. WordPress content/options tooling is planned for Phase 2 (WP-CLI).
`;

server.server.setRequestHandler(ListToolsRequestSchema, async () => ({
  tools: TOOLS.map(({ name, description, inputSchema }) => ({
    name,
    description,
    inputSchema,
  })),
}));

server.server.setRequestHandler(CallToolRequestSchema, async (request) => {
  const { name, arguments: args } = request.params;

  try {
    switch (name) {
      case "theme_status": {
        const status = await getThemeStatus();
        return {
          content: [{ type: "text", text: JSON.stringify(status, null, 2) }],
        };
      }

      case "list_flexi_layouts": {
        const layouts = await getFlexiInventory();
        return {
          content: [{ type: "text", text: JSON.stringify(layouts, null, 2) }],
        };
      }

      case "validate_flexi_blocks": {
        const result = await validateFlexiBlocks();
        return {
          content: [{ type: "text", text: JSON.stringify(result, null, 2) }],
        };
      }

      case "scaffold_flexi_block": {
        const input = z
          .object({
            layout: z.string(),
            label: z.string().optional(),
            overwrite: z.boolean().optional(),
          })
          .parse(args ?? {});

        const result = await scaffoldFlexiBlock({
          layout: input.layout,
          label: input.label ?? "",
          overwrite: input.overwrite ?? false,
        });

        return {
          content: [{ type: "text", text: JSON.stringify(result, null, 2) }],
        };
      }

      case "get_theme_tokens": {
        const tokens = await readThemeTokens();
        return {
          content: [{ type: "text", text: JSON.stringify(tokens, null, 2) }],
        };
      }

      case "update_theme_tokens": {
        const input = z
          .object({
            patches: z.array(
              z.object({
                group: z.string(),
                key: z.string(),
                value: z.string(),
              }),
            ),
          })
          .parse(args ?? {});

        const tokens = await updateThemeTokens(input.patches);
        return {
          content: [
            {
              type: "text",
              text: JSON.stringify(
                {
                  updated: input.patches,
                  tokens,
                  nextStep: "Run theme_build to regenerate dist/*.css",
                },
                null,
                2,
              ),
            },
          ],
        };
      }

      case "theme_build": {
        const result = await runNpmScript("build", { timeoutMs: 15 * 60 * 1000 });
        return {
          content: [{ type: "text", text: formatCommandResult(result) }],
          isError: result.exitCode !== 0,
        };
      }

      case "theme_test": {
        const input = z
          .object({
            suite: z.enum(["php", "e2e", "a11y", "a11y:flexi", "links", "ci"]).optional(),
          })
          .parse(args ?? {});

        const suite = input.suite ?? "php";
        const scriptMap = {
          php: "test:php",
          e2e: "test:e2e",
          a11y: "test:a11y",
          "a11y:flexi": "test:a11y:flexi",
          links: "test:links",
          ci: "ci",
        } as const;

        const result = await runNpmScript(scriptMap[suite], {
          timeoutMs: 30 * 60 * 1000,
        });

        return {
          content: [{ type: "text", text: formatCommandResult(result) }],
          isError: result.exitCode !== 0,
        };
      }


      case "validate_theme_structure": {
        const result = await validateThemeStructure();
        return {
          content: [{ type: "text", text: JSON.stringify(result, null, 2) }],
          isError: !result.valid,
        };
      }


      case "validate_flexi_a11y_conventions": {
        const input = z.object({ layout: z.string().optional() }).parse(args ?? {});
        const result = await validateFlexiA11yConventions(
          input.layout ? { layout: input.layout } : undefined,
        );
        return {
          content: [{ type: "text", text: JSON.stringify(result, null, 2) }],
          isError: !result.valid,
        };
      }

      case "validate_flexi_a11y": {
        const input = z
          .object({ layout: z.string().optional(), baseUrl: z.string().optional() })
          .parse(args ?? {});
        const scriptArgs = ["scripts/run-a11y-flexi.js"];
        if (input.baseUrl) scriptArgs.push(input.baseUrl);
        if (input.layout) scriptArgs.push(`--layout=${input.layout}`);
        const result = await runCommand("node", scriptArgs, { timeoutMs: 10 * 60 * 1000 });
        return {
          content: [{ type: "text", text: formatCommandResult(result) }],
          isError: result.exitCode !== 0,
        };
      }

      case "list_theme_inventory": {
        const inventory = await getThemeInventory();
        const referenceBlocks = await listReferenceBlockLayouts();
        const libraryComponents = await listLibraryComponents();
        return {
          content: [
            {
              type: "text",
              text: JSON.stringify({ ...inventory, referenceBlocks, libraryComponents }, null, 2),
            },
          ],
        };
      }

      default:
        return {
          content: [{ type: "text", text: `Unknown tool: ${name}` }],
          isError: true,
        };
    }
  } catch (error) {
    const message = error instanceof Error ? error.message : String(error);
    return {
      content: [{ type: "text", text: message }],
      isError: true,
    };
  }
});

server.server.setRequestHandler(ListResourcesRequestSchema, async () => {
  const referenceLayouts = await listReferenceBlockLayouts();
  const libraryComponents = await listLibraryComponents();
  const libraryResources = libraryComponents.map(({ type, folder }) => ({
    uri: `theme://library/${type}/${folder}`,
    name: `Library: ${type}/${folder}`,
    description: `ACF + template from wp-content/matrix-component-library/`,
    mimeType: "application/json",
  }));
  const referenceResources = referenceLayouts.map((layout) => ({
    uri: `theme://reference-blocks/${layout}`,
    name: `Reference block: ${layout}`,
    description: `Gold-standard ACF + template pair from reference-blocks/flexi/`,
    mimeType: "application/json",
  }));

  return {
    resources: [
      ...RESOURCES.map(({ uri, name, description, mimeType }) => ({
        uri,
        name,
        description,
        mimeType,
      })),
      ...referenceResources,
      ...libraryResources,
    ],
  };
});

server.server.setRequestHandler(ReadResourceRequestSchema, async (request) => {
  const { uri } = request.params;

  switch (uri) {
    case "theme://architecture":
      return {
        contents: [
          {
            uri,
            mimeType: "text/markdown",
            text: architectureMarkdown,
          },
        ],
      };

    case "theme://docs/flexi-blocks-basics":
      return {
        contents: [
          {
            uri,
            mimeType: "text/markdown",
            text: await readThemeDoc("docs/flexi-blocks-basics.md"),
          },
        ],
      };


    case "theme://structure":
      return {
        contents: [
          {
            uri,
            mimeType: "text/markdown",
            text: await fs.readFile(PATHS.themeStructureDoc, "utf8"),
          },
        ],
      };

    case "theme://library":
      return {
        contents: [
          {
            uri,
            mimeType: "text/markdown",
            text: await readLibraryReadme(),
          },
        ],
      };

    case "theme://docs/daily-flow":
      return {
        contents: [
          {
            uri,
            mimeType: "text/markdown",
            text: await readThemeDoc("docs/wiki/3-daily-flow-for-development.md"),
          },
        ],
      };

    default: {
      const libMatch = /^theme:\/\/library\/([a-z0-9-]+)\/([a-zA-Z0-9_-]+)$/.exec(uri);
      if (libMatch) {
        const block = await readLibraryComponent(libMatch[1], libMatch[2]);
        return {
          contents: [
            {
              uri,
              mimeType: "application/json",
              text: JSON.stringify(block, null, 2),
            },
          ],
        };
      }

      const legacyLibMatch = /^theme:\/\/library\/examples\/(.+)$/.exec(uri);
      if (legacyLibMatch) {
        const block = await readLibraryExample(legacyLibMatch[1]);
        return {
          contents: [
            {
              uri,
              mimeType: "application/json",
              text: JSON.stringify(block, null, 2),
            },
          ],
        };
      }

      const refMatch = /^theme:\/\/reference-blocks\/([a-z][a-z0-9_]*)$/.exec(uri);
      if (refMatch) {
        const block = await readReferenceBlock(refMatch[1]);
        return {
          contents: [
            {
              uri,
              mimeType: "application/json",
              text: JSON.stringify(block, null, 2),
            },
          ],
        };
      }
      throw new Error(`Unknown resource: ${uri}`);
    }
  }
});

async function main(): Promise<void> {
  const transport = new StdioServerTransport();
  await server.connect(transport);
}

main().catch((error) => {
  console.error(error);
  process.exit(1);
});
