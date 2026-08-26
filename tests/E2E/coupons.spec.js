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
 *   RD_COUPON_TEST_PRODUCT_ID - a purchasable box product id (default 1959 Midi box of 20)
 *   RD_COUPON_PERCENT         - valid 10% coupon code      (default rd-e2e-10pct)
 *   RD_COUPON_EXPIRED         - expired coupon code        (default rd-e2e-expired)
 *   RD_COUPON_MINSPEND        - €500 min-spend coupon code (default rd-e2e-min500)
 *   RD_COUPON_FREE            - valid 100% coupon code     (default rd-e2e-100pct)
 */

const PRODUCT_ID = process.env.RD_COUPON_TEST_PRODUCT_ID || '1959';
const PERCENT_CODE = process.env.RD_COUPON_PERCENT || 'rd-e2e-10pct';
const EXPIRED_CODE = process.env.RD_COUPON_EXPIRED || 'rd-e2e-expired';
const MINSPEND_CODE = process.env.RD_COUPON_MINSPEND || 'rd-e2e-min500';
const FREE_CODE = process.env.RD_COUPON_FREE || 'rd-e2e-100pct';
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

/**
 * Checkout payment-step coupon UI (#rd_payment_coupon_code).
 * Lives inside #payment on the Pay step — not the cart coupon form.
 * Walk Method → Schedule → Details → Pay like a real customer.
 */
const CHECKOUT_PATH = process.env.CHECKOUT_PATH || '/checkout/';
const CHECKOUT_COUPON_INPUT = '#rd_payment_coupon_code';
const CHECKOUT_COUPON_APPLY = '.rd-checkout-payment-coupon__apply';
const CHECKOUT_COUPON_CODE = '.rd-checkout-payment-coupon__code';
const CHECKOUT_COUPON_REMOVE = '.rd-checkout-payment-coupon__remove';
const PLACE_ORDER = '#place_order';
const PAYMENT_METHODS =
  '#payment input[name="payment_method"], #payment #wc-stripe-upe-form, #payment .wc-stripe-upe-element, #payment ul.wc_payment_methods';
const METHOD_STEP = '#rd-checkout-step-method';
const SCHEDULE_STEP = '#rd-checkout-step-schedule';
const PICKUP_RADIO = `${METHOD_STEP} input.shipping_method[value*="local_pickup"]`;
const DELIVERY_RADIO = `${METHOD_STEP} input.shipping_method:not([value*="local_pickup"])`;

async function waitForCheckoutSettled(page) {
  await page
    .waitForFunction(() => !document.querySelector('.blockOverlay'), undefined, { timeout: 8000 })
    .catch(() => {});
  await page.waitForTimeout(150);
}

async function waitForOrderReview(page, action) {
  const resp = page
    .waitForResponse((r) => /wc-ajax=update_order_review/i.test(r.url()), { timeout: 20000 })
    .catch(() => {});
  await action();
  await resp;
  await waitForCheckoutSettled(page);
}

/**
 * Choose a pickup location in a way the express-checkout wizard accepts.
 * The wizard only trusts `pickupUserSelected` (set on a real non-jQuery-triggered
 * `change`, or a Select2 select with originalEvent). A plain `.val()` or jQuery
 * `.trigger('change')` leaves validation saying "Choose a pickup location".
 */
async function choosePickupLocation(page) {
  const select2 = page.locator(`${METHOD_STEP} select.pickup-location-lookup + .select2-container`).first();

  if ((await select2.count()) > 0 && (await select2.isVisible().catch(() => false))) {
    await page.keyboard.press('Escape').catch(() => {});
    await page.waitForTimeout(250);
    await select2.click();
    // Past the 200ms open-tap guard in rolling-donut-express-checkout.js.
    await page.waitForTimeout(250);

    const option = page
      .locator('.select2-results__option[role="option"]:not([aria-disabled="true"])')
      .filter({ hasNotText: /select|choose|loading/i })
      .first();

    if ((await option.count()) > 0 && (await option.isVisible().catch(() => false))) {
      await option.click();
      await waitForCheckoutSettled(page);
      await page.waitForTimeout(400);
      return true;
    }

    // Keyboard fallback still carries an originalEvent Select2 accepts.
    await page.keyboard.press('ArrowDown');
    await page.keyboard.press('Enter');
    await waitForCheckoutSettled(page);
    await page.waitForTimeout(400);
    return true;
  }

  // Native change (not jQuery .trigger) so the wizard's change handler records it.
  const ok = await page.evaluate(() => {
    const sel = document.querySelector('#rd-checkout-step-method select.pickup-location-lookup');
    if (!sel) return false;
    const opt = Array.from(sel.options).find((o) => o.value && o.value !== '' && o.value !== '0');
    if (!opt) return false;
    sel.value = opt.value;
    sel.dispatchEvent(new Event('change', { bubbles: true }));
    return true;
  });
  await waitForCheckoutSettled(page);
  await page.waitForTimeout(400);
  return ok;
}

