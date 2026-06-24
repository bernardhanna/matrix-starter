<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once get_template_directory() . '/inc/class-matrix-theme-test-runner.php';

function matrix_theme_test_runner(): Matrix_Theme_Test_Runner
{
    static $runner = null;

    if ($runner === null) {
        $runner = new Matrix_Theme_Test_Runner();
    }

    return $runner;
}

function matrix_theme_tests_last_result(): ?array
{
    $stored = get_option('matrix_theme_tests_last_result');
    return is_array($stored) ? $stored : null;
}

function matrix_theme_tests_store_result(array $result): void
{
    $result['ran_at'] = current_time('mysql');
    update_option('matrix_theme_tests_last_result', $result, false);
}

add_action('wp_ajax_matrix_run_theme_tests', function () {
    if (!current_user_can('edit_theme_options')) {
        wp_send_json_error(['message' => 'Unauthorized'], 403);
    }

    check_ajax_referer('matrix_run_theme_tests', 'nonce');

    $suite = isset($_POST['suite']) ? sanitize_key(wp_unslash($_POST['suite'])) : 'my-account-auth';
    $result = matrix_theme_test_runner()->run($suite);
    matrix_theme_tests_store_result($result);

    if ($result['failed'] > 0) {
        wp_send_json_error($result, 422);
    }

    wp_send_json_success($result);
});

add_action('acf/save_post', function ($post_id) {
    if ($post_id !== 'options' || !function_exists('get_field') || !function_exists('update_field')) {
        return;
    }

    $settings = get_field('theme_test_controls', 'option');
    if (!is_array($settings) || empty($settings['run_tests_on_save'])) {
        return;
    }

    $suite = !empty($settings['test_suite']) ? sanitize_key($settings['test_suite']) : 'my-account-auth';
    $result = matrix_theme_test_runner()->run($suite);
    matrix_theme_tests_store_result($result);

    $settings['run_tests_on_save'] = 0;
    update_field('theme_test_controls', $settings, 'option');

    $status = $result['failed'] > 0 ? 'error' : 'success';
    set_transient(
        'matrix_theme_tests_notice',
        [
            'status' => $status,
            'result' => $result,
        ],
        120
    );
}, 45);

add_action('admin_notices', function () {
    if (!current_user_can('edit_theme_options')) {
        return;
    }

    $notice = get_transient('matrix_theme_tests_notice');
    if (!$notice || !is_array($notice)) {
        return;
    }

    delete_transient('matrix_theme_tests_notice');

    $result = $notice['result'] ?? [];
    $passed = (int) ($result['passed'] ?? 0);
    $failed = (int) ($result['failed'] ?? 0);
    $total  = (int) ($result['total'] ?? 0);
    $class  = ($notice['status'] ?? '') === 'success' ? 'notice-success' : 'notice-error';

    echo '<div class="notice ' . esc_attr($class) . ' is-dismissible"><p>';
    echo esc_html(sprintf('Theme tests finished: %d passed, %d failed (%d total).', $passed, $failed, $total));
    echo '</p></div>';
});

add_action('acf/input/admin_enqueue_scripts', function () {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || $screen->id !== 'toplevel_page_theme-options') {
        return;
    }

    wp_enqueue_script(
        'matrix-theme-tests',
        get_template_directory_uri() . '/assets/js/admin-theme-tests.js',
        ['jquery'],
        (string) filemtime(get_template_directory() . '/assets/js/admin-theme-tests.js'),
        true
    );

    wp_localize_script('matrix-theme-tests', 'matrixThemeTests', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('matrix_run_theme_tests'),
        'suites'  => [
            ['id' => 'my-account-auth', 'label' => 'My Account auth forms'],
            ['id' => 'product-actions', 'label' => 'Floating bar + Buy Now buttons'],
            ['id' => 'all', 'label' => 'All theme tests'],
        ],
        'lastResult' => matrix_theme_tests_last_result(),
    ]);
});

add_action('acf/render_field/name=theme_tests_panel', function () {
    if (!current_user_can('edit_theme_options')) {
        return;
    }

    $last = matrix_theme_tests_last_result();
    ?>
    <div id="matrix-theme-tests-panel" class="matrix-theme-tests-panel" style="margin-top:12px;padding:16px;border:1px solid #c3c4c7;background:#fff;max-width:760px;">
        <p style="margin-top:0;">Run structural and live-page checks for the My Account sign-in and register forms.</p>
        <p>
            <label for="matrix-theme-test-suite" style="font-weight:600;">Test suite</label><br>
            <select id="matrix-theme-test-suite">
                <option value="my-account-auth">My Account auth forms</option>
                <option value="product-actions">Floating bar + Buy Now buttons</option>
                <option value="all">All theme tests</option>
            </select>
        </p>
        <p>
            <button type="button" class="button button-primary" id="matrix-run-theme-tests">Run tests now</button>
            <span id="matrix-theme-tests-status" style="margin-left:8px;"></span>
        </p>
        <pre id="matrix-theme-tests-output" style="display:none;max-height:320px;overflow:auto;background:#f6f7f7;padding:12px;border:1px solid #dcdcde;white-space:pre-wrap;"></pre>
        <?php if (is_array($last)) : ?>
            <p style="margin-bottom:0;color:#50575e;">
                Last run: <?php echo esc_html((string) ($last['ran_at'] ?? 'unknown')); ?>
                — <?php echo esc_html(sprintf('%d passed, %d failed', (int) ($last['passed'] ?? 0), (int) ($last['failed'] ?? 0))); ?>
            </p>
        <?php endif; ?>
        <p style="margin-bottom:0;color:#50575e;">
            For browser interaction tests, run
            <code>npm run test:e2e:myaccount</code>
            from the theme directory.
        </p>
    </div>
    <?php
});
