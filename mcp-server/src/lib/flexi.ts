import fs from "node:fs/promises";
import path from "node:path";
import {
  PATHS,
  THEME_ROOT,
  layoutFromAcfFilename,
  layoutFromFlexiFilename,
} from "../config.js";

function relativeThemePath(absolutePath: string): string {
  return path.relative(THEME_ROOT, absolutePath).replace(/\\/g, "/");
}

export type FlexiLayoutStatus = {
  layout: string;
  acfFile: string | null;
  templateFile: string | null;
  valid: boolean;
  issues: string[];
};

async function listPhpBasenames(dir: string): Promise<string[]> {
  try {
    const entries = await fs.readdir(dir);
    return entries.filter((name) => name.endsWith(".php")).sort();
  } catch {
    return [];
  }
}

export async function getFlexiInventory(): Promise<FlexiLayoutStatus[]> {
  const acfFiles = await listPhpBasenames(PATHS.acfBlocks);
  const templateFiles = await listPhpBasenames(PATHS.flexiTemplates);

  const acfByLayout = new Map<string, string>();
  for (const file of acfFiles) {
    const layout = layoutFromAcfFilename(file);
    if (layout) {
      acfByLayout.set(layout, file);
    }
  }

  const templateByLayout = new Map<string, string>();
  for (const file of templateFiles) {
    const layout = layoutFromFlexiFilename(file);
    if (layout) {
      templateByLayout.set(layout, file);
    }
  }

  const layouts = new Set([...acfByLayout.keys(), ...templateByLayout.keys()]);

  return [...layouts]
    .sort()
    .map((layout) => {
      const acfFile = acfByLayout.get(layout) ?? null;
      const templateFile = templateByLayout.get(layout) ?? null;
      const issues: string[] = [];

      if (!acfFile) {
        issues.push(`Missing ACF definition: acf-fields/partials/blocks/acf_${layout}.php`);
      }

      if (!templateFile) {
        issues.push(`Missing template: template-parts/flexi/${layout}.php`);
      }

      return {
        layout,
        acfFile,
        templateFile,
        valid: issues.length === 0,
        issues,
      };
    });
}

export async function validateFlexiBlocks(): Promise<{
  valid: boolean;
  layouts: FlexiLayoutStatus[];
  summary: string;
}> {
  const layouts = await getFlexiInventory();
  const invalid = layouts.filter((layout) => !layout.valid);

  const summary =
    invalid.length === 0
      ? `All ${layouts.length} flexi layout(s) have matching ACF + template files.`
      : `${invalid.length} of ${layouts.length} layout(s) are missing a paired file.`;

  return {
    valid: invalid.length === 0,
    layouts,
    summary,
  };
}

export type ScaffoldFlexiBlockInput = {
  layout: string;
  label: string;
  overwrite?: boolean;
};

export type ScaffoldFlexiBlockResult = {
  created: string[];
  skipped: string[];
  hint: string;
  allowedPaths: string[];
};

function toLabel(layout: string): string {
  return layout
    .split("_")
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join(" ");
}


function buildAcfPartial(layout: string, label: string): string {
  const varName = layout.replace(/[^a-zA-Z0-9_]/g, "_");
  return `<?php

use StoutLogic\\AcfBuilder\\FieldsBuilder;

$${varName} = new FieldsBuilder('${layout}', [
    'label' => '${label.replace(/'/g, "\\'")}',
]);

$${varName}
  ->addTab('Content', ['placement' => 'top'])
    ->addWysiwyg('text_content', [
        'label' => 'Content',
        'instructions' => 'Enter the section content.',
        'media_upload' => 0,
        'toolbar' => 'full',
    ])

  ->addTab('Design', ['placement' => 'top'])
    ->addColorPicker('background_color', [
        'label' => 'Background colour',
        'instructions' => 'Optional section background.',
    ])

  ->addTab('Layout', ['placement' => 'top'])
    ->addRepeater('padding_settings', [
        'label' => 'Padding Settings',
        'button_label' => 'Add Screen Size Padding',
    ])
      ->addSelect('screen_size', [
          'label' => 'Screen Size',
          'choices' => [
              'xxs' => 'xxs',
              'xs' => 'xs',
              'mob' => 'mob',
              'sm' => 'sm',
              'md' => 'md',
              'lg' => 'lg',
              'xl' => 'xl',
              'xxl' => 'xxl',
              'ultrawide' => 'ultrawide',
          ],
      ])
      ->addNumber('padding_top', ['label' => 'Padding Top', 'min' => 0, 'max' => 20, 'step' => 0.1, 'append' => 'rem'])
      ->addNumber('padding_bottom', ['label' => 'Padding Bottom', 'min' => 0, 'max' => 20, 'step' => 0.1, 'append' => 'rem'])
    ->endRepeater();

return $${varName};
`;
}

