/**
 * CookieScript overlays intercept Playwright clicks. Call from test.beforeEach.
 *
 * @param {import('@playwright/test').Page} page
 */
async function installCookieBlocker(page) {
  await page.addInitScript(() => {
    const zap = () => {
      document
        .querySelectorAll(
          '#cookiescript_injected, #cookiescript_injected_wrapper, [id^="cookiescript"], .cookiescript_badge'
        )
        .forEach((el) => el.remove());
    };
    zap();
    const start = () => {
      if (!document.documentElement) {
        return;
      }
      new MutationObserver(zap).observe(document.documentElement, {
        childList: true,
        subtree: true,
      });
    };
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', start);
    } else {
      start();
    }
  });
}

module.exports = { installCookieBlocker };
