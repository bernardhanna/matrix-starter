// @ts-check
const { test, expect } = require('@playwright/test');
const fs = require('fs');

/**
 * Real end-to-end paid orders through the express checkout, using the Stripe
 * TEST card (4242 4242 4242 4242). Playwright can type into Stripe's
 * cross-origin Payment Element iframe, which the in-IDE browser cannot.
 *
 * Two scenarios:
 *   1. A box product with a required product option (Football Team -> Arsenal).
 *   2. A build-your-own-box (box builder) product.
 *
 * Both use Free Collection (which has bookable dates; Delivery currently has
 * none) so we exercise the full Method -> Date -> Details -> Pay flow.
 *
 * Env:
 *   BASE_URL  - site origin (default http://localhost:10029)
 */

const BOX_OPTION_PATH = '/product/football-team-large-sourdough/';
// A build-your-own-box product that ships pre-filled to capacity, so a single
// "Add to Basket" adds a complete, valid box (matches box-builder-cart.spec.js).
const BOX_BUILDER_PATH = process.env.RD_BB_PRODUCT_PATH || '/product/midi-sourdough-donuts-box-of-20/';
const EMAIL = process.env.TEST_ORDER_EMAIL || 'test@example.com';

const SHIPPING_METHOD = 'input.shipping_method';
const PICKUP_RADIO = `${SHIPPING_METHOD}[value*="local_pickup"]`;
const PLACE_ORDER = '#place_order';

