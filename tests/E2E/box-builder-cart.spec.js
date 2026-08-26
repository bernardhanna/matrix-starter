// @ts-check
const { test, expect } = require('@playwright/test');
const { installCookieBlocker } = require('./helpers/cookie-blocker');

/**
 * Box builder — basket stepper flow.
 *
 * The "Build Your Own Box" products (WPC bundle + rd-box-builder skin) replace the
 * raw WooCommerce quantity number field with a single "Add to Basket" button. Once
 * a box is added (over AJAX, no reload) the button morphs in place into a −/+
 * stepper that adjusts the quantity of that box in the basket, and decrementing to
 * zero removes it and restores the button.
 *
 * This spec guards that flow:
 *   1. the native quantity input is hidden,
 *   2. "Add to Basket" adds the box and shows the stepper at qty 1,
 *   3. + / − change the basket quantity,
 *   4. decrementing to 0 removes the box and restores the button.
 *
 * Env:
 *   BASE_URL              - site origin (see playwright.config.cjs)
 *   RD_BB_PRODUCT_PATH    - path to a box-builder product
 *                           (default "/product/midi-sourdough-donuts-box-of-20/")
 */

const PRODUCT_PATH = process.env.RD_BB_PRODUCT_PATH || '/product/midi-sourdough-donuts-box-of-20/';

const ADD_BTN = 'form.cart .single_add_to_cart_button';
const QTY_INPUT = 'form.cart .quantity';
const STEPPER = '.rd-bb-cart-stepper--main';
const STEP_QTY = `${STEPPER} .rd-bb-cart-qty`;
const STEP_PLUS = `${STEPPER} .rd-bb-cart-step--plus`;
const STEP_MINUS = `${STEPPER} .rd-bb-cart-step--minus`;
const TOGGLE = '.rd-bb-toggle';
const BUILDER = '#rd-bb';

const SIDE_CART = '[data-rd-side-cart]';
const SIDE_CART_CLOSE = `${SIDE_CART} [data-rd-side-cart-close]`;
const SIDE_CART_CHECKOUT = `${SIDE_CART} .rd-side-cart__btn--primary`;

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
  await page.evaluate(() => {
    document.querySelectorAll('#cookiescript_injected, #cookiescript_injected_wrapper, .cookiescript_badge').forEach((el) => {
      el.remove();
    });
  }).catch(() => {});
  await page.keyboard.press('Escape').catch(() => {});
}

async function closeSideCart(page) {
  const closer = page.locator(SIDE_CART_CLOSE).first();
  if (await closer.isVisible().catch(() => false)) {
    await closer.click({ force: true }).catch(() => {});
    await expect(page.locator(SIDE_CART)).not.toHaveClass(/is-open/);
  }
}

async function waitCartIdle(page) {
  await expect(page.locator('form.cart')).not.toHaveClass(/rd-bb-cart-busy/, { timeout: 15_000 });
}

