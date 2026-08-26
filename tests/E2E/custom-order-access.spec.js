// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * Custom Order (/custom-order, product 3947) is a logged-in staff tool.
 * Save & Share Cart recipients are almost always guests and must still reach
 * the cart / retrieve URL without hitting that login wall.
 */

const CUSTOM_ORDER_PATH = process.env.RD_CUSTOM_ORDER_PATH || '/product/custom-order/';
const CUSTOM_ORDER_SHORT_PATH = '/custom-order/';
const CART_PATH = '/cart/';
const CHECKOUT_PATH = '/checkout/';

function locationOf(response) {
  return String(response.headers()['location'] || '');
}

test.describe('Custom Order access', () => {
  test('sends a guest on the product page to My Account', async ({ request }) => {
    const response = await request.get(CUSTOM_ORDER_PATH, { maxRedirects: 0 });
    expect(response.status(), 'custom-order should 302 guests to sign in').toBe(302);
    expect(locationOf(response)).toMatch(/my-account/i);
  });

  test('canonical /custom-order/ still ends at My Account for guests', async ({ request }) => {
    const response = await request.get(CUSTOM_ORDER_SHORT_PATH, { maxRedirects: 0 });
    expect([301, 302]).toContain(response.status());
    expect(locationOf(response)).toMatch(/custom-order|my-account/i);
  });

  test('the cart page stays available to guests', async ({ request }) => {
    const response = await request.get(CART_PATH, { maxRedirects: 0 });
    expect(response.status()).toBeLessThan(400);
    expect(locationOf(response)).not.toMatch(/my-account/i);
  });

  test('a Save & Share Cart retrieve URL does not bounce guests to login', async ({ request }) => {
    const response = await request.get(`${CART_PATH}?cxecrt-retrieve-cart=1`, {
      maxRedirects: 0,
    });
    expect(response.status()).toBeLessThan(400);
    expect(locationOf(response)).not.toMatch(/my-account/i);
  });

  test('checkout stays available to guests', async ({ request }) => {
    const response = await request.get(CHECKOUT_PATH, { maxRedirects: 0 });
    expect(response.status()).toBeLessThan(400);
    expect(locationOf(response)).not.toMatch(/my-account/i);
  });
});
