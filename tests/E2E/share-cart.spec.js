// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * Save & Share Cart — open flow + theme accessibility enhancements.
 *
 * The "Save & Share Cart" plugin (woocommerce-email-cart, `cxecrt-*` markup)
 * lets a customer email or copy a link to their current basket. Its modal markup
 * is injected as a direct child of <body> and is not WCAG-clean out of the box,
 * so the theme patches it at runtime in `inc/rolling-donut-a11y.php`:
 *
 *   1. orphan form controls (no <label>/aria-label/title) get an aria-label
 *      derived from their placeholder/name/id (with the `cxecrt` prefix stripped),
 *   2. the plugin's top-level container — injected outside any landmark — is
 *      exposed IN PLACE as a labelled region (role="region" + aria-label
 *      "Save and share your cart").
 *
 * The modal is opened with the plugin's documented deep-link target
 * (`#cxecrt-save-cart`): any element whose href contains it triggers the modal,
 * which is the integration point the theme/site relies on.
 *
 * This spec guards both the user-facing open flow and those theme-owned a11y
 * fixes, so a plugin update or a refactor of the enhancement script can't
 * silently regress them. If the plugin isn't active (no modal markup on the cart
 * page) the tests skip rather than fail, matching the other e2e specs.
 *
 * Env:
 *   BASE_URL                  - site origin (see playwright.config.cjs)
 *   RD_SHARE_CART_PRODUCT_ID  - a purchasable box product id used to fill the
 *                               cart (default 1959 Midi box of 20, shared with the
 *                               coupons spec).
 */

const PRODUCT_ID =
  process.env.RD_SHARE_CART_PRODUCT_ID || process.env.RD_COUPON_TEST_PRODUCT_ID || '1959';

const MODAL_CONTENT = '#cxecrt-save-share-cart-modal';
const MODAL_POPUP = '.cxecrt-component-modal-popup:not(.cxecrt-component-modal-hard-hide)';
const CLOSE_CONTROLS = '.cxecrt-cross, #cxecrt_finish_new, .cxecrt-button-done';

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

/** Put a product in the cart, then land on the cart page. */
async function addProductAndOpenCart(page) {
  await page.goto(`/?add-to-cart=${PRODUCT_ID}`);
  await page.goto('/cart/');
  await dismissBlockingUi(page);
}

/**
 * Open the Save & Share Cart modal via the plugin's deep-link target and wait
 * for it to be on-screen. Injecting an anchor exercises the same delegated
 * `[href*="#cxecrt-save-cart"]` handler that real custom buttons use.
 */
async function openShareCart(page) {
  await page.evaluate(() => {
    const a = document.createElement('a');
    a.href = '#cxecrt-save-cart';
    a.setAttribute('data-rd-share-trigger', '');
    a.textContent = 'Save & share cart';
    document.body.appendChild(a);
  });
  await page.locator('[data-rd-share-trigger]').click();

  const popup = page.locator(MODAL_POPUP);
  await expect(popup).toBeVisible();
  await expect(popup.locator(MODAL_CONTENT).first()).toBeVisible();
  return popup;
}

test.describe('Save & Share Cart — stays hidden until opened', () => {
  for (const path of ['/', '/about-us/', '/cart/']) {
    test(`does not dump the form at the bottom of ${path}`, async ({ page }) => {
      await page.setViewportSize({ width: 1280, height: 900 });
      await page.goto(path);

      const modal = page.locator('#cxecrt-save-share-cart-modal');
      if ((await modal.count()) === 0) {
        return;
      }

      await expect(modal).not.toBeInViewport();
      await expect(page.locator('#cxecrt_submit_get_link')).not.toBeInViewport();
    });
  }
});

test.describe('Save & Share Cart — open + a11y enhancements', () => {
  test.beforeEach(async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await addProductAndOpenCart(page);
    test.skip(
      (await page.locator(MODAL_CONTENT).count()) === 0,
      'No Save & Share Cart markup on /cart/ — is the woocommerce-email-cart plugin active?'
    );
  });

  test('the deep-link trigger opens the share-cart modal', async ({ page }) => {
    await openShareCart(page);
  });

  test('a cart with items is not treated as empty', async ({ page }) => {
    const popup = await openShareCart(page);

    await expect(
      popup.locator('.cxecrt-save-get-button-slide-3.cxecrt-component-slide-current')
    ).toHaveCount(0);
    await expect(popup.locator('#cxecrt_submit_get_link')).toBeVisible();
    await expect(popup.getByText('Empty cart. Please add products before saving')).toBeHidden();
  });

  test('every form control in the modal has an accessible name (theme a11y fix)', async ({ page }) => {
    const popup = await openShareCart(page);

    // Without the theme enhancement the plugin's email/landing-page fields render
    // with no programmatic name; afterwards each reachable control must have one.
    const unnamed = await popup.evaluate((root) => {
      const skip = ['hidden', 'submit', 'button', 'reset', 'image'];
      return Array.from(root.querySelectorAll('input, textarea, select'))
        .filter((el) => {
          const type = (el.getAttribute('type') || '').toLowerCase();
          if (skip.includes(type)) {
            return false;
          }
          // Ignore controls on hidden slides the user can't currently reach.
          if (el.offsetParent === null && el.getClientRects().length === 0) {
            return false;
          }
          const named =
            (el.labels && el.labels.length > 0) ||
            el.hasAttribute('aria-label') ||
            el.hasAttribute('aria-labelledby') ||
            (el.getAttribute('title') || '').trim() !== '';
          return !named;
        })
        .map((el) => el.getAttribute('name') || el.id || el.outerHTML.slice(0, 80));
    });

    expect(unnamed, `Controls missing an accessible name: ${unnamed.join(', ')}`).toEqual([]);
  });

  test('the share-cart container is exposed as a labelled region (theme a11y fix)', async ({ page }) => {
    // Checked before opening: the enhancement tags the plugin's body-level
    // container in place, and opening the modal relocates it into the popup.
    const regions = await page.evaluate(() => {
      return Array.from(document.body.children)
        .filter((el) => {
          const className = typeof el.className === 'string' ? el.className : '';
          return /cxecrt/.test(className) && el.getAttribute('role') === 'region';
        })
        .map((el) => el.getAttribute('aria-label') || '');
    });

    expect(regions.length).toBeGreaterThan(0);
    expect(regions).toContain('Save and share your cart');
    expect(regions.every((label) => label.trim() !== '')).toBe(true);
  });

  test('the modal can be closed again', async ({ page }) => {
    const popup = await openShareCart(page);

    const close = popup.locator(CLOSE_CONTROLS).first();
    test.skip((await close.count()) === 0, 'This open mode renders no close control.');

    await close.click({ force: true });
    await expect(page.locator(MODAL_POPUP)).toBeHidden();
  });
});