test.describe('Box builder — basket stepper', () => {
  test.beforeEach(async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await installCookieBlocker(page);
    await page.goto(PRODUCT_PATH);
    await dismissBlockingUi(page);
  });

  test('Add to Basket morphs into a working quantity stepper', async ({ page }) => {
    const stepper = page.locator(STEPPER);
    test.skip(
      (await stepper.count()) === 0,
      `No box-builder stepper on ${PRODUCT_PATH} (set RD_BB_PRODUCT_PATH to a build-your-own-box product).`
    );

    const addBtn = page.locator(ADD_BTN).first();
    const qty = page.locator(STEP_QTY);

    // 1) The raw quantity number field is hidden in favour of the button/stepper.
    await expect(page.locator(QTY_INPUT).first()).toBeHidden();

    // The box is pre-filled to capacity, so the button should be enabled.
    await expect(addBtn).toBeVisible();
    await expect(addBtn).toBeEnabled();
    await expect(addBtn).not.toHaveClass(/woosb-disabled/);

    // 2) Adding the box (AJAX) hides the button and shows the stepper at qty 1.
    await addBtn.click();
    await expect(stepper).toBeVisible();
    await expect(qty).toHaveText('1');
    await expect(addBtn).toBeHidden();
    await waitCartIdle(page);
    await closeSideCart(page);

    // 3) + / − adjust the quantity of the box in the basket.
    await page.locator(STEP_PLUS).click({ force: true });
    await expect(qty).toHaveText('2');
    await waitCartIdle(page);
    await closeSideCart(page);

    await page.locator(STEP_MINUS).click({ force: true });
    await expect(qty).toHaveText('1');
    await waitCartIdle(page);
    await closeSideCart(page);

    // 4) Decrementing to zero removes the box and restores "Add to Basket".
    await page.locator(STEP_MINUS).click({ force: true });
    await expect(addBtn).toBeVisible();
    await expect(stepper).toBeHidden();
  });

  test('each box is a separate add — the stepper does not return after reload', async ({ page }) => {
    const stepper = page.locator(STEPPER);
    test.skip((await stepper.count()) === 0, `No box-builder stepper on ${PRODUCT_PATH}.`);

    const addBtn = page.locator(ADD_BTN).first();
    const qty = page.locator(STEP_QTY);

    await expect(addBtn).toBeEnabled();
    await addBtn.click();
    await expect(qty).toHaveText('1');

    await page.reload();
    await dismissBlockingUi(page);

    // Each configured box is a separate, one-off add. Even though the box is still
    // in the basket, reopening the page must start from a clean "Add Box to Cart"
    // button — not a restored stepper reflecting an earlier (or unrelated) add.
    await expect(page.locator(ADD_BTN).first()).toBeVisible();
    await expect(page.locator(STEPPER)).toBeHidden();

    // Clean up: re-adding the same config merges into the existing basket line, so
    // it now reads 2; decrement back to zero to remove it for a clean re-run.
    await page.locator(ADD_BTN).first().click();
    await expect(qty).toHaveText('2');
    await waitCartIdle(page);
    await closeSideCart(page);
    await page.locator(STEP_MINUS).click({ force: true });
    await expect(qty).toHaveText('1');
    await waitCartIdle(page);
    await closeSideCart(page);
    await page.locator(STEP_MINUS).click({ force: true });
    await expect(page.locator(ADD_BTN).first()).toBeVisible();
  });

  // Regression: increasing the quantity from the stepper must reopen the
  // side-cart slide-out so the customer always has a path to checkout. The
  // bug was that the + / − stepper only refreshed fragments and never surfaced
  // the slide-out, leaving "X in your basket" with no way to check out.
  test('increasing quantity from the stepper reopens the cart with a checkout path', async ({ page }) => {
    const stepper = page.locator(STEPPER);
    test.skip((await stepper.count()) === 0, `No box-builder stepper on ${PRODUCT_PATH}.`);

    const sideCart = page.locator(SIDE_CART);
    test.skip(
      (await sideCart.count()) === 0,
      'This build does not use the side-cart slide-out (notice-popup mode).'
    );

    const addBtn = page.locator(ADD_BTN).first();
    const qty = page.locator(STEP_QTY);

    // Add the box — the slide-out opens on the initial add.
    await expect(addBtn).toBeEnabled();
    await addBtn.click();
    await expect(qty).toHaveText('1');
    await expect(sideCart).toHaveClass(/is-open/);

    // Close it, so the only thing that can reopen it next is the stepper.
    await page.locator(SIDE_CART_CLOSE).first().click();
    await expect(sideCart).not.toHaveClass(/is-open/);

    // Increasing the quantity must reopen the slide-out with a working checkout link.
    await page.locator(STEP_PLUS).click();
    await expect(qty).toHaveText('2');
    await expect(sideCart).toHaveClass(/is-open/);

    const checkout = page.locator(SIDE_CART_CHECKOUT);
    await expect(checkout).toBeVisible();
    await expect(checkout).toHaveAttribute('href', /checkout/i);

    // Clean up: remove the box from the basket for a clean re-run.
    await waitCartIdle(page);
    await closeSideCart(page);
    await page.locator(STEP_MINUS).click({ force: true });
    await expect(qty).toHaveText('1');
    await waitCartIdle(page);
    await closeSideCart(page);
    await page.locator(STEP_MINUS).click({ force: true });
    await expect(addBtn).toBeVisible();
  });

  // Regression: in builder mode the header/side-cart total used to stay stale
  // until the (slow) bundle qty AJAX returned. The stepper must update the
  // visible total immediately, even if that request is delayed.
  test('stepper updates cart total immediately even if the qty request is slow', async ({ page }) => {
    test.setTimeout(90_000);
    const stepper = page.locator(STEPPER);
    test.skip((await stepper.count()) === 0, `No box-builder stepper on ${PRODUCT_PATH}.`);

    const sideCart = page.locator(SIDE_CART);
    test.skip(
      (await sideCart.count()) === 0,
      'This build does not use the side-cart slide-out (notice-popup mode).'
    );

    const addBtn = page.locator(ADD_BTN).first();
    const qty = page.locator(STEP_QTY);
    const sideTotal = page.locator(`${SIDE_CART} .rd-side-cart__subtotal-amount`);

    const toggle = page.locator(TOGGLE).first();
    if (await toggle.isVisible().catch(() => false)) {
      await toggle.click({ force: true });
      await expect(page.locator(BUILDER)).toBeVisible();
    }

    await expect(addBtn).toBeEnabled();
    await addBtn.click({ force: true });
    await expect(qty).toHaveText('1');
    await expect(sideCart).toHaveClass(/is-open/);
    await expect(sideTotal).toContainText(/€|\$|£/);

    const startAmount = parseFloat((await sideTotal.innerText()).replace(/[^\d.]/g, '')) || 0;
    const unitText = await page
      .locator('.rd-summary-price, .rd-bb-title-price, .woosb-sync-price')
      .first()
      .innerText();
    const unitAmount = parseFloat(unitText.replace(/[^\d.]/g, '')) || 0;
    test.skip(!startAmount || !unitAmount, 'Could not read the box unit price.');

    await page.locator(SIDE_CART_CLOSE).first().click({ force: true });
    await expect(sideCart).not.toHaveClass(/is-open/);

    await page.route('**/admin-ajax.php*', async (route) => {
      const post = route.request().postData() || '';
      if (post.includes('rd_bb_set_qty')) {
        await new Promise((resolve) => setTimeout(resolve, 2500));
      }
      await route.continue().catch(() => {});
    });

    await page.locator(STEP_PLUS).click({ force: true });
    await expect(qty).toHaveText('2', { timeout: 5000 });
    await expect(sideTotal).toContainText(String(Math.round(startAmount + unitAmount)), {
      timeout: 5000,
    });

    await expect(page.locator('form.cart')).not.toHaveClass(/rd-bb-cart-busy/, { timeout: 15_000 });
    await page.unroute('**/admin-ajax.php*').catch(() => {});
    const closer = page.locator(SIDE_CART_CLOSE).first();
    if (await closer.isVisible().catch(() => false)) {
      await closer.click({ force: true }).catch(() => {});
    }
  });
});
