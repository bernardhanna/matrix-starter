// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * Cart page — quantity update flow on the standard WooCommerce cart.
 *
 * The themed /cart/ page (woocommerce/cart/cart.php) renders a normal quantity
 * number field per line (name="cart[KEY][qty]", min 0) plus an "Update cart"
 * submit button. This spec guards the bread-and-butter cart maths:
 *   1. increasing the quantity raises the line subtotal (and item count),
 *   2. decreasing the quantity lowers it again,
 *   3. setting the quantity to 0 + Update removes the line (cart empties),
 *   4. the per-line remove "×" link empties the cart.
 *
 * Note: this covers the *plain* cart page only. Box-builder products replace the
 * quantity field with a bespoke +/− stepper — that flow lives in
 * box-builder-cart.spec.js. Use a simple, purchasable product here.
 *
 * Env:
 *   BASE_URL                 - site origin (see playwright.config.cjs)
 *   RD_CART_TEST_PRODUCT_ID  - a simple, purchasable product id
 *                              (default 41707 "Branded Mug")
 */

const PRODUCT_ID = process.env.RD_CART_TEST_PRODUCT_ID || process.env.RD_COUPON_TEST_PRODUCT_ID || '41707';

const CART_FORM = '.woocommerce-cart-form';
const CART_ROW = `${CART_FORM}__cart-item[data-main-product="true"]`;
const QTY_INPUT = `${CART_FORM} input.qty`;
const UPDATE_BTN = 'button[name="update_cart"]';
const LINE_SUBTOTAL = '.product-subtotal';
const REMOVE_LINK = `${CART_FORM} td.product-remove a.remove`;

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

/** Add the simple test product, then land on the cart page. */
async function addProductAndOpenCart(page) {
  await page.goto(`/?add-to-cart=${PRODUCT_ID}`);
  await page.goto('/cart/');
  await dismissBlockingUi(page);
}

/** Read the first line's subtotal as a number (e.g. "€12.00" -> 12). */
async function readLineSubtotal(page) {
  const text = (await page.locator(LINE_SUBTOTAL).first().innerText()).trim();
  const num = parseFloat(text.replace(/[^0-9.]/g, ''));
  return Number.isFinite(num) ? num : null;
}

/** Set the first line's quantity and submit the "Update cart" form. */
async function setQuantityAndUpdate(page, value) {
  const qty = page.locator(QTY_INPUT).first();
  await qty.fill(String(value));
  // WooCommerce's cart.js enables the (otherwise inert) Update button once a
  // quantity input changes; the fill above fires that input event.
  const update = page.locator(UPDATE_BTN).first();
  await expect(update).toBeEnabled();
  await Promise.all([
    page.waitForLoadState('networkidle'),
    update.click(),
  ]);
  await dismissBlockingUi(page);
}

test.describe('Cart — quantity update', () => {
  test.beforeEach(async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await addProductAndOpenCart(page);
    // Every test needs at least one editable line in the cart to be meaningful.
    test.skip(
      (await page.locator(QTY_INPUT).count()) === 0,
      `No editable quantity field on /cart/ — is product #${PRODUCT_ID} purchasable and not "sold individually"? Set RD_CART_TEST_PRODUCT_ID.`
    );
  });

  test('increasing the quantity raises the line subtotal', async ({ page }) => {
    const subtotalAtOne = await readLineSubtotal(page);

    await setQuantityAndUpdate(page, 3);

    // The submitted quantity persists across the reload.
    await expect(page.locator(QTY_INPUT).first()).toHaveValue('3');

    const subtotalAtThree = await readLineSubtotal(page);
    if (subtotalAtOne !== null && subtotalAtThree !== null) {
      expect(subtotalAtThree).toBeGreaterThan(subtotalAtOne);
      // Linear pricing: three of the same line ≈ 3× one (allow rounding wobble).
      expect(subtotalAtThree).toBeCloseTo(subtotalAtOne * 3, 1);
    }
  });

  test('decreasing the quantity lowers the line subtotal', async ({ page }) => {
    await setQuantityAndUpdate(page, 3);
    const subtotalAtThree = await readLineSubtotal(page);

    await setQuantityAndUpdate(page, 2);
    await expect(page.locator(QTY_INPUT).first()).toHaveValue('2');

    const subtotalAtTwo = await readLineSubtotal(page);
    if (subtotalAtThree !== null && subtotalAtTwo !== null) {
      expect(subtotalAtTwo).toBeLessThan(subtotalAtThree);
    }
  });

  test('setting the quantity to zero removes the line and empties the cart', async ({ page }) => {
    await expect(page.locator(CART_ROW)).toHaveCount(1);

    await setQuantityAndUpdate(page, 0);

    await expect(page.locator(CART_ROW)).toHaveCount(0);
    await expect(page.getByText(/your cart is empty/i).first()).toBeVisible();
  });

  test('the per-line remove link empties the cart', async ({ page }) => {
    await expect(page.locator(CART_ROW)).toHaveCount(1);

    // The remove "×" is a plain GET link; navigate it directly so the theme's
    // success/notice overlay can't intercept the click.
    const removeHref = await page.locator(REMOVE_LINK).first().getAttribute('href');
    expect(removeHref, 'remove link should have an href').toBeTruthy();
    await page.goto(removeHref);
    await dismissBlockingUi(page);

    await expect(page.locator(CART_ROW)).toHaveCount(0);
    await expect(page.getByText(/your cart is currently empty/i).first()).toBeVisible();
  });
});
