// @ts-check
const { test, expect } = require('@playwright/test');

const MY_ACCOUNT_PATH = process.env.MY_ACCOUNT_PATH || '/my-account/';
const TEST_EMAIL = process.env.MY_ACCOUNT_TEST_EMAIL || '';
const TEST_PASSWORD = process.env.MY_ACCOUNT_TEST_PASSWORD || '';
const REGISTER_LIVE = process.env.MY_ACCOUNT_REGISTER_SUBMIT === '1';

async function dismissBlockingUi(page) {
  for (let attempt = 0; attempt < 3; attempt += 1) {
    const closeDialog = page.getByRole('button', { name: /close dialog|no thanks/i }).first();
    if (await closeDialog.isVisible().catch(() => false)) {
      await closeDialog.click({ force: true });
      await page.waitForTimeout(300);
      continue;
    }
    break;
  }

  await page.keyboard.press('Escape').catch(() => {});
}

async function openMyAccount(page) {
  await page.goto(MY_ACCOUNT_PATH);
  await dismissBlockingUi(page);
  await expect(page.getByTestId('rd-auth-card')).toBeVisible();
}

async function submitAuthForm(page, formTestId, submitName) {
  await page.getByTestId(formTestId).evaluate(
    (form, buttonName) => {
      if (!(form instanceof HTMLFormElement)) {
        return;
      }

      const submitter = form.querySelector(`[name="${buttonName}"]`);
      if (submitter instanceof HTMLElement) {
        form.requestSubmit(submitter);
        return;
      }

      form.requestSubmit();
    },
    submitName
  );
}

async function expectAuthError(page, pattern) {
  const alert = page.getByRole('alert').filter({ hasText: pattern }).first();
  const inline = page.locator('.text-red-600').filter({ hasText: pattern }).first();

  await expect(alert.or(inline)).toBeVisible({ timeout: 10000 });
}

async function fillRegisterForm(page, overrides = {}) {
  const data = {
    firstName: 'E2E',
    lastName: 'Tester',
    email: `e2e-register-${Date.now()}@matrix-e2e.test`,
    password: 'E2eTestPass!234',
    confirmPassword: 'E2eTestPass!234',
    ...overrides,
  };

  await page.getByTestId('rd-field-first-name').fill(data.firstName);
  await page.getByTestId('rd-field-last-name').fill(data.lastName);
  await page.getByTestId('rd-field-email').fill(data.email);
  await page.getByTestId('rd-field-reg-password').fill(data.password);
  await page.getByTestId('rd-field-confirm-password').fill(data.confirmPassword);
  if (overrides.checkPrivacy !== false) {
    await page.getByTestId('rd-field-privacy').setChecked(true, { force: true });
  }

  return data;
}

test.describe('My Account auth forms', () => {
  test.beforeEach(async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await openMyAccount(page);
  });

  test('sign-in inputs accept and retain typed values', async ({ page }) => {
    const email = page.getByTestId('rd-field-username');
    const password = page.getByTestId('rd-field-password');

    await email.fill('signin.test@example.com');
    await password.fill('SecretPass!99');

    await expect(email).toHaveValue('signin.test@example.com');
    await expect(password).toHaveValue('SecretPass!99');
    await expect(page.getByTestId('rd-submit-sign-in')).toBeEnabled();
  });

  test('register inputs accept and retain typed values', async ({ page }) => {
    await page.getByTestId('rd-tab-register').click();
    await expect(page.getByTestId('rd-form-register')).toBeVisible();

    const data = await fillRegisterForm(page, {
      firstName: 'Ada',
      lastName: 'Lovelace',
      email: 'ada.lovelace@example.com',
      password: 'RegisterPass!99',
      confirmPassword: 'RegisterPass!99',
    });

    await expect(page.getByTestId('rd-field-first-name')).toHaveValue(data.firstName);
    await expect(page.getByTestId('rd-field-last-name')).toHaveValue(data.lastName);
    await expect(page.getByTestId('rd-field-email')).toHaveValue(data.email);
    await expect(page.getByTestId('rd-field-reg-password')).toHaveValue(data.password);
    await expect(page.getByTestId('rd-field-confirm-password')).toHaveValue(data.confirmPassword);
    await expect(page.getByTestId('rd-field-privacy')).toBeChecked();
  });

  test('lost password input accepts typed value', async ({ page }) => {
    await page.getByTestId('rd-link-forgot-password').click();
    const field = page.getByTestId('rd-field-user-login');
    await field.fill('lost-password@example.com');
    await expect(field).toHaveValue('lost-password@example.com');
  });

  test('sign-in HTML5 validation blocks empty submit', async ({ page }) => {
    await page.getByTestId('rd-submit-sign-in').click();
    await expect(page.getByTestId('rd-field-username')).toBeFocused();
    await expect(page.getByTestId('rd-form-sign-in')).toBeVisible();
  });

  test('register tab shows all registration fields', async ({ page }) => {
    await page.getByTestId('rd-tab-register').click();
    await expect(page.getByTestId('rd-form-register')).toBeVisible();
    await expect(page.getByTestId('rd-field-first-name')).toBeVisible();
    await expect(page.getByTestId('rd-field-last-name')).toBeVisible();
    await expect(page.getByTestId('rd-field-email')).toBeVisible();
    await expect(page.getByTestId('rd-field-reg-password')).toBeVisible();
    await expect(page.getByTestId('rd-field-confirm-password')).toBeVisible();
    await expect(page.getByTestId('rd-field-privacy')).toBeVisible();
    await expect(page.getByTestId('rd-submit-register')).toBeVisible();
  });

  test('forgot password form is reachable from sign-in', async ({ page }) => {
    await page.getByTestId('rd-link-forgot-password').click();
    await expect(page.getByTestId('rd-form-lost-password')).toBeVisible();
    await expect(page.getByTestId('rd-field-user-login')).toBeVisible();
  });

  test('auth card has side padding on small screens', async ({ page }) => {
    const card = page.getByTestId('rd-auth-card');
    const box = await card.boundingBox();
    const viewport = page.viewportSize();

    expect(box).not.toBeNull();
    expect(viewport).not.toBeNull();

    if (box && viewport) {
      expect(box.x).toBeGreaterThan(8);
      expect(viewport.width - (box.x + box.width)).toBeGreaterThan(8);
    }
  });
});

