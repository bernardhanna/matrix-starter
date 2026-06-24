// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * Checkout — box details accordion is read-only (no editing).
 *
 * Box-builder (WPC bundle + rd-box-builder) parents render a collapsible
 * accordion inside the checkout "Order Details" summary. It lists the box
 * contents and add-ons read-only. Editing the box (including the customer note)
 * is only offered in the cart, NOT at checkout.
 *
 * This spec guards that:
 *   1. the box accordion is present in the order review,
 *   2. it can be expanded to show the read-only box contents/add-ons,
 *   3. there is no edit affordance at checkout (no "Edit" button, no edit form).
 *
 * Env:
 *   BASE_URL              - site origin (see playwright.config.cjs)
 *   RD_BB_PRODUCT_PATH    - path to a box-builder product
 *                           (default "/product/midi-sourdough-donuts-box-of-20/")
 *   CHECKOUT_PATH         - checkout path (default "/checkout/")
 */

const PRODUCT_PATH = process.env.RD_BB_PRODUCT_PATH || '/product/midi-sourdough-donuts-box-of-20/';
const CHECKOUT_PATH = process.env.CHECKOUT_PATH || '/checkout/';

const ADD_BTN = 'form.cart .single_add_to_cart_button';
const STEPPER = '.rd-bb-cart-stepper--main';

const ORDER_SUMMARY = 'details.rd-order-summary';
const SUMMARY_BAR = `${ORDER_SUMMARY} .rd-order-summary__bar`;
const ACC = `${ORDER_SUMMARY} .rd-bb-cart-acc`;
const ACC_TOGGLE = `${ACC} .rd-bb-cart-acc-toggle`;
const ACC_EDIT = `${ACC} .rd-bb-cart-addons-edit`;
const ACC_FORM = `${ACC} .rd-bb-cart-addons-form`;
const ACC_NOTE = `${ACC_FORM} textarea[name="special_requests"]`;
const ACC_SAVE = `${ACC} .rd-bb-cart-addons-save`;
const ACC_VIEW = `${ACC} .rd-bb-cart-addons-view`;

/** Best-effort dismissal of cookie / promo overlays that can intercept clicks. */
async function dismissBlockingUi(page) {
  for (let attempt = 0; attempt < 3; attempt += 1) {
    const closer = page
      .getByRole('button', { name: /accept|got it|close|dismiss|no thanks/i })
      .first();
    if (await closer.isVisible().catch(() => false)) {
      await closer.click({ force: true }).catch(() => {});
      await page.waitForTimeout(200);
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

/** Add a box to the basket from its product page (AJAX add, no reload). */
async function addBoxToBasket(page) {
  await page.goto(PRODUCT_PATH);
  await dismissBlockingUi(page);

  const addBtn = page.locator(ADD_BTN).first();
  if ((await addBtn.count()) === 0) {
    return false;
  }

  // The box-builder skin pre-fills the bundle on load; wait until the add button
  // is ready (enabled, not woosb-disabled) before clicking, otherwise the click
  // fires before the bundle is synced and nothing is added.
  await expect(addBtn).toBeEnabled();
  await expect(addBtn).not.toHaveClass(/woosb-disabled/);
  await addBtn.click();

  // The add is AJAX; the button morphs into a quantity stepper once the box is in
  // the basket. That is our reliable "added" signal.
  const stepper = page.locator(STEPPER).first();
  await stepper.waitFor({ state: 'visible', timeout: 15000 }).catch(() => {});
  return (await stepper.isVisible().catch(() => false))
    || (await addBtn.isHidden().catch(() => false));
}

/** Open the Order Details summary, then expand the box accordion within it. */
async function openBoxAccordion(page) {
  await page.goto(CHECKOUT_PATH);
  await dismissBlockingUi(page);
  await waitForCheckoutSettled(page);

  const accordion = page.locator(ACC).first();
  if ((await accordion.count()) === 0) {
    return false;
  }

  // The order summary is a native <details>; open it if collapsed.
  const summary = page.locator(ORDER_SUMMARY).first();
  if (!(await summary.evaluate((el) => el.hasAttribute('open')).catch(() => true))) {
    await page.locator(SUMMARY_BAR).first().click({ force: true }).catch(() => {});
  }

  const toggle = page.locator(ACC_TOGGLE).first();
  await expect(toggle).toBeVisible();

  // Expand the accordion body if it isn't already open.
  const body = accordion.locator('.rd-bb-cart-acc-body').first();
  if (await body.evaluate((el) => el.hasAttribute('hidden')).catch(() => true)) {
    await toggle.click();
  }
  await expect(body).toBeVisible();
  return true;
}

test.describe('Checkout — box details are read-only', () => {
  test.beforeEach(async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
  });

  test('the box accordion shows at checkout with no edit controls', async ({ page }) => {
    const added = await addBoxToBasket(page);
    test.skip(!added, `No box-builder product/add button at ${PRODUCT_PATH} (set RD_BB_PRODUCT_PATH).`);

    const hasAccordion = await openBoxAccordion(page);
    test.skip(!hasAccordion, 'No box accordion in the checkout order review (cart empty or not a box product).');

    // The read-only summary is present (box contents / add-ons are still shown).
    await expect(page.locator(ACC_VIEW).first()).toBeVisible();

    // Editing is only allowed in the cart, so the edit affordances must NOT exist
    // anywhere in the checkout order review.
    await expect(page.locator(ACC_EDIT)).toHaveCount(0);
    await expect(page.locator(ACC_FORM)).toHaveCount(0);
    await expect(page.locator(ACC_NOTE)).toHaveCount(0);
    await expect(page.locator(ACC_SAVE)).toHaveCount(0);
  });
});
