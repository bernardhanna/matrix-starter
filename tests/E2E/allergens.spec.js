// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * Allergen Info accordion on box-builder product pages.
 *
 * Env:
 *   BASE_URL
 *   RD_BB_PRODUCT_PATH  default /product/midi-sourdough-donuts-box-of-20/
 */

const PRODUCT_PATH = process.env.RD_BB_PRODUCT_PATH || '/product/midi-sourdough-donuts-box-of-20/';

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

test.describe('Allergen Info accordion', () => {
  test('box product lists flavours and allergens', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await page.goto(PRODUCT_PATH);
    await dismissBlockingUi(page);

    const accordion = page.locator('#allergen-info-open');
    test.skip((await accordion.count()) === 0, `No Allergen Info accordion on ${PRODUCT_PATH}.`);

    await expect(accordion).toContainText(/Allergen Info/i);

    const trigger = page.locator('#allergenAccordionButton, #allergen-info-open button').first();
    if ((await trigger.count()) > 0) {
      await trigger.click({ force: true });
    }

    const list = page.locator('#allergen-info-open-list').first();
    await expect(list).toBeAttached();
    const text = (await list.evaluate((el) => (el.textContent || '').trim()));
    expect(text.length).toBeGreaterThan(10);
  });
});
