// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * Our Shops map — shop pins must render (legacy /our-shops/ Leaflet map).
 *
 * Guards the regression where markers existed in the Leaflet pane but were
 * invisible because the pin PNG 404'd, and Nominatim was preferred over
 * stored coordinates so several pins landed far outside Dublin.
 */

const SHOPS_PATH = process.env.OUR_SHOPS_PATH || '/our-shops/';

test.describe('Our Shops map', () => {
  test.beforeEach(async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await page.goto(SHOPS_PATH);
  });

  test('renders loaded shop pin markers on the map', async ({ page }) => {
    const map = page.locator('#map');
    await expect(map).toBeVisible();

    const markers = page.locator('#map img.rd-shop-map-marker, #map img.leaflet-marker-icon');
    await expect(markers.first()).toBeVisible({ timeout: 15000 });

    const loaded = await markers.evaluateAll((imgs) =>
      imgs.filter((img) => img instanceof HTMLImageElement && img.complete && img.naturalWidth > 0)
    );
    expect(loaded.length, 'shop pin image must load (not 404)').toBeGreaterThan(0);

    const inViewport = await markers.evaluateAll((imgs) => {
      const mapEl = document.getElementById('map');
      if (!mapEl) {
        return 0;
      }
      const mapRect = mapEl.getBoundingClientRect();
      return imgs.filter((img) => {
        const rect = img.getBoundingClientRect();
        return rect.width > 0
          && rect.height > 0
          && rect.right > mapRect.left
          && rect.left < mapRect.right
          && rect.bottom > mapRect.top
          && rect.top < mapRect.bottom;
      }).length;
    });
    expect(inViewport, 'at least one shop pin should be visible in the default Dublin view').toBeGreaterThan(0);
  });
});
