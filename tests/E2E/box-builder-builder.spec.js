// @ts-check
const { test, expect } = require('@playwright/test');

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

const curText = (page) => page.locator(COUNT_CUR).first();

test.describe('Box builder — picker interactions', () => {
  test.beforeEach(async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
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

    await page.locator(CLEAR).first().click();

    await expect(curText(page)).toHaveText('0');
    await expect(page.locator(FILLED_SLOT)).toHaveCount(0);
  });

  test('adding and removing a flavour updates the count', async ({ page }) => {
    await openBuilder(page);

    // Start from a known-empty box.
    await page.locator(CLEAR).first().click();
    await expect(curText(page)).toHaveText('0');

    // Add one donut.
    await page.locator(CARD_PLUS).first().click();
    await expect(curText(page)).toHaveText('1');
    await expect(page.locator(FILLED_SLOT)).toHaveCount(1);

    // Add a second.
    await page.locator(CARD_PLUS).first().click();
    await expect(curText(page)).toHaveText('2');

    // Remove one again.
    await page.locator(CARD_MINUS).first().click();
    await expect(curText(page)).toHaveText('1');
  });

  test('filling the box to capacity enables Add to Basket', async ({ page }) => {
    await openBuilder(page);

    const max = parseInt((await page.locator(COUNT_MAX).first().textContent()) || '0', 10);
    test.skip(!max || max > 24, `Box capacity ${max} not suitable for a quick fill test.`);

    await page.locator(CLEAR).first().click();
    await expect(curText(page)).toHaveText('0');

    // Add the first available flavour until the box is full.
    for (let i = 0; i < max; i += 1) {
      const plus = page.locator(CARD_PLUS).first();
      if ((await plus.count()) === 0) break; // all flavours blocked = box full
      await plus.click();
    }

    await expect(curText(page)).toHaveText(String(max));

    const addBtn = page.locator(ADD_BTN).first();
    await expect(addBtn).toBeEnabled();
    await expect(addBtn).not.toHaveClass(/woosb-disabled/);
  });
});
