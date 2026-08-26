// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * Collection checkout: billing country can be UK, US, AU, FR, CA, DE, ES.
 *
 * Env:
 *   BASE_URL
 *   CHECKOUT_PATH
 *   CHECKOUT_ADD_TO_CART
 */

const CHECKOUT_PATH = process.env.CHECKOUT_PATH || '/checkout/';
// Midi sourdough box of 20 — not merch, and no required football/occasion options.
const ADD_TO_CART_ID = process.env.CHECKOUT_ADD_TO_CART || '1959';
const COUNTRIES = ['GB', 'US', 'AU', 'FR', 'CA', 'DE', 'ES'];

const METHOD_STEP = '#rd-checkout-step-method';
const PICKUP_RADIO = `${METHOD_STEP} input.shipping_method[value*="local_pickup"]`;

async function dismissBlockingUi(page) {
  for (let attempt = 0; attempt < 3; attempt += 1) {
    const closer = page
      .getByRole('button', { name: /close dialog|no thanks|close|dismiss|accept/i })
      .first();
    if (await closer.isVisible().catch(() => false)) {
      await closer.click({ force: true }).catch(() => {});
      await page.waitForTimeout(250);
      continue;
    }
    break;
  }
  await page.evaluate(() => {
    document.querySelectorAll('#cookiescript_injected, #cookiescript_injected_wrapper, .cookiescript_badge').forEach((el) => {
      el.remove();
    });
  }).catch(() => {});
  await page.keyboard.press('Escape').catch(() => {});
}

async function waitForCheckoutSettled(page) {
  await page
    .waitForFunction(() => !document.querySelector('.blockOverlay'), undefined, { timeout: 8000 })
    .catch(() => {});
  await page.waitForTimeout(250);
}

test.describe('Checkout — collection billing countries', () => {
  test('billing country list includes UK, US, AU, FR, CA, DE, ES for collection', async ({ page }) => {
    test.setTimeout(90_000);
    await page.setViewportSize({ width: 1280, height: 900 });

    await page.goto(`/?add-to-cart=${encodeURIComponent(ADD_TO_CART_ID)}`, {
      waitUntil: 'domcontentloaded',
    });
    await page.goto(CHECKOUT_PATH, { waitUntil: 'domcontentloaded' });
    await dismissBlockingUi(page);
    await waitForCheckoutSettled(page);

    const pickup = page.locator(PICKUP_RADIO).first();
    await expect(page.locator(METHOD_STEP)).toBeVisible({ timeout: 15000 });
    test.skip((await pickup.count()) === 0, 'No Free collection method on checkout.');

    if (!(await pickup.isChecked().catch(() => false))) {
      const response = page
        .waitForResponse((res) => /wc-ajax=update_order_review/i.test(res.url()), { timeout: 15000 })
        .catch(() => {});
      const pickupRow = page.locator(`${METHOD_STEP} li:has(input.shipping_method[value*="local_pickup"])`).first();
      await pickupRow.click({ force: true });
      await pickup.evaluate((el) => {
        if (el instanceof HTMLInputElement && !el.checked) {
          el.checked = true;
          el.dispatchEvent(new Event('change', { bubbles: true }));
        }
      });
      await response;
      await waitForCheckoutSettled(page);
    }

    const country = page.locator('#billing_country').first();
    test.skip((await country.count()) === 0, 'No billing country field on checkout.');

    const values = await country.locator('option').evaluateAll((opts) =>
      opts.map((o) => (o.value || '').toUpperCase())
    );

    for (const code of COUNTRIES) {
      expect(values, `billing country missing ${code}`).toContain(code);
    }
  });
});