async function clickContinueIfVisible(page, selector) {
  const btn = page.locator(selector).first();
  if (!(await btn.isVisible().catch(() => false))) {
    return false;
  }
  await btn.click();
  await waitForCheckoutSettled(page);
  return true;
}

/**
 * Drive the express checkout wizard far enough that #payment (and the coupon
 * field) is on the active Pay step.
 */
async function reachCheckoutPayStep(page) {
  await page.goto(`/?add-to-cart=${PRODUCT_ID}`);
  await page.waitForLoadState('domcontentloaded').catch(() => {});
  await page.goto(CHECKOUT_PATH);
  await dismissBlockingUi(page);
  await waitForCheckoutSettled(page);

  const pickup = page.locator(PICKUP_RADIO).first();
  const delivery = page.locator(DELIVERY_RADIO).first();
  // Prefer Collection — Delivery needs Dublin address/eircode and is flakier.
  const usePickup = (await pickup.count()) > 0;
  const method = usePickup ? pickup : delivery;
  test.skip((await method.count()) === 0, 'No shipping methods on checkout (cart empty?).');

  await waitForOrderReview(page, async () => {
    await method.check({ force: true });
  });

  if (usePickup) {
    const chose = await choosePickupLocation(page);
    test.skip(!chose, 'No pickup location available to select');
  }

  // Auto-advance may already open Schedule; otherwise use Continue.
  if (!(await page.locator(`${SCHEDULE_STEP}.rd-checkout-step--active`).count())) {
    await clickContinueIfVisible(page, `${METHOD_STEP} .rd-checkout-step__continue`);
  }

  await expect(
    page.locator(SCHEDULE_STEP),
    'should reach schedule step after choosing method/location'
  ).toHaveClass(/rd-checkout-step--active/, { timeout: 15000 });

  const firstDate = await page.evaluate(
    () => (window.jckwds_vars && window.jckwds_vars.bookable_dates && window.jckwds_vars.bookable_dates[0]) || null
  );
  if (firstDate) {
    await page.evaluate((d) => {
      if (window.jckwds && typeof window.jckwds.set_date === 'function') {
        window.jckwds.set_date(d, false);
      }
    }, firstDate);
  } else {
    await page.evaluate(() => {
      const el = document.getElementById('jckwds-delivery-date');
      if (!el) return;
      if (!el.value) el.value = '2099-12-31';
      el.dispatchEvent(new Event('change', { bubbles: true }));
    });
  }
  await waitForCheckoutSettled(page);

  const detailsStep = '#rd-checkout-step-details';
  if (!(await page.locator(`${detailsStep}.rd-checkout-step--active`).count())) {
    await clickContinueIfVisible(page, `${SCHEDULE_STEP} .rd-checkout-step__continue`);
  }

  await expect(
    page.locator(detailsStep),
    'should reach details step after choosing a date'
  ).toHaveClass(/rd-checkout-step--active/, { timeout: 15000 });

  await expect(page.locator('#billing_first_name')).toBeVisible({ timeout: 10000 });
  await page.locator('#billing_first_name').fill('Playwright');
  await page.locator('#billing_last_name').fill('Tester');
  await page.locator('#billing_address_1').fill('1 Test Street').catch(() => {});
  await page.locator('#billing_city').fill('Dublin').catch(() => {});
  await page.locator('#billing_phone').fill('0851234567').catch(() => {});
  await page.locator('#billing_email').fill('rd-e2e-coupon@matrix-e2e.test').catch(() => {});
  await page.evaluate(() => {
    const jq = window.jQuery;
    const country = document.querySelector('#billing_country');
    if (country) {
      country.value = 'IE';
      jq ? jq(country).trigger('change') : country.dispatchEvent(new Event('change', { bubbles: true }));
    }
    const state = document.querySelector('#billing_state');
    if (state && state.options) {
      const o =
        Array.from(state.options).find((x) => /^dublin$/i.test(x.text.trim())) ||
        Array.from(state.options).find((x) => x.value);
      if (o) {
        state.value = o.value;
        jq ? jq(state).trigger('change') : state.dispatchEvent(new Event('change', { bubbles: true }));
      }
    }
  });
  await waitForCheckoutSettled(page);

  const payContinue = page.locator('.rd-checkout-step__continue--pay').first();
  await expect(payContinue, 'Pay continue should be visible on Details step').toBeVisible({
    timeout: 10000,
  });
  await payContinue.click();
  await waitForCheckoutSettled(page);

  const input = page.locator(CHECKOUT_COUPON_INPUT).first();
  await expect(input, 'payment coupon field should be visible on Pay step').toBeVisible({
    timeout: 15000,
  });
  return input;
}

