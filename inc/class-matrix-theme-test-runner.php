<?php

if (!defined('ABSPATH')) {
    exit;
}

class Matrix_Theme_Test_Runner
{
    /** @var array<int, array{name: string, passed: bool, message: string}> */
    private array $results = [];

    /**
     * @return array{passed: int, failed: int, total: int, results: array<int, array{name: string, passed: bool, message: string}>}
     */
    public function run(string $suite = 'my-account-auth'): array
    {
        $this->results = [];

        if ($suite === 'all' || $suite === 'my-account-auth') {
            $this->run_my_account_auth_suite();
        }

        if ($suite === 'all' || $suite === 'product-actions') {
            $this->run_product_actions_suite();
        }

        $passed = 0;
        $failed = 0;

        foreach ($this->results as $result) {
            if ($result['passed']) {
                $passed++;
            } else {
                $failed++;
            }
        }

        return [
            'passed'  => $passed,
            'failed'  => $failed,
            'total'   => count($this->results),
            'results' => $this->results,
        ];
    }

    private function run_my_account_auth_suite(): void
    {
        $template = get_template_directory() . '/woocommerce/myaccount/form-login.php';

        $this->assert(
            'Auth template exists',
            is_readable($template),
            $template
        );

        $contents = is_readable($template) ? (string) file_get_contents($template) : '';

        $required_markup = [
            'data-testid="rd-auth-card"'        => 'Auth card wrapper',
            'data-testid="rd-form-sign-in"'     => 'Sign-in form',
            'data-testid="rd-form-register"'   => 'Register form',
            'data-testid="rd-form-lost-password"' => 'Lost password form',
            'id="username"'                    => 'Sign-in email field',
            'id="password"'                    => 'Sign-in password field',
            'id="reg_first_name"'              => 'Register first name field',
            'id="reg_last_name"'               => 'Register last name field',
            'id="reg_email"'                   => 'Register email field',
            'id="reg_password"'                => 'Register password field',
            'id="confirm_password"'            => 'Confirm password field',
            'woocommerce-login-nonce'          => 'Sign-in nonce',
            'woocommerce-register-nonce'       => 'Register nonce',
            'woocommerce-lost-password-nonce'  => 'Lost password nonce',
        ];

        foreach ($required_markup as $needle => $label) {
            $this->assert(
                sprintf('Template contains %s', $label),
                $contents !== '' && str_contains($contents, $needle),
                $needle
            );
        }

        $this->assert(
            'Mobile padding uses compiled Tailwind utilities',
            $contents !== ''
                && str_contains($contents, 'px-4')
                && str_contains($contents, 'laptop:px-0')
                && !str_contains($contents, 'mobile:px-4'),
            'px-4 laptop:px-0'
        );

        $this->assert(
            'WooCommerce registration is enabled',
            get_option('woocommerce_enable_myaccount_registration') === 'yes',
            'woocommerce_enable_myaccount_registration'
        );

        if (!function_exists('wc_get_page_permalink')) {
            $this->assert('WooCommerce is active', false, 'wc_get_page_permalink missing');
            return;
        }

        $this->assert('WooCommerce is active', true, 'wc_get_page_permalink available');

        $my_account_url = wc_get_page_permalink('myaccount');
        $this->assert(
            'My Account page URL is configured',
            is_string($my_account_url) && $my_account_url !== '',
            (string) $my_account_url
        );

        if (!is_string($my_account_url) || $my_account_url === '') {
            return;
        }

        $response = wp_remote_get($my_account_url, [
            'timeout'   => 15,
            'sslverify' => false,
        ]);

        if (is_wp_error($response)) {
            $this->assert('My Account page is reachable', false, $response->get_error_message());
            return;
        }

        $status = (int) wp_remote_retrieve_response_code($response);
        $body   = (string) wp_remote_retrieve_body($response);

        $this->assert(
            'My Account page returns HTTP 200',
            $status === 200,
            'HTTP ' . $status
        );

        $live_checks = [
            'rd-auth-card'           => 'Auth card on live page',
            'rd-field-username'      => 'Sign-in email on live page',
            'rd-field-password'      => 'Sign-in password on live page',
            'id="registerTab"'       => 'Register tab on live page',
            'woocommerce-login-nonce' => 'Sign-in nonce on live page',
        ];

        foreach ($live_checks as $needle => $label) {
            $this->assert(
                $label,
                $body !== '' && str_contains($body, $needle),
                $needle
            );
        }

        $this->assert(
            'Registration validation hook is registered',
            has_action('woocommerce_register_post', 'matrix_rd_validate_registration_fields') !== false,
            'woocommerce_register_post'
        );

        $this->assert(
            'Registration save hook is registered',
            has_action('woocommerce_created_customer', 'matrix_rd_save_registration_fields') !== false,
            'woocommerce_created_customer'
        );

        if (function_exists('matrix_rd_validate_registration_fields')) {
            $errors = new WP_Error();
            $_POST['password']         = 'RunnerPass!111';
            $_POST['confirm_password'] = 'DifferentPass!222';
            matrix_rd_validate_registration_fields('runner', 'runner@matrix-e2e.test', $errors);
            $this->assert(
                'Registration rejects mismatched passwords',
                $errors->get_error_code() === 'password_mismatch',
                $errors->get_error_message() ?: 'password_mismatch'
            );
            unset($_POST['password'], $_POST['confirm_password']);
        }

        // ---- Sign-in works (deterministic, self-provisioned, self-cleaning) ----
        $this->assert_sign_in_works();

        // ---- Registration works (real WooCommerce path + theme field-save hooks) ----
        $this->assert_registration_works();

        // ---- Forgot / reset password works (deterministic, self-cleaning) ----
        $this->assert_forgot_password_works();

        // ---- Optional: authenticate against a real, persistent account if provided ----
        $test_email    = (string) (getenv('MATRIX_TEST_USER_EMAIL') ?: '');
        $test_password = (string) (getenv('MATRIX_TEST_USER_PASSWORD') ?: '');

        if ($test_email !== '' && $test_password !== '') {
            $user = wp_authenticate($test_email, $test_password);
            $this->assert(
                'Configured test user can authenticate',
                $user instanceof WP_User,
                is_wp_error($user) ? $user->get_error_message() : $test_email
            );
        }
    }

