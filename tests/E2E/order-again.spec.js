// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * "Order Again" (WooCommerce reorder) button.
 *
 * The theme overrides woocommerce/order/order-again.php to render
 *   <a class="button wc-reorder-button">Order Again</a>
 * on the View Order page, and exposes the same action as an `order-again`
 * link in the My Account → Orders list. Clicking it re-adds the previous
 * order's items to the basket and redirects to the cart.
 *
 * This spec guards that flow:
 *   1. a logged-in customer with a past order sees an Order Again button,
 *   2. clicking it lands on the cart/basket,
 *   3. the basket is populated (success notice and/or at least one line item).
 *
 * It requires a real account that already has at least one order, so it is
 * env-gated and skips cleanly when credentials (or eligible orders) are absent.
 *
 * Env:
 *   BASE_URL                 - site origin (see playwright.config.cjs)
 *   MY_ACCOUNT_PATH          - my-account base path (default "/my-account/")
 *   MY_ACCOUNT_TEST_EMAIL    - login email for an account with past orders
 *   MY_ACCOUNT_TEST_PASSWORD - login password
 */

const MY_ACCOUNT_PATH = process.env.MY_ACCOUNT_PATH || '/my-account/';
const TEST_EMAIL = process.env.MY_ACCOUNT_TEST_EMAIL || '';
const TEST_PASSWORD = process.env.MY_ACCOUNT_TEST_PASSWORD || '';

const REORDER_BTN = 'a.wc-reorder-button, a.order-again';

/** Best-effort dismissal of cookie / promo overlays that can intercept clicks. */
async function dismissBlockingUi(page) {
  for (let attempt = 0; attempt < 3; attempt += 1) {
    const closer = page
      .getByRole('button', { name: /accept|got it|close|dismiss|no thanks|close dialog/i })
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

/** Log in through the custom My Account auth card (mirrors my-account-auth.spec.js). */
async function login(page) {
  await page.goto(MY_ACCOUNT_PATH);
  await dismissBlockingUi(page);
  await expect(page.getByTestId('rd-auth-card')).toBeVisible();

  await page.getByTestId('rd-field-username').fill(TEST_EMAIL);
  await page.getByTestId('rd-field-password').fill(TEST_PASSWORD);
  await page.getByTestId('rd-form-sign-in').evaluate((form) => {
    if (!(form instanceof HTMLFormElement)) return;
    const submitter = form.querySelector('[name="login"]');
    if (submitter instanceof HTMLElement) form.requestSubmit(submitter);
    else form.requestSubmit();
  });
  await page.waitForLoadState('networkidle');

  await expect(page.locator('.woocommerce-MyAccount-navigation')).toBeVisible({ timeout: 15000 });
}

/**
 * Locate an Order Again button. WooCommerce only exposes reorder for orders in
 * eligible statuses, so it lives either as an `order-again` action in the
 * orders list or as the `wc-reorder-button` on a specific View Order page.
 * Returns a resolved Locator, or null when the account has no eligible order.
 */
async function findReorderButton(page) {
  await page.goto(`${MY_ACCOUNT_PATH}orders/`);
  await dismissBlockingUi(page);

  const listBtn = page.locator(REORDER_BTN).first();
  if (await listBtn.count()) return listBtn;

  // Fall back to walking each order's View page until one offers a reorder.
  const viewLinks = await page.locator('a.woocommerce-button.view, a.button.view').all();
  for (const link of viewLinks) {
    const href = await link.getAttribute('href');
    if (!href) continue;
    await page.goto(href);
    await dismissBlockingUi(page);
    const viewBtn = page.locator(REORDER_BTN).first();
    if (await viewBtn.count()) return viewBtn;
  }

  return null;
}

test.describe('Order Again (reorder) button', () => {
  test.beforeEach(async ({ page }) => {
    test.skip(
      !TEST_EMAIL || !TEST_PASSWORD,
      'Set MY_ACCOUNT_TEST_EMAIL and MY_ACCOUNT_TEST_PASSWORD for an account with past orders.'
    );
    await page.setViewportSize({ width: 1280, height: 900 });
    await login(page);
  });

  test('reorder button points at the order_again cart action', async ({ page }) => {
    const button = await findReorderButton(page);
    test.skip(button === null, 'No eligible past order with an Order Again button on this account.');

    await expect(button).toBeVisible();
    await expect(button).toHaveText(/order again/i);
    // The link must carry the WooCommerce reorder query arg + nonce.
    await expect(button).toHaveAttribute('href', /order_again=\d+/);
    await expect(button).toHaveAttribute('href', /_wpnonce=/);
  });

  test('clicking Order Again refills the basket and lands on the cart', async ({ page }) => {
    const button = await findReorderButton(page);
    test.skip(button === null, 'No eligible past order with an Order Again button on this account.');

    await button.click();
    await page.waitForLoadState('networkidle');
    await dismissBlockingUi(page);

    // WooCommerce redirects the reorder to the cart/basket page.
    await expect(page).toHaveURL(/cart|basket/i);

    // The previous order's items should now populate the basket: confirm via the
    // reorder success notice and/or at least one cart line item.
    const notice = page
      .locator('.woocommerce-message, .wc-block-components-notice-banner')
      .filter({ hasText: /cart|basket|added/i })
      .first();
    const cartItems = page.locator(
      'tr.woocommerce-cart-form__cart-item, .wc-block-cart-items__row, .cart_item'
    );

    await expect(notice.or(cartItems.first())).toBeVisible({ timeout: 15000 });
    expect(await cartItems.count()).toBeGreaterThan(0);
  });
});