async function dismissBlockingUi(page) {
  for (let i = 0; i < 3; i += 1) {
    const closer = page
      .getByRole('button', { name: /accept|got it|close|dismiss|no thanks|close cart/i })
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

async function settle(page) {
  await page
    .waitForFunction(() => !document.querySelector('.blockOverlay'), undefined, { timeout: 15000 })
    .catch(() => {});
  await page.waitForTimeout(400);
}

async function waitForOrderReview(page, action) {
  const resp = page
    .waitForResponse((r) => /wc-ajax=update_order_review/i.test(r.url()), { timeout: 20000 })
    .catch(() => {});
  await action();
  await resp;
  await settle(page);
}

/**
 * Set the Local Pickup Plus location on the native select WITHOUT triggering an
 * order-review refresh (which rebuilds the dropdown and clears the value).
 * Returns the chosen value so callers can assert it stuck.
 */
async function ensurePickupLocation(page) {
  return page.evaluate(() => {
    const sel = document.querySelector('#rd-checkout-step-method select.pickup-location-lookup');
    if (!sel) return '';
    const opt = Array.from(sel.options).find((o) => o.value && o.value !== '');
    if (opt) sel.value = opt.value;
    return sel.value || '';
  });
}

/**
 * Regression guard for "checkout keeps asking for a time slot".
 *
 * The store runs date-only collection/delivery: the timeslot field is DISABLED
 * so no `jckwds-delivery-time` field renders. The plugin still had timeslot
 * MANDATORY on, which used to reject every order with "Please select a time
 * slot." matrix_rd_checkout_relax_timeslot_requirement() now drops that
 * requirement server-side, so a chosen date is enough.
 *
 * This previously INJECTED a hidden `jckwds-delivery-time` to force the order
 * through, which masked the bug. We now assert the field is genuinely absent and
 * inject nothing — if the server fix regresses, place-order fails and the test
 * catches it for real.
 */
async function assertNoTimeslotFieldInjected(page) {
  const hasTimeField = await page.evaluate(
    () => !!document.querySelector('form.checkout input[name="jckwds-delivery-time"], #jckwds-delivery-time')
  );
  expect(hasTimeField, 'no timeslot field renders (date-only store); the fix must work without one').toBe(false);
}

/** Fill the Stripe UPE card fields by scanning every js.stripe.com frame. */
async function fillStripeCard(page) {
  const fields = [
    { name: 'card number', re: /(1234|card number)/i, value: '4242424242424242' },
    { name: 'expiry', re: /mm\s*\/\s*yy/i, value: '1234' },
    { name: 'cvc', re: /(cvc|cvv|security)/i, value: '123' },
  ];

  // Wait until the Payment Element actually rendered an input we can target.
  await expect
    .poll(
      async () => {
        for (const frame of page.frames()) {
          if (!/js\.stripe\.com/.test(frame.url())) continue;
          const c = await frame.getByPlaceholder(/(1234|card number)/i).count().catch(() => 0);
          if (c) return true;
        }
        return false;
      },
      { timeout: 30000, message: 'Stripe card number field never appeared' }
    )
    .toBe(true);

  for (const field of fields) {
    let filled = false;
    for (const frame of page.frames()) {
      if (!/js\.stripe\.com/.test(frame.url())) continue;
      const loc = frame.getByPlaceholder(field.re).first();
      const count = await loc.count().catch(() => 0);
      if (!count) continue;
      await loc.click().catch(() => {});
      await loc.fill('').catch(() => {});
      await loc.type(field.value, { delay: 30 });
      filled = true;
      break;
    }
    if (!filled) throw new Error(`Stripe ${field.name} field not found`);
  }
}

async function completeCollectionCheckout(page, label, driverNote = '') {
  await page.goto('/checkout/');
  await dismissBlockingUi(page);
  await settle(page);

  // ---- STEP 1: Method (Free Collection) ----
  const pickup = page.locator(PICKUP_RADIO).first();
  await expect(pickup, 'pickup method available').toHaveCount(1);
  await waitForOrderReview(page, async () => {
    await pickup.check({ force: true });
  });

  // The Local Pickup Plus dropdown is rebuilt (and reset to empty) on every
  // order-review AJAX refresh, so set the location WITHOUT triggering another
  // refresh, and re-apply it right before each validation gate.
  await ensurePickupLocation(page);

  await page.locator('#rd-checkout-step-method .rd-checkout-step__continue').click();
  await page.waitForTimeout(500);

  // ---- STEP 2: Schedule (collection date) ----
  const firstDate = await page.evaluate(
    () => (window.jckwds_vars && window.jckwds_vars.bookable_dates && window.jckwds_vars.bookable_dates[0]) || null
  );
  expect(firstDate, 'a bookable collection date exists').toBeTruthy();
  await page.evaluate((d) => {
    if (window.jckwds && typeof window.jckwds.set_date === 'function') window.jckwds.set_date(d, false);
  }, firstDate);
  await page.waitForTimeout(600);
  await settle(page);
  await expect(page.locator('#jckwds-delivery-date')).toHaveValue(/\S/);

  const timeslot = page.locator('#jckwds-timeslot, select[name="jckwds-timeslot"]').first();
  if (await timeslot.count()) {
    const opts = await timeslot.locator('option').count();
    if (opts > 1) {
      await page.evaluate(() => {
        const s = document.querySelector('#jckwds-timeslot, select[name="jckwds-timeslot"]');
        if (s && s.options.length > 1) {
          s.selectedIndex = 1;
          window.jQuery ? window.jQuery(s).trigger('change') : s.dispatchEvent(new Event('change', { bubbles: true }));
        }
      });
      await settle(page);
    }
  }

  await page.locator('#rd-checkout-step-schedule .rd-checkout-step__continue').click();
  await page.waitForTimeout(500);

  // ---- STEP 3: Details (collection uses the billing block) ----
  await expect(page.locator('#billing_first_name')).toBeVisible();
  await page.locator('#billing_first_name').fill('Playwright');
  await page.locator('#billing_last_name').fill('Tester');
  await page.locator('#billing_address_1').fill('1 Test Street');
  await page.locator('#billing_city').fill('Dublin');
  await page.locator('#billing_phone').fill('0851234567');
  await page.locator('#billing_email').fill(EMAIL);

  // "Note to delivery driver" == WooCommerce order_comments (shown on the PDF
  // invoice as "Note to Driver"). It lives in the shipping block, which the
  // wizard hides for collection (pickup has no driver), so set the value
  // directly + fire input/change so it serializes with the form on submit.
  if (driverNote) {
    const set = await page.evaluate((note) => {
      const el = document.querySelector('#order_comments');
      if (!el) return 'missing';
      el.value = note;
      const jq = window.jQuery;
      if (jq) jq(el).trigger('input').trigger('change');
      else {
        el.dispatchEvent(new Event('input', { bubbles: true }));
        el.dispatchEvent(new Event('change', { bubbles: true }));
      }
      return el.value;
    }, driverNote);
    expect(set, 'order_comments / Note to delivery driver field present').toBe(driverNote);
  }

  await page.evaluate(() => {
    const jq = window.jQuery;
    const country = document.querySelector('#billing_country');
    if (country) {
      country.value = 'IE';
      jq ? jq(country).trigger('change') : country.dispatchEvent(new Event('change', { bubbles: true }));
    }
  });
  await settle(page);
  await page.evaluate(() => {
    const jq = window.jQuery;
    const state = document.querySelector('#billing_state');
    if (state && state.options) {
      const o = Array.from(state.options).find((x) => /^dublin$/i.test(x.text.trim())) || Array.from(state.options).find((x) => x.value);
      if (o) {
        state.value = o.value;
        jq ? jq(state).trigger('change') : state.dispatchEvent(new Event('change', { bubbles: true }));
      }
    }
    const ec = document.querySelector('#custom_shipping_eircode');
    if (ec) {
      ec.value = 'D01F5P2';
      jq ? jq(ec).trigger('change') : ec.dispatchEvent(new Event('change', { bubbles: true }));
    }
  });
  await settle(page);

  // ---- STEP 4: Payment ----
  await ensurePickupLocation(page);
  await page.locator('.rd-checkout-step__continue--pay').click();
  await page.waitForTimeout(800);
  await settle(page);

  // Payment: Stripe test card.
  await page.evaluate(() => {
    const r = document.querySelector('#payment input[name="payment_method"][value="stripe"]');
    if (r && !r.checked) {
      r.checked = true;
      window.jQuery ? window.jQuery(r).trigger('change') : r.dispatchEvent(new Event('change', { bubbles: true }));
    }
  });
  await settle(page);
  await fillStripeCard(page);

  // 6) Terms + place order. The checkbox is custom-styled (the real input is
  // visually replaced), so set it directly rather than clicking.
  await page.evaluate(() => {
    const t = document.querySelector('#terms');
    if (t && !t.checked) {
      t.checked = true;
      window.jQuery ? window.jQuery(t).trigger('change') : t.dispatchEvent(new Event('change', { bubbles: true }));
    }
  });

  await page
    .waitForFunction(() => !document.querySelector('.blockOverlay'), undefined, { timeout: 30000 })
    .catch(() => {});
  await page.waitForTimeout(800);
  await ensurePickupLocation(page);
  await assertNoTimeslotFieldInjected(page);

  const preSubmit = await page.evaluate(() => ({
    method: (document.querySelector('input.shipping_method:checked') || {}).value || '(none)',
    date: (document.querySelector('#jckwds-delivery-date') || {}).value || '',
    ymd: (document.querySelector('#jckwds-delivery-date-ymd') || {}).value || '',
    pickup: (document.querySelector('#rd-checkout-step-method select.pickup-location-lookup') || {}).value || '',
    terms: (document.querySelector('#terms') || {}).checked,
  }));
  console.log(`[${label}] preSubmit`, JSON.stringify(preSubmit));

  const fireSubmit = async (timeout) => {
    const wait = page
      .waitForResponse((r) => /wc-ajax=checkout/i.test(r.url()), { timeout })
      .catch(() => null);
    // Clear any stale `.processing` lock (WC's submit bails while it is set) then
    // click the place-order button, which is the path Stripe UPE hooks into.
    await page.evaluate(() => {
      const $ = window.jQuery;
      if ($) $('form.checkout').removeClass('processing');
      const btn = document.querySelector('#place_order');
      if (btn) btn.click();
    });
    return wait;
  };

  let resp = await fireSubmit(45000);
  if (!resp && !/order-received/i.test(page.url())) {
    await page.waitForFunction(() => !document.querySelector('.blockOverlay'), undefined, { timeout: 15000 }).catch(() => {});
    resp = await fireSubmit(45000);
  }
  if (resp) {
    const payload = await resp.json().catch(() => null);
    console.log(`[${label}] checkout response:`, JSON.stringify(payload));
  } else {
    const wiz = await page.evaluate(() => ({
      errs: Array.from(document.querySelectorAll('.rd-checkout-step--has-error [class*="errors-list"] li, .rd-checkout-step__errors li')).map((n) => n.textContent.trim()),
      invalid: Array.from(document.querySelectorAll('form.checkout .woocommerce-invalid')).map((n) => (n.id || n.className).slice(0, 50)),
      processing: window.jQuery ? window.jQuery('form.checkout').hasClass('processing') : null,
    }));
    console.log(`[${label}] no wc-ajax response; wizard state:`, JSON.stringify(wiz));
  }

  // The Stripe UPE confirmation redirects to the order-received page on success.
  await page.waitForURL(/order-received/i, { timeout: 120000 }).catch(() => {});

  if (!/order-received/i.test(page.url())) {
    const err = await page
      .locator('.woocommerce-error, #wc-stripe-upe-errors')
      .first()
      .innerText()
      .catch(() => '(none)');
    throw new Error(`[${label}] checkout did not reach order-received. URL=${page.url()} error=${err}`);
  }

  const m = page.url().match(/order-received\/(\d+)/);
  let orderId = m ? m[1] : '';
  if (!orderId) {
    orderId = (
      await page
        .locator('.woocommerce-order-overview__order strong, li.woocommerce-order-overview__order strong')
        .first()
        .innerText()
        .catch(() => '')
    ).trim();
  }
  return orderId;
}

test.describe.configure({ mode: 'serial' });

test.describe('Real Stripe-paid orders', () => {
  test.beforeEach(async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 1000 });
  });

  test('box product with a product option (Football Team -> Arsenal)', async ({ page }) => {
    test.setTimeout(300000);

    await page.goto(BOX_OPTION_PATH);
    await dismissBlockingUi(page);

    await page
      .locator('select[name="custom_dropdown_groups[football_team]"]')
      .selectOption('Arsenal');

    await page.locator('form.cart .single_add_to_cart_button').first().click();
    await page.waitForTimeout(2500);
    await dismissBlockingUi(page);

    const orderId = await completeCollectionCheckout(page, 'box+option');
    console.log('BOX+OPTION ORDER:', orderId);
    expect(orderId).toMatch(/\d/);
  });

  test('note to driver (order_comments) is captured at checkout', async ({ page }) => {
    test.setTimeout(300000);

    const driverNote = `Leave at side gate, ring bell twice - PW ${Date.now()}`;

    await page.goto(BOX_OPTION_PATH);
    await dismissBlockingUi(page);

    await page
      .locator('select[name="custom_dropdown_groups[football_team]"]')
      .selectOption('Arsenal');

    await page.locator('form.cart .single_add_to_cart_button').first().click();
    await page.waitForTimeout(2500);
    await dismissBlockingUi(page);

    const orderId = await completeCollectionCheckout(page, 'note-to-driver', driverNote);
    console.log('NOTE-TO-DRIVER ORDER:', orderId, '| note:', driverNote);
    expect(orderId).toMatch(/\d/);

    // Hand off to wp-cli verification (DB + PDF render check).
    fs.writeFileSync('/tmp/rd-driver-note.json', JSON.stringify({ orderId, driverNote }));
  });

  test('box builder product (build-your-own box)', async ({ page }) => {
    test.setTimeout(300000);

    await page.goto(BOX_BUILDER_PATH);
    await dismissBlockingUi(page);

    const addBtn = page.locator('form.cart .single_add_to_cart_button').first();
    await expect(addBtn).toBeEnabled();
    await addBtn.click();

    // Confirm the box actually entered the basket (stepper appears) before
    // proceeding, otherwise the checkout would have no items.
    await expect(page.locator('.rd-bb-cart-stepper--main')).toBeVisible({ timeout: 15000 });
    await page.waitForTimeout(1000);
    await dismissBlockingUi(page);

    const orderId = await completeCollectionCheckout(page, 'box-builder');
    console.log('BOX-BUILDER ORDER:', orderId);
    expect(orderId).toMatch(/\d/);
  });
});
