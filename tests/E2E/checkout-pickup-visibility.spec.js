// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * Regression guard for the express checkout shipping step.
 *
 * Bug: when Delivery is selected first and the customer then switches to
 * Free Collection, the "Select a Pickup location" picker sometimes failed to
 * appear (a stale inline `display:none` survived, and/or Select2 had been
 * initialised while the field was hidden and rendered a zero-width box).
 *
 * These tests assert the pickup picker is ALWAYS visible (and has a real,
 * non-zero width) once a collection method is selected — including after
 * toggling away to delivery and back, repeated to catch the intermittency.
 *
 * Env:
 *   BASE_URL                 - site origin (see playwright.config.cjs)
 *   CHECKOUT_PATH            - checkout path (default "/checkout/")
 *   CHECKOUT_ADD_TO_CART     - product id to seed the cart via ?add-to-cart=ID
 */

const CHECKOUT_PATH = process.env.CHECKOUT_PATH || '/checkout/';
const ADD_TO_CART_ID = process.env.CHECKOUT_ADD_TO_CART || '';

const METHOD_STEP = '#rd-checkout-step-method';
const SELECTED_LI = `${METHOD_STEP} li:has(input.shipping_method:checked)`;
const PICKUP_RADIO = `${METHOD_STEP} input.shipping_method[value*="local_pickup"]`;
const NON_PICKUP_RADIO = `${METHOD_STEP} input.shipping_method:not([value*="local_pickup"])`;
const SELECTED_PICKER = `${SELECTED_LI} .pickup-location-field`;
// The pickup field can contain other enhanced selects (e.g. a hidden
// country_to_state widget), so target the location picker's own control: the
// Select2 box rendered next to `select.pickup-location-lookup` (or, if Select2
// is not applied, the native select itself).
const SELECTED_SELECT2 = `${SELECTED_LI} select.pickup-location-lookup + .select2-container`;
const SELECTED_NATIVE = `${SELECTED_LI} select.pickup-location-lookup`;

async function dismissBlockingUi(page) {
  for (let attempt = 0; attempt < 3; attempt += 1) {
    const closeDialog = page
      .getByRole('button', { name: /close dialog|no thanks|close|dismiss/i })
      .first();
    if (await closeDialog.isVisible().catch(() => false)) {
      await closeDialog.click({ force: true }).catch(() => {});
      await page.waitForTimeout(250);
      continue;
    }
    break;
  }
  await page.keyboard.press('Escape').catch(() => {});
}

/** Wait for any in-flight WooCommerce checkout AJAX (blockUI overlay) to clear. */
async function waitForCheckoutSettled(page) {
  await page
    .waitForFunction(() => !document.querySelector('.blockOverlay'), undefined, { timeout: 8000 })
    .catch(() => {});
  await page.waitForTimeout(250);
}

/**
 * Select a shipping method and wait for the WooCommerce order-review AJAX to
 * complete. Arming the response wait before the click avoids a race where the
 * AJAX resolves before we start listening.
 */
async function selectMethodAndSettle(page, locator) {
  const response = page
    .waitForResponse((res) => /wc-ajax=update_order_review/i.test(res.url()), { timeout: 15000 })
    .catch(() => {});
  await locator.check({ force: true });
  await response;
  await waitForCheckoutSettled(page);
}

async function openCheckout(page) {
  if (ADD_TO_CART_ID) {
    await page.goto(`/?add-to-cart=${encodeURIComponent(ADD_TO_CART_ID)}`);
    await page.waitForLoadState('networkidle').catch(() => {});
  }

  await page.goto(CHECKOUT_PATH);
  await dismissBlockingUi(page);
  await waitForCheckoutSettled(page);
}

async function assertPickerVisible(page, context) {
  const picker = page.locator(SELECTED_PICKER).first();
  await expect(picker, `pickup field hidden (${context})`).toBeVisible({ timeout: 10000 });

  // Prefer the Select2 box; fall back to the native select if Select2 is absent.
  const select2 = page.locator(SELECTED_SELECT2).first();
  const control = (await select2.count()) > 0 ? select2 : page.locator(SELECTED_NATIVE).first();

  await expect(control, `pickup control hidden (${context})`).toBeVisible({ timeout: 10000 });

  const box = await control.boundingBox();
  expect(box, `pickup control has no box (${context})`).not.toBeNull();
  expect(box?.width || 0, `pickup control collapsed to 0 width (${context})`).toBeGreaterThan(0);

  // The picker must always fill its field (100% width) and never overflow.
  const fieldBox = await picker.boundingBox();
  expect(fieldBox, `pickup field has no box (${context})`).not.toBeNull();
  if (box && fieldBox) {
    expect(
      Math.abs(box.width - fieldBox.width),
      `pickup control is not full-width (${context}): control ${box.width} vs field ${fieldBox.width}`
    ).toBeLessThanOrEqual(2);
  }

  const overflow = await page.evaluate(
    () => document.documentElement.scrollWidth - document.documentElement.clientWidth
  );
  expect(overflow, `page overflows horizontally (${context})`).toBeLessThanOrEqual(1);
}

test.describe('Express checkout — pickup location visibility', () => {
  test.beforeEach(async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await openCheckout(page);
  });

  test('pickup picker is visible whenever a collection method is selected', async ({ page }) => {
    const methodCount = await page.locator(`${METHOD_STEP} input.shipping_method`).count();
    test.skip(methodCount === 0, 'No shipping methods on checkout (cart empty? set CHECKOUT_ADD_TO_CART).');

    const pickup = page.locator(PICKUP_RADIO).first();
    test.skip((await pickup.count()) === 0, 'No collection (local pickup) method available for this store/destination.');

    await selectMethodAndSettle(page, pickup);
    await assertPickerVisible(page, 'collection selected');
  });

  test('switching delivery -> collection always reveals the picker', async ({ page }) => {
    test.setTimeout(120000);

    const pickup = page.locator(PICKUP_RADIO).first();
    const delivery = page.locator(NON_PICKUP_RADIO).first();

    const hasPickup = (await pickup.count()) > 0;
    const hasDelivery = (await delivery.count()) > 0;
    test.skip(!hasPickup || !hasDelivery, 'Need both a delivery and a collection method to test the toggle.');

    // Repeat the toggle to catch the intermittent (race-condition) failure.
    for (let i = 0; i < 3; i += 1) {
      await selectMethodAndSettle(page, delivery);
      await selectMethodAndSettle(page, pickup);
      await assertPickerVisible(page, `delivery->collection toggle #${i + 1}`);
    }
  });
});