function buildFlexiTemplate(layout: string): string {
  return `<?php
$section_id = '${layout}-' . wp_generate_uuid4();
$text_content = get_sub_field('text_content');
$background_color = get_sub_field('background_color');

$padding_classes = [];
if (have_rows('padding_settings')) {
  while (have_rows('padding_settings')) {
    the_row();
    $screen = get_sub_field('screen_size');
    $pt = get_sub_field('padding_top');
    $pb = get_sub_field('padding_bottom');
    if ($screen && $pt !== null && $pt !== '') {
      $padding_classes[] = "{$screen}:pt-[{$pt}rem]";
    }
    if ($screen && $pb !== null && $pb !== '') {
      $padding_classes[] = "{$screen}:pb-[{$pb}rem]";
    }
  }
}

$section_style = $background_color ? 'background-color:' . esc_attr($background_color) . ';' : '';
?>

<section
  id="<?php echo esc_attr($section_id); ?>"
  class="relative flex overflow-hidden bg-white font-montserrat"
  role="region"
  aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
  <?php if ($section_style) : ?>style="<?php echo esc_attr($section_style); ?>"<?php endif; ?>
>
  <div class="flex flex-col items-center w-full mx-auto max-w-container max-lg:px-5 <?php echo esc_attr(implode(' ', $padding_classes)); ?>">
    <div class="theme-prose wp_editor w-full">
      <div class="entry-content">
        <?php if ($text_content) : ?>
          <?php echo wp_kses_post($text_content); ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
`;
}

export async function scaffoldFlexiBlock(
  input: ScaffoldFlexiBlockInput,
): Promise<ScaffoldFlexiBlockResult> {
  const layout = input.layout.trim();
  if (!/^[a-z][a-z0-9_]*$/.test(layout)) {
    throw new Error(
      "Layout must be lowercase snake_case and start with a letter (e.g. content_002).",
    );
  }

  const label = input.label.trim() || toLabel(layout);
  const acfPath = path.join(PATHS.acfBlocks, `acf_${layout}.php`);
  const templatePath = path.join(PATHS.flexiTemplates, `${layout}.php`);
  const allowedPaths = [
    relativeThemePath(acfPath),
    relativeThemePath(templatePath),
  ];
  const created: string[] = [];
  const skipped: string[] = [];

  await fs.mkdir(PATHS.acfBlocks, { recursive: true });
  await fs.mkdir(PATHS.flexiTemplates, { recursive: true });

  for (const [filePath, contents] of [
    [acfPath, buildAcfPartial(layout, label)],
    [templatePath, buildFlexiTemplate(layout)],
  ] as const) {
    let exists = false;
    try {
      await fs.access(filePath);
      exists = true;
    } catch {
      exists = false;
    }

    if (exists && !input.overwrite) {
      skipped.push(relativeThemePath(filePath));
      continue;
    }

    await fs.writeFile(filePath, contents, "utf8");
    created.push(relativeThemePath(filePath));
  }

  const hint =
    skipped.length > 0 && created.length === 0
      ? "Files already exist. Pass overwrite:true to replace. Do not create files outside allowedPaths."
      : "Edit only the two files in allowedPaths. Do not add requires, inc/ partials, or loader scripts.";

  return { created, skipped, hint, allowedPaths };
}
