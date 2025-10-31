#!/usr/bin/env node
const { exec } = require('child_process');
const fs = require('fs');
const url = require('url');
require('dotenv').config({ path: '.env' });   // ← load .env first

/* ── 1. Decide which URL to scan ───────────────────────────── */
const cliUrl = process.argv[2];               // allow: npm run test:links https://stage.site
const base =
  cliUrl ||
  process.env.BASE_URL ||
  process.env.WP_HOME ||
  'http://localhost:10054';                   // final hard-coded fallback

/* Normalise to IPv4 so ::1 doesn’t bite us on macOS */
const parsed = new url.URL(base);
if (parsed.hostname === 'localhost') parsed.hostname = '127.0.0.1';

/* ── 2. Output folder ─────────────────────────────────────── */
const reportDir = 'tests/link-report';
fs.mkdirSync(reportDir, { recursive: true });

console.log(`🔗  Crawling for broken links: ${parsed.href}`);

/* ── 3. Run broken-link-checker ───────────────────────────── */
exec(
  `npx blc "${parsed.href}" --recursive --ordered ` +
  `--filter-level 3 --exclude ".*/wp-admin/.*"`,
  { maxBuffer: 1024 * 500 },
  (err, stdout) => {
    const file = `${reportDir}/blc.txt`;
    fs.writeFileSync(file, stdout);
    console.log(stdout);

    if (err) {
      console.error(`❌  Broken links found (or crawl failed) – see ${file}`);
      process.exit(1);
    } else {
      console.log('✅  No broken links detected');
    }
  }
);