    /**
     * Create a throwaway customer, confirm valid credentials authenticate and a
     * wrong password is rejected, then delete the user. No emails, no leftovers.
     */
    private function assert_sign_in_works(): void
    {
        $email = 'rd-signin-' . wp_generate_password(8, false, false) . '@matrix-e2e.test';
        $pass  = 'SignIn-' . wp_generate_password(12, true, false);

        $user_id = wp_insert_user([
            'user_login' => $email,
            'user_email' => $email,
            'user_pass'  => $pass,
            'role'       => 'customer',
        ]);

        if (is_wp_error($user_id)) {
            $this->assert('Sign-in succeeds with valid credentials', false, 'Could not create test user: ' . $user_id->get_error_message());
            return;
        }

        try {
            $good = wp_authenticate($email, $pass);
            $this->assert(
                'Sign-in succeeds with valid credentials',
                $good instanceof WP_User && (int) $good->ID === (int) $user_id,
                is_wp_error($good) ? $good->get_error_message() : ('authenticated as ' . $email)
            );

            $bad = wp_authenticate($email, $pass . 'WRONG');
            $this->assert(
                'Sign-in rejects an incorrect password',
                is_wp_error($bad),
                is_wp_error($bad) ? (string) $bad->get_error_code() : 'unexpectedly authenticated'
            );
        } finally {
            wp_delete_user((int) $user_id, true);
        }
    }

