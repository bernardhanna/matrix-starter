# Rolling Donut — developer manual QA

The **hourly email subject** lists check *groups* (about 14) plus **coverage rows** (50+ MANUAL-QA / Woo lines inside `rd_shop_journeys`, `rd_order_selftest`, `woocommerce`, etc.). Ten groups is not ten tests.

Apple Pay / Google Pay coverage rows are **skipped** until express wallets are in scope. Woo email recipients stay live-only (section 11).

AIOS login lockout is **off** (and leftover lockout rows are released) so customers cannot be IP-banned from My Account by tests or mistyped passwords. Turnstile still applies on live.

```bash
# From the WordPress root
wp msm shop-report --coverage
wp msm shop-report --email --coverage   # send the hourly report now
```

Playwright (theme directory `wp-content/themes/matrix-starter`):

```bash
npm run test:e2e:box-builder-ui
npm run test:e2e:cart-quantity
npm run test:e2e:coupons
npm run test:e2e:pickup
npm run test:e2e:product-options
npm run test:e2e:allergens
```

Test coupon: **Freeall1978** (100% off). Do not complete live card payments on production.

Repeat any storefront flow at a mobile viewport (390×844) as well as desktop.

---

## 1. Non box-builder products

Use a set box (flavours cannot be changed), e.g. a midi/large sourdough box that is **not** in builder mode.

| # | Check | Pass when |
|---|--------|-----------|
| 1.1 | **Build Your Own Box** | Button opens the builder (`#rd-bb`). |
| 1.2 | **Note to customer** | Accordion/field accepts text and the note reaches cart + packing slip as Special Requests. |
| 1.3 | **Add to basket** | Correct box (name, size, price) lands in the cart. |
| 1.4 | **Buy Now** | Goes straight to checkout with that box in the cart (skips cart page). |
| 1.5 | **Allergen Info** | Accordion `#allergen-info-open` lists flavours and allergens. |
| 1.6 | **Quantity** | + / − (or qty field) increases, decreases, and can return to 1. |

---

## 2. Box-builder products

Example: `/product/midi-sourdough-donuts-box-of-20/`

| # | Check | Pass when |
|---|--------|-----------|
| 2.1 | Open builder | **Build Your Own Box** opens `#rd-bb`. |
| 2.2 | Add items | Flavour + increases the count and fills a slot. |
| 2.3 | Remove items | Flavour − decreases the count. |
| 2.4 | Clear box | **Clear** returns count to 0 and empties slots. |
| 2.5 | Incomplete box | Add to basket stays disabled until the box is full. |
| 2.6 | Full box | At capacity, Add to basket is enabled. |
| 2.7 | Close builder | Toggle / close returns to the set-box view. |
| 2.8 | Filters | Category/search/sort actually hide and show the right flavours. |
| 2.9 | Note to customer | Saved on the box line. |
| 2.10 | Add to basket | Cart shows this box with the chosen flavours and the correct item count. |
| 2.11 | Buy Now | Checkout opens with those exact flavours. |
| 2.12 | Packing slip | Kitchen slip lists **the selected flavours**, not the default set box. |
| 2.13 | Isolation | Place a **second** order with different flavours. Slip 2 must not contain slip 1’s flavours. |
| 2.14 | Missing donuts | A 12-box must not check out with 11. If it somehow does, integrity must backfill or the order number must be obvious so bakery can fix it. |

---

## 3. Product-specific options

Cannot add to basket or Buy Now until the required option is set. After adding, the option (or logo) must appear on the **packing slip**.

| Product | Rule |
|---------|------|
| `/product/football-team-large-sourdough/` | Football team required. |
| `/product/midi-sourdough-football-team/` | Football team required. |
| `/product/special-occasions-large-sourdough/` | Special occasion required. Can remove and pick a different one. |
| `/product/special-occasions-midi-sourdough-donuts/` | Same as above. |
| `/product/special-occasion-coffee-break-combo/` | Same as above. |
| `/product/personalised-large-sourdough-donuts-box-of-12/` | Logo required. **PNG or JPG only, max 2MB.** Reject PDF and oversized files. |

Extra:

- Selecting a football team from the dropdown must **not** add a second full box — only the option on that box.
- If **additional boxes** (qty 2+) are selected, the packing slip lists **individual donuts**, not only the parent box name.

---

## 4. Cart

`/cart/`

| # | Check | Pass when |
|---|--------|-----------|
| 4.1 | Quantity | Updating qty updates line totals **and** the packing slip qty after checkout. |
| 4.2 | Coupon | Valid code applies; remove restores the total. `Freeall1978` zeroes the total. |
| 4.3 | Proceed to checkout | Lands on `/checkout/` with the same items. |
| 4.4 | Express wallets | No Stripe Link / Apple Pay / Google Pay buttons on cart or product pages (checkout only). |

---

## 5. Checkout

`/checkout/`

