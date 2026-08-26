// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * Contact Us form — smoke test.
 *
 * The /contact-us/ page renders the Theme_Forms contact form
 * (template-parts/forms/contact-us.php, `data-theme-form="contact-us"`).
 * Submission is AJAX via inc/forms/js/forms.js, which:
 *   - blocks submit when required fields are empty (checkValidity + reportValidity),
 *   - on success injects a `.theme-form-alert.is-success` banner after the form.
 *
 * What this guards (no email sent by default):
 *   1. the form and all required fields render,
 *   2. inputs accept and retain typed values,
 *   3. an empty submit is blocked by validation (no success banner),
 *   4. (opt-in) a real submit shows the success banner.
 *
 * Env:
 *   BASE_URL              - site origin (see playwright.config.cjs)
 *   CONTACT_FORM_PATH     - path to the contact page (default "/contact-us/")
 *   CONTACT_FORM_SUBMIT   - set to "1" to run the live submit test (sends a real
 *                           message — sandbox/local only)
 */

const FORM_PATH = process.env.CONTACT_FORM_PATH || '/contact-us/';
const RUN_LIVE_SUBMIT = process.env.CONTACT_FORM_SUBMIT === '1';

const FORM = 'form[data-theme-form="contact-us"]';
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

test.describe('Contact Us form', () => {
  test.beforeEach(async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await blockMarketingPopups(page);
    await page.goto(FORM_PATH);
    await dismissBlockingUi(page);
  });

  test('renders the form with all required fields', async ({ page }) => {
    const form = page.locator(FORM).first();
    test.skip(
      (await form.count()) === 0,
      `No contact form on ${FORM_PATH} (set CONTACT_FORM_PATH to the contact page).`
    );

    await expect(form).toBeVisible();
    await expect(form.locator('input[name="first_name"]')).toBeVisible();
    await expect(form.locator('input[name="last_name"]')).toBeVisible();
    await expect(form.locator('input[name="email"]')).toBeVisible();
    await expect(form.locator('textarea[name="message"]')).toBeVisible();
    await expect(form.locator('input[name="terms_conditions"]')).toBeVisible();
    await expect(form.locator('button[type="submit"]')).toBeVisible();

    // The required guards the JS submit handler relies on must be present.
    await expect(form.locator('input[name="first_name"]')).toHaveAttribute('required', '');
    await expect(form.locator('input[name="email"]')).toHaveAttribute('required', '');
    await expect(form.locator('textarea[name="message"]')).toHaveAttribute('required', '');
  });

  test('Turnstile is only active on therollingdonut.ie', async ({ page }) => {
    const form = page.locator(FORM).first();
    test.skip((await form.count()) === 0, `No contact form on ${FORM_PATH}.`);

    const host = new URL(page.url()).hostname.replace(/^www\./, '');
    const live = host === 'therollingdonut.ie';
    const widget = form.locator('.cf-turnstile');
    const api = page.locator('script[src*="challenges.cloudflare.com/turnstile"]');

    if (live) {
      await expect(widget).toHaveCount(1);
      await expect(api).toHaveCount(1);
    } else {
      await expect(widget).toHaveCount(0);
      await expect(api).toHaveCount(0);
    }
  });

  test('inputs accept and retain typed values', async ({ page }) => {
    const form = page.locator(FORM).first();
    test.skip((await form.count()) === 0, `No contact form on ${FORM_PATH}.`);

    const firstName = form.locator('input[name="first_name"]');
    const lastName = form.locator('input[name="last_name"]');
    const email = form.locator('input[name="email"]');
    const message = form.locator('textarea[name="message"]');

    await firstName.fill('Ada');
    await lastName.fill('Lovelace');
    await email.fill('ada.lovelace@example.com');
    await message.fill('Hello from the contact form smoke test.');

    await expect(firstName).toHaveValue('Ada');
    await expect(lastName).toHaveValue('Lovelace');
    await expect(email).toHaveValue('ada.lovelace@example.com');
    await expect(message).toHaveValue('Hello from the contact form smoke test.');
  });

  test('empty submit is blocked by validation (no success banner)', async ({ page }) => {
    const form = page.locator(FORM).first();
    test.skip((await form.count()) === 0, `No contact form on ${FORM_PATH}.`);

    await form.locator('button[type="submit"]').click();

    // The handler calls reportValidity() and aborts, so the first required field
    // is invalid and no success banner is ever injected.
    const firstValid = await form
      .locator('input[name="first_name"]')
      .evaluate((el) => /** @type {HTMLInputElement} */ (el).checkValidity());
    expect(firstValid).toBe(false);

    await expect(page.locator(SUCCESS_BANNER)).toHaveCount(0);
  });

  test('valid submit shows the success banner', async ({ page }) => {
    const form = page.locator(FORM).first();
    test.skip((await form.count()) === 0, `No contact form on ${FORM_PATH}.`);
    test.skip(
      !RUN_LIVE_SUBMIT,
      'Set CONTACT_FORM_SUBMIT=1 to run the live submit (sends a real message — sandbox only).'
    );
    test.skip(
      (await form.locator('.cf-turnstile').count()) > 0,
      'CAPTCHA is enabled on this form — live submit cannot be automated.'
    );

    await form.locator('input[name="first_name"]').fill('E2E');
    await form.locator('input[name="last_name"]').fill('Tester');
    await form.locator('input[name="email"]').fill(`e2e-contact-${Date.now()}@matrix-e2e.test`);
    await form.locator('textarea[name="message"]').fill('Automated smoke-test submission. Please ignore.');
    await form.locator('input[name="terms_conditions"]').setChecked(true, { force: true });

    await form.locator('button[type="submit"]').click();

    await expect(page.locator(SUCCESS_BANNER)).toBeVisible({ timeout: 15000 });
    await expect(page.locator(SUCCESS_BANNER)).toContainText(/thanks|received|touch/i);
  });
});
