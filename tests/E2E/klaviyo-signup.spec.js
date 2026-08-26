// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * Klaviyo storefront signup — footer embed + onsite loader (popup).
 *
 * The footer form is an empty `.klaviyo-form-VEZU7S` shell until klaviyo.js
 * hydrates it. The delayed popup is also served by that onsite loader, so a
 * missing company_id / embed is the regression we can assert without creating
 * real Klaviyo profiles on every run.
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
  await page.keyboard.press('Escape').catch(() => {});
}

test.describe('Klaviyo signup embeds', () => {
  test('homepage footer has the Klaviyo form shell and onsite loader', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await page.goto('/');
    await dismissBlockingUi(page);

    const embed = page.locator('.klaviyo-form-VEZU7S').first();
    await expect(embed).toHaveCount(1);

    const loader = page.locator('script[src*="static.klaviyo.com/onsite/js/klaviyo.js"]');
    await expect(loader).toHaveCount(1);
    await expect(loader).toHaveAttribute('src', /company_id=/);

    await expect(page.locator('.newsletter-consent__check')).toHaveCount(1);
  });

  test('homepage does not load Cloudflare Turnstile', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await page.goto('/');

    await expect(page.locator('script[src*="challenges.cloudflare.com/turnstile"]')).toHaveCount(0);
    await expect(page.locator('.cf-turnstile')).toHaveCount(0);
  });
});
