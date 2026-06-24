# Tests

Quality checks live in the theme repo. Set `BASE_URL` in `.env` (or export it) to match your Local port.

---

## Environment

```bash
cp .env.example .env
# BASE_URL=http://localhost:10029/
```

---

## PHP (Pest)

```bash
composer install
npm run test:php
```

---

## Playwright (E2E)

```bash
npm run test:e2e
npm run test:e2e:network          # multisite smoke
npm run test:e2e:forms            # contact + weddings + donation + my-account forms
npm run test:e2e:contact-form
npm run test:e2e:weddings-form
npm run test:e2e:donation-form
npm run test:e2e:myaccount        # My Account sign-in/register forms
npm run test:watch                # UI mode
```

My Account auth forms (structure, inputs, validation — no test user required):

```bash
MY_ACCOUNT_PATH="/my-account/" npm run test:e2e:myaccount
```

### My Account — live login & register

These tests submit real sign-in and register forms. Create a disposable local customer first, then run the live suite.

From the theme directory (`wp-content/themes/matrix-starter`):

```bash
# 1. Create a test customer (run from WordPress root — see WP_PATH in .env)
wp user create e2e-customer customer@example.com --role=customer --user_pass='YourTestPass!99'

# 2. Run live auth flows (login + register)
MY_ACCOUNT_TEST_EMAIL="customer@example.com" \
MY_ACCOUNT_TEST_PASSWORD="YourTestPass!99" \
MY_ACCOUNT_REGISTER_SUBMIT=1 \
npm run test:e2e:myaccount:live
```

If `wp` is not on your PATH, use the site root from `.env`:

```bash
cd "/Users/bernardhanna/Local Sites/rollingdonut/app/public"
wp user create e2e-customer customer@example.com --role=customer --user_pass='YourTestPass!99'
```

**Notes:**
- `MY_ACCOUNT_REGISTER_SUBMIT=1` creates a new user each run (unique email). Safe for local only.
- Login-only (skip register): omit `MY_ACCOUNT_REGISTER_SUBMIT=1`.
- Register-only: set `MY_ACCOUNT_REGISTER_SUBMIT=1` and omit the email/password vars (login test will skip).
- Re-run user create only if the account was deleted; otherwise WP-CLI will report the user already exists.

### Order Again (reorder button)

Guards the WooCommerce "Order Again" button (theme override in
`woocommerce/order/order-again.php`). It requires a logged-in customer who
already has at least one past order, so it skips cleanly when credentials are
missing or the account has no eligible order.

```bash
MY_ACCOUNT_TEST_EMAIL="customer@example.com" \
MY_ACCOUNT_TEST_PASSWORD="YourTestPass!99" \
npm run test:e2e:order-again
```

Checks that the button carries the `order_again` + nonce query args, and that
clicking it lands on the cart/basket with the previous order's items restored.

## Theme Options (admin)

WP Admin → **Theme Options** → **Tests** tab:

- **Run tests now** — PHP structural + live page checks (AJAX)
- **Run tests on save** — runs once when you save Theme Options

Browser interaction tests still run from the theme directory via `npm run test:e2e:myaccount`.

Override URL:

```bash
BASE_URL="http://localhost:10029/" npm run test:e2e:network
```

Theme forms (Contact Us + Weddings & Events) run structural, value-retention and
validation checks without sending anything. The live submit step is opt-in and
auto-skips when a CAPTCHA is enabled on the form.

Contact form on a custom path:

```bash
CONTACT_FORM_PATH="/contact-us/" npm run test:e2e:contact-form
CONTACT_FORM_SUBMIT=1 npm run test:e2e:contact-form          # live submit, sandbox only
```

Weddings & Events form:

```bash
WEDDINGS_FORM_PATH="/weddings-events/" npm run test:e2e:weddings-form
WEDDINGS_FORM_SUBMIT=1 npm run test:e2e:weddings-form        # live submit, sandbox only
```

**Warning:** `*_SUBMIT=1` sends real emails — use sandbox only.

Donation form (no donation form ships in the theme yet — this suite skips
cleanly until one exists at `DONATION_FORM_PATH`):

```bash
DONATION_FORM_PATH="/donate/" npm run test:e2e:donation-form
DONATION_FORM_SUBMIT=1 npm run test:e2e:donation-form:live   # sandbox only
```

---

## Accessibility

```bash
npm run test:a11y:quick
npm run test:a11y:full
```

See repo `docs/accessibility-basics.md`.

---

## Performance (Lighthouse CI)

```bash
npm run test:perf
npm run perf:open
```

---

## Broken links

```bash
npm run test:links
```

---

## Visual regression

```bash
npm run test:visual
```

---

## Full CI-style run locally

```bash
npm run ci
```

Runs: PHP tests, E2E, a11y, perf, link check (can be slow).

---

## Codegen (new E2E tests)

```bash
npm run e2e:codegen
```

Requires `BASE_URL` in `.env`.