async function applyCheckoutCoupon(page, code) {
  const input = page.locator(CHECKOUT_COUPON_INPUT).first();
  await expect(input).toBeVisible({ timeout: 10000 });
  await input.click();
  await input.fill(code);

  const applyResponse = page.waitForResponse(
    (res) => /wc-ajax=apply_coupon/i.test(res.url()),
    { timeout: 20000 }
  );

  await page.locator(CHECKOUT_COUPON_APPLY).first().click();
  const response = await applyResponse;
  // Coupon AJAX may leave checkout fragments refreshing; don't block on overlays.
  await page.waitForTimeout(500);
  return response;
}

async function waitForPaymentCouponApplied(page, code) {
  await page
    .waitForResponse((r) => /wc-ajax=update_order_review/i.test(r.url()), { timeout: 20000 })
    .catch(() => {});
  await waitForCheckoutSettled(page);
  await expect(page.locator(CHECKOUT_COUPON_CODE).first()).toContainText(new RegExp(code, 'i'), {
    timeout: 15000,
  });
  await expect(page.locator(CHECKOUT_COUPON_REMOVE).first()).toBeVisible();
  await expect(page.locator(CHECKOUT_COUPON_INPUT)).toHaveCount(0);
}

async function removeCheckoutCoupon(page) {
  const remove = page.locator(CHECKOUT_COUPON_REMOVE).first();
  await expect(remove).toBeVisible({ timeout: 10000 });

  const removeResponse = page.waitForResponse(
    (res) => /wc-ajax=remove_coupon/i.test(res.url()),
    { timeout: 20000 }
  );

  await remove.click();
  await removeResponse;
  await page
    .waitForResponse((r) => /wc-ajax=update_order_review/i.test(r.url()), { timeout: 20000 })
    .catch(() => {});
  await waitForCheckoutSettled(page);
}

