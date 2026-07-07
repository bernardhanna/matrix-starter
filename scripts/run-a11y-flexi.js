#!/usr/bin/env node
/**
 * Accessibility scan for the /flexi/ review page.
 *
 * Usage:
 *   npm run test:a11y:flexi
 *   npm run test:a11y:flexi -- --layout=content_002
 *   node scripts/run-a11y-flexi.js http://localhost:8080/ --layout=faq
 */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');
const AxeBuilder = require('@axe-core/playwright').default;
require('dotenv').config({ path: '.env' });

const args = process.argv.slice(2);
const cliUrl = args.find((arg) => arg.startsWith('http'));
const layoutArg = args.find((arg) => arg.startsWith('--layout='));
const layout = layoutArg ? layoutArg.split('=')[1] : '';

const base = cliUrl || process.env.BASE_URL || process.env.WP_HOME || 'http://localhost:10014/';
const baseUrl = new URL(base).href.replace(/\/$/, '');
const flexiUrl = `${baseUrl}/flexi/`;

const reportDir = path.join('tests', 'a11y-report');
const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
const reportPath = path.join(reportDir, `a11y-flexi${layout ? `-${layout}` : ''}-${timestamp}.json`);

const impactPenalty = { critical: 10, serious: 7, moderate: 3, minor: 1 };
const minScore = 80;

async function run() {
  fs.mkdirSync(reportDir, { recursive: true });
  console.log(`♿ Flexi accessibility scan: ${flexiUrl}${layout ? ` (layout: ${layout})` : ''}`);

  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    const response = await page.goto(flexiUrl, { waitUntil: 'networkidle', timeout: 45000 });
    if (!response || !response.ok()) {
      throw new Error(`Navigation failed (${response ? response.status() : 'no response'})`);
    }

    if (layout) {
      const selector = `[id*="${layout}"], [class*="${layout}"]`;
      const count = await page.locator(selector).count();
      if (count === 0) {
        console.warn(`⚠️ No element matched ${selector} — scanning full /flexi/ page. Add block to review page first.`);
      }
    }

    const axe = new AxeBuilder({ page })
      .exclude('iframe[src*="youtube.com"]')
      .exclude('#movie_player')
      .disableRules(['aria-prohibited-attr']);

    if (layout) {
      axe.include(`[id*="${layout}"], [class*="${layout}"]`);
    }

    const results = await axe.analyze();
    const penalty = results.violations.reduce((sum, v) => sum + (impactPenalty[v.impact] || 3), 0);
    const score = Math.max(0, Math.min(100, 100 - penalty));

    const payload = {
      mode: 'flexi',
      layout: layout || null,
      url: flexiUrl,
      scannedAt: new Date().toISOString(),
      minScoreRequired: minScore,
      score,
      violationCount: results.violations.length,
      violations: results.violations.map((v) => ({
        id: v.id,
        impact: v.impact,
        description: v.description,
        help: v.help,
        helpUrl: v.helpUrl,
        nodes: v.nodes.map((n) => ({
          target: n.target,
          html: n.html,
          failureSummary: n.failureSummary,
        })),
      })),
    };

    fs.writeFileSync(reportPath, JSON.stringify(payload, null, 2));
    console.log(`Report written to ${reportPath}`);
    console.log(`Score ${score}/100 (${results.violations.length} violation(s))`);

    if (score < minScore) {
      console.error(`❌ Below minimum score ${minScore}`);
      process.exit(1);
    }

    if (results.violations.length > 0) {
      console.warn(`⚠️ ${results.violations.length} violation(s) but score ≥ ${minScore}`);
    } else {
      console.log(`✅ No axe violations on flexi page${layout ? ` for ${layout}` : ''}`);
    }
  } finally {
    await browser.close();
  }
}

run().catch((error) => {
  console.error('❌ Flexi accessibility scan failed:', error.message);
  process.exit(1);
});
