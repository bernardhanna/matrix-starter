// @ts-check
const { test, expect } = require('@playwright/test');
const { installCookieBlocker } = require('./helpers/cookie-blocker');

/**
 * Header live search: flavour queries must not return standalone donut products.
 * They should surface the boxes that include that flavour instead.
 *
 * Hits the same REST route the header uses, with the wp_rest nonce from the page
 * (AIOS blocks anonymous /wp-json calls with 403).
 */

const SEARCH_PATH = '/wp-json/matrix-rd/v1/products/search';

test.describe('Product live search', () => {
  test('rewrites a donut flavour match to boxes that include it', async ({ page }) => {
    await installCookieBlocker(page);
    await page.goto('/');
    const nonce = await page.evaluate(() => {
      const html = document.documentElement.innerHTML;
      const match = html.match(/"nonce":"([^"]+)"/);
      return match ? match[1] : '';
    });

    const response = await page.request.get(SEARCH_PATH, {
      params: { q: 'Kinder', limit: 10 },
      headers: nonce ? { 'X-WP-Nonce': nonce } : {},
    });
    expect(response.ok(), `search REST HTTP ${response.status()}`).toBeTruthy();

    const body = await response.json();
    const results = Array.isArray(body.results) ? body.results : [];
    const titles = results.map((item) => String(item.title || ''));

    expect(
      titles.some((title) => /kinder crunch/i.test(title)),
      'standalone donut must not appear in live search'
    ).toBeFalsy();

    const boxHits = results.filter(
      (item) => item.type_label === 'Box' || /sourdough|box|combo/i.test(String(item.title || ''))
    );
    expect(boxHits.length, 'flavour search should return boxes that include the donut').toBeGreaterThan(0);
  });
});