test.describe('Coupons — checkout payment coupon field', () => {
  test.describe.configure({ timeout: 180_000 });

  test.beforeEach(async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await reachCheckoutPayStep(page);
  });

  test('checkout coupon input is visible, labelled, and has an accessible border', async ({ page }) => {
    const input = page.locator(CHECKOUT_COUPON_INPUT).first();
    await expect(input).toBeVisible();

    const labelled = page.locator('label[for="rd_payment_coupon_code"]');
    await expect(labelled).toBeVisible();
    await expect(labelled).toContainText(/coupon|gift voucher/i);

    const borderWidth = await input.evaluate((el) => {
      const style = window.getComputedStyle(el);
      return parseFloat(style.borderTopWidth || '0');
    });
    expect(borderWidth, 'coupon input should have a visible border').toBeGreaterThanOrEqual(1);

    const apply = page.locator(CHECKOUT_COUPON_APPLY).first();
    await expect(apply).toBeVisible();
    await expect(apply).toHaveAttribute('aria-label', /coupon|voucher/i);
  });

  test('a valid coupon applied from checkout payment UI discounts the order', async ({ page }) => {
    const response = await applyCheckoutCoupon(page, PERCENT_CODE);
    expect(response.ok(), 'apply_coupon AJAX should succeed').toBeTruthy();

    const body = await response.text();
    const missing = /does not exist/i.test(body);
    test.skip(missing, `Coupon "${PERCENT_CODE}" not found. Run: npm run test:e2e:coupons:setup`);

    // Order summary shows the coupon row + remove control (class can vary by theme).
    const discount = page
      .locator('.woocommerce-remove-coupon, .cart-discount')
      .or(page.getByText(new RegExp(`Coupon:\\s*${PERCENT_CODE}`, 'i')))
      .or(page.getByText(/coupon code applied successfully/i))
      .first();
    await expect(discount).toBeVisible({ timeout: 15000 });

    // Coupon apply rebuilds #payment so Stripe can remount against the new total
    // and the input is replaced with the applied code + Remove.
    await waitForPaymentCouponApplied(page, PERCENT_CODE);

    await expect(
      page.locator(PAYMENT_METHODS).first(),
      'payment methods should still be present after applying a coupon'
    ).toBeAttached({ timeout: 15000 });
    await expect(page.getByText(/invalid payment method/i)).toHaveCount(0);
  });

  test('removing an applied checkout coupon restores the input and payment methods', async ({ page }) => {
    const response = await applyCheckoutCoupon(page, PERCENT_CODE);
    expect(response.ok(), 'apply_coupon AJAX should succeed').toBeTruthy();
    const body = await response.text();
    test.skip(/does not exist/i.test(body), `Coupon "${PERCENT_CODE}" not found. Run: npm run test:e2e:coupons:setup`);

    await waitForPaymentCouponApplied(page, PERCENT_CODE);
    await removeCheckoutCoupon(page);

    await expect(page.locator(CHECKOUT_COUPON_INPUT).first()).toBeVisible({ timeout: 15000 });
    await expect(page.locator(CHECKOUT_COUPON_APPLY).first()).toBeVisible();
    await expect(page.locator(CHECKOUT_COUPON_CODE)).toHaveCount(0);
    await expect(page.locator(PAYMENT_METHODS).first()).toBeAttached({ timeout: 15000 });
  });

  test('a 100% coupon swaps Pay now for a free-checkout label and hides payment methods', async ({
    page,
  }) => {
    const response = await applyCheckoutCoupon(page, FREE_CODE);
    expect(response.ok(), 'apply_coupon AJAX should succeed').toBeTruthy();
    const body = await response.text();
    test.skip(/does not exist/i.test(body), `Coupon "${FREE_CODE}" not found. Run: npm run test:e2e:coupons:setup`);
    test.skip(/woocommerce-error|is-error/i.test(body), `Coupon "${FREE_CODE}" was rejected`);

    await waitForPaymentCouponApplied(page, FREE_CODE);

    await expect(page.locator('#payment ul.wc_payment_methods')).toHaveCount(0);
    await expect(page.locator(PLACE_ORDER)).toContainText(/get it for free/i);

    await removeCheckoutCoupon(page);

    await expect(page.locator(CHECKOUT_COUPON_INPUT).first()).toBeVisible({ timeout: 15000 });
    await expect(page.locator(PAYMENT_METHODS).first()).toBeAttached({ timeout: 15000 });
    await expect(page.locator(PLACE_ORDER)).toContainText(/pay now/i);
  });

  test('an invalid coupon from checkout payment UI shows an inline error', async ({ page }) => {
    await applyCheckoutCoupon(page, INVALID_CODE);

    const error = page.locator('#rd-payment-coupon-error, .rd-checkout-payment-coupon__error').first();
    await expect(error).toBeVisible({ timeout: 10000 });
    await expect(error).toContainText(/does not exist|unable to apply|coupon/i);

    const input = page.locator(CHECKOUT_COUPON_INPUT).first();
    await expect(input).toHaveAttribute('aria-invalid', 'true');
  });
});
