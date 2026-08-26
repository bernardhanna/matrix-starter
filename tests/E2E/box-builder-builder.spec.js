// @ts-check
const { test, expect } = require('@playwright/test');
const { installCookieBlocker } = require('./helpers/cookie-blocker');

/**
 * Box builder — in-builder picker flow.
 *
 * Complements box-builder-cart.spec.js (which covers the basket stepper). This
 * spec drives the actual "Build Your Own Box" interface a customer uses to choose
 * donuts, guarding the core interactions:
 *   1. opening the builder,
 *   2. clearing the box (count back to 0, no filled slots),
 *   3. adding a flavour (count goes up, a filled slot appears),
 *   4. removing a flavour (count goes down),
 *   5. filling to capacity enables "Add to Basket".
 *
 * Env:
 *   BASE_URL            - site origin (see playwright.config.cjs)
 *   RD_BB_PRODUCT_PATH  - path to a box-builder product
 *                         (default "/product/midi-sourdough-donuts-box-of-20/")
 */

const PRODUCT_PATH = process.env.RD_BB_PRODUCT_PATH || '/product/midi-sourdough-donuts-box-of-20/';

const TOGGLE = '.rd-bb-toggle';
const BUILDER = '#rd-bb';
const COUNT_CUR = '.rd-bb-box .rd-bb-count-current';
const COUNT_MAX = '.rd-bb-box .rd-bb-count-max';
const CLEAR = '.rd-bb-clear';
const OPEN_PICKER = '.rd-bb-open-picker';
const PICKER = '.rd-bb-picker';
const FILLED_SLOT = '.rd-bb-slots .rd-bb-slot--filled';
const CARD = `${PICKER} .rd-bb-card`;
const CARD_PLUS = `${PICKER} .rd-bb-card .rd-bb-card-controls button[aria-label="+"]:not([disabled])`;
const CARD_MINUS = `${PICKER} .rd-bb-card .rd-bb-step--minus:not([disabled])`;
const ADD_BTN = 'form.cart .single_add_to_cart_button';
const FORM_QTY = 'form.cart input[name="quantity"]';
const FORM_QTY_PLUS = 'form.cart .quantity:has(input[name="quantity"]) .increment-btn';
const STEPPER = '.rd-bb-cart-stepper--main';
const STEP_QTY = `${STEPPER} .rd-bb-cart-qty`;
const NEW_BOX = 'form.cart .rd-product-cta-row .rd-bb-new-box, form.cart .rd-bb-cta-row .rd-bb-new-box';
const SIDE_CART_CLOSE = '[data-rd-side-cart] [data-rd-side-cart-close]';

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
  await page.evaluate(() => {
    document.querySelectorAll('#cookiescript_injected, #cookiescript_injected_wrapper, .cookiescript_badge').forEach((el) => {
      el.remove();
    });
  }).catch(() => {});
  await page.keyboard.press('Escape').catch(() => {});
}

/** Open the builder and (if needed) the flavour picker. */
async function openBuilder(page) {
  const toggle = page.locator(TOGGLE).first();
  if (await toggle.isVisible().catch(() => false)) {
    await toggle.click().catch(() => {});
  }
  await expect(page.locator(BUILDER)).toBeVisible();

  const picker = page.locator(PICKER);
  if (!(await picker.isVisible().catch(() => false))) {
    const open = page.locator(OPEN_PICKER).first();
    if (await open.isVisible().catch(() => false)) {
      await open.click().catch(() => {});
    }
  }
  await expect(page.locator(CARD).first()).toBeVisible();
}

async function closeSideCart(page) {
  const closer = page.locator(SIDE_CART_CLOSE).first();
  if (await closer.isVisible().catch(() => false)) {
    await closer.click({ force: true }).catch(() => {});
  }
}

async function addBoxAndWaitForStepper(page) {
  await page.locator(ADD_BTN).first().click({ force: true });
  await expect(page.locator(STEPPER)).toBeVisible({ timeout: 20000 });
}

