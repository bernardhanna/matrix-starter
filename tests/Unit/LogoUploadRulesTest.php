<?php
/**
 * Personalised boxes: PNG/JPG logo, max 2MB, no PDF.
 */

test('box-builder addons cap logos at 2MB PNG/JPG', function () {
    $file = dirname(__DIR__, 2) . '/../../plugins/rd-box-builder/includes/class-addons.php';
    $file = realpath($file) ?: $file;

    expect(is_readable($file))->toBeTrue();

    $contents = file_get_contents($file);

    expect($contents)->toContain('MAX_BYTES    = 2097152');
    expect($contents)->toContain("ALLOWED_EXTS = array('png', 'jpg', 'jpeg')");
});

test('storefront logo upload rejects PDF and files over 2MB', function () {
    $file = dirname(__DIR__, 2) . '/../../plugins/box-builder-woo/includes/frontend-functions.php';
    $file = realpath($file) ?: $file;

    expect(is_readable($file))->toBeTrue();

    $contents = file_get_contents($file);

    expect($contents)->toContain('2 * 1024 * 1024');
    expect($contents)->toContain("array('png', 'jpg', 'jpeg')");
    expect($contents)->toContain('Please upload your logo in PNG or JPG format');
    expect($contents)->toContain('require_logo_upload');
    expect($contents)->toContain('accept=".png,.jpg,.jpeg"');
});