    /**
     * Register a customer through WooCommerce's real creation path so the theme's
     * registration hooks fire: confirm the account is created, the first/last name
     * are saved, the chosen password can sign in, and duplicate emails are rejected.
     * Customer emails are suppressed and the synthetic account is deleted afterwards.
     */
    private function assert_registration_works(): void
    {
        if (!function_exists('wc_create_new_customer')) {
            return;
        }

        $email = 'rd-register-' . wp_generate_password(8, false, false) . '@matrix-e2e.test';
        $pass  = 'Register-' . wp_generate_password(12, true, false);

        // Simulate the submitted registration form so the field-save hook has data.
        $_POST['first_name']       = 'Reg';
        $_POST['last_name']        = 'Tester';
        $_POST['password']         = $pass;
        $_POST['confirm_password'] = $pass;

        // Never email a customer about a synthetic test account.
        $disable_email = static function () {
            return false;
        };
        add_filter('woocommerce_email_enabled_customer_new_account', $disable_email, 99);
        add_filter('pre_wp_mail', '__return_true', 99);

        $customer_id = wc_create_new_customer($email, '', $pass);

        remove_filter('pre_wp_mail', '__return_true', 99);
        remove_filter('woocommerce_email_enabled_customer_new_account', $disable_email, 99);

        try {
            if (is_wp_error($customer_id)) {
                $this->assert('Registration creates a new customer', false, $customer_id->get_error_message());
                return;
            }

            $this->assert('Registration creates a new customer', true, $email);

            $first = (string) get_user_meta((int) $customer_id, 'first_name', true);
            $last  = (string) get_user_meta((int) $customer_id, 'last_name', true);
            $this->assert(
                'Registration saves the first and last name',
                $first === 'Reg' && $last === 'Tester',
                sprintf("first='%s' last='%s'", $first, $last)
            );

            $auth = wp_authenticate($email, $pass);
            $this->assert(
                'Newly registered user can sign in',
                $auth instanceof WP_User && (int) $auth->ID === (int) $customer_id,
                is_wp_error($auth) ? $auth->get_error_message() : 'authenticated'
            );

            if (function_exists('matrix_rd_validate_registration_fields')) {
                $dup = new WP_Error();
                $_POST['password']         = $pass;
                $_POST['confirm_password'] = $pass;
                matrix_rd_validate_registration_fields('dup', $email, $dup);
                $this->assert(
                    'Registration rejects a duplicate email',
                    in_array('email_exists', $dup->get_error_codes(), true),
                    implode(',', $dup->get_error_codes()) ?: 'no error raised'
                );
            }
        } finally {
            if (!is_wp_error($customer_id)) {
                wp_delete_user((int) $customer_id, true);
            }
            unset($_POST['first_name'], $_POST['last_name'], $_POST['password'], $_POST['confirm_password']);
        }
    }

    /**
     * Exercise the lost-password lifecycle for a throwaway user: a reset key is
     * issued and validates for the right account, resetting the password lets the
     * user sign in with it, an invalid key is rejected, and an unknown email maps
     * to no account. No emails are sent and the synthetic user is deleted after.
     */
    private function assert_forgot_password_works(): void
    {
        if (!function_exists('get_password_reset_key') || !function_exists('check_password_reset_key')) {
            return;
        }

        $email   = 'rd-reset-' . wp_generate_password(8, false, false) . '@matrix-e2e.test';
        $old_pass = 'OldPass-' . wp_generate_password(12, true, false);
        $new_pass = 'NewPass-' . wp_generate_password(12, true, false);

        $user_id = wp_insert_user([
            'user_login' => $email,
            'user_email' => $email,
            'user_pass'  => $old_pass,
            'role'       => 'customer',
        ]);

        if (is_wp_error($user_id)) {
            $this->assert('Forgot-password issues a valid reset key', false, 'Could not create test user: ' . $user_id->get_error_message());
            return;
        }

        try {
            $user = get_user_by('id', (int) $user_id);

            $key = ($user instanceof WP_User) ? get_password_reset_key($user) : new WP_Error('no_user', 'User missing');
            $this->assert(
                'Forgot-password issues a valid reset key',
                is_string($key) && $key !== '',
                is_wp_error($key) ? $key->get_error_message() : 'reset key issued'
            );

            if (is_string($key) && $key !== '') {
                $checked = check_password_reset_key($key, $user->user_login);
                $this->assert(
                    'Reset key validates for the right account',
                    $checked instanceof WP_User && (int) $checked->ID === (int) $user_id,
                    is_wp_error($checked) ? $checked->get_error_message() : 'key accepted'
                );

                reset_password($user, $new_pass);
                $auth = wp_authenticate($email, $new_pass);
                $this->assert(
                    'Password reset takes effect for sign-in',
                    $auth instanceof WP_User && (int) $auth->ID === (int) $user_id,
                    is_wp_error($auth) ? $auth->get_error_message() : 'signed in with the new password'
                );
            }

            $bad = check_password_reset_key('not-a-real-reset-key', ($user instanceof WP_User) ? $user->user_login : $email);
            $this->assert(
                'Reset key rejects an invalid key',
                is_wp_error($bad),
                is_wp_error($bad) ? (string) $bad->get_error_code() : 'unexpectedly accepted'
            );

            $unknown = get_user_by('email', 'rd-nobody-' . wp_generate_password(10, false, false) . '@matrix-e2e.test');
            $this->assert(
                'Forgot-password finds no account for an unknown email',
                $unknown === false,
                $unknown === false ? 'no account, as expected' : 'unexpected match'
            );
        } finally {
            wp_delete_user((int) $user_id, true);
        }
    }

