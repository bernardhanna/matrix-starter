<?php
/**
 * Contact Us admin notifications BCC Bernard.
 */

if (! defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

if (! function_exists('get_template_directory')) {
    function get_template_directory(): string {
        return dirname(__DIR__, 2);
    }
}

if (! function_exists('add_action')) {
    function add_action($hook = '', $callback = '', $priority = 10, $accepted_args = 1): void {}
}

if (! function_exists('add_filter')) {
    function add_filter($hook = '', $callback = '', $priority = 10, $accepted_args = 1): void {}
}

require_once __DIR__ . '/../../inc/rolling-donut-contact.php';

test('contact form BCC is Bernard at Matrix', function () {
    expect(matrix_rd_contact_form_bcc())->toBe('bernard@matrixinternet.ie');
});

test('contact form submissions append the Matrix BCC', function () {
    expect(matrix_rd_contact_form_append_bcc([], MATRIX_RD_CONTACT_GF_FORM_ID))
        ->toBe(['bernard@matrixinternet.ie']);
});

test('other theme forms do not get the contact BCC', function () {
    expect(matrix_rd_contact_form_append_bcc([], 36))->toBe([]);
});

test('contact BCC is not duplicated if the hidden field already has it', function () {
    expect(matrix_rd_contact_form_append_bcc(
        ['bernard@matrixinternet.ie'],
        MATRIX_RD_CONTACT_GF_FORM_ID
    ))->toBe(['bernard@matrixinternet.ie']);
});
