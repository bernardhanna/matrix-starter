// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * Interior / Woo archive heroes must keep a readable title on phones.
 * Regression: transparent absolute overlay + hidden mobile banner made
 * the white H1 vanish over the content below (e.g. Delivery / Collection).
 */

test.describe('Hero headers on small devices', () => {
  test('Our Boxes title stays in a dark band above services at 360px', async ({ page }) => {
    await page.setViewportSize({ width: 360, height: 800 });
    await page.goto('/donut-box/');

    const title = page.locator('.rd-woo-header h1').first();
    await expect(title).toBeVisible();
    await expect(title).toHaveText(/Our Boxes/i);

    const layout = await page.evaluate(() => {
      const header = document.querySelector('.rd-woo-header');
      const h1 = document.querySelector('.rd-woo-header h1');
      const services = document.querySelector('.rd-box-services-section, .services');
      if (!header || !h1) {
        return { ok: false };
      }
      const headerRect = header.getBoundingClientRect();
      const titleRect = h1.getBoundingClientRect();
      const servicesRect = services ? services.getBoundingClientRect() : null;
      const cs = getComputedStyle(h1);
      const inner = document.querySelector('.rd-woo-header__inner');
      return {
        ok: true,
        titleColor: cs.color,
        titleHeight: titleRect.height,
        headerBg: inner ? getComputedStyle(inner).backgroundColor : '',
        headerPosition: inner ? getComputedStyle(inner).position : '',
        titleBottom: titleRect.bottom,
        servicesTop: servicesRect ? servicesRect.top : null,
        headerHeight: headerRect.height,
      };
    });

    expect(layout.ok).toBe(true);
    expect(layout.titleHeight).toBeGreaterThan(16);
    expect(layout.headerHeight).toBeGreaterThan(80);
    expect(layout.headerPosition).toBe('relative');
    if (layout.servicesTop !== null) {
      expect(layout.titleBottom).toBeLessThanOrEqual(layout.servicesTop + 1);
    }
  });

  test('Our Shops page title is visible at 360px', async ({ page }) => {
    await page.setViewportSize({ width: 360, height: 800 });
    await page.goto('/our-shops/');

    const title = page.locator('.rd-page-header h1').first();
    await expect(title).toBeVisible();
    await expect(title).toHaveText(/Our Shops/i);

    const height = await title.evaluate((el) => el.getBoundingClientRect().height);
    expect(height).toBeGreaterThan(16);

    const headerBg = await page.locator('.rd-page-header').evaluate((el) => getComputedStyle(el).backgroundColor);
    expect(headerBg.replace(/\s/g, '')).toMatch(/rgb\(0,\s*0,\s*0\)|#000/i);
  });
});