    /**
     * Floating mobile action bar (Add to Basket + Buy Now) and the inline Buy Now
     * express-checkout button. Verifies the hooks are wired, the proxy script
     * ships, the button renders for ordinary products but is suppressed for
     * box-builder products, the live product page exposes the bar, and the Buy Now
     * redirect only diverts to checkout when the buy-now flag is present.
     */
    private function run_product_actions_suite(): void
    {
        $cart_file = get_template_directory() . '/inc/rolling-donut-cart.php';
        $this->assert('Cart UX file exists', is_readable($cart_file), $cart_file);

        $this->assert(
            'Buy Now button hook is registered',
            has_action('woocommerce_after_add_to_cart_button', 'matrix_rd_render_buy_now_button') !== false,
            'woocommerce_after_add_to_cart_button'
        );
        $this->assert(
            'Buy Now redirect filter is registered',
            has_filter('woocommerce_add_to_cart_redirect', 'matrix_rd_buy_now_redirect') !== false,
            'woocommerce_add_to_cart_redirect'
        );
        $this->assert(
            'Floating action bar render hook is registered',
            has_action('wp_footer', 'matrix_rd_render_mobile_action_bar') !== false,
            'wp_footer'
        );
        $this->assert(
            'Floating action bar script hook is registered',
            has_action('wp_enqueue_scripts', 'matrix_rd_enqueue_mobile_actionbar_script') !== false,
            'wp_enqueue_scripts'
        );

        $js = get_template_directory() . '/assets/js/rolling-donut-mobile-actionbar.js';
        $this->assert('Floating action bar script asset exists', is_readable($js), $js);

        if (!function_exists('matrix_rd_render_buy_now_button') || !function_exists('wc_get_products')) {
            $this->assert('Product action functions are available', false, 'theme/WooCommerce functions missing');
            return;
        }

        $normal = $this->find_action_test_product(false);
        $box    = $this->find_action_test_product(true);

        // ---- Buy Now button renders for an ordinary product ----
        if ($normal instanceof WC_Product) {
            $html = $this->capture_buy_now_button($normal);
            $this->assert(
                'Buy Now button renders for an ordinary product',
                str_contains($html, 'rd-buy-now-button')
                    && str_contains($html, 'name="add-to-cart"')
                    && str_contains($html, 'value="' . $normal->get_id() . '"')
                    && str_contains($html, 'rd_buy_now=1'),
                $html !== '' ? $html : 'no output'
            );
        } else {
            $this->assert('Buy Now button renders for an ordinary product', false, 'no purchasable non-box product found');
        }

        // ---- Buy Now button is suppressed on box-builder products ----
        if ($box instanceof WC_Product) {
            $html = $this->capture_buy_now_button($box);
            $this->assert(
                'Buy Now button is hidden on box-builder products',
                trim($html) === '',
                $html === '' ? 'no button, as expected' : $html
            );
        }

        // ---- Live product page exposes the floating bar + Buy Now ----
        if ($normal instanceof WC_Product) {
            $body = $this->fetch_product_html($normal->get_permalink());
            if ($body !== null) {
                $live = [
                    'rd-buy-now-button'                 => 'Buy Now button on live product page',
                    'rd-mobile-actionbar'               => 'Floating action bar on live product page',
                    'rd-mobile-actionbar-add'           => 'Floating Add to Basket button on live page',
                    'rd-mobile-actionbar-buy'           => 'Floating Buy Now button on live page',
                    'rolling-donut-mobile-actionbar.js' => 'Floating bar proxy script enqueued',
                ];
                foreach ($live as $needle => $label) {
                    $this->assert($label, str_contains($body, $needle), $needle);
                }
            }
        }

        // ---- Live box-builder page must NOT show the generic floating bar ----
        if ($box instanceof WC_Product) {
            $body = $this->fetch_product_html($box->get_permalink());
            if ($body !== null) {
                $this->assert(
                    'Floating action bar hidden on box-builder product',
                    !str_contains($body, 'class="rd-mobile-actionbar"'),
                    'generic rd-mobile-actionbar should be absent on box-builder pages'
                );
            }
        }

        // ---- Variable (merch) products load the variation-validation guard ----
        $js_validation = get_template_directory() . '/assets/js/rolling-donut-variation-validation.js';
        $this->assert('Variation validation script asset exists', is_readable($js_validation), $js_validation);

        $variable = $this->find_action_test_product(false, 'variable');
        if ($variable instanceof WC_Product) {
            $body = $this->fetch_product_html($variable->get_permalink());
            if ($body !== null) {
                $this->assert(
                    'Variation validation enqueued on variable product (' . $variable->get_slug() . ')',
                    str_contains($body, 'rolling-donut-variation-validation.js'),
                    'rolling-donut-variation-validation.js'
                );
            }
        } else {
            $this->assert('Variable product available to validate', false, 'no variable product found');
        }

        // ---- Buy Now redirect only diverts to checkout when flagged ----
        if (function_exists('wc_get_checkout_url') && function_exists('matrix_rd_buy_now_redirect')) {
            if (function_exists('wc_load_cart') && is_null(WC()->session)) {
                wc_load_cart();
            }

            $home = home_url('/');
            $_REQUEST['add-to-cart'] = $normal instanceof WC_Product ? (int) $normal->get_id() : 1;

            unset($_REQUEST['rd_buy_now']);
            $no_flag = matrix_rd_buy_now_redirect($home);
            $this->assert(
                'Add to Basket keeps the normal redirect (no Buy Now flag)',
                $no_flag === $home,
                $no_flag
            );

            $_REQUEST['rd_buy_now'] = '1';
            $checkout = (string) wc_get_checkout_url();
            $flagged  = matrix_rd_buy_now_redirect($home);
            $this->assert(
                'Buy Now redirects straight to checkout',
                $flagged === $checkout && $checkout !== '',
                $flagged
            );

            unset($_REQUEST['rd_buy_now'], $_REQUEST['add-to-cart']);
        }
    }