test.describe('Box builder — picker interactions', () => {
  test.beforeEach(async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await installCookieBlocker(page);
    await page.goto(PRODUCT_PATH);
    await dismissBlockingUi(page);

    const builderToggle = page.locator(TOGGLE);
    test.skip(
      (await builderToggle.count()) === 0,
      `No box builder on ${PRODUCT_PATH} (set RD_BB_PRODUCT_PATH to a build-your-own-box product).`
    );
  });

  test('clear box empties the box (count 0, no filled slots)', async ({ page }) => {
    await openBuilder(page);

    await page.locator(CLEAR).first().click({ force: true });

    await expect(page.locator(COUNT_CUR).first()).toHaveText('0');
    await expect(page.locator(FILLED_SLOT)).toHaveCount(0);
  });

  test('adding and removing a flavour updates the count', async ({ page }) => {
    await openBuilder(page);

    // Start from a known-empty box.
    await page.locator(CLEAR).first().click({ force: true });
    await expect(page.locator(COUNT_CUR).first()).toHaveText('0');

    // Add one donut.
    await page.locator(CARD_PLUS).first().click();
    await expect(page.locator(COUNT_CUR).first()).toHaveText('1');
    await expect(page.locator(FILLED_SLOT)).toHaveCount(1);

    // Add a second.
    await page.locator(CARD_PLUS).first().click();
    await expect(page.locator(COUNT_CUR).first()).toHaveText('2');

    // Remove one again.
    await page.locator(CARD_MINUS).first().click();
    await expect(page.locator(COUNT_CUR).first()).toHaveText('1');
  });

  test('filling the box to capacity enables Add to Basket', async ({ page }) => {
    await openBuilder(page);

    const max = parseInt((await page.locator(COUNT_MAX).first().textContent()) || '0', 10);
    test.skip(!max || max > 24, `Box capacity ${max} not suitable for a quick fill test.`);

    await page.locator(CLEAR).first().click({ force: true });
    await expect(page.locator(COUNT_CUR).first()).toHaveText('0');

    // Add the first available flavour until the box is full.
    for (let i = 0; i < max; i += 1) {
      const plus = page.locator(CARD_PLUS).first();
      if ((await plus.count()) === 0) break; // all flavours blocked = box full
      await plus.click();
    }

    await expect(page.locator(COUNT_CUR).first()).toHaveText(String(max));

    const addBtn = page.locator(ADD_BTN).first();
    await expect(addBtn).toBeEnabled();
    await expect(addBtn).not.toHaveClass(/woosb-disabled/);
  });

  test('set-box quantity and builder quantity stay independent when toggling', async ({ page }) => {
    const qty = page.locator(FORM_QTY).first();
    const plus = page.locator(FORM_QTY_PLUS).first();

    await expect(qty).toHaveValue('1');

    // Set-box mode: order 3 classic boxes via the visible stepper.
    await plus.click({ force: true });
    await plus.click({ force: true });
    await expect(qty).toHaveValue('3');

    // Opening the builder must not inherit that 3 — custom boxes start at 1.
    await page.locator(TOGGLE).first().click({ force: true });
    await expect(page.locator(BUILDER)).toBeVisible();
    await expect(qty).toHaveValue('1');

    // Builder mode: order 2 custom boxes, then close.
    await plus.click({ force: true });
    await expect(qty).toHaveValue('2');
    await page.locator(TOGGLE).first().click({ force: true });
    await expect(page.locator(BUILDER)).toBeHidden();

    // Set-box qty is restored; reopening restores the builder qty.
    await expect(qty).toHaveValue('3');
    await page.locator(TOGGLE).first().click({ force: true });
    await expect(page.locator(BUILDER)).toBeVisible();
    await expect(qty).toHaveValue('2');
  });

  test('adding 3 set-boxes to cart does not put the builder at qty 3', async ({ page }) => {
    const qty = page.locator(FORM_QTY).first();
    const plus = page.locator(FORM_QTY_PLUS).first();
    const addBtn = page.locator(ADD_BTN).first();
    const stepper = page.locator(STEPPER);

    await plus.click();
    await plus.click();
    await expect(qty).toHaveValue('3');

    await addBtn.click();
    await expect(stepper).toBeVisible();
    await expect(page.locator(STEP_QTY).first()).toHaveText('3');

    const closer = page.locator(SIDE_CART_CLOSE).first();
    if (await closer.isVisible().catch(() => false)) {
      await closer.click();
    }

    // Builder is a separate add: Add to Cart + qty 1, not the 3-box stepper.
    await page.locator(TOGGLE).first().click();
    await expect(page.locator(BUILDER)).toBeVisible();
    await expect(addBtn).toBeVisible();
    await expect(stepper).toBeHidden();
    await expect(qty).toHaveValue('1');

    // Closing restores the set-box stepper at 3.
    await page.locator(TOGGLE).first().click();
    await expect(page.locator(BUILDER)).toBeHidden();
    await expect(stepper).toBeVisible();
    await expect(page.locator(STEP_QTY).first()).toHaveText('3');
  });

  test('set-box Clear restores Add to Cart without opening the builder', async ({ page }) => {
    const qty = page.locator(FORM_QTY).first();
    const plus = page.locator(FORM_QTY_PLUS).first();
    const addBtn = page.locator(ADD_BTN).first();
    const stepper = page.locator(STEPPER);
    const newBox = page.locator(NEW_BOX).first();

    await plus.click({ force: true });
    await plus.click({ force: true });
    await expect(qty).toHaveValue('3');
    await addBoxAndWaitForStepper(page);
    await expect(newBox).toBeVisible();
    await expect(newBox).toHaveText('Clear');

    await closeSideCart(page);

    await newBox.click({ force: true });
    await expect(page.locator(BUILDER)).toBeHidden();
    await expect(addBtn).toBeVisible();
    await expect(stepper).toBeHidden();
    await expect(qty).toHaveValue('1');
  });

  test('builder Build a new box stays in the builder and restores Add to Cart', async ({ page }) => {
    const addBtn = page.locator(ADD_BTN).first();
    const stepper = page.locator(STEPPER);
    const newBox = page.locator(NEW_BOX).first();

    await openBuilder(page);
    await expect(addBtn).toBeEnabled();
    await expect(page.locator(BUILDER)).toBeVisible();
    await addBoxAndWaitForStepper(page);
    await expect(page.locator(BUILDER)).toBeVisible();
    await expect(newBox).toBeVisible();
    await expect(newBox).toHaveText('Build a new box');

    await closeSideCart(page);

    await newBox.click({ force: true });
    await expect(page.locator(BUILDER)).toBeVisible();
    await expect(addBtn).toBeVisible();
    await expect(stepper).toBeHidden();
  });

  test('close builder returns to the set-box view', async ({ page }) => {
    await openBuilder(page);
    await expect(page.locator(BUILDER)).toBeVisible();

    const sheetClose = page.locator('.rd-bb-sheet-close').first();
    if (await sheetClose.isVisible().catch(() => false)) {
      await sheetClose.click();
    } else {
      await page.locator(TOGGLE).first().click();
    }

    await expect(page.locator(BUILDER)).toBeHidden();
  });

  test('filters hide and show flavours', async ({ page }) => {
    await openBuilder(page);

    const filterToggle = page.locator('.rd-bb-filter-toggle').first();
    if (await filterToggle.isVisible().catch(() => false)) {
      await filterToggle.click();
    }

    const filterRoot = page.locator('#rd-bb-filter, .rd-bb-filter').first();
    test.skip((await filterRoot.count()) === 0, 'No box-builder filter UI.');

    const chip = filterRoot.locator('button, [role="button"], label, a').nth(1);
    test.skip((await chip.count()) === 0, 'Filter chips have not populated yet.');

    const before = await page.locator(CARD).count();
    await chip.click();
    await page.waitForTimeout(300);
    const after = await page.locator(`${CARD}:visible`).count();
    expect(after).toBeGreaterThan(0);
    expect(after).toBeLessThanOrEqual(before);
  });
});
