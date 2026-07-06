#!/usr/bin/env node
/**
 * Capture a preview.png for a flexi block on the /flexi/ review page.
 *
 * Usage:
 *   node scripts/capture-section-preview.js --layout=content_029 --out=/path/to/preview.png
 *   BASE_URL=http://localhost:10024/ node scripts/capture-section-preview.js --layout=wysiwyg --out=preview.png
 */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');
require('dotenv').config({ path: '.env' });

const args = process.argv.slice(2);
const layoutArg = args.find((a) => a.startsWith('--layout='));
const outArg = args.find((a) => a.startsWith('--out='));
const layout = layoutArg ? layoutArg.split('=')[1] : '';
const outPath = outArg ? outArg.split('=')[1] : 'preview.png';

if (!layout) {
  console.error('Usage: capture-section-preview.js --layout=SLUG --out=preview.png');
  process.exit(1);
}

const base = process.env.BASE_URL || process.env.WP_HOME || 'http://localhost:10014/';
const baseUrl = new URL(base).href.replace(/\/$/, '');
const flexiUrl = `${baseUrl}/flexi/`;

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });

  try {
    await page.goto(flexiUrl, { waitUntil: 'networkidle', timeout: 60000 });

    const section = page.locator('section').filter({
      has: page.locator(`[id*="${layout}"], [aria-labelledby*="${layout}"]`),
    }).first();

    const count = await section.count();
    const target = count > 0 ? section : page.locator('section').last();

    await target.scrollIntoViewIfNeeded();
    await page.waitForTimeout(500);

    fs.mkdirSync(path.dirname(path.resolve(outPath)), { recursive: true });
    await target.screenshot({ path: outPath });

    console.log(`Preview saved: ${outPath}`);
  } finally {
    await browser.close();
  }
})().catch((err) => {
  console.error(err.message);
  process.exit(1);
});
