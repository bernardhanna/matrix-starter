// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * Standalone donut flavour pages must 301 to /our-donuts/.
 * Box / merch / rental product pages stay on their own permalinks.
 */

const DONUT_PATH = process.env.DONUT_PRODUCT_PATH || '/product/strawberries-ice-cream-birthday-cake-donut/';
const BOX_PATH = process.env.BOX_PRODUCT_PATH || '/product/large-sourdough-donuts-box-of-12/';

test.describe('Donut product redirects', () => {
  test('sends a single donut flavour page to Our Donuts', async ({ request }) => {
    const response = await request.get(DONUT_PATH, { maxRedirects: 0 });
    expect(response.status(), 'donut product should 301').toBe(301);

    const location = String(response.headers()['location'] || '');
    expect(location).toMatch(/\/our-donuts\/?$/);
  });

  test('does not redirect a box product page', async ({ request }) => {
    const response = await request.get(BOX_PATH, { maxRedirects: 0 });
    expect(response.status()).not.toBe(301);
    expect(response.status()).toBeLessThan(400);
  });
});
