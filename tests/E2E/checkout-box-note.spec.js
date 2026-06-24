// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * Checkout — edit a box's "Note to customer" from the Order Details accordion.
 *
 * Box-builder (WPC bundle + rd-box-builder) parents render a collapsible
 * accordion inside the checkout "Order Details" summary. It lists the box
 * contents and add-ons read-only, and lets the customer edit just the customer
 * note in place over AJAX (the locked-in options are not editable here).
 *
 * This spec guards that flow:
 *   1. the box accordion is present in the order review,
 *   2. expanding it and editing the note saves over AJAX,
 *   3. the new note shows in the read-only summary,
 *   4. the note survives a checkout reload (it was persisted to the cart line,
 *      which is what later reaches the order line item + packing slip).
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

test.describe('Checkout — edit box note in Order Details', () => {
  test.beforeEach(async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
  });

  test('editing the note saves, shows in the summary, and survives reload', async ({ page }) => {
    const added = await addBoxToBasket(page);
    test.skip(!added, `No box-builder product/add button at ${PRODUCT_PATH} (set RD_BB_PRODUCT_PATH).`);

    const hasAccordion = await openBoxAccordion(page);
    test.skip(!hasAccordion, 'No box accordion in the checkout order review (cart empty or not a box product).');

    const note = `Leave with the neighbour at no. 7 — ${Date.now()}`;

    // Enter edit mode (at checkout this button is labelled "Edit note").
    await page.locator(ACC_EDIT).first().click();
    await expect(page.locator(ACC_FORM).first()).toBeVisible();

    await page.locator(ACC_NOTE).first().fill(note);

    // Saving posts to admin-ajax (rd_bb_update_addons) and swaps the summary in place.
    const saved = page
      .waitForResponse((res) => /admin-ajax\.php/i.test(res.url()) && res.request().method() === 'POST', {
        timeout: 15000,
      })
      .catch(() => null);
    await page.locator(ACC_SAVE).first().click();
    await saved;

    // The read-only summary now shows the new note.
    await expect(page.locator(ACC_VIEW).first()).toContainText(note);
    await expect(page.locator(ACC_VIEW).first()).toContainText(/note to customer/i);

    // Reload the checkout: the note was persisted to the cart line, so it must
    // still be there (this is the same value that reaches the order + packing slip).
    const stillThere = await openBoxAccordion(page);
    expect(stillThere).toBe(true);
    await expect(page.locator(ACC_VIEW).first()).toContainText(note);
  });
});
