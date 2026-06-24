/**
 * Increment/decrement controls for WooCommerce quantity inputs (product + cart).
 */
jQuery(function ($) {
  var decrementSvg =
    '<svg xmlns="http://www.w3.org/2000/svg" width="19" height="4" viewBox="0 0 19 4" fill="none"><path d="M18.0553 2.26316C18.0553 3.2175 17.2816 3.99116 16.3273 3.99116H2.0073C1.05295 3.99116 0.279297 3.2175 0.279297 2.26316C0.279297 1.30881 1.05295 0.535156 2.0073 0.535156H16.3273C17.2816 0.535156 18.0553 1.30881 18.0553 2.26316Z" fill="#291F19"/></svg>';
  var incrementSvg =
    '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="13" viewBox="0 0 12 13" fill="none"><path d="M2.08737 8.00708C1.13303 8.00708 0.359375 7.23343 0.359375 6.27908C0.359375 5.32473 1.13303 4.55108 2.08738 4.55108H4.42338V2.18308C4.42338 1.22873 5.19703 0.455078 6.15137 0.455078C7.10572 0.455078 7.87937 1.22873 7.87937 2.18308V4.55108H10.2474C11.2017 4.55108 11.9754 5.32473 11.9754 6.27908C11.9754 7.23343 11.2017 8.00708 10.2474 8.00708H7.87937V10.3431C7.87937 11.2974 7.10572 12.0711 6.15137 12.0711C5.19703 12.0711 4.42338 11.2974 4.42338 10.3431V8.00708H2.08737Z" fill="#291F19"/></svg>';

  function addQuantityButtons() {
    $('input.qty').not('.hasQtyButtons').each(function () {
      var $input = $(this);
      $input.addClass('hasQtyButtons');

      var $wrapper = $('<div class="flex relative justify-between items-center quantity_input"></div>');
      $input.wrap($wrapper);
      $wrapper = $input.parent();

      var min = parseInt($input.attr('min'), 10);
      if (isNaN(min)) {
        min = 1;
      }

      $('<button type="button" class="border border-solid border-black rounded bg-white h-[29px] w-[29px] ml-4 flex items-center justify-center decrement-btn absolute left-0 hover:bg-yellow-primary"></button>')
        .html(decrementSvg)
        .on('click', function (e) {
          e.preventDefault();
          var value = parseInt($input.val(), 10) || 0;
          $input.val(Math.max(min, value - 1));
          $input.trigger('change');
        })
        .appendTo($wrapper);

      $('<button type="button" class="border border-solid border-black rounded bg-white h-[29px] w-[29px] mr-4 flex items-center justify-center increment-btn absolute right-0 hover:bg-yellow-primary"></button>')
        .html(incrementSvg)
        .on('click', function (e) {
          e.preventDefault();
          var value = parseInt($input.val(), 10) || 0;
          var max = parseInt($input.attr('max'), 10);
          var next = value + 1;
          if (!isNaN(max) && max > 0) {
            next = Math.min(max, next);
          }
          $input.val(next);
          $input.trigger('change');
        })
        .appendTo($wrapper);
    });
  }

  addQuantityButtons();

  $(document.body).on('updated_cart_totals updated_wc_div found_variation reset_data', function () {
    addQuantityButtons();
  });
});
