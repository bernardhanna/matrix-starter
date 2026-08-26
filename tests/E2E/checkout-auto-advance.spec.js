// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * Express checkout — Phase 1 step auto-advance.
 *
 * The single-choice steps move the customer forward automatically once a valid
 * choice is made (the Continue buttons remain as a manual fallback):
 *   - Method  -> Schedule  as soon as a method is chosen. Delivery advances
 *               immediately; Collection waits until a pickup location is picked.
 *   - Schedule -> Details  as soon as a date is chosen.
 * The Details step is deliberately NOT auto-advanced.
 *
 * "On a step" is asserted via the wizard's state classes:
 *   - active   step -> .rd-checkout-step--active (its body is visible)
 *   - complete step -> .rd-checkout-step--collapsed
 *   - upcoming step -> .rd-checkout-step--upcoming
 *
 * Env:
 *   BASE_URL                 - site origin (see playwright.config.cjs)
 *   CHECKOUT_PATH            - checkout path (default "/checkout/")
 *   CHECKOUT_ADD_TO_CART     - product id to seed the cart via ?add-to-cart=ID
 */

const CHECKOUT_PATH = process.env.CHECKOUT_PATH || '/checkout/';
const ADD_TO_CART_ID = process.env.CHECKOUT_ADD_TO_CART || '1959';

const METHOD_STEP = '#rd-checkout-step-method';
const SCHEDULE_STEP = '#rd-checkout-step-schedule';
const DETAILS_STEP = '#rd-checkout-step-details';
const PICKUP_RADIO = `${METHOD_STEP} input.shipping_method[value*="local_pickup"]`;
const NON_PICKUP_RADIO = `${METHOD_STEP} input.shipping_method:not([value*="local_pickup"])`;
const PICKUP_SELECT = `${METHOD_STEP} select.pickup-location-lookup`;
const PICKUP_SELECT2 = `${PICKUP_SELECT} + .select2-container`;
const DATE_INPUT = '#jckwds-delivery-date';

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
  const response = page
    .waitForResponse((res) => /wc-ajax=update_order_review/i.test(res.url()), { timeout: 15000 })
    .catch(() => {});
  await locator.check({ force: true });
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

async function expectStepActive(page, stepSelector, context) {
  await expect(page.locator(stepSelector), `step not active (${context})`).toHaveClass(
    /rd-checkout-step--active/,
    { timeout: 10000 }
  );
  await expect(
    page.locator(`${stepSelector} .rd-checkout-step__body`),
    `step body hidden (${context})`
  ).toBeVisible({ timeout: 10000 });
}

async function expectStepCollapsed(page, stepSelector, context) {
  await expect(page.locator(stepSelector), `step not collapsed (${context})`).toHaveClass(
    /rd-checkout-step--collapsed/,
    { timeout: 10000 }
  );
}

/**
 * Commit a delivery/collection date the way the Iconic datepicker does: set the
 * field value (if not already populated) and fire the native `change` it
 * dispatches when a day is clicked — which is exactly what the auto-advance hook
 * listens for. Returns false when the date field isn't on the page.
 */
async function chooseDate(page) {
  return page.evaluate(() => {
    const el = /** @type {HTMLInputElement|null} */ (
      document.getElementById('jckwds-delivery-date')
    );
    if (!el) {
      return false;
    }
    if (!el.value) {
      el.value = '2099-12-31';
    }
    el.dispatchEvent(new Event('change', { bubbles: true }));
    return true;
  });
}

