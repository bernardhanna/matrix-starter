// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * Coupons — apply / remove / validation flow on the cart page.
 *
 * Guards the discount engine that shop staff and customers rely on:
 *   1. a valid percentage coupon applies, shows a discount line and lowers the total,
 *   2. removing it restores the original total,
 *   3. a non-existent code is rejected with an error,
 *   4. an expired coupon is rejected,
 *   5. a coupon with a minimum-spend higher than the cart is rejected.
 *
 * Fixtures (clearly-named, test-only coupons) are provisioned by:
 *   bash scripts/e2e-coupon-fixtures.sh up      # before running
 *   bash scripts/e2e-coupon-fixtures.sh down    # after, to clean up
 * or via npm:  npm run test:e2e:coupons:setup / :teardown
 *
 * If the fixtures are missing, the affected tests skip (rather than fail) with a
 * hint to run the setup script — matching the convention in the other e2e specs.
 *
 * Env (defaults match the fixture script):
 *   BASE_URL                  - site origin (see playwright.config.cjs)
 *   RD_COUPON_TEST_PRODUCT_ID - a simple, purchasable product id (default 41707 "Branded Mug")
 *   RD_COUPON_PERCENT         - valid 10% coupon code      (default rd-e2e-10pct)
 *   RD_COUPON_EXPIRED         - expired coupon code        (default rd-e2e-expired)
 *   RD_COUPON_MINSPEND        - €500 min-spend coupon code (default rd-e2e-min500)
 */

const PRODUCT_ID = process.env.RD_COUPON_TEST_PRODUCT_ID || '41707';
const PERCENT_CODE = process.env.RD_COUPON_PERCENT || 'rd-e2e-10pct';
const EXPIRED_CODE = process.env.RD_COUPON_EXPIRED || 'rd-e2e-expired';
const MINSPEND_CODE = process.env.RD_COUPON_MINSPEND || 'rd-e2e-min500';
const INVALID_CODE = 'rd-e2e-does-not-exist-zzz';

const COUPON_INPUT = '#coupon_code';
const APPLY_BTN = 'button[name="apply_coupon"]';
const DISCOUNT_ROW = '.cart-discount';
const REMOVE_COUPON = '.woocommerce-remove-coupon';
const TOTAL_AFTER_DISCOUNTS = '[data-title="Total after Discounts"]';

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

/** Fill + submit the cart coupon form and wait for the reload to settle. */
async function applyCoupon(page, code) {
  await page.fill(COUPON_INPUT, code);
  await Promise.all([
    page.waitForLoadState('networkidle'),
    page.click(APPLY_BTN),
  ]);
  await dismissBlockingUi(page);
}

/** Read the numeric "Total after Discounts" value (e.g. "€10.80" -> 10.8). */
async function readTotal(page) {
  const text = (await page.locator(TOTAL_AFTER_DISCOUNTS).first().innerText()).trim();
  const num = parseFloat(text.replace(/[^0-9.]/g, ''));
  return Number.isFinite(num) ? num : null;
}

test.describe('Coupons — cart apply/remove/validation', () => {
  test.beforeEach(async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await addProductAndOpenCart(page);
    // The cart must actually contain the product for any coupon test to be meaningful.
    test.skip(
      (await page.locator(COUPON_INPUT).count()) === 0,
      `No coupon field on /cart/ — is product #${PRODUCT_ID} purchasable? Set RD_COUPON_TEST_PRODUCT_ID.`
    );
  });

  test('a valid percentage coupon applies, discounts the total, and can be removed', async ({ page }) => {
    const totalBefore = await readTotal(page);

    await applyCoupon(page, PERCENT_CODE);

    // Gracefully skip if the fixture coupon hasn't been provisioned.
    const missing = await page
      .getByText(/does not exist/i)
      .first()
      .isVisible()
      .catch(() => false);
    test.skip(
      missing,
      `Coupon "${PERCENT_CODE}" not found. Run: npm run test:e2e:coupons:setup`
    );

    // A discount line appears, showing a negative amount.
    const discount = page.locator(DISCOUNT_ROW).first();
    await expect(discount).toBeVisible();
    await expect(discount).toContainText(/[-−]\s*[€$]?\s*\d/);

    // The order total drops after the discount is applied.
    const totalAfter = await readTotal(page);
    if (totalBefore !== null && totalAfter !== null) {
      expect(totalAfter).toBeLessThan(totalBefore);
    }

    // Removing the coupon restores the cart. Follow the remove link directly
    // (a plain GET URL) so the theme's success overlay can't intercept the click.
    const removeHref = await page.locator(REMOVE_COUPON).first().getAttribute('href');
    expect(removeHref, 'remove-coupon link should have an href').toBeTruthy();
    await page.goto(removeHref);
    await dismissBlockingUi(page);
    await expect(page.locator(DISCOUNT_ROW)).toHaveCount(0);

    const totalRestored = await readTotal(page);
    if (totalBefore !== null && totalRestored !== null) {
      expect(totalRestored).toBeCloseTo(totalBefore, 2);
    }
  });

  test('a non-existent coupon code is rejected with an error', async ({ page }) => {
    await applyCoupon(page, INVALID_CODE);

    await expect(page.getByText(/does not exist/i).first()).toBeVisible();
    await expect(page.locator(DISCOUNT_ROW)).toHaveCount(0);
  });

  test('an expired coupon is rejected', async ({ page }) => {
    await applyCoupon(page, EXPIRED_CODE);

    const missing = await page
      .getByText(/does not exist/i)
      .first()
      .isVisible()
      .catch(() => false);
    test.skip(
      missing,
      `Coupon "${EXPIRED_CODE}" not found. Run: npm run test:e2e:coupons:setup`
    );

    await expect(page.getByText(/expired/i).first()).toBeVisible();
    await expect(page.locator(DISCOUNT_ROW)).toHaveCount(0);
  });

  test('a coupon below its minimum spend is rejected', async ({ page }) => {
    await applyCoupon(page, MINSPEND_CODE);

    const missing = await page
      .getByText(/does not exist/i)
      .first()
      .isVisible()
      .catch(() => false);
    test.skip(
      missing,
      `Coupon "${MINSPEND_CODE}" not found. Run: npm run test:e2e:coupons:setup`
    );

    // WooCommerce: "The minimum spend for this coupon is €500.00."
    await expect(page.getByText(/minimum spend/i).first()).toBeVisible();
    await expect(page.locator(DISCOUNT_ROW)).toHaveCount(0);
  });
});
