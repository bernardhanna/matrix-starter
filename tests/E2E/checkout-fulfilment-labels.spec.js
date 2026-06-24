// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * The step 1 "Continue to ... date" button and the step 2 "Choose your ... date"
 * heading must reflect the chosen fulfilment method:
 *   - Free Collection -> "Continue to collection date" / "Choose your collection date"
 *   - Delivery        -> "Continue to delivery date"   / "Choose your delivery date"
 *
 * Wording is server-rendered to match the session method and swapped client-side
 * when the customer switches method.
 *
 * Env:
 *   BASE_URL                 - site origin (see playwright.config.cjs)
 *   CHECKOUT_PATH            - checkout path (default "/checkout/")
 *   CHECKOUT_ADD_TO_CART     - product id to seed the cart via ?add-to-cart=ID
 */

const CHECKOUT_PATH = process.env.CHECKOUT_PATH || '/checkout/';
const ADD_TO_CART_ID = process.env.CHECKOUT_ADD_TO_CART || '';

const METHOD_STEP = '#rd-checkout-step-method';
const PICKUP_RADIO = `${METHOD_STEP} input.shipping_method[value*="local_pickup"]`;
const NON_PICKUP_RADIO = `${METHOD_STEP} input.shipping_method:not([value*="local_pickup"])`;
const CONTINUE_BTN = `${METHOD_STEP} .rd-checkout-step__continue`;
const SCHEDULE_HEADING = '#rd-checkout-step-schedule-heading';

const COLLECTION_CONTINUE = 'Continue to collection date';
const COLLECTION_HEADING = 'Choose your collection date';
const DELIVERY_CONTINUE = 'Continue to delivery date';
const DELIVERY_HEADING = 'Choose your delivery date';

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

async function waitForCheckoutSettled(page) {
  await page
    .waitForFunction(() => !document.querySelector('.blockOverlay'), undefined, { timeout: 8000 })
    .catch(() => {});
  await page.waitForTimeout(250);
}

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

async function expectLabels(page, { continueText, headingText }, context) {
  await expect(page.locator(CONTINUE_BTN).first(), `continue button (${context})`).toHaveText(
    continueText
  );
  await expect(page.locator(SCHEDULE_HEADING).first(), `schedule heading (${context})`).toHaveText(
    headingText
  );
}

test.describe('Express checkout — fulfilment date labels', () => {
  test.beforeEach(async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await openCheckout(page);
  });

  test('collection wording when a collection method is selected', async ({ page }) => {
    const methodCount = await page.locator(`${METHOD_STEP} input.shipping_method`).count();
    test.skip(methodCount === 0, 'No shipping methods on checkout (cart empty? set CHECKOUT_ADD_TO_CART).');

    const pickup = page.locator(PICKUP_RADIO).first();
    test.skip((await pickup.count()) === 0, 'No collection (local pickup) method available.');

    await selectMethodAndSettle(page, pickup);
    await expectLabels(
      page,
      { continueText: COLLECTION_CONTINUE, headingText: COLLECTION_HEADING },
      'collection selected'
    );
  });

  test('labels switch between delivery and collection wording', async ({ page }) => {
    test.setTimeout(120000);

    const pickup = page.locator(PICKUP_RADIO).first();
    const delivery = page.locator(NON_PICKUP_RADIO).first();

    const hasPickup = (await pickup.count()) > 0;
    const hasDelivery = (await delivery.count()) > 0;
    test.skip(!hasPickup || !hasDelivery, 'Need both a delivery and a collection method to test the switch.');

    await selectMethodAndSettle(page, delivery);
    await expectLabels(
      page,
      { continueText: DELIVERY_CONTINUE, headingText: DELIVERY_HEADING },
      'delivery selected'
    );

    await selectMethodAndSettle(page, pickup);
    await expectLabels(
      page,
      { continueText: COLLECTION_CONTINUE, headingText: COLLECTION_HEADING },
      'collection selected'
    );

    await selectMethodAndSettle(page, delivery);
    await expectLabels(
      page,
      { continueText: DELIVERY_CONTINUE, headingText: DELIVERY_HEADING },
      'delivery selected again'
    );
  });
});
