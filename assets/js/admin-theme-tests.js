(function ($) {
  function renderResult(result) {
    var lines = [];
    lines.push(
      'Passed: ' + result.passed + ' | Failed: ' + result.failed + ' | Total: ' + result.total
    );

    (result.results || []).forEach(function (item) {
      lines.push((item.passed ? '[PASS]' : '[FAIL]') + ' ' + item.name + ' — ' + item.message);
    });

    return lines.join('\n');
  }

  $(document).on('click', '#matrix-run-theme-tests', function () {
    var $button = $(this);
    var $status = $('#matrix-theme-tests-status');
    var $output = $('#matrix-theme-tests-output');
    var suite = $('#matrix-theme-test-suite').val() || 'my-account-auth';

    $button.prop('disabled', true);
    $status.text('Running…');
    $output.hide().text('');

    $.post(matrixThemeTests.ajaxUrl, {
      action: 'matrix_run_theme_tests',
      nonce: matrixThemeTests.nonce,
      suite: suite,
    })
      .done(function (response) {
        var result = response.data || {};
        $status.text('All tests passed.');
        $output.text(renderResult(result)).show();
      })
      .fail(function (xhr) {
        var result = (xhr.responseJSON && xhr.responseJSON.data) || {};
        $status.text('Some tests failed.');
        if (result.total) {
          $output.text(renderResult(result)).show();
        } else {
          $output.text(xhr.responseText || 'Request failed.').show();
        }
      })
      .always(function () {
        $button.prop('disabled', false);
      });
  });
})(jQuery);
