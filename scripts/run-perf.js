#!/usr/bin/env node
/**
 * Performance scan using the bundled Lighthouse (desktop preset).
 *
 * Shells out to node_modules/.bin/lighthouse per page so it works regardless of
 * Lighthouse's ESM-only module format, then aggregates Performance scores and
 * Core Web Vitals into a single JSON report under tests/perf-report.
 *
 * Usage:
 *   npm run test:perf
 *   npm run test:perf:full
 *   npm run test:perf -- http://localhost:10029/
 */
const fs = require('fs');
const os = require('os');
const path = require('path');
const { execFileSync } = require('child_process');
require('dotenv').config({ path: '.env' });

const mode = process.argv[2] === 'full' ? 'full' : 'quick';
const cliUrl = process.argv[3];
const base = cliUrl || process.env.BASE_URL || process.env.WP_HOME || 'http://localhost:10029/';
const baseUrl = new URL(base).href.replace(/\/$/, '');

const quickPaths = ['/', '/our-donuts/', '/product/midi-sourdough-donuts-box-of-20/'];
const fullPaths = [
  '/',
  '/about-us/',
  '/our-shops/',
  '/our-donuts/',
  '/contact-us/',
  '/product/midi-sourdough-donuts-box-of-20/',
  '/cart/',
];

const pathsToScan = mode === 'full' ? fullPaths : quickPaths;
const reportDir = path.join('tests', 'perf-report');
const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
const reportPath = path.join(reportDir, `perf-${mode}-${timestamp}.json`);

const lighthouseBin = path.join('node_modules', '.bin', 'lighthouse');
const minScore = Number(process.env.PERF_MIN_SCORE || 50);

function runLighthouse(url) {
  const tmp = path.join(os.tmpdir(), `lh-${Date.now()}-${Math.random().toString(36).slice(2)}.json`);
  execFileSync(
    lighthouseBin,
    [
      url,
      '--preset=desktop',
      '--quiet',
      '--output=json',
      `--output-path=${tmp}`,
      '--chrome-flags=--headless=new --no-sandbox',
    ],
    { stdio: ['ignore', 'ignore', 'inherit'] }
  );
  const data = JSON.parse(fs.readFileSync(tmp, 'utf8'));
  fs.unlinkSync(tmp);
  return data;
}

function run() {
  fs.mkdirSync(reportDir, { recursive: true });
  console.log(`Running ${mode} performance scan on ${baseUrl}`);

  const summary = [];
  let failures = 0;

  for (const route of pathsToScan) {
    const target = `${baseUrl}${route}`;
    try {
      console.log(`-> Auditing ${target}`);
      const data = runLighthouse(target);
      const a = data.audits;
      const metric = (id) => (a[id] ? a[id].numericValue : null);
      summary.push({
        url: target,
        performance: Math.round((data.categories.performance.score || 0) * 100),
        lcpMs: Math.round(metric('largest-contentful-paint') || 0),
        fcpMs: Math.round(metric('first-contentful-paint') || 0),
        tbtMs: Math.round(metric('total-blocking-time') || 0),
        cls: Number((metric('cumulative-layout-shift') || 0).toFixed(3)),
        speedIndexMs: Math.round(metric('speed-index') || 0),
      });
    } catch (error) {
      failures += 1;
      summary.push({ url: target, error: error.message, performance: 0 });
    }
  }

  const scored = summary.filter((r) => !r.error);
  const avg = scored.length
    ? Math.round(scored.reduce((s, r) => s + r.performance, 0) / scored.length)
    : 0;
  const lowest = scored.length ? Math.min(...scored.map((r) => r.performance)) : 0;

  const payload = {
    mode,
    baseUrl,
    scannedAt: new Date().toISOString(),
    pagesScanned: pathsToScan.length,
    averageScore: avg,
    lowestScore: lowest,
    minScoreRequired: minScore,
    results: summary,
  };
  fs.writeFileSync(reportPath, JSON.stringify(payload, null, 2));

  console.log(`\nReport written to ${reportPath}`);
  for (const r of summary) {
    if (r.error) {
      console.log(`  ${r.url} — ERROR: ${r.error}`);
    } else {
      console.log(
        `  ${r.url} — perf ${r.performance}/100 | LCP ${r.lcpMs}ms | FCP ${r.fcpMs}ms | TBT ${r.tbtMs}ms | CLS ${r.cls}`
      );
    }
  }
  console.log(`\nAverage performance: ${avg}/100 (lowest ${lowest}/100)`);

  if (failures > 0) {
    console.error(`${failures} page(s) failed to audit`);
    process.exit(1);
  }
  if (lowest < minScore) {
    console.error(`Lowest score ${lowest} is below the minimum ${minScore}`);
    process.exit(1);
  }
  console.log(`All scanned pages meet the minimum performance score (${minScore})`);
}

run();