test.describe('Express checkout — step auto-advance', () => {
  test.beforeEach(async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await openCheckout(page);
  });

  test('selecting Delivery auto-advances Method -> Schedule', async ({ page }) => {
    const delivery = page.locator(NON_PICKUP_RADIO).first();
    test.skip((await delivery.count()) === 0, 'No delivery (non-pickup) method available.');

    // The Method step starts active.
    await expectStepActive(page, METHOD_STEP, 'initial');

    await delivery.evaluate((el) => {
      if (!(el instanceof HTMLInputElement)) {
        return;
      }
      el.checked = true;
      el.dispatchEvent(new Event('change', { bubbles: true }));
    });
    await waitForCheckoutSettled(page);

    // Choosing delivery completes Method and reveals Schedule automatically.
    await expectStepActive(page, SCHEDULE_STEP, 'after delivery selected');
    await expectStepCollapsed(page, METHOD_STEP, 'after delivery selected');
  });

  test('Collection waits for a pickup location, then auto-advances', async ({ page }) => {
    const pickup = page.locator(PICKUP_RADIO).first();
    test.skip((await pickup.count()) === 0, 'No collection (local pickup) method available.');

    await selectMethodAndSettle(page, pickup);

    const select = page.locator(PICKUP_SELECT).first();
    test.skip((await select.count()) === 0, 'No pickup-location picker rendered.');

    // If the environment pre-selected a location, the step legitimately advances
    // on its own — nothing left to assert about "waiting".
    const preselected = await select.inputValue().catch(() => '');
    test.skip(!!preselected, 'Pickup location was pre-selected; cannot test the "waits" path.');

    // With no location chosen yet, the customer must STAY on the Method step.
    await expectStepActive(page, METHOD_STEP, 'collection chosen, no location yet');
    await expect(page.locator(SCHEDULE_STEP)).toHaveClass(/rd-checkout-step--upcoming/);

    // Pick a location through the Select2 UI so selectWoo fires `select2:select`
    // (the genuine user-selection event the auto-advance hook is bound to).
    const select2 = page.locator(PICKUP_SELECT2).first();
    test.skip((await select2.count()) === 0, 'Pickup picker is not a Select2 control.');

    await select2.click();
    const option = page
      .locator('.select2-results__option:not(.select2-results__message)')
      .filter({ hasNot: page.locator('[aria-disabled="true"]') })
      .first();
    test.skip((await option.count()) === 0, 'No selectable pickup locations.');
    await option.click();
    await waitForCheckoutSettled(page);

    // Now the location is set, Method completes and Schedule opens automatically.
    await expectStepActive(page, SCHEDULE_STEP, 'after pickup location chosen');
    await expectStepCollapsed(page, METHOD_STEP, 'after pickup location chosen');
  });

  test('choosing a date auto-advances Schedule -> Details', async ({ page }) => {
    const delivery = page.locator(NON_PICKUP_RADIO).first();
    const pickup = page.locator(PICKUP_RADIO).first();
    const method = (await delivery.count()) > 0 ? delivery : pickup;
    test.skip((await method.count()) === 0, 'No shipping methods on checkout (cart empty?).');

    await selectMethodAndSettle(page, method);

    // Get onto the Schedule step (delivery auto-advances; for collection we fall
    // back to the manual Continue when a location step is in the way).
    if (!(await page.locator(`${SCHEDULE_STEP}.rd-checkout-step--active`).count())) {
      const cont = page.locator(`${METHOD_STEP} .rd-checkout-step__continue`).first();
      await cont.click({ force: true }).catch(() => {});
    }
    await expectStepActive(page, SCHEDULE_STEP, 'on schedule step');

    const hasDate = await chooseDate(page);
    test.skip(!hasDate, 'No delivery-date field rendered (date-slot plugin inactive?).');

    // A chosen date completes Schedule and opens Details automatically.
    await expectStepActive(page, DETAILS_STEP, 'after date chosen');
    await expectStepCollapsed(page, SCHEDULE_STEP, 'after date chosen');
  });

  test('Change on Method keeps both options visible and does not auto-advance', async ({ page }) => {
    const delivery = page.locator(NON_PICKUP_RADIO).first();
    const pickup = page.locator(PICKUP_RADIO).first();
    test.skip((await delivery.count()) === 0, 'No delivery (non-pickup) method available.');
    test.skip((await pickup.count()) === 0, 'No collection (local pickup) method available.');

    if (await delivery.isChecked().catch(() => false)) {
      await page.locator(`${METHOD_STEP} .rd-checkout-step__continue`).click({ force: true });
    } else {
      await selectMethodAndSettle(page, delivery);
    }
    await expectStepActive(page, SCHEDULE_STEP, 'after delivery selected');
    await expectStepCollapsed(page, METHOD_STEP, 'after delivery selected');

    await page.locator(`${METHOD_STEP} .rd-checkout-step__change`).click();

    await expectStepActive(page, METHOD_STEP, 'after Change');
    await expect(
      page.locator(`${METHOD_STEP} li`).filter({ has: page.locator('input.shipping_method:not([value*="local_pickup"])') })
    ).toBeVisible();
    await expect(
      page.locator(`${METHOD_STEP} li`).filter({ has: page.locator('input.shipping_method[value*="local_pickup"]') })
    ).toBeVisible();
    await expect(page.locator(`${METHOD_STEP} label`).filter({ hasText: /^Delivery/ })).toBeVisible();
    await expect(page.locator(`${METHOD_STEP} label`).filter({ hasText: /Free Collection/i })).toBeVisible();

    await selectMethodAndSettle(page, pickup);

    // Editing after Change must not yank the customer forward. Collection still
    // needs a location, and Delivery must also stay put until Continue.
    await expectStepActive(page, METHOD_STEP, 'after switching to collection via Change');
    await expect(
      page.locator(`${METHOD_STEP} li`).filter({ has: page.locator('input.shipping_method:not([value*="local_pickup"])') })
    ).toBeVisible();
    await expect(
      page.locator(`${METHOD_STEP} li`).filter({ has: page.locator('input.shipping_method[value*="local_pickup"]') })
    ).toBeVisible();
    await expect(page.locator(`${METHOD_STEP} label`).filter({ hasText: /^Delivery/ })).toBeVisible();

    await selectMethodAndSettle(page, delivery);
    await expectStepActive(page, METHOD_STEP, 'after switching back to delivery via Change');
    await expect(delivery).toBeChecked();
  });
});