    /**
     * Find a published product for the action tests. When $box_builder is true a
     * donut_box_builder product is returned (legacy "custom-order" id 3947 first);
     * otherwise the first purchasable, in-stock, non-box product.
     */
    private function find_action_test_product(bool $box_builder, ?string $require_type = null): ?WC_Product
    {
        if ($box_builder) {
            $legacy = wc_get_product(3947);
            if ($legacy instanceof WC_Product && $legacy->get_type() === 'donut_box_builder') {
                return $legacy;
            }
        }

        $ids = wc_get_products([
            'limit'   => 60,
            'status'  => 'publish',
            'orderby' => 'menu_order',
            'order'   => 'ASC',
            'return'  => 'ids',
        ]);

        foreach ($ids as $id) {
            $product = wc_get_product($id);
            if (!$product instanceof WC_Product) {
                continue;
            }

            $is_box = $product->get_type() === 'donut_box_builder';
            if ($require_type !== null && $product->get_type() !== $require_type) {
                continue;
            }
            if ($box_builder && $is_box) {
                return $product;
            }
            if (!$box_builder && !$is_box && $product->is_purchasable() && $product->is_in_stock()) {
                return $product;
            }
        }

        return null;
    }

    /** Render matrix_rd_render_buy_now_button() for a product and capture the HTML. */
    private function capture_buy_now_button(WC_Product $prod): string
    {
        global $product;
        $previous = $product;
        $product  = $prod;

        ob_start();
        matrix_rd_render_buy_now_button();
        $out = (string) ob_get_clean();

        $product = $previous;

        return $out;
    }

    /** Fetch a product page over HTTP, asserting reachability; returns body or null. */
    private function fetch_product_html($url): ?string
    {
        if (!is_string($url) || $url === '') {
            $this->assert('Product page URL is valid', false, (string) $url);
            return null;
        }

        $response = wp_remote_get($url, [
            'timeout'   => 15,
            'sslverify' => false,
        ]);

        if (is_wp_error($response)) {
            $this->assert('Product page is reachable (' . $url . ')', false, $response->get_error_message());
            return null;
        }

        $status = (int) wp_remote_retrieve_response_code($response);
        $this->assert('Product page returns HTTP 200 (' . $url . ')', $status === 200, 'HTTP ' . $status);

        return $status === 200 ? (string) wp_remote_retrieve_body($response) : null;
    }

    private function assert(string $name, bool $passed, string $message): void
    {
        $this->results[] = [
            'name'    => $name,
            'passed'  => $passed,
            'message' => $message,
        ];
    }
}
