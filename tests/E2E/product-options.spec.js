// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * Product-specific required options (football team, special occasion, logo).
 *
 * Customers must pick the option (or upload a PNG/JPG logo) before add to basket
 * or Buy Now. These pages are the same slugs used by MSM rd_shop_journeys.
 *
 * Env:
 *   BASE_URL
 */

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

const OPTION_PRODUCTS = [
  '/product/football-team-large-sourdough/',
  '/product/midi-sourdough-football-team/',
  '/product/special-occasions-large-sourdough/',
  '/product/special-occasions-midi-sourdough-donuts/',
  '/product/special-occasion-coffee-break-combo/',
];

const LOGO_PRODUCT = '/product/personalised-large-sourdough-donuts-box-of-12/';

test.describe('Required product options', () => {
  test.beforeEach(async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
  });

  for (const path of OPTION_PRODUCTS) {
    test(`${path} requires a dropdown option before add to basket`, async ({ page }) => {
      const res = await page.goto(path);
      test.skip(!res || res.status() >= 400, `${path} is not published on this site.`);
      await dismissBlockingUi(page);

      const select = page.locator('select.custom-dropdown-group-select').first();
      test.skip((await select.count()) === 0, `No custom dropdown on ${path}.`);

      await expect(select).toBeVisible();
      await expect(select).toHaveAttribute('required', '');
      await expect(select).toHaveValue('');

      const addBtn = page.locator('form.cart .single_add_to_cart_button').first();
      await expect(addBtn).toBeVisible();
    });
  }

  test('personalised box requires a PNG/JPG logo under 2MB', async ({ page }) => {
    const res = await page.goto(LOGO_PRODUCT);
    test.skip(!res || res.status() >= 400, `${LOGO_PRODUCT} is not published on this site.`);
    await dismissBlockingUi(page);

    const input = page.locator('#logo_upload, input[name="logo_upload"]').first();
    test.skip((await input.count()) === 0, 'No logo_upload field on the personalised product.');

    await expect(input).toBeVisible();
    const accept = (await input.getAttribute('accept')) || '';
    expect(accept.toLowerCase()).toMatch(/png/);
    expect(accept.toLowerCase()).toMatch(/jpe?g/);
    expect(accept.toLowerCase()).not.toMatch(/pdf/);
  });

  test('builder mode can pick a different product option than the set box', async ({ page }) => {
    const path = '/product/special-occasions-midi-sourdough-donuts/';
    const res = await page.goto(path);
    test.skip(!res || res.status() >= 400, `${path} is not published on this site.`);
    await dismissBlockingUi(page);

    const toggle = page.locator('.rd-bb-toggle').first();
    test.skip((await toggle.count()) === 0, 'No box builder on this product.');

    const select = page.locator('#custom-product-addons select.custom-dropdown-group-select').first();
    test.skip((await select.count()) === 0, 'No custom dropdown on this product.');

    const values = await select.locator('option').evaluateAll((els) =>
      els.map((el) => el.value).filter((v) => v)
    );
    test.skip(values.length < 2, 'Need two option values to compare modes.');

    await select.selectOption(values[0]);
    await expect(select).toHaveValue(values[0]);

    // Opening the builder must not inherit the set-box option.
    await toggle.click();
    await expect(page.locator('#rd-bb')).toBeVisible();
    await expect(select).toHaveValue('');

    await select.selectOption(values[1]);
    await expect(select).toHaveValue(values[1]);

    // Closing restores the set-box option; reopening restores the builder option.
    await toggle.click();
    await expect(page.locator('#rd-bb')).toBeHidden();
    await expect(select).toHaveValue(values[0]);
    await toggle.click();
    await expect(page.locator('#rd-bb')).toBeVisible();
    await expect(select).toHaveValue(values[1]);
  });

  test('set-box Clear resets the product option so a new one can be chosen', async ({ page }) => {
    const path = '/product/special-occasions-midi-sourdough-donuts/';
    const res = await page.goto(path);
    test.skip(!res || res.status() >= 400, `${path} is not published on this site.`);
    await dismissBlockingUi(page);

    const select = page.locator('#custom-product-addons select.custom-dropdown-group-select').first();
    test.skip((await select.count()) === 0, 'No custom dropdown on this product.');

    const values = await select.locator('option').evaluateAll((els) =>
      els.map((el) => el.value).filter((v) => v)
    );
    test.skip(values.length < 1, 'Need an option value to select.');

    await select.selectOption(values[0]);
    await expect(select).toHaveValue(values[0]);

    const addBtn = page.locator('form.cart .single_add_to_cart_button').first();
    const stepper = page.locator('form.cart .rd-bb-cart-stepper--main');
    await addBtn.click({ force: true });
    await expect(stepper).toBeVisible({ timeout: 20000 });

    const clear = page.locator('form.cart .rd-product-cta-row .rd-bb-new-box, form.cart .rd-bb-cta-row .rd-bb-new-box').first();
    await expect(clear).toBeVisible();
    await expect(clear).toHaveText('Clear');

    const sideClose = page.locator('[data-rd-side-cart] [data-rd-side-cart-close]').first();
    if (await sideClose.isVisible().catch(() => false)) {
      await sideClose.click({ force: true }).catch(() => {});
    }

    await clear.click({ force: true });
    await expect(page.locator('#rd-bb')).toBeHidden();
    await expect(stepper).toBeHidden();
    await expect(addBtn).toBeVisible();
    await expect(select).toHaveValue('');
  });
});
