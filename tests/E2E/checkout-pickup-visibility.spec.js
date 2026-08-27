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
const ADD_TO_CART_ID = process.env.CHECKOUT_ADD_TO_CART || '1959';

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
      .getByRole('button', { name: /close dialog|no thanks|close|dismiss|accept/i })
      .first();
    if (await closeDialog.isVisible().catch(() => false)) {
      await closeDialog.click({ force: true }).catch(() => {});
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
  if (await locator.isChecked().catch(() => false)) {
    await waitForCheckoutSettled(page);
    return;
  }

  const response = page
    .waitForResponse((res) => /wc-ajax=update_order_review/i.test(res.url()), { timeout: 15000 })
    .catch(() => {});
  await locator.evaluate((el) => {
    if (!(el instanceof HTMLInputElement)) {
      return;
    }
    el.checked = true;
    el.dispatchEvent(new Event('change', { bubbles: true }));
  });
  await response;
  await waitForCheckoutSettled(page);
}

async function openCheckout(page) {
  if (ADD_TO_CART_ID) {
    await page.goto(`/?add-to-cart=${encodeURIComponent(ADD_TO_CART_ID)}`, {
      waitUntil: 'domcontentloaded',
    });
  }

  await page.goto(CHECKOUT_PATH, { waitUntil: 'domcontentloaded' });
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

async function assertBothMethodsVisible(page, context) {
  const deliveryLi = page
    .locator(`${METHOD_STEP} li`)
    .filter({ has: page.locator('input.shipping_method:not([value*="local_pickup"])') })
    .first();
  const pickupLi = page
    .locator(`${METHOD_STEP} li`)
    .filter({ has: page.locator('input.shipping_method[value*="local_pickup"]') })
    .first();

  await expect(deliveryLi, `delivery option missing (${context})`).toBeVisible({ timeout: 10000 });
  await expect(pickupLi, `collection option missing (${context})`).toBeVisible({ timeout: 10000 });
  await expect(
    page.locator(`${METHOD_STEP} label`).filter({ hasText: /^Delivery/ }),
    `delivery label hidden (${context})`
  ).toBeVisible();
  await expect(
    page.locator(`${METHOD_STEP} label`).filter({ hasText: /Free Collection/i }),
    `collection label hidden (${context})`
  ).toBeVisible();

  const deliveryBox = await deliveryLi.boundingBox();
  const pickupBox = await pickupLi.boundingBox();
  expect(deliveryBox?.height || 0, `delivery option collapsed (${context})`).toBeGreaterThan(20);
  expect(pickupBox?.height || 0, `collection option collapsed (${context})`).toBeGreaterThan(20);
}

async function choosePickupLocation(page) {
  const select = page.locator(SELECTED_NATIVE).first();
  if ((await select.count()) === 0) {
    return false;
  }

  const select2 = page.locator(SELECTED_SELECT2).first();
  if ((await select2.count()) > 0) {
    await select2.click();
    const option = page
      .locator('.select2-results__option:not(.select2-results__message)')
      .filter({ hasNot: page.locator('[aria-disabled="true"]') })
      .first();
    if ((await option.count()) === 0) {
      return false;
    }
    await option.click();
    await waitForCheckoutSettled(page);
    return true;
  }

  const value = await select.evaluate((el) => {
    const opt = Array.from(/** @type {HTMLSelectElement} */ (el).options).find(
      (item) => item.value && item.value !== '0'
    );
    return opt ? opt.value : '';
  });
  if (!value) {
    return false;
  }
  await select.selectOption(value);
  await waitForCheckoutSettled(page);
  return true;
}

test.describe('Express checkout — pickup location visibility', () => {
  test.beforeEach(async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await openCheckout(page);
  });

  test('mobile collection picker stays tappable after it appears', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await openCheckout(page);

    const pickup = page.locator(PICKUP_RADIO).first();
    test.skip((await pickup.count()) === 0, 'No collection method available.');

    await selectMethodAndSettle(page, pickup);

    const loading = page.locator(`${METHOD_STEP} .rd-pickup-location-loading`);
    await expect(loading, 'collection picker stuck on loading spinner').toBeHidden({
      timeout: 4000,
    });

    const picked = await choosePickupLocation(page);
    test.skip(!picked, 'No selectable pickup location.');

    const overlayBlocking = await page.evaluate(() => {
      const overlay = document.querySelector('.blockUI, .select2-container--open');
      if (!overlay) {
        return false;
      }
      const btn = document.querySelector(`${'#rd-checkout-step-method'} .rd-checkout-step__continue`);
      if (!btn) {
        return false;
      }
      const box = btn.getBoundingClientRect();
      const top = document.elementFromPoint(box.left + box.width / 2, box.top + box.height / 2);
      return !!(top && (top.classList.contains('blockUI') || top.closest('.select2-container--open')));
    });
    expect(overlayBlocking, 'an overlay is still swallowing taps after choosing a location').toBeFalsy();

    const cont = page.locator(`${METHOD_STEP} .rd-checkout-step__continue`).first();
    await expect(cont).toBeEnabled();
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
      const change = page.locator(`${METHOD_STEP} .rd-checkout-step__change`);
      if (await change.isVisible().catch(() => false)) {
        await change.click();
        await waitForCheckoutSettled(page);
      }
      await assertBothMethodsVisible(page, `delivery selected #${i + 1}`);
      await selectMethodAndSettle(page, pickup);
      await assertPickerVisible(page, `delivery->collection toggle #${i + 1}`);
      await assertBothMethodsVisible(page, `collection selected #${i + 1}`);
    }
  });

  test('Change after collection still shows delivery and collection', async ({ page }) => {
    test.setTimeout(120000);

    const pickup = page.locator(PICKUP_RADIO).first();
    const delivery = page.locator(NON_PICKUP_RADIO).first();
    test.skip((await pickup.count()) === 0 || (await delivery.count()) === 0, 'Need both fulfilment methods.');

    await assertBothMethodsVisible(page, 'initial method step');

    await selectMethodAndSettle(page, pickup);
    await assertBothMethodsVisible(page, 'after selecting collection');

    const picked = await choosePickupLocation(page);
    test.skip(!picked, 'No selectable pickup location.');

    await page.locator(`${METHOD_STEP} .rd-checkout-step__continue`).click({ force: true }).catch(() => {});
    await waitForCheckoutSettled(page);

    const change = page.locator(`${METHOD_STEP} .rd-checkout-step__change`);
    await expect(change).toBeVisible({ timeout: 10000 });

    for (let i = 0; i < 3; i += 1) {
      const review = page
        .waitForResponse((res) => /wc-ajax=update_order_review/i.test(res.url()), { timeout: 8000 })
        .catch(() => {});
      await change.click();
      await review;
      await waitForCheckoutSettled(page);
      await assertBothMethodsVisible(page, `after Change #${i + 1}`);
      await expect(delivery).toBeVisible();
      await expect(pickup).toBeVisible();

      await page.locator(`${METHOD_STEP} .rd-checkout-step__continue`).click({ force: true });
      await waitForCheckoutSettled(page);
      await expect(change).toBeVisible({ timeout: 10000 });
    }
  });
});