| # | Check | Pass when |
|---|--------|-----------|
| 5.1 | 5pm cutoff | Copy says orders must be in by **5pm**. Iconic same-day cutoff is **17:00**. WP timezone **Europe/Dublin** (DST-safe). |
| 5.2 | Methods | **Delivery** and **Free collection** always both show. |
| 5.3 | Switch back | Choose collection, continue to date/billing, go back — both methods show again. Collection picker visible. |
| 5.4 | Collection picker | Choosing Free collection reveals the pickup location field. |
| 5.5 | Delivery dates | **Today is never** offered as a delivery date. |
| 5.6 | Collection + foreign billing | With collection, billing country can be UK, USA, Australia, France, Canada, Germany, Spain. |
| 5.7 | Coupon toggle | **Click here** opens the coupon field. Apply and remove work. |
| 5.8 | Login | Customer can log in from checkout. |
| 5.9 | Different billing | On delivery, “billing address different from delivery” works; billing country can differ. |
| 5.10 | Payments | Stripe (or enabled gateway) can complete a test order. Watch for cart **Error code** regressions. |

---

## 6. Admin / packing slips

| # | Check | Pass when |
|---|--------|-----------|
| 6.1 | Eircode | Order screen has `#rd_custom_shipping_eircode`. Saving it updates `_custom_shipping_eircode` and shipping postcode. |
| 6.2 | Driver note | Checkout “note to delivery driver” prints on the packing slip. |
| 6.3 | Staff notes | Private/public notes added in admin print. |
| 6.4 | Noise | Woo “Email … sent.” / status-change / Stripe charge logs do **not** print. |

---

## 7. Forms

| # | Check | Pass when |
|---|--------|-----------|
| 7.1 | Klaviyo footer | Homepage footer form `VEZU7S` + consent checkbox. Submitting with consent triggers signup. |
| 7.2 | Klaviyo popup | Popup still hydrates from the same onsite loader. |
| 7.3 | Contact | `/contact-us/` form works. **Turnstile only on https://therollingdonut.ie/** — never on localhost. |
| 7.4 | Weddings | `/weddings-events/` same Turnstile rule. |

---

## 8. My Account

`/my-account/`

| # | Check | Pass when |
|---|--------|-----------|
| 8.1 | Login | Valid customer signs in. |
| 8.2 | Forgot password | Reset key works; user can sign in with the new password. |
| 8.3 | Register | Create a throwaway user, confirm it works, **delete the user**. |
| 8.4 | Turnstile | Account forms get Turnstile **on live only**. |
| 8.5 | Order again | Restores the box; packing slip lists individual donuts. Adding extra items on top still works. |
| 8.6 | Logout | Returns to the sign-in form. |

---

## 9. Save and share cart

Vital. Staff use **Custom Order** (`/custom-order`) to share baskets.

| # | Check | Pass when |
|---|--------|-----------|
| 9.1 | Login wall | Logged-out users cannot open `/custom-order` (redirect to My Account). |
| 9.2 | Share | Logged-in staff save a cart (optional send to bernard@matrixinternet.ie). |
| 9.3 | Guest retrieve | Logged-out visitor opens `cxecrt-retrieve-cart` and sees **exactly** those items. |
| 9.4 | Isolation | Repeat with a **different** basket. The second link must not show the first basket. |

---

## 10. Old bugs — do not reintroduce

- Donuts missing from boxes (11 in a 12-box) with the shortfall only visible on the packing slip.
- Cart payment dead with a generic **Error code**.
- Custom orders / cookies attaching a previous order’s options onto the next order.
- Allergens disappearing from donut product pages.
- 5pm cutoff drifting when the clocks change (timezone not Europe/Dublin).

---

## 11. WooCommerce emails — live only

**Skip on localhost / test.** On live, WooCommerce → Settings → Emails must match:

| Email | Recipient |
|-------|-----------|
| New order | bakery@therollingdonut.net, info@therollingdonut.ie |
| Cancelled order (admin) | info@therollingdonut.ie, bakery@therollingdonut.net |
| Failed order (admin) | info@therollingdonut.ie |
| Payment gateway enabled | info@therollingdonut.ie |
| Payment Authentication Requested | info@therollingdonut.ie |
| Customer emails (processing, completed, note, reset, new account, …) | Customer |

Hourly MSM skips this row on local (`qa_profile` development) and asserts it on live.

---

## Automation map

| Area | MSM check | Playwright / PHP |
|------|-----------|------------------|
| Orders, slips, box integrity | `rd_order_selftest` | — |
| Options, allergens, cutoff, eircode | `rd_shop_journeys` | `product-options`, `allergens` |
| Share cart | `rd_share_cart` | `share-cart.spec.js` |
| Order again | `rd_order_again` | `order-again.spec.js` |
| Custom order access | `rd_custom_order_access` | `custom-order-access.spec.js` |
| My Account | `rd_customer_login` | `my-account-auth.spec.js` |
| Klaviyo | `rd_klaviyo_forms` | `klaviyo-signup.spec.js` |
| Turnstile | `rd_turnstile` | contact / weddings specs |
| Box builder UI | storefront rows in `rd_shop_journeys` | `box-builder-builder.spec.js` |
| Cart qty / coupons | `rd_shop_journeys` + `wc_coupon` | `cart-quantity`, `coupons` |
| Checkout methods | `rd_shop_journeys` | `checkout-pickup-visibility`, `checkout-auto-advance` |
| Payments | `wc_gateways` + `woocommerce` (Apple/Google Pay skipped) | `real-order-stripe.spec.js` (sandbox) |
| Key pages | `http_smoke` (wp-login omitted — AIOS returns 503; My Account is the customer login) | — |
| Woo emails | `rd_shop_journeys` (live only) | — |
| Login lockouts | `rd_customer_login` (AIOS lockdown disabled; lockout rows released) | `my-account-auth.spec.js` |
