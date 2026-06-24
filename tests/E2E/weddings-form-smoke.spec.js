// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * Weddings & Events enquiry form — smoke test.
 *
 * The /weddings-events/ page renders the Theme_Forms wedding enquiry form
 * (template-parts/forms/weddings-events.php, `data-theme-form="weddings-events"`).
 * On top of the shared AJAX handler (inc/forms/js/forms.js) it adds a custom
 * submit guard for the required checkbox groups declared in
 * `data-required-checkbox-groups="event_type,donuts_interested"`, which alert()s
 * and aborts when neither group has a selection.
 *
 * What this guards (no email sent by default):
 *   1. the form and its key fields render,
 *   2. inputs/checkboxes accept and retain values,
 *   3. submitting with no checkbox-group selection is blocked (native dialog,
 *      no success banner),
 *   4. (opt-in) a real submit shows the success banner.
 *
 * Env:
 *   BASE_URL              - site origin (see playwright.config.cjs)
 *   WEDDINGS_FORM_PATH    - path to the weddings page (default "/weddings-events/")
 *   WEDDINGS_FORM_SUBMIT  - set to "1" to run the live submit (sends a real
 *                           enquiry — sandbox/local only)
 */

const FORM_PATH = process.env.WEDDINGS_FORM_PATH || '/weddings-events/';
const RUN_LIVE_SUBMIT = process.env.WEDDINGS_FORM_SUBMIT === '1';

const FORM = 'form[data-theme-form="weddings-events"]';
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

/**
 * Toggle a checkbox deterministically. These inputs are nested inside their
 * <label>, so a user-style click bubbles to the label and re-fires onto the
 * control, cancelling itself out. A direct click on the input fires exactly once.
 */
async function setCheckbox(locator, checked = true) {
  await locator.evaluate((el, want) => {
    if (/** @type {HTMLInputElement} */ (el).checked !== want) {
      /** @type {HTMLInputElement} */ (el).click();
    }
  }, checked);
}

test.describe('Weddings & Events form', () => {
  test.beforeEach(async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await blockMarketingPopups(page);
    await page.goto(FORM_PATH);
    await dismissBlockingUi(page);
  });

  test('renders the form with its key fields', async ({ page }) => {
    const form = page.locator(FORM).first();
    test.skip(
      (await form.count()) === 0,
      `No weddings form on ${FORM_PATH} (set WEDDINGS_FORM_PATH to the weddings page).`
    );

    await expect(form).toBeVisible();
    await expect(form.locator('input[name="first_name"]')).toBeVisible();
    await expect(form.locator('input[name="last_name"]')).toBeVisible();
    await expect(form.locator('input[name="email"]')).toBeVisible();
    await expect(form.locator('input[name="event_date"]')).toBeVisible();
    await expect(form.locator('input[name="event_type[]"]').first()).toBeVisible();
    await expect(form.locator('input[name="donuts_interested[]"]').first()).toBeVisible();
    await expect(form.locator('input[name="terms_conditions"]')).toBeVisible();
    await expect(form.locator('button[type="submit"]')).toBeVisible();
  });

  test('inputs and checkboxes accept and retain values', async ({ page }) => {
    const form = page.locator(FORM).first();
    test.skip((await form.count()) === 0, `No weddings form on ${FORM_PATH}.`);

    const firstName = form.locator('input[name="first_name"]');
    const email = form.locator('input[name="email"]');
    const eventType = form.locator('input[name="event_type[]"]').first();
    const donuts = form.locator('input[name="donuts_interested[]"]').first();

    await firstName.fill('Ada');
    await email.fill('ada.lovelace@example.com');
    await setCheckbox(eventType, true);
    await setCheckbox(donuts, true);

    await expect(firstName).toHaveValue('Ada');
    await expect(email).toHaveValue('ada.lovelace@example.com');
    await expect(eventType).toBeChecked();
    await expect(donuts).toBeChecked();
  });

  test('submitting with no checkbox-group selection is blocked', async ({ page }) => {
    const form = page.locator(FORM).first();
    test.skip((await form.count()) === 0, `No weddings form on ${FORM_PATH}.`);

    // The checkbox-group guard runs in the capture phase, ahead of the shared
    // AJAX handler, so it alert()s and aborts before anything is sent — even with
    // the rest of the form empty. Auto-dismiss the alert and assert nothing sent.
    let alertSeen = false;
    page.on('dialog', (dialog) => {
      alertSeen = true;
      dialog.dismiss().catch(() => {});
    });

    await form.locator('button[type="submit"]').click();

    await expect.poll(() => alertSeen, { timeout: 5000 }).toBe(true);
    await expect(page.locator(SUCCESS_BANNER)).toHaveCount(0);
  });

  test('valid submit shows the success banner', async ({ page }) => {
    const form = page.locator(FORM).first();
    test.skip((await form.count()) === 0, `No weddings form on ${FORM_PATH}.`);
    test.skip(
      !RUN_LIVE_SUBMIT,
      'Set WEDDINGS_FORM_SUBMIT=1 to run the live submit (sends a real enquiry — sandbox only).'
    );
    test.skip(
      (await form.locator('.cf-turnstile').count()) > 0,
      'CAPTCHA is enabled on this form — live submit cannot be automated.'
    );

    await form.locator('input[name="first_name"]').fill('E2E');
    await form.locator('input[name="last_name"]').fill('Tester');
    await form.locator('input[name="email"]').fill(`e2e-weddings-${Date.now()}@matrix-e2e.test`);
    await form.locator('input[name="event_date"]').fill('2030-01-01');
    await setCheckbox(form.locator('input[name="event_type[]"]').first(), true);
    await setCheckbox(form.locator('input[name="donuts_interested[]"]').first(), true);
    await setCheckbox(form.locator('input[name="terms_conditions"]'), true);

    await form.locator('button[type="submit"]').click();

    await expect(page.locator(SUCCESS_BANNER)).toBeVisible({ timeout: 15000 });
    await expect(page.locator(SUCCESS_BANNER)).toContainText(/thank|received|touch/i);
  });
});
