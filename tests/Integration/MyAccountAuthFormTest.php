<?php

beforeEach(function () {
    if (!class_exists('Matrix_Theme_Test_Runner')) {
        require_once dirname(__DIR__, 2) . '/inc/class-matrix-theme-test-runner.php';
    }
});

test('my account auth template contains required fields and padding utilities', function () {
    $template = dirname(__DIR__, 2) . '/woocommerce/myaccount/form-login.php';

    expect(is_readable($template))->toBeTrue();

    $contents = file_get_contents($template);

    expect($contents)->toContain('data-testid="rd-form-sign-in"');
    expect($contents)->toContain('data-testid="rd-form-register"');
    expect($contents)->toContain('id="username"');
    expect($contents)->toContain('id="reg_password"');
    expect($contents)->toContain('px-4');
    expect($contents)->toContain('laptop:px-0');
    expect($contents)->not->toContain('mobile:px-4');
});

test('checkout login form has placeholders and no All-in-One reCAPTCHA markup', function () {
    $template = dirname(__DIR__, 2) . '/woocommerce/global/form-login.php';

    expect(is_readable($template))->toBeTrue();

    $contents = file_get_contents($template);

    expect($contents)->toContain("esc_attr_e( 'Enter username or email'");
    expect($contents)->toContain("esc_attr_e( 'Enter Password'");
    expect($contents)->toContain('matrix_rd_account_captcha_markup');
    expect($contents)->not->toContain('g-recaptcha');
    expect($contents)->not->toContain('aios-wc-captcha');
});

test('lost password endpoint uses a reset-password hero and white intro copy', function () {
    $header = dirname(__DIR__, 2) . '/woocommerce/custom/woocommerce-header.php';
    $lost   = dirname(__DIR__, 2) . '/woocommerce/myaccount/form-lost-password.php';

    expect(is_readable($header))->toBeTrue();
    expect(is_readable($lost))->toBeTrue();

    $header_contents = file_get_contents($header);
    $lost_contents   = file_get_contents($lost);

    expect($header_contents)->toContain("is_wc_endpoint_url('lost-password')");
    expect($header_contents)->toContain("showLostPassword: <?php echo \$is_lost_password ? 'true' : 'false'; ?>");
    expect($lost_contents)->toContain('text-white');
    expect($lost_contents)->not->toContain('text-black-font');
    expect($lost_contents)->toContain('for="user_login"');
});

test('registration auth helpers are defined in theme', function () {
    $auth_file = dirname(__DIR__, 2) . '/inc/rolling-donut-myaccount-auth.php';
    expect(is_readable($auth_file))->toBeTrue();

    $contents = file_get_contents($auth_file);
    expect($contents)->toContain('function matrix_rd_validate_registration_fields');
    expect($contents)->toContain('function matrix_rd_save_registration_fields');
    expect($contents)->toContain('password_mismatch');
    expect($contents)->toContain('woocommerce_created_customer');
});

test('theme test runner my-account-auth suite passes structural checks', function () {
    $runner = new Matrix_Theme_Test_Runner();
    $result = $runner->run('my-account-auth');

    $structural = array_filter(
        $result['results'],
        fn (array $row) => !str_contains($row['name'], 'live page')
            && !str_contains($row['name'], 'reachable')
            && !str_contains($row['name'], 'HTTP 200')
            && $row['name'] !== 'WooCommerce is active'
            && $row['name'] !== 'WooCommerce registration is enabled'
            && $row['name'] !== 'My Account page URL is configured'
    );

    foreach ($structural as $row) {
        expect($row['passed'])->toBeTrue($row['name'] . ': ' . $row['message']);
    }
});
