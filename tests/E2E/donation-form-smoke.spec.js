// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * Donation form — smoke test.
 *
 * There is currently no donation form shipped in the theme, but the
 * `npm run test:e2e:donation-form` script (and docs) reference this spec. Rather
 * than erroring with "no tests found", this suite probes DONATION_FORM_PATH and
 * skips cleanly when no Theme_Forms donation form is present. If/when a donation
 * form is added at that path, the structural and (opt-in) submit checks below
 * start exercising it automatically.
 *
 * Env:
 *   BASE_URL              - site origin (see playwright.config.cjs)
 *   DONATION_FORM_PATH    - path to the donation page (default "/donate/")
 *   DONATION_FORM_SUBMIT  - set to "1" to run the live submit (sandbox/local only)
 */

const FORM_PATH = process.env.DONATION_FORM_PATH || '/donate/';
const RUN_LIVE_SUBMIT = process.env.DONATION_FORM_SUBMIT === '1';

const FORM = 'form[data-theme-form]';
const SUCCESS_BANNER = '.theme-form-alert.is-success';

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

/** Block the Klaviyo embed so its delayed "10% off" pop-up can't overlay the form. */
async function blockMarketingPopups(page) {
  await page.route(/klaviyo\.com/i, (route) => route.abort());
}

test.describe('Donation form', () => {
  test.beforeEach(async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await blockMarketingPopups(page);
    await page.goto(FORM_PATH).catch(() => {});
    await dismissBlockingUi(page);
  });

  test('renders a donation form with a submit control', async ({ page }) => {
    const form = page.locator(FORM).first();
    test.skip(
      (await form.count()) === 0,
      `No donation form on ${FORM_PATH} (set DONATION_FORM_PATH or add a donation form).`
    );

    await expect(form).toBeVisible();
    await expect(form.locator('button[type="submit"], [type="submit"]').first()).toBeVisible();
  });

  test('valid submit shows the success banner', async ({ page }) => {
    const form = page.locator(FORM).first();
    test.skip((await form.count()) === 0, `No donation form on ${FORM_PATH}.`);
    test.skip(
      !RUN_LIVE_SUBMIT,
      'Set DONATION_FORM_SUBMIT=1 to run the live submit (sandbox/local only).'
    );
    test.skip(
      (await form.locator('.cf-turnstile').count()) > 0,
      'CAPTCHA is enabled on this form — live submit cannot be automated.'
    );

    // Fill any required text/email fields generically, then submit.
    for (const field of await form.locator('input[required], textarea[required]').all()) {
      const type = (await field.getAttribute('type')) || 'text';
      if (type === 'checkbox' || type === 'radio') {
        await field.setChecked(true, { force: true });
      } else if (type === 'email') {
        await field.fill(`e2e-donation-${Date.now()}@matrix-e2e.test`);
      } else {
        await field.fill('E2E smoke test');
      }
    }

    await form.locator('button[type="submit"], [type="submit"]').first().click();

    await expect(page.locator(SUCCESS_BANNER)).toBeVisible({ timeout: 15000 });
  });
});