test.describe('My Account authentication flows', () => {
  test.beforeEach(async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await openMyAccount(page);
  });

  test('invalid login shows an error and keeps auth form', async ({ page }) => {
    await page.getByTestId('rd-field-username').fill('not-a-real-user@matrix-e2e.test');
    await page.getByTestId('rd-field-password').fill('WrongPassword!99');
    await submitAuthForm(page, 'rd-form-sign-in', 'login');
    await page.waitForLoadState('networkidle');

    await expectAuthError(page, /unknown email|incorrect|invalid/i);
    await expect(page.getByTestId('rd-auth-card')).toBeVisible();
  });

  test('register rejects mismatched passwords', async ({ page }) => {
    await page.getByTestId('rd-tab-register').click();

    await fillRegisterForm(page, {
      email: `mismatch-${Date.now()}@matrix-e2e.test`,
      password: 'MatchPass!111',
      confirmPassword: 'DifferentPass!222',
    });

    await submitAuthForm(page, 'rd-form-register', 'register');
    await page.waitForLoadState('networkidle');

    await expectAuthError(page, /passwords do not match/i);
    await expect(page.getByTestId('rd-auth-card')).toBeVisible();
    await expect(page.locator('.woocommerce-MyAccount-navigation')).toHaveCount(0);
  });

  test('user can log in with valid credentials', async ({ page }) => {
    test.skip(!TEST_EMAIL || !TEST_PASSWORD, 'Set MY_ACCOUNT_TEST_EMAIL and MY_ACCOUNT_TEST_PASSWORD');

    await page.getByTestId('rd-field-username').fill(TEST_EMAIL);
    await page.getByTestId('rd-field-password').fill(TEST_PASSWORD);
    await submitAuthForm(page, 'rd-form-sign-in', 'login');
    await page.waitForLoadState('networkidle');

    await expect(page.locator('.woocommerce-MyAccount-navigation')).toBeVisible({ timeout: 15000 });
    await expect(page.getByTestId('rd-auth-card')).toHaveCount(0);
  });

  test('user can register a new account', async ({ page }) => {
    test.skip(!REGISTER_LIVE, 'Set MY_ACCOUNT_REGISTER_SUBMIT=1 to run live registration');

    await page.getByTestId('rd-tab-register').click();

    const data = await fillRegisterForm(page);
    await submitAuthForm(page, 'rd-form-register', 'register');
    await page.waitForLoadState('networkidle');

    await expect(page.locator('.woocommerce-MyAccount-navigation')).toBeVisible({ timeout: 15000 });
    await expect(page.getByTestId('rd-auth-card')).toHaveCount(0);

    await page.goto(`${MY_ACCOUNT_PATH}edit-account/`);
    await expect(page.locator('#account_first_name')).toHaveValue(data.firstName);
    await expect(page.locator('#account_last_name')).toHaveValue(data.lastName);
    await expect(page.locator('#account_email')).toHaveValue(data.email);
  });
});
