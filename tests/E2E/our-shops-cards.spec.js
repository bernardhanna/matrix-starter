// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * Our Shops location cards must keep the legacy layout utilities
 * (side-by-side from the `mobile` 575px breakpoint, 691px action row).
 */

const SHOPS_PATH = process.env.OUR_SHOPS_PATH || '/our-shops/';

test.describe('Our Shops location cards', () => {
  test('desktop cards sit in a row with a 691px action row', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 900 });
    await page.goto(SHOPS_PATH);

    const item = page.locator('.location-item').first();
    await expect(item).toBeVisible();
    await expect(item).toHaveClass(/mobile:flex-row/);
    await expect(item.locator('.mobile\\:w-3\\/5')).toHaveCount(1);

    const styles = await item.evaluate((el) => {
      const cs = getComputedStyle(el);
      const stacked = el.querySelector('h4.max-mobile\\:block');
      const beside = el.querySelector('h4.max-mobile\\:hidden');
      const actions = el.querySelector('.max-w-max-691');
      return {
        flexDirection: cs.flexDirection,
        borderWidth: cs.borderTopWidth,
        borderRadius: cs.borderRadius,
        stackedDisplay: stacked ? getComputedStyle(stacked).display : null,
        besideDisplay: beside ? getComputedStyle(beside).display : null,
        actionsMaxWidth: actions ? getComputedStyle(actions).maxWidth : null,
        actionsFlexDirection: actions ? getComputedStyle(actions).flexDirection : null,
      };
    });

    expect(styles.flexDirection).toBe('row');
    expect(styles.borderWidth).toBe('4px');
    expect(styles.borderRadius).toBe('32px');
    expect(styles.stackedDisplay).toBe('none');
    expect(styles.besideDisplay).toBe('block');
    expect(parseFloat(styles.actionsMaxWidth || '0')).toBeCloseTo(691, 0);
    expect(styles.actionsFlexDirection).toBe('row');
  });

  test('action buttons stack full width below lg', async ({ page }) => {
    await page.setViewportSize({ width: 1000, height: 900 });
    await page.goto(SHOPS_PATH);

    const item = page.locator('.location-item').first();
    await expect(item).toBeVisible();

    const layout = await item.evaluate((el) => {
      const actions = el.querySelector('.location-item__actions');
      const collection = actions && actions.querySelector('a[href*="donut-box"], a:last-of-type');
      if (!actions || !collection) {
        return { flexDirection: '', overlap: true, wrapped: true };
      }
      const buttons = [...actions.querySelectorAll('a')];
      const first = buttons[0].getBoundingClientRect();
      const last = buttons[buttons.length - 1].getBoundingClientRect();
      return {
        flexDirection: getComputedStyle(actions).flexDirection,
        collectionWidth: last.width,
        actionsWidth: actions.getBoundingClientRect().width,
        overlap: first.right > last.left + 1 && Math.abs(first.top - last.top) < 8,
        wrapped: last.height > 64,
        collectionWhiteSpace: getComputedStyle(collection).whiteSpace,
      };
    });

    expect(layout.flexDirection).toBe('column');
    expect(layout.overlap).toBe(false);
    expect(layout.wrapped).toBe(false);
    expect(layout.collectionWidth).toBeGreaterThan(layout.actionsWidth * 0.9);
  });

  test('image column is max 45% wide above 575px', async ({ page }) => {
    await page.setViewportSize({ width: 800, height: 900 });
    await page.goto(SHOPS_PATH);

    const item = page.locator('.location-item').first();
    await expect(item).toBeVisible();

    const sizes = await item.evaluate((el) => {
      const media = el.querySelector('.location-item__media');
      if (!media) {
        return { itemWidth: 0, mediaWidth: 0, maxWidth: '' };
      }
      return {
        itemWidth: el.getBoundingClientRect().width,
        mediaWidth: media.getBoundingClientRect().width,
        maxWidth: getComputedStyle(media).maxWidth,
      };
    });

    const ratio = String(sizes.maxWidth).endsWith('%')
      ? parseFloat(sizes.maxWidth) / 100
      : parseFloat(sizes.maxWidth) / sizes.itemWidth;
    expect(ratio).toBeCloseTo(0.45, 2);
    expect(sizes.mediaWidth).toBeLessThanOrEqual(sizes.itemWidth * 0.45 + 2);
  });

  test('narrow mobile stacks the title above the image', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto(SHOPS_PATH);

    const item = page.locator('.location-item').first();
    await expect(item).toBeVisible();

    const styles = await item.evaluate((el) => {
      const stacked = el.querySelector('h4.max-mobile\\:block');
      const beside = el.querySelector('h4.max-mobile\\:hidden');
      const media = el.querySelector('.location-item__media');
      return {
        flexDirection: getComputedStyle(el).flexDirection,
        stackedDisplay: stacked ? getComputedStyle(stacked).display : null,
        besideDisplay: beside ? getComputedStyle(beside).display : null,
        mediaMaxWidth: media ? getComputedStyle(media).maxWidth : null,
      };
    });

    expect(styles.flexDirection).toBe('column');
    expect(styles.stackedDisplay).toBe('block');
    expect(styles.besideDisplay).toBe('none');
    expect(styles.mediaMaxWidth).toBe('none');
  });
});